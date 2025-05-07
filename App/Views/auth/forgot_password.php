<?php
require_once __DIR__.'/../../Services/session.service.php';
session_init();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mot de passe oublié - Sonatel Academy</title>
  <link rel="stylesheet" href="/assets/css/login.css">
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

    <div class="login-title">Réinitialisation du mot de passe</div>
      
    <form method="POST" action="/forgot-password" class="login-form">
      <?php if (session_has('forgot_password_errors')): ?>
        <div class="alert alert-danger">
          <?php foreach (session_get('forgot_password_errors', []) as $error): ?>
            <div><?= htmlspecialchars($error) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="form-group">
        <label for="login">Matricule ou Email</label>
        <input type="text" id="login" name="login" class="form-control"
          value="<?= htmlspecialchars(session_get('old_input.login', '')) ?>"
          placeholder="Entrez votre matricule ou email">
      </div>

      <div class="form-group">
        <label for="new_password">Nouveau mot de passe</label>
        <input type="password" id="new_password" name="new_password" class="form-control"
          placeholder="Entrez votre nouveau mot de passe">
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirmer le mot de passe</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
          placeholder="Confirmez votre nouveau mot de passe">
      </div>

      <button type="submit" class="btn-login">Mettre à jour mon mot de passe</button>

      <div class="back-to-login">
        <a href="/">Retourner à la page de connexion</a>
      </div>
    </form>
  </div>
</body>
</html>
<?php
session_remove('forgot_password_errors');
session_remove('old_input');
?>
