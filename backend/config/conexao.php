<?php
$host = 'db';
$dbname = 'afterlife';
$username = 'root';
$password = 'root';

try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo ("Conexão com o banco de dados bem-sucedida!");
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
}
