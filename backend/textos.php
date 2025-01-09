<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');
require './config/conexao.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $input['idArquivos'];
    $titulo = $input['titulo'] ?? '';
    $descricao = $input['descricao'] ?? '';
    $texto = $input['texto'] ?? '';
    $fkIdPasta = $input['fkIdPasta'] ?? null;

    if (empty($titulo) || empty($descricao) || empty($texto)) {
        echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    try {
        if (!empty($id)) {
            // Editar
            $sql = "UPDATE arquivo SET titulo = :titulo, descricao = :descricao, texto = :texto, dataAtualizacao = NOW() WHERE idArquivos = :idArquivos";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->bindParam(':titulo', $titulo);
            $consultaPreparada->bindParam(':descricao', $descricao);
            $consultaPreparada->bindParam(':texto', $texto);
            $consultaPreparada->bindParam(':idArquivos', $id);
            $consultaPreparada->execute();
        } else {
            // Criar
            $sql = "INSERT INTO arquivo (titulo, descricao, texto, fkIdPasta, dataCriacao, dataAtualizacao) VALUES (:titulo, :descricao, :texto, :fkIdPasta, NOW(), NOW())";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->bindParam(':titulo', $titulo);
            $consultaPreparada->bindParam(':descricao', $descricao);
            $consultaPreparada->bindParam(':texto', $texto);
            $consultaPreparada->bindParam(':fkIdPasta', $fkIdPasta);
            $consultaPreparada->execute();
        }

        echo json_encode(['success' => 'Dados salvos com sucesso.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao salvar o texto: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id = $_GET['idPasta'];
    echo $id;
    $sql = "SELECT * FROM arquivo WHERE fkIdPasta = :fkIdPasta";
    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->bindParam(':fkIdPasta', $id);
    $consultaPreparada->execute();

    $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC); 
    echo json_encode($resultados); 
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $input['idArquivos'];
    try {
        $sql = "DELETE FROM arquivo WHERE idArquivos = :id";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->bindParam(':id', $id);
        $consultaPreparada->execute();
        echo json_encode(['success' => 'Texto excluído com sucesso.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao excluir o texto: ' . $e->getMessage()]);
    }
}
