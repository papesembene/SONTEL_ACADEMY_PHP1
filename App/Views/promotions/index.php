<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Promotions</title>
    <link rel="stylesheet" href="/assets/css/promotions.css?v=1.2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (session_has('success_message')): ?>
        <div class="global-alert success">
            <i class="fas fa-check-circle"></i>
            <p><?= htmlspecialchars(session_get('success_message')['content']) ?></p>
        </div>
    <?php endif; ?>
    <div class="container">
        <div class="header">
            <div class="title-section">
                <h1>Promotion</h1>
                <p>Gérer les promotions de l'école</p>
            </div>
            <?php if ($action !== 'add'): ?>
                <a href="/promotions?action=add" class="add-button">
                    <i class="fas fa-plus"></i> Ajouter une promotion
                </a>
            <?php else: ?>
                <a href="/promotions" class="add-button" style="background-color: #6c757d;">
                    <i class="fas fa-arrow-left"></i> Retour aux promotions
                </a>
            <?php endif; ?>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info">
                    <h2><?=App\Controllers\Promotions\get_active_promotion_apprenant_count()?></h2>
                    <p>Apprenants</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h2><?=App\Controllers\Promotions\get_active_promotion_referentiel_count()?></h2>
                    <p>Référentiels</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h2>1</h2>
                    <p>Promotion active</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h2><?=$nbPromos?></h2>
                    <p>Total promotions</p>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-folder"></i>
                </div>
            </div>
        </div>

        <?php if ($action === 'add'): ?>
            <!-- Formulaire d'ajout de promotion -->
            <div class="promotion-form-container">
                <div class="form-header">
                    <h2 class="form-title">Créer une nouvelle promotion</h2>
                    <p class="form-subtitle">Remplissez les informations ci-dessous pour créer une nouvelle promotion.</p>
                </div>

                <form action="/promotions/create" method="POST" enctype="multipart/form-data" class="promotion-form">
                    <input type="hidden" name="action" value="create">
                    <div class="form-body">
                        <!-- Nom -->
                        <div class="form-group">
                            <label for="promotionName">Nom de la promotion</label>
                            <div class="input-icon-wrapper">
                                <input type="text" id="promotionName" name="nom" class="form-control <?= isset($errors['nom']) ? 'input-error' : '' ?>"
                                    placeholder="Ex: Promotion 7"
                                    value="<?= htmlspecialchars($oldInput['nom'] ?? '') ?>">
                                <?php if (isset($errors['nom'])): ?>
                                    <span class="error-icon">!</span>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($errors['nom'])): ?>
                                <div class="error-message"><?= implode('<br>', $errors['nom']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Dates -->
                        <div class="form-row">
                            <!-- Date de début -->
                            <div class="form-group">
                                <label for="startDate">Date de début</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" id="startDate" name="datedebut" class="form-control <?= isset($errors['datedebut']) ? 'input-error' : '' ?>"
                                        placeholder="dd/mm/yyyy"
                                        value="<?= htmlspecialchars($oldInput['datedebut'] ?? '') ?>">
                                    <?php if (isset($errors['datedebut'])): ?>
                                        <span class="error-icon">!</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (isset($errors['datedebut'])): ?>
                                    <div class="error-message"><?= implode('<br>', $errors['datedebut']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Date de fin -->
                            <div class="form-group">
                                <label for="endDate">Date de fin</label>
                                <div class="input-icon-wrapper">
                                    <input type="text" id="endDate" name="datefin" class="form-control <?= isset($errors['datefin']) ? 'input-error' : '' ?>"
                                        placeholder="dd/mm/yyyy"
                                        value="<?= htmlspecialchars($oldInput['datefin'] ?? '') ?>">
                                    <?php if (isset($errors['datefin'])): ?>
                                        <span class="error-icon">!</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (isset($errors['datefin'])): ?>
                                    <div class="error-message"><?= implode('<br>', $errors['datefin']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Photo -->
                        <div class="form-group">
                            <label for="photoInput">Photo de la promotion</label>
                            <div class="file-upload-wrapper">
                                <label for="photoInput" class="upload-area <?= isset($errors['photo']) ? 'upload-area-error' : '' ?>">
                                    <span class="upload-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15" stroke="#667085" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M17 8L12 3M12 3L7 8M12 3V15" stroke="#667085" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span class="upload-button">Choisir un fichier</span>
                                    <span class="upload-text">ou glisser-déposer ici</span>
                                </label>
                                <input type="file" id="photoInput" name="photo" class="file-input"
                                    accept="image/jpeg,image/png,image/jpg,image/webp,image/gif">
                            </div>
                            <?php if (isset($errors['photo'])): ?>
                                <div class="error-message"><?= implode('<br>', $errors['photo']) ?></div>
                            <?php endif; ?>
                            <div class="upload-info">Format JPG, PNG, WEBP, GIF. Taille max 2MB</div>
                        </div>

                        <!-- Référentiels avec cases à cocher -->
                        <div class="form-group full-width">
                            <label>Référentiels</label>
                            <?php if (isset($errors['referentiels'])): ?>
                                <div class="error-message"><?= implode('<br>', $errors['referentiels']) ?></div>
                            <?php endif; ?>
                            
                            <div class="referentiels-container">
                                <?php 
                                $oldReferentiels = $oldInput['referentiels'] ?? [];
                                $totalItems = count($referentiels);
                                $itemsPerColumn = $totalItems > 0 ? ceil($totalItems / 3) : 1; 
                                $columns = array_chunk($referentiels, $itemsPerColumn, true);
                                
                                foreach ($columns as $column): ?>
                                    <div class="referentiels-column">
                                        <?php foreach ($column as $ref): ?>
                                            <div class="referentiel-item">
                                                <label class="checkbox-container">
                                                    <input type="checkbox" name="referentiels[]" value="<?= $ref['id'] ?>"
                                                        <?= in_array($ref['id'], $oldReferentiels) ? 'checked' : '' ?>>
                                                    <span class="checkmark"></span>
                                                    <span class="referentiel-label"><?= htmlspecialchars($ref['nom']) ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <a href="/promotions" class="btn btn-cancel">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer la promotion</button>
                    </div>
                </form>
            </div>    
        <?php else: ?>
            <!-- Affichage normal des promotions -->
            <form method="GET" action="/promotions" id="filterForm">
                <div class="search-filter-section">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher..." name="search" 
                            value="<?= htmlspecialchars($search ?? '') ?>">
                        <input type="hidden" name="view" value="<?= htmlspecialchars($viewMode) ?>">
                    </div>
                    <div class="filter-section">
                        <select class="filter-dropdown" name="status">
                            <option value="">Tous</option>
                            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Actifs</option>
                            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactifs</option>
                        </select>
                  
                    <button type="submit" class="filter-submit-btn" style="background-color: #ff6b1b; color:white; font-size:10px ;width:90px; margin:5px;border:none; border-radius:5px">Rechercher</button>
                </div>
                        <div class="view-buttons">
                            <a href="<?= App\Controllers\Promotions\buildUrl(['view' => 'grid']) ?>" class="view-button <?= ($viewMode === 'grid' ? 'active' : '') ?>">
                                <i class="fas fa-th-large"></i> Grille
                            </a>
                            <a href="<?= App\Controllers\Promotions\buildUrl(['view' => 'list']) ?>" class="view-button <?= ($viewMode === 'list' ? 'active' : '') ?>">
                                <i class="fas fa-list"></i> Liste
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <?php if ($viewMode === 'grid'): ?>
                <!-- Affichage en grille -->
                <div class="promotions-grid">
                    <?php if (!empty($promos)) : ?>
                        <?php foreach ($promos as $promotion) : ?>
                            <?php
                            $defaults = [
                                'id' => '0',
                                'nom' => 'Promotion sans nom',
                                'statut' => 'inactif',
                                'date_debut' => 'Non définie',
                                'date_fin' => 'Non définie',
                                'photo' => '/assets/default-promo.png',
                                'nbr_etudiants' => 0
                            ];
                            $promotion = array_merge($defaults, $promotion);

                            $name = $promotion['nom'] ?? 'Promotion';
                            $words = explode(' ', $name);
                            $initials = count($words) >= 2 
                                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                : strtoupper(substr($name, 0, 2));

                            $colors = ['#00a67d', '#ff6b1b', '#2ecc71', '#e74c3c', '#3498db'];
                            $colorIndex = hexdec(substr(md5($name), 0, 1)) % count($colors);
                            $bgColor = $colors[$colorIndex];
                            ?>
                            <div class="promotion-card">
                                <div class="status-bar">
                                    <span class="status-badge <?= $promotion['statut'] === 'active' ? 'active' : 'inactive' ?>">
                                        <?= htmlspecialchars(ucfirst($promotion['statut'])) ?>
                                    </span>
                                    <a href="/promotions/toggle?promotion_id=<?= $promotion['id'] ?>&<?= http_build_query(['view' => $viewMode]) ?>" 
                                        class="power-button <?= $promotion['statut'] === 'active' ? 'active' : '' ?>"
                                        <?= $promotion['statut'] === 'active' ? 'disabled style="pointer-events: none; opacity: 0.5;"' : '' ?>>
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                </div>

                                <div class="promotion-content">
                                    <div class="promotion-header">
                                        <div class="promotion-logo initials-logo" style="background-color: <?= $bgColor ?>">
                                            <?= $initials ?>
                                        </div>
                                        <h3 class="promotion-title">
                                            <?= htmlspecialchars($name) ?>
                                        </h3>
                                    </div>
                                    <div class="promotion-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <span>
                                            <?= htmlspecialchars($promotion['date_debut']) ?> - 
                                            <?= htmlspecialchars($promotion['date_fin']) ?>
                                        </span>
                                    </div>
                                    <div class="promotion-stats">
                                        <i class="fas fa-user-graduate"></i>
                                        <span>
                                            <?= htmlspecialchars($promotion['nbr_etudiants']) ?> apprenants
                                        </span>
                                    </div>
                                    <div class="promotion-footer">
                                        <a href="/promotions/<?= $promotion['id'] ?>" class="details-link">
                                            Voir détails <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="no-promotions">
                            <p>Aucune promotion disponible pour le moment</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination pour la grille -->
                <?php if ($totalItems > 0) : ?>
                <div class="pagination">
                    <div class="page-selector">
                        <span>page</span>
                        <select onchange="window.location.href='<?= App\Controllers\Promotions\buildUrl(['per_page' => 'REPLACE', 'page' => 1]) ?>'.replace('REPLACE', this.value)">
                            <?php foreach ([5, 10, 15, 20] as $pageSize) : ?>
                                <option value="<?= $pageSize ?>" <?= $itemsPerPage == $pageSize ? 'selected' : '' ?>><?= $pageSize ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="pagination-info">
                        <?= $startIndex ?> à <?= $endIndex ?> pour <?= $totalItems ?>
                    </div>
                    <div class="pagination-controls">
                        <a href="<?= App\Controllers\Promotions\buildUrl(['page' => max(1, $currentPage - 1)]) ?>" 
                            class="pagination-button nav <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <a href="<?= App\Controllers\Promotions\buildUrl(['page' => $i]) ?>" 
                                class="pagination-button <?= $i == $currentPage ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        <a href="<?= App\Controllers\Promotions\buildUrl(['page' => min($totalPages, $currentPage + 1)]) ?>" 
                            class="pagination-button nav <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            <?php else : ?>
                <!-- Affichage en liste -->
                <div class="promotions-list-container">
                    <table class="promotions-list-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Promotion</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Référentiel</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($promos)) : ?>
                                <?php foreach ($promos as $promotion) : ?>
                                    <?php
                                    $defaults = [
                                        'id' => '0',
                                        'nom' => 'Promotion sans nom',
                                        'statut' => 'inactif',
                                        'date_debut' => 'Non définie',
                                        'date_fin' => 'Non définie',
                                        'photo' => '/assets/default-promo.png',
                                        'nbr_etudiants' => 0,
                                        'referentiels' => []
                                    ];
                                    $promotion = array_merge($defaults, $promotion);
                                    
                                    $name = $promotion['nom'] ?? 'Promotion';
                                    $words = explode(' ', $name);
                                    $initials = count($words) >= 2 
                                        ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                        : strtoupper(substr($name, 0, 2));
                                    
                                    $colors = ['#00a67d', '#ff6b1b', '#2ecc71', '#e74c3c', '#3498db'];
                                    $colorIndex = hexdec(substr(md5($name), 0, 1)) % count($colors);
                                    $bgColor = $colors[$colorIndex];
                                    
                                    $isActive = $promotion['statut'] === 'active';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="promotion-logo initials-logo" style="background-color: <?= $bgColor ?>">
                                                <?= $initials ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($promotion['nom']) ?></td>
                                        <td><?= htmlspecialchars($promotion['date_debut']) ?></td>
                                        <td><?= htmlspecialchars($promotion['date_fin']) ?></td>
                                        <td>
                                            <div class="ref-badges">
                                                <?php if (!empty($promotion['referentiels'])) : ?>
                                                    <?php foreach ($promotion['referentiels'] as $refId) : ?>
                                                        <?php
                                                        $refNom = isset($referentielMap[$refId]) ? $referentielMap[$refId] : 'Inconnu';
                                                        $refLower = strtolower(trim($refNom));
                                                        $refClass = '';

                                                        if (strpos($refLower, 'dev web') !== false) {
                                                            $refClass = 'dev-web';
                                                        } elseif (strpos($refLower, 'dev mobile') !== false) {
                                                            $refClass = 'dev-web';
                                                        } elseif (strpos($refLower, 'ref dig') !== false) {
                                                            $refClass = 'ref-dig';
                                                        } elseif (strpos($refLower, 'dev data') !== false) {
                                                            $refClass = 'dev-data';
                                                        } elseif (strpos($refLower, 'aws') !== false) {
                                                            $refClass = 'aws';
                                                        } elseif (strpos($refLower, 'hackeuse') !== false) {
                                                            $refClass = 'hackeuse';
                                                        } else {
                                                            $refClass = 'ref-' . preg_replace('/[^a-z0-9]/', '-', $refLower);
                                                        }
                                                        ?>
                                                        <span class="ref-badge <?= $refClass ?>">
                                                            <?= htmlspecialchars(strtoupper($refNom)) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                <?php else : ?>
                                                    <span class="ref-badge">NON DÉFINI</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $isActive ? 'active' : 'inactive' ?>">
                                                <?= $isActive ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="actions-cell">
                                            <!-- Bouton power pour activer/désactiver -->
                                            <a href="/promotions/toggle?promotion_id=<?= $promotion['id'] ?>&<?= http_build_query(['view' => $viewMode]) ?>" 
                                               class="power-button <?= $isActive ? 'active' : '' ?>"
                                        <?= $promotion['statut'] === 'active' ? 'disabled style="pointer-events: none; opacity: 0.5;"' : '' ?>>
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="no-promotions">
                                        <p>Aucune promotion disponible pour le moment</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination pour la liste -->
                    <?php if ($totalItems > 0) : ?>
                    <div class="pagination">
                        <div class="page-selector">
                            <span>page</span>
                            <select onchange="window.location.href='<?= App\Controllers\Promotions\buildUrl(['per_page' => 'REPLACE', 'page' => 1]) ?>'.replace('REPLACE', this.value)">
                                <?php foreach ([5, 10, 15, 20] as $pageSize) : ?>
                                    <option value="<?= $pageSize ?>" <?= $itemsPerPage == $pageSize ? 'selected' : '' ?>><?= $pageSize ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="pagination-info">
                            <?= $startIndex ?> à <?= $endIndex ?> pour <?= $totalItems ?>
                        </div>
                        <div class="pagination-controls">
                            <a href="<?= App\Controllers\Promotions\buildUrl(['page' => max(1, $currentPage - 1)]) ?>" 
                                class="pagination-button nav <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                <a href="<?= App\Controllers\Promotions\buildUrl(['page' => $i]) ?>" 
                                    class="pagination-button <?= $i == $currentPage ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            <a href="<?= App\Controllers\Promotions\buildUrl(['page' => min($totalPages, $currentPage + 1)]) ?>" 
                                class="pagination-button nav <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php
    clear_session_messages();
    ?>
</body>
</html>

