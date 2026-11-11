<?php namespace Metayota; include_once('../metayota/library.php');  
function formatResourceValues($resourceName, $values, $language, $depth = 3) {
    global $db;
    $resource = $db->getByName('resource', $resourceName);
    $parameters = json_decode($resource['parameters'], true);
    $valuesFormatted = [];

    foreach($parameters as $parameter) {
        $fieldName = $parameter['name'];
        $options = $parameter['options'];
        
        // Skip if this field doesn't exist in values
        if (!isset($values[$fieldName])) {
            continue;
        }
        
        $value = $values[$fieldName];
        
        if ($parameter['type'] == 'db_row') {
            // Get the display value from the referenced table first
            $displayValue = $db->getColumn(
                $options['dbtable'], 
                $options['titlefield'], 
                [$options['idfield'] => $value ]
            );
            
            if ($options['translate_options']) {
                // Translate the retrieved display value
                $valuesFormatted[$fieldName] = translateLanguage($language, $displayValue ?: $value);
            } else {
                $valuesFormatted[$fieldName] = $displayValue ?: $value;
            }
            
            // If depth > 1, recursively format the referenced row
            if ($depth > 1) {
                // Get the full row data from the referenced table
                $referencedRow = $db->get($options['dbtable'], [$options['idfield'] => $value]);
                
                if ($referencedRow) {
                    // Recursively format using the referenced table as resource
                    $nestedFormatted = formatResourceValues(
                        $options['dbtable'], 
                        $referencedRow, 
                        $language, 
                        $depth - 1
                    );
                    
                    // Add nested values with dot notation
                    foreach ($nestedFormatted as $nestedKey => $nestedValue) {
                        $valuesFormatted[$fieldName . '.' . $nestedKey] = $nestedValue;
                    }
                }
            }
        } else if ($parameter['type'] == 'number') {
            // Get number formatting from translations
            $decimalPlaces = $options['decimal_places'] ?? 2;
            $groupSep = translateLanguage($language, 'number_format_group_separator');
            $decimalSep = translateLanguage($language, 'number_format_decimal_separator');
            $suffix = $options['suffix'] ?? '';
            
            $formattedNumber = number_format($value, $decimalPlaces, $decimalSep, $groupSep);
            $valuesFormatted[$fieldName] = $formattedNumber . $suffix;
        } else if ($parameter['type'] == 'date') {
            // Get date format from translations
            $phpDateFormat = translateLanguage($language, 'php_date_format');
            $valuesFormatted[$fieldName] = date($phpDateFormat, strtotime($value));
        } else {
            // For other types, keep original value
            $valuesFormatted[$fieldName] = $value;
        }
    }
    
    return $valuesFormatted;
}
?>