<?php namespace Metayota; include_once('../metayota/library.php');   
    if (!empty($parameter->options?->decimal_places)) {
        $decimalPlaces = $parameter->options?->decimal_places;
        if (!empty($parameter->options?->max_decimal_places)) {
            $maxDecimalPlaces = $parameter->options?->max_decimal_places;
            if ($maxDecimalPlaces > $decimalPlaces) {
                $decimalPlaces = $maxDecimalPlaces;
            }
        }
        if ($decimalPlaces > 0) {
            $intPlaces = 10;
            if (!empty($parameter->options?->max)) {
                $max = $parameter->options?->max;
                $intPlaces = strlen(ltrim((string) abs((int) $max), '0'));
            }
            $totalPlaces = $decimalPlaces + $intPlaces;
            return "DECIMAL($totalPlaces, $decimalPlaces)";
        }
    } 

    // No decimals — determine appropriate integer type
    if (!empty($parameter->options?->max)) {
        $max = (int) $parameter->options->max;

        // Use value ranges to determine SQL type
        if ($max <= 2147483647) {
            return "INT";
        } elseif ($max <= 9223372036854775807) {
            return "BIGINT";
        } else {
            // Too big even for BIGINT, fallback to DECIMAL without decimal places
            $intPlaces = strlen((string) abs($max));
            return "DECIMAL($intPlaces, 0)";
        }
    }

    // Default
    return "INT";
?>