<?php
$initiales = '';
if (session_has('user')) {
    $user = session_get('user');
    
    // Différencier le traitement selon le rôle
    if ($user['role'] === 'apprenant') {
        // Pour les apprenants, récupérer les données complètes
        $apprenant = App\Controllers\Apprenants\getApprenantById($user['matricule']);
        
        if ($apprenant) {
            // Utiliser le prénom et le nom de l'apprenant
            $prenom = trim($apprenant['prenom'] ?? '');
            $nom = trim($apprenant['nom'] ?? '');
            
            // Prendre la première lettre du prénom et la première lettre du nom
            if (!empty($prenom) && !empty($nom)) {
                $initiales = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
            } else {
                // Fallback si l'un des deux est vide
                $nomComplet = trim($prenom . ' ' . $nom);
                $initiales = strtoupper(substr($nomComplet, 0, 2));
            }
        } else {
            // Fallback si l'apprenant n'est pas trouvé
            $nomComplet = trim($user['nom'] ?? '');
            $mots = explode(' ', $nomComplet);
            
            if (count($mots) >= 2) {
                $initiales = strtoupper(substr($mots[0], 0, 1) . substr($mots[1], 0, 1));
            } else {
                $initiales = strtoupper(substr($nomComplet, 0, 2));
            }
        }
    } else {
        // Pour les admins, garder la logique actuelle
        $nomComplet = trim($user['nom'] ?? '');
        $mots = explode(' ', $nomComplet);
        
        if (count($mots) >= 2) {
            $initiales = strtoupper(substr($mots[0], 0, 1) . substr($mots[1], 0, 1));
        } else {
            $initiales = strtoupper(substr($nomComplet, 0, 2));
        }
    }
}   
$promotionActive = App\Controllers\Promotions\get_active_promotion();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sonatel Academy' ?></title>
    <!-- Inclure les fichiers CSS -->
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
    <?php if (isset($additionalStyles)) echo $additionalStyles; ?>
</head>
<style>
    .initials-avatar {
    background-color: #007bff !important;
    color: white;
    font-weight: bold;
    border-radius: 50% !important;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    text-transform: uppercase;
}
</style>
<body>
    <!-- Menu toggle button for mobile -->
    <div class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </div>   
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-container">
                <div class="orange-text">Orange Digital Center</div>
                <div class="logo">
                    <div class="sonatel-text">sonatel</div>
                    <div class="orange-square"></div>
                </div>
                <div class="promotion-badge">
                    Promotion <?= isset($promotionActive['date_debut']) ? date('Y', strtotime($promotionActive['date_debut'])) : 'Non spécifiée' ?>
                </div>

            </div>
        </div>   
        <div class="sidebar-menu">
        <?php if ( session_has('user') && session_get('user')['role']==='admin'):?>
            <a href="dashboard" class="menu-item <?= $currentPage == 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="promotions" class="menu-item <?= $currentPage == 'promotions' ? 'active' : '' ?>">
                <i class="fas fa-bookmark"></i>
                <span>Promotions</span>
            </a>
            <a href="referentiels" class="menu-item <?= $currentPage == 'referentiels' ? 'active' : '' ?>">
                <i class="fas fa-book"></i>
                <span>Référentiels</span>
            </a>
            <a href="apprenants" class="menu-item <?= $currentPage == 'apprenants' ? 'active' : '' ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Apprenants</span>
            </a>
            <a href="" class="menu-item <?= $currentPage == 'presences' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-check"></i>
                <span>Gestion des présences</span>
            </a>
            <a href="kits" class="menu-item <?= $currentPage == 'kits' ? 'active' : '' ?>">
                <i class="fas fa-laptop"></i>
                <span>Kits & Laptops</span>
            </a>
            <a href="rapports" class="menu-item <?= $currentPage == 'rapports' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Rapports & Stats</span>
            </a>
        <?php endif; ?>
            <?php if (session_has('user') && session_get('user')['role']==='apprenant'):?>
                <a href="profile_apprenant" class="menu-item <?= $currentPage == 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-user"></i>
                <span>Mon Profil</span>
            </a>
            <?php endif; ?>
            <br><br><br><br><br><br><br><br>
            <a href="logout" class="menu-item <?= $currentPage == 'rapports' ? 'active' : '' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                </svg>
                <span>Deconnexion</span>
            </a>
        </div>
    </div> 
    <!-- Main content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search">
            </div>
            
            <div class="user-section">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                </div>
                
                <div class="user-profile">
                    <div class="user-info">

                    <div class="user-name">
                        <?php 
                        if (session_has('user')) {
                            $user = session_get('user');
                            if ($user['role'] === 'apprenant') {
                                $apprenant = App\Controllers\Apprenants\getApprenantById($user['matricule']);
                                if ($apprenant) {
                                    echo htmlspecialchars($apprenant['prenom'] . ' ' . $apprenant['nom']);
                                } else {
                                    echo htmlspecialchars($user['nom']);
                                }
                            } else {
                                echo htmlspecialchars($user['nom']);
                            }
                        }
                        ?>
                    </div>
                        <div class="user-role"><?=session_has('user') ? htmlspecialchars(session_get('user')['role']) : '' ?></div>
                    </div>
                    
                    <div class="avatar">
                        <?php if (!empty($initiales)) : ?>
                            <div class="initials-avatar"><?= $initiales ?></div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
        

        <div class="content-wrapper">
            <?php if (isset($contentHeader)): ?>
                <div class="content-header">
                    <?= $contentHeader ?>
                </div>
            <?php endif; ?>
            
           
            <div class="content-container">
                <?php if (isset($content)): ?>
                    <?= $content ?>
                <?php else: ?>
                    <p>Contenu non disponible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
 
    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Responsive adjustments
        function checkWidth() {
            if (window.innerWidth <= 576) {
                document.getElementById('sidebar').classList.remove('active');
            }
        }
        
        window.addEventListener('resize', checkWidth);
        checkWidth();
    </script>
    <?php if (isset($additionalScripts)) echo $additionalScripts; ?>
</body>
</html>