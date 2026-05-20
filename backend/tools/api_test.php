<?php
$data = ["email" => "customer@example.com", "password" => "password"];
$opts = [
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/json",
        "content" => json_encode($data),
    ],
];
$context = stream_context_create($opts);
// For dev testing use /dev/token to avoid CSRF on web routes (returns JWT for seeded customer)
$res = file_get_contents("http://127.0.0.1:8000/dev/token");
echo "DEV TOKEN RESPONSE:\n";
echo $res;
$j = json_decode($res, true);
if (isset($j["access_token"])) {
    $opts2 = [
        "http" => [
            "method" => "GET",
            "header" => "Authorization: Bearer " . $j["access_token"],
        ],
    ];
    $ctx2 = stream_context_create($opts2);
    $me = file_get_contents("http://127.0.0.1:8000/auth/me", false, $ctx2);
    echo "\nME_RESPONSE:\n";
    echo $me;
} else {
    echo "\nNo access_token received from dev/token\n";
}
