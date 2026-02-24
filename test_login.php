<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: admin.fcta.gov.local']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
list($header, $body) = explode("\r\n\r\n", $response, 2);

preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
$cookies = array();
foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $cookies = array_merge($cookies, $cookie);
}

preg_match('/name="_token" value="(.*?)"/', $body, $tokenMatch);
$token = $tokenMatch[1] ?? '';

$cookieStr = '';
foreach ($cookies as $key => $value) {
    $cookieStr .= "$key=$value; ";
}

$postData = http_build_query([
    '_token' => $token,
    'email' => 'superadmin@fcta.gov.local',
    'password' => 'passw0rd!'
]);

curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_COOKIE, rtrim($cookieStr, '; '));
$response2 = curl_exec($ch);
curl_close($ch);

echo "Token: $token\n";
echo "Cookies: $cookieStr\n";
echo "Headers:\n$header\n";
list($header2, $body2) = explode("\r\n\r\n", $response2, 2);
echo "Result Headers:\n$header2\n";
