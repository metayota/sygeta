<?php namespace Metayota; include_once('../metayota/library.php');   namespace Metayota; include_once('../metayota/library.php');   
    if (!empty($parameter->options?->options)) {

        if (is_string($parameter->options?->options)) {
            $options = json_decode($parameter->options?->options);
        } else {
            $options = $parameter->options?->options;
        }
        $values = array_column((array)$options, 'value');
        $enumValues = "'" . implode("', '", $values) . "'";
        return "ENUM($enumValues)";
    }
    return "VARCHAR(255)";
?>