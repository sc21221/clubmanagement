# Logging-System Dokumentation

## Übersicht

Das Vereinsverwaltungssystem verfügt über ein umfassendes, konfigurierbares Logging-System, das alle wichtigen Aktionen, Fehler und Performance-Metriken protokolliert.

## Komponenten

### 1. Logger-Klasse (`classes/Logger.php`)
- Hauptklasse für das Logging
- Unterstützt verschiedene Log-Level (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- Automatische Log-Rotation
- Strukturierte Log-Einträge mit Kontext

### 2. LogManager-Klasse (`classes/LogManager.php`)
- Singleton-Pattern für einfache Integration
- Konfigurierbare Logging-Bereiche
- Convenience-Methoden für häufige Logging-Operationen

### 3. Konfiguration (`config/logging.php`)
- Zentrale Konfiguration aller Logging-Einstellungen
- Separate Konfiguration für verschiedene Bereiche
- Ein-/Ausschalten von Logging-Bereichen

## Log-Level

| Level | Beschreibung | Verwendung |
|-------|-------------|------------|
| DEBUG | Detaillierte Informationen | Entwicklung, Debugging |
| INFO | Allgemeine Informationen | Normale Operationen |
| WARNING | Warnungen | Potenzielle Probleme |
| ERROR | Fehler | Fehlgeschlagene Operationen |
| CRITICAL | Kritische Fehler | System-kritische Probleme |

## Logging-Bereiche

### 1. Datenbank-Operationen
- Alle CRUD-Operationen (CREATE, READ, UPDATE, DELETE)
- Query-Performance
- Fehler bei Datenbankoperationen

### 2. Benutzer-Aktionen
- Mitglieder erstellen/bearbeiten/löschen
- Suchoperationen
- Export-Operationen

### 3. Sicherheit
- Fehlgeschlagene Anmeldungen
- SQL-Injection-Versuche
- XSS-Versuche
- Datei-Uploads

### 4. Performance
- Speicherverbrauch
- Ausführungszeiten
- Datenbankabfragen

## Konfiguration

### Grundlegende Einstellungen
```php
'enabled' => true,                    // Logging aktiviert/deaktiviert
'log_file' => '/app/logs/application.log',
'log_level' => Logger::LEVEL_INFO,    // Mindest-Log-Level
'max_file_size' => 10485760,         // 10MB
'max_files' => 5,                    // Anzahl rotierter Dateien
'date_format' => 'Y-m-d H:i:s',      // Zeitstempel-Format
```

### Bereichs-spezifische Einstellungen
```php
'database' => [
    'enabled' => true,
    'log_queries' => true,
    'log_errors' => true,
    'log_slow_queries' => true,
    'slow_query_threshold' => 1.0
],
'security' => [
    'enabled' => true,
    'log_failed_logins' => true,
    'log_sql_injections' => true,
    'log_xss_attempts' => true
],
'user_actions' => [
    'enabled' => true,
    'log_crud_operations' => true,
    'log_search_operations' => true
],
'performance' => [
    'enabled' => true,
    'log_memory_usage' => true,
    'log_execution_time' => true
]
```

## Verwendung

### Einfache Logs
```php
LogManager::info("Benutzer angemeldet", ['user_id' => 123]);
LogManager::error("Datenbankfehler", ['error' => $e->getMessage()]);
LogManager::warning("Langsame Abfrage", ['query_time' => 2.5]);
```

### Spezielle Logging-Methoden
```php
// Datenbank-Operationen
LogManager::logDatabaseOperation('CREATE', 'members', $data, 'SUCCESS');

// Benutzer-Aktionen
LogManager::logUserAction('Member created', ['member_name' => 'Max Mustermann']);

// Sicherheits-Ereignisse
LogManager::logSecurityEvent('Failed login attempt', ['ip' => '192.168.1.1']);

// Fehler
LogManager::logError('Database connection failed', ['error' => $e->getMessage()]);
```

## Log-Format

Jeder Log-Eintrag enthält:
- Zeitstempel
- Log-Level
- Nachricht
- Kontext (JSON)
- Benutzer-Information
- IP-Adresse
- Speicherverbrauch

Beispiel:
```
[2024-01-15 14:30:25] INFO: Member created {"member_name":"Max Mustermann","email":"max@example.com"} | User: Anonymous | IP: 192.168.1.100 | Memory: 2.5 MB
```

## Log-Viewer

### Web-Interface (`logs.php`)
- Anzeige aller Logs
- Filterung nach Log-Level
- Statistiken
- Logs löschen

### Features
- Farbkodierung nach Log-Level
- Suchfunktion
- Export-Funktion
- Automatische Aktualisierung

## Log-Rotation

- Automatische Rotation bei Erreichen der maximalen Dateigröße
- Beibehaltung einer konfigurierbaren Anzahl von Log-Dateien
- Zeitstempel-basierte Benennung rotierter Dateien

## Sicherheit

- Logs enthalten keine sensiblen Daten (Passwörter, etc.)
- IP-Adressen werden protokolliert
- Benutzer-Aktionen werden nachverfolgt
- Fehlgeschlagene Sicherheitsversuche werden geloggt

## Performance

- Asynchrones Logging (kein Blocking)
- Konfigurierbare Log-Level für Produktionsumgebung
- Automatische Bereinigung alter Logs
- Optimierte Datei-Operationen

## Monitoring

### Wichtige Metriken
- Anzahl der Fehler pro Stunde/Tag
- Performance von Datenbankabfragen
- Benutzer-Aktivitäten
- Sicherheits-Ereignisse

### Alerts
- Kritische Fehler
- Ungewöhnliche Aktivitäten
- Performance-Probleme
- Sicherheits-Verletzungen

## Wartung

### Regelmäßige Aufgaben
- Überprüfung der Log-Dateigrößen
- Bereinigung alter Logs
- Überwachung der Fehlerrate
- Performance-Analyse

### Troubleshooting
- Logs nach spezifischen Fehlern durchsuchen
- Performance-Probleme identifizieren
- Benutzer-Aktivitäten nachverfolgen
- Sicherheits-Incidents analysieren

