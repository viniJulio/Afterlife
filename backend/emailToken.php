<?php
session_start();
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';
require './config/conexao.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                       //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'lucasmarques.afterlife@gmail.com';     //SMTP username
    $mail->Password   = 'uyts pgyg bypw vcep';                  //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    $mail->CharSet = 'UTF-8';

    // Gera o token de 50 caracteres
    $token = bin2hex(random_bytes(25));

    // Consulta para buscar os dependentes de titulares com status 2
    $stmt = $pdo->prepare("
    SELECT p.idPessoa, d.tokenAcesso AS tokenAcesso, d.idDependente as idDependente, p.email AS emailDependente, t.fkIdPessoa, pt.cpf AS cpfTitular, pt.nome AS nomeTitular, pt.idPessoa AS idPessoaTitular
    FROM titular t
    INNER JOIN pessoa pt ON t.fkIdPessoa = pt.idPessoa
    INNER JOIN titularDependente td ON t.idTitular = td.fkIdTitular
    INNER JOIN dependente d ON td.fkIdDependente = d.idDependente
    INNER JOIN pessoa p ON d.fkIdPessoa = p.idPessoa
    WHERE t.status = 2
    ");

    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cpfTitular = $row['cpfTitular'];
        $nomeTitular = $row['nomeTitular'];
        $emailDependente = $row['emailDependente'];
        $idPessoaTitular = $row['idPessoaTitular'];

        // Gera o token com o idPessoa do titular
        $token = bin2hex(random_bytes(25)) . '_' . $idPessoaTitular;

        // Atualiza o campo tokenAcesso com o novo token, mantendo tokens anteriores
        $tokenAcessoAtual = $row['tokenAcesso'];
        $novoTokenAcesso = $tokenAcessoAtual ? $tokenAcessoAtual . ', ' . $token : $token;

        // Atualiza o tokenAcesso na tabela dependente
        $pdo->beginTransaction();
        $updateStmt = $pdo->prepare("UPDATE dependente SET tokenAcesso = :novoTokenAcesso WHERE idDependente = :idDependente");
        $updateStmt->execute([
            ':novoTokenAcesso' => $novoTokenAcesso,
            ':idDependente' => $row['idDependente']
        ]);
        $pdo->commit();
        $nomeTitular = strtoupper($nomeTitular);
        // Configura o destinatário e o conteúdo do e-mail
        $mail->clearAddresses();
        $mail->setFrom('lucasmarques.afterlife@gmail.com', 'Suporte Afterlife');
        $mail->addAddress('lucasmarques.afterlife@gmail.com', 'Lucas Marques');     //Add a recipient
        $mail->addReplyTo('lucasmarques.afterlife@gmail.com', 'Informação');
        $mail->isHTML(true);
        $mail->Subject = 'Acesso aos documentos de ' . $nomeTitular;
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; text-align: center; background-color: #F1F1F1; border: 1px solid transparent; border-radius: 8px; padding: 8px;'>
                <h2 style='color: #9AC15C; font-size: 32px;'>Visualização de documentos</h2>
                <p style='font-size: 16px;'>Olá, os documentos do(a) titular <b>$nomeTitular</b> foram disponibilizados para visualização dos dependentes.</p>
                <br/>
                <h2 style='color: #617A95; font-size: 24px;'>Instruções de acesso</h2>
                <p style='font-size: 16px;'>Acesse a plataforma <a href='http://localhost:5174'>Afterlife</a></p>
                <p style='font-size: 16px;'>Clique em <b>Entrar</b></p>
                <p style='font-size: 16px;'>Insira o seu CPF e no campo Senha insira o <b>Token</b> informado.</p>
                <p style='font-size: 16px;'>Token de acesso: <br><b>$token</b></p>
            </div>
        ";
        $mail->AltBody = "Os documentos do titular $nomeTitular (CPF: $cpfTitular) foram disponibilizados para visualização. Token de acesso: $token";

        // Envia o e-mail
        $mail->send();
    }
    echo 'E-mails enviados com sucesso.';
} catch (Exception $e) {
    echo "Falha ao enviar e-mail: {$mail->ErrorInfo}";
}
