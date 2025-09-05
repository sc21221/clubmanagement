<?php
session_start();
require_once 'config/database.php';
require_once 'classes/MemberFeeClass.php';
require_once 'classes/Member.php';
require_once 'classes/FeeClass.php';

$database = new Database();
$db = $database->getConnection();
$member_fee_class = new MemberFeeClass($db);
$member = new Member($db);
$fee_class = new FeeClass($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Nachrichten verarbeiten
if(isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Aktionen verarbeiten
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $member_fee_class->member_id = $_POST['member_id'];
                $member_fee_class->fee_class_id = $_POST['fee_class_id'];
                $member_fee_class->custom_amount = !empty($_POST['custom_amount']) ? $_POST['custom_amount'] : null;
                $member_fee_class->start_date = $_POST['start_date'];
                $member_fee_class->end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                $member_fee_class->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if($member_fee_class->create()) {
                    $_SESSION['message'] = "Zuordnung erfolgreich erstellt!";
                } else {
                    $_SESSION['message'] = "Fehler beim Erstellen der Zuordnung.";
                }
                header("Location: member_fees.php");
                exit();
                break;
                
            case 'update':
                $member_fee_class->id = $_POST['id'];
                $member_fee_class->member_id = $_POST['member_id'];
                $member_fee_class->fee_class_id = $_POST['fee_class_id'];
                $member_fee_class->custom_amount = !empty($_POST['custom_amount']) ? $_POST['custom_amount'] : null;
                $member_fee_class->start_date = $_POST['start_date'];
                $member_fee_class->end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
                $member_fee_class->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if($member_fee_class->update()) {
                    $_SESSION['message'] = "Zuordnung erfolgreich aktualisiert!";
                } else {
                    $_SESSION['message'] = "Fehler beim Aktualisieren der Zuordnung.";
                }
                header("Location: member_fees.php");
                exit();
                break;
                
            case 'delete':
                $member_fee_class->id = $_POST['id'];
                if($member_fee_class->delete()) {
                    $_SESSION['message'] = "Zuordnung erfolgreich gelöscht!";
                } else {
                    $_SESSION['message'] = "Fehler beim Löschen der Zuordnung.";
                }
                header("Location: member_fees.php");
                exit();
                break;
        }
    }
}

// Zuordnungen abrufen
$assignments = $member_fee_class->readWithDetails();
$members = $member->read();
$fee_classes = $fee_class->readActive();
$search_results = null;

