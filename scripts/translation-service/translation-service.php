<?php namespace Metayota; include_once('../metayota/library.php');  
$language = isset($GLOBALS['language']) ? $GLOBALS['language'] : NULL;
//if (empty($language)) {
    if (!empty($_GET['language'])) {
        if (preg_match('/^[a-zA-Z]{2,3}(_[a-zA-Z]{2,3})?$/', $_GET['language'])) {
            $language = $_GET['language']; 
            $GLOBALS['language'] = $language;
        } else {
            header('HTTP/1.0 404 Page not found');
            echo "404 / Language not found";
            die();
        }
    } else {
        if (!isset($GLOBALS['language'])) {
            $GLOBALS['language'] = getConfig('default_language','en');
        }
    }

//}

$GLOBALS['translation_cache'] = [];

function translate($key, $parameters=[],$generated=false,$insert=true,$language=NULL) {
    if (empty($key)) {
        return '';
    }
    $originalKey = $key;
    $key = strtolower($key);
    $useDefaultLanguage = false;
    if (empty($language)) {
        $language = !empty($_GET['language']) ? $_GET['language'] : 'de';
        $useDefaultLanguage = true;
    }
    global $db;
    if ($useDefaultLanguage && isset($GLOBALS['translation_cache'][$key])) {
        $translation = $GLOBALS['translation_cache'][$key];
    } else {
        $translation = $db->get('translation', ['translation_key'=>$key,'language'=>$language]);
    }
    
    if (empty($translation) && $insert) {
        $availableVariables = !empty($parameters) && is_array($parameters) ? json_encode(array_keys($parameters)) : NULL;
        $db->query("INSERT IGNORE INTO translation SET translation_key=:key, language=:language, generated_key=:generated, available_variables=:vars", ['key'=>$key,'vars'=>$availableVariables, 'language'=>$language,'generated'=>($generated ? 1 : 0)]);
    }
    if (empty($translation) || empty($translation['translation'])) {
        return $originalKey;
    }
    if (!empty($translation['depends_on'])) {
        $dependsOnKeys = json_decode($translation['depends_on'],true);
        $additionalGeneratedKeys = [];
        foreach($dependsOnKeys as $dependsOn) {
            if (is_array($parameters)) {
                $additionalGeneratedKeys[] = $parameters[$dependsOn];
            } else {
                $additionalGeneratedKeys[] = $parameters;
            }
        }
        $realKey = $key.'_'.join('_',$additionalGeneratedKeys);
        
        $realTranslation = softTranslate($realKey,$parameters,true,true);
        if (!empty($realTranslation) && $realTranslation != $realKey) {
            return $realTranslation;
        }
    }
    $translationText = $translation['translation'];
    if (is_array($parameters)) {
        foreach ($parameters as $key => $value) {
            if (!is_null($value)) {
                $translationText = str_replace("{" . $key . "}", $value, $translationText);
            }
        }
    } else {
        if (!is_null($parameters)) {
            $translationText = preg_replace("/\{(.*?)\}/",$parameters,$translationText);
        }
    }
    $translationText = preg_replace_callback("/\[\[(.*?)\]\]/",
    function ($matches) use($parameters){
        $variable = $matches[1];
        if (is_array($parameters)) {
            return translate($variable,$parameters);
        } else {
            return '[['.$variable.']]';
        }
    }, $translationText );
    $translationText = preg_replace_callback("/\[(.*?)\]/",
    function ($matches) use($parameters){
        $variable = $matches[1];
        if (is_array($parameters)) {
            if (!empty($parameters[$variable])) {
                return translate($parameters[$variable],$parameters);
            } else {
                return '['.$variable.']';
            }
        } else {
            return translate($parameters);
        }
    }, $translationText );
    return $translationText;
}

function translateLanguage($language, $key,$parameters=[]) {
    return translate($key,$parameters,false,false,$language);
}

function softTranslate($key,$parameters=[]) {
    return translate($key,$parameters,false,false);
}

function msg($translation_key,$translation_vars=[],$type=null){
    $translatedMessage = translate($translation_key,$translation_vars,false,false);
    if (!empty($type)) {
        echo json_encode(['message'=>$translatedMessage,'type'=>$type]);
    } else {
        echo json_encode(['message'=>$translatedMessage]);
    }
    die();
}
?>