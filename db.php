<?php
// Dane do połączenia z bazą danych
$host = 'pogoda.database.windows.net';
$db = 'BazaPogodowa';
$user = 'Pawel';
$pass = 'Admin123';


try {
    $pdo = new PDO("sqlsrv:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>

