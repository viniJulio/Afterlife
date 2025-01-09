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
    $senha = $input['senha'] ?? '';
    $fkIdPasta = $input['fkIdPasta'] ?? null;

    if (empty($titulo) || empty($descricao) || empty($senha)) {
        echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    try {
        if (!empty($id)) {
            $sql = "UPDATE arquivo SET titulo = ?, descricao = ?, senha = ? WHERE idArquivos = ?";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([$titulo, $descricao, $senha, $id]);
        } else {
            $sql = "INSERT INTO arquivo (titulo, descricao, senha, fkIdPasta) VALUES (?, ?, ?, ?)";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([$titulo, $descricao, $senha, $fkIdPasta]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao salvar a senha: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id = $_GET['idPasta'];
    echo $id;
    $sql = "SELECT * FROM arquivo WHERE fkIdPasta = ?";
    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->execute([$id]);

    $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $input['idArquivos'];
    try {
        $sql = "DELETE FROM arquivo WHERE idArquivos = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$id]);
        echo json_encode(['success' => 'Senha excluída com sucesso.']);
    } catch (PDOException $e){
        echo json_encode(['error' => 'Erro ao excluir a senha: ' . $e->getMessage()]);
    }
}