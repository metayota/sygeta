<?php 
    namespace Metayota;
    include_once('../metayota/library.php'); 

    function generateKeyWithDashes() {

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        
        // Generate a string of 32 random characters
        for ($i = 0; $i < 24; $i++) {
            // random_int is used for a cryptographically secure random number
            $randomString .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Split the string into chunks of 4 characters and join them with a dash
        $formattedString = implode('-', str_split($randomString, 4));
        return urlencode($formattedString);
    }
?>