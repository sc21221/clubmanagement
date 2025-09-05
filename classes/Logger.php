<?php
class Logger {
    private $logFile;
    private $logLevel;
    private $maxFileSize;
    private $maxFiles;
    private $dateFormat;
    
    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;
    
    private $levelNames = [
        self::LEVEL_DEBUG => 'DEBUG',
        self::LEVEL_INFO => 'INFO',
        self::LEVEL_WARNING => 'WARNING',
        self::LEVEL_ERROR => 'ERROR',
        self::LEVEL_CRITICAL => 'CRITICAL'
    ];
    
    public function __construct($config = []) {
        $this->logFile = $config['log_file'] ?? '/app/logs/application.log';
        $this->logLevel = $config['log_level'] ?? self::LEVEL_INFO;
        $this->maxFileSize = $config['max_file_size'] ?? 10485760; // 10MB
        $this->maxFiles = $config['max_files'] ?? 5;
        $this->dateFormat = $config['date_format'] ?? 'Y-m-d H:i:s';
        
        // Log-Verzeichnis erstellen falls nicht vorhanden
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }
    
    public function critical($message, $context = []) {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    private function log($level, $message, $context = []) {
        if ($level < $this->logLevel) {
            return;
        }
        
        $timestamp = date($this->dateFormat);
        $levelName = $this->levelNames[$level];
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $user = $this->getCurrentUser();
        $ip = $this->getClientIP();
        
        $logEntry = sprintf(
            "[%s] %s: %s%s | User: %s | IP: %s | Memory: %s\n",
            $timestamp,
            $levelName,
            $message,
            $contextStr,
            $user,
            $ip,
            $this->formatBytes(memory_get_usage(true))
        );
        
        // Prüfen, ob Log-Datei schreibbar ist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        // Versuche zu schreiben, falle zurück auf error_log bei Fehlern
        $result = @file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            // Fallback: Verwende error_log wenn Datei nicht schreibbar ist
            error_log("Logger: Cannot write to log file: " . $this->logFile);
            error_log("Log entry: " . $logEntry);
            return;
        }
        
        // Log-Rotation prüfen
        $this->rotateLogs();
    }
    
    private function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            return 'ID:' . $_SESSION['user_id'];
        }
        return 'Anonymous';
    }
    
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    private function rotateLogs() {
        if (!file_exists($this->logFile) || filesize($this->logFile) < $this->maxFileSize) {
            return;
        }
        
        // Aktuelle Log-Datei umbenennen
        $timestamp = date('Y-m-d_H-i-s');
        $rotatedFile = $this->logFile . '.' . $timestamp;
        rename($this->logFile, $rotatedFile);
        
        // Alte Log-Dateien löschen
        $logDir = dirname($this->logFile);
        $pattern = basename($this->logFile) . '.*';
        $files = glob($logDir . '/' . $pattern);
        
        if (count($files) > $this->maxFiles) {
            // Nach Zeitstempel sortieren (älteste zuerst)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Überschüssige Dateien löschen
            $filesToDelete = array_slice($files, 0, count($files) - $this->maxFiles);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }
    
    public function logDatabaseOperation($operation, $table, $data = [], $result = null) {
        $context = [
            'operation' => $operation,
            'table' => $table,
            'data' => $data,
            'result' => $result,
            'sql_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        ];
        
        $this->info("Database operation: {$operation} on {$table}", $context);
    }
    
    public function logUserAction($action, $details = []) {
        $context = array_merge([
            'action' => $action,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ], $details);
        
        $this->info("User action: {$action}", $context);
    }
    
    public function logSecurityEvent($event, $details = []) {
        $context = array_merge([
            'event' => $event,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ], $details);
        
        $this->warning("Security event: {$event}", $context);
    }
    
    public function logError($error, $context = []) {
        $context = array_merge([
            'error' => $error,
            'file' => debug_backtrace()[0]['file'] ?? '',
            'line' => debug_backtrace()[0]['line'] ?? '',
            'url' => $_SERVER['REQUEST_URI'] ?? ''
        ], $context);
        
        $this->error("Application error: {$error}", $context);
    }
    
    public function getLogs($lines = 100, $level = null) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $logs = file($this->logFile, FILE_IGNORE_NEW_LINES);
        $logs = array_reverse($logs); // Neueste zuerst
        
        if ($lines > 0) {
            $logs = array_slice($logs, 0, $lines);
        }
        
        if ($level !== null) {
            $levelName = $this->levelNames[$level];
            $logs = array_filter($logs, function($log) use ($levelName) {
                return strpos($log, $levelName) !== false;
            });
        }
        
        return $logs;
    }
    
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
        
        // Alle rotierten Logs löschen
        $logDir = dirname($this->logFile);
        $pattern = basename($this->logFile) . '.*';
        $files = glob($logDir . '/' . $pattern);
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
