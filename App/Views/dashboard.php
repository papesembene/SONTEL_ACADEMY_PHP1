<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sonatel Academy</title>
    <style>
        :root {
            --primary-color: #00a69b;
            --secondary-color: #ff7900;
            --light-primary: #c9ecec;
            --light-secondary: #ffd8b5;
            --text-color: #333333;
            --light-text: #ffffff;
            --border-color: #e0e0e0;
            --light-bg: #f8f9fa;
            --card-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            --transition-speed: 0.3s;
            --border-radius: 12px;
            --border-radius-sm: 6px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
        }

        /* .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        } */

        .stats-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: var(--secondary-color);
            color: white;
            border-radius: var(--border-radius);
            padding: 15px 20px;
            flex: 1;
            min-width: 200px;
            display: flex;
            align-items: center;
            box-shadow: var(--card-shadow);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            font-size: 18px;
        }

        .stat-info h3 {
            font-size: 16px;
            font-weight: normal;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .info-card {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .card-options {
            text-align: right;
            margin-bottom: 15px;
        }

        .more-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #777;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container .orange-text {
            color: #ff7900;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .sonatel-text {
            color: #00a69b;
            font-size: 28px;
            font-weight: bold;
        }

        .orange-square {
            width: 20px;
            height: 20px;
            background-color: #ff7900;
            margin-left: 5px;
        }

        .slogan {
            font-size: 12px;
            color: #777;
            text-align: center;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .metrics {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .metric-item {
            flex: 1;
        }

        .metric-value {
            font-size: 28px;
            font-weight: bold;
        }

        .metric-label {
            font-size: 12px;
            color: #777;
        }

        .chart-card {
            padding: 20px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: bold;
        }

        .chart-legend {
            display: flex;
            gap: 15px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            font-size: 12px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            margin-right: 5px;
            border-radius: 2px;
        }

        .legend-presence {
            background-color: var(--primary-color);
        }

        .legend-retard {
            background-color: var(--light-primary);
        }

        .legend-absence {
            background-color: var(--secondary-color);
        }

        .chart-filter {
            display: flex;
            align-items: center;
        }

        .filter-dropdown {
            padding: 5px 10px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 12px;
            background-color: white;
        }

        .chart-container {
            width: 100%;
            height: 250px;
            position: relative;
        }

        .month-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .month-label {
            font-size: 12px;
            color: #777;
            flex: 1;
            text-align: center;
        }

        .month-label.active {
            font-weight: bold;
            color: #333;
        }

        .bar-chart {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            height: 220px;
        }

        .bar-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
        }

        .bar {
            width: 70%;
            position: relative;
        }

        .bar-absence {
            background-color: var(--secondary-color);
            height: 20%;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .bar-retard {
            background-color: var(--light-primary);
            height: 20%;
        }

        .bar-presence {
            background-color: var(--primary-color);
            height: 60%;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .bar-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .stats-dashboard {
            margin-top: 20px;
        }

        .stats-container {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--card-shadow);
        }

        .stats-header {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .stats-logo {
            text-align: center;
        }

        .stats-logo .orange-text {
            color: #ff7900;
            font-size: 12px;
        }

        .stats-logo .sonatel-text {
            color: #00a69b;
            font-size: 24px;
        }

        .stats-metrics {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .metric-card {
            flex: 1;
            min-width: 180px;
            text-align: center;
        }

        .donut-chart {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            position: relative;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .donut-chart.full {
            background: conic-gradient(var(--primary-color) 0% 100%);
        }

        .donut-chart.half {
            background: conic-gradient(var(--primary-color) 0% 56%, var(--light-primary) 56% 100%);
        }

        .donut-chart.split {
            background: conic-gradient(var(--secondary-color) 0% 35%, var(--primary-color) 35% 100%);
        }

        .donut-hole {
            width: 80px;
            height: 80px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .donut-percent {
            font-size: 22px;
            font-weight: bold;
        }

        .percent-symbol {
            font-size: 12px;
        }

        .metric-title {
            font-size: 14px;
            color: #777;
            margin-bottom: 5px;
        }

        .metric-title.orange {
            color: var(--secondary-color);
            font-weight: bold;
        }

        .metric-subtitle {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .location-text {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }

        .gender-indicator {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .gender-icon {
            margin: 0 5px;
            color: #777;
        }

        .centers-count {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-metrics {
                gap: 15px;
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                flex-direction: column;
            }
            
            .stat-card {
                width: 100%;
            }
            
            .stats-metrics {
                flex-direction: column;
                align-items: center;
            }
            
            .metric-card {
                width: 100%;
                max-width: 300px;
            }
            
            .chart-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .chart-legend {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .metrics {
                flex-direction: column;
                gap: 15px;
            }
            
            .month-labels {
                display: none;
            }
            
            .bar-chart {
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .bar-group {
                min-width: 40px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3>Apprenants</h3>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3>Référentiels</h3>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h3>Stagiaires</h3>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3>Permanent</h3>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Info Card -->
            <div class="card info-card">
                <div class="card-options">
                    <button class="more-btn">&#8230;</button>
                </div>
                
                <div class="logo-container">
                    <div class="orange-text">Orange Digital Center</div>
                    <div class="logo">
                        <div class="sonatel-text">sonatel</div>
                        <div class="orange-square"></div>
                    </div>
                </div>
                
                <div class="slogan">
                    Transformer la vie des personnes grâce à nos solutions technologiques innovantes et accessibles.
                </div>
                
                <div class="metrics">
                    <div class="metric-item">
                        <div class="metric-value">180</div>
                        <div class="metric-label">Apprenants</div>
                    </div>
                    
                    <div class="metric-item">
                        <div class="metric-value">2025</div>
                        <div class="metric-label">Promotion</div>
                    </div>
                    
                    <div class="metric-item">
                        <div class="metric-value">10</div>
                        <div class="metric-label">Mois</div>
                    </div>
                </div>
            </div>
            
            <!-- Chart Card -->
            <div class="card chart-card">
                <div class="chart-header">
                    <div class="chart-title">Présences statistiques</div>
                    
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color legend-presence"></div>
                            <span>Présence</span>
                        </div>
                        
                        <div class="legend-item">
                            <div class="legend-color legend-retard"></div>
                            <span>Retard</span>
                        </div>
                        
                        <div class="legend-item">
                            <div class="legend-color legend-absence"></div>
                            <span>Absences</span>
                        </div>
                    </div>
                    
                    <div class="chart-filter">
                        <select class="filter-dropdown">
                            <option>This Month</option>
                            <option>Last Month</option>
                            <option>This Year</option>
                        </select>
                    </div>
                </div>
                
                <div class="chart-container">
                    <div class="bar-chart">
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence">
                                    <div class="bar-value">77</div>
                                </div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                        <div class="bar-group">
                            <div class="bar">
                                <div class="bar-absence"></div>
                                <div class="bar-retard"></div>
                                <div class="bar-presence"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="month-labels">
                        <div class="month-label">Jan</div>
                        <div class="month-label">Feb</div>
                        <div class="month-label">Mar</div>
                        <div class="month-label">Apr</div>
                        <div class="month-label">May</div>
                        <div class="month-label">Jun</div>
                        <div class="month-label">Jul</div>
                        <div class="month-label active">Aug</div>
                        <div class="month-label">Sep</div>
                        <div class="month-label">Oct</div>
                        <div class="month-label">Nov</div>
                        <div class="month-label">Dec</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Stats Row -->
        <div class="stats-dashboard">
            <div class="stats-container">
                <div class="stats-header">
                    <div class="stats-logo">
                        <div class="orange-text">Orange Digital Center</div>
                        <div class="logo">
                            <div class="sonatel-text">sonatel</div>
                            <div class="orange-square"></div>
                        </div>
                    </div>
                </div>
                
                <div class="stats-metrics">
                    <div class="metric-card">
                        <div class="donut-chart split">
                            <div class="gender-indicator">
                                <div class="gender-icon"><i class="fas fa-female" style="color: #ff7900;"></i> 35%</div>
                                <div class="gender-icon"><i class="fas fa-male" style="color: #00a69b;"></i> 65%</div>
                            </div>
                        </div>
                        <div class="metric-subtitle">180 <span style="font-size: 14px;">Apprenants</span></div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="donut-chart full">
                            <div class="donut-hole">
                                <div class="donut-percent">100<span class="percent-symbol">%</span></div>
                            </div>
                        </div>
                        <div class="metric-title orange">Taux d'insertion</div>
                        <div class="metric-subtitle">Professionnelle</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="donut-chart half">
                            <div class="donut-hole">
                                <div class="donut-percent">56<span class="percent-symbol">%</span></div>
                            </div>
                        </div>
                        <div class="metric-title orange">Taux de</div>
                        <div class="metric-subtitle">Féminisation</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-title orange">Communauté de plus de</div>
                        <div class="metric-subtitle">1000</div>
                        <div class="metric-subtitle">Développeurs</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="centers-count">4 Centres</div>
                        <div class="location-text">Dakar, Diamniadio <br>Ziguinchor, et Saint Louis</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>