<?php
session_init();
$user = session_get('user');
$id = $user['matricule'];
$apprenant = App\Controllers\Apprenants\getApprenantById($id);

if (!$apprenant) {
    echo "L'apprenant n'a pas été trouvé.";
    die;
}
$promotion = App\Controllers\Promotions\get_promotion_by_id($apprenant['promotion_id']);
if ($promotion && isset($promotion['referentiels'][0])) {
    $referentiel = App\Controllers\Referentiels\get_referentiel_by_id($promotion['referentiels'][0]);
} else {
    $referentiel = null; 
}
$qrData = sprintf(
    "%s %s %s %s %s %s",
    $apprenant['nom'] ?? '',
    $apprenant['prenom'] ?? '',
    $apprenant['email'] ?? '',
    $apprenant['telephone'] ?? '',
    $promotion['nom'] ?? '',
    $referentiel['nom'] ?? ''
);
// Générer l'URL du QR code
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($qrData) . "&size=300x300";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .head {
            background-color: #e35209;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .head h1 {
            font-size: 24px;
            font-weight: bold;
        }
        
        .profile-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
        }
        
        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            margin-right: 20px;
            border: 1px solid #e0e0e0;
        }
        
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-info h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .profile-info p {
            color: #666;
            margin-bottom: 5px;
        }
        
        .profile-info .email, .profile-info .id {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .dashboard-container {
            display: flex;
            gap: 20px;
        }
        
        .left-column {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 60%;
        }
        
        .right-column {
            width: 35%;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            height: auto;
        }
        
        .presence-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            gap: 10px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            border-radius: 5px;
            width: 32%;
        }
        
        .stat-present {
            background-color: #e8f5e9;
        }
        
        .stat-late {
            background-color: #fff8e1;
        }
        
        .stat-absent {
            background-color: #ffebee;
        }
        
        .stat-item .number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-item .label {
            color: #666;
            font-size: 14px;
        }
        
        .donut-chart {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 15px;
        }
        
        .chart-container {
            width: 120px;
            height: 120px;
            position: relative;
        }
        
        .donut-chart svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }
        
        .circle {
            fill: none;
            stroke-width: 30;
        }
        
        .present-circle {
            stroke: #00b86b;
            stroke-dasharray: 100 100;
        }
        
        .legend {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
            gap: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
        }
        
        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            margin-right: 8px;
        }
        
        .present-color {
            background-color: #00b86b;
        }
        
        .late-color {
            background-color: #ffc107;
        }
        
        .absent-color {
            background-color: #f44336;
        }
        
        .qr-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }
        
        .qr-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .qr-header h3 {
            font-size: 18px;
            color: #333;
        }
        
        .qr-code {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
        }
        
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        
        .qr-footer {
            text-align: center;
        }
        
        .qr-footer p {
            color: #666;
            margin-bottom: 5px;
        }
        
        .qr-footer .id {
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-header .icon {
            background-color: #e35209;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
            font-size: 20px;
        }
        
        .email-icon, .id-icon {
            margin-right: 10px;
            color: #666;
        }
        
        .qr-icon {
            background-color: #ff8c00;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column-reverse; /* Afficher le QR en haut */
            }
            
            .left-column, .right-column {
                width: 100%;
            }
            
            .qr-card {
                margin-bottom: 20px;
            }
            
            /* S'assurer que le QR code est visible */
            .qr-code {
                display: block;
                width: 150px;
                height: 150px;
            }
            
            .qr-code img {
                display: block;
                width: 100%;
                height: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="head">
            <h1>Tableau de Bord</h1>
        </div>
       
        <div class="profile-card">
            <div class="profile-image">
                <?php if (!empty($apprenant['photo'])): ?>
                    <img src="/uploads/apprenants/<?= htmlspecialchars($apprenant['photo']) ?>" alt="Photo de profil">
                <?php else: ?>
                        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Avatar par défaut">
                    <?php endif; ?>
            </div>
            <div class="profile-info">
                <h2><?= $apprenant['prenom'] . ' ' . $apprenant['nom'] ?></h2>
                <p><?= $referentiel['nom'] ?? 'DevWeb' ?></p>
                <div class="email">
                    <span class="email-icon">✉️</span>
                    <span><?= $apprenant['email'] ?? 'sembenpape4@gmail.com' ?></span>
                </div>
                <div class="id">
                    <span class="id-icon">🆔</span>
                    <span>#<?= $id ?? 'DW25013' ?></span>
                </div>
            </div>
        </div>
        
        <div class="dashboard-container">
            <div class="left-column">
                <div class="card">
                    <div class="card-header">
                        <div class="icon">📅</div>
                        <h3>Présences</h3>
                    </div>
                    <div class="presence-stats">
                        <div class="stat-item stat-present">
                            <div class="number">39</div>
                            <div class="label">Présent</div>
                        </div>
                        <div class="stat-item stat-late">
                            <div class="number">0</div>
                            <div class="label">Retard</div>
                        </div>
                        <div class="stat-item stat-absent">
                            <div class="number">0</div>
                            <div class="label">Absent</div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="icon">🕒</div>
                        <h3>Répartition</h3>
                    </div>
                    <div class="donut-chart">
                        <div class="chart-container">
                            <svg viewBox="0 0 100 100">
                                <circle class="circle present-circle" cx="50" cy="50" r="35"></circle>
                            </svg>
                        </div>
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-color present-color"></div>
                                <span>Présents</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color late-color"></div>
                                <span>Retards</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color absent-color"></div>
                                <span>Absents</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="right-column">
                <div class="qr-card">
                    <div class="qr-icon">📲</div>
                    <div class="qr-header">
                        <h3>Scanner pour la présence</h3>
                    </div>
                    <div class="qr-code">
                        <img src="<?= $qrCodeUrl ?>" alt="QR Code">
                    </div>
                    <div class="qr-footer">
                        <p>Code de présence personnel</p>
                        <p class="id"><?= $id ?? 'DW25013' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>