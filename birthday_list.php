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

// SQL-Abfrage für Mitglieder mit runden Geburtstagen in diesem Jahr
$query = "SELECT 
            id,
            salutation,
            first_name,
            last_name,
            birth_date,
            email,
            phone,
            membership_type,
            status,
            TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as current_age,
            DATE_ADD(birth_date, INTERVAL TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) + 1 YEAR) as next_birthday
          FROM members 
          WHERE 
            birth_date IS NOT NULL 
            AND status = 'Aktiv'
            AND YEAR(DATE_ADD(birth_date, INTERVAL TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) + 1 YEAR)) = :current_year
            AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) + 1 IN (50, 60, 70, 80, 85, 90, 100)
          ORDER BY 
            TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) + 1 DESC,
            MONTH(birth_date),
            DAY(birth_date)";

$stmt = $db->prepare($query);
$stmt->bindParam(':current_year', $currentYear);
$stmt->execute();

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Logging der Abfrage
LogManager::logUserAction('Birthday list generated', [
    'current_year' => $currentYear,
    'members_found' => count($members)
]);

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runde Geburtstage <?php echo $currentYear; ?> - Vereinsverwaltung</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .member-name {
            font-size: 1.3em;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .member-age {
            font-size: 1.1em;
            color: #667eea;
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
        
        .birthday-date {
            background: #e3f2fd;
            color: #1976d2;
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
            <h1>🎂 Runde Geburtstage <?php echo $currentYear; ?></h1>
            <p>Mitglieder mit besonderen Geburtstagen in diesem Jahr</p>
        </div>
        
        <div class="stats">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($members); ?></div>
                    <div class="stat-label">Mitglieder mit runden Geburtstagen</div>
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
                    <?php foreach ($members as $member): ?>
                        <div class="member-card">
                            <div class="member-name">
                                <?php echo htmlspecialchars($member['salutation'] . ' ' . $member['first_name'] . ' ' . $member['last_name']); ?>
                            </div>
                            <div class="member-age">
                                Wird <?php echo $member['current_age'] + 1; ?> Jahre alt
                            </div>
                            <div class="member-details">
                                <div><strong>Geburtsdatum:</strong> <?php echo date('d.m.Y', strtotime($member['birth_date'])); ?></div>
                                <div><strong>Mitgliedstyp:</strong> <?php echo htmlspecialchars($member['membership_type']); ?></div>
                                <?php if (!empty($member['email'])): ?>
                                    <div><strong>E-Mail:</strong> <?php echo htmlspecialchars($member['email']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($member['phone'])): ?>
                                    <div><strong>Telefon:</strong> <?php echo htmlspecialchars($member['phone']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="birthday-date">
                                🎉 Geburtstag: <?php echo date('d.m.Y', strtotime($member['next_birthday'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-members">
                    <h3>Keine runden Geburtstage gefunden</h3>
                    <p>In diesem Jahr haben keine aktiven Mitglieder einen runden Geburtstag (50, 60, 70, 80, 85, 90 oder 100 Jahre).</p>
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
    </script>
</body>
</html>
