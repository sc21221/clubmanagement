<?php
require_once 'config/database.php';
require_once 'classes/Member.php';
require_once 'classes/LogManager.php';

// Datenbankverbindung herstellen
$database = new Database();
$db = $database->getConnection();

if($db === null) {
    die("Fehler: Keine Datenbankverbindung möglich.");
}

// Aktuelles Jahr ermitteln
if(isset($_GET['year'])) {
    $currentYear = $_GET['year'];
} else {
    $currentYear = date('Y');
}

// SQL-Abfrage für Mitglieder mit Vereinsjubiläen in diesem Jahr
$query = "SELECT 
            id,
            salutation,
            first_name,
            last_name,
            join_date,
            email,
            phone,
            membership_type,
            status,
            TIMESTAMPDIFF(YEAR, join_date, CURDATE()) as membership_years,
            DATE_ADD(join_date, INTERVAL TIMESTAMPDIFF(YEAR, join_date, CURDATE()) + 1 YEAR) as next_anniversary
          FROM members 
          WHERE 
            join_date IS NOT NULL 
            AND status = 'Aktiv'
            AND YEAR(DATE_ADD(join_date, INTERVAL TIMESTAMPDIFF(YEAR, join_date, CURDATE()) + 1 YEAR)) = :current_year
            AND TIMESTAMPDIFF(YEAR, join_date, CURDATE()) + 1 IN (10, 15, 20, 25, 30, 40, 50, 60)
          ORDER BY 
            TIMESTAMPDIFF(YEAR, join_date, CURDATE()) + 1 DESC,
            MONTH(join_date),
            DAY(join_date)";

$stmt = $db->prepare($query);
$stmt->bindParam(':current_year', $currentYear);
$stmt->execute();

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Logging der Abfrage
LogManager::logUserAction('Anniversary list generated', [
    'current_year' => $currentYear,
    'members_found' => count($members)
]);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vereinsjubiläen <?php echo $currentYear; ?> - Vereinsverwaltung</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .header p {
            margin: 10px 0 0 0;
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .stats {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .members-list {
            padding: 30px;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .member-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        
        .member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .member-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }
        
        .member-name {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .member-years {
            font-size: 1.1em;
            color: #e74c3c;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .member-details {
            color: #6c757d;
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .member-details div {
            margin-bottom: 5px;
        }
        
        .anniversary-date {
            background: #fdf2e9;
            color: #d35400;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 500;
            display: inline-block;
            margin-top: 10px;
        }
        
        .no-members {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-members h3 {
            margin-bottom: 10px;
            color: #495057;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .print-button:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        
        .jubilee-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.7em;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .jubilee-10 { background: #95a5a6; }
        .jubilee-15 { background: #3498db; }
        .jubilee-20 { background: #9b59b6; }
        .jubilee-25 { background: #f39c12; }
        .jubilee-30 { background: #e67e22; }
        .jubilee-40 { background: #e74c3c; }
        .jubilee-50 { background: #c0392b; }
        .jubilee-60 { background: #8e44ad; }
        
        @media print {
            .print-button {
                display: none;
            }
            
            .container {
                box-shadow: none;
            }
            
            .member-card {
                break-inside: avoid;
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .members-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">📄 Drucken</button>
    
    <div class="container">
        <div class="header">
            <h1>🏆 Vereinsjubiläen <?php echo $currentYear; ?></h1>
            <p>Mitglieder mit besonderen Vereinsjubiläen in diesem Jahr</p>
        </div>
        
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($members); ?></div>
                    <div class="stat-label">Mitglieder mit Vereinsjubiläen</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $currentYear; ?></div>
                    <div class="stat-label">Aktuelles Jahr</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo date('d.m.Y'); ?></div>
                    <div class="stat-label">Generiert am</div>
                </div>
            </div>
        </div>
        
        <div class="members-list">
            <?php if (count($members) > 0): ?>
                <div class="members-grid">
                    <?php foreach ($members as $member): 
                        $years = $member['membership_years'] + 1;
                        $jubileeClass = 'jubilee-' . $years;
                    ?>
                        <div class="member-card">
                            <div class="jubilee-badge <?php echo $jubileeClass; ?>">
                                <?php echo $years; ?> Jahre
                            </div>
                            <div class="member-name">
                                <?php echo htmlspecialchars($member['salutation'] . ' ' . $member['first_name'] . ' ' . $member['last_name']); ?>
                            </div>
                            <div class="member-years">
                                <?php echo $years; ?> Jahre Vereinsmitglied
                            </div>
                            <div class="member-details">
                                <div><strong>Beitrittsdatum:</strong> <?php echo date('d.m.Y', strtotime($member['join_date'])); ?></div>
                                <div><strong>Mitgliedstyp:</strong> <?php echo htmlspecialchars($member['membership_type']); ?></div>
                                <?php if (!empty($member['email'])): ?>
                                    <div><strong>E-Mail:</strong> <?php echo htmlspecialchars($member['email']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($member['phone'])): ?>
                                    <div><strong>Telefon:</strong> <?php echo htmlspecialchars($member['phone']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="anniversary-date">
                                🎉 Jubiläum: <?php echo date('d.m.Y', strtotime($member['next_anniversary'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-members">
                    <h3>Keine Vereinsjubiläen gefunden</h3>
                    <p>In diesem Jahr haben keine aktiven Mitglieder ein Vereinsjubiläum (10, 15, 20, 25, 30, 40, 50 oder 60 Jahre Mitgliedschaft).</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Automatisches Aktualisieren der Seite alle 5 Minuten
        setTimeout(function() {
            location.reload();
        }, 300000);
        
        // Tastaturkürzel für Drucken
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
        
        // Jubiläums-Badges mit Animation
        document.addEventListener('DOMContentLoaded', function() {
            const badges = document.querySelectorAll('.jubilee-badge');
            badges.forEach((badge, index) => {
                setTimeout(() => {
                    badge.style.animation = 'pulse 2s infinite';
                }, index * 100);
            });
        });
    </script>
    
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</body>
</html>
