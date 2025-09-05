<?php
require_once 'Logger.php';

class LogManager {
    private static $instance = null;
    private $logger;
    private $config;
    
    private function __construct() {
        $this->config = require_once __DIR__ . '/../config/logging.php';
        $this->logger = new Logger($this->config);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getLogger() {
        return $this->logger;
    }
    
    public function isEnabled() {
        return $this->config['enabled'];
    }
    
    public function isDatabaseLoggingEnabled() {
        return $this->config['database']['enabled'];
    }
    
    public function isSecurityLoggingEnabled() {
        return $this->config['security']['enabled'];
    }
    
    public function isUserActionLoggingEnabled() {
        return $this->config['user_actions']['enabled'];
    }
    
    public function isPerformanceLoggingEnabled() {
        return $this->config['performance']['enabled'];
    }
    
    // Convenience-Methoden
    public static function debug($message, $context = []) {
        if (self::getInstance()->isEnabled()) {
            try {
                self::getInstance()->getLogger()->debug($message, $context);
            } catch (Exception $e) {
                // Fallback: Verwende error_log wenn Logger fehlschlägt
                error_log("LogManager Debug: " . $message . " | Context: " . json_encode($context));
            }
        }
    }
    
    public static function info($message, $context = []) {
        if (self::getInstance()->isEnabled()) {
            try {
                self::getInstance()->getLogger()->info($message, $context);
            } catch (Exception $e) {
                error_log("LogManager Info: " . $message . " | Context: " . json_encode($context));
            }
        }
    }
    
    public static function warning($message, $context = []) {
        if (self::getInstance()->isEnabled()) {
            try {
                self::getInstance()->getLogger()->warning($message, $context);
            } catch (Exception $e) {
                error_log("LogManager Warning: " . $message . " | Context: " . json_encode($context));
            }
        }
    }
    
    public static function error($message, $context = []) {
        if (self::getInstance()->isEnabled()) {
            try {
                self::getInstance()->getLogger()->error($message, $context);
            } catch (Exception $e) {
                error_log("LogManager Error: " . $message . " | Context: " . json_encode($context));
            }
        }
    }
    
    public static function critical($message, $context = []) {
        if (self::getInstance()->isEnabled()) {
            try {
                self::getInstance()->getLogger()->critical($message, $context);
            } catch (Exception $e) {
                error_log("LogManager Critical: " . $message . " | Context: " . json_encode($context));
            }
        }
    }
    
    public static function logDatabaseOperation($operation, $table, $data = [], $result = null) {
        if (self::getInstance()->isDatabaseLoggingEnabled()) {
            try {
                self::getInstance()->getLogger()->logDatabaseOperation($operation, $table, $data, $result);
            } catch (Exception $e) {
                error_log("LogManager Database: {$operation} on {$table} | Data: " . json_encode($data));
            }
        }
    }
    
    public static function logUserAction($action, $details = []) {
        if (self::getInstance()->isUserActionLoggingEnabled()) {
            try {
                self::getInstance()->getLogger()->logUserAction($action, $details);
            } catch (Exception $e) {
                error_log("LogManager UserAction: {$action} | Details: " . json_encode($details));
            }
        }
    }
    
    public static function logSecurityEvent($event, $details = []) {
        if (self::getInstance()->isSecurityLoggingEnabled()) {
            try {
                self::getInstance()->getLogger()->logSecurityEvent($event, $details);
            } catch (Exception $e) {
                error_log("LogManager Security: {$event} | Details: " . json_encode($details));
            }
        }
    }
    
    public static function logError($error, $context = []) {
        if (self::getInstance()->isEnabled()) {
            try {
                self::getInstance()->getLogger()->logError($error, $context);
            } catch (Exception $e) {
                error_log("LogManager Error: {$error} | Context: " . json_encode($context));
            }
        }
    }
}
