<?php
namespace Metayota;
include_once('../metayota/library.php');


/**
* Short URL / Tracking ID Library
*
* Provides encoding and decoding of tracking links with contact IDs
* using a reversible, distributed, URL-safe base62 encoding.
*/
class TrackingLink
{
    // Maximum bits for IDs
    const TRACKING_BITS = 20; // for tracking_id (max 1_000_000)
    const CONTACT_BITS  = 26; // for contact_id (max 50_000_000)
    
    // Secret key for obfuscation (46-bit number)
    private static int $key = 0xA5B3C7D9E1;
    
    /**
    * Encode tracking_id and contact_id into a short base62 string
    */
    public static function encode(int $trackingId, int $contactId): string
    {
        // Pack IDs
        $packed = ($trackingId << self::CONTACT_BITS) | $contactId;
        
        // Obfuscate with XOR key
        $obfuscated = $packed ^ self::$key;
        
        // Convert to base62
        return self::base62encode($obfuscated);
    }
    
    /**
    * Decode base62 string into tracking_id and contact_id
    */
    public static function decode(string $code): array
    {
        $obfuscated = self::base62decode($code);
        $packed = $obfuscated ^ self::$key;
        
        $trackingId = $packed >> self::CONTACT_BITS;
        $contactId  = $packed & ((1 << self::CONTACT_BITS) - 1);
        
        return [$trackingId, $contactId];
    }
    
    /**
    * Encode integer to base62 string
    */
    private static function base62encode(int $num): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        do {
            $str = $chars[$num % 62] . $str;
            $num = intdiv($num, 62);
        } while ($num > 0);
        return $str;
    }
    
    /**
    * Decode base62 string to integer
    */
    private static function base62decode(string $str): int
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 62 + strpos($chars, $str[$i]);
        }
        return $num;
    }
}

?>