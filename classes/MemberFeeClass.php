<?php
class MemberFeeClass {
    private $conn;
    private $table_name = "member_fee_classes";

    public $id;
    public $member_id;
    public $fee_class_id;
    public $custom_amount;
    public $start_date;
    public $end_date;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Alle Zuordnungen abrufen
    public function read() {
        $query = "SELECT mfc.*, m.first_name, m.last_name, fc.name as fee_class_name, fc.base_amount
                  FROM " . $this->table_name . " mfc
                  JOIN members m ON mfc.member_id = m.id
                  JOIN fee_classes fc ON mfc.fee_class_id = fc.id
                  ORDER BY m.last_name, m.first_name, mfc.start_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Zuordnungen für ein bestimmtes Mitglied abrufen
    public function readByMember($member_id) {
        $query = "SELECT mfc.*, fc.name as fee_class_name, fc.base_amount, fc.description, fc.billing_cycle
                  FROM " . $this->table_name . " mfc
                  JOIN fee_classes fc ON mfc.fee_class_id = fc.id
                  WHERE mfc.member_id = ? AND mfc.is_active = 1
                  ORDER BY mfc.start_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->execute();
        return $stmt;
    }

    // Aktive Zuordnungen für ein Mitglied abrufen
    public function readActiveByMember($member_id) {
        $query = "SELECT mfc.*, fc.name as fee_class_name, fc.base_amount, fc.description, fc.billing_cycle
                  FROM " . $this->table_name . " mfc
                  JOIN fee_classes fc ON mfc.fee_class_id = fc.id
                  WHERE mfc.member_id = ? AND mfc.is_active = 1 
                  AND (mfc.end_date IS NULL OR mfc.end_date >= CURDATE())
                  ORDER BY mfc.start_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->execute();
        return $stmt;
    }

    // Einzelne Zuordnung abrufen
    public function readOne() {
        $query = "SELECT mfc.*, m.first_name, m.last_name, fc.name as fee_class_name, fc.base_amount
                  FROM " . $this->table_name . " mfc
                  JOIN members m ON mfc.member_id = m.id
                  JOIN fee_classes fc ON mfc.fee_class_id = fc.id
                  WHERE mfc.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->member_id = $row['member_id'];
            $this->fee_class_id = $row['fee_class_id'];
            $this->custom_amount = $row['custom_amount'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Neue Zuordnung erstellen
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (member_id, fee_class_id, custom_amount, start_date, end_date, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Parameter binden
        $stmt->bindParam(1, $this->member_id);
        $stmt->bindParam(2, $this->fee_class_id);
        $stmt->bindParam(3, $this->custom_amount);
        $stmt->bindParam(4, $this->start_date);
        $stmt->bindParam(5, $this->end_date);
        $stmt->bindParam(6, $this->is_active);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Zuordnung aktualisieren
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET member_id = ?, fee_class_id = ?, custom_amount = ?, 
                      start_date = ?, end_date = ?, is_active = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Parameter binden
        $stmt->bindParam(1, $this->member_id);
        $stmt->bindParam(2, $this->fee_class_id);
        $stmt->bindParam(3, $this->custom_amount);
        $stmt->bindParam(4, $this->start_date);
        $stmt->bindParam(5, $this->end_date);
        $stmt->bindParam(6, $this->is_active);
        $stmt->bindParam(7, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Zuordnung löschen
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Zuordnung deaktivieren (statt zu löschen)
    public function deactivate() {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Prüfen, ob Mitglied bereits einer Beitragsklasse zugeordnet ist
    public function isMemberAssigned($member_id, $fee_class_id, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE member_id = ? AND fee_class_id = ? AND is_active = 1";
        
        if($exclude_id) {
            $query .= " AND id != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->bindParam(2, $fee_class_id);
        
        if($exclude_id) {
            $stmt->bindParam(3, $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    // Alle aktiven Zuordnungen mit Mitglieder- und Beitragsklasseninformationen
    public function readWithDetails() {
        $query = "SELECT mfc.*, 
                         m.first_name, m.last_name, m.email,
                         fc.name as fee_class_name, fc.base_amount, fc.description, fc.billing_cycle,
                         COALESCE(mfc.custom_amount, fc.base_amount) as effective_amount
                  FROM " . $this->table_name . " mfc
                  JOIN members m ON mfc.member_id = m.id
                  JOIN fee_classes fc ON mfc.fee_class_id = fc.id
                  WHERE mfc.is_active = 1
                  ORDER BY m.last_name, m.first_name, mfc.start_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Mitglieder nach Beitragsklasse filtern
    public function readMembersByFeeClass($fee_class_id) {
        $query = "SELECT mfc.*, m.first_name, m.last_name, m.email, m.phone, m.join_date
                  FROM " . $this->table_name . " mfc
                  JOIN members m ON mfc.member_id = m.id
                  WHERE mfc.fee_class_id = ? AND mfc.is_active = 1
                  ORDER BY m.last_name, m.first_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fee_class_id);
        $stmt->execute();
        return $stmt;
    }

    // Beitragsklassen nach Mitglied filtern
    public function readFeeClassesByMember($member_id) {
        $query = "SELECT mfc.*, fc.name, fc.description, fc.base_amount, fc.billing_cycle
                  FROM " . $this->table_name . " mfc
                  JOIN fee_classes fc ON mfc.fee_class_id = fc.id
                  WHERE mfc.member_id = ? AND mfc.is_active = 1
                  ORDER BY mfc.start_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->execute();
        return $stmt;
    }
}

