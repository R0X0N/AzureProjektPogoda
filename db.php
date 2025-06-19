<?php
$host = 'pogoda.database.windows.net';   // pogoda.postgres.database.azure.com
$db   = 'BazaPogodowa';   // WeatherDB
$user = 'Pawel@pogoda';   // np. admin@pogoda
$pass = 'Admin123';   // twoje hasło

try {
    // Używamy poprawnych zmiennych zdefiniowanych powyżej
    $dsn = "sqlsrv:server=$host;Database=$db";
    $pdo = new PDO($dsn, $user, $pass);

    // Ustawienie atrybutów PDO jest dobrą praktyką
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Połączenie OK!";

} catch (PDOException $e) {
    // Wypisanie błędu
    echo "Błąd połączenia: " . $e->getMessage();
}
?>
