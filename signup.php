<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sms.php';

$next = ($_GET['next'] ?? '') === 'sell' ? 'sell' : 'account';
$message = '';
$verificationSent = false;
$phoneValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
    $action = $_POST['action'] ?? '';
    $phoneValue = preg_replace('/\D+/', '', $_POST['phone'] ?? '');
    $phone = str_starts_with($phoneValue, '254') ? '+' . $phoneValue : '+254' . ltrim($phoneValue, '0');
    if ($action === 'request_code' && strlen($phoneValue) >= 9) {
        $code = (string) random_int(100000, 999999);
        $query = $pdo->prepare('INSERT INTO verification_codes (phone, code_hash, expires_at) VALUES (:phone, :code_hash, DATE_ADD(NOW(), INTERVAL 10 MINUTE))');
        $query->execute(['phone' => $phone, 'code_hash' => password_hash($code, PASSWORD_DEFAULT)]);
        $_SESSION['verification_phone'] = $phone;
        $delivery = sendVerificationSms($phone, $code);
        $verificationSent = true;
        $message = $delivery['sent'] ? 'A verification code was sent to ' . $phone . '.' : 'Local mode: your verification code is ' . $code . '.';
    } elseif ($action === 'verify_code') {
        $code = trim($_POST['code'] ?? '');
        $phone = $_SESSION['verification_phone'] ?? '';
        $query = $pdo->prepare('SELECT * FROM verification_codes WHERE phone = :phone AND verified_at IS NULL AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
        $query->execute(['phone' => $phone]);
        $record = $query->fetch();
        if ($record && password_verify($code, $record['code_hash'])) {
            $update = $pdo->prepare('UPDATE verification_codes SET verified_at = NOW() WHERE id = :id');
            $update->execute(['id' => $record['id']]);
            $_SESSION['verified_phone'] = $phone;
            header('Location: ' . ($next === 'sell' ? 'sell.php' : 'index.php'));
            exit;
        }
        $verificationSent = true;
        $message = 'That code is invalid or expired.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = 'Database is not connected. Start XAMPP MySQL first.';
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
</head>
<body><main class="shell"><a class="brand" href="index.php"><span class="mark">M</span> CampusMarket</a><h1><?= $verificationSent ? 'Verify your phone' : 'Create your account' ?></h1><p class="lead"><?= $verificationSent ? 'Enter the six-digit code sent to your phone.' : 'Sign up before you list an item on campus.' ?></p><?php if (!$verificationSent): ?><a class="google" href="https://accounts.google.com/" target="_blank" rel="noopener"><span class="google-mark">G</span> Continue with Google</a><div class="divider"><span>or continue with phone</span></div><form method="post"><input type="hidden" name="action" value="request_code"><div class="field"><label for="phone">Phone number</label><div class="phone-row"><input value="+254" aria-label="Country code" readonly><input id="phone" name="phone" type="tel" inputmode="tel" value="<?= htmlspecialchars($phoneValue) ?>" placeholder="712 345 678" required></div></div><button class="primary" type="submit">Send verification code</button></form><?php else: ?><form method="post"><input type="hidden" name="action" value="verify_code"><div class="field"><label for="code">Verification code</label><input id="code" name="code" type="text" inputmode="numeric" maxlength="6" placeholder="Enter 6-digit code" required></div><button class="primary" type="submit">Verify and continue</button></form><?php endif; ?><?php if ($message !== ''): ?><div class="status" role="status"><?= htmlspecialchars($message) ?></div><?php endif; ?><p class="fine">By continuing, you agree to our <a href="#terms">Terms</a> and <a href="#privacy">Privacy Policy</a>.</p><a class="back" href="index.php">&larr; Back to marketplace</a></main></body>
<script>
const googleMark = document.querySelector('.google-mark');
if (googleMark) {
    googleMark.outerHTML = '<svg class="google-mark" viewBox="0 0 24 24" role="img" aria-label="Google"><path fill="#4285F4" d="M21.35 12.27c0-.72-.06-1.42-.18-2.09H12v3.96h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.7 2.91-4.2 2.91-7.26Z"/><path fill="#34A853" d="M12 21.82c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.92-3.31.92-2.54 0-4.7-1.72-5.47-4.03H3.28v2.53A9.75 9.75 0 0 0 12 21.82Z"/><path fill="#FBBC05" d="M6.53 13.9A5.86 5.86 0 0 1 6.22 12c0-.66.11-1.3.31-1.9V7.57H3.28A9.76 9.76 0 0 0 2.25 12c0 1.57.38 3.05 1.03 4.43l3.25-2.53Z"/><path fill="#EA4335" d="M12 6.07c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 3.16 14.63 2.18 12 2.18a9.75 9.75 0 0 0-8.72 5.39l3.25 2.53C7.3 7.79 9.46 6.07 12 6.07Z"/></svg>';
}
</script>
</html>
