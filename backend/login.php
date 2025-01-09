<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

ob_start();
require './config/conexao.php';
ob_end_clean();

$input = json_decode(file_get_contents('php://input'), true);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['acao'])) {
    $cpf = $input['cpf'];
    $senha = $input['senha'];

    try {
        $pdo->beginTransaction();

        // Consulta para verificar se o CPF corresponde a um titular ou a um dependente
        $sql = "
            SELECT pessoa.idPessoa as idPessoa, pessoa.senha as senha, dependente.tokenAcesso as tokenAcesso 
            FROM pessoa 
            LEFT JOIN dependente ON dependente.fkIdPessoa = pessoa.idPessoa 
            WHERE pessoa.cpf = ?
        ";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$cpf]);

        if ($consultaPreparada->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cliente não autenticado.',
                'data' => $input
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $pdo->rollBack();
            exit;
        }

        $resultado = $consultaPreparada->fetch(PDO::FETCH_ASSOC);

        // Verifica se o usuário é um dependente com tokenAcesso
        if (!empty($resultado['tokenAcesso'])) {
            // Converte a lista de tokens em um array
            $tokens = explode(', ', $resultado['tokenAcesso']);
            if (in_array($senha, $tokens)) {

                $pdo->commit();
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Dependente autenticado.',
                    'data' => [
                        'cpf' => $input['cpf'],
                        'idPessoa' => $resultado['idPessoa'],
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                if (preg_match('/_(\d+)$/', $senha, $matches)) {
                    $_SESSION['id'] = $matches[1];
                }

                $_SESSION['titular'] = false;
                exit;
            }
        }

        // Caso não seja dependente, tenta autenticar como titular usando a senha
        if (!password_verify($senha, $resultado['senha'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cliente não autenticado.',
                'data' => $input
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $pdo->rollBack();
            exit;
        }

        $sql = "SELECT t.acesso
        FROM titular t
        JOIN pessoa p ON t.fkIdPessoa = p.idPessoa
        WHERE p.cpf = ?";

        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$cpf]);

        // Verifica se algum resultado foi retornado
        if ($consultaPreparada->rowCount() > 0) {
            $row = $consultaPreparada->fetch(PDO::FETCH_ASSOC);
            $acesso = $row['acesso'];
            // Verifica o valor de 'acesso'
            if ($acesso === 'bloqueado') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Titular bloqueado.<br>Entre em contato com nosso suporte para mais informações: <b>contato@afterlife.com.br</b>'
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                $pdo->commit();
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Cliente autenticado.',
                    'data' => [
                        'cpf' => $input['cpf'],
                        'idPessoa' => $resultado['idPessoa'],
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $_SESSION['id'] = $resultado['idPessoa'];
                $_SESSION['titular'] = true;
                exit;
            }
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao tentar fazer login: ' . $e->getMessage(),
            'data' => $input
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['acao'] == "logout") {
    session_unset();
    session_destroy();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['acao']) && $_GET['acao'] == 'admin') {
    $cpf = $input['cpf'];
    $senha = $input['senha'];

    try {
        $sql = "SELECT idAdmin, senha, cadastroAceito FROM `admin` WHERE cpf = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$cpf]);

        if ($consultaPreparada->rowCount() === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Admin não autenticado',
                'data' => $input
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $resultado = $consultaPreparada->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($senha, $resultado['senha']) || $resultado['cadastroAceito'] == 'não') {
            echo json_encode([
                'status' => 'error',
                'message' => 'Admin não autenticado.',
                'data' => $input
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Remove as transações, pois não há necessidade de commit/rollback aqui
        echo json_encode([
            'status' => 'success',
            'message' => 'Admin autenticado.',
            'data' => [
                'cpf' => $input['cpf'],
                'idAdmin' => $resultado['idAdmin'],
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $_SESSION['idAdmin'] = $resultado['idAdmin'];
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao tentar fazer login: ' . $e->getMessage(),
            'data' => $input
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
