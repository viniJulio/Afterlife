<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

ob_start();
require './config/conexao.php';
ob_end_clean();

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['idEmpresa'];
    $nomeResponsavel = $_POST['nomeResponsavel'] ?? '';
    $emailResponsavel = $_POST['emailResponsavel'] ?? '';
    $telefoneResponsavel = preg_replace('/[^\d]/', '', $_POST['telefoneResponsavel']);
    $razaoSocial = $_POST['razaoSocial'] ?? '';
    $nomeFantasia = $_POST['nomeFantasia'] ?? '';
    $cnpj = preg_replace('/[^\d]/', '', $_POST['cnpj']);
    $inscEstadual = preg_replace('/[^\d]/', '', $_POST['inscEstadual']) ?? '';
    $inscMunicipal = preg_replace('/[^\d]/', '', $_POST['inscMunicipal']) ?? '';
    $cep = preg_replace('/[^\d]/', '', $_POST['cep']);
    $endereco = $_POST['endereco'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $telefone1 = preg_replace('/[^\d]/', '', $_POST['telefone1']);
    $telefone2 = preg_replace('/[^\d]/', '', $_POST['telefone2']) ?? '';
    $email = $_POST['email'] ?? '';
    $tipoEmpresa = $_POST['tipoEmpresa'] ?? '';
    $redesSociais = $_POST['redesSociais'] ?? '';

    $caminhoArquivo = '';

    if (
        empty($nomeResponsavel) ||
        empty($emailResponsavel) ||
        empty($telefoneResponsavel) ||
        empty($razaoSocial) ||
        empty($cnpj) ||
        empty($cep) ||
        empty($endereco) ||
        empty($numero) ||
        empty($bairro) ||
        empty($cidade) ||
        empty($estado) ||
        empty($telefone1) ||
        empty($email) ||
        empty($tipoEmpresa)
    ) {
        echo json_encode(['status' => 'error', 'message' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    $arquivos = [
        'logo' => 'caminhoLogo',             // Para o campo 'logo', aceitamos imagens
        'logoPequena' => 'caminhoLogoPequena' // Para o campo 'logoPequena', aceitamos apenas .ico
    ];
    
    $caminhos = []; // Array para armazenar os caminhos dos arquivos para o banco
    
    $caminhoLogo = '';
    $caminhoLogoPequena = '';
    
    foreach ($arquivos as $chave => $campoBanco) {
        if (isset($_FILES[$chave]) && $_FILES[$chave]['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES[$chave];
    
            // Obter a extensão do arquivo
            $tipoArquivo = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    
            // Verifica o tipo de arquivo conforme o campo
            if ($chave === 'logo') {
                // Tipos de arquivo permitidos para 'logo' (imagens)
                $tiposPermitidosImagens = ['jpg', 'jpeg', 'png'];
    
                // Verifica se o tipo do arquivo é uma imagem
                if (!in_array($tipoArquivo, $tiposPermitidosImagens)) {
                    echo json_encode(['status' => 'error', 'message' => 'Formato de arquivo inválido para ' . $chave]);
                    exit;
                }
            } elseif ($chave === 'logoPequena') {
                // Verifica se o arquivo é .ico para 'logoPequena'
                if ($tipoArquivo !== 'ico') {
                    echo json_encode(['status' => 'error', 'message' => 'Formato de arquivo inválido para ' . $chave . '. Apenas arquivos .ico são permitidos.']);
                    exit;
                }
            }
    
            // Caminho de destino para os arquivos
            $pastaDestino = 'assets/arquivos/images/';
    
            // Cria o diretório se não existir
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0777, true);
            }
    
            // Define o nome do arquivo com base no campo (logo ou logoPequena)
            $nomeArquivo = $chave . '.' . $tipoArquivo;
            $caminhoArquivo = $pastaDestino . $nomeArquivo;
    
            // Consulta para obter o caminho atual no banco
            $sql = "SELECT $campoBanco FROM empresa LIMIT 1";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute();
            $logo = $consultaPreparada->fetch(PDO::FETCH_ASSOC);
    
            // Remove o arquivo antigo, se existir
            if ($logo && file_exists($logo[$campoBanco])) {
                unlink($logo[$campoBanco]);
            }
    
            // Move o arquivo para o diretório de destino
            if (move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
                $caminhos[$campoBanco] = $caminhoArquivo; // Armazena o caminho no array
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Falha ao mover o arquivo ' . $arquivo['name']]);
                exit;
            }
        }
    }

    $pdo->beginTransaction();

    try {
        if (!empty($id)) {

            $sqlSelect = "SELECT caminhoLogo, caminhoLogoPequena FROM empresa WHERE idEmpresa = :idEmpresa";
            $consultaPreparada = $pdo->prepare($sqlSelect);
            $consultaPreparada->bindParam(':idEmpresa', $id);
            $consultaPreparada->execute();
            $empresa = $consultaPreparada->fetch(PDO::FETCH_ASSOC);

            if (empty($caminhos['caminhoLogo'])) {
                $caminhoLogo = $empresa['caminhoLogo'];
            } else {
                $caminhoLogo = $caminhos['caminhoLogo'];
            }

            if (empty($caminhos['caminhoLogoPequena'])) {
                $caminhoLogoPequena = $empresa['caminhoLogoPequena'];
            } else {
                $caminhoLogoPequena = $caminhos['caminhoLogoPequena'];
            }

            $sql = "UPDATE empresa SET
                nomeResponsavel = :nomeResponsavel,
                emailResponsavel = :emailResponsavel,
                telefoneResponsavel = :telefoneResponsavel,
                razaoSocial = :razaoSocial,
                nomeFantasia = :nomeFantasia,
                cnpj = :cnpj,
                inscEstadual = :inscEstadual,
                inscMunicipal = :inscMunicipal,
                cep = :cep,
                endereco = :endereco,
                numero = :numero,
                complemento = :complemento,
                bairro = :bairro,
                cidade = :cidade,
                estado = :estado,
                telefone1 = :telefone1,
                telefone2 = :telefone2,
                email = :email,
                caminhoLogo = :caminhoLogo,
                caminhoLogoPequena = :caminhoLogoPequena,
                tipoEmpresa = :tipoEmpresa,
                redesSociais = :redesSociais
                WHERE idEmpresa = :idEmpresa";

            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->bindParam(':idEmpresa', $id);
            $consultaPreparada->bindParam(':nomeResponsavel', $nomeResponsavel);
            $consultaPreparada->bindParam(':emailResponsavel', $emailResponsavel);
            $consultaPreparada->bindParam(':telefoneResponsavel', $telefoneResponsavel);
            $consultaPreparada->bindParam(':razaoSocial', $razaoSocial);
            $consultaPreparada->bindParam(':nomeFantasia', $nomeFantasia);
            $consultaPreparada->bindParam(':cnpj', $cnpj);
            $consultaPreparada->bindParam(':inscEstadual', $inscEstadual);
            $consultaPreparada->bindParam(':inscMunicipal', $inscMunicipal);
            $consultaPreparada->bindParam(':cep', $cep);
            $consultaPreparada->bindParam(':endereco', $endereco);
            $consultaPreparada->bindParam(':numero', $numero);
            $consultaPreparada->bindParam(':complemento', $complemento);
            $consultaPreparada->bindParam(':bairro', $bairro);
            $consultaPreparada->bindParam(':cidade', $cidade);
            $consultaPreparada->bindParam(':estado', $estado);
            $consultaPreparada->bindParam(':telefone1', $telefone1);
            $consultaPreparada->bindParam(':telefone2', $telefone2);
            $consultaPreparada->bindParam(':email', $email);
            $consultaPreparada->bindParam(':caminhoLogo', $caminhoLogo);
            $consultaPreparada->bindParam(':caminhoLogoPequena', $caminhoLogoPequena);
            $consultaPreparada->bindParam(':tipoEmpresa', $tipoEmpresa);
            $consultaPreparada->bindParam(':redesSociais', $redesSociais);
            $consultaPreparada->execute();
        } else {

            $caminhoLogo = $caminhos['caminhoLogo'] ?? null;
            $caminhoLogoPequena = $caminhos['caminhoLogoPequena'] ?? null;

            $sql = "INSERT INTO empresa 
                (nomeResponsavel, emailResponsavel, telefoneResponsavel, razaoSocial, nomeFantasia, cnpj, inscEstadual, inscMunicipal,
                 cep, endereco, numero, complemento, bairro, cidade, estado, telefone1, telefone2, email, caminhoLogo, caminhoLogoPequena, tipoEmpresa, redesSociais)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([
                $nomeResponsavel,
                $emailResponsavel,
                $telefoneResponsavel,
                $razaoSocial,
                $nomeFantasia,
                $cnpj,
                $inscEstadual,
                $inscMunicipal,
                $cep,
                $endereco,
                $numero,
                $complemento,
                $bairro,
                $cidade,
                $estado,
                $telefone1,
                $telefone2,
                $email,
                $caminhoLogo,
                $caminhoLogoPequena,
                $tipoEmpresa,
                $redesSociais
            ]);
        }
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Dados salvos com sucesso.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar a empresa: ' . $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM empresa";
    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->execute();

    $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
}

if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $_POST['idEmpresa'];
    echo 'id' . $id;
    try {
        $sql = "DELETE FROM empresa WHERE idEmpresa = :idEmpresa";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->bindParam(':idEmpresa', $id);
        $consultaPreparada->execute();
        echo json_encode(['status' => 'success', 'message' => 'Empresa excluída com sucesso.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir a empresa: ' . $e->getMessage()]);
    }
}
