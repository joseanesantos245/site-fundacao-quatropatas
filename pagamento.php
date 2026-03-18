<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite requisições de qualquer origem (ajuste em produção se souber a origem exata)
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Suas credenciais da SigiloPay
$client_id = 'briannesilva1_krlesbfhm6p6229o';
$client_secret = 'fuq138dydled93zlsbx7fachkwih2sgt6t7sxqtb6ayawg0pwmo6fcf2kjd4qsup';
$api_base_url = 'https://app.sigilopay.com.br/api/v1';

// Verifica se a requisição é POST e se o valor foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = (float)$_POST['amount'];
    
    // O valor da API da SigiloPay é em centavos, então multiplicamos por 100
    $price_in_cents = (int)($amount * 100);

    // Dados para a requisição de criação de checkout
    $data = [
        'product' => [
            'name' => 'Doação para Fundação Quatro Patas',
            'externalId' => 'DONATION-' . uniqid(), // ID único para cada doação
            'photos' => [
                'https://i.postimg.cc/J4YfHrjd/Logo_Canina.webp' // Logo da Fundação
            ],
            'offer' => [
                'name' => 'Doação de ' . number_format($amount, 2, ',', '.') . ' BRL',
                'price' => $price_in_cents,
                'offerType' => 'NATIONAL',
                'currency' => 'BRL',
                'lang' => 'pt-BR'
            ]
        ],
        'settings' => [
            'paymentMethods' => [
                'BOLETO',
                'PIX',
                'CREDIT_CARD'
            ],
            'thankYouPage' => '', // Opcional: URL de agradecimento após o pagamento
            'askForAddress' => false,
            'askForDocument' => false,
            'askForPhone' => false,
            'askForName' => false,
            'askForEmail' => false,
        ]
    ];

    $ch = curl_init($api_base_url . '/gateway/checkout');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-public-key: ' . $client_id,
        'x-secret-key: ' . $client_secret
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        echo $response;
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar checkout: ' . $response]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida ou valor não fornecido.']);
}

?>
