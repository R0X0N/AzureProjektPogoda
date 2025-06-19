<!-- show.php -->
<?php
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
}

// Pobieranie lokalizacji
$lokalizacjeQuery = 'SELECT lokalizacja_id, nazwa_miejscowosci FROM lokalizacja';
$lokalizacjeStmt = $pdo->query($lokalizacjeQuery);
$lokalizacje = $lokalizacjeStmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie województw
$wojewodztwaQuery = 'SELECT wojewodztwo_id, nazwa FROM wojewodztwa';
$wojewodztwaStmt = $pdo->query($wojewodztwaQuery);
$wojewodztwa = $wojewodztwaStmt->fetchAll(PDO::FETCH_ASSOC);

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
    $informacjeQuery = 'SELECT i.*, l.nazwa_miejscowosci, w.nazwa AS wojewodztwo, op.opis, a.alert
                        FROM informacje i
                        JOIN lokalizacja l ON i.lokalizacja_id = l.lokalizacja_id
                        JOIN wojewodztwa w ON l.wojewodztwo_id = w.wojewodztwo_id
                        JOIN opis_pogody op ON i.opis_pogody_id = op.opis_pogody_id
                        JOIN alerty a ON i.alert_id = a.alert_id
                        WHERE i.lokalizacja_id = :lokalizacja_id';
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
        <div id="branding">
            <h1>Zarządzanie danymi pogodowymi</h1>
        </div>
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
                    <option value="<?= $lokalizacja['lokalizacja_id'] ?>"><?= $lokalizacja['nazwa_miejscowosci'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="wojewodztwo_id">Województwo:</label>
            <select name="wojewodztwo_id" id="wojewodztwo_id" required>
                <?php foreach ($wojewodztwa as $wojewodztwo): ?>
                    <option value="<?= $wojewodztwo['wojewodztwo_id'] ?>"><?= $wojewodztwo['nazwa'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>
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
                    <option value="<?= $opis['opis_pogody_id'] ?>"><?= $opis['opis'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="alert_id">Alert:</label>
            <select name="alert_id" id="alert_id" required>
                <?php foreach ($alerty as $alert): ?>
                    <option value="<?= $alert['alert_id'] ?>"><?= $alert['alert'] ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <button type="submit" name="add">Dodaj dane</button>
        </form>
    </div>

    <!-- Formularz wyboru lokalizacji do wyświetlenia i usunięcia danych -->
    <div class="tile">
        <h2>Usuwanie danych</h2>
        <form method="get">
            <label for="lokalizacja_id">Wybierz lokalizację:</label>
            <select name="lokalizacja_id" id="lokalizacja_id">
                <?php foreach ($lokalizacje as $lokalizacja): ?>
                    <option value="<?= $lokalizacja['lokalizacja_id'] ?>" <?= ($selected_lokalizacja_id == $lokalizacja['lokalizacja_id']) ? 'selected' : '' ?>><?= $lokalizacja['nazwa_miejscowosci'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Wyświetl dane</button>
        </form>
    </div>

    <?php if ($informacje): ?>
        <div class="tile">
            <h2>Dane pogodowe dla
                lokalizacji: <?= $lokalizacje[array_search($selected_lokalizacja_id, array_column($lokalizacje, 'lokalizacja_id'))]['nazwa_miejscowosci'] ?></h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Województwo</th>
                    <th>Lokalizacja</th>
                    <th>Czas</th>
                    <th>Temperatura (°C)</th>
                    <th>Wilgotność (%)</th>
                    <th>Prędkość wiatru (km/h)</th>
                    <th>Opis pogody</th>
                    <th>Alert</th>
                    <th>Akcje</th>
                </tr>
                <?php foreach ($informacje as $info): ?>
                    <tr>
                        <td><?= $info['informacje_id'] ?></td>
                        <td><?= $wojewodztwo['nazwa'] ?></td>
                        <td><?= $lokalizacje[array_search($info['lokalizacja_id'], array_column($lokalizacje, 'lokalizacja_id'))]['nazwa_miejscowosci'] ?></td>
                        <td><?= $info['czas'] ?></td>
                        <td><?= $info['temperatura'] ?></td>
                        <td><?= $info['wilgotnosc'] ?></td>
                        <td><?= $info['predkosc_wiatru'] ?></td>
                        <td><?= $opisyPogody[array_search($info['opis_pogody_id'], array_column($opisyPogody, 'opis_pogody_id'))]['opis'] ?></td>
                        <td><?= $alerty[array_search($info['alert_id'], array_column($alerty, 'alert_id'))]['alert'] ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="informacje_id" value="<?= $info['informacje_id'] ?>">
                                <button type="submit" name="delete">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <form method="post">
                <input type="hidden" name="lokalizacja_id" value="<?= $selected_lokalizacja_id ?>">
                <button type="submit" name="delete_all">Usuń wszystkie dane dla tej lokalizacji</button>
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
            <form method="post">
                <label for="alert_id">Alert:</label>
                <select name="alert_id" id="alert_id">
                    <?php foreach ($alerty as $alert): ?>
                        <option value="<?= $alert['alert_id'] ?>"><?= $alert['alert'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="delete_alert">Usuń alert</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
