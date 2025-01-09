<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
require './config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['acao'] == 'titulares') {
    // ID do dependente logado
    $idDependente = $_SESSION['id'];

    // Consulta para obter os titulares relacionados ao dependente logado
    $sql = "SELECT pessoa.idPessoa, pessoa.nome, pessoa.cpf 
            FROM pessoa
            INNER JOIN titular ON titular.fkIdPessoa = pessoa.idPessoa
            INNER JOIN titularDependente ON titularDependente.fkIdTitular = titular.idTitular
            WHERE titularDependente.fkIdDependente = ?";

    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->execute([$idDependente]);

    $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['acao'] == 'isLoggedIn') {
    if (!isset($_SESSION['id'])) {
        echo json_encode(['status' => 'erro']);
    } else {
        echo json_encode(['status' => 'sucesso']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['acao'] == 'isAdmin') {
    if (!isset($_SESSION['idAdmin'])) {
        echo json_encode(['status' => 'erro']);
    } else {
        echo json_encode(['status' => 'sucesso']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['acao'] == 'isTitular') {
    if ($_SESSION['titular'] == false) {
        echo json_encode(['status' => false, 'message' => $_SESSION['id']]);
    } else if ($_SESSION['titular'] == true) {
        echo json_encode(['status' => true, 'message' => $_SESSION['id']]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['acao'] == 'alteracao') {
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];
    
        $sql = "
        SELECT
            pessoa.idPessoa AS idPessoa,
            pessoa.nome AS nomeTitular,
            pessoa.nomeSocial,
            pessoa.cpf,
            pessoa.dataNascimento,
            pessoa.email,
            pessoa.telefone1,
            pessoa.telefone2,
            pessoa.numeroContrato,
            pessoa.cep,
            pessoa.endereco,
            pessoa.numero,
            pessoa.complemento,
            pessoa.bairro,
            pessoa.cidade,
            pessoa.estado,
            pessoa.senha,
            pessoaDependente.idPessoa AS idDependente,
            pessoaDependente.nome AS nomeDependente,
            pessoaDependente.nomeSocial AS nomeSocialDependente,
            pessoaDependente.email AS emailDependente,
            dependente.parentesco AS relacionamentoDependente,
            pessoaDependente.cpf AS cpfDependente,
            pessoaDependente.dataNascimento AS dataNascimentoDependente,
            pessoaDependente.telefone1 AS telefone1Dependente,
            pessoaDependente.telefone2 AS telefone2Dependente,
            pessoaDependente.cep AS cepDependente,
            pessoaDependente.endereco AS enderecoDependente,
            pessoaDependente.numero AS numeroDependente,
            pessoaDependente.complemento AS complementoDependente,
            pessoaDependente.bairro AS bairroDependente,
            pessoaDependente.cidade AS cidadeDependente,
            pessoaDependente.estado AS estadoDependente
        FROM pessoa
        INNER JOIN titular ON titular.fkIdPessoa = pessoa.idPessoa
        INNER JOIN titularDependente ON titularDependente.fkIdTitular = titular.idTitular
        INNER JOIN dependente ON dependente.idDependente = titularDependente.fkIdDependente
        INNER JOIN pessoa AS pessoaDependente ON pessoaDependente.idPessoa = dependente.fkIdPessoa
        WHERE pessoa.idPessoa = ?
        ";

        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$id]);
        $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
        
        // Estrutura para armazenar os dados do titular e dos dependentes
        $dados = [
            'idPessoa' => $resultados[0]['idPessoa'] ?? '',
            'nomeTitular' => $resultados[0]['nomeTitular'] ?? '',
            'nomeSocial' => $resultados[0]['nomeSocial'] ?? '',
            'cpf' => $resultados[0]['cpf'] ?? '',
            'dataNascimento' => $resultados[0]['dataNascimento'] ?? '',
            'email' => $resultados[0]['email'] ?? '',
            'telefone1' => $resultados[0]['telefone1'] ?? '',
            'telefone2' => $resultados[0]['telefone2'] ?? '',
            'numeroContrato' => $resultados[0]['numeroContrato'] ?? '',
            'cep' => $resultados[0]['cep'] ?? '',
            'endereco' => $resultados[0]['endereco'] ?? '',
            'numero' => $resultados[0]['numero'] ?? '',
            'complemento' => $resultados[0]['complemento'] ?? '',
            'bairro' => $resultados[0]['bairro'] ?? '',
            'cidade' => $resultados[0]['cidade'] ?? '',
            'estado' => $resultados[0]['estado'] ?? '',
            'senha' => $resultados[0]['senha'] ?? '',
            'dependentes' => []
        ];

        // Preenche a lista de dependentes
        foreach ($resultados as $resultado) {
            $dependente = [
                'idDependente' => $resultado['idDependente'] ?? '',
                'nome' => $resultado['nomeDependente'] ?? '',
                'nomeSocial' => $resultado['nomeSocialDependente'] ?? '',
                'email' => $resultado['emailDependente'] ?? '',
                'relacionamento' => $resultado['relacionamentoDependente'] ?? '',
                'cpf' => $resultado['cpfDependente'] ?? '',
                'dataNascimento' => $resultado['dataNascimentoDependente'] ?? '',
                'telefone1' => $resultado['telefone1Dependente'] ?? '',
                'telefone2' => $resultado['telefone2Dependente'] ?? '',
                'cep' => $resultado['cepDependente'] ?? '',
                'endereco' => $resultado['enderecoDependente'] ?? '',
                'numero' => $resultado['numeroDependente'] ?? '',
                'complemento' => $resultado['complementoDependente'] ?? '',
                'bairro' => $resultado['bairroDependente'] ?? '',
                'cidade' => $resultado['cidadeDependente'] ?? '',
                'estado' => $resultado['estadoDependente'] ?? ''
            ];
            
            // Adiciona o dependente à lista de dependentes
            $dados['dependentes'][] = $dependente;
        }

        // Retorna os dados em JSON
        echo json_encode($dados);
    }
}
