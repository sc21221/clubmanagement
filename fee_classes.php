<?php
session_start();
require_once 'config/database.php';
require_once 'classes/FeeClass.php';

$database = new Database();
$db = $database->getConnection();
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
                $fee_class->name = $_POST['name'];
                $fee_class->description = $_POST['description'];
                $fee_class->base_amount = $_POST['base_amount'];
                $fee_class->currency = $_POST['currency'];
                $fee_class->billing_cycle = $_POST['billing_cycle'];
                $fee_class->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if($fee_class->create()) {
                    $_SESSION['message'] = "Beitragsklasse erfolgreich erstellt!";
                } else {
                    $_SESSION['message'] = "Fehler beim Erstellen der Beitragsklasse.";
                }
                header("Location: fee_classes.php");
                exit();
                break;
                
            case 'update':
                $fee_class->id = $_POST['id'];
                $fee_class->name = $_POST['name'];
                $fee_class->description = $_POST['description'];
                $fee_class->base_amount = $_POST['base_amount'];
                $fee_class->currency = $_POST['currency'];
                $fee_class->billing_cycle = $_POST['billing_cycle'];
                $fee_class->is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if($fee_class->update()) {
                    $_SESSION['message'] = "Beitragsklasse erfolgreich aktualisiert!";
                } else {
                    $_SESSION['message'] = "Fehler beim Aktualisieren der Beitragsklasse.";
                }
                header("Location: fee_classes.php");
                exit();
                break;
                
            case 'delete':
                $fee_class->id = $_POST['id'];
                if($fee_class->delete()) {
                    $_SESSION['message'] = "Beitragsklasse erfolgreich gelöscht!";
                } else {
                    $_SESSION['message'] = "Beitragsklasse kann nicht gelöscht werden, da sie noch Mitgliedern zugeordnet ist.";
                }
                header("Location: fee_classes.php");
                exit();
                break;
        }
    }
}

// Beitragsklassen abrufen
$fee_classes = $fee_class->readWithMemberCount();
$search_results = null;

// Suche durchführen
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search_results = $fee_class->searchByName($_GET['search']);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vereinsverwaltung - Beitragsklassen</title>
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
                        <a class="nav-link active" href="fee_classes.php">
                            <i class="fas fa-tags me-1"></i>Beitragsklassen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="member_fees.php">
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
                <h1><i class="fas fa-tags me-2"></i>Beitragsklassenverwaltung</h1>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFeeClassModal">
                    <i class="fas fa-plus me-1"></i>Neue Beitragsklasse
                </button>
            </div>
        </div>

        <!-- Suchleiste -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group search-box">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Nach Namen oder Beschreibung suchen..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="fee_classes.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Suche löschen
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Beitragsklassen-Tabelle -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    <?php if($search_results): ?>
                        Suchergebnisse (<?php echo $search_results->rowCount(); ?> Beitragsklassen)
                    <?php else: ?>
                        Alle Beitragsklassen (<?php echo $fee_classes->rowCount(); ?> Beitragsklassen)
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Beschreibung</th>
                                <th>Beitrag</th>
                                <th>Abrechnungszyklus</th>
                                <th>Status</th>
                                <th>Mitglieder</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $data = $search_results ? $search_results : $fee_classes;
                            while ($row = $data->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td>
                                    <span class="amount-display">
                                        <?php echo number_format($row['base_amount'], 2, ',', '.'); ?> €
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['billing_cycle']); ?></span>
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
                                    <?php if(isset($row['member_count'])): ?>
                                        <span class="badge bg-primary"><?php echo $row['member_count']; ?> Mitglieder</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" 
                                            onclick="editFeeClass(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-action" 
                                            onclick="deleteFeeClass(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Neue Beitragsklasse hinzufügen -->
    <div class="modal fade" id="addFeeClassModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Neue Beitragsklasse hinzufügen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="base_amount" class="form-label">Beitrag (€) *</label>
                                <input type="number" class="form-control" id="base_amount" name="base_amount" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Währung</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="EUR">EUR (€)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="CHF">CHF (CHF)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_cycle" class="form-label">Abrechnungszyklus</label>
                                <select class="form-select" id="billing_cycle" name="billing_cycle">
                                    <option value="Jährlich">Jährlich</option>
                                    <option value="Halbjährlich">Halbjährlich</option>
                                    <option value="Vierteljährlich">Vierteljährlich</option>
                                    <option value="Monatlich">Monatlich</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Beitragsklasse ist aktiv
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-success">Beitragsklasse hinzufügen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Beitragsklasse bearbeiten -->
    <div class="modal fade" id="editFeeClassModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Beitragsklasse bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_base_amount" class="form-label">Beitrag (€) *</label>
                                <input type="number" class="form-control" id="edit_base_amount" name="base_amount" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_currency" class="form-label">Währung</label>
                                <select class="form-select" id="edit_currency" name="currency">
                                    <option value="EUR">EUR (€)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="CHF">CHF (CHF)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_billing_cycle" class="form-label">Abrechnungszyklus</label>
                                <select class="form-select" id="edit_billing_cycle" name="billing_cycle">
                                    <option value="Jährlich">Jährlich</option>
                                    <option value="Halbjährlich">Halbjährlich</option>
                                    <option value="Vierteljährlich">Vierteljährlich</option>
                                    <option value="Monatlich">Monatlich</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Beschreibung</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    Beitragsklasse ist aktiv
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
                    <h5 class="modal-title">Beitragsklasse löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Möchten Sie die Beitragsklasse <strong id="deleteFeeClassName"></strong> wirklich löschen?</p>
                    <p class="text-danger"><small>Diese Aktion kann nicht rückgängig gemacht werden.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteFeeClassId">
                        <button type="submit" class="btn btn-danger">Löschen bestätigen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Beitragsklasse bearbeiten
        function editFeeClass(id) {
            // Hier würden Sie die Beitragsklassendaten per AJAX laden
            // Für dieses Beispiel verwenden wir ein einfaches Formular
            document.getElementById('edit_id').value = id;
            
            // Modal öffnen
            new bootstrap.Modal(document.getElementById('editFeeClassModal')).show();
        }

        // Beitragsklasse löschen
        function deleteFeeClass(id, name) {
            document.getElementById('deleteFeeClassId').value = id;
            document.getElementById('deleteFeeClassName').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }
    </script>
</body>
</html>

