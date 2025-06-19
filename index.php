<!DOCTYPE html>
<html>
<head>
    <title>Informacje pogodowe</title>
    <link rel="stylesheet" type="text/css" href="styles/style-index.css">
</head>
<body>
<header>
    <div class="header-container">
        <h1>Informacje pogodowe</h1>
        <?php if (!isset($_GET['manage'])): ?>
            <form method="GET" action="index.php" class="search-form">
                <input type="hidden" name="search" value="1">
                <input type="text" name="search_query" class="search-box" placeholder="Szukaj lokalizacji">
                <input type="submit" value="Szukaj" class="search-button">
            </form>
        <?php endif; ?>
    </div>
</header>

<div class="content">
    <?php
    include 'db.php';

    if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
        $search_query = $_GET['search_query'];
        $query = 'SELECT i.informacje_id, l.nazwa_miejscowosci, w.nazwa AS wojewodztwo, i.czas, i.temperatura, i.wilgotnosc, i.predkosc_wiatru, op.opis, a.alert
              FROM informacje i
              JOIN lokalizacja l ON i.lokalizacja_id = l.lokalizacja_id
              JOIN wojewodztwa w ON l.wojewodztwo_id = w.wojewodztwo_id
              JOIN opis_pogody op ON i.opis_pogody_id = op.opis_pogody_id
              JOIN alerty a ON i.alert_id = a.alert_id
              WHERE l.nazwa_miejscowosci ILIKE :search_query
              ORDER BY i.czas DESC';

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute(['search_query' => '%' . $search_query . '%']);

            if ($stmt->rowCount() > 0) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo '<div class="container">';

                // Kolumna dla średnich wartości pogodowych
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
                if ($avgResult) {
                    echo '<h3>Średnie wartości pogodowe dla województwa ' . $wojewodztwo . ':</h3>';
                    echo '<p>Średnia temperatura: ' . number_format($avgResult['avg_temperatura'], 2) . ' °C</p>';
                    echo '<p>Średnia wilgotność: ' . number_format($avgResult['avg_wilgotnosc'], 2) . ' %</p>';
                    echo '<p>Średnia prędkość wiatru: ' . number_format($avgResult['avg_predkosc_wiatru'], 2) . ' km/h</p>';
                } else {
                    echo '<p>Brak danych dla średnich wartości pogodowych.</p>';
                }
                echo '</div>';

                // Kolumna dla szczegółowych danych pogodowych
                echo '<div class="data-container">';
                foreach ($results as $row) {
                    echo '<div class="tile">';
                    echo '<p>Czas: ' . $row['czas'] . '</p>';
                    // Dodanie ikony pogody na podstawie opisu pogody
                    $weatherIcon = getWeatherIcon($row['opis']);
                    echo '<img src="weather_icons/' . $weatherIcon . '.png" alt="' . $row['opis'] . '">';
                    // Wyświetlanie pozostałych informacji
                    echo '<p>Opis pogody: ' . $row['opis'] . '</p>';
                    echo '<p>Miejscowość: ' . $row['nazwa_miejscowosci'] . '</p>';
                    echo '<p>Temperatura: ' . $row['temperatura'] . ' °C</p>';
                    echo '<p>Wilgotność: ' . $row['wilgotnosc'] . ' %</p>';
                    echo '<p>Prędkość wiatru: ' . $row['predkosc_wiatru'] . ' km/h</p>';
                    echo '<p>Alert: ' . $row['alert'] . '</p>';
                    echo '</div>';
                }
                echo '</div>';

                echo '</div>';
            } else {
                echo 'Brak wyników dla podanej lokalizacji.';
            }
        } catch (PDOException $e) {
            echo 'Query failed: ' . $e->getMessage();
        }
    } else {
        echo '<p>Wprowadź nazwę lokalizacji, aby zobaczyć dane pogodowe.</p>';
    }

    // Funkcja do pobierania ikony pogody na podstawie opisu pogody
    function getWeatherIcon($weatherDescription)
    {
        // Mapowanie opisów na nazwy plików ikon
        $weatherIcons = [
            'słonecznie' => '01',
            'lekkie zachmurzenie' => '02',
            'zachmurzenie' => '03',
            'duże zachmurzenie' => '04',
            'lekkie opady' => '05',
            'duże opady' => '06',
            'burza' => '07',
            'opady śniegu' => '08',
            'mgła' => '09',

        ];

        // Jeśli opis pogody jest znany, zwróć odpowiednią nazwę pliku ikony, w przeciwnym razie zwróć domyślną ikonę
        return isset($weatherIcons[strtolower($weatherDescription)]) ? $weatherIcons[strtolower($weatherDescription)] : 'default';
    }

    ?>

</div>
</body>
</html>
