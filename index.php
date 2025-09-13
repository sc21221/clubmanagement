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
                'salutation' => $member->salutation,
                'sex' => $member->sex,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'join_date' => $member->join_date,
                'leave_date' => $member->leave_date,
                'birth_date' => $member->birth_date,
                'membership_type' => $member->membership_type,
                'status' => $member->status,
                'invoice_marker' => $member->invoice_marker,
                'street' => $member->street,
                'zip' => $member->zip,
                'city' => $member->city,
                'country' => $member->country,
                'bank_name' => $member->bank_name,
                'bank_bic' => $member->bank_bic,
                'bank_iban' => $member->bank_iban,
                'bank_holder' => $member->bank_holder,
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
                $member->salutation = $_POST['salutation'];
                $member->sex = $_POST['sex'];
                $member->first_name = $_POST['first_name'];
                $member->last_name = $_POST['last_name'];
                $member->email = $_POST['email'];
                $member->phone = $_POST['phone'];
                $member->join_date = $_POST['join_date'];
                $member->leave_date = $_POST['leave_date'];
                $member->birth_date = $_POST['birth_date'];
                $member->membership_type = $_POST['membership_type'];
                $member->status = $_POST['status'];
                $member->invoice_marker = $_POST['invoice_marker'];
                $member->street = $_POST['street'];
                $member->zip = $_POST['zip'];
                $member->city = $_POST['city'];
                $member->country = $_POST['country'];
                $member->bank_name = $_POST['bank_name'];
                $member->bank_bic = $_POST['bank_bic'];
                $member->bank_iban = $_POST['bank_iban'];
                $member->bank_holder = $_POST['bank_holder'];
                
                
                if($member->create()) {
                    $_SESSION['message'] = "Mitglied erfolgreich erstellt!";
                    header("Location: index.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Fehler beim Erstellen des Mitglieds.";
                    header("Location: index.php");
                    exit();
                }
                break;
                
            case 'update':
                $member->id = $_POST['id'];
                $member->salutation = $_POST['salutation'];
                $member->sex = $_POST['sex'];
                $member->first_name = $_POST['first_name'];
                $member->last_name = $_POST['last_name'];
                $member->email = $_POST['email'];
                $member->phone = $_POST['phone'];
                $member->join_date = $_POST['join_date'];
                $member->leave_date = $_POST['leave_date'];
                $member->birth_date = $_POST['birth_date'];
                $member->membership_type = $_POST['membership_type'];
                $member->status = $_POST['status'];
                $member->invoice_marker = $_POST['invoice_marker'];
                $member->street = $_POST['street'];
                $member->zip = $_POST['zip'];
                $member->city = $_POST['city'];
                $member->country = $_POST['country'];
                $member->bank_name = $_POST['bank_name'];
                $member->bank_bic = $_POST['bank_bic'];
                $member->bank_iban = $_POST['bank_iban'];
                $member->bank_holder = $_POST['bank_holder'];
                
                if($member->update()) {
                    $_SESSION['message'] = "Mitglied erfolgreich aktualisiert!";
                    header("Location: index.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Fehler beim Aktualisieren des Mitglieds.";
                    header("Location: index.php");
                    exit();
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
        .modal-dialog {
            transition: transform 0.1s ease;
        }
        .modal-header {
            user-select: none;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php require_once 'partials/menu.php'; echo $htmlMenu; ?>

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
                <h1><i class="fas fa-users me-2"></i>Mitglieder</h1>
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
                                <th>Telefon</th>
                                <th>Geburtsdatum</th>
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
                                    <strong><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($row['birth_date'])); ?></td>
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
    <?php include 'partials/member_modal.php'; ?>
    <?php echo getMemberModalHtml('add'); ?>    

    <!-- Modal: Mitglied bearbeiten -->
    <?php echo getMemberModalHtml('edit'); ?>    
                                    
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
            // Modal verschiebbar machen
            function makeModalDraggable(modalId, headerId) {
                const modal = document.getElementById(modalId);
                const header = document.getElementById(headerId);
                let isDragging = false;
                let currentX;
                let currentY;
                let initialX;
                let initialY;
                let xOffset = 0;
                let yOffset = 0;

                header.addEventListener('mousedown', dragStart);
                document.addEventListener('mousemove', drag);
                document.addEventListener('mouseup', dragEnd);

                function dragStart(e) {
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;

                    if (e.target === header || header.contains(e.target)) {
                        isDragging = true;
                    }
                }

                function drag(e) {
                    if (isDragging) {
                        e.preventDefault();
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;

                        xOffset = currentX;
                        yOffset = currentY;

                        modal.querySelector('.modal-dialog').style.transform = `translate(${currentX}px, ${currentY}px)`;
                    }
                }

                function dragEnd(e) {
                    initialX = currentX;
                    initialY = currentY;
                    isDragging = false;
                }
            }

            // Modals verschiebbar machen
            makeModalDraggable('addMemberModal', 'addMemberModalHeader');
            makeModalDraggable('editMemberModal', 'editMemberModalHeader');

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
                document.getElementById('edit_salutation').value = '<?php echo htmlspecialchars($editData['salutation']); ?>';
                document.getElementById('edit_sex').value = '<?php echo htmlspecialchars($editData['sex']); ?>';
                document.getElementById('edit_first_name').value = '<?php echo htmlspecialchars($editData['first_name']); ?>';
                document.getElementById('edit_last_name').value = '<?php echo htmlspecialchars($editData['last_name']); ?>';
                document.getElementById('edit_email').value = '<?php echo htmlspecialchars($editData['email']); ?>';
                document.getElementById('edit_phone').value = '<?php echo htmlspecialchars($editData['phone']); ?>';
                document.getElementById('edit_join_date').value = '<?php echo $editData['join_date']; ?>';
                document.getElementById('edit_birth_date').value = '<?php echo $editData['birth_date']; ?>';
                document.getElementById('edit_membership_type').value = '<?php echo $editData['membership_type']; ?>';
                document.getElementById('edit_status').value = '<?php echo $editData['status']; ?>';
                document.getElementById('edit_street').value = '<?php echo htmlspecialchars($editData['street']); ?>';
                document.getElementById('edit_zip').value = '<?php echo htmlspecialchars($editData['zip']); ?>';
                document.getElementById('edit_city').value = '<?php echo htmlspecialchars($editData['city']); ?>';
                document.getElementById('edit_country').value = '<?php echo htmlspecialchars($editData['country']); ?>';
                document.getElementById('edit_invoice_marker').value = '<?php echo $editData['invoice_marker']; ?>';
                document.getElementById('edit_bank_name').value = '<?php echo htmlspecialchars($editData['bank_name']); ?>';
                document.getElementById('edit_bank_bic').value = '<?php echo htmlspecialchars($editData['bank_bic']); ?>';
                document.getElementById('edit_bank_iban').value = '<?php echo htmlspecialchars($editData['bank_iban']); ?>';
                document.getElementById('edit_bank_holder').value = '<?php echo htmlspecialchars($editData['bank_holder']); ?>';

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
                document.getElementById('street').value = '<?php echo htmlspecialchars($createData['street']); ?>';
                document.getElementById('zip').value = '<?php echo htmlspecialchars($createData['zip']); ?>';
                document.getElementById('city').value = '<?php echo htmlspecialchars($createData['city']); ?>';
                document.getElementById('country').value = '<?php echo htmlspecialchars($createData['country']); ?>';
                document.getElementById('invoice_marker').value = '<?php echo $createData['invoice_marker']; ?>';
                document.getElementById('bank_name').value = '<?php echo htmlspecialchars($createData['bank_name']); ?>';
                document.getElementById('bank_bic').value = '<?php echo htmlspecialchars($createData['bank_bic']); ?>';
                document.getElementById('bank_iban').value = '<?php echo htmlspecialchars($createData['bank_iban']); ?>';
                document.getElementById('bank_holder').value = '<?php echo htmlspecialchars($createData['bank_holder']); ?>';

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
                        document.getElementById('edit_salutation').value = data.member.salutation || '';
                        document.getElementById('edit_sex').value = data.member.sex || '';
                        document.getElementById('edit_first_name').value = data.member.first_name || '';
                        document.getElementById('edit_last_name').value = data.member.last_name || '';
                        document.getElementById('edit_email').value = data.member.email || '';
                        document.getElementById('edit_phone').value = data.member.phone || '';
                        document.getElementById('edit_join_date').value = data.member.join_date || '';
                        document.getElementById('edit_birth_date').value = data.member.birth_date || '';
                        document.getElementById('edit_membership_type').value = data.member.membership_type || 'Vollmitglied';
                        document.getElementById('edit_status').value = data.member.status || 'Aktiv';
                        document.getElementById('edit_street').value = data.member.street || '';
                        document.getElementById('edit_zip').value = data.member.zip || '';
                        document.getElementById('edit_city').value = data.member.city || '';
                        document.getElementById('edit_country').value = data.member.country || '';
                        document.getElementById('edit_invoice_marker').value = data.member.invoice_marker || '0';
                        document.getElementById('edit_bank_name').value = data.member.bank_name || '';
                        document.getElementById('edit_bank_bic').value = data.member.bank_bic || '';
                        document.getElementById('edit_bank_iban').value = data.member.bank_iban || '';
                        document.getElementById('edit_bank_holder').value = data.member.bank_holder || '';
                        
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
