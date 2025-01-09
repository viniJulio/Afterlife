<?php
session_start();
header("Access-Control-Allow-Methods: GET, PATCH, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

ob_start();
require './config/conexao.php';
ob_end_clean();

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_GET['acao']) && $_GET['acao'] == 'dadosAdmin')) {
    try {
        $idAdmin = $_SESSION['idAdmin'];

        $sql = "SELECT nome, nomeSocial, cpf, email, telefone FROM `admin` WHERE idAdmin = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$idAdmin]);

        $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultados);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Falha ao buscar dados do Admin.',
            'error' => $e->getMessage(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['acao'])) {

    $sql = "
    SELECT 
        t.idTitular,
        t.acesso,
        pt.nome AS titular_nome,
        pt.nomeSocial AS titular_nome_social,
        pt.cpf AS titular_cpf,
        pt.email AS titular_email,
        pt.dataNascimento AS titular_dataNascimento,
        pt.cep AS titular_cep,
        pt.endereco AS titular_endereco,
        pt.numero AS titular_numero,
        pt.complemento AS titular_complemento,
        pt.bairro AS titular_bairro,
        pt.cidade AS titular_cidade,
        pt.estado AS titular_estado,
        pt.telefone1 AS titular_telefone1,
        pt.telefone2 AS titular_telefone2,
        pt.dataCriacao AS titular_dataCriacao,
        pt.dataAtualizacao AS titular_dataAtualizacao,
        pt.numeroContrato AS titular_numeroContrato,
        t.status AS titular_status,
        pd.nome AS dependente_nome,
        pd.nomeSocial AS dependente_nome_social,
        pd.cpf AS dependente_cpf,
        pd.email AS dependente_email,
        pd.dataNascimento AS dependente_dataNascimento,
        pd.cep AS dependente_cep,
        pd.endereco AS dependente_endereco,
        pd.numero AS dependente_numero,
        pd.complemento AS dependente_complemento,
        pd.bairro AS dependente_bairro,
        pd.cidade AS dependente_cidade,
        pd.estado AS dependente_estado,
        pd.telefone1 AS dependente_telefone1,
        pd.telefone2 AS dependente_telefone2,
        pd.dataCriacao AS dependente_dataCriacao,
        pd.dataAtualizacao AS dependente_dataAtualizacao,
        d.parentesco AS dependente_parentesco
    FROM titular AS t
    INNER JOIN pessoa AS pt ON t.fkIdPessoa = pt.idPessoa
    LEFT JOIN titularDependente AS td ON t.idTitular = td.fkIdTitular
    LEFT JOIN dependente AS d ON td.fkIdDependente = d.idDependente
    LEFT JOIN pessoa AS pd ON d.fkIdPessoa = pd.idPessoa
";

    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->execute();

    $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);

    // Organização dos dados para a estrutura desejada
    $data = [];
    foreach ($resultados as $row) {
        $titularId = $row['idTitular'];

        if (!isset($data[$titularId])) {
            $data[$titularId] = [
                'titular' => [
                    'id' => $titularId,
                    'acesso' => $row['acesso'],
                    'nome' => $row['titular_nome'],
                    'nomeSocial' => $row['titular_nome_social'],
                    'cpf' => $row['titular_cpf'],
                    'email' => $row['titular_email'],
                    'dataNascimento' => $row['titular_dataNascimento'],
                    'cep' => $row['titular_cep'],
                    'endereco' => $row['titular_endereco'],
                    'numero' => $row['titular_numero'],
                    'complemento' => $row['titular_complemento'],
                    'bairro' => $row['titular_bairro'],
                    'cidade' => $row['titular_cidade'],
                    'estado' => $row['titular_estado'],
                    'telefone1' => $row['titular_telefone1'],
                    'telefone2' => $row['titular_telefone2'],
                    'dataCriacao' => $row['titular_dataCriacao'],
                    'dataAtualizacao' => $row['titular_dataAtualizacao'],
                    'numeroContrato' => $row['titular_numeroContrato'],
                    'status' => $row['titular_status'],
                ],
                'dependentes' => []
            ];
        }

        if ($row['dependente_nome']) {
            $data[$titularId]['dependentes'][] = [
                'nome' => $row['dependente_nome'],
                'nomeSocial' => $row['dependente_nome_social'],
                'cpf' => $row['dependente_cpf'],
                'email' => $row['dependente_email'],
                'dataNascimento' => $row['dependente_dataNascimento'],
                'cep' => $row['dependente_cep'],
                'endereco' => $row['dependente_endereco'],
                'numero' => $row['dependente_numero'],
                'complemento' => $row['dependente_complemento'],
                'bairro' => $row['dependente_bairro'],
                'cidade' => $row['dependente_cidade'],
                'estado' => $row['dependente_estado'],
                'telefone1' => $row['dependente_telefone1'],
                'telefone2' => $row['dependente_telefone2'],
                'dataCriacao' => $row['dependente_dataCriacao'],
                'dataAtualizacao' => $row['dependente_dataAtualizacao'],
                'parentesco' => $row['dependente_parentesco'],
            ];
        }
    }

    // Resultado final
    echo json_encode(array_values($data));
}

