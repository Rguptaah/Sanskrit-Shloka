<?php

namespace App\Services;

class UnicodeConversionService
{
    /**
     * Convert text to Unicode escape sequences
     */
    public function toUnicodeEscape(string $text): string
    {
        $result = '';
        $length = mb_strlen($text, 'UTF-8');
        
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $codepoint = $this->getCodepoint($char);
            
            if ($codepoint > 127) {
                $result .= '\\u' . str_pad(dechex($codepoint), 4, '0', STR_PAD_LEFT);
            } else {
                $result .= $char;
            }
        }
        
        return $result;
    }

    /**
     * Convert Unicode escape sequences back to text
     */
    public function fromUnicodeEscape(string $unicodeString): string
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
            return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
        }, $unicodeString);
    }

    /**
     * Convert Devanagari text to Unicode escape format
     */
    public function devanagariToUnicodeEscape(string $devanagariText): string
    {
        return $this->toUnicodeEscape($devanagariText);
    }

    /**
     * Convert Unicode escape format back to Devanagari
     */
    public function unicodeEscapeToDevanagari(string $unicodeEscape): string
    {
        return $this->fromUnicodeEscape($unicodeEscape);
    }

    /**
     * Get the Unicode codepoint of a character
     */
    private function getCodepoint(string $char): int
    {
        $values = array_values(unpack('N*', iconv('UTF-8', 'UCS-4BE', $char)));
        return $values[0] ?? 0;
    }

    /**
     * Check if text contains Unicode escape sequences
     */
    public function hasUnicodeEscapes(string $text): bool
    {
        return preg_match('/\\\\u[0-9a-fA-F]{4}/', $text);
    }

    /**
     * Convert text to HTML entities
     */
    public function toHtmlEntities(string $text): string
    {
        return htmlentities($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Convert HTML entities back to text
     */
    public function fromHtmlEntities(string $htmlEntities): string
    {
        return html_entity_decode($htmlEntities, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Get character information
     */
    public function getCharacterInfo(string $char): array
    {
        $codepoint = $this->getCodepoint($char);
        
        return [
            'character' => $char,
            'codepoint' => $codepoint,
            'hex' => dechex($codepoint),
            'unicode_escape' => '\\u' . str_pad(dechex($codepoint), 4, '0', STR_PAD_LEFT),
            'html_entity' => '&#' . $codepoint . ';',
            'is_devanagari' => $this->isDevanagariChar($codepoint),
        ];
    }

    /**
     * Check if a codepoint is in the Devanagari range
     */
    private function isDevanagariChar(int $codepoint): bool
    {
        return $codepoint >= 0x0900 && $codepoint <= 0x097F;
    }

    /**
     * Clean and normalize Unicode text
     */
    public function normalizeText(string $text): string
    {
        // Normalize Unicode to NFC form
        if (class_exists('Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_C);
        }
        
        // Remove zero-width characters
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        
        // Trim whitespace
        return trim($text);
    }

    /**
     * Get text encoding information
     */
    public function getEncodingInfo(string $text): array
    {
        return [
            'encoding' => mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true),
            'byte_length' => strlen($text),
            'character_length' => mb_strlen($text, 'UTF-8'),
            'is_ascii' => mb_check_encoding($text, 'ASCII'),
            'is_utf8' => mb_check_encoding($text, 'UTF-8'),
        ];
    }
}