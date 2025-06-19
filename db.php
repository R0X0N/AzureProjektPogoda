<?php
$host = 'pogoda.database.windows.net';   // pogoda.postgres.database.azure.com
$db   = 'BazaPogodowa';   // WeatherDB
$user = 'Pawel';   // np. admin@pogoda
$pass = 'Admin123';   // twoje hasło

try {
    // Dla Microsoft SQL Server używamy DSN: sqlsrv
    $dsn = "sqlsrv:server=$server;Database=$database";
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Połączenie OK!";
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
