<?php
header("Content-Type: application/json");
session_start(); // Nécessaire pour lier le score à l'utilisateur connecté

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

// Action : Inscription
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

// Action : Connexion
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

// Action : Enregistrement automatique du Score (Ajouté)
if ($action === 'save_score') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Utilisateur non connecté."]);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $attempts = intval($inputData['attempts'] ?? 6);
    $won = intval($inputData['won'] ?? 0);

    $query = "INSERT INTO scores (user_id, attempts, won, played_at) VALUES (:user_id, :attempts, :won, NOW())";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":attempts", $attempts);
    $stmt->bindParam(":won", $won);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Score sauvegardé !"]);
    } else {
        echo json_encode(["success" => false, "message" => "Impossible d'enregistrer le score."]);
    }
    exit();
}

echo json_encode(["success" => false, "message" => "Action non autorisée."]);
?>