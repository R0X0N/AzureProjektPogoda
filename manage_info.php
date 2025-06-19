<?php
// <!-- BEZ ZMIAN -->
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        // Dodawanie nowych danych pogodowych
        $lokalizacja_id = $_POST['lokalizacja_id'];
        $czas = $_POST['czas'];
        $temperatura = $_POST['temperatura'];
        $wilgotnosc = $_POST['wilgotnosc'];
        $predkosc_wiatru = $_POST['predkosc_wiatru'];
        $opis_pogody_id = $_POST['opis_pogody_id'];
        $alert_id = $_POST['alert_id'];

        $query = 'INSERT INTO informacje (lokalizacja_id, czas, temperatura, wilgotnosc, predkosc_wiatru, opis_pogody_id, alert_id) 
                  VALUES (:lokalizacja_id, :czas, :temperatura, :wilgotnosc, :predkosc_wiatru, :opis_pogody_id, :alert_id)';
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'lokalizacja_id' => $lokalizacja_id,
            'czas' => $czas,
            'temperatura' => $temperatura,
            'wilgotnosc' => $wilgotnosc,
            'predkosc_wiatru' => $predkosc_wiatru,
            'opis_pogody_id' => $opis_pogody_id,
            'alert_id' => $alert_id
        ]);
    } elseif (isset($_POST['delete'])) {
        // Usuwanie pojedynczego rekordu
        $informacje_id = $_POST['informacje_id'];
        $query = 'DELETE FROM informacje WHERE informacje_id = :informacje_id';
        $stmt = $pdo->prepare($query);
        $stmt->execute(['informacje_id' => $informacje_id]);
    } elseif (isset($_POST['delete_all'])) {
        // Usuwanie wszystkich danych dla wybranej lokalizacji
        $lokalizacja_id = $_POST['lokalizacja_id'];
        $query = 'DELETE FROM informacje WHERE lokalizacja_id = :lokalizacja_id';
        $stmt = $pdo->prepare($query);
        $stmt->execute(['lokalizacja_id' => $lokalizacja_id]);
    } elseif (isset($_POST['add_alert'])) {
        // Dodawanie nowego alertu
        $alert = $_POST['alert'];
        $query = 'INSERT INTO alerty (alert) VALUES (:alert)';
        $stmt = $pdo->prepare($query);
        $stmt->execute(['alert' => $alert]);
    } elseif (isset($_POST['delete_alert'])) {
        // Usuwanie alertu
        $alert_id = $_POST['alert_id'];
        $query = 'DELETE FROM alerty WHERE alert_id = :alert_id';
        $stmt = $pdo->prepare($query);
        $stmt->execute(['alert_id' => $alert_id]);
    }
    // <!-- POPRAWKA: Przekierowanie, aby uniknąć ponownego wysłania formularza po odświeżeniu strony -->
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// <!-- BEZ ZMIAN -->
// Pobieranie lokalizacji
$lokalizacjeQuery = 'SELECT lokalizacja_id, nazwa_miejscowosci FROM lokalizacja';
$lokalizacjeStmt = $pdo->query($lokalizacjeQuery);
$lokalizacje = $lokalizacjeStmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie opisów pogody
$opisyPogodyQuery = 'SELECT opis_pogody_id, opis FROM opis_pogody';
$opisyPogodyStmt = $pdo->query($opisyPogodyQuery);
$opisyPogody = $opisyPogodyStmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie alertów
$alertyQuery = 'SELECT alert_id, alert FROM alerty';
$alertyStmt = $pdo->query($alertyQuery);
$alerty = $alertyStmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie danych pogodowych dla wybranej lokalizacji
$selected_lokalizacja_id = isset($_GET['lokalizacja_id']) ? $_GET['lokalizacja_id'] : null;
$informacje = [];
if ($selected_lokalizacja_id) {
    // <!-- BEZ ZMIAN W ZAPYTANIU, JEST DOBRE! -->
    $informacjeQuery = 'SELECT i.*, l.nazwa_miejscowosci, w.nazwa AS wojewodztwo, op.opis AS opis_pogody, a.alert AS tresc_alertu
                        FROM informacje i
                        JOIN lokalizacja l ON i.lokalizacja_id = l.lokalizacja_id
                        JOIN wojewodztwa w ON l.wojewodztwo_id = w.wojewodztwo_id
                        JOIN opis_pogody op ON i.opis_pogody_id = op.opis_pogody_id
                        JOIN alerty a ON i.alert_id = a.alert_id
                        WHERE i.lokalizacja_id = :lokalizacja_id
                        ORDER BY i.czas DESC'; // Dodano sortowanie dla lepszego wyglądu
    $informacjeStmt = $pdo->prepare($informacjeQuery);
    $informacjeStmt->execute(['lokalizacja_id' => $selected_lokalizacja_id]);
    $informacje = $informacjeStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie danymi pogodowymi</title>
    <link rel="stylesheet" href="styles/style-info.css">
</head>
<body>
<header>
    <div class="container">
        <h1>Zarządzanie danymi pogodowymi</h1>
    </div>
</header>

<div class="container">

    <!-- Formularz dodawania danych pogodowych -->
    <div class="dodaj_dane">
        <h2>Dodaj dane pogodowe</h2>
        <form method="post">
            <label for="lokalizacja_id">Lokalizacja:</label>
            <select name="lokalizacja_id" id="lokalizacja_id" required>
                <?php foreach ($lokalizacje as $lokalizacja): ?>
                    <option value="<?= htmlspecialchars($lokalizacja['lokalizacja_id']) ?>"><?= htmlspecialchars($lokalizacja['nazwa_miejscowosci']) ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <!-- USUNIĘTO NIEPOTRZEBNE POLE WOJEWÓDZTWA, BO JEST POWIĄZANE Z LOKALIZACJĄ -->
            <label for="czas">Czas:</label>
            <input type="datetime-local" name="czas" id="czas" required>
            <br>
            <label for="temperatura">Temperatura (°C):</label>
            <input type="number" step="0.01" name="temperatura" id="temperatura" required>
            <br>
            <label for="wilgotnosc">Wilgotność (%):</label>
            <input type="number" step="0.01" name="wilgotnosc" id="wilgotnosc" required>
            <br>
            <label for="predkosc_wiatru">Prędkość wiatru (km/h):</label>
            <input type="number" step="0.01" name="predkosc_wiatru" id="predkosc_wiatru" required>
            <br>
            <label for="opis_pogody_id">Opis pogody:</label>
            <select name="opis_pogody_id" id="opis_pogody_id" required>
                <?php foreach ($opisyPogody as $opis): ?>
                    <option value="<?= htmlspecialchars($opis['opis_pogody_id']) ?>"><?= htmlspecialchars($opis['opis']) ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="alert_id">Alert:</label>
            <select name="alert_id" id="alert_id" required>
                <?php foreach ($alerty as $alert): ?>
                    <option value="<?= htmlspecialchars($alert['alert_id']) ?>"><?= htmlspecialchars($alert['alert']) ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <button type="submit" name="add">Dodaj dane</button>
        </form>
    </div>

    <!-- Formularz wyboru lokalizacji -->
    <div class="tile">
        <h2>Wyświetl lub usuń dane dla lokalizacji</h2>
        <form method="get">
            <label for="lokalizacja_id_select">Wybierz lokalizację:</label>
            <select name="lokalizacja_id" id="lokalizacja_id_select">
                <option value="">-- Wybierz --</option>
                <?php foreach ($lokalizacje as $lokalizacja): ?>
                    <option value="<?= htmlspecialchars($lokalizacja['lokalizacja_id']) ?>" <?= ($selected_lokalizacja_id == $lokalizacja['lokalizacja_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lokalizacja['nazwa_miejscowosci']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Wyświetl dane</button>
        </form>
    </div>

    <?php if ($selected_lokalizacja_id && $informacje): ?>
        <div class="tile">
            <h2>Dane pogodowe dla: <?= htmlspecialchars($informacje[0]['nazwa_miejscowosci']) ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Województwo</th>
                        <th>Lokalizacja</th>
                        <th>Czas</th>
                        <th>Temp.</th>
                        <th>Wilgotność</th>
                        <th>Wiatr</th>
                        <th>Opis pogody</th>
                        <th>Alert</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($informacje as $info): ?>
                        <tr>
                            <!-- POPRAWKA: Używamy danych pobranych bezpośrednio z zapytania z JOINami -->
                            <td><?= htmlspecialchars($info['informacje_id']) ?></td>
                            <td><?= htmlspecialchars($info['wojewodztwo']) ?></td>
                            <td><?= htmlspecialchars($info['nazwa_miejscowosci']) ?></td>
                            <td><?= htmlspecialchars($info['czas']) ?></td>
                            <td><?= htmlspecialchars($info['temperatura']) ?>°C</td>
                            <td><?= htmlspecialchars($info['wilgotnosc']) ?>%</td>
                            <td><?= htmlspecialchars($info['predkosc_wiatru']) ?> km/h</td>
                            <td><?= htmlspecialchars($info['opis_pogody']) ?></td>
                            <td><?= htmlspecialchars($info['tresc_alertu']) ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz usunąć ten wpis?');">
                                    <input type="hidden" name="informacje_id" value="<?= htmlspecialchars($info['informacje_id']) ?>">
                                    <button type="submit" name="delete">Usuń</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <form method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć WSZYSTKIE dane dla tej lokalizacji?');">
                <input type="hidden" name="lokalizacja_id" value="<?= htmlspecialchars($selected_lokalizacja_id) ?>">
                <button type="submit" name="delete_all" class="delete-all-btn">Usuń wszystkie dane dla tej lokalizacji</button>
            </form>
        </div>
    <?php elseif ($selected_lokalizacja_id): ?>
        <p>Brak danych dla wybranej lokalizacji.</p>
    <?php endif; ?>

    <!-- Zarządzanie alertami -->
    <div class="tile alert-management">
        <div>
            <h2>Dodaj alert</h2>
            <form method="post">
                <label for="alert">Alert:</label>
                <input type="text" name="alert" id="alert" required>
                <button type="submit" name="add_alert">Dodaj alert</button>
            </form>
        </div>
        <div>
            <h2>Usuń alert</h2>
            <form method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć ten alert? Usunięcie może wpłynąć na istniejące dane pogodowe.');">
                <label for="alert_id_delete">Alert:</label>
                <select name="alert_id" id="alert_id_delete">
                    <?php foreach ($alerty as $alert): ?>
                        <option value="<?= htmlspecialchars($alert['alert_id']) ?>"><?= htmlspecialchars($alert['alert']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="delete_alert">Usuń alert</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
