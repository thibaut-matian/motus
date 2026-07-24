<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motus - Connexion / Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="game-container">
        <h1 id="auth-title">Connexion</h1>
        
        <!-- Formulaire de Connexion -->
        <form id="login-form" class="auth-form">
            <input type="text" id="login-username" placeholder="Pseudo" required autocomplete="username">
            <input type="password" id="login-password" placeholder="Mot de passe" required autocomplete="current-password">
            <button type="submit">Se connecter</button>
            <p>Pas encore inscrit ? <a href="#" id="show-register" style="color: #f1c40f;">Créer un compte</a></p>
        </form>

        <!-- Formulaire d'Inscription -->
        <form id="register-form" class="auth-form" style="display: none;">
            <input type="text" id="register-username" placeholder="Choisir un pseudo" required autocomplete="new-username">
            <input type="password" id="register-password" placeholder="Choisir un mot de passe" required autocomplete="new-password">
            <button type="submit">S'inscrire</button>
            <p>Déjà un compte ? <a href="#" id="show-login" style="color: #f1c40f;">Se connecter</a></p>
        </form>

        <!-- Zone d'affichage des erreurs ou succès -->
        <div id="message-box" class="error-msg"></div>
    </div>

    <script>
        console.log("Script d'authentification chargé avec succès !");

        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const authTitle = document.getElementById('auth-title');
        const messageBox = document.getElementById('message-box');

        // Basculer vers l'inscription
        document.getElementById('show-register').addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.style.display = 'none';
            registerForm.style.display = 'flex';
            authTitle.innerText = "Inscription";
            messageBox.innerText = "";
        });

        // Basculer vers la connexion
        document.getElementById('show-login').addEventListener('click', (e) => {
            e.preventDefault();
            registerForm.style.display = 'none';
            loginForm.style.display = 'flex';
            authTitle.innerText = "Connexion";
            messageBox.innerText = "";
        });

        // Fonction d'envoi AJAX via Fetch
        async function sendAuthData(action, username, password) {
            console.log(`Tentative d'envoi Fetch pour l'action : ${action} avec le pseudo : ${username}`);
            messageBox.innerText = "Traitement en cours...";
            messageBox.style.color = "#f1c40f";

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action, username, password })
                });

                console.log("Statut de la réponse HTTP :", response.status);
                
                // --- INTERCEPTION DE L'ERREUR PHP ICI ---
                const textResponse = await response.text();
                console.log("RÉPONSE BRUTE DU SERVEUR :", textResponse);

                // Conversion manuelle du texte en JSON
                const data = JSON.parse(textResponse);
                console.log("Données JSON reçues du serveur :", data);

                if (data.success) {
                    if (action === 'login') {
                        messageBox.style.color = '#2ecc71';
                        messageBox.innerText = "Connexion réussie ! Redirection...";
                        setTimeout(() => {
                            window.location.href = 'game.php';
                        }, 1200);
                    } else {
                        messageBox.style.color = '#2ecc71';
                        messageBox.innerText = "Inscription réussie ! Vous pouvez vous connecter.";
                        document.getElementById('register-username').value = "";
                        document.getElementById('register-password').value = "";
                        document.getElementById('show-login').click();
                    }
                } else {
                    messageBox.style.color = '#ff9f9f';
                    messageBox.innerText = data.message;
                }
            } catch (error) {
                console.error("Erreur attrapée par le catch JavaScript :", error);
                messageBox.style.color = '#ff9f9f';
                messageBox.innerText = "Une erreur technique est survenue lors de la communication avec le serveur.";
            }
        }

        // Écoute de la soumission de la Connexion
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            console.log("Formulaire de connexion soumis");
            const user = document.getElementById('login-username').value.trim();
            const pass = document.getElementById('login-password').value;
            sendAuthData('login', user, pass);
        });

        // Écoute de la soumission de l'Inscription
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            console.log("Formulaire d'inscription soumis");
            const user = document.getElementById('register-username').value.trim();
            const pass = document.getElementById('register-password').value;
            sendAuthData('register', user, pass);
        });
    </script>
</body>
</html>