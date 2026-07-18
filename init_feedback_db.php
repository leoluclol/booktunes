<?php
$db = new SQLite3(__DIR__ . '/feedback.sqlite');
$db->exec('CREATE TABLE IF NOT EXISTS feedback (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    message TEXT NOT NULL,
    lang TEXT NOT NULL,
    created_at TEXT NOT NULL
)');
echo "feedback.sqlite initialized\n";
