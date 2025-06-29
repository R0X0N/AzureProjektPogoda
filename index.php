<?php
include 'db.php'; // Dołączamy bazę danych

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Informacje pogodowe</title>
    <link rel="stylesheet" type="text/css" href="style-index.css">
</head>
<body>
<header>
    <div class="header-container">
        <h1>Informacje pogodowe</h1>
        <form method="GET" action="index.php" class="search-form">
            <input type="text" name="search_query" class="search-box" placeholder="Szukaj lokalizacji"
                   value="<?= isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : '' ?>">
            <input type="submit" value="Szukaj" class="search-button">
        </form>
    </div>
</header>

<div class="content">
    <?php
    if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
        $search_query = trim($_GET['search_query']);

        $query = 'SELECT i.informacje_id, l.nazwa_miejscowosci, w.nazwa AS wojewodztwo, i.czas, i.temperatura, i.wilgotnosc, i.predkosc_wiatru, op.opis, a.alert
                  FROM informacje i
                  JOIN lokalizacja l ON i.lokalizacja_id = l.lokalizacja_id
                  JOIN wojewodztwa w ON l.wojewodztwo_id = w.wojewodztwo_id
                  JOIN opis_pogody op ON i.opis_pogody_id = op.opis_pogody_id
                  JOIN alerty a ON i.alert_id = a.alert_id
                  WHERE LOWER(l.nazwa_miejscowosci) LIKE LOWER(:search_query)
                  ORDER BY i.czas DESC';

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute(['search_query' => '%' . $search_query . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) > 0) {
                echo '<div class="container">';

                $wojewodztwo = $results[0]['wojewodztwo'];

                $avgQuery = 'SELECT AVG(temperatura) AS avg_temperatura, AVG(wilgotnosc) AS avg_wilgotnosc, AVG(predkosc_wiatru) AS avg_predkosc_wiatru
                             FROM informacje i
                             JOIN lokalizacja l ON i.lokalizacja_id = l.lokalizacja_id
                             JOIN wojewodztwa w ON l.wojewodztwo_id = w.wojewodztwo_id
                             WHERE w.nazwa = :wojewodztwo';
                $avgStmt = $pdo->prepare($avgQuery);
                $avgStmt->execute(['wojewodztwo' => $wojewodztwo]);
                $avgResult = $avgStmt->fetch(PDO::FETCH_ASSOC);

                echo '<div class="average-container">';
                if ($avgResult && $avgResult['avg_temperatura'] !== null) {
                    echo '<h3>Średnie wartości dla województwa ' . htmlspecialchars($wojewodztwo) . ':</h3>';
                    echo '<p>Średnia temperatura: ' . number_format($avgResult['avg_temperatura'], 2) . ' °C</p>';
                    echo '<p>Średnia wilgotność: ' . number_format($avgResult['avg_wilgotnosc'], 2) . ' %</p>';
                    echo '<p>Średnia prędkość wiatru: ' . number_format($avgResult['avg_predkosc_wiatru'], 2) . ' km/h</p>';
                } else {
                    echo '<p>Brak danych do obliczenia średnich wartości dla tego województwa.</p>';
                }
                echo '</div>';

                echo '<div class="data-container">';
                foreach ($results as $row) {
                    echo '<div class="tile">';
                    echo '<p><strong>Miejscowość:</strong> ' . htmlspecialchars($row['nazwa_miejscowosci']) . '</p>';
                    echo '<p><strong>Czas:</strong> ' . htmlspecialchars($row['czas']) . '</p>';
                    echo '<p><strong>Opis pogody:</strong> ' . htmlspecialchars($row['opis']) . '</p>';
                    echo '<p><strong>Temperatura:</strong> ' . htmlspecialchars($row['temperatura']) . ' °C</p>';
                    echo '<p><strong>Wilgotność:</strong> ' . htmlspecialchars($row['wilgotnosc']) . ' %</p>';
                    echo '<p><strong>Prędkość wiatru:</strong> ' . htmlspecialchars($row['predkosc_wiatru']) . ' km/h</p>';
                    if (!empty($row['alert']) && strtolower($row['alert']) !== 'brak') {
                        echo '<p class="alert"><strong>Alert:</strong> ' . htmlspecialchars($row['alert']) . '</p>';
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            } else {
                echo '<p class="no-results">Brak wyników dla zapytania: "' . htmlspecialchars($search_query) . '".</p>';
            }
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage());
            echo '<p class="error">Wystąpił błąd podczas wyszukiwania. Prosimy spróbować później.</p>';
        }
    } else {
        echo '<p class="info-message">Wpisz nazwę miejscowości w polu wyszukiwania, aby zobaczyć aktualne dane pogodowe.</p>';
    }
    ?>
</div>
</body>
</html>
