-- ======================================================================
-- MAGAIEES ERP - Complete Database Schema
-- Generated: 2026-07-21 17:08:43
-- Database: magaees
-- Engine: MySQL / MariaDB with InnoDB
-- Charset: utf8mb4 / utf8mb4_general_ci
-- Architecture: Multi-tenant SaaS, JSON-in-SQL document store
-- ======================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE IF NOT EXISTS `magaees`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE `magaees`;

-- ----------------------------------------------------------------------
-- Table: `tenants`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE "tenants" (
  "id" int NOT NULL AUTO_INCREMENT,
  "subdomain" varchar(50) NOT NULL,
  "company_name" varchar(100) NOT NULL,
  "owner_name" varchar(100) DEFAULT NULL,
  "owner_phone" varchar(50) DEFAULT NULL,
  "owner_email" varchar(100) DEFAULT NULL,
  "status" varchar(20) DEFAULT 'active',
  "valid_until" datetime DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "phone2" varchar(255) DEFAULT NULL,
  "receipt_path" varchar(255) DEFAULT NULL,
  "max_users" int DEFAULT '5',
  "plan_id" int DEFAULT '1',
  "max_storage" int DEFAULT NULL,
  "last_login_at" datetime DEFAULT NULL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "subdomain" ("subdomain"),
  KEY "idx_subdomain" ("subdomain"),
  KEY "idx_status" ("status")
);

-- ----------------------------------------------------------------------
-- Table: `accounts`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE "accounts" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_accounts_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_accounts_updated_at" ("updated_at"),
  KEY "idx_accounts_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_accounts_is_deleted" ("is_deleted"),
  KEY "idx_accounts_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "accounts_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `alert_logs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `alert_logs`;
CREATE TABLE "alert_logs" (
  "id" int NOT NULL AUTO_INCREMENT,
  "rule_key" varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "severity" varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "message" text COLLATE utf8mb4_unicode_ci NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "is_read" tinyint(1) DEFAULT '0',
  PRIMARY KEY ("id"),
  CONSTRAINT "alert_logs_chk_1" CHECK ((`severity` in (_utf8mb4'YELLOW',_utf8mb4'RED')))
);

-- ----------------------------------------------------------------------
-- Table: `alert_settings`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `alert_settings`;
CREATE TABLE "alert_settings" (
  "rule_key" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  "rule_name" varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  "description" text COLLATE utf8mb4_unicode_ci,
  "is_enabled" tinyint(1) DEFAULT '1',
  "warning_threshold" decimal(15,2) DEFAULT '0.00',
  "critical_threshold" decimal(15,2) DEFAULT '0.00',
  PRIMARY KEY ("rule_key")
);

-- ----------------------------------------------------------------------
-- Table: `audit_logs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE "audit_logs" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int DEFAULT NULL,
  "data" longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) COLLATE utf8mb4_general_ci GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_audit_logs_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_audit_logs_updated_at" ("updated_at"),
  KEY "idx_audit_logs_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_audit_logs_is_deleted" ("is_deleted"),
  KEY "idx_audit_logs_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "audit_logs_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE,
  CONSTRAINT "audit_logs_chk_1" CHECK (json_valid(`data`))
);

-- ----------------------------------------------------------------------
-- Table: `banks`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `banks`;
CREATE TABLE "banks" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_banks_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_banks_updated_at" ("updated_at"),
  KEY "idx_banks_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_banks_is_deleted" ("is_deleted"),
  KEY "idx_banks_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "banks_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `broadcast_messages`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `broadcast_messages`;
CREATE TABLE "broadcast_messages" (
  "id" int NOT NULL AUTO_INCREMENT,
  "title" varchar(255) DEFAULT NULL,
  "message" text,
  "target_tenants" json DEFAULT NULL,
  "show_once" tinyint(1) DEFAULT '1',
  "theme" varchar(50) DEFAULT 'info',
  "is_active" tinyint(1) DEFAULT '1',
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_broadcast_created" ("created_at")
);

