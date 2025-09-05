# Vereinsverwaltungsanwendung

Eine moderne PHP-basierte Webanwendung zur Verwaltung von Vereinsmitgliedern, Veranstaltungen und Mitgliedsbeiträgen.

## Funktionen

- **Mitgliederverwaltung**: Hinzufügen, Bearbeiten, Löschen und Durchsuchen von Mitgliedern
- **Beitragsklassenverwaltung**: Erstellen und verwalten verschiedener Beitragsklassen mit individuellen Beträgen
- **Mitglieder-Beitragsklassen-Zuordnung**: Flexible Zuordnung von Mitgliedern zu einer oder mehreren Beitragsklassen
- **Individuelle Beiträge**: Möglichkeit, von der Standardbeitragsklasse abweichende Beträge für einzelne Mitglieder zu definieren
- **Mitgliedsdaten**: Vollständige Verwaltung aller relevanten Mitgliedsinformationen
- **Suchfunktion**: Schnelle Suche nach Namen, E-Mail oder anderen Kriterien
- **Responsive Design**: Moderne Benutzeroberfläche mit Bootstrap 5
- **Sichere Datenbankabfragen**: Verwendung von PDO mit vorbereiteten Statements

## Systemanforderungen

- PHP 7.4 oder höher
- MySQL 5.7 oder höher
- Web-Server (Apache/Nginx)
- PDO MySQL Extension

## Installation

### 1. Datenbank einrichten

1. Erstellen Sie eine neue MySQL-Datenbank
2. Importieren Sie die Datei `database/schema.sql` in Ihre Datenbank
3. Passen Sie die Datenbankverbindungsdaten in `config/database.php` an:

```php
private $host = "localhost";
private $db_name = "ihre_datenbank_name";
private $username = "ihr_benutzername";
private $password = "ihr_passwort";
```

### 2. Dateien hochladen

Laden Sie alle Projektdateien in das gewünschte Verzeichnis Ihres Web-Servers hoch.

### 3. Berechtigungen setzen

Stellen Sie sicher, dass der Web-Server Schreibrechte auf das Projektverzeichnis hat.

### 4. Anwendung testen

Öffnen Sie die Anwendung in Ihrem Browser. Die Hauptseite sollte unter `index.php` erreichbar sein.

## Verzeichnisstruktur

```
club-management/
├── config/
│   └── database.php          # Datenbankverbindungskonfiguration
├── classes/
│   ├── Member.php            # Member-Klasse für Datenbankoperationen
│   ├── FeeClass.php          # FeeClass-Klasse für Beitragsklassen
│   └── MemberFeeClass.php    # MemberFeeClass-Klasse für Zuordnungen
├── database/
│   └── schema.sql            # MySQL-Datenbankschema
├── index.php                 # Hauptseite der Anwendung
├── fee_classes.php           # Beitragsklassenverwaltung
├── member_fees.php           # Mitglieder-Beitragsklassen-Zuordnung
└── README.md                 # Diese Datei
```

## Datenbankstruktur

### Mitglieder-Tabelle (`members`)
- Persönliche Daten (Name, E-Mail, Telefon, Adresse)
- Vereinsdaten (Beitrittsdatum, Mitgliedstyp, Status)
- Zeitstempel für Erstellung und Aktualisierung

### Mitgliedsbeiträge-Tabelle (`membership_fees`)
- Beitragshöhe und Fälligkeitsdatum
- Zahlungsstatus (Offen, Bezahlt, Überfällig)

### Veranstaltungen-Tabelle (`events`)
- Veranstaltungsdetails (Titel, Beschreibung, Datum, Ort)
- Maximale Teilnehmeranzahl

### Veranstaltungsteilnehmer-Tabelle (`event_participants`)
- Verknüpfung zwischen Veranstaltungen und Teilnehmern
- Anmeldestatus

## Verwendung

### Mitglieder verwalten

1. **Neues Mitglied hinzufügen**: Klicken Sie auf "Neues Mitglied" und füllen Sie das Formular aus
2. **Mitglied bearbeiten**: Klicken Sie auf das Bearbeiten-Symbol in der Mitgliedertabelle
3. **Mitglied löschen**: Klicken Sie auf das Löschen-Symbol und bestätigen Sie die Aktion
4. **Mitglieder suchen**: Verwenden Sie die Suchleiste oben in der Tabelle

### Beitragsklassen verwalten

1. **Neue Beitragsklasse erstellen**: Gehen Sie zu "Beitragsklassen" und klicken Sie auf "Neue Beitragsklasse"
2. **Beitragsklasse bearbeiten**: Klicken Sie auf das Bearbeiten-Symbol in der Beitragsklassentabelle
3. **Beitragsklasse löschen**: Klicken Sie auf das Löschen-Symbol (nur möglich, wenn keine Mitglieder zugeordnet sind)

### Mitglieder zu Beitragsklassen zuordnen

1. **Neue Zuordnung erstellen**: Gehen Sie zu "Zuordnungen" und klicken Sie auf "Neue Zuordnung"
2. **Mitglied und Beitragsklasse auswählen**: Wählen Sie ein Mitglied und eine Beitragsklasse aus
3. **Individuellen Beitrag definieren**: Optional können Sie einen von der Standardbeitragsklasse abweichenden Betrag festlegen
4. **Zeitraum festlegen**: Definieren Sie Start- und optional Enddatum der Zuordnung

### Daten exportieren

Die Anwendung kann leicht um Export-Funktionen erweitert werden (CSV, PDF, etc.).

## Sicherheit

- Alle Benutzereingaben werden bereinigt und validiert
- Verwendung von vorbereiteten SQL-Statements gegen SQL-Injection
- XSS-Schutz durch `htmlspecialchars()`

## Erweiterungen

Die Anwendung kann um folgende Funktionen erweitert werden:

- **Benutzerverwaltung**: Login-System mit verschiedenen Berechtigungsstufen
- **E-Mail-Benachrichtigungen**: Automatische Benachrichtigungen bei Beitragsfälligkeit
- **Berichte und Statistiken**: Mitgliederstatistiken und Finanzberichte
- **API-Schnittstelle**: REST-API für mobile Anwendungen
- **Backup-System**: Automatische Datenbanksicherungen

## Support

Bei Fragen oder Problemen wenden Sie sich an den Entwickler oder erstellen Sie ein Issue im Projekt-Repository.

## Lizenz

Diese Anwendung steht unter der MIT-Lizenz zur freien Verfügung.

## Changelog

### Version 1.0.0
- Grundlegende Mitgliederverwaltung
- Suchfunktion
- Responsive Benutzeroberfläche
- MySQL-Datenbankintegration
