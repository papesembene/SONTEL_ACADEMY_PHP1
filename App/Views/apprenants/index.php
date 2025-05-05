<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Apprenants</title>
    <!-- Ressources communes -->
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/common.css?v=1.0">

    <!-- Ressources spécifiques -->
    <?php if ($current_page === 'apprenants'): ?>
        <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/apprenants.css?v=1.0">
    <?php elseif ($current_page === 'referentiels'): ?>
        <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/referentiel.css?v=1.0">
    <?php elseif ($current_page === 'promotions'): ?>
        <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/promotions.css?v=1.0">
    <?php endif; ?>
   
</head>
<body>
    <div class="container">
        <?php if ($action === 'list'): ?>
            <!-- Header with title and action buttons -->
            <div class="header">
                <h2>Apprenants <span class="apprenant-count"><?= count($allApprenants ?? $apprenants) ?>apprenant(s)</span></h2>
                <div class="action-buttons">
                    <?php 
                        $today = new \DateTime();
                        $endDate = \DateTime::createFromFormat('d/m/Y', $activePromotion['date_fin']);
                        if ($endDate >= $today): ?>
                            <a href="?action=add" class="btn btn-success" style="background-color:#009989;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14"></path>
                                </svg>
                                Ajouter apprenant
                            </a>
                       

                        <a href="?action=import" class="btn btn-download" style="background-color:#009989;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"></path>
                            </svg>
                            Importer la liste
                        </a>
                        <?php endif; ?>
                    </div>
                   
            </div>
            
            <?php if ($endDate < $today): ?>
                <div class="promo-ended-notice" style="background: #fff3bf;color: #5c3c00; padding: 8px 12px;border-radius: 4px;margin-top: 10px;font-size: 14px;">
                    La promotion est terminée - nouvelles inscriptions fermées
                </div>
            <?php endif; ?>
            <!-- Search and filter section -->
            <div class="filters">
                <div class="search-container">
                    <input type="text" placeholder="Rechercher...">
                </div>
                <div class="filter-dropdown">
                    <select onchange="window.location.href='?tab=<?= $tab ?>&referentiel=' + this.value">
                        <option value="">Filtre par classe</option>
                        <?php foreach($allreferentiels as $ref) :?>
                        <option value="<?= $ref['id'] ?>" <?= isset($referentielFilter) && $referentielFilter === $ref['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ref['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-dropdown">
                    <select>
                        <option value="">Filtre par status</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                        <option value="replaced">Remplacé</option>
                    </select>
                </div>
                
                <?php if (isset($referentielFilter) && !empty($referentielFilter)): ?>
                <div class="filter-actions">
                    <a href="?tab=<?= $tab ?>" class="btn btn-clear">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Effacer le filtre
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Afficher un message indiquant le filtre actif -->
            <?php if (isset($referentielFilter) && !empty($referentielFilter)): 
                $referentielNom = '';
                foreach ($allreferentiels as $ref) {
                    if ($ref['id'] === $referentielFilter) {
                        $referentielNom = $ref['nom'];
                        break;
                    }
                }
                if (!empty($referentielNom)):
            ?>
                <div class="filter-info">
                    <span>Filtré par référentiel: <strong><?= htmlspecialchars($referentielNom) ?></strong></span>
                </div>
            <?php 
                endif;
            endif; 
            ?>
            <!-- Tabs -->
            <div class="tabs">


                <a href="/apprenants?tab=retained" class="tab <?= !isset($_GET['tab']) || $_GET['tab'] === 'retained' ? 'active' : '' ?>">
                    <i class="fas fa-user-check"></i> Liste des retenus
                </a>
                <a href="/apprenants?tab=waiting" class="tab <?= isset($_GET['tab']) && $_GET['tab'] === 'waiting' ? 'active' : '' ?>" 
                   <?= $endDate < $today? 'hidden':'' ?>>
                    <i class="fas fa-user-clock"></i> Liste d'attente
                </a>
            </div>

            <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'retained'): ?>
                <!-- Table des apprenants retenus -->
                <div class="table-container">
                    <table>
                        <thead class="thead">
                            <tr>
                                <th>Photo</th>
                                <th>Matricule</th>
                                <th>Nom Complet</th>
                                <th>Adresse</th>
                                <th>Téléphone</th>
                                <th>Référentiel</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($apprenants as $apprenant): ?>
                            <tr>
                                <td class="photo">
                                <?php if (!empty($apprenant['photo'])): ?>
                                    <img src="/uploads/apprenants/<?= htmlspecialchars($apprenant['photo']) ?>" alt="Photo de profil">
                                <?php else: ?>
                                    <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Avatar par défaut">
                                <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($apprenant['matricule']) ?></td>
                                <td><?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></td>
                                <td><?= htmlspecialchars($apprenant['adresse']) ?></td>
                                <td><?= htmlspecialchars($apprenant['telephone']) ?></td>
                                <td>
                                    <?php
                                    // Trouver le nom du référentiel correspondant
                                    $referentielNom = 'Inconnu';
                                    foreach ($allreferentiels as $referentiel) {
                                        if ($referentiel['id'] === $apprenant['referentiel_id']) {
                                            $referentielNom = $referentiel['nom'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <span class="referentiel-tag"><?= htmlspecialchars($referentielNom) ?></span>
                                </td>
                                <td><span class="status-badge status-active">Actif</span></td>
                                <td class="action-dots">
                                  <div class="dropdown">
                                      <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-toggle">
                                          <circle cx="12" cy="5" r="1"></circle>
                                          <circle cx="12" cy="12" r="1"></circle>
                                          <circle cx="12" cy="19" r="1"></circle>
                                      </svg>
                                      <div class="dropdown-content">
                                          <a href="/apprenants?action=show&matricule=<?= $apprenant['matricule'] ?>">Voir détails</a>
                                          <a href="/apprenants/edit/<?= $apprenant['id'] ?>">Modifier</a>
                                      </div>
                                  </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'waiting'): ?>
                <!-- Table des apprenants en attente -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Référentiel</th>
                                <th>Erreur(s)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($waitingApprenants)): ?>
                            <?php foreach ($waitingApprenants as $apprenant): ?>
                                <tr>
                                    <td><?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></td>
                                    <td><?= htmlspecialchars($apprenant['email'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($apprenant['telephone'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        // Trouver le nom du référentiel correspondant
                                        $referentielNom = 'Inconnu';
                                        if (isset($apprenant['referentiel_id'])) {
                                            foreach ($allreferentiels as $referentiel) {
                                                if ($referentiel['id'] === $apprenant['referentiel_id']) {
                                                    $referentielNom = $referentiel['nom'];
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <span class="referentiel-tag"><?= htmlspecialchars($referentielNom) ?></span>
                                    </td>
                                    <td class="error-cell">
                                        <?php if (isset($apprenant['errors'])): ?>
                                            <?php if (isset($apprenant['errors']['exception'])): ?>
                                                <span class="error-detail"><?= htmlspecialchars($apprenant['errors']['exception']) ?></span>
                                            <?php else: ?>
                                                <ul class="error-detail" style="margin: 0; padding-left: 20px;">
                                                    <?php foreach ($apprenant['errors'] as $field => $message): ?>
                                                        <li><strong><?= htmlspecialchars($field) ?></strong>: <?= htmlspecialchars($message) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span>Aucune erreur spécifiée</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/apprenants?action=edit_waiting&id=<?= $apprenant['id'] ?>" class="btn btn-edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                            <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                            </svg>
                                        </a>
                                        <form action="/apprenants" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="remove_waiting">
                                            <input type="hidden" name="id" value="<?= $apprenant['id'] ?>">
                                            <button type="submit" class="btn btn-delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Aucun apprenant en attente</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            <!-- Pagination -->
            <div class="pagination">
                <div class="page-info">
                    <span>Apprenants/page:</span>
                    <select onchange="window.location.href='?per_page=' + this.value + '&tab=<?= $tab ?><?= isset($referentielFilter) ? '&referentiel=' . $referentielFilter : '' ?>'">
                        <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20</option>
                        <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    </select>
                </div>
                <div class="page-info">
                    <?= $startIndex ?> à <?= $endIndex ?> apprenants pour <?= $totalItems ?>
                </div>
                <div class="page-controls">
                    <a href="?page=<?= max(1, $currentPage - 1) ?>&tab=<?= $tab ?>&per_page=<?= $perPage ?><?= isset($referentielFilter) ? '&referentiel=' . $referentielFilter : '' ?>" 
                       class="page-button <?= $currentPage <= 1 ? 'disabled' : '' ?>"><</a>
                    
                    <?php 
                    // Afficher un nombre limité de pages avec ellipsis
                    $maxPagesToShow = 5;
                    $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
                    $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                    
                    // Ajuster startPage si on est proche de la fin
                    if ($endPage - $startPage + 1 < $maxPagesToShow) {
                        $startPage = max(1, $endPage - $maxPagesToShow + 1);
                    }
                    
                    // Afficher la première page et ellipsis si nécessaire
                    if ($startPage > 1) {
                        echo '<a href="?page=1&tab=' . $tab . '&per_page=' . $perPage . (isset($referentielFilter) ? '&referentiel=' . $referentielFilter : '') . '" class="page-button">1</a>';
                        if ($startPage > 2) {
                            echo '<span class="page-ellipsis">...</span>';
                        }
                    }
                    
                    // Afficher les pages
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <a href="?page=<?= $i ?>&tab=<?= $tab ?>&per_page=<?= $perPage ?><?= isset($referentielFilter) ? '&referentiel=' . $referentielFilter : '' ?>" 
                           class="page-button <?= $currentPage == $i ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; 
                    
                    // Afficher la dernière page et ellipsis si nécessaire
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<span class="page-ellipsis">...</span>';
                        }
                        echo '<a href="?page=' . $totalPages . '&tab=' . $tab . '&per_page=' . $perPage . (isset($referentielFilter) ? '&referentiel=' . $referentielFilter : '') . '" class="page-button">' . $totalPages . '</a>';
                    }
                    ?>
                    
                    <a href="?page=<?= min($totalPages, $currentPage + 1) ?>&tab=<?= $tab ?>&per_page=<?= $perPage ?><?= isset($referentielFilter) ? '&referentiel=' . $referentielFilter : '' ?>" 
                       class="page-button <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">></a>
                </div>
            </div>
        <?php elseif ($action === 'import'): ?>
            <!-- Formulaire d'import Excel -->
            <div class="apprenant-form-container">
                <div class="form-header">
                    <h1 style="color:#009989;" >Importer la liste des apprenants</h1>
                </div>
                <?php if (session_has('warning_message')):?>
                    <div style="color:orange; padding: 10px; background-color: #fff3cd; border-radius: 5px; margin-bottom: 20px;">
                        <?= htmlspecialchars(session_get('warning_message')['content']) ?>
                        <?php session_remove('warning_message'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session_has('error_message')):?>
                    <div style="color:#721c24; padding: 10px; background-color: #f8d7da; border-radius: 5px; margin-bottom: 20px;">
                        <?= htmlspecialchars(session_get('error_message')['content']) ?>
                        <?php session_remove('error_message'); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session_has('success_message')):?>
                    <div style="color:#155724; padding: 10px; background-color: #d4edda; border-radius: 5px; margin-bottom: 20px;">
                        <?= htmlspecialchars(session_get('success_message')['content']) ?>
                        <?php session_remove('success_message'); ?>
                    </div>
                <?php endif; ?> 
                <form action="/apprenants/import" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import">
                    <input type="hidden" id="promotion_id" name="promotion_id" value="<?= htmlspecialchars($activePromotion['id']) ?>" readonly>                  
                    <!-- Section pour l'importation -->
                    <div class="form-section">
                        <div class="section-header">
                            <h2>Informations d'importation</h2>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="fichier_excel">Fichier Excel</label>
                                <input type="file" id="fichier_excel" name="import_file" accept=".xls,.xlsx,.csv" >
                                <?php if (!empty($validation_errors['fichier_excel'])): ?>
                                    <div style="color:red;"><?= htmlspecialchars($validation_errors['import_file']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer">
                        <a href="/apprenants" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary" style="background-color:#009989;">Importer</button>
                    </div>
                </form>
            </div>
        <?php elseif ($action === 'add' || $action === 'edit_waiting'): ?>
            <!-- Formulaire d'ajout d'apprenant -->
                <div class="apprenant-form-container">
        <div class="form-header" style="color:#009989;">
            <h1 style="color:#009989;"><?= $action === 'add' ? 'Ajout apprenant' : 'Correction apprenant' ?></h1>
        </div>

    <?php if (session_has('error_message')): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars(session_get('error_message')['content']) ?>
            <?php session_remove('error_message'); // Nettoyer après affichage ?>
        </div>
    <?php endif; ?>

    <form action="/apprenants" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $action === 'edit_waiting' ? 'validate_waiting' : 'create' ?>">
        <?php if ($action === 'edit_waiting'): ?>
            <input type="hidden" name="waiting_id" value="<?= htmlspecialchars($waitingId ?? '') ?>">
        <?php endif; ?>
        <input type="hidden" id="promotion_id" name="promotion_id" value="<?= htmlspecialchars($activePromotion['id']) ?>" readonly>
        
        <!-- Informations de l'apprenant Section -->
        <div class="form-section">
            <div class="section-header">
                <h2>Informations de l'apprenant</h2>
                <span class="edit-icon">✏️</span>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="prenom">Prénom(s)</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($oldInput['prenom'] ?? '') ?>" placeholder="Prénom(s) de l'apprenant">
                    <?php if (!empty($validation_errors['prenom'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['prenom']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($oldInput['nom'] ?? '') ?>" placeholder="Nom de l'apprenant">
                    <?php if (!empty($validation_errors['nom'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['nom']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($oldInput['date_naissance'] ?? '') ?>">
                    <?php if (!empty($validation_errors['date_naissance'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['date_naissance']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="lieu_naissance">Lieu de naissance</label>
                    <input type="text" id="lieu_naissance" name="lieu_naissance" value="<?= htmlspecialchars($oldInput['lieu_naissance'] ?? '') ?>" placeholder="Ville, Pays">
                    <?php if (!empty($validation_errors['lieu_naissance'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['lieu_naissance']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($oldInput['adresse'] ?? '') ?>" placeholder="Adresse complète">
                    <?php if (!empty($validation_errors['adresse'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['adresse']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" placeholder="exemple@email.com">
                    <?php if (!empty($validation_errors['email'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($oldInput['telephone'] ?? '') ?>" placeholder="Numéro de téléphone">
                    <?php if (!empty($validation_errors['telephone'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['telephone']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="referentiel">Référentiel</label>
                    <select id="referentiel" name="referentiel_id">
                        <option value="">Sélectionnez un référentiel</option>
                        <?php foreach ($referentiels as $referentiel): ?>
                            <option value="<?= htmlspecialchars($referentiel['id']) ?>" <?= (isset($oldInput['referentiel_id']) && $oldInput['referentiel_id'] == $referentiel['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($referentiel['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($validation_errors['referentiel_id'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['referentiel_id']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group photo-upload">
                    <label for="photo">Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <div class="file-info">Formats acceptés: JPG, PNG. Taille max: 2MB</div>
                    <?php if (!empty($validation_errors['photo'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['photo']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Informations du tuteur Section -->
        <div class="form-section">
            <div class="section-header">
                <h2>Informations du tuteur</h2>
                <span class="edit-icon">✏️</span>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="tuteur_prenom_nom">Prénom(s) & nom</label>
                    <input type="text" id="tuteur_prenom_nom" name="tuteur_prenom_nom" value="<?= htmlspecialchars($oldInput['tuteur_prenom_nom'] ?? '') ?>" placeholder="Prénom et nom du tuteur">
                    <?php if (!empty($validation_errors['tuteur_prenom_nom'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['tuteur_prenom_nom']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="tuteur_lien_parente">Lien de parenté</label>
                    <input type="text" id="tuteur_lien_parente" name="tuteur_lien_parente" value="<?= htmlspecialchars($oldInput['tuteur_lien_parente'] ?? '') ?>" placeholder="Ex: Père, Mère, Oncle, etc.">
                    <?php if (!empty($validation_errors['tuteur_lien_parente'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['tuteur_lien_parente']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="tuteur_adresse">Adresse</label>
                    <input type="text" id="tuteur_adresse" name="tuteur_adresse" value="<?= htmlspecialchars($oldInput['tuteur_adresse'] ?? '') ?>" placeholder="Adresse complète du tuteur">
                    <?php if (!empty($validation_errors['tuteur_adresse'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['tuteur_adresse']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="tuteur_telephone">Téléphone</label>
                    <input type="tel" id="tuteur_telephone" name="tuteur_telephone" value="<?= htmlspecialchars($oldInput['tuteur_telephone'] ?? '') ?>" placeholder="Numéro de téléphone du tuteur">
                    <?php if (!empty($validation_errors['tuteur_telephone'])): ?>
                        <div class="error-message"><?= htmlspecialchars($validation_errors['tuteur_telephone']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <?php if ($action === 'edit_waiting'): ?>
                <a href="/apprenants?tab=waiting" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Valider l'apprenant</button>
            <?php else: ?>
                <a href="/apprenants" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary" style="background-color:#009989;">Enregistrer</button>
            <?php endif; ?>
        </div>
    </form>
</div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
    clear_session_messages();
?>
<script>
    
function toggleDropdown(id) {
  document.getElementById(id).classList.toggle("show");
  
  // Fermer le menu si on clique ailleurs
  window.onclick = function(event) {
    if (!event.target.matches('.dropdown-toggle') && !event.target.matches('.dropdown-toggle *')) {
      var dropdowns = document.getElementsByClassName("dropdown-content");
      for (var i = 0; i < dropdowns.length; i++) {
        var openDropdown = dropdowns[i];
        if (openDropdown.classList.contains('show')) {
          openDropdown.classList.remove('show');
        }
      }
    }
  }
}
</script>
<style>
/* Style pour le menu déroulant des actions */
.action-dots {
    position: relative;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-toggle {
    cursor: pointer;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 4px;
    overflow: hidden;
}

.dropdown-content a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: background-color 0.2s;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

/* Afficher le menu au survol */
.dropdown:hover .dropdown-content {
    display: block;
}

</style>