-- ----------------------------------------------------------------------
-- Table: `categories`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE "categories" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_categories_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_categories_updated_at" ("updated_at"),
  KEY "idx_categories_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_categories_is_deleted" ("is_deleted"),
  KEY "idx_categories_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "categories_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `checks`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `checks`;
CREATE TABLE "checks" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_checks_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_checks_updated_at" ("updated_at"),
  KEY "idx_checks_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_checks_is_deleted" ("is_deleted"),
  KEY "idx_checks_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "checks_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `checks_velocity`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `checks_velocity`;
CREATE TABLE "checks_velocity" (
  "check_id" int NOT NULL AUTO_INCREMENT,
  "customer_id" int DEFAULT NULL,
  "check_number" varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  "amount" decimal(15,2) NOT NULL,
  "due_date" date NOT NULL,
  "status" varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'PENDING',
  "type" varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("check_id"),
  KEY "customer_id" ("customer_id"),
  CONSTRAINT "checks_velocity_ibfk_1" FOREIGN KEY ("customer_id") REFERENCES "customer_credit_profiles" ("customer_id") ON DELETE SET NULL,
  CONSTRAINT "chk_status" CHECK ((`status` in (_utf8mb4'PENDING',_utf8mb4'CLEARED',_utf8mb4'BOUNCED'))),
  CONSTRAINT "chk_type" CHECK ((`type` in (_utf8mb4'INBOUND',_utf8mb4'OUTBOUND')))
);

-- ----------------------------------------------------------------------
-- Table: `customer_credit_profiles`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `customer_credit_profiles`;
CREATE TABLE "customer_credit_profiles" (
  "customer_id" int NOT NULL AUTO_INCREMENT,
  "customer_name" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "credit_limit" decimal(15,2) DEFAULT '0.00',
  "current_balance" decimal(15,2) DEFAULT '0.00',
  "risk_level" varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'GREEN',
  "last_payment_date" timestamp NULL DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("customer_id"),
  CONSTRAINT "chk_risk_level" CHECK ((`risk_level` in (_utf8mb4'GREEN',_utf8mb4'YELLOW',_utf8mb4'RED')))
);

-- ----------------------------------------------------------------------
-- Table: `customers`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE "customers" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "customer_id" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "search_name" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.name'))) VIRTUAL,
  "search_phone" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.phone'))) VIRTUAL,
  "search_email" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.email'))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.status'))) VIRTUAL,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_customer" ("tenant_id","customer_id"),
  UNIQUE KEY "idx_customers_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_customers_updated_at" ("updated_at"),
  KEY "idx_customers_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_cust_name" ("search_name"),
  KEY "idx_cust_phone" ("search_phone"),
  KEY "idx_cust_status" ("status_val"),
  KEY "idx_customers_created_by" ("created_by_val"),
  KEY "idx_customers_is_deleted" ("is_deleted"),
  KEY "idx_customers_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "customers_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `delivery_notes`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `delivery_notes`;
CREATE TABLE "delivery_notes" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_delivery_notes_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_delivery_notes_updated_at" ("updated_at"),
  KEY "idx_delivery_notes_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_delivery_notes_is_deleted" ("is_deleted"),
  KEY "idx_delivery_notes_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "delivery_notes_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `direct_purchases`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `direct_purchases`;
