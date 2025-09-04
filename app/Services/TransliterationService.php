<?php

namespace App\Services;

use Exception;

class TransliterationService
{
    /**
     * Transliterate text from one script to another
     */
    public function transliterate(string $text, string $from, string $to): string
    {
        try {
            // Try using IndicScript package first
            if (class_exists('Sanskritick\Script\IndicScript')) {
                $indicScript = new \Sanskritick\Script\IndicScript();
                return $indicScript->transliterate($text, $from, $to);
            }
            
            // Fallback to Sanscript package
            if (class_exists('Sanskrit\Sanscript')) {
                $sanscript = new \Sanskrit\Sanscript();
                return $sanscript->t($text, $from, $to);
            }
            
            // Manual fallback for basic conversions
            return $this->manualTransliteration($text, $from, $to);
            
        } catch (Exception $e) {
            // Log error and return original text
            \Log::error('Transliteration failed: ' . $e->getMessage());
            return $text;
        }
    }

    /**
     * Convert Devanagari to IAST
     */
    public function devanagariToIAST(string $devanagariText): string
    {
        return $this->transliterate($devanagariText, 'devanagari', 'iast');
    }

    /**
     * Convert IAST to Devanagari
     */
    public function iastToDevanagari(string $iastText): string
    {
        return $this->transliterate($iastText, 'iast', 'devanagari');
    }

    /**
     * Convert Devanagari to Harvard-Kyoto
     */
    public function devanagariToHK(string $devanagariText): string
    {
        return $this->transliterate($devanagariText, 'devanagari', 'hk');
    }

    /**
     * Convert Harvard-Kyoto to Devanagari
     */
    public function hkToDevanagari(string $hkText): string
    {
        return $this->transliterate($hkText, 'hk', 'devanagari');
    }

    /**
     * Auto-detect script and convert to IAST
     */
    public function autoToIAST(string $text): string
    {
        if ($this->isDevanagari($text)) {
            return $this->devanagariToIAST($text);
        } elseif ($this->isHarvardKyoto($text)) {
            return $this->transliterate($text, 'hk', 'iast');
        }
        
        // Assume it's already IAST or similar
        return $text;
    }

    /**
     * Check if text is in Devanagari script
     */
    public function isDevanagari(string $text): bool
    {
        // Check for Devanagari Unicode range (U+0900-U+097F)
        return preg_match('/[\x{0900}-\x{097F}]/u', $text);
;
    }

    /**
     * Check if text is in Harvard-Kyoto format
     */
    public function isHarvardKyoto(string $text): bool
    {
        // Simple heuristic: contains common HK patterns
        return preg_match('/[AEIOURKLGHNGCJYNTDNPBMYRLVSH]/u', $text);
    }

    /**
     * Manual transliteration for basic conversions (fallback)
     */
    private function manualTransliteration(string $text, string $from, string $to): string
    {
        if ($from === 'devanagari' && $to === 'iast') {
            return $this->manualDevanagariToIAST($text);
        }
        
        // Return original text if conversion not supported
        return $text;
    }

    /**
     * Manual Devanagari to IAST conversion (basic mapping)
     */
    private function manualDevanagariToIAST(string $text): string
    {
        $mapping = [
            // Vowels
            'अ' => 'a', 'आ' => 'ā', 'इ' => 'i', 'ई' => 'ī',
            'उ' => 'u', 'ऊ' => 'ū', 'ऋ' => 'ṛ', 'ॠ' => 'ṝ',
            'ऌ' => 'ḷ', 'ॡ' => 'ḹ', 'ए' => 'e', 'ऐ' => 'ai',
            'ओ' => 'o', 'औ' => 'au',
            
            // Consonants
            'क' => 'ka', 'ख' => 'kha', 'ग' => 'ga', 'घ' => 'gha', 'ङ' => 'ṅa',
            'च' => 'ca', 'छ' => 'cha', 'ज' => 'ja', 'झ' => 'jha', 'ञ' => 'ña',
            'ट' => 'ṭa', 'ठ' => 'ṭha', 'ड' => 'ḍa', 'ढ' => 'ḍha', 'ण' => 'ṇa',
            'त' => 'ta', 'थ' => 'tha', 'द' => 'da', 'ध' => 'dha', 'न' => 'na',
            'प' => 'pa', 'फ' => 'pha', 'ब' => 'ba', 'भ' => 'bha', 'म' => 'ma',
            'य' => 'ya', 'र' => 'ra', 'ल' => 'la', 'व' => 'va',
            'श' => 'śa', 'ष' => 'ṣa', 'स' => 'sa', 'ह' => 'ha',
            
            // Diacritics
            'ा' => 'ā', 'ि' => 'i', 'ी' => 'ī', 'ु' => 'u', 'ू' => 'ū',
            'ृ' => 'ṛ', 'ॄ' => 'ṝ', 'ॢ' => 'ḷ', 'ॣ' => 'ḹ',
            'े' => 'e', 'ै' => 'ai', 'ो' => 'o', 'ौ' => 'au',
            
            // Special characters
            'ं' => 'ṃ', 'ः' => 'ḥ', '्' => '', 'ॐ' => 'oṃ', '।' => '.',
        ];

        $result = $text;
        foreach ($mapping as $devanagari => $iast) {
            $result = str_replace($devanagari, $iast, $result);
        }

        return $result;
    }

    /**
     * Get list of supported schemes
     */
    public function getSupportedSchemes(): array
    {
        return [
            'devanagari' => 'Devanagari',
            'iast' => 'IAST',
            'hk' => 'Harvard-Kyoto',
            'itrans' => 'ITRANS',
            'slp1' => 'SLP1',
            'velthuis' => 'Velthuis',
        ];
    }
}