<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    'app_env' => env('APP_ENV', 'dev'),
    'report_url' => env('REPORT_URL', ''),
    'report_url2' => env('REPORT_URL2', ''),
    'app_url' => env('APP_URL', ''),
    'dist_base_url' => env('DIST_BASE_URL', ''),
    'db_host' => env('DB_HOST', ''),
    'db_port' => env('DB_PORT', ''),
    'db_database' => env('DB_DATABASE', ''),
    'db_username' => env('DB_USERNAME', ''),
    'db_password' => env('DB_PASSWORD', ''),
    'newdist_host' => env('DB_HOST3', ''),
    'newdist_port' => env('DB_PORT3', ''),
    'newdist_database' => env('DB_DATABASE3', ''),
    'newdist_username' => env('DB_USERNAME3', ''),
    'newdist_password' => env('DB_PASSWORD3', ''),
    'duckdb_path' => env('DUCKDB_PATH', ''),
    'duckdb_file' => env('DUCKDB_FILE', ''),
];