// Suche nach Mitglied
if(isset($_GET['member_search']) && !empty($_GET['member_search'])) {
    $search_results = $member->search($_GET['member_search']);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vereinsverwaltung - Mitglieder-Beitragsklassen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .btn-action {
            margin: 0 2px;
        }
        .search-box {
            max-width: 400px;
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .amount-display {
            font-weight: bold;
            color: #28a745;
        }
        .custom-amount {
            color: #dc3545;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-users me-2"></i>Vereinsverwaltung
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-list me-1"></i>Mitglieder
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fee_classes.php">
                            <i class="fas fa-tags me-1"></i>Beitragsklassen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="member_fees.php">
                            <i class="fas fa-link me-1"></i>Zuordnungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="events.php">
                            <i class="fas fa-calendar me-1"></i>Veranstaltungen
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Nachrichten anzeigen -->
        <?php if($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header mit Aktionen -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h1><i class="fas fa-link me-2"></i>Mitglieder-Beitragsklassen</h1>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                    <i class="fas fa-plus me-1"></i>Neue Zuordnung
                </button>
            </div>
        </div>

        <!-- Suchleiste -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group search-box">
                            <input type="text" class="form-control" name="member_search" 
                                   placeholder="Nach Mitgliedsnamen suchen..." 
                                   value="<?php echo isset($_GET['member_search']) ? htmlspecialchars($_GET['member_search']) : ''; ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if(isset($_GET['member_search']) && !empty($_GET['member_search'])): ?>
                            <a href="member_fees.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Suche löschen
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Zuordnungen-Tabelle -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    <?php if($search_results): ?>
                        Suchergebnisse für Mitglieder
                    <?php else: ?>
                        Alle Zuordnungen (<?php echo $assignments->rowCount(); ?> Zuordnungen)
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mitglied</th>
                                <th>Beitragsklasse</th>
                                <th>Beitrag</th>
                                <th>Startdatum</th>
                                <th>Enddatum</th>
                                <th>Status</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($search_results) {
                                // Wenn nach Mitgliedern gesucht wird, zeige deren Zuordnungen
                                while ($member_row = $search_results->fetch(PDO::FETCH_ASSOC)) {
                                    $member_assignments = $member_fee_class->readByMember($member_row['id']);
                                    while ($assignment = $member_assignments->fetch(PDO::FETCH_ASSOC)) {
                                        displayAssignmentRow($assignment);
                                    }
                                }
                            } else {
                                // Zeige alle Zuordnungen
                                while ($row = $assignments->fetch(PDO::FETCH_ASSOC)): 
                                    displayAssignmentRow($row);
                                endwhile;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Neue Zuordnung hinzufügen -->
    <div class="modal fade" id="addAssignmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Neue Zuordnung erstellen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="member_id" class="form-label">Mitglied *</label>
                                <select class="form-select" id="member_id" name="member_id" required>
                                    <option value="">Mitglied auswählen...</option>
                                    <?php 
                                    $members->execute();
                                    while ($member_row = $members->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                    <option value="<?php echo $member_row['id']; ?>">
                                        <?php echo htmlspecialchars($member_row['first_name'] . ' ' . $member_row['last_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fee_class_id" class="form-label">Beitragsklasse *</label>
                                <select class="form-select" id="fee_class_id" name="fee_class_id" required>
                                    <option value="">Beitragsklasse auswählen...</option>
                                    <?php 
                                    $fee_classes->execute();
                                    while ($fee_class_row = $fee_classes->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                    <option value="<?php echo $fee_class_row['id']; ?>">
                                        <?php echo htmlspecialchars($fee_class_row['name'] . ' (' . $fee_class_row['base_amount'] . '€)'); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Startdatum *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Enddatum (optional)</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="custom_amount" class="form-label">Individueller Beitrag (optional)</label>
                            <input type="number" class="form-control" id="custom_amount" name="custom_amount" 
                                   step="0.01" min="0" placeholder="Leer lassen für Standardbeitrag">
                            <small class="form-text text-muted">Lassen Sie dieses Feld leer, um den Standardbeitrag der Beitragsklasse zu verwenden.</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Zuordnung ist aktiv
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-success">Zuordnung erstellen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Zuordnung bearbeiten -->
    <div class="modal fade" id="editAssignmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Zuordnung bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_member_id" class="form-label">Mitglied *</label>
                                <select class="form-select" id="edit_member_id" name="member_id" required>
                                    <option value="">Mitglied auswählen...</option>
                                    <?php 
                                    $members->execute();
                                    while ($member_row = $members->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                    <option value="<?php echo $member_row['id']; ?>">
                                        <?php echo htmlspecialchars($member_row['first_name'] . ' ' . $member_row['last_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_fee_class_id" class="form-label">Beitragsklasse *</label>
                                <select class="form-select" id="edit_fee_class_id" name="fee_class_id" required>
                                    <option value="">Beitragsklasse auswählen...</option>
                                    <?php 
                                    $fee_classes->execute();
                                    while ($fee_class_row = $fee_classes->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                    <option value="<?php echo $fee_class_row['id']; ?>">
                                        <?php echo htmlspecialchars($fee_class_row['name'] . ' (' . $fee_class_row['base_amount'] . '€)'); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_start_date" class="form-label">Startdatum *</label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_end_date" class="form-label">Enddatum (optional)</label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_custom_amount" class="form-label">Individueller Beitrag (optional)</label>
                            <input type="number" class="form-control" id="edit_custom_amount" name="custom_amount" 
                                   step="0.01" min="0" placeholder="Leer lassen für Standardbeitrag">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    Zuordnung ist aktiv
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Änderungen speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Löschbestätigung Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Zuordnung löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Möchten Sie diese Zuordnung wirklich löschen?</p>
                    <p class="text-danger"><small>Diese Aktion kann nicht rückgängig gemacht werden.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteAssignmentId">
                        <button type="submit" class="btn btn-danger">Löschen bestätigen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Zuordnung bearbeiten
        function editAssignment(id) {
            // Hier würden Sie die Zuordnungsdaten per AJAX laden
            // Für dieses Beispiel verwenden wir ein einfaches Formular
            document.getElementById('edit_id').value = id;
            
            // Modal öffnen
            new bootstrap.Modal(document.getElementById('editAssignmentModal')).show();
        }

        // Zuordnung löschen
        function deleteAssignment(id) {
            document.getElementById('deleteAssignmentId').value = id;
            
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }
    </script>
</body>
</html>

<?php
// Hilfsfunktion zum Anzeigen der Zuordnungszeilen
function displayAssignmentRow($row) {
    $effective_amount = $row['custom_amount'] ? $row['custom_amount'] : $row['base_amount'];
    $amount_class = $row['custom_amount'] ? 'custom-amount' : 'amount-display';
    $amount_text = $row['custom_amount'] ? $effective_amount . '€ (individuell)' : $effective_amount . '€';
    ?>
    <tr>
        <td>
            <strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong><br>
            <small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small>
        </td>
        <td>
            <strong><?php echo htmlspecialchars($row['fee_class_name']); ?></strong><br>
            <small class="text-muted"><?php echo htmlspecialchars($row['description']); ?></small>
        </td>
        <td>
            <span class="<?php echo $amount_class; ?>">
                <?php echo $amount_text; ?>
            </span>
        </td>
        <td><?php echo date('d.m.Y', strtotime($row['start_date'])); ?></td>
        <td>
            <?php 
            if($row['end_date']) {
                echo date('d.m.Y', strtotime($row['end_date']));
            } else {
                echo '<span class="text-muted">-</span>';
            }
            ?>
        </td>
        <td>
            <?php 
            $status_class = $row['is_active'] ? 'bg-success' : 'bg-secondary';
            $status_text = $row['is_active'] ? 'Aktiv' : 'Inaktiv';
            ?>
            <span class="badge <?php echo $status_class; ?> status-badge">
                <?php echo $status_text; ?>
            </span>
        </td>
        <td>
            <button class="btn btn-sm btn-outline-primary btn-action" 
                    onclick="editAssignment(<?php echo $row['id']; ?>)">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger btn-action" 
                    onclick="deleteAssignment(<?php echo $row['id']; ?>)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
    <?php
}
?>

