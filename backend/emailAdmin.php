<?php
session_start();
ob_start();
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';
require './config/conexao.php';

if (isset($_GET['acao']) && $_GET['acao'] == 'solicitarCadastro' && isset($_GET['token'])) {
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
        $mail->isSMTP(); // Send using SMTP
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'lucasmarques.afterlife@gmail.com'; // SMTP username
        $mail->Password = 'uyts pgyg bypw vcep'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        $stmt = $pdo->prepare("SELECT nome, nomeSocial, cpf, email, telefone FROM `admin` WHERE tokenCadastro = ?");
        $stmt->execute([$_GET['token']]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $nome = censurarInformacao($row['nome']);

        if (strlen($row['nomeSocial']) > 0) {
            $nome = censurarInformacao($row['nomeSocial']);
        }

        $cpf = censurarInformacao($row['cpf']);
        $email = censurarInformacao($row['email']);
        $telefone = censurarInformacao($row['telefone']);
        $token = $_GET['token'];

        $mail->clearAddresses();
        $mail->setFrom('lucasmarques.afterlife@gmail.com', 'Suporte Afterlife');
        $mail->addAddress('lucasmarques.afterlife@gmail.com', 'Lucas Marques');
        $mail->addReplyTo('lucasmarques.afterlife@gmail.com', 'Informação');
        $mail->isHTML(true);
        $mail->Subject = 'Solicitação de cadastro de Administrador';
        $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; text-align: center; background-color: #F1F1F1; border: 1px solid transparent; border-radius: 8px; padding: 8px;'>
                    <h2 style='color: #9AC15C; font-size: 28px;'>Solicitação de cadastro de Administrador</h2>
                    <p style='font-size: 16px;'><b>Usuário:</b> $nome</p>
                    <p style='font-size: 16px;'><b>CPF:</b> $cpf</p>
                    <p style='font-size: 16px;'><b>E-mail:</b> $email</p>
                    <p style='font-size: 16px;'><b>Telefone:</b> $telefone</p>
                    <br/>
                    <a href='http://localhost:8181/emailAdmin.php?acao=recusado&token=$token' style='display: inline-block; padding: 10px 20px; margin: 5px; font-size: 16px; color: #000; background-color: #fff; text-decoration: none; border-radius: 5px; border: 2px solid #000;'>Reprovar</a>
                    <a href='http://localhost:8181/emailAdmin.php?acao=aceito&token=$token' style='display: inline-block; padding: 10px 20px; margin: 5px; font-size: 16px; color: #fff; background-color: #91c141; text-decoration: none; border-radius: 5px; border: 2px solid #91c141'>Aprovar</a>
                </div>
            ";
        $mail->send();
        exit(); // Certifique-se de chamar exit() após o redirecionamento
    } catch (Exception $e) {
        echo "Falha ao enviar e-mail: {$mail->ErrorInfo}";
    }
}

function censurarInformacao($info)
{
    if (strlen($info) <= 6) {
        return $info;
    }

    $inicio = substr($info, 0, 3);
    $fim = substr($info, -3);

    $asteriscos = str_repeat('*', strlen($info) - 6);

    return $inicio . $asteriscos . $fim;
}

if (isset($_GET['acao']) && isset($_GET['token']) && $_GET['acao'] != 'solicitarCadastro') {
    $acao = $_GET['acao'];
    $token = $_GET['token'];

    $pdo->beginTransaction();

    $sql = "SELECT cadastroAceito FROM `admin` WHERE tokenCadastro = ?";
    $consultaPreparada = $pdo->prepare($sql);
    $consultaPreparada->execute([$token]);
    $resultado = $consultaPreparada->fetchColumn();

    if ($resultado == 'não') {
        if ($acao === 'aceito') {
            $sql = "UPDATE `admin` SET cadastroAceito = 2 WHERE tokenCadastro = ?";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([$token]);
        } elseif ($acao === 'recusado') {
            $sql = "DELETE FROM `admin` WHERE tokenCadastro = ?";
            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([$token]);
        }
        $pdo->commit();
    } else {
        $pdo->rollBack();
    }
    header("Location: http://localhost:5174/acessoAdmin");
    exit();
}

ob_end_flush();
