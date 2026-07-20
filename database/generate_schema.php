<?php
/**
 * MAGAIEES ERP - Database Schema Dump Generator
 * Generates a complete, reproducible schema.sql from the live database.
 * Run: php database/generate_schema.php > database/schema.sql
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=magaees', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$output = [];
$output[] = "-- ======================================================================";
$output[] = "-- MAGAIEES ERP - Complete Database Schema";
$output[] = "-- Generated: " . date('Y-m-d H:i:s');
$output[] = "-- Database: magaees";
$output[] = "-- Engine: MySQL / MariaDB with InnoDB";
$output[] = "-- Charset: utf8mb4 / utf8mb4_general_ci";
$output[] = "-- Architecture: Multi-tenant SaaS, JSON-in-SQL document store";
$output[] = "-- ======================================================================";
$output[] = "";
$output[] = "SET NAMES utf8mb4;";
$output[] = "SET FOREIGN_KEY_CHECKS = 0;";
$output[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
$output[] = "";
$output[] = "CREATE DATABASE IF NOT EXISTS `magaees`";
$output[] = "  DEFAULT CHARACTER SET utf8mb4";
$output[] = "  DEFAULT COLLATE utf8mb4_general_ci;";
$output[] = "";
$output[] = "USE `magaees`;";
$output[] = "";

// Get tables in dependency order (tenants first)
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// Sort: tenants first, then everything else alphabetically
usort($tables, function($a, $b) {
    if ($a === 'tenants') return -1;
    if ($b === 'tenants') return 1;
    return strcmp($a, $b);
});

foreach ($tables as $table) {
    $output[] = "-- ----------------------------------------------------------------------";
    $output[] = "-- Table: `$table`";
    $output[] = "-- ----------------------------------------------------------------------";
    
    $row = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    $ddl = $row['Create Table'];
    
    // Add DROP IF EXISTS
    $output[] = "DROP TABLE IF EXISTS `$table`;";
    $output[] = $ddl . ";";
    $output[] = "";
}

$output[] = "SET FOREIGN_KEY_CHECKS = 1;";
$output[] = "";
$output[] = "-- ======================================================================";
$output[] = "-- END OF SCHEMA";
$output[] = "-- ======================================================================";

echo implode("\n", $output);
