<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
require_once 'config/database.php';
require_once 'classes/Member.php';
require_once 'classes/LogManager.php';

$database = new Database();
$db = $database->getConnection();

if($db === null) {
    LogManager::critical("Database connection failed");
    $_SESSION['message'] = "Datenbankverbindung fehlgeschlagen. Bitte überprüfen Sie die Konfiguration.";
    // Redirect to avoid header issues
    header("Location: index.php");
    exit();
}

$member = new Member($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$editError = '';
$editData = null;
$createError = '';
$createData = null;

// Nachrichten verarbeiten
if(isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Bearbeitungsfehler verarbeiten
if(isset($_SESSION['edit_error'])) {
    $editError = $_SESSION['edit_error'];
    unset($_SESSION['edit_error']);
}

// Bearbeitungsdaten verarbeiten
if(isset($_SESSION['edit_data'])) {
    $editData = $_SESSION['edit_data'];
    unset($_SESSION['edit_data']);
}

// Erstellungsfehler verarbeiten
if(isset($_SESSION['create_error'])) {
    $createError = $_SESSION['create_error'];
    unset($_SESSION['create_error']);
}

// Erstellungsdaten verarbeiten
if(isset($_SESSION['create_data'])) {
    $createData = $_SESSION['create_data'];
    unset($_SESSION['create_data']);
}

// AJAX-Handler für Mitgliederdaten
if($action == 'get_member' && isset($_GET['id'])) {
    $member->id = $_GET['id'];
    $member->readOne();
    
    if($member->first_name) {
        echo json_encode([
            'success' => true,
            'member' => [
                'id' => $member->id,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'join_date' => $member->join_date,
                'birth_date' => $member->birth_date,
                'membership_type' => $member->membership_type,
                'status' => $member->status,
                'address' => $member->address
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Mitglied nicht gefunden'
        ]);
    }
    exit();
}

// Aktionen verarbeiten
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $member->first_name = $_POST['first_name'];
                $member->last_name = $_POST['last_name'];
                $member->email = $_POST['email'];
                $member->phone = $_POST['phone'];
                $member->join_date = $_POST['join_date'];
                $member->membership_type = $_POST['membership_type'];
                $member->status = $_POST['status'];
                $member->address = $_POST['address'];
                $member->birth_date = $_POST['birth_date'];
                
                if($member->create()) {
                    $_SESSION['message'] = "Mitglied erfolgreich erstellt!";
                    header("Location: index.php");
                    exit();
                } else {
                    // Prüfen, ob es ein E-Mail-Konflikt ist
                    if($member->emailExists($member->email)) {
                        // E-Mail-Konflikt: Daten in Session speichern und Modal öffnen
                        $_SESSION['create_error'] = "Fehler: Die E-Mail-Adresse '{$member->email}' ist bereits vergeben.";
                        $_SESSION['create_data'] = [
                            'first_name' => $member->first_name,
                            'last_name' => $member->last_name,
                            'email' => $member->email,
                            'phone' => $member->phone,
                            'join_date' => $member->join_date,
                            'birth_date' => $member->birth_date,
                            'membership_type' => $member->membership_type,
                            'status' => $member->status,
                            'address' => $member->address
                        ];
                        header("Location: index.php?action=create_error");
                        exit();
                    } else {
                        $_SESSION['message'] = "Fehler beim Erstellen des Mitglieds.";
                        header("Location: index.php");
                        exit();
                    }
                }
                break;
                
            case 'update':
                $member->id = $_POST['id'];
                $member->first_name = $_POST['first_name'];
                $member->last_name = $_POST['last_name'];
                $member->email = $_POST['email'];
                $member->phone = $_POST['phone'];
                $member->join_date = $_POST['join_date'];
                $member->membership_type = $_POST['membership_type'];
                $member->status = $_POST['status'];
                $member->address = $_POST['address'];
                $member->birth_date = $_POST['birth_date'];
                
                if($member->update()) {
                    $_SESSION['message'] = "Mitglied erfolgreich aktualisiert!";
                    header("Location: index.php");
                    exit();
                } else {
                    // Prüfen, ob es ein E-Mail-Konflikt ist
                    if($member->emailExists($member->email, $member->id)) {
                        // E-Mail-Konflikt: Daten in Session speichern und Modal öffnen
                        $_SESSION['edit_error'] = "Fehler: Die E-Mail-Adresse '{$member->email}' ist bereits vergeben.";
                        $_SESSION['edit_data'] = [
                            'id' => $member->id,
                            'first_name' => $member->first_name,
                            'last_name' => $member->last_name,
                            'email' => $member->email,
                            'phone' => $member->phone,
                            'join_date' => $member->join_date,
                            'birth_date' => $member->birth_date,
                            'membership_type' => $member->membership_type,
                            'status' => $member->status,
                            'address' => $member->address
                        ];
                        header("Location: index.php?action=edit&id=" . $member->id);
                        exit();
                    } else {
                        $_SESSION['message'] = "Fehler beim Aktualisieren des Mitglieds.";
                        header("Location: index.php");
                        exit();
                    }
                }
                break;
                
            case 'delete':
                $member->id = $_POST['id'];
                if($member->delete()) {
                    $_SESSION['message'] = "Mitglied erfolgreich gelöscht!";
                } else {
                    $_SESSION['message'] = "Fehler beim Löschen des Mitglieds.";
                }
                header("Location: index.php");
                exit();
                break;
        }
    }
}

