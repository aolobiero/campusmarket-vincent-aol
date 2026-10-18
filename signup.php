<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sms.php';

$next = ($_GET['next'] ?? '') === 'sell' ? 'sell' : 'account';
$googleClientId = getenv('GOOGLE_CLIENT_ID') ?: '';
$csrfToken = $_SESSION['auth_csrf'] ?? bin2hex(random_bytes(32));
$googleNonce = $_SESSION['google_nonce'] ?? bin2hex(random_bytes(32));
$_SESSION['auth_csrf'] = $csrfToken;
$_SESSION['google_nonce'] = $googleNonce;
$message = '';
$verificationSent = false;
$phoneValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO && is_string($_POST['csrf_token'] ?? null) && hash_equals($_SESSION['auth_csrf'], $_POST['csrf_token'])) {
    $action = $_POST['action'] ?? '';
    $phoneValue = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
    $phone = str_starts_with($phoneValue, '254') ? '+' . $phoneValue : '+254' . ltrim($phoneValue, '0');
    if ($action === 'request_code' && preg_match('/^(?:254|0)?[17][0-9]{8}$/', $phoneValue)) {
        $code = (string) random_int(100000, 999999);
        $query = $pdo->prepare('INSERT INTO verification_codes (phone, code_hash, expires_at) VALUES (:phone, :code_hash, DATE_ADD(NOW(), INTERVAL 10 MINUTE))');
        $query->execute(['phone' => $phone, 'code_hash' => password_hash($code, PASSWORD_DEFAULT)]);
        $_SESSION['verification_phone'] = $phone;
        $delivery = sendVerificationSms($phone, $code);
        if ($delivery['sent']) {
            $verificationSent = true;
            $message = 'A verification code was sent to ' . $phone . '.';
        } elseif (getenv('APP_ENV') === 'production') {
            $message = 'We could not send a verification code right now. Please try again shortly.';
        } else {
            $verificationSent = true;
            $message = 'Local mode: your verification code is ' . $code . '.';
        }
    } elseif ($action === 'verify_code') {
        $code = trim($_POST['code'] ?? '');
        $phone = $_SESSION['verification_phone'] ?? '';
        $query = $pdo->prepare('SELECT * FROM verification_codes WHERE phone = :phone AND verified_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
        $query->execute(['phone' => $phone]);
        $record = $query->fetch();
        if ($record && password_verify($code, $record['code_hash'])) {
            $update = $pdo->prepare('UPDATE verification_codes SET verified_at = NOW() WHERE id = :id');
            $update->execute(['id' => $record['id']]);
            $userQuery = $pdo->prepare("INSERT INTO users (phone, display_name, auth_provider) VALUES (:phone, 'CampusMarket member', 'phone') ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), auth_provider = 'phone'");
            $userQuery->execute(['phone' => $phone]);
            session_regenerate_id(true);
            $_SESSION['auth_user'] = ['id' => (int) $pdo->lastInsertId(), 'name' => 'CampusMarket member', 'provider' => 'phone'];
            $_SESSION['verified_phone'] = $phone;
            $_SESSION['auth_csrf'] = bin2hex(random_bytes(32));
            unset($_SESSION['google_nonce']);
            header('Location: ' . ($next === 'sell' ? 'sell.php' : 'index.php'));
            exit;
        }
        $verificationSent = true;
        $message = 'That code is invalid or expired.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $pdo instanceof PDO ? 'Your session expired. Reload and try again.' : 'Database is not connected. Start XAMPP MySQL first.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join CampusMarket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#142b38;--muted:#60727a;--paper:#e8f3ed;--white:#fff;--line:#dce7e3;--teal:#087f78;--teal-dark:#075b59}
        *{box-sizing:border-box}body{min-height:100vh;margin:0;display:grid;place-items:center;color:var(--ink);background:var(--paper);font-family:"DM Sans",sans-serif}.shell{width:min(430px,calc(100% - 28px));padding:34px 30px;border:1px solid var(--line);border-radius:14px;background:#fff;box-shadow:0 20px 55px rgba(20,43,56,.12)}.brand{display:flex;align-items:center;justify-content:center;gap:10px;color:var(--ink);font-family:"Space Grotesk",sans-serif;font-size:1.25rem;font-weight:700;letter-spacing:-.04em;text-decoration:none}.mark{display:grid;place-items:center;width:34px;height:34px;border-radius:9px;color:#fff;background:var(--teal)}h1{margin:28px 0 7px;text-align:center;font-family:"Space Grotesk",sans-serif;font-size:1.85rem;letter-spacing:-.045em}.lead{margin:0 0 24px;color:var(--muted);font-size:.88rem;text-align:center}.google,.primary{display:flex;align-items:center;justify-content:center;width:100%;min-height:46px;border-radius:7px;font-weight:700;cursor:pointer}.google{gap:10px;border:1px solid var(--line);color:var(--ink);background:#fff;text-decoration:none}.google-mark{font-size:1.15rem;font-weight:700}.divider{display:flex;align-items:center;gap:10px;margin:20px 0;color:#8a999c;font-size:.75rem}.divider:before,.divider:after{height:1px;flex:1;background:var(--line);content:""}.field{display:grid;gap:7px;margin-bottom:15px}.field label{font-size:.8rem;font-weight:700}.phone-row{display:grid;grid-template-columns:78px 1fr;gap:8px}.field input{width:100%;min-height:46px;padding:11px;border:1px solid #c8ded5;border-radius:7px;outline:0;color:var(--ink);font:inherit}.field input:focus{border-color:var(--teal)}.primary{border:0;color:#fff;background:var(--teal)}.primary:hover{background:var(--teal-dark)}.fine{margin:14px 0 0;color:var(--muted);font-size:.75rem;text-align:center}.fine a,.back{color:var(--teal);font-weight:700}.status{margin-top:15px;padding:10px;border-radius:6px;color:var(--teal-dark);background:#e7f3ee;font-size:.8rem;text-align:center}.back{display:block;margin-top:22px;text-align:center;font-size:.82rem}
        .google-mark{display:block;width:19px;height:19px;flex:0 0 19px}
        </style>
        <script>try{document.documentElement.classList.add(localStorage.getItem('campusmarket-theme')==='light'?'light-theme':'dark-theme')}catch(error){document.documentElement.classList.add('dark-theme')}</script>
        <link rel="stylesheet" href="assets/theme.css">
        <script src="assets/theme.js" defer></script>
        <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body><main class="shell"><a class="brand" href="index.php"><span class="mark"><img class="brand-symbol" src="assets/brand-mark.svg" alt=""></span> CampusMarket</a><h1><?= $verificationSent ? 'Verify your phone' : 'Create your account' ?></h1><p class="lead"><?= $verificationSent ? 'Enter the six-digit code sent to your phone.' : 'Sign up before you list an item on campus.' ?></p><?php if (!$verificationSent): ?>
<div id="google-button" data-client-id="<?= htmlspecialchars($googleClientId, ENT_QUOTES, 'UTF-8') ?>" data-next="<?= htmlspecialchars($next, ENT_QUOTES, 'UTF-8') ?>" data-nonce="<?= htmlspecialchars($googleNonce, ENT_QUOTES, 'UTF-8') ?>" data-csrf="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="divider"><span>or continue with phone</span></div>
<form method="post" id="phone-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="action" value="request_code">
    <div class="field">
        <label for="phone">Phone number</label>
        <div class="phone-row">
            <input value="+254" aria-label="Country code" readonly>
            <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" value="<?= htmlspecialchars($phoneValue) ?>" placeholder="712 345 678" pattern="(?:254|0)?[17][0-9]{8}" required>
        </div>
    </div>
    <button class="primary" type="submit">Send verification code</button>
</form>
<?php else: ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="verify_code"><div class="field"><label for="code">Verification code</label><input id="code" name="code" type="text" inputmode="numeric" maxlength="6" placeholder="Enter 6-digit code" required></div><button class="primary" type="submit">Verify and continue</button></form>
<?php endif; ?><?php if ($message !== ''): ?><div class="status" role="status"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<div class="status" id="auth-status" role="status" aria-live="polite" hidden></div>
<p class="fine">By continuing, you agree to our <a href="#terms">Terms</a> and <a href="#privacy">Privacy Policy</a>.</p>
<a class="back" href="index.php">&larr; Back to marketplace</a></main>
<script>
const googleButton = document.getElementById('google-button');
const authStatus = document.getElementById('auth-status');
const googleClientId = googleButton?.dataset.clientId || '';
const nextPage = googleButton?.dataset.next || 'account';
const googleNonce = googleButton?.dataset.nonce || '';
const csrfToken = googleButton?.dataset.csrf || '';

function showAuthStatus(message, isError) {
    if (!authStatus) return;
    authStatus.hidden = false;
    authStatus.textContent = message;
    authStatus.classList.toggle('status-error', Boolean(isError));
}

async function handleGoogleCredential(response) {
    showAuthStatus('Signing you in securely...', false);
    try {
        const result = await fetch('auth_google.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ credential: response.credential, next: nextPage, csrfToken: csrfToken })
        });
        const data = await result.json();
        if (!result.ok || !data.ok || !data.redirect) {
            throw new Error(data.error || 'Google sign-in failed. Please try again.');
        }
        window.location.assign(data.redirect);
    } catch (error) {
        showAuthStatus(error.message || 'Google sign-in failed. Please try again.', true);
    }
}

function initializeGoogleSignIn() {
    if (!googleButton) return;
    if (!googleClientId) {
        googleButton.innerHTML = '<button class="google" type="button" disabled><svg class="google-mark" viewBox="0 0 48 48" aria-hidden="true"><path fill="#4285F4" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11a9.4 9.4 0 0 1-4.1 6.2v5.1h6.6c3.9-3.6 6.1-8.8 6.1-15Z"/><path fill="#34A853" d="M24 44c5.5 0 10.1-1.8 13.5-4.9l-6.6-5.1c-1.8 1.2-4.1 2-6.9 2-5.3 0-9.8-3.6-11.4-8.4H5.8v5.3A20 20 0 0 0 24 44Z"/><path fill="#FBBC05" d="M12.6 27.6a12 12 0 0 1 0-7.2v-5.3H5.8a20 20 0 0 0 0 17.8l6.8-5.3Z"/><path fill="#EA4335" d="M24 12c3 0 5.7 1 7.8 3l5.9-5.9C34.1 5.8 29.5 4 24 4A20 20 0 0 0 5.8 15.1l6.8 5.3C14.2 15.6 18.7 12 24 12Z"/></svg><span>Continue with Google</span></button>';
        showAuthStatus('Google sign-in needs a configured Google OAuth web client ID.', true);
        return;
    }
    if (!window.google?.accounts?.id) {
        showAuthStatus('Google sign-in could not load. Check your connection and try again.', true);
        return;
    }
    window.google.accounts.id.initialize({ client_id: googleClientId, nonce: googleNonce, callback: handleGoogleCredential });
    window.google.accounts.id.renderButton(googleButton, {
        type: 'standard',
        theme: document.documentElement.classList.contains('dark-theme') ? 'filled_black' : 'outline',
        size: 'large',
        text: 'continue_with',
        shape: 'rectangular',
        width: Math.min(370, googleButton.parentElement.clientWidth)
    });
}

if (window.google?.accounts?.id) {
    initializeGoogleSignIn();
} else {
    window.addEventListener('load', initializeGoogleSignIn, { once: true });
}

const phoneForm = document.getElementById('phone-form');
if (phoneForm) {
    phoneForm.addEventListener('submit', function (event) {
        const phoneInput = document.getElementById('phone');
        const digits = phoneInput.value.replace(/\D/g, '');
        if (!/^(?:254|0)?[17]\d{8}$/.test(digits)) {
            event.preventDefault();
            phoneInput.setCustomValidity('Enter a valid Kenyan mobile number.');
            phoneInput.reportValidity();
            phoneInput.addEventListener('input', function clearPhoneError() {
                phoneInput.setCustomValidity('');
                phoneInput.removeEventListener('input', clearPhoneError);
            });
        }
    });
}
</script>
</body>
</html>
