-- Vereinsverwaltungs-Datenbank
CREATE DATABASE IF NOT EXISTS club_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE club_management;

-- Mitglieder-Tabelle
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(50),
    join_date DATE NOT NULL,
    membership_type ENUM('Vollmitglied', 'Fördermitglied', 'Jugendmitglied', 'Ehrenmitglied') DEFAULT 'Vollmitglied',
    status ENUM('Aktiv', 'Inaktiv', 'Gesperrt') DEFAULT 'Aktiv',
    address TEXT,
    birth_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Beitragsklassen-Tabelle
CREATE TABLE IF NOT EXISTS fee_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    base_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    billing_cycle ENUM('Jährlich', 'Halbjährlich', 'Vierteljährlich', 'Monatlich') DEFAULT 'Jährlich',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Mitglieder-Beitragsklassen-Zuordnung
CREATE TABLE IF NOT EXISTS member_fee_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    fee_class_id INT NOT NULL,
    custom_amount DECIMAL(10,2) NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_class_id) REFERENCES fee_classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member_fee_class (member_id, fee_class_id, start_date)
);

-- Mitgliedsbeiträge-Tabelle
CREATE TABLE IF NOT EXISTS membership_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    fee_class_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE NULL,
    status ENUM('Offen', 'Bezahlt', 'Überfällig') DEFAULT 'Offen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (fee_class_id) REFERENCES fee_classes(id) ON DELETE CASCADE
);

-- Veranstaltungen-Tabelle
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(255),
    max_participants INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Veranstaltungsteilnehmer-Tabelle
CREATE TABLE IF NOT EXISTS event_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    member_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Angemeldet', 'Bestätigt', 'Storniert') DEFAULT 'Angemeldet',
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (event_id, member_id)
);

-- Beispieldaten einfügen
INSERT INTO members (first_name, last_name, email, phone, join_date, membership_type, status, address) VALUES
('Max', 'Mustermann', 'max.mustermann@example.com', '+49 123 456789', '2023-01-15', 'Vollmitglied', 'Aktiv', 'Musterstraße 123, 12345 Musterstadt'),
('Anna', 'Schmidt', 'anna.schmidt@example.com', '+49 987 654321', '2023-03-20', 'Fördermitglied', 'Aktiv', 'Beispielweg 456, 54321 Beispielstadt'),
('Peter', 'Müller', 'peter.mueller@example.com', '+49 555 123456', '2023-06-10', 'Jugendmitglied', 'Aktiv', 'Jugendstraße 789, 67890 Jugendstadt');

-- Beispieldaten für Beitragsklassen
INSERT INTO fee_classes (name, description, base_amount, billing_cycle) VALUES
('Standard', 'Standardbeitrag für Vollmitglieder', 50.00, 'Jährlich'),
('Ermäßigt', 'Ermäßigter Beitrag für Studenten und Senioren', 30.00, 'Jährlich'),
('Jugend', 'Beitrag für Jugendliche unter 18 Jahren', 25.00, 'Jährlich'),
('Förderer', 'Beitrag für Fördermitglieder', 100.00, 'Jährlich'),
('Familie', 'Familienbeitrag (2 Erwachsene + Kinder)', 80.00, 'Jährlich');

-- Beispieldaten für Mitglieder-Beitragsklassen-Zuordnung
INSERT INTO member_fee_classes (member_id, fee_class_id, start_date) VALUES
(1, 1, '2023-01-15'),
(2, 4, '2023-03-20'),
(3, 3, '2023-06-10');

-- Beispieldaten für Mitgliedsbeiträge
INSERT INTO membership_fees (member_id, fee_class_id, amount, due_date, status) VALUES
(1, 1, 50.00, '2024-01-31', 'Bezahlt'),
(2, 4, 100.00, '2024-01-31', 'Bezahlt'),
(3, 3, 25.00, '2024-01-31', 'Offen');

-- Beispieldaten für Veranstaltungen
INSERT INTO events (title, description, event_date, location, max_participants) VALUES
('Jahreshauptversammlung 2024', 'Jährliche Mitgliederversammlung mit Wahlen', '2024-03-15 19:00:00', 'Vereinsheim', 100),
('Sommerfest', 'Gemeinsames Fest mit Grillen und Spielen', '2024-07-20 14:00:00', 'Vereinsgelände', 150);
