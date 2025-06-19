<?php
$host = getenv('DB_HOST');   // pogoda.postgres.database.azure.com
$db   = getenv('DB_NAME');   // WeatherDB
$user = getenv('DB_USER');   // np. admin@pogoda
$pass = getenv('DB_PASS');   // twoje hasło

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;sslmode=require";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Połączenie udane!";
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
