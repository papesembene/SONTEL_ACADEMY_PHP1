<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Référentiels de Formation</title>
    <link rel="stylesheet" href="/assets/css/referentiel.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
</head>
<body>
<?php
session_init();
$search = $_GET['search'] ?? null;
$show_all = isset($_GET['show_all']) && $_GET['show_all'] == 1;
$showCreateForm = isset($_GET['action']) && $_GET['action'] == 'create';
$showAssignForm = isset($_GET['action']) && $_GET['action'] == 'assign';

if ($show_all) {
    $referentiels = App\Controllers\Referentiels\get_all_referentiels($search);
    $title = "Tous les Référentiels";
    $subtitle = "Liste complète de tous les référentiels de formation";
} else {
    $referentiels = App\Controllers\Referentiels\get_referentiels_by_active_promotion($search);
    $activePromotion = App\Controllers\Promotions\get_active_promotion();
    $promotionName = $activePromotion ? $activePromotion['nom'] : "Aucune promotion active";
    $title = "Référentiels de la promotion active";
    $subtitle = "Liste des référentiels de la promotion: " . htmlspecialchars($promotionName);
    $activePromotionId = $activePromotion ? $activePromotion['id'] : null;
    $nonAssignedReferentiels = App\Controllers\Referentiels\get_non_assigned_referentiels($activePromotionId);
}
?>

