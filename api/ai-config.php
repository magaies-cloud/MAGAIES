<?php
// =============================================================
// MAGAIEES ERP - Centralized AI Engine Configuration & Keys
// إدارة وتهيئة مفاتيح ونماذج الذكاء الاصطناعي المركزية
// =============================================================

if (!function_exists('getGeminiApiKey')) {
    function getGeminiApiKey($clientKey = null) {
        if (!empty($clientKey)) {
            return trim($clientKey);
        }
        
        $envKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
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
