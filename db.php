<?php
// Dane do połączenia z bazą danych
$host = 'localhost';
$db = 'WeatherDB';
$user = 'postgres';
$pass = '123';


try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>

