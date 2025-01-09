<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

require './config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idArquivos = $_POST['idArquivos'] ?? '';
    $idPasta = $_POST['idPasta'] ?? '';
    $idTitular = $_POST['idTitular'] ?? ''; // Certifique-se de que o campo "idTitular" esteja presente
    $tituloArquivo = $_POST['tituloArquivo'] ?? ''; // Título do arquivo
    $descricaoArquivo = $_POST['descricaoArquivo'] ?? ''; // Descrição do arquivo

    if (!empty($idArquivos)) {
        // Consulta para obter o caminho do arquivo pelo id
        $sql = "SELECT caminho FROM arquivo WHERE idArquivos = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$idArquivos]);
    
        // Verifica se encontrou o registro e obtém o caminho
        $resultado = $consultaPreparada->fetch(PDO::FETCH_ASSOC);
        $caminhoArquivo = $resultado['caminho'] ?? null;
    
        // Caso tenha encontrado o caminho, prossegue com a atualização
        if ($caminhoArquivo !== null) {
            // Atualização na tabela "arquivo"
            $sqlArquivo = "UPDATE arquivo 
                SET caminho = ?, titulo = ?, descricao = ?, fkIdPasta = ? 
                WHERE idArquivos = ?";
    
            // Preparar a consulta e executar a atualização
            $consultaPreparadaArquivo = $pdo->prepare($sqlArquivo);
            $consultaPreparadaArquivo->execute([$caminhoArquivo, $tituloArquivo, $descricaoArquivo, $idPasta, $idArquivos]);
        } else {
            echo "Caminho do arquivo não encontrado para o ID informado.";
        }
    } else {
        echo "ID do arquivo não fornecido.";
    }        

}
