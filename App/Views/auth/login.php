<?php
// En haut du fichier
require_once __DIR__.'/../../Services/session.service.php';
session_init();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Sonatel Academy</title>
    <link rel="stylesheet" href="assets/css/login.css">
 
</head>
<body>
    <div class="container">
        <div class="border-decoration"></div>
        <div class="bottom-decoration"></div>
        
        <div class="logo">
            <div class="sonatel-logo">
                <div class="orange-text">Orange Digital Center</div>
                <div class="sonatel-text">sonatel</div>
                <div class="orange-square">
                    <div class="white-line"></div>
                </div>
            </div>
        </div>
        
        <div class="welcome-text">
            <h2>Bienvenue sur</h2>
            <h2 class="highlight">Ecole du code Sonatel Academy</h2>
        </div>
        
        <div class="login-title">
            Se connecter
        </div>
        <?php if (session_has('success_message')): ?>
    <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
        <?= htmlspecialchars(session_get('success_message')['content']) ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

        <form method="POST" action="/" class="login-form">
        <?php if (session_has('login_errors')): ?>
            <div class="alert alert-danger">
                <?php foreach (session_get('login_errors', []) as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

            <div class="form-group">
                <label for="email">Login</label>
                <input type="text" id="email" name="login" class="form-control" placeholder="Matricule ou email">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password"  name="password" class="form-control" placeholder="Mot de passe">
            </div>
                <div class="forgot-password">
                    <a href="/forgot-password">Mot de passe oublié ?</a>
                </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
    </div>
</body>
</html>
<?php
// Nettoyage en fin de script
clear_session_messages();
