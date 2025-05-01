<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Apprenant</title>
    <link rel="stylesheet" href="assets/css/details.css">
</head>
<body>
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/apprenants">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"></path>
                </svg>
                Retour sur la liste
            </a>
        </div>
        <!-- Header with Title -->
        <div class="page-header">
            <div class="title-section">
                <h1>Apprenants <span class="title-details">/ Détails</span></h1>
            </div>
        </div>
        <!-- Profile Section -->
        <div class="profile-section">
        <div class="profile-image">
            <?php if (!empty($apprenant['photo'])): ?>
                <img src="/uploads/apprenants/<?= htmlspecialchars($apprenant['photo']) ?>" alt="Photo de profil">
            <?php else: ?>
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Avatar par défaut">
             <?php endif; ?>
        </div>
            <div class="profile-info">
                <h2 class="profile-name"><?= htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></h2>
                <span class="profile-spec"><?= htmlspecialchars($referentielNom) ?></span>
                
                <div class="profile-details">
                    <div class="detail-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path>
                        </svg>
                        <span class="detail-label">Tel:</span>
                        <span><?= htmlspecialchars($apprenant['telephone'] ?? 'Non renseigné') ?></span>
                    </div>
                    <div class="detail-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <span class="detail-label">Email:</span>
                        <span><?= htmlspecialchars($apprenant['email'] ?? 'Non renseigné') ?></span>
                    </div>
                    <div class="detail-item">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span class="detail-label">Adresse:</span>
                        <span><?= htmlspecialchars($apprenant['adresse'] ?? 'Non renseigné') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-icon stat-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div>
                    <div class="stat-number"><?= $stats['presences'] ?? 0 ?></div>
                    <div class="stat-label">Présence(s)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-orange">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div>
                    <div class="stat-number"><?= $stats['retards'] ?? 0 ?></div>
                    <div class="stat-label">Retard(s)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-red">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <div class="stat-number"><?= $stats['absences'] ?? 0 ?></div>
                    <div class="stat-label">Absence(s)</div>
                </div>
            </div>
        </div>
        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs">
                <div class="tab active">Programme & Modules</div>
                <div class="tab">Total absences par étudiant</div>
            </div>
        </div>
        <!-- Modules Grid -->
        <div class="modules-grid">
            <?php 
            $accentColors = ['accent-blue', 'accent-orange', 'accent-green', 'accent-purple', 'accent-red', 'accent-teal'];
            $i = 0;
            
            if (!empty($modules)):
                foreach ($modules as $module): 
                    $colorClass = $accentColors[$i % count($accentColors)];
                    $i++;
            ?>
            <div class="module-card">
                <div class="module-header">
                    <div class="module-duration">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?= htmlspecialchars($module['duree'] ?? '30 jours') ?>
                    </div>
                    <div class="module-menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </div>
                    <h3 class="module-title"><?= htmlspecialchars($module['titre'] ?? 'Module') ?></h3>
                    <p class="module-description"><?= htmlspecialchars($module['description'] ?? 'Description du module') ?></p>
                    <span class="module-tag"><?= htmlspecialchars($module['niveau'] ?? 'Débutant') ?></span>
                </div>
                <div class="module-details">
                    <div class="module-info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?= htmlspecialchars($module['date_debut'] ?? date('d M Y')) ?>
                    </div>
                    <div class="module-info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?= htmlspecialchars($module['heure'] ?? '12:45 pm') ?>
                    </div>
                </div>
                <div class="accent-line <?= $colorClass ?>"></div>
            </div>
            <?php 
                endforeach;
            else:
                // Modules par défaut si aucun n'est disponible
                $defaultModules = [
                    [
                        'titre' => 'Algorithme & Langage C',
                        'description' => 'Complexité algorithmique & pratique codage en langage C',
                        'duree' => '30 jours',
                        'date_debut' => '15 Février 2025',
                        'heure' => '12:45 pm',
                        'niveau' => 'Débutant',
                        'color' => 'accent-blue'
                    ],
                    [
                        'titre' => 'Frontend 1: Html, Css & JS',
                        'description' => 'Création d\'interfaces de design avec animations avancées !',
                        'duree' => '15 jours',
                        'date_debut' => '24 Mars 2025',
                        'heure' => '12:45 pm',
                        'niveau' => 'Débutant',
                        'color' => 'accent-orange'
                    ],
                    [
                        'titre' => 'Backend 1: PHP avancé & POO',
                        'description' => 'Programmation orientée objet et concepts avancés en PHP',
                        'duree' => '20 jours',
                        'date_debut' => '23 Mar 2024',
                        'heure' => '12:45 pm',
                        'niveau' => 'Débutant',
                        'color' => 'accent-green'
                    ],
                    [
                        'titre' => 'Frontend 2: JS & TS + Tailwind',
                        'description' => 'JavaScript avancé, TypeScript et framework CSS Tailwind',
                        'duree' => '15 jours',
                        'date_debut' => '23 Mar 2024',
                        'heure' => '12:45 pm',
                        'niveau' => 'Débutant',
                        'color' => 'accent-purple'
                    ],
                    [
                        'titre' => 'Backend 2: Laravel & SOLID',
                        'description' => 'Framework Laravel et principes SOLID',
                        'duree' => '30 jours',
                        'date_debut' => '23 Mar 2024',
                        'heure' => '12:45 pm',
                        'niveau' => 'Débutant',
                        'color' => 'accent-teal'
                    ],
                    [
                        'titre' => 'Frontend 3: ReactJs',
                        'description' => 'Développement d\'applications avec React',
                        'duree' => '15 jours',
                        'date_debut' => '23 Mar 2024',
                        'heure' => '12:45 pm',
                        'niveau' => 'Débutant',
                        'color' => 'accent-red'
                    ]
                ];
                
                foreach ($defaultModules as $module):
            ?>
            <div class="module-card">
                <div class="module-header">
                    <div class="module-duration">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?= htmlspecialchars($module['duree']) ?>
                    </div>
                    <div class="module-menu">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                        </svg>
                    </div>
                    <h3 class="module-title"><?= htmlspecialchars($module['titre']) ?></h3>
                    <p class="module-description"><?= htmlspecialchars($module['description']) ?></p>
                    <span class="module-tag"><?= htmlspecialchars($module['niveau']) ?></span>
                </div>
                <div class="module-details">
                    <div class="module-info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?= htmlspecialchars($module['date_debut']) ?>
                    </div>
                    <div class="module-info">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?= htmlspecialchars($module['heure']) ?>
                    </div>
                </div>
                <div class="accent-line <?= $module['color'] ?>"></div>
            </div>
            <?php 
                endforeach;
            endif; 
            ?>
        </div>
    </div>
</body>
</html>