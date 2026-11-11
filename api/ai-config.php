<?php
// =============================================================
// MAGAIEES ERP - Centralized AI Engine Configuration & Keys
// إدارة وتهيئة مفاتيح ونماذج الذكاء الاصطناعي المركزية
// =============================================================

if (!function_exists('isValidGeminiApiKeyFormat')) {
    function isValidGeminiApiKeyFormat($key) {
        $k = trim((string)$key);
        return ((strpos($k, 'AIza') === 0 || strpos($k, 'AQ.') === 0) && strlen($k) >= 30);
    }
}

if (!function_exists('getGeminiApiKey')) {
    function getGeminiApiKey($clientKey = null) {
        if (!empty($clientKey) && isValidGeminiApiKeyFormat($clientKey)) {
            return trim($clientKey);
        }

        $envKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        if (empty($envKey)) {
            $envFile = __DIR__ . '/../.env';
            if (file_exists($envFile)) {
                $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, 'GEMINI_API_KEY=') === 0) {
                        $envKey = trim(substr($line, strlen('GEMINI_API_KEY=')));
                        $_ENV['GEMINI_API_KEY'] = $envKey;
                        @putenv('GEMINI_API_KEY=' . $envKey);
                        break;
                    }
                }
            }
        }
        if (!empty($envKey) && isValidGeminiApiKeyFormat($envKey)) {
            return trim($envKey);
        }

        if (!empty($clientKey)) return trim($clientKey);
        if (!empty($envKey)) return trim($envKey);
        return '';
    }
}

if (!function_exists('getGeminiModel')) {
    function getGeminiModel() {
        return 'gemini-3.5-flash';
    }
}

if (!function_exists('getGeminiFallbackModels')) {
    function getGeminiFallbackModels() {
        return ['gemini-3.5-flash', 'gemini-3.8-flash', 'gemini-3.7-flash', 'gemini-flash-latest'];
    }
}
