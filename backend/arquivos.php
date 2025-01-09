<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

ob_start();
require './config/conexao.php';
ob_end_clean();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo'])) {
    $idArquivos = $_POST['idArquivos'] ?? '';
    $idPasta = $_POST['idPasta'] ?? '';
    $idTitular = $_POST['idTitular'] ?? ''; // Certifique-se de que o campo "idTitular" esteja presente
    $tituloArquivo = $_POST['tituloArquivo'] ?? ''; // Título do arquivo
    $descricaoArquivo = $_POST['descricaoArquivo'] ?? ''; // Descrição do arquivo

    $arquivos = [];
    if (is_array($_FILES['arquivo']['name'])) {
        $fileCount = count($_FILES['arquivo']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $arquivos[] = [
                'name' => $_FILES['arquivo']['name'][$i],
                'type' => $_FILES['arquivo']['type'][$i],
                'tmp_name' => $_FILES['arquivo']['tmp_name'][$i],
                'error' => $_FILES['arquivo']['error'][$i],
                'size' => $_FILES['arquivo']['size'][$i],
            ];
        }
    } else {
        // Caso apenas um arquivo tenha sido enviado
        $arquivos[] = $_FILES['arquivo'];
    }

    // Variável para armazenar os caminhos dos arquivos concatenados
    $caminhosArquivos = '';

    foreach ($arquivos as $arquivo) {
        if ($arquivo['error'] === UPLOAD_ERR_OK) {
            // Obter extensão do arquivo
            $tipoArquivo = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

            // Tipos de arquivo permitidos: imagens, vídeos e áudios
            $tiposPermitidosImagens = ['jpg', 'jpeg', 'png'];
            $tiposPermitidosVideos = ['mp4', 'avi', 'wmv', 'avchd'];
            $tiposPermitidosAudios = ['mp3'];
            $tiposPermitidosDocumentos = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'odt', 'rtf', 'txt', 'html', 'epub', 'md'];

            // Verifica o tipo de arquivo e define o diretório de destino
            if (in_array($tipoArquivo, $tiposPermitidosImagens)) {
                $pastaDestino = 'assets/arquivos/images/';
            } elseif (in_array($tipoArquivo, $tiposPermitidosVideos)) {
                $pastaDestino = 'assets/arquivos/videos/';
            } elseif (in_array($tipoArquivo, $tiposPermitidosAudios)) {
                $pastaDestino = 'assets/arquivos/audios/';
            } elseif (in_array($tipoArquivo, $tiposPermitidosDocumentos)) {
                $pastaDestino = 'assets/arquivos/documentos/';
            } else {
                echo json_encode(['error' => 'Formato de arquivo inválido. Apenas imagens (JPG, PNG, JPEG), vídeos (MP4, AVI, WMV, AVCHD) e áudios (MP3) são permitidos.']);
                exit;
            }

            // Cria o diretório, se não existir
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0777, true);
            }

            // Gera um nome de arquivo aleatório usando MD5 e Uniqid
            $nomeAleatorio = md5(uniqid(rand(), true)) . '.' . $tipoArquivo;
            $caminhoArquivo = $pastaDestino . $nomeAleatorio;

            // Movendo o arquivo para o diretório de destino
            if (move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
                // Adiciona o caminho do arquivo à string concatenada, separados por vírgula
                $caminhosArquivos .= ($caminhosArquivos ? ',' : '') . $caminhoArquivo;
            } else {
                echo json_encode(['error' => 'Falha ao mover o arquivo ' . $arquivo['name']]);
                exit;
            }
        } else {
            echo json_encode(['error' => 'Falha no upload do arquivo ' . $arquivo['name']]);
            exit;
        }
    }

    // Agora você tem a string $caminhosArquivos com os caminhos separados por vírgula

    try {
        // Inicia uma transação
        $pdo->beginTransaction();

        // Verifica se a pasta existe no banco de dados
        $sqlPasta = "SELECT idPasta FROM pasta WHERE idPasta = ?";
        $consultaPasta = $pdo->prepare($sqlPasta);
        $consultaPasta->execute([$idPasta]);

        // Se a pasta não existir, retorna um erro
        if ($consultaPasta->rowCount() === 0) {
            echo json_encode(['error' => 'A pasta especificada não existe.']);
            $pdo->rollBack(); // Desfaz a transação
            exit;
        }

        if (empty($idArquivos)) {
            // Inserção na tabela "arquivo"
            $sqlArquivo = "INSERT INTO arquivo (caminho, titulo, descricao, fkidPasta) 
                           VALUES (?, ?, ?, ?)";

            // Preparar a consulta e executar inserção
            $consultaPreparadaArquivo = $pdo->prepare($sqlArquivo);
            $consultaPreparadaArquivo->execute([$caminhosArquivos, $tituloArquivo, $descricaoArquivo, $idPasta]);
        } else {
            // Atualização na tabela "arquivo"
            $sqlArquivo = "UPDATE arquivo 
                           SET caminho = ?, titulo = ?, descricao = ?, fkIdPasta = ? 
                           WHERE idArquivos = ?";

            // Preparar a consulta e executar a atualização
            $consultaPreparadaArquivo = $pdo->prepare($sqlArquivo);
            $consultaPreparadaArquivo->execute([$caminhosArquivos, $tituloArquivo, $descricaoArquivo, $idPasta, $idArquivos]);
        }

        // Confirma a transação
        $pdo->commit();

        echo json_encode(['success' => 'Arquivos enviados e salvos com sucesso.']);
    } catch (PDOException $e) {
        // Desfaz a transação em caso de erro
        $pdo->rollBack();
        echo json_encode(['error' => 'Erro ao salvar os arquivos: ' . $e->getMessage()]);
    }
}

// Método GET para buscar dados
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $id = $_GET['idPasta'] ?? '';

    if (!empty($id)) {
        $sql = "SELECT idArquivos, caminho, titulo, descricao, dataCriacao, dataAtualizacao FROM arquivo WHERE fkIdPasta = ?";
        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([$id]);
    }

    $resultados = $consultaPreparada->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
}

// Método DELETE para excluir dados
if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $id = $_GET['idArquivos'] ?? '';

    try {
        // Obtenha o caminho do arquivo a partir do banco de dados antes de excluir o registro
        $sqlSelect = "SELECT caminho FROM arquivo WHERE idArquivos = :id";
        $consultaSelect = $pdo->prepare($sqlSelect);
        $consultaSelect->bindParam(':id', $id);
        $consultaSelect->execute();
        $arquivo = $consultaSelect->fetch(PDO::FETCH_ASSOC);

        if ($arquivo) {
            // Divide os caminhos em um array
            $caminhos = explode(',', $arquivo['caminho']);

            foreach ($caminhos as $caminho) {
                $caminho = trim($caminho); // Remove espaços extras
                // Tenta excluir o arquivo local se ele existir
                if (file_exists($caminho)) {
                    unlink($caminho);
                }
            }

            // Depois de excluir os arquivos localmente, exclua o registro no banco de dados
            $sqlDelete = "DELETE FROM arquivo WHERE idArquivos = :id";
            $consultaDelete = $pdo->prepare($sqlDelete);
            $consultaDelete->bindParam(':id', $id);
            $consultaDelete->execute();

            echo json_encode(['status' => 'success', 'message' => 'Arquivos excluídos com sucesso.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registro não encontrado no banco de dados.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir os arquivos: ' . $e->getMessage()]);
    }
}
