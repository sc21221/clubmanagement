<?php
require_once 'LogManager.php';

class Member {
    private $conn;
    private $table_name = "members";

    public $id;
    public $club_id;
    public $salutation;
    public $sex;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $invoice_marker;
    public $join_date;
    public $leave_date;
    public $birth_date;
    public $membership_type;
    public $status;
    public $invoice_number;
    public $street;
    public $zip;
    public $city;
    public $country;
    public $bank_name;
    public $bank_bic;
    public $bank_iban;
    public $bank_holder;
    public $created_at;
    public $updated_at;
    public $updated_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Alle Mitglieder abrufen
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY last_name, first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Einzelnes Mitglied abrufen
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->id = $row['id'];
            $this->club_id = $row['club_id'];
            $this->salutation = $row['salutation'];
            $this->sex = $row['sex'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->join_date = $row['join_date'];
            $this->leave_date = $row['leave_date'];
            $this->birth_date = $row['birth_date'];
            $this->membership_type = $row['membership_type'];
            $this->status = $row['status'];
            $this->invoice_marker = $row['invoice_marker'];
            $this->street = $row['street'];
            $this->zip = $row['zip'];
            $this->city = $row['city'];
            $this->country = $row['country'];
            $this->bank_name = $row['bank_name'];
            $this->bank_bic = $row['bank_bic'];
            $this->bank_iban = $row['bank_iban'];
            $this->bank_holder = $row['bank_holder'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->updated_by = $row['updated_by'];
            return true;
        }
        return false;
    }