CREATE TABLE "direct_purchases" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int DEFAULT NULL,
  "data" longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "invoice_no" varchar(255) COLLATE utf8mb4_general_ci GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.invoiceNo'))) VIRTUAL,
  "supplier_id_val" varchar(255) COLLATE utf8mb4_general_ci GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.supplierId'))) VIRTUAL,
  "date_val" date GENERATED ALWAYS AS (str_to_date(left(json_unquote(json_extract(`data`,_utf8mb4'$.date')),10),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  "total_val" decimal(15,2) GENERATED ALWAYS AS (cast(json_unquote(json_extract(`data`,_utf8mb4'$.netTotal')) as decimal(15,2))) VIRTUAL,
  PRIMARY KEY ("id"),
  KEY "tenant_id" ("tenant_id"),
  KEY "idx_dp_invoice_no" ("invoice_no"),
  KEY "idx_dp_date" ("date_val"),
  CONSTRAINT "dp_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE,
  CONSTRAINT "direct_purchases_chk_1" CHECK (json_valid(`data`))
);

-- ----------------------------------------------------------------------
-- Table: `employees`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE "employees" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_employees_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_employees_updated_at" ("updated_at"),
  KEY "idx_employees_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_employees_is_deleted" ("is_deleted"),
  KEY "idx_employees_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "employees_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `financial_periods`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `financial_periods`;
CREATE TABLE "financial_periods" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int DEFAULT NULL,
  "data" longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) COLLATE utf8mb4_general_ci GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "is_closed" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.isClosed')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.isClosed')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "start_date" date GENERATED ALWAYS AS (str_to_date(json_unquote(json_extract(`data`,_utf8mb4'$.startDate')),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  "end_date" date GENERATED ALWAYS AS (str_to_date(json_unquote(json_extract(`data`,_utf8mb4'$.endDate')),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_periods_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_financial_periods_updated_at" ("updated_at"),
  KEY "idx_financial_periods_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_financial_periods_is_deleted" ("is_deleted"),
  KEY "idx_financial_periods_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "periods_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE,
  CONSTRAINT "financial_periods_chk_1" CHECK (json_valid(`data`))
);

-- ----------------------------------------------------------------------
-- Table: `gl_entries`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `gl_entries`;
CREATE TABLE "gl_entries" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "account_id_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.accountId'))) VIRTUAL,
  "date_val" varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.date'))) VIRTUAL,
  "debit_val" decimal(15,2) GENERATED ALWAYS AS (cast(json_unquote(json_extract(`data`,_utf8mb4'$.debit')) as decimal(15,2))) VIRTUAL,
  "credit_val" decimal(15,2) GENERATED ALWAYS AS (cast(json_unquote(json_extract(`data`,_utf8mb4'$.credit')) as decimal(15,2))) VIRTUAL,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "currency_val" varchar(10) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.currency'))) VIRTUAL,
  "exchange_rate_val" decimal(15,4) GENERATED ALWAYS AS (cast(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.rate')),json_unquote(json_extract(`data`,_utf8mb4'$.exchangeRate')),_utf8mb4'1') as decimal(15,4))) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (lower(json_unquote(json_extract(`data`,_utf8mb4'$.status')))) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_gl_entries_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_gl_entries_updated_at" ("updated_at"),
  KEY "idx_gl_entries_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_gl_account" ("account_id_val"),
  KEY "idx_gl_date" ("date_val"),
  KEY "idx_gl_entries_currency" ("currency_val"),
  KEY "idx_gl_entries_created_by" ("created_by_val"),
  KEY "idx_gl_entries_status" ("status_val"),
  KEY "idx_gl_entries_is_deleted" ("is_deleted"),
  KEY "idx_gl_entries_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "gl_entries_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `global_settings`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `global_settings`;
CREATE TABLE "global_settings" (
  "setting_key" varchar(100) NOT NULL,
  "setting_value" json DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("setting_key")
);

-- ----------------------------------------------------------------------
-- Table: `inventory`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `inventory`;
CREATE TABLE "inventory" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "item_id" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_inventory_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_inventory_updated_at" ("updated_at"),
  KEY "idx_inventory_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_inventory_is_deleted" ("is_deleted"),
  KEY "idx_inventory_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "inventory_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `inventory_items`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `inventory_items`;
CREATE TABLE "inventory_items" (
  "id" int NOT NULL AUTO_INCREMENT,
  "name" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "category" varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY ("id")
);

-- ----------------------------------------------------------------------
-- Table: `items`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `items`;
CREATE TABLE "items" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "item_id" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "search_name" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.name'))) VIRTUAL,
  "search_barcode" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.barcode'))) VIRTUAL,
  "category_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.category'))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.status'))) VIRTUAL,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_item" ("tenant_id","item_id"),
  UNIQUE KEY "idx_items_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_items_updated_at" ("updated_at"),
  KEY "idx_items_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_item_name" ("search_name"),
  KEY "idx_item_barcode" ("search_barcode"),
  KEY "idx_item_category" ("category_val"),
  KEY "idx_items_created_by" ("created_by_val"),
  KEY "idx_items_is_deleted" ("is_deleted"),
  KEY "idx_items_tenant_deleted" ("tenant_id","is_deleted"),
  KEY "idx_items_status_val" ("status_val"),
  CONSTRAINT "items_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `journal_vouchers`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `journal_vouchers`;
CREATE TABLE "journal_vouchers" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "jv_no" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (lower(json_unquote(json_extract(`data`,_utf8mb4'$.status')))) VIRTUAL,
  "date_val" date GENERATED ALWAYS AS (str_to_date(left(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.date')),json_unquote(json_extract(`data`,_utf8mb4'$.invoiceDate')),json_unquote(json_extract(`data`,_utf8mb4'$.receiptDate')),json_unquote(json_extract(`data`,_utf8mb4'$.paymentDate'))),10),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_jv" ("tenant_id","jv_no"),
  UNIQUE KEY "idx_journal_vouchers_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_journal_vouchers_updated_at" ("updated_at"),
  KEY "idx_journal_vouchers_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_journal_vouchers_created_by" ("created_by_val"),
  KEY "idx_journal_vouchers_status" ("status_val"),
  KEY "idx_journal_vouchers_date" ("date_val"),
  KEY "idx_journal_vouchers_is_deleted" ("is_deleted"),
  KEY "idx_journal_vouchers_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "journal_vouchers_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `otp_verifications`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `otp_verifications`;
CREATE TABLE "otp_verifications" (
  "id" int NOT NULL AUTO_INCREMENT,
  "email" varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  "otp" varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  "created_at" datetime DEFAULT CURRENT_TIMESTAMP,
  "expires_at" datetime NOT NULL,
  "is_used" tinyint(1) DEFAULT '0',
  PRIMARY KEY ("id"),
  KEY "email" ("email"),
  KEY "otp" ("otp")
);

-- ----------------------------------------------------------------------
-- Table: `partners`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `partners`;
CREATE TABLE "partners" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_partners_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_partners_updated_at" ("updated_at"),
  KEY "idx_partners_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_partners_is_deleted" ("is_deleted"),
  KEY "idx_partners_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "partners_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `payments`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE "payments" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "payment_no" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "currency_val" varchar(10) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.currency'))) VIRTUAL,
  "exchange_rate_val" decimal(15,4) GENERATED ALWAYS AS (cast(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.rate')),json_unquote(json_extract(`data`,_utf8mb4'$.exchangeRate')),_utf8mb4'1') as decimal(15,4))) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (lower(json_unquote(json_extract(`data`,_utf8mb4'$.status')))) VIRTUAL,
  "date_val" date GENERATED ALWAYS AS (str_to_date(left(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.date')),json_unquote(json_extract(`data`,_utf8mb4'$.invoiceDate')),json_unquote(json_extract(`data`,_utf8mb4'$.receiptDate')),json_unquote(json_extract(`data`,_utf8mb4'$.paymentDate'))),10),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_payment" ("tenant_id","payment_no"),
  UNIQUE KEY "idx_payments_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_payments_updated_at" ("updated_at"),
  KEY "idx_payments_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_payments_currency" ("currency_val"),
  KEY "idx_payments_created_by" ("created_by_val"),
  KEY "idx_payments_status" ("status_val"),
  KEY "idx_payments_date" ("date_val"),
  KEY "idx_payments_is_deleted" ("is_deleted"),
  KEY "idx_payments_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "payments_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `payroll`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `payroll`;
CREATE TABLE "payroll" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_payroll_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_payroll_updated_at" ("updated_at"),
  KEY "idx_payroll_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_payroll_is_deleted" ("is_deleted"),
  KEY "idx_payroll_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "payroll_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `purchase_invoices`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `purchase_invoices`;
CREATE TABLE "purchase_invoices" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "invoice_no" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "supplier_id_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.supplierId'))) VIRTUAL,
  "date_val" varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.date'))) VIRTUAL,
  "total_val" decimal(15,2) GENERATED ALWAYS AS (cast(json_unquote(json_extract(`data`,_utf8mb4'$.netTotal')) as decimal(15,2))) VIRTUAL,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "currency_val" varchar(10) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.currency'))) VIRTUAL,
  "exchange_rate_val" decimal(15,4) GENERATED ALWAYS AS (cast(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.rate')),json_unquote(json_extract(`data`,_utf8mb4'$.exchangeRate')),_utf8mb4'1') as decimal(15,4))) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (lower(json_unquote(json_extract(`data`,_utf8mb4'$.status')))) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_purch_inv" ("tenant_id","invoice_no"),
  UNIQUE KEY "idx_purchase_invoices_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_purch_invoice_no" ("invoice_no"),
  KEY "idx_purchase_invoices_updated_at" ("updated_at"),
  KEY "idx_purchase_invoices_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_purch_supplier" ("supplier_id_val"),
  KEY "idx_purchase_invoices_currency" ("currency_val"),
  KEY "idx_purchase_invoices_created_by" ("created_by_val"),
  KEY "idx_purchase_invoices_status" ("status_val"),
  KEY "idx_purchase_invoices_is_deleted" ("is_deleted"),
  KEY "idx_purchase_invoices_tenant_deleted" ("tenant_id","is_deleted"),
  KEY "idx_purchase_invoices_date_val" ("date_val"),
  CONSTRAINT "purchase_invoices_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `receipts`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `receipts`;
CREATE TABLE "receipts" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "receipt_no" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "currency_val" varchar(10) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.currency'))) VIRTUAL,
  "exchange_rate_val" decimal(15,4) GENERATED ALWAYS AS (cast(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.rate')),json_unquote(json_extract(`data`,_utf8mb4'$.exchangeRate')),_utf8mb4'1') as decimal(15,4))) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (lower(json_unquote(json_extract(`data`,_utf8mb4'$.status')))) VIRTUAL,
  "date_val" date GENERATED ALWAYS AS (str_to_date(left(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.date')),json_unquote(json_extract(`data`,_utf8mb4'$.invoiceDate')),json_unquote(json_extract(`data`,_utf8mb4'$.receiptDate')),json_unquote(json_extract(`data`,_utf8mb4'$.paymentDate'))),10),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_receipt" ("tenant_id","receipt_no"),
  UNIQUE KEY "idx_receipts_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_receipts_updated_at" ("updated_at"),
  KEY "idx_receipts_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_receipts_currency" ("currency_val"),
  KEY "idx_receipts_created_by" ("created_by_val"),
  KEY "idx_receipts_status" ("status_val"),
  KEY "idx_receipts_date" ("date_val"),
  KEY "idx_receipts_is_deleted" ("is_deleted"),
  KEY "idx_receipts_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "receipts_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `reserved_sales`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `reserved_sales`;
CREATE TABLE "reserved_sales" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_reserved_sales_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_reserved_sales_updated_at" ("updated_at"),
  KEY "idx_reserved_sales_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_reserved_sales_is_deleted" ("is_deleted"),
  KEY "idx_reserved_sales_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "reserved_sales_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `roles`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE "roles" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_roles_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_roles_updated_at" ("updated_at"),
  KEY "idx_roles_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_roles_is_deleted" ("is_deleted"),
  KEY "idx_roles_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "roles_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `saas_audit_logs`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `saas_audit_logs`;
CREATE TABLE "saas_audit_logs" (
  "id" int NOT NULL AUTO_INCREMENT,
  "action" varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  "details" text COLLATE utf8mb4_general_ci,
  "ip_address" varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_saas_audit_created" ("created_at")
);

-- ----------------------------------------------------------------------
-- Table: `safes`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `safes`;
CREATE TABLE "safes" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int DEFAULT NULL,
  "data" longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) COLLATE utf8mb4_general_ci GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_safes_unique_frontend" ("tenant_id","frontend_id"),
  KEY "tenant_id" ("tenant_id"),
  KEY "idx_safes_updated_at" ("updated_at"),
  KEY "idx_safes_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_safes_is_deleted" ("is_deleted"),
  KEY "idx_safes_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "safes_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE,
  CONSTRAINT "safes_chk_1" CHECK (json_valid(`data`))
);

-- ----------------------------------------------------------------------
-- Table: `sales_invoices`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `sales_invoices`;
CREATE TABLE "sales_invoices" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "invoice_no" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "customer_id_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.customerId'))) VIRTUAL,
  "date_val" varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.date'))) VIRTUAL,
  "total_val" decimal(15,2) GENERATED ALWAYS AS (cast(json_unquote(json_extract(`data`,_utf8mb4'$.netTotal')) as decimal(15,2))) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.status'))) VIRTUAL,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "currency_val" varchar(10) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.currency'))) VIRTUAL,
  "exchange_rate_val" decimal(15,4) GENERATED ALWAYS AS (cast(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.rate')),json_unquote(json_extract(`data`,_utf8mb4'$.exchangeRate')),_utf8mb4'1') as decimal(15,4))) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_sales_inv" ("tenant_id","invoice_no"),
  UNIQUE KEY "idx_sales_invoices_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_sales_invoice_no" ("invoice_no"),
  KEY "idx_sales_invoices_updated_at" ("updated_at"),
  KEY "idx_sales_invoices_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_sales_customer" ("customer_id_val"),
  KEY "idx_sales_date" ("date_val"),
  KEY "idx_sales_invoices_currency" ("currency_val"),
  KEY "idx_sales_invoices_created_by" ("created_by_val"),
  KEY "idx_sales_invoices_is_deleted" ("is_deleted"),
  KEY "idx_sales_invoices_tenant_deleted" ("tenant_id","is_deleted"),
  KEY "idx_sales_invoices_status_val" ("status_val"),
  CONSTRAINT "sales_invoices_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `settings`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE "settings" (
  "tenant_id" int NOT NULL,
  "setting_key" varchar(100) NOT NULL,
  "setting_value" json NOT NULL,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY ("tenant_id","setting_key"),
  KEY "idx_settings_updated_at" ("updated_at"),
  KEY "idx_settings_tenant_updated" ("tenant_id","updated_at"),
  CONSTRAINT "settings_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `single_payments`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `single_payments`;
CREATE TABLE "single_payments" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (lower(json_unquote(json_extract(`data`,_utf8mb4'$.status')))) VIRTUAL,
  "date_val" date GENERATED ALWAYS AS (str_to_date(left(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.date')),json_unquote(json_extract(`data`,_utf8mb4'$.invoiceDate')),json_unquote(json_extract(`data`,_utf8mb4'$.receiptDate')),json_unquote(json_extract(`data`,_utf8mb4'$.paymentDate'))),10),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_single_payments_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_single_payments_updated_at" ("updated_at"),
  KEY "idx_single_payments_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_single_payments_status" ("status_val"),
  KEY "idx_single_payments_date" ("date_val"),
  KEY "idx_single_payments_is_deleted" ("is_deleted"),
  KEY "idx_single_payments_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "single_payments_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `single_receipts`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `single_receipts`;
CREATE TABLE "single_receipts" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "status_val" varchar(50) GENERATED ALWAYS AS (lower(json_unquote(json_extract(`data`,_utf8mb4'$.status')))) VIRTUAL,
  "date_val" date GENERATED ALWAYS AS (str_to_date(left(coalesce(json_unquote(json_extract(`data`,_utf8mb4'$.date')),json_unquote(json_extract(`data`,_utf8mb4'$.invoiceDate')),json_unquote(json_extract(`data`,_utf8mb4'$.receiptDate')),json_unquote(json_extract(`data`,_utf8mb4'$.paymentDate'))),10),_utf8mb4'%Y-%m-%d')) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_single_receipts_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_single_receipts_updated_at" ("updated_at"),
  KEY "idx_single_receipts_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_single_receipts_status" ("status_val"),
  KEY "idx_single_receipts_date" ("date_val"),
  KEY "idx_single_receipts_is_deleted" ("is_deleted"),
  KEY "idx_single_receipts_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "single_receipts_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `suppliers`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE "suppliers" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "supplier_id" varchar(50) DEFAULT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  "created_by_val" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.createdBy'))) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "unique_supplier" ("tenant_id","supplier_id"),
  UNIQUE KEY "idx_suppliers_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_suppliers_updated_at" ("updated_at"),
  KEY "idx_suppliers_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_suppliers_created_by" ("created_by_val"),
  KEY "idx_suppliers_is_deleted" ("is_deleted"),
  KEY "idx_suppliers_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "suppliers_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `transactions`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE "transactions" (
  "id" int NOT NULL AUTO_INCREMENT,
  "account_id" int DEFAULT NULL,
  "item_id" int DEFAULT NULL,
  "quantity" decimal(15,2) DEFAULT NULL,
  "unit_price" decimal(15,2) DEFAULT NULL,
  "actual_cost" decimal(15,2) DEFAULT NULL,
  "total_amount" decimal(15,2) DEFAULT NULL,
  "transaction_date" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "account_id" ("account_id"),
  KEY "item_id" ("item_id"),
  CONSTRAINT "transactions_ibfk_1" FOREIGN KEY ("account_id") REFERENCES "accounts" ("id") ON DELETE SET NULL,
  CONSTRAINT "transactions_ibfk_2" FOREIGN KEY ("item_id") REFERENCES "inventory_items" ("id") ON DELETE SET NULL
);

-- ----------------------------------------------------------------------
-- Table: `user_message_views`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `user_message_views`;
CREATE TABLE "user_message_views" (
  "user_id" int NOT NULL,
  "message_id" int NOT NULL,
  "viewed_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("user_id","message_id")
);

-- ----------------------------------------------------------------------
-- Table: `users`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE "users" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "username" varchar(50) NOT NULL,
  "password" varchar(255) NOT NULL,
  "role" varchar(20) DEFAULT 'admin',
  "token" varchar(255) DEFAULT NULL,
  "token_expires_at" datetime DEFAULT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "data" json DEFAULT NULL,
  "status" varchar(20) DEFAULT 'active',
  "last_active" datetime DEFAULT NULL,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "username" ("username"),
  UNIQUE KEY "idx_users_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_last_active" ("tenant_id","last_active"),
  KEY "idx_username" ("username"),
  KEY "idx_token" ("token"),
  KEY "idx_users_updated_at" ("updated_at"),
  KEY "idx_users_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_users_is_deleted" ("is_deleted"),
  KEY "idx_users_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "users_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `warehouses`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE "warehouses" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "data" json NOT NULL,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  "updated_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  "frontend_id" varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`data`,_utf8mb4'$.id'))) VIRTUAL,
  "is_deleted" tinyint(1) GENERATED ALWAYS AS (if(((json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'true') or (json_unquote(json_extract(`data`,_utf8mb4'$.deleted')) = _utf8mb4'1')),1,0)) VIRTUAL,
  PRIMARY KEY ("id"),
  UNIQUE KEY "idx_warehouses_unique_frontend" ("tenant_id","frontend_id"),
  KEY "idx_warehouses_updated_at" ("updated_at"),
  KEY "idx_warehouses_tenant_updated" ("tenant_id","updated_at"),
  KEY "idx_warehouses_is_deleted" ("is_deleted"),
  KEY "idx_warehouses_tenant_deleted" ("tenant_id","is_deleted"),
  CONSTRAINT "warehouses_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

-- ----------------------------------------------------------------------
-- Table: `saas_payments`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `saas_payments`;
CREATE TABLE "saas_payments" (
  "id" int NOT NULL AUTO_INCREMENT,
  "tenant_id" int NOT NULL,
  "amount" decimal(10,2) NOT NULL,
  "description" varchar(255) DEFAULT NULL,
  "payment_date" datetime DEFAULT CURRENT_TIMESTAMP,
  "created_at" timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("id"),
  KEY "idx_tenant" ("tenant_id"),
  CONSTRAINT "saas_payments_ibfk_1" FOREIGN KEY ("tenant_id") REFERENCES "tenants" ("id") ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;

-- ======================================================================
-- END OF SCHEMA
-- ======================================================================