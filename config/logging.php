<?php
return [
    'enabled' => true,
    'log_file' => '/app/logs/application.log',
    'log_level' => Logger::LEVEL_INFO, // DEBUG, INFO, WARNING, ERROR, CRITICAL
    'max_file_size' => 10485760, // 10MB
    'max_files' => 5,
    'date_format' => 'Y-m-d H:i:s',
    
    // Spezifische Logging-Konfigurationen
    'database' => [
        'enabled' => true,
        'log_queries' => true,
        'log_errors' => true,
        'log_slow_queries' => true,
        'slow_query_threshold' => 1.0 // Sekunden
    ],
    
    'security' => [
        'enabled' => true,
        'log_failed_logins' => true,
        'log_sql_injections' => true,
        'log_xss_attempts' => true,
        'log_file_uploads' => true
    ],
    
    'user_actions' => [
        'enabled' => true,
        'log_crud_operations' => true,
        'log_search_operations' => true,
        'log_export_operations' => true
    ],
    
    'performance' => [
        'enabled' => true,
        'log_memory_usage' => true,
        'log_execution_time' => true,
        'log_database_queries' => true
    ]
];