if ($_SERVER['REQUEST_METHOD'] == 'PATCH' && isset($_GET['acao']) && $_GET['acao'] == 'editAdmin') {
    try {
        $idAdmin = $_SESSION['idAdmin'];
        $nome = $input['nome'];
        $nomeSocial = $input['nomeSocial'];
        $cpf = preg_replace('/[^0-9]/', '', $input['cpf']) ?? '';
        $email = $input['email'];
        $telefone = preg_replace('/[^0-9]/', '', $input['telefone']) ?? '';

        $pdo->beginTransaction();

        // Verificar CPF no banco de dados
        $sql = "SELECT idAdmin FROM `admin` WHERE cpf = ? AND idAdmin != ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$cpf, $idAdmin]);
        if ($consultaPreparada->rowCount() > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O CPF informado já está cadastrado em outro administrador.'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Verificar e-mail no banco de dados
        $sql = "SELECT idAdmin FROM `admin` WHERE email = ? AND idAdmin != ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$email, $idAdmin]);
        if ($consultaPreparada->rowCount() > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O e-mail informado já está cadastrado em outro administrador.'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Atualizar o administrador no banco de dados
        $sql = "UPDATE `admin` SET 
            nome = ?, 
            nomeSocial = ?, 
            cpf = ?, 
            email = ?, 
            telefone = ? 
            WHERE idAdmin = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([
            $nome,
            $nomeSocial,
            $cpf,
            $email,
            $telefone,
            $idAdmin,
        ]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Administrador atualizado com sucesso.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Falha ao atualizar o administrador.',
            'error' => $e->getMessage(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PATCH' && !isset($_GET['acao'])) {

    $id = $input['id'];

    // Consulta o valor atual do campo 'acesso' do titular
    $sql = "SELECT acesso FROM titular WHERE idTitular = ?";
    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->execute([$id]);

    // Verifica se o titular existe
    if ($consultaPreparada->rowCount() > 0) {
        $row = $consultaPreparada->fetch(PDO::FETCH_ASSOC);
        $acessoAtual = $row['acesso'];

        // Alterna entre 'bloqueado' e 'desbloqueado'
        $novoAcesso = ($acessoAtual === 'desbloqueado') ? 'bloqueado' : 'desbloqueado';

        // Atualiza o campo 'acesso'
        $sql = "UPDATE titular SET acesso = ? WHERE idTitular = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$novoAcesso, $id]);

        // Resposta de sucesso
        echo json_encode(['status' => 'success', 'message' => 'Titular ' . $novoAcesso . ' com sucesso!']);
    } else {
        // Se o titular não for encontrado
        echo json_encode(['status' => 'error', 'message' => 'Titular não encontrado']);
    }
}
