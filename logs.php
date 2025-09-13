<?php
session_start();
require_once 'classes/LogManager.php';

$logManager = LogManager::getInstance();
$logs = [];
$filteredLogs = [];
$selectedLevel = isset($_GET['level']) ? (int)$_GET['level'] : null;
$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 100;

// Logs abrufen
if ($logManager->isEnabled()) {
    $logs = $logManager->getLogger()->getLogs($lines, $selectedLevel);
}

// Filter anwenden
if ($selectedLevel !== null) {
    $filteredLogs = array_filter($logs, function($log) use ($selectedLevel) {
        $levelNames = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];
        return strpos($log, $levelNames[$selectedLevel]) !== false;
    });
} else {
    $filteredLogs = $logs;
}

// Logs löschen
if (isset($_POST['action']) && $_POST['action'] === 'clear_logs') {
    $logManager->getLogger()->clearLogs();
    header("Location: logs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vereinsverwaltung - Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin-bottom: 5px;
            padding: 8px;
            border-radius: 4px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .log-debug { background-color: #f8f9fa; border-left: 4px solid #6c757d; }
        .log-info { background-color: #d1ecf1; border-left: 4px solid #17a2b8; }
        .log-warning { background-color: #fff3cd; border-left: 4px solid #ffc107; }
        .log-error { background-color: #f8d7da; border-left: 4px solid #dc3545; }
        .log-critical { background-color: #f5c6cb; border-left: 4px solid #721c24; font-weight: bold; }
        .log-container {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php require_once 'partials/menu.php'; echo $htmlMenu; ?>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1><i class="fas fa-file-alt me-2"></i>System Logs</h1>
                <p class="text-muted">Überwachung und Analyse der Anwendungsprotokolle</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                    <i class="fas fa-trash me-1"></i>Logs löschen
                </button>
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fas fa-sync me-1"></i>Aktualisieren
                </button>
            </div>
        </div>

        <!-- Statistiken -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3><?php echo count($filteredLogs); ?></h3>
                        <p class="mb-0">Log-Einträge</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3><?php echo count(array_filter($filteredLogs, function($log) { return strpos($log, 'ERROR') !== false; })); ?></h3>
                        <p class="mb-0">Fehler</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3><?php echo count(array_filter($filteredLogs, function($log) { return strpos($log, 'WARNING') !== false; })); ?></h3>
                        <p class="mb-0">Warnungen</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3><?php echo count(array_filter($filteredLogs, function($log) { return strpos($log, 'INFO') !== false; })); ?></h3>
                        <p class="mb-0">Informationen</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="level" class="form-label">Log-Level</label>
                        <select class="form-select" id="level" name="level">
                            <option value="">Alle Level</option>
                            <option value="0" <?php echo $selectedLevel === 0 ? 'selected' : ''; ?>>DEBUG</option>
                            <option value="1" <?php echo $selectedLevel === 1 ? 'selected' : ''; ?>>INFO</option>
                            <option value="2" <?php echo $selectedLevel === 2 ? 'selected' : ''; ?>>WARNING</option>
                            <option value="3" <?php echo $selectedLevel === 3 ? 'selected' : ''; ?>>ERROR</option>
                            <option value="4" <?php echo $selectedLevel === 4 ? 'selected' : ''; ?>>CRITICAL</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="lines" class="form-label">Anzahl Einträge</label>
                        <select class="form-select" id="lines" name="lines">
                            <option value="50" <?php echo $lines === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $lines === 100 ? 'selected' : ''; ?>>100</option>
                            <option value="200" <?php echo $lines === 200 ? 'selected' : ''; ?>>200</option>
                            <option value="500" <?php echo $lines === 500 ? 'selected' : ''; ?>>500</option>
                            <option value="0" <?php echo $lines === 0 ? 'selected' : ''; ?>>Alle</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i>Filter anwenden
                        </button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="logs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Filter zurücksetzen
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs anzeigen -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    <?php if ($selectedLevel !== null): ?>
                        Logs (Level: <?php echo ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'][$selectedLevel]; ?>)
                    <?php else: ?>
                        Alle Logs
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="log-container">
                    <?php if (empty($filteredLogs)): ?>
                        <div class="text-center p-4 text-muted">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>Keine Log-Einträge gefunden.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($filteredLogs as $log): ?>
                            <?php
                            $logClass = 'log-info';
                            if (strpos($log, 'DEBUG') !== false) $logClass = 'log-debug';
                            elseif (strpos($log, 'WARNING') !== false) $logClass = 'log-warning';
                            elseif (strpos($log, 'ERROR') !== false) $logClass = 'log-error';
                            elseif (strpos($log, 'CRITICAL') !== false) $logClass = 'log-critical';
                            ?>
                            <div class="log-entry <?php echo $logClass; ?>">
                                <?php echo htmlspecialchars($log); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs löschen Modal -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Logs löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Möchten Sie wirklich alle Log-Dateien löschen?</p>
                    <p class="text-danger"><small>Diese Aktion kann nicht rückgängig gemacht werden.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="clear_logs">
                        <button type="submit" class="btn btn-danger">Logs löschen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

