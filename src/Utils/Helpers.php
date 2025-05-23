<?php
namespace App\Utils;

class Helpers {
    public static function trim_text($text, $limit = 2) {
        if (!$text) {
            return $text;
        }
        $text_as_array = explode(' ', trim($text));
        if (count($text_as_array) < $limit) {
            return $text;
        }
        $text_array_to_keep = array_slice($text_as_array, 0, $limit);
        return implode(' ', $text_array_to_keep);
    }

    public static function formatIndonesianDate($dateString = 'now') {
        if ($dateString === 'now') {
            $timestamp = time();
        } else {
            $timestamp = strtotime($dateString);
        }
        
        $days = array("Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu");
        $months = array(1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
        
        $dayIndex = (int)date('w', $timestamp);
        $day = isset($days[$dayIndex]) ? $days[$dayIndex] : '';
        
        $dateVal = date('j', $timestamp);
        
        $monthIndex = (int)date('n', $timestamp);
        $month = isset($months[$monthIndex]) ? $months[$monthIndex] : '';
        
        $year = date('Y', $timestamp);
        
        return "{$day}, {$dateVal} {$month} {$year}";
    }
    
    public static function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}