<div class="container">
    <!-- Messages d'alerte -->
    <?php if (session_has('error_message')): ?>
        <div class="alert alert-danger">
            <?php
            $error = session_get('error_message');
            if (is_array($error)) {
                echo htmlspecialchars($error['content'] ?? 'Erreur inconnue');
            } else {
                echo htmlspecialchars($error ?? 'Erreur inconnue');
            }
            ?>
        </div>
        <?php session_remove('error_message'); ?>
    <?php endif; ?>

    <?php if (session_has('success_message_assignation')): ?>
        <div class="alert alert-success">
            <?= is_array(session_get('success_message_assignation')) && isset(session_get('success_message_assignation')['content']) 
                ? htmlspecialchars(session_get('success_message_assignation')['content']) 
                : 'Opération réussie.'; ?>
        </div>
        <?php session_remove('success_message_assignation'); ?>
    <?php endif; ?>

    <?php if (session_has('success_message_unassign')): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars(session_get('success_message_unassign')); ?>
        </div>
        <?php session_remove('success_message_unassign'); ?>
    <?php endif; ?>

    <header>
        <a href="/referentiels" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Retour aux référentiels actifs
        </a>
        <h1><?= $title ?></h1>
        <p class="subtitle"><?= $subtitle ?></p>
    </header>

    <!-- Barre de recherche et boutons d'action -->
    <form method="GET" action="/referentiels" class="search">
        <input type="text" name="search" class="search-input" placeholder="Rechercher un référentiel..." 
            value="<?= htmlspecialchars($search ?? '') ?>">
        
        <?php if ($show_all): ?>
            <a href="/referentiels" class="btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
                Référentiels actifs
            </a>
            <a href="/referentiels?action=create" class="btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Créer un référentiel
            </a>
        <?php else: ?>
            <a href="/referentiels?show_all=1" class="btn btn-orange">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11-8 11-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                Tous les référentiels
            </a>
            <a href="/referentiels?show_all=1&action=assign" class="btn btn-blue">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Ajouter à la promo
            </a>
        <?php endif; ?>
    </form>

    <!-- Formulaire de création de référentiel -->
    <?php if ($showCreateForm): ?>
    <div class="form-container">
        <h2>Créer un nouveau référentiel</h2>
        <?php if (session_has('validation_errors')): ?>
            <div class="alert alert-danger">
                <?php 
                $validation_errors = session_get('validation_errors', []);
                if (is_array($validation_errors)): 
                    foreach ($validation_errors as $field => $errors): 
                        if (is_array($errors)): 
                            foreach ($errors as $error): ?>
                                <div><?= htmlspecialchars($error) ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php session_remove('validation_errors'); ?>
        <?php endif; ?>
        <form action="/referentiels/create" method="POST" enctype="multipart/form-data">
            <div class="image-upload">
                <label for="photo-upload" class="upload-label">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                    <p class="image-upload-text">Cliquez pour ajouter une photo</p>
                </label>
                <input type="file" id="photo-upload" name="photo" accept="image/*" class="file-input">
                <?php if(session_has('old_input') && isset(session_get('old_input')['photo'])): ?>
                    <p class="file-selected">Fichier précédemment sélectionné (veuillez le sélectionner à nouveau)</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="nom">Nom <span style="color:red;">*</span></label>
                <input type="text" name="nom" id="nom" class="form-control" 
                       value="<?= htmlspecialchars(session_get('old_input')['nom'] ?? '') ?>"
                       placeholder="Entrez le nom du référentiel">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" 
                          placeholder="Description du référentiel (optionnel)"><?= htmlspecialchars(session_get('old_input')['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="capacite">Capacité <span style="color:red;">*</span></label>
                        <input type="number" name="capacite" id="capacite" class="form-control" 
                               value="<?= htmlspecialchars(session_get('old_input')['capacite'] ?? '30') ?>"
                               min="1" placeholder="Nombre de places">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="sessions">Nombre de sessions <span style="color:red;">*</span></label>
                        <select name="sessions" id="sessions" class="form-control" >
                            <?php 
                            $sessionOptions = ['1 session', '2 sessions', '3 sessions', '4 sessions'];
                            $oldSessions = session_get('old_input')['sessions'] ?? '';
                            foreach($sessionOptions as $option): ?>
                                <option value="<?= $option ?>" <?= ($oldSessions === $option) ? 'selected' : '' ?>><?= $option ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="/referentiels" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn" name="action" value="create">Créer</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Formulaire pour assigner des référentiels à une promotion -->
    <?php if ($showAssignForm): ?>
    <div class="form-container">
        <h2>Assigner ou désaffecter des référentiels</h2>
        <?php 
        $activePromotion = App\Controllers\Promotions\get_active_promotion();
        $activePromotionId = $activePromotion ? $activePromotion['id'] : null;
        $assignedReferentiels = $activePromotion['referentiels'] ?? [];
        $allReferentiels = App\Controllers\Referentiels\get_all_referentiels();

        // Vérifier si la promotion est terminée
        $promotionTerminee = $activePromotion && strtotime($activePromotion['date_fin']) < time();
        ?>
        
        <form action="/referentiels/assign" method="POST">
            <div class="form-group">
                <label for="promotion-info">Promotion active :</label>
                <?php if ($activePromotion): ?>
                    <div class="form-control" id="promotion-info" style="background-color: #f8f9fa; cursor: not-allowed;">
                        <?= htmlspecialchars($activePromotion['nom']) ?> 
                        <span style="color: <?= $promotionTerminee ? '#dc3545' : '#28a745' ?>">
                            (<?= htmlspecialchars($activePromotion['statut']) ?>)
                        </span>
                    </div>
                <?php else: ?>
                    <div class="form-control" id="promotion-info" style="background-color: #f8d7da; color: #721c24;">
                        Aucune promotion active
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($activePromotion): ?>
                <div class="form-group">
                    <label>Sélectionnez les référentiels à assigner ou désaffecter</label>
                    <?php if (!empty($allReferentiels)): ?>
                        <div class="referentiel-list">
                            <?php foreach ($allReferentiels as $ref): ?>
                                <div class="referentiel-item">
                                    <label>
                                        <input type="checkbox" name="referentiels[]" value="<?= $ref['id'] ?>" 
                                            <?= in_array($ref['id'], $assignedReferentiels) ? 'checked' : '' ?>
                                            <?= $promotionTerminee ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($ref['nom']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Aucun référentiel disponible
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$promotionTerminee): ?>
                    <div class="form-actions">
                        <a href="/referentiels?show_all=1" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-blue">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Cette promotion est terminée. Vous ne pouvez plus modifier ses référentiels.
                    </div>
                <?php endif; ?>
                <input type="hidden" name="promotion_id" value="<?= $activePromotionId ?>">
            <?php else: ?>
                <div class="alert alert-warning">
                    Veuillez d'abord activer une promotion avant d'assigner des référentiels.
                </div>
                <div class="form-actions">
                    <a href="/referentiels?show_all=1" class="btn btn-secondary">Retour</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
    <?php endif; ?>

    <!-- Liste des référentiels -->
    <?php if (!$showCreateForm && !$showAssignForm): ?>
    <div class="grid">
        <?php foreach ($referentiels as $referentiel): ?>
            <div class="card">
                <div class="card-img">
                    <img src="/uploads/referentiels/<?= htmlspecialchars($referentiel['photo']) ?>" 
                        alt="<?= htmlspecialchars($referentiel['nom']) ?>"
                        onerror="this.src='/assets/images/default-referentiel.jpg'">
                </div>
                <div class="card-content">
                    <h3 class="card-title"><?= htmlspecialchars($referentiel['nom']) ?></h3>
                    <p class="card-description">
                        <?= !empty($referentiel['description']) 
                            ? htmlspecialchars($referentiel['description']) 
                            : '<em>Aucune description disponible</em>' ?>
                    </p>
                    <div class="card-capacity">
                        <i class="fas fa-users"></i> Capacité: <?= htmlspecialchars($referentiel['capacite'] ?? 0) ?> places
                    </div>
                    <?php if (isset($referentiel['sessions'])): ?>
                        <div class="card-sessions" style="margin-top: 8px; font-size: 0.85rem; color: #6c757d;">
                            <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($referentiel['sessions']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($referentiels)): ?>
            <div class="no-results">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="11" y1="8" x2="11" y2="14"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
                <h3>Aucun référentiel trouvé</h3>
                <p>Essayez de modifier vos critères de recherche ou de créer un nouveau référentiel.</p>
                <?php if (!$show_all): ?>
                    <a href="/referentiels?show_all=1" class="btn btn-orange" style="margin-top: 15px;">
                        Voir tous les référentiels
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php
clear_session_messages();
?>
</body>
</html>