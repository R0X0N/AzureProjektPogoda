<?php
$host = 'pogoda.database.windows.net';   // pogoda.postgres.database.azure.com
$db   = 'BazaPogodowa';   // WeatherDB
$user = 'Pawel';   // np. admin@pogoda
$pass = 'Admin123';   // twoje hasło

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db;sslmode=require";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Połączenie udane!";
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
