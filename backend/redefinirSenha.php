<?php
session_start();
header("Access-Control-Allow-Methods: GET, PATCH, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

ob_start();
require './config/conexao.php';
ob_end_clean();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

if ($_SERVER['REQUEST_METHOD'] == 'PATCH' && (isset($_GET['acao']) && $_GET['acao'] == "cpf")) {
    $cpf = $input['cpf'];

    try {
        $pdo->beginTransaction();

        // Query para verificar CPF e senha
        $sql = "SELECT nome, cpf, senha, email, tokenSenha, tokenSenhaValidade FROM pessoa WHERE cpf = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$cpf]);

        if ($consultaPreparada->rowCount() === 0) {
            // CPF não encontrado
            echo json_encode([
                'status' => 'error',
                'message' => 'CPF não cadastrado.',
                'data' => $input
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $pdo->rollBack();
            exit;
        }

        $resultado = $consultaPreparada->fetch(PDO::FETCH_ASSOC);

        if (empty($resultado['senha'])) {
            // CPF encontrado, mas sem senha
            echo json_encode([
                'status' => 'error',
                'message' => 'CPF não pertence a um titular.',
                'data' => $input
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $pdo->rollBack();
            exit;
        }

        // Obtém o timestamp atual
        $tempoAtual = time();

        // Verifica se o tokenSenha foi gerado anteriormente
        if (!empty($resultado['tokenSenha'])) {
            // Converte a validade do token em timestamp
            $ultimoCodigoGerado = strtotime($resultado['tokenSenhaValidade']) - (5 * 60); // Subtrai os 5 minutos da validade

            // Verifica se já passou 1 minuto (60 segundos) desde o último envio
            if ($tempoAtual - $ultimoCodigoGerado < 60) {
                $tempoRestante = 60 - ($tempoAtual - $ultimoCodigoGerado);
                echo json_encode([
                    'status' => 'error',
                    'message' => "Aguarde $tempoRestante segundos para solicitar um novo código.",
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $pdo->rollBack();
                exit;
            }
        }

        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'lucasmarques.afterlife@gmail.com';     //SMTP username
        $mail->Password   = 'uyts pgyg bypw vcep';                  //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->CharSet = 'UTF-8';

        // Gerar novo código OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $dataValidade = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $sql = "UPDATE pessoa SET tokenSenha = ?, tokenSenhaValidade = ? WHERE cpf = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$otp, $dataValidade, $cpf]);

        // Configurar e enviar o e-mail
        $mail->clearAddresses();
        $mail->setFrom('lucasmarques.afterlife@gmail.com', 'Suporte Afterlife');
        $mail->addAddress($resultado['email'], $resultado['nome']);
        $mail->isHTML(true);
        $mail->Subject = 'Afterlife - Redefinição de senha';
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; color: #333; text-align: center; background-color: #F1F1F1; border: 1px solid transparent; border-radius: 8px; padding: 8px;'>
            <p style='font-size: 16px;'>Seu código para redefinição de senha é: " . $otp . "</p>
            <p style='font-size: 16px;'>Código válido até: " . $dataValidade . "</p>
        </div>
        ";
        $mail->send();

        echo json_encode([
            'status' => 'success',
            'message' => 'Código enviado com sucesso.',
            'email' => $resultado['email'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $pdo->commit();
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Ocorreu um erro inesperado.',
            'error' => $e->getMessage(),
            'data' => $input
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_GET['acao']) && $_GET['acao'] == "codigo")) {
    $cpf = $input['cpf'];
    $codigo = $input['codigo'];

    $sql = 'SELECT tokenSenha, tokenSenhaValidade FROM pessoa WHERE cpf = ?';
    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->execute([$cpf]);

    if ($consultaPreparada->rowCount() === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Código inválido.',
            'data' => $input
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $resultado = $consultaPreparada->fetch(PDO::FETCH_ASSOC);

    if ($codigo !== $resultado['tokenSenha']) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Código inválido.',
            'data' => $input
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $tokenSenhaValidade = $resultado['tokenSenhaValidade'];

    // Verificar se o token está expirado
    $dataAtual = new DateTime();
    $dataValidadeToken = new DateTime($tokenSenhaValidade);

    if ($dataAtual > $dataValidadeToken) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Código expirado.',
            'data' => $input
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Código válido.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'PATCH' && (isset($_GET['acao']) && $_GET['acao'] == "senha")) {
    $senha = $input['senha'];
    $cpf = $input['cpf'];

    $senhaHashed = password_hash($senha, PASSWORD_BCRYPT);

    try {
        $sql = "UPDATE pessoa SET senha = ? WHERE cpf = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([
            $senhaHashed,
            $cpf
        ]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Erro ao redefinir senha.',
            'error' => $e->getMessage(),
            'data' => $input
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode([
        'status' => 'success',
        'message' => 'Senha redefinida com sucesso!',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
