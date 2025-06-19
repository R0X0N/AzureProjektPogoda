<?php
// Ta funkcja musi być zdefiniowana globalnie, zanim zostanie użyta
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
    // Bezpieczne porównanie (małe litery)
    $normalizedDescription = strtolower(trim($weatherDescription));
    // Jeśli opis pogody jest znany, zwróć odpowiednią nazwę pliku ikony, w przeciwnym razie zwróć domyślną ikonę
    return 'icons/' . (isset($weatherIcons[$normalizedDescription]) ? $weatherIcons[$normalizedDescription] : 'default');
}

include 'db.php'; // Dołączamy bazę danych po definicji funkcji
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
            <!-- Usunięto niepotrzebne ukryte pole -->
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

        // ### GŁÓWNA POPRAWKA BŁĘDU ###
        // Zamieniono 'ILIKE' na 'LOWER(l.nazwa_miejscowosci) LIKE'
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

                // --- Sekcja średnich wartości (zoptymalizowana) ---
                // Pobieramy nazwę województwa z pierwszego wyniku
                $wojewodztwo = $results[0]['wojewodztwo'];

                // To zapytanie jest OK, ale upewnijmy się, że działa poprawnie
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
                // --- Koniec sekcji średnich wartości ---


                // --- Sekcja szczegółowych danych ---
                echo '<div class="data-container">';
                foreach ($results as $row) {
                    // Używamy zdefiniowanej wcześniej funkcji
                    $weatherIconPath = getWeatherIcon($row['opis']);

                    echo '<div class="tile">';
                    // Używamy htmlspecialchars() do zabezpieczenia wszystkich danych wyjściowych
                    echo '<p><strong>Miejscowość:</strong> ' . htmlspecialchars($row['nazwa_miejscowosci']) . '</p>';
                    echo '<p><strong>Czas:</strong> ' . htmlspecialchars($row['czas']) . '</p>';
                    echo '<div class="weather-icon-container">';
                    echo '<img src="' . htmlspecialchars($weatherIconPath) . '.png" alt="' . htmlspecialchars($row['opis']) . '" class="weather-icon">';
                    echo '<span class="weather-description">' . htmlspecialchars($row['opis']) . '</span>';
                    echo '</div>';
                    echo '<p><strong>Temperatura:</strong> ' . htmlspecialchars($row['temperatura']) . ' °C</p>';
                    echo '<p><strong>Wilgotność:</strong> ' . htmlspecialchars($row['wilgotnosc']) . ' %</p>';
                    echo '<p><strong>Prędkość wiatru:</strong> ' . htmlspecialchars($row['predkosc_wiatru']) . ' km/h</p>';
                    // Sprawdzamy, czy jest alert do wyświetlenia
                    if (!empty($row['alert']) && strtolower($row['alert']) !== 'brak') {
                       echo '<p class="alert"><strong>Alert:</strong> ' . htmlspecialchars($row['alert']) . '</p>';
                    }
                    echo '</div>';
                }
                echo '</div>';
                // --- Koniec sekcji szczegółowych danych ---

                echo '</div>'; // Zamyka div.container
            } else {
                echo '<p class="no-results">Brak wyników dla zapytania: "' . htmlspecialchars($search_query) . '".</p>';
            }
        } catch (PDOException $e) {
            // Unikaj wyświetlania szczegółów błędu na produkcji. Loguj je.
            error_log('Query failed: ' . $e->getMessage());
            echo '<p class="error">Wystąpił błąd podczas wyszukiwania. Prosimy spróbować później.</p>';
        }
    } else {
        // Zmieniono komunikat na bardziej zachęcający
        echo '<p class="info-message">Wpisz nazwę miejscowości w polu wyszukiwania, aby zobaczyć aktualne dane pogodowe.</p>';
    }
    ?>
</div>
</body>
</html>