// Mitglieder abrufen
$members = $member->read();
$search_results = null;

// Suche durchführen
if(isset($_GET['search']) && !empty($_GET['search'])) {
    LogManager::logUserAction('Member search performed', [
        'search_term' => $_GET['search'],
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ]);
    $search_results = $member->search($_GET['search']);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vereinsverwaltung - Mitgliederverwaltung</title>
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
                <h1><i class="fas fa-users me-2"></i>Mitgliederverwaltung</h1>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                    <i class="fas fa-plus me-1"></i>Neues Mitglied
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
                                   placeholder="Nach Namen oder E-Mail suchen..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Suche löschen
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mitgliedertabelle -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-table me-2"></i>
                    <?php if($search_results): ?>
                        Suchergebnisse (<?php echo $search_results->rowCount(); ?> Mitglieder)
                    <?php else: ?>
                        Alle Mitglieder (<?php echo $members->rowCount(); ?> Mitglieder)
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>E-Mail</th>
                                <th>Telefon</th>
                                <th>Beitrittsdatum</th>
                                <th>Typ</th>
                                <th>Status</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $data = $search_results ? $search_results : $members;
                            while ($row = $data->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($row['join_date'])); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['membership_type']); ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $status_class = $row['status'] == 'Aktiv' ? 'bg-success' : 
                                                  ($row['status'] == 'Inaktiv' ? 'bg-warning' : 'bg-danger');
                                    ?>
                                    <span class="badge <?php echo $status_class; ?> status-badge">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" 
                                            onclick="editMember(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-action" 
                                            onclick="deleteMember(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>')">
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

    <!-- Modal: Neues Mitglied hinzufügen -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Neues Mitglied hinzufügen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">Vorname *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Nachname *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-Mail *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="join_date" class="form-label">Beitrittsdatum *</label>
                                <input type="date" class="form-control" id="join_date" name="join_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="birth_date" class="form-label">Geburtsdatum</label>
                                <input type="date" class="form-control" id="birth_date" name="birth_date">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="membership_type" class="form-label">Mitgliedstyp</label>
                                <select class="form-select" id="membership_type" name="membership_type">
                                    <option value="Vollmitglied">Vollmitglied</option>
                                    <option value="Fördermitglied">Fördermitglied</option>
                                    <option value="Jugendmitglied">Jugendmitglied</option>
                                    <option value="Ehrenmitglied">Ehrenmitglied</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Aktiv">Aktiv</option>
                                    <option value="Inaktiv">Inaktiv</option>
                                    <option value="Gesperrt">Gesperrt</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-success">Mitglied hinzufügen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Mitglied bearbeiten -->
    <div class="modal fade" id="editMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mitglied bearbeiten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_first_name" class="form-label">Vorname *</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_last_name" class="form-label">Nachname *</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_email" class="form-label">E-Mail *</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_phone" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_join_date" class="form-label">Beitrittsdatum *</label>
                                <input type="date" class="form-control" id="edit_join_date" name="join_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_birth_date" class="form-label">Geburtsdatum</label>
                                <input type="date" class="form-control" id="edit_birth_date" name="birth_date">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_membership_type" class="form-label">Mitgliedstyp</label>
                                <select class="form-select" id="edit_membership_type" name="membership_type">
                                    <option value="Vollmitglied">Vollmitglied</option>
                                    <option value="Fördermitglied">Fördermitglied</option>
                                    <option value="Jugendmitglied">Jugendmitglied</option>
                                    <option value="Ehrenmitglied">Ehrenmitglied</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status">
                                    <option value="Aktiv">Aktiv</option>
                                    <option value="Inaktiv">Inaktiv</option>
                                    <option value="Gesperrt">Gesperrt</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
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
                    <h5 class="modal-title">Mitglied löschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Möchten Sie das Mitglied <strong id="deleteMemberName"></strong> wirklich löschen?</p>
                    <p class="text-danger"><small>Diese Aktion kann nicht rückgängig gemacht werden.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteMemberId">
                        <button type="submit" class="btn btn-danger">Löschen bestätigen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatisch Modals öffnen wenn Fehler vorliegt
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($editError && $editData): ?>
                // Bearbeitungsfehler anzeigen
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                errorAlert.innerHTML = `
                    <?php echo htmlspecialchars($editError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container').insertBefore(errorAlert, document.querySelector('.container').firstChild);
                
                // Bearbeitungsformular mit Daten füllen
                document.getElementById('edit_id').value = '<?php echo $editData['id']; ?>';
                document.getElementById('edit_first_name').value = '<?php echo htmlspecialchars($editData['first_name']); ?>';
                document.getElementById('edit_last_name').value = '<?php echo htmlspecialchars($editData['last_name']); ?>';
                document.getElementById('edit_email').value = '<?php echo htmlspecialchars($editData['email']); ?>';
                document.getElementById('edit_phone').value = '<?php echo htmlspecialchars($editData['phone']); ?>';
                document.getElementById('edit_join_date').value = '<?php echo $editData['join_date']; ?>';
                document.getElementById('edit_birth_date').value = '<?php echo $editData['birth_date']; ?>';
                document.getElementById('edit_membership_type').value = '<?php echo $editData['membership_type']; ?>';
                document.getElementById('edit_status').value = '<?php echo $editData['status']; ?>';
                document.getElementById('edit_address').value = '<?php echo htmlspecialchars($editData['address']); ?>';
                
                // Bearbeitungsmodal öffnen
                new bootstrap.Modal(document.getElementById('editMemberModal')).show();
            <?php elseif($createError && $createData): ?>
                // Erstellungsfehler anzeigen
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger alert-dismissible fade show';
                errorAlert.innerHTML = `
                    <?php echo htmlspecialchars($createError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container').insertBefore(errorAlert, document.querySelector('.container').firstChild);
                
                // Erstellungsformular mit Daten füllen
                document.getElementById('first_name').value = '<?php echo htmlspecialchars($createData['first_name']); ?>';
                document.getElementById('last_name').value = '<?php echo htmlspecialchars($createData['last_name']); ?>';
                document.getElementById('email').value = '<?php echo htmlspecialchars($createData['email']); ?>';
                document.getElementById('phone').value = '<?php echo htmlspecialchars($createData['phone']); ?>';
                document.getElementById('join_date').value = '<?php echo $createData['join_date']; ?>';
                document.getElementById('birth_date').value = '<?php echo $createData['birth_date']; ?>';
                document.getElementById('membership_type').value = '<?php echo $createData['membership_type']; ?>';
                document.getElementById('status').value = '<?php echo $createData['status']; ?>';
                document.getElementById('address').value = '<?php echo htmlspecialchars($createData['address']); ?>';
                
                // Erstellungsmodal öffnen
                new bootstrap.Modal(document.getElementById('addMemberModal')).show();
            <?php endif; ?>
        });

        // Mitglied bearbeiten
        function editMember(id) {
            // AJAX-Anfrage um Mitgliederdaten zu laden
            fetch('index.php?action=get_member&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Formularfelder mit Daten füllen
                        document.getElementById('edit_id').value = data.member.id;
                        document.getElementById('edit_first_name').value = data.member.first_name || '';
                        document.getElementById('edit_last_name').value = data.member.last_name || '';
                        document.getElementById('edit_email').value = data.member.email || '';
                        document.getElementById('edit_phone').value = data.member.phone || '';
                        document.getElementById('edit_join_date').value = data.member.join_date || '';
                        document.getElementById('edit_birth_date').value = data.member.birth_date || '';
                        document.getElementById('edit_membership_type').value = data.member.membership_type || 'Vollmitglied';
                        document.getElementById('edit_status').value = data.member.status || 'Aktiv';
                        document.getElementById('edit_address').value = data.member.address || '';
                        
                        // Modal öffnen
                        new bootstrap.Modal(document.getElementById('editMemberModal')).show();
                    } else {
                        alert('Fehler beim Laden der Mitgliederdaten: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Fehler beim Laden der Mitgliederdaten');
                });
        }

        // Mitglied löschen
        function deleteMember(id, name) {
            document.getElementById('deleteMemberId').value = id;
            document.getElementById('deleteMemberName').textContent = name;
            
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
        }
    </script>
</body>
</html>
