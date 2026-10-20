<?php
// =============================================================
// MAGAIEES ERP - Centralized AI Engine Configuration & Keys
// إدارة وتهيئة مفاتيح ونماذج الذكاء الاصطناعي المركزية
// =============================================================

if (!function_exists('isValidGeminiApiKeyFormat')) {
    function isValidGeminiApiKeyFormat($key) {
        $k = trim((string)$key);
        return (strpos($k, 'AIza') === 0 && strlen($k) >= 30);
    }
}

if (!function_exists('getGeminiApiKey')) {
    function getGeminiApiKey($clientKey = null) {
        if (!empty($clientKey) && isValidGeminiApiKeyFormat($clientKey)) {
            return trim($clientKey);
        }

        $envKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        if (!empty($envKey) && isValidGeminiApiKeyFormat($envKey)) {
            return trim($envKey);
        }

        if (!empty($clientKey)) {
            return trim($clientKey);
        }
        if (!empty($envKey)) {
            return trim($envKey);
        }

        // System active Gemini key
        return 'AQ.Ab8RN6IMdWH31t8-IbFYJZgytQxgFYW1GnTMee5UwEZ2Tlpqzw';
    }
}

if (!function_exists('getGeminiModel')) {
    function getGeminiModel() {
        return 'gemini-2.5-flash';
    }
}
