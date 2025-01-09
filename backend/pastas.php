<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

require './config/conexao.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $input['idPasta'] ?? '';
    $titulo = $input['titulo'] ?? '';
    $tipo = $input['tipo'] ?? '';
    $descricao = $input['descricao'] ?? '';
    $idPessoa = $_SESSION['id'];

    if (empty($titulo) || empty($tipo) || empty($descricao)) {
        echo json_encode(['error' => 'Todos os campos são obrigatórios']);
        exit;
    }

    try {

        $sql = "SELECT idTitular FROM titular WHERE fkIdPessoa = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$idPessoa]);

        $resultado = $consultaPreparada->fetch(PDO::FETCH_ASSOC);

        $idTitular = $resultado['idTitular'];
        
        if (!empty($id)) {
            // Editar
            $sql = "UPDATE pasta SET titulo = :titulo, tipo = :tipo, descricao = :descricao, dataAtualizacao = NOW() WHERE idPasta = :idPasta";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->bindParam(':titulo', $titulo);
            $consultaPreparada->bindParam(':tipo', $tipo);
            $consultaPreparada->bindParam(':descricao', $descricao);
            $consultaPreparada->bindParam(':idPasta', $id);
            $consultaPreparada->execute();
        } else {
            // Criar
            $sql = "INSERT INTO pasta (titulo, tipo, descricao, fkIdTitular, dataCriacao, dataAtualizacao) VALUES (:titulo, :tipo, :descricao, :fkIdTitular, NOW(), NOW())";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->bindParam(':titulo', $titulo);
            $consultaPreparada->bindParam(':tipo', $tipo);
            $consultaPreparada->bindParam(':descricao', $descricao);
            $consultaPreparada->bindParam(':fkIdTitular', $idTitular);
            $consultaPreparada->execute();
        }

        echo json_encode(['success' => 'Dados salvos com sucesso.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao salvar a pasta: ' . $e->getMessage(), 'message' => [$idTitular]]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $id = isset($_GET['idPasta']) ? $_GET['idPasta'] : null;

    if (isset($_GET['acao']) && $_GET['acao'] == "folderExists") {
        $sql = "SELECT pasta.idPasta
        FROM pasta
        INNER JOIN titular ON titular.idTitular = pasta.fkIdTitular
        INNER JOIN pessoa ON pessoa.idPessoa = titular.fkIdPessoa
        WHERE pessoa.idPessoa = ?";

        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$_SESSION['id']]);

        $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultados);
    } else {
        if (!empty($id)) {
            $sql = "SELECT titulo FROM pasta WHERE idPasta = :idPasta";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->bindParam(':idPasta', $id);
            $consultaPreparada->execute();

            $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($resultados);
        } else {
            $sql = "SELECT pasta.idPasta, pasta.titulo, pasta.tipo, pasta.descricao, pasta.dataCriacao, pasta.dataAtualizacao, pasta.fkIdTitular
        FROM pasta
        INNER JOIN titular ON titular.idTitular = pasta.fkIdTitular
        INNER JOIN pessoa ON pessoa.idPessoa = titular.fkIdPessoa
        WHERE pessoa.idPessoa = ?";

            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([$_SESSION['id']]);

            $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($resultados);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $input['idPasta'];
    try {
        $sql = "DELETE FROM pasta WHERE idPasta = :idPasta";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->bindParam(':idPasta', $id);
        $consultaPreparada->execute();
        echo json_encode(['success' => 'Pasta excluída com sucesso.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao excluir a pasta: ' . $e->getMessage()]);
    }
}
