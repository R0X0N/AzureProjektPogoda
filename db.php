<?php
$host = 'pogoda.database.windows.net';   // pogoda.postgres.database.azure.com
$db   = 'BazaPogodowa';   // WeatherDB
$user = 'Pawel@pogoda';   // np. admin@pogoda
$pass = 'Admin123';   // twoje hasło

try {
    // DLA SQL SERVER - WAŻNE: sqlsrv + portu nie podajemy, używa 1433 domyślnie!
    $dsn = "sqlsrv:server=$server;Database=$database";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Połączenie OK!";
} catch (PDOException $e) {
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
