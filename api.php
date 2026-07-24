<?php
header("Content-Type: application/json");

require_once 'db.php';
require_once 'User.php';

$inputData = json_decode(file_get_contents("php://input"), true);

if (!$inputData) {
    echo json_encode(["success" => false, "message" => "Données invalides."]);
    exit();
}

$action = $inputData['action'] ?? '';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if ($action === 'register') {
    $username = trim($inputData['username'] ?? '');
    $password = trim($inputData['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
        exit();
    }

    $response = $user->register($username, $password);
    echo json_encode($response);
    exit();
}

if ($action === 'login') {
    $username = trim($inputData['username'] ?? '');
    $password = trim($inputData['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
        exit();
    }

    $response = $user->login($username, $password);
    echo json_encode($response);
    exit();
}

echo json_encode(["success" => false, "message" => "Action non autorisée."]);
?>