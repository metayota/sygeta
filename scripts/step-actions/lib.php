<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
function replacePlaceholders($template, array $replacements) {
    if (empty($template)) {
        return null;
    }
    // Run the replacement process twice
    for ($i = 0; $i < 2; $i++) {
        $template = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($matches) use ($replacements) {
            $key = $matches[1];

            if (!isset($replacements[$key])) {
                return $matches[0];
            }

            if ($replacements[$key] === true) {
                $replacements[$key] = 1;
            }
            if ($replacements[$key] === false) {
                $replacements[$key] = 0;
            }

            return $replacements[$key] ?? $matches[0]; // fallback to original if key not found
        }, $template);
    }

    return $template;
}


function allNamedParametersFilled($sql, $params) {
    // Find all named parameters in the SQL string
    preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*/', $sql, $matches);
    
    if (empty($matches[0])) {
        return true; // No named parameters to fill
    }
    
    // Get unique parameter names (remove the colon)
    $requiredParams = array_unique(array_map(function($param) {
        return ltrim($param, ':');
    }, $matches[0]));
    
    // Check if all required parameters exist in the provided array
    foreach ($requiredParams as $param) {
        if (!array_key_exists($param, $params)) {
            return false;
        }
    }
    
    return true;
}

function extractNamedParameters($sql, array $availableVariables) {
    if (empty($sql)) {
        return [];
    }
    
    $where = [];
    
    // Find all named parameters in the format :parameter_name
    preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $matches);
    
    if (!empty($matches[1])) {
        foreach ($matches[1] as $parameterName) {
            // Check if the parameter exists in available variables
            if (isset($availableVariables[$parameterName])) {
                $value = $availableVariables[$parameterName];
                
                // Handle boolean values like in your replacePlaceholders function
                if ($value === true) {
                    $value = 1;
                }
                if ($value === false) {
                    $value = 0;
                }
                
                $where[$parameterName] = $value;
            }
        }
    }
    
    return $where;
}
?>