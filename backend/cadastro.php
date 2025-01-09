<?php
session_start();
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

ob_start();
require './config/conexao.php';
ob_end_clean();

$input =  json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['acao'])) {

    // Dados do titular
    $idPessoa = $input['idPessoa'] ?? '';
    $nomeTitular = $input['nomeTitular'] ?? '';
    $nomeSocialTitular =  $input['nomeSocial'] ?? '';
    $emailTitular = $input['email'] ?? '';
    $cpfTitular = preg_replace('/[^0-9]/', '', $input['cpf']) ?? '';
    $telefone1Titular = preg_replace('/[^0-9]/', '', $input['telefone1']) ?? '';
    $telefone2Titular = preg_replace('/[^0-9]/', '', $input['telefone2']) ?? '';
    $numeroContrato = $input['numeroContrato'] ?? '';
    $dataNascimentoTitular = $input['dataNascimento'] ?? '';
    $cepTitular = preg_replace('/[^0-9]/', '', $input['cep']) ?? '';
    $enderecoTitular = $input['endereco'] ?? '';
    $numeroTitular = $input['numero'] ?? '';
    $complementoTitular = $input['complemento'] ?? '';
    $bairroTitular = $input['bairro'] ?? '';
    $cidadeTitular = $input['cidade'] ?? '';
    $ufTitular = $input['estado'] ?? '';
    $senhaTitular = $input['senha'] ?? '';

    // Validação básica dos campos obrigatórios
    if (
        empty($nomeTitular) ||
        empty($emailTitular) ||
        empty($cpfTitular) ||
        empty($telefone1Titular) ||
        empty($dataNascimentoTitular) ||
        empty($cepTitular) ||
        empty($enderecoTitular) ||
        empty($numeroTitular) ||
        empty($bairroTitular) ||
        empty($cidadeTitular) ||
        empty($ufTitular) ||
        empty($senhaTitular)
    ) {
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Lógica para INSERIR NOVO TITULAR E DEPENDENTES
        if (empty($idPessoa)) {

            $nomeMostrado;

            if (strlen(($nomeSocialTitular)) > 0) {
                $nomeMostrado = strtoupper($nomeSocialTitular);
            } else {
                $nomeMostrado = strtoupper($nomeTitular);
            }

            // Verificação se o CPF já está cadastrado
            $sqlVerificaCpf = "SELECT COUNT(*) FROM `pessoa` WHERE `cpf` = ?";
            $consultaVerificaCpf = $pdo->prepare($sqlVerificaCpf);
            $consultaVerificaCpf->execute([$cpfTitular]);
            $cpfExiste = $consultaVerificaCpf->fetchColumn();

            if ($cpfExiste > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'CPF do(a) titular ' . $nomeMostrado . ' já cadastrado!'
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Verificação se o e-mail já está cadastrado
            $sqlVerificaEmail = "SELECT COUNT(*) FROM `pessoa` WHERE `email` = ?";
            $consultaVerificaEmail = $pdo->prepare($sqlVerificaEmail);
            $consultaVerificaEmail->execute([$emailTitular]);
            $emailExiste = $consultaVerificaEmail->fetchColumn();

            if ($emailExiste > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'E-mail do(a) titular ' . $nomeMostrado . ' já cadastrado!'
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }

            $senhaHashed = password_hash($senhaTitular, PASSWORD_BCRYPT);

            //Salvar titular
            $sql = "INSERT INTO pessoa (
                nome,
                nomeSocial,
                cpf,
                email,
                senha,
                dataNascimento,
                cep,
                endereco,
                numero,
                complemento,
                bairro,
                cidade,
                estado,
                telefone1,
                telefone2,
                numeroContrato
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";

            $consultaPreparada = $pdo->prepare($sql);

            // Vinculando os parâmetros diretamente na execução
            $consultaPreparada->execute([
                $nomeTitular,
                $nomeSocialTitular,
                $cpfTitular,
                $emailTitular,
                $senhaHashed,
                $dataNascimentoTitular,
                $cepTitular,
                $enderecoTitular,
                $numeroTitular,
                $complementoTitular,
                $bairroTitular,
                $cidadeTitular,
                $ufTitular,
                $telefone1Titular,
                $telefone2Titular,
                $numeroContrato
            ]);

            $idTitular = $pdo->lastInsertId();

            //Salva o titular na pasta titular
            $sql = "INSERT INTO titular (
            fkIdPessoa
            ) VALUES (
            ?
            )";

            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([
                $idTitular
            ]);

            $idTitular = $pdo->lastInsertId();

            //Criar pastas básicas
            $pastas = [
                ['titulo' => 'Imagens', 'tipo' => 1, 'descricao' => 'Pasta para armazenar imagens'],
                ['titulo' => 'Documentos', 'tipo' => 2, 'descricao' => 'Pasta para armazenar documentos'],
                ['titulo' => 'Senhas', 'tipo' => 3, 'descricao' => 'Pasta para armazenar senhas'],
                ['titulo' => 'Áudios', 'tipo' => 4, 'descricao' => 'Pasta para armazenar áudios'],
                ['titulo' => 'Textos', 'tipo' => 5, 'descricao' => 'Pasta para armazenar textos'],
                ['titulo' => 'Vídeos', 'tipo' => 6, 'descricao' => 'Pasta para armazenar vídeos'],
            ];

            $sql = "INSERT INTO pasta (
            titulo,
            tipo,
            descricao,
            fkIdTitular
            ) VALUES (
            ?, ?, ?, ?
            )";

            $consultaPreparada = $pdo->prepare($sql);

            foreach ($pastas as $pasta) {
                $consultaPreparada->execute([
                    $pasta['titulo'],
                    $pasta['tipo'],
                    $pasta['descricao'],
                    $idTitular
                ]);
            }

            //Loop de salvar dependentes
            if ($idTitular > 0) {
                // Itera sobre o array de dependentes (se existir)
                if (!empty($input['dependentes']) && is_array($input['dependentes'])) {
                    foreach ($input['dependentes'] as $dependente) {
                        $nomeDependente = $dependente['nome'] ?? '';
                        $nomeSocialDependente = $dependente['nomeSocial'] ?? '';
                        $emailDependente = $dependente['email'] ?? '';
                        $cpfDependente = preg_replace('/[^0-9]/', '', $dependente['cpf']) ?? '';
                        $telefone1Dependente = preg_replace('/[^0-9]/', '', $dependente['telefone1']) ?? '';
                        $telefone2Dependente = preg_replace('/[^0-9]/', '', $dependente['telefone2']) ?? '';
                        $dataNascimentoDependente = $dependente['dataNascimento'] ?? '';
                        $cepDependente = preg_replace('/[^0-9]/', '', $dependente['cep']) ?? '';
                        $enderecoDependente = $dependente['endereco'] ?? '';
                        $numeroDependente = $dependente['numero'] ?? '';
                        $complementoDependente = $dependente['complemento'] ?? '';
                        $bairroDependente = $dependente['bairro'] ?? '';
                        $cidadeDependente = $dependente['cidade'] ?? '';
                        $ufDependente = $dependente['estado'] ?? '';

                        if (strlen(($nomeSocialDependente)) > 0) {
                            $nomeMostrado = strtoupper($nomeSocialDependente);
                        } else {
                            $nomeMostrado = strtoupper($nomeDependente);
                        }

                        // Verificação se o CPF já está cadastrado
                        $sqlVerificaCpf = "SELECT COUNT(*) FROM `pessoa` WHERE `cpf` = ?";
                        $consultaVerificaCpf = $pdo->prepare($sqlVerificaCpf);
                        $consultaVerificaCpf->execute([$cpfDependente]);
                        $cpfExiste = $consultaVerificaCpf->fetchColumn();

                        if ($cpfExiste > 0) {
                            echo json_encode([
                                'status' => 'error',
                                'message' => 'CPF do(a) dependente ' . $nomeMostrado . ' já cadastrado!'
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                            exit;
                        }

                        // Verificação se o e-mail já está cadastrado
                        $sqlVerificaEmail = "SELECT COUNT(*) FROM `pessoa` WHERE `email` = ?";
                        $consultaVerificaEmail = $pdo->prepare($sqlVerificaEmail);
                        $consultaVerificaEmail->execute([$emailDependente]);
                        $emailExiste = $consultaVerificaEmail->fetchColumn();

                        if ($emailExiste > 0) {
                            echo json_encode([
                                'status' => 'error',
                                'message' => 'E-mail do(a) dependente ' . $nomeMostrado . ' já cadastrado!'
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                            exit;
                        }

                        //Salva os dependentes
                        $sql = "INSERT INTO pessoa (
                    nome,
                    nomeSocial,
                    cpf,
                    email,
                    dataNascimento,
                    cep,
                    endereco,
                    numero,
                    complemento,
                    bairro,
                    cidade,
                    estado,
                    telefone1,
                    telefone2
                    ) VALUES (
                    ?,?,?,?,?,?,?,?,?,?,?,?,?,?
                    )";

                        $consultaPreparada = $pdo->prepare($sql);
                        $consultaPreparada->execute([
                            $nomeDependente,
                            $nomeSocialDependente,
                            $cpfDependente,
                            $emailDependente,
                            $dataNascimentoDependente,
                            $cepDependente,
                            $enderecoDependente,
                            $numeroDependente,
                            $complementoDependente,
                            $bairroDependente,
                            $cidadeDependente,
                            $ufDependente,
                            $telefone1Dependente,
                            $telefone2Dependente,
                        ]);

                        $idPessoa = $pdo->lastInsertId();


                        if ($idPessoa > 0) {

                            //Se o parentesco for 'Outro',
                            //armazene o parentesco diferente informado pelo usuário
                            $relacaoDependente = $dependente['relacionamento'] ?? '';
                            $outraRelacaoDependente = $dependente['outroRelacionamento'] ?? '';

                            if ($relacaoDependente == "Outros") {
                                $relacaoDependente = $outraRelacaoDependente;
                            }

                            //Insere o dependente na tabela de dependente
                            $sql = "INSERT INTO dependente (
                            parentesco,
                            fkIdPessoa
                            ) VALUES (
                            ?, ?
                            )";

                            $consultaPreparada = $pdo->prepare($sql);

                            $consultaPreparada->execute([
                                $relacaoDependente,
                                $idPessoa
                            ]);

                            $idDependente = $pdo->lastInsertId();

                            if ($idDependente > 0) {

                                //Insere o dependente e o titular na tabela titularDependente
                                $sql = "INSERT INTO titularDependente (
                                fkIdTitular,
                                fkIdDependente
                                ) VALUES (
                                ?, ?
                                )";

                                $consultaPreparada = $pdo->prepare($sql);

                                $consultaPreparada->execute([
                                    $idTitular,
                                    $idDependente
                                ]);
                            }
                        }
                    }
                }

                $pdo->commit();

                // Retorna uma resposta para o Vue.js
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Formulário recebido com sucesso!',
                    'data' => $input
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Falha ao inserir os dados do titular.');
            }
        } else {
            // Verificar se o e-mail foi alterado
            $sqlVerificaEmailAtual = "SELECT email FROM pessoa WHERE idPessoa = ?";
            $consultaVerificaEmailAtual = $pdo->prepare($sqlVerificaEmailAtual);
            $consultaVerificaEmailAtual->execute([$idPessoa]);
            $emailAtual = $consultaVerificaEmailAtual->fetchColumn();

            // Se o e-mail foi alterado, verificar se o novo e-mail já está em uso
            if ($emailTitular != $emailAtual) {
                $sqlVerificaEmailNovo = "SELECT COUNT(*) FROM pessoa WHERE email = ?";
                $consultaVerificaEmailNovo = $pdo->prepare($sqlVerificaEmailNovo);
                $consultaVerificaEmailNovo->execute([$emailTitular]);
                $emailExiste = $consultaVerificaEmailNovo->fetchColumn();

                if ($emailExiste > 0) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'E-mail já cadastrado para outro titular!'
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            // Atualizar dados do titular
            $senhaHashed = password_hash($senhaTitular, PASSWORD_BCRYPT);

            $sql = "UPDATE pessoa SET nome = ?, nomeSocial = ?, email = ?, senha = ?, 
                cep = ?, endereco = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, 
                telefone1 = ?, telefone2 = ?, numeroContrato = ? WHERE idPessoa = ?";

            $consultaPreparada = $pdo->prepare($sql);
            $consultaPreparada->execute([
                $nomeTitular,
                $nomeSocialTitular,
                $emailTitular,
                $senhaHashed,
                $cepTitular,
                $enderecoTitular,
                $numeroTitular,
                $complementoTitular,
                $bairroTitular,
                $cidadeTitular,
                $ufTitular,
                $telefone1Titular,
                $telefone2Titular,
                $numeroContrato,
                $idPessoa
            ]);

            // Atualizar dependentes
            $dependentesAtuais = $pdo->prepare("
                SELECT dependente.idDependente, pessoaDependente.idPessoa 
                FROM pessoa
                INNER JOIN titular ON titular.fkIdPessoa = pessoa.idPessoa
                INNER JOIN titularDependente ON titularDependente.fkIdTitular = titular.idTitular
                INNER JOIN dependente ON dependente.idDependente = titularDependente.fkIdDependente
                INNER JOIN pessoa AS pessoaDependente ON pessoaDependente.idPessoa = dependente.fkIdPessoa
                WHERE pessoa.idPessoa = ?
            ");

            $dependentesAtuais->execute([$idPessoa]);
            $dependentesBanco = $dependentesAtuais->fetchAll(PDO::FETCH_ASSOC);

            // Mapear dependentes do banco por ID para controle
            $idsDependentesBanco = array_column($dependentesBanco, 'idPessoa');

            foreach ($input['dependentes'] as $dependente) {
                $idDependentePessoa = $dependente['idPessoa'] ?? null;
                $nomeDependente = $dependente['nome'] ?? '';
                $nomeSocialDependente = $dependente['nomeSocial'] ?? '';
                $cpfDependente = preg_replace('/[^0-9]/', '', $dependente['cpf']) ?? '';
                $emailDependente = $dependente['email'] ?? '';
                $telefone1Dependente = preg_replace('/[^0-9]/', '', $dependente['telefone1']) ?? '';
                $telefone2Dependente = preg_replace('/[^0-9]/', '', $dependente['telefone2']) ?? '';
                $dataNascimentoDependente = $dependente['dataNascimento'] ?? '';
                $cepDependente = preg_replace('/[^0-9]/', '', $dependente['cep']) ?? '';
                $enderecoDependente = $dependente['endereco'] ?? '';
                $numeroDependente = $dependente['numero'] ?? '';
                $complementoDependente = $dependente['complemento'] ?? '';
                $bairroDependente = $dependente['bairro'] ?? '';
                $cidadeDependente = $dependente['cidade'] ?? '';
                $ufDependente = $dependente['estado'] ?? '';
                $relacaoDependente = $dependente['relacionamento'] ?? '';
                $outraRelacaoDependente = $dependente['outroRelacionamento'] ?? '';

                if ($relacaoDependente == "Outros") {
                    $relacaoDependente = $outraRelacaoDependente;
                }

                // Verificar se o CPF ou o e-mail foram alterados
                if (in_array($idDependentePessoa, $idsDependentesBanco)) {
                    // Dependente já existe, verificar CPF e e-mail
                    $sqlVerificaCpfAtual = "SELECT cpf FROM pessoa WHERE idPessoa = ?";
                    $consultaVerificaCpfAtual = $pdo->prepare($sqlVerificaCpfAtual);
                    $consultaVerificaCpfAtual->execute([$idDependentePessoa]);
                    $cpfAtual = $consultaVerificaCpfAtual->fetchColumn();

                    // Verificar se o CPF foi alterado
                    if ($cpfDependente != $cpfAtual) {
                        // Verificar se o CPF já está em uso por outro usuário
                        $sqlVerificaCpfNovo = "SELECT COUNT(*) FROM pessoa WHERE cpf = ?";
                        $consultaVerificaCpfNovo = $pdo->prepare($sqlVerificaCpfNovo);
                        $consultaVerificaCpfNovo->execute([$cpfDependente]);
                        $cpfExiste = $consultaVerificaCpfNovo->fetchColumn();

                        if ($cpfExiste > 0) {
                            echo json_encode([
                                'status' => 'error',
                                'message' => 'CPF já cadastrado para outra pessoa!'
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                    }

                    // Verificar se o e-mail foi alterado
                    $sqlVerificaEmailAtual = "SELECT email FROM pessoa WHERE idPessoa = ?";
                    $consultaVerificaEmailAtual = $pdo->prepare($sqlVerificaEmailAtual);
                    $consultaVerificaEmailAtual->execute([$idDependentePessoa]);
                    $emailAtual = $consultaVerificaEmailAtual->fetchColumn();

                    if ($emailDependente != $emailAtual) {
                        // Verificar se o e-mail já está em uso por outro usuário
                        $sqlVerificaEmailNovo = "SELECT COUNT(*) FROM pessoa WHERE email = ?";
                        $consultaVerificaEmailNovo = $pdo->prepare($sqlVerificaEmailNovo);
                        $consultaVerificaEmailNovo->execute([$emailDependente]);
                        $emailExiste = $consultaVerificaEmailNovo->fetchColumn();

                        if ($emailExiste > 0) {
                            echo json_encode([
                                'status' => 'error',
                                'message' => 'E-mail já cadastrado para outra pessoa!'
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                    }

                    // Atualizar dependente no banco
                    $sql = "UPDATE pessoa SET nome = ?, nomeSocial = ?, cpf = ?, email = ?, dataNascimento = ?, cep = ?, endereco = ?, 
                            numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, telefone1 = ?, telefone2 = ?
                            WHERE idPessoa = ?";
                    $consultaPreparada = $pdo->prepare($sql);
                    $consultaPreparada->execute([
                        $nomeDependente,
                        $nomeSocialDependente,
                        $cpfDependente,
                        $emailDependente,
                        $dataNascimentoDependente,
                        $cepDependente,
                        $enderecoDependente,
                        $numeroDependente,
                        $complementoDependente,
                        $bairroDependente,
                        $cidadeDependente,
                        $ufDependente,
                        $telefone1Dependente,
                        $telefone2Dependente,
                        $idDependentePessoa
                    ]);

                    // Atualizar parentesco na tabela dependente
                    $sql = "UPDATE dependente SET parentesco = ? WHERE fkIdPessoa = ?";
                    $consultaPreparada = $pdo->prepare($sql);
                    $consultaPreparada->execute([$relacaoDependente, $idDependentePessoa]);

                    // Remover ID atualizado da lista para controle
                    $idsDependentesBanco = array_diff($idsDependentesBanco, [$idDependentePessoa]);
                } else {
                    // Verificar se o CPF já está cadastrado para outra pessoa
                    $sqlVerificaCpfNovo = "SELECT COUNT(*) FROM pessoa WHERE cpf = ?";
                    $consultaVerificaCpfNovo = $pdo->prepare($sqlVerificaCpfNovo);
                    $consultaVerificaCpfNovo->execute([$cpfDependente]);
                    $cpfExiste = $consultaVerificaCpfNovo->fetchColumn();

                    if ($cpfExiste > 0) {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'CPF já cadastrado para outra pessoa!'
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        exit;
                    }

                    // Verificar se o e-mail já está cadastrado para outra pessoa
                    $sqlVerificaEmailNovo = "SELECT COUNT(*) FROM pessoa WHERE email = ?";
                    $consultaVerificaEmailNovo = $pdo->prepare($sqlVerificaEmailNovo);
                    $consultaVerificaEmailNovo->execute([$emailDependente]);
                    $emailExiste = $consultaVerificaEmailNovo->fetchColumn();

                    if ($emailExiste > 0) {
                        echo json_encode([
                            'status' => 'error',
                            'message' => 'E-mail já cadastrado para outra pessoa!'
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        exit;
                    }

                    // Inserir novo dependente no banco
                    $sql = "INSERT INTO pessoa (nome, nomeSocial, cpf, email, dataNascimento, cep, endereco, numero,
                            complemento, bairro, cidade, estado, telefone1, telefone2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $consultaPreparada = $pdo->prepare($sql);
                    $consultaPreparada->execute([
                        $nomeDependente,
                        $nomeSocialDependente,
                        $cpfDependente,
                        $emailDependente,
                        $dataNascimentoDependente,
                        $cepDependente,
                        $enderecoDependente,
                        $numeroDependente,
                        $complementoDependente,
                        $bairroDependente,
                        $cidadeDependente,
                        $ufDependente,
                        $telefone1Dependente,
                        $telefone2Dependente
                    ]);

                    $idNovoDependentePessoa = $pdo->lastInsertId();

                    $sql = "INSERT INTO dependente (parentesco, fkIdPessoa) VALUES (?, ?)";
                    $consultaPreparada = $pdo->prepare($sql);
                    $consultaPreparada->execute([$relacaoDependente, $idNovoDependentePessoa]);

                    $idDependente = $pdo->lastInsertId();

                    $sql = "SELECT idTitular FROM titular WHERE fkIdPessoa = ?";
                    $consultaPreparada = $pdo->prepare($sql);
                    $consultaPreparada->execute([$idPessoa]);

                    $resultado = $consultaPreparada->fetch(PDO::FETCH_ASSOC);

                    $idTitular = $resultado['idTitular'];

                    $sql = "INSERT INTO titularDependente (fkIdTitular, fkIdDependente) VALUES (?, ?)";
                    $consultaPreparada = $pdo->prepare($sql);
                    $consultaPreparada->execute([$idTitular, $idDependente]);
                }
            }

            // Excluir dependentes que não estão no input (restaram em $idsDependentesBanco)
            foreach ($idsDependentesBanco as $idExcluido) {
                // Remover da titularDependente
                $sql = "DELETE FROM titularDependente WHERE fkIdDependente = (SELECT idDependente FROM dependente WHERE fkIdPessoa = ?)";
                $consultaPreparada = $pdo->prepare($sql);
                $consultaPreparada->execute([$idExcluido]);

                // Remover da dependente
                $sql = "DELETE FROM dependente WHERE fkIdPessoa = ?";
                $consultaPreparada = $pdo->prepare($sql);
                $consultaPreparada->execute([$idExcluido]);

                // Remover da pessoa
                $sql = "DELETE FROM pessoa WHERE idPessoa = ?";
                $consultaPreparada = $pdo->prepare($sql);
                $consultaPreparada->execute([$idExcluido]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Dados atualizados com sucesso!', 'data' => $input], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();

        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar dados: ' . $e->getMessage()],  JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['acao']) && $_GET['acao'] == 'admin') {

    $nome = $input['nome'] ?? '';
    $nomeSocial = $input['nomeSocial'] ?? '';
    $cpf = preg_replace('/[^0-9]/', '', $input['cpf']) ?? '';
    $telefone = preg_replace('/[^0-9]/', '', $input['telefone']) ?? '';
    $email = $input['email'] ?? '';
    $senha = $input['senha'] ?? '';

    if (empty($nome) || empty($cpf) || empty($telefone) || empty($email) || empty($senha)) {
        exit;
    }

    try {
        // Verificação se o CPF já está cadastrado
        $sqlVerificaCpf = "SELECT COUNT(*) FROM `admin` WHERE `cpf` = ?";
        $consultaVerificaCpf = $pdo->prepare($sqlVerificaCpf);
        $consultaVerificaCpf->execute([$cpf]);
        $cpfExiste = $consultaVerificaCpf->fetchColumn();

        if ($cpfExiste > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'CPF já cadastrado!'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Verificação se o e-mail já está cadastrado
        $sqlVerificaEmail = "SELECT COUNT(*) FROM `admin` WHERE `email` = ?";
        $consultaVerificaEmail = $pdo->prepare($sqlVerificaEmail);
        $consultaVerificaEmail->execute([$email]);
        $emailExiste = $consultaVerificaEmail->fetchColumn();

        if ($emailExiste > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'E-mail já cadastrado!'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Início da transação
        $pdo->beginTransaction();

        $senhaHashed = password_hash($senha, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(25));

        $sql = "INSERT INTO `admin` (
            nome,
            nomeSocial,
            senha,
            cpf,
            email,
            telefone,
            tokenCadastro
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $consultaPreparada = $pdo->prepare($sql);
        $consultaPreparada->execute([
            $nome,
            $nomeSocial,
            $senhaHashed,
            $cpf,
            $email,
            $telefone,
            $token
        ]);

        $pdo->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'Solicitação de cadastro enviada!',
            'token' => $token
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        $pdo->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar dados: ' . $e->getMessage()]);
    }
}
