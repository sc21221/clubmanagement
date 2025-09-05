<?php
class FeeClass {
    private $conn;
    private $table_name = "fee_classes";

    public $id;
    public $name;
    public $description;
    public $base_amount;
    public $currency;
    public $billing_cycle;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Alle Beitragsklassen abrufen
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Aktive Beitragsklassen abrufen
    public function readActive() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_active = 1 ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Einzelne Beitragsklasse abrufen
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->base_amount = $row['base_amount'];
            $this->currency = $row['currency'];
            $this->billing_cycle = $row['billing_cycle'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Neue Beitragsklasse erstellen
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, base_amount, currency, billing_cycle, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Daten bereinigen
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Parameter binden
        $stmt->bindParam(1, $this->name);
        $stmt->bindParam(2, $this->description);
        $stmt->bindParam(3, $this->base_amount);
        $stmt->bindParam(4, $this->currency);
        $stmt->bindParam(5, $this->billing_cycle);
        $stmt->bindParam(6, $this->is_active);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Beitragsklasse aktualisieren
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = ?, description = ?, base_amount = ?, 
                      currency = ?, billing_cycle = ?, is_active = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Daten bereinigen
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Parameter binden
        $stmt->bindParam(1, $this->name);
        $stmt->bindParam(2, $this->description);
        $stmt->bindParam(3, $this->base_amount);
        $stmt->bindParam(4, $this->currency);
        $stmt->bindParam(5, $this->billing_cycle);
        $stmt->bindParam(6, $this->is_active);
        $stmt->bindParam(7, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Beitragsklasse löschen
    public function delete() {
        // Prüfen, ob Beitragsklasse noch Mitgliedern zugeordnet ist
        $check_query = "SELECT COUNT(*) as count FROM member_fee_classes WHERE fee_class_id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(1, $this->id);
        $check_stmt->execute();
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($result['count'] > 0) {
            return false; // Kann nicht gelöscht werden, da noch zugeordnet
        }
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Beitragsklasse nach Namen suchen
    public function searchByName($search_term) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE name LIKE ? OR description LIKE ? 
                  ORDER BY name";
        
        $search_term = "%{$search_term}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $search_term);
        $stmt->bindParam(2, $search_term);
        $stmt->execute();
        
        return $stmt;
    }

    // Beitragsklassen nach Abrechnungszyklus filtern
    public function readByBillingCycle($cycle) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE billing_cycle = ? ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cycle);
        $stmt->execute();
        return $stmt;
    }

    // Anzahl der Mitglieder pro Beitragsklasse
    public function getMemberCount($fee_class_id) {
        $query = "SELECT COUNT(*) as count FROM member_fee_classes 
                  WHERE fee_class_id = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $fee_class_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Alle Beitragsklassen mit Mitgliederanzahl
    public function readWithMemberCount() {
        $query = "SELECT fc.*, 
                         (SELECT COUNT(*) FROM member_fee_classes mfc 
                          WHERE mfc.fee_class_id = fc.id AND mfc.is_active = 1) as member_count
                  FROM " . $this->table_name . " fc 
                  ORDER BY fc.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

