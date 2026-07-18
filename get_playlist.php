<?php
// Ensure the browser expects JSON
header('Content-Type: application/json');

// 1. Capture and sanitize the POST data from jQuery
$book = isset($_POST['book']) ? trim($_POST['book']) : '';
$lang = isset($_POST['lang']) ? strtolower(trim($_POST['lang'])) : 'en';

if ($lang !== 'it') {
    $lang = 'en';
}

if (empty($book)) {
    $errorMessage = $lang === 'it'
        ? 'Non ho trovato un libro con quel nome. Riprova.'
        : 'I could not find a book with that name. Please try again.';

    echo json_encode(['error' => $errorMessage]);
    exit;
}


// Load the configuration array
$config = require_once 'config.php';
$apiKey = $config['openai_api_key'];

$existencePrompt_en = "You are a literary reference assistant. Determine whether the title '{$book}' refers to a real book that exists. "
    . "Answer ONLY with a valid JSON object matching this exact structure: {\"exists\": true, \"reason\": \"A brief explanation\"}";

$existencePrompt_it = "Sei un assistente di riferimento letterario. Determina se il titolo '{$book}' si riferisce a un libro reale esistente. "
    . "Rispondi SOLO con un oggetto JSON valido che rispetti esattamente questa struttura: {\"exists\": true, \"reason\": \"Una breve spiegazione\"}";

$existencePrompt = $lang === 'it' ? $existencePrompt_it : $existencePrompt_en;
$existenceData = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'user', 'content' => $existencePrompt]
    ],
    'response_format' => ['type' => 'json_object'],
    'temperature' => 0.2
];

$existenceCh = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($existenceCh, CURLOPT_RETURNTRANSFER, true);
curl_setopt($existenceCh, CURLOPT_POST, true);
curl_setopt($existenceCh, CURLOPT_POSTFIELDS, json_encode($existenceData));
curl_setopt($existenceCh, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$existenceResponse = curl_exec($existenceCh);
$existenceHttpCode = curl_getinfo($existenceCh, CURLINFO_HTTP_CODE);
curl_close($existenceCh);

if ($existenceHttpCode !== 200 || !$existenceResponse) {
    $errorMessage = $lang === 'it'
        ? 'Non ho potuto verificare il titolo del libro. Riprova più tardi.'
        : 'I could not verify the book title. Please try again later.';

    echo json_encode(['error' => $errorMessage]);
    exit;
}

$existenceDecoded = json_decode($existenceResponse, true);
$existenceContent = $existenceDecoded['choices'][0]['message']['content'] ?? '{"exists": false, "reason": "No response"}';
$existenceResult = json_decode($existenceContent, true);

if (empty($existenceResult['exists'])) {
    $errorMessage = $lang === 'it'
        ? 'Non ho trovato conferma che questo titolo corrisponda a un libro esistente. Prova con un altro titolo.'
        : 'I could not confirm that this title refers to an existing book. Please try a different title.';

    echo json_encode(['error' => $errorMessage]);
    exit;
}


// Instruct the AI to act as a curator and return ONLY a strict JSON schema
$prompt_en = "You are an expert music curator for readers. A user is reading the book '{$book}'. "
    . "Suggest 10 songs to listen to while reading this specific book that match its mood, era, atmosphere, and pacing. Do not default to generic ambient tracks. "
    . "Also provide a brief description of the book focusing on its themes and genre. "
    . "Favor instrumental, ambient, or musically thematic tracks that do not distract from reading. "
    . "Respond ONLY with a valid JSON object matching this exact structure: "
    . '{"description": "Brief description of the book", "tracks": [{"title": "Song Name", "artist": "Artist Name", "reason": "Why it fits the book"}]}' ;

// Instruct the AI to act as a curator and return ONLY a strict JSON schema
$prompt_it = "Sei un curatore musicale esperto per lettori. Un utente sta leggendo il libro '{$book}'. "
    . "Suggerisci 10 brani da ascoltare durante la lettura di questo libro specifico, in base al suo umore, periodo, atmosfera e ritmo. Non limitarti a brani ambientali generici. "
    . "Fornisci anche una breve descrizione del libro, concentrandoti sui suoi temi e sul genere. "
    . "Preferisci brani strumentali, ambientali o tematicamente adatti, che non distraggano dalla lettura. "
    . "Rispondi SOLO con un oggetto JSON valido che rispetti esattamente questa struttura: "
    . '{"description": "Breve descrizione del libro", "tracks": [{"title": "Nome del brano", "artist": "Nome dell\'artista", "reason": "Perché si adatta al libro"}]}' ;

$prompt = $lang === 'it' ? $prompt_it : $prompt_en;

$data = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ],
    'response_format' => ['type' => 'json_object'],
    'temperature' => 0.85,
    'presence_penalty' => 0.5,
];

// Initialize PHP cURL
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle API communication errors
if ($httpCode !== 200 || !$response) {
    $errorMessage = $lang === 'it'
        ? 'Impossibile generare la playlist. Controlla la configurazione dell’API backend.'
        : 'Failed to generate playlist. Please check your backend API configuration.';

    echo json_encode(['error' => $errorMessage]);
    exit;
}

// Decode the OpenAI wrapper to get directly to our requested JSON string
$decoded = json_decode($response, true);
$jsonContent = $decoded['choices'][0]['message']['content'] ?? '{"error": "Invalid AI response"}';

// Send the pure JSON tracklist back to jQuery
echo $jsonContent;