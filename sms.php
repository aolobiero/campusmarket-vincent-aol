<?php
function sendVerificationSms(string $phone, string $code): array
{
    $username = getenv('AT_USERNAME') ?: '';
    $apiKey = getenv('AT_API_KEY') ?: '';
    $senderId = getenv('AT_SENDER_ID') ?: '';

    if ($username === '' || $apiKey === '') {
        return ['sent' => false, 'configured' => false];
    }

    $payload = [
        'username' => $username,
        'to' => $phone,
        'message' => "Your CampusMarket verification code is $code. It expires in 10 minutes.",
    ];
    if ($senderId !== '') {
        $payload['from'] = $senderId;
    }

    $curl = curl_init('https://api.africastalking.com/version1/messaging');
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_HTTPHEADER => [
            'apiKey: ' . $apiKey,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return ['sent' => $status >= 200 && $status < 300, 'configured' => true, 'response' => $response];
}
