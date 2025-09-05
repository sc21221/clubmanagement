<?php
require_once 'LogManager.php';

class Member {
    private $conn;
    private $table_name = "members";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $join_date;
    public $membership_type;
    public $status;
    public $address;
    public $birth_date;
    public $created_at;
    public $updated_at;

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
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->join_date = $row['join_date'];
            $this->membership_type = $row['membership_type'];
            $this->status = $row['status'];
            $this->address = $row['address'];
            $this->birth_date = $row['birth_date'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
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
        
        // Prüfen, ob E-Mail bereits existiert
        if($this->emailExists($this->email)) {
            LogManager::warning("Attempted to create member with duplicate email", [
                'email' => $this->email,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name
            ]);
            return false; // E-Mail bereits vorhanden
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (first_name, last_name, email, phone, join_date, membership_type, status, address, birth_date) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Daten bereinigen
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        // Leere Datumsfelder in NULL konvertieren
        $this->birth_date = empty($this->birth_date) ? null : $this->birth_date;
        
        // Parameter binden
        $stmt->bindParam(1, $this->first_name);
        $stmt->bindParam(2, $this->last_name);
        $stmt->bindParam(3, $this->email);
        $stmt->bindParam(4, $this->phone);
        $stmt->bindParam(5, $this->join_date);
        $stmt->bindParam(6, $this->membership_type);
        $stmt->bindParam(7, $this->status);
        $stmt->bindParam(8, $this->address);
        $stmt->bindParam(9, $this->birth_date);
        
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
        
        // Prüfen, ob E-Mail bereits existiert (außer für aktuelles Mitglied)
        if($this->emailExists($this->email, $this->id)) {
            LogManager::warning("Attempted to update member with duplicate email", [
                'member_id' => $this->id,
                'email' => $this->email,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name
            ]);
            return false; // E-Mail bereits vorhanden
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = ?, last_name = ?, email = ?, phone = ?, 
                      join_date = ?, membership_type = ?, status = ?, address = ?, birth_date = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Daten bereinigen
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        // Leere Datumsfelder in NULL konvertieren
        $this->birth_date = empty($this->birth_date) ? null : $this->birth_date;
        
        // Parameter binden
        $stmt->bindParam(1, $this->first_name);
        $stmt->bindParam(2, $this->last_name);
        $stmt->bindParam(3, $this->email);
        $stmt->bindParam(4, $this->phone);
        $stmt->bindParam(5, $this->join_date);
        $stmt->bindParam(6, $this->membership_type);
        $stmt->bindParam(7, $this->status);
        $stmt->bindParam(8, $this->address);
        $stmt->bindParam(9, $this->birth_date);
        $stmt->bindParam(10, $this->id);
        
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

