<?php
header('Content-Type: application/json');

$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';
$lang = isset($_POST['lang']) ? strtolower(trim($_POST['lang'])) : 'en';

if ($lang !== 'it') {
    $lang = 'en';
}

if ($feedback === '') {
    $message = $lang === 'it'
        ? 'Inserisci un messaggio di feedback.'
        : 'Please enter feedback.';

    echo json_encode(['error' => $message]);
    exit;
}

if (strlen($feedback) > 2000) {
    $message = $lang === 'it'
        ? 'Il feedback è troppo lungo. Resta sotto i 2000 caratteri.'
        : 'Feedback is too long. Keep it under 2000 characters.';

    echo json_encode(['error' => $message]);
    exit;
}

$dbPath = __DIR__ . '/feedback.sqlite';

try {
    $db = new SQLite3($dbPath);
    $db->exec('CREATE TABLE IF NOT EXISTS feedback (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        message TEXT NOT NULL,
        lang TEXT NOT NULL,
        created_at TEXT NOT NULL
    )');

    $statement = $db->prepare('INSERT INTO feedback (message, lang, created_at) VALUES (:message, :lang, :created_at)');
    $statement->bindValue(':message', $feedback, SQLITE3_TEXT);
    $statement->bindValue(':lang', $lang, SQLITE3_TEXT);
    $statement->bindValue(':created_at', gmdate('c'), SQLITE3_TEXT);
    $statement->execute();

    echo json_encode(['success' => true]);
} catch (Throwable $exception) {
    $message = $lang === 'it'
        ? 'Impossibile salvare il feedback.'
        : 'Could not save feedback.';

    echo json_encode(['error' => $message]);
}
