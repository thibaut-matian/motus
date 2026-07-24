<?php
session_start();

// Protection de la page : si l'utilisateur n'est pas connecté, retour à l'accueil
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.php';

// Connexion BDD
$database = new Database();
$db = $database->getConnection();

// Sécurité anti-répétition
$excludeCondition = "";
if (isset($_SESSION['last_word_id'])) {
    $excludeCondition = "WHERE id != " . intval($_SESSION['last_word_id']);
}

// Sélection d'un mot aléatoire de 5 lettres
$query = "SELECT id, word FROM words $excludeCondition ORDER BY RAND() LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$wordRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$wordRow) {
    $query = "SELECT id, word FROM words ORDER BY RAND() LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $wordRow = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($wordRow) {
    $_SESSION['last_word_id'] = $wordRow['id'];
}

// Configuration stricte
$secretWord = $wordRow ? strtoupper($wordRow['word']) : "MOTUS";
$wordLength = 5;
$firstLetter = $secretWord[0];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motus - Le Jeu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="game-container">
        <!-- Infos Joueur & Déconnexion -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <span style="font-size: 0.9rem;">Joueur : <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
            <a href="logout.php" style="color: #ff9f9f; text-decoration: none; font-size: 0.9rem;">Déconnexion</a>
        </div>

        <h1>MOTUS</h1>
        
        <div id="grid" class="grid"></div>

        <div id="message" class="error-msg" style="color: #f1c40f; min-height: 24px; margin-top: 15px;"></div>

        <button id="btn-restart" style="display: none; margin: 15px auto; padding: 10px 20px; background-color: #f1c40f; color: black; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Rejouer 🔄</button>

        <!-- ==========================================
             WALL OF FAME
             ========================================== -->
        <div class="wall-of-fame" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px;">
            <h3 style="margin-bottom: 15px;">🏆 Wall of Fame</h3>
            <?php
            $scoreQuery = "SELECT u.username, s.attempts 
                           FROM scores s 
                           JOIN users u ON s.user_id = u.id 
                           WHERE s.won = 1 
                           ORDER BY s.attempts ASC, s.played_at DESC 
                           LIMIT 5";
            $scoreStmt = $db->prepare($scoreQuery);
            $scoreStmt->execute();
            $scores = $scoreStmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($scores) > 0): ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.4);">
                            <th style="padding: 8px; text-align: left;">Joueur</th>
                            <th style="padding: 8px; text-align: center;">Tentatives</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scores as $s): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <td style="padding: 8px; text-align: left;"><?php echo htmlspecialchars($s['username']); ?></td>
                                <td style="padding: 8px; text-align: center; font-weight: bold; color: #f1c40f;"><?php echo $s['attempts']; ?>/6</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="font-size: 0.85rem; opacity: 0.7;">Aucun score enregistré. Soyez le premier !</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const SECRET_WORD = "<?php echo $secretWord; ?>";
        const WORD_LENGTH = <?php echo $wordLength; ?>;
        const FIRST_LETTER = "<?php echo $firstLetter; ?>";
    </script>
    <script src="app.js"></script>
</body>
</html>