    // Prüfen, ob E-Mail bereits existiert
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE email = ?";
        if($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        if($exclude_id) {
            $stmt->bindParam(2, $exclude_id);
        }
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Neues Mitglied erstellen
    public function create() {
        $startTime = microtime(true);
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (salutation, sex, first_name, last_name, email, phone, 
                   membership_type, status, invoice_marker,
                   street, zip, city, country, 
                   join_date, leave_date, birth_date,
                   bank_name, bank_bic, bank_iban, bank_holder) 
                  VALUES (:salutation, :sex, :first_name, :last_name, :email, :phone, :membership_type, :status, :invoice_marker, :street, :zip, :city, :country, :join_date, :leave_date, :birth_date, :bank_name, :bank_bic, :bank_iban, :bank_holder)";
        
        $stmt = $this->conn->prepare($query);
        
        // Daten bereinigen
        $this->salutation = htmlspecialchars(strip_tags($this->salutation));
        $this->sex = htmlspecialchars(strip_tags($this->sex));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->membership_type = htmlspecialchars(strip_tags($this->membership_type));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->invoice_marker = htmlspecialchars(strip_tags($this->invoice_marker));
        $this->street = htmlspecialchars(strip_tags($this->street));
        $this->zip = htmlspecialchars(strip_tags($this->zip));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->join_date = htmlspecialchars(strip_tags($this->join_date));
        $this->leave_date = htmlspecialchars(strip_tags($this->leave_date));
        $this->birth_date = htmlspecialchars(strip_tags($this->birth_date));
        $this->bank_name = htmlspecialchars(strip_tags($this->bank_name));
        $this->bank_bic = htmlspecialchars(strip_tags($this->bank_bic));
        $this->bank_iban = htmlspecialchars(strip_tags($this->bank_iban));
        $this->bank_holder = htmlspecialchars(strip_tags($this->bank_holder));
                   
        // Leere Datumsfelder in NULL konvertieren
        $this->birth_date = empty($this->birth_date) ? null : $this->birth_date;
        $this->leave_date = empty($this->leave_date) ? null : $this->leave_date;
        
        // Parameter binden
        $stmt->bindParam(':salutation', $this->salutation);
        $stmt->bindParam(':sex', $this->sex);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':membership_type', $this->membership_type);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':invoice_marker', $this->invoice_marker);
        $stmt->bindParam(':street', $this->street);
        $stmt->bindParam(':zip', $this->zip);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':join_date', $this->join_date);
        $stmt->bindParam(':leave_date', $this->leave_date);
        $stmt->bindParam(':birth_date', $this->birth_date);
        $stmt->bindParam(':bank_name', $this->bank_name);
        $stmt->bindParam(':bank_bic', $this->bank_bic);
        $stmt->bindParam(':bank_iban', $this->bank_iban);
        $stmt->bindParam(':bank_holder', $this->bank_holder);
        
        if($stmt->execute()) {
            $executionTime = microtime(true) - $startTime;
            LogManager::logDatabaseOperation('CREATE', $this->table_name, [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'membership_type' => $this->membership_type
            ], 'SUCCESS');
            
            LogManager::logUserAction('Member created', [
                'member_name' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'execution_time' => $executionTime
            ]);
            
            return true;
        }
        
        LogManager::error("Failed to create member", [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'error_info' => $stmt->errorInfo()
        ]);
        
        return false;
    }

    // Mitglied aktualisieren
    public function update() {
        $startTime = microtime(true);
        
       
        $query = "UPDATE " . $this->table_name . " 
                  SET salutation = :salutation, sex = :sex, first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, 
                      membership_type = :membership_type, status = :status, invoice_marker = :invoice_marker,
                      street = :street, zip = :zip, city = :city, country = :country, 
                      join_date = :join_date, leave_date = :leave_date, birth_date = :birth_date,
                      bank_name = :bank_name, bank_bic = :bank_bic, bank_iban = :bank_iban, bank_holder = :bank_holder
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Daten bereinigen
        $this->salutation = htmlspecialchars(strip_tags($this->salutation));
        $this->sex = htmlspecialchars(strip_tags($this->sex));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->membership_type = htmlspecialchars(strip_tags($this->membership_type));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->invoice_marker = htmlspecialchars(strip_tags($this->invoice_marker));
        $this->street = htmlspecialchars(strip_tags($this->street));
        $this->zip = htmlspecialchars(strip_tags($this->zip));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->join_date = htmlspecialchars(strip_tags($this->join_date));
        $this->leave_date = htmlspecialchars(strip_tags($this->leave_date));
        $this->birth_date = htmlspecialchars(strip_tags($this->birth_date));
        $this->bank_name = htmlspecialchars(strip_tags($this->bank_name));
        $this->bank_bic = htmlspecialchars(strip_tags($this->bank_bic));
        $this->bank_iban = htmlspecialchars(strip_tags($this->bank_iban));
        $this->bank_holder = htmlspecialchars(strip_tags($this->bank_holder));
        
        // Leere Datumsfelder in NULL konvertieren
        $this->birth_date = empty($this->birth_date) ? null : $this->birth_date;
        $this->leave_date = empty($this->leave_date) ? null : $this->leave_date;
        
        // Parameter binden
        $stmt->bindParam(':salutation', $this->salutation);
        $stmt->bindParam(':sex', $this->sex);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':membership_type', $this->membership_type);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':invoice_marker', $this->invoice_marker);
        $stmt->bindParam(':street', $this->street);
        $stmt->bindParam(':zip', $this->zip);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':join_date', $this->join_date);
        $stmt->bindParam(':leave_date', $this->leave_date);
        $stmt->bindParam(':birth_date', $this->birth_date);
        $stmt->bindParam(':bank_name', $this->bank_name);
        $stmt->bindParam(':bank_bic', $this->bank_bic);
        $stmt->bindParam(':bank_iban', $this->bank_iban);
        $stmt->bindParam(':bank_holder', $this->bank_holder);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            $executionTime = microtime(true) - $startTime;
            LogManager::logDatabaseOperation('UPDATE', $this->table_name, [
                'member_id' => $this->id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'membership_type' => $this->membership_type
            ], 'SUCCESS');
            
            LogManager::logUserAction('Member updated', [
                'member_id' => $this->id,
                'member_name' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'execution_time' => $executionTime
            ]);
            
            return true;
        }
        
        LogManager::error("Failed to update member", [
            'member_id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'error_info' => $stmt->errorInfo()
        ]);
        
        return false;
    }

    // Mitglied löschen
    public function delete() {
        $startTime = microtime(true);
        
        // Mitgliederdaten vor dem Löschen loggen
        $this->readOne();
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            $executionTime = microtime(true) - $startTime;
            LogManager::logDatabaseOperation('DELETE', $this->table_name, [
                'member_id' => $this->id,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email
            ], 'SUCCESS');
            
            LogManager::logUserAction('Member deleted', [
                'member_id' => $this->id,
                'member_name' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'execution_time' => $executionTime
            ]);
            
            return true;
        }
        
        LogManager::error("Failed to delete member", [
            'member_id' => $this->id,
            'error_info' => $stmt->errorInfo()
        ]);
        
        return false;
    }

    // Mitglieder suchen
    public function search($search_term) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? 
                  ORDER BY last_name, first_name";
        
        $search_term = "%{$search_term}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $search_term);
        $stmt->bindParam(2, $search_term);
        $stmt->bindParam(3, $search_term);
        $stmt->execute();
        
        return $stmt;
    }

    // Mitglieder nach Status filtern
    public function readByStatus($status) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = ? ORDER BY last_name, first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();
        return $stmt;
    }

    // Mitglieder nach Mitgliedstyp filtern
    public function readByMembershipType($type) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE membership_type = ? ORDER BY last_name, first_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $type);
        $stmt->execute();
        return $stmt;
    }
}

