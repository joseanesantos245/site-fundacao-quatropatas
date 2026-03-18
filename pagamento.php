<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Função simples para carregar o arquivo .env
function loadEnv($path)
{
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), "#") === 0) continue;
        list($name, $value) = explode("=", $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Carrega o .env do diretório atual
loadEnv(__DIR__ . "/.env");

// Suas credenciais da SigiloPay (agora lidas do .env)
$client_id = $_ENV["SIGILOPAY_CLIENT_ID"];
$client_secret = $_ENV["SIGILOPAY_CLIENT_SECRET"];
$api_base_url = "https://app.sigilopay.com.br/api/v1";

// Verifica se a requisição é POST e se o valor foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["amount"] )) {
    $amount = (float)$_POST["amount"];
    $price_in_cents = (int)($amount * 100);

    $data = [
        "product" => [
            "name" => "Doação para Fundação Quatro Patas",
            "externalId" => "DONATION-" . uniqid(),
            "photos" => ["https://i.postimg.cc/J4YfHrjd/Logo_Canina.webp"],
            "offer" => [
                "name" => "Doação de " . number_format($amount, 2, ",", "." ) . " BRL",
                "price" => $price_in_cents,
                "offerType" => "NATIONAL",
                "currency" => "BRL",
                "lang" => "pt-BR"
            ]
        ],
        "settings" => [
            "paymentMethods" => ["BOLETO", "PIX", "CREDIT_CARD"],
            "thankYouPage" => "",
            "askForAddress" => false,
            "askForDocument" => false,
            "askForPhone" => false,
            "askForName" => false,
            "askForEmail" => false,
        ]
    ];

    $ch = curl_init($api_base_url . "/gateway/checkout");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "x-public-key: " . $client_id,
        "x-secret-key: " . $client_secret
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE );
    curl_close($ch);

    if ($http_code === 200 ) {
        echo $response;
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao criar checkout"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Requisição inválida"]);
}
?>
