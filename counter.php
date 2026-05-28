<?php
/*
 * MPS Visitor Counter
 * -------------------
 * Speichert den Zählerstand in visitor_count.txt (gleicher Ordner).
 *
 * Aufruf aus dem Frontend:
 *   GET counter.php              → gibt aktuellen Stand zurück
 *   GET counter.php?action=increment → erhöht um 1 und gibt neuen Stand zurück
 *
 * Beide Aufrufe liefern JSON:  { "count": 1234 }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$file = __DIR__ . '/visitor_count.txt';

// Datei anlegen, falls sie noch nicht existiert
if (!file_exists($file)) {
    file_put_contents($file, '0');
}

// Zähler lesen
$count = (int) file_get_contents($file);

// Bei ?action=increment hochzählen (mit Dateisperre)
if (isset($_GET['action']) && $_GET['action'] === 'increment') {
    $fp = fopen($file, 'c+');
    if (flock($fp, LOCK_EX)) {
        $count = (int) stream_get_contents($fp);
        $count++;
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) $count);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

echo json_encode(['count' => $count]);
