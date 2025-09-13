-- Vereinsverwaltungs-Datenbank
CREATE DATABASE IF NOT EXISTS club_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE club_management;

CREATE TABLE clubs (
  Id int auto_increment primary key,
  name varchar(100) NOT NULL,
  name2 varchar(100) DEFAULT NULL,
  boss varchar(40) DEFAULT NULL,
  street varchar(60) DEFAULT NULL,
  zip varchar(20) DEFAULT NULL,
  city varchar(60) DEFAULT NULL,
  country varchar(20) DEFAULT NULL,
  email varchar(80) DEFAULT NULL,
  phone varchar(20) DEFAULT NULL,
  bank_name varchar(30) DEFAULT NULL,
  bank_creditor_id varchar(32) NOT NULL,
  bank_bic varchar(32) NOT NULL,
  bank_iban varchar(32) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_by varchar(80) DEFAULT NULL,
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

INSERT INTO `clubs` (`name`, `boss`, `street`, `zip`, `city`, `country`, `email`, `phone`, `bank_name`, `bank_Creditor_ID`, `bank_BIC`, `bank_IBAN`,created_at) VALUES
('Musikverein Denkendorf', 'Michael Rill', 'Schmiedstr. 1', '85095', 'Gelbelsee', 'D', 'michael.rill@musikverein-denkendorf.de', '08465 3176', 'Raiffeisenbank Bayern Mitte', 'DE90ZZZ00001055067', 'GENODEF1INP', 'DE65721608180007114818', '2014-04-11 17:31:08');


-- Mitglieder-Tabelle
CREATE TABLE IF NOT EXISTS members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL Default 1,
    salutation ENUM('Herr', 'Frau', 'Divers') DEFAULT 'Herr',
    sex ENUM('Männlich', 'Weiblich', 'Divers') DEFAULT 'Männlich',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    membership_type ENUM('Vollmitglied', 'Fördermitglied', 'Jugendmitglied', 'Ehrenmitglied') DEFAULT 'Vollmitglied',
    status ENUM('Aktiv', 'Inaktiv', 'Gesperrt') DEFAULT 'Aktiv',
    invoice_marker boolean DEFAULT FALSE,
    street VARCHAR(255),
    zip VARCHAR(255),
    city VARCHAR(255),
    country VARCHAR(255),
    join_date DATE NOT NULL,
    leave_date DATE,
    birth_date DATE NOT NULL,
    bank_name VARCHAR(255),
    bank_bic VARCHAR(255),
    bank_iban VARCHAR(255),
    bank_holder VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by varchar(80) DEFAULT NULL,
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
INSERT INTO members (first_name, last_name, email, phone,birth_date, join_date, membership_type, status, street, zip, city, country,bank_name,bank_bic,bank_iban,bank_holder) VALUES
('Max', 'Mustermann', 'max.mustermann@example.com', '+49 123 456789', '1999-01-15','2023-01-15', 'Vollmitglied', 'Aktiv', 'Musterstraße 123', '12345', 'Musterstadt', 'D', 'Bank1', 'BIC1', 'IBAN1', 'Holder1'),
('Anna', 'Schmidt', 'anna.schmidt@example.com', '+49 987 654321', '1999-03-20', '2023-03-20', 'Fördermitglied', 'Aktiv', 'Beispielweg 456', '54321', 'Beispielstadt', 'D', 'Bank2', 'BIC2', 'IBAN2', 'Holder2'),
('Peter', 'Müller', 'peter.mueller@example.com', '+49 555 123456', '2009-06-10', '2023-06-10', 'Jugendmitglied', 'Aktiv', 'Jugendstraße 789', '67890', 'Jugendstadt', 'D', 'Bank3', 'BIC3', 'IBAN3', 'Holder3')
;

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
