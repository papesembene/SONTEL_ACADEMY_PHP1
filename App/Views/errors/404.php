<div class="error-container">
    <h1>404 - Page non trouvée</h1>
    <p>La page demandée n'existe pas.</p>
    <?php if (isset($message)): ?>
    <div class="debug-info">
        <p><?= htmlspecialchars($message) ?></p>
    </div>
    <?php endif; ?>
    <a href="/" class="btn">Retour à l'accueil</a>
</div>

<style>
    .error-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        text-align: center;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .btn {
        display: inline-block;
        padding: 0.8rem 1.5rem;
        background: #FF7900;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 1rem;
    }
</style>