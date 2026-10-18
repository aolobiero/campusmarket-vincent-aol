<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'Method not allowed.']);
}
if (!isset($_SESSION['auth_csrf'], $_SESSION['google_nonce'])) {
    respond(419, ['error' => 'Your sign-in session expired. Reload the page and try again.']);
}

$clientId = getenv('GOOGLE_CLIENT_ID') ?: '';
if ($clientId === '') {
    respond(503, ['error' => 'Google sign-in is not configured on this server.']);
}
if (!$pdo instanceof PDO || !function_exists('curl_init')) {
    respond(503, ['error' => 'Authentication is temporarily unavailable.']);
}

$payload = json_decode(file_get_contents('php://input'), true);
$credential = is_array($payload) ? ($payload['credential'] ?? '') : '';
if (!is_string($credential) || $credential === '' || strlen($credential) > 10000) {
    respond(400, ['error' => 'A valid Google credential is required.']);
}
if (!is_string($payload['csrfToken'] ?? null) || !hash_equals($_SESSION['auth_csrf'], $payload['csrfToken'])) {
    respond(419, ['error' => 'Your sign-in session expired. Reload the page and try again.']);
}

$verificationUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($credential);
$curl = curl_init($verificationUrl);
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_CONNECTTIMEOUT => 4,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);
$response = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_errno($curl);
curl_close($curl);

if ($response === false || $curlError !== 0 || $status !== 200) {
    respond(401, ['error' => 'Google could not verify this sign-in. Please try again.']);
}

$claims = json_decode($response, true);
$issuer = is_array($claims) ? ($claims['iss'] ?? '') : '';
$validIssuer = in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true);
$validAudience = is_array($claims) && hash_equals($clientId, (string) ($claims['aud'] ?? ''));
$validExpiry = is_array($claims) && isset($claims['exp']) && (int) $claims['exp'] > time();
$verifiedEmail = is_array($claims) && filter_var($claims['email'] ?? '', FILTER_VALIDATE_EMAIL)
    && in_array($claims['email_verified'] ?? false, [true, 'true', 1, '1'], true);
$subject = is_array($claims) ? (string) ($claims['sub'] ?? '') : '';
$validNonce = is_array($claims) && is_string($claims['nonce'] ?? null)
    && hash_equals($_SESSION['google_nonce'], $claims['nonce']);

if (!$validIssuer || !$validAudience || !$validExpiry || !$verifiedEmail || !$validNonce || $subject === '') {
    respond(401, ['error' => 'The Google credential is invalid or expired.']);
}

$name = trim((string) ($claims['name'] ?? ''));
if ($name === '') {
    $name = (string) $claims['email'];
}
$name = function_exists('mb_substr') ? mb_substr($name, 0, 160) : substr($name, 0, 160);

try {
    $query = $pdo->prepare("INSERT INTO users (google_sub, email, display_name, auth_provider) VALUES (:google_sub, :email, :display_name, 'google') ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), email = VALUES(email), display_name = VALUES(display_name), auth_provider = 'google'");
    $query->execute([
        'google_sub' => $subject,
        'email' => (string) $claims['email'],
        'display_name' => $name,
    ]);
    $userId = (int) $pdo->lastInsertId();
} catch (PDOException $exception) {
    error_log('Google sign-in account persistence failed: ' . $exception->getMessage());
    respond(503, ['error' => 'Your account could not be saved. Please try again.']);
}

session_regenerate_id(true);
unset($_SESSION['auth_csrf'], $_SESSION['google_nonce']);
$_SESSION['auth_user'] = ['id' => $userId, 'name' => $name, 'email' => (string) $claims['email'], 'provider' => 'google'];

$next = ($payload['next'] ?? '') === 'sell' ? 'sell' : 'account';
respond(200, ['ok' => true, 'redirect' => $next === 'sell' ? 'sell.php' : 'index.php']);
