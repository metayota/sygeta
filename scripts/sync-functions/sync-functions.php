<?php namespace Metayota; include_once('../metayota/library.php');  




function addSQLToUpdateScript(string $table, string $sql): void
{
    global $serverDB;

    // Get resource by name
    $resource = $serverDB->getByName('resource', $table);

    if (!$resource) {
        echo 'resource not found';
        return;
    }

    // Decode implementation JSON
    if (!empty($resource['implementation'])) {
        $implementation = json_decode($resource['implementation'], true);
    } else {
        $implementation = [];
    }

    // Existing PHP code as string
    $code = $implementation['install.php'] ?? '';

    // Bump version (e.g. 1.1.2 → 1.1.3)
    if (empty($resource['version'])) {
        $newVersion = '1.0.0';
    } else {
        $versionParts = explode('.', $resource['version']);
        $last = count($versionParts) - 1;
        $versionParts[$last] = (string)((int)$versionParts[$last] + 1);
        $newVersion = implode('.', $versionParts);
    }

    // Safe PHP line
    $escapedSQL = var_export($sql, true);
    $wrappedCode =
        "\n" .
        "if (version_compare(\$data['old_version'], '$newVersion', '<')) {\n" .
        "    \$db->query($escapedSQL);\n" .
        "}\n";

    // Inject before closing tag or at end
    // If code is empty or missing PHP tags, wrap it in php tag
    if (trim($code) === '') {
        $code = "<?php\n\n$wrappedCode\n?>";
    } else if (preg_match('/\?>\s*$/', $code)) {
        // If it ends with ? >, inject before closing tag
        $code = preg_replace('/\?>\s*$/', $wrappedCode . "?>", $code);
    } else {
        // Otherwise, just append
        $code = rtrim($code) . $wrappedCode;
    }




    // Update database
    $implementation['install.php'] = $code;
    $newImpl = [
        'version' => $newVersion,
        'implementation' => json_encode($implementation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    ];
    include_once('../change_history/change_history.php');
    saveResourceDelta($serverDB, $resource['id'], $newImpl);
    $serverDB->update('resource', $newImpl, [
        'id' => $resource['id']
    ]);
}


function saveScriptsForResource($resourceName) {
    global $db;
    if (getConfig('wordpress')) {
        $data = ['resourcename'=>$resourceName];
        include('../metayota/write_script.php');
    } else {
        $server = $db->getById('server',$_SESSION['server']);
        if ($server['software'] == 'wordpress') {
            file_get_contents($server['http_host'].'wp-content/plugins/metayota/scripts/metayota/write_script.php?resourcename='.$resourceName);//?==
        } else {
            file_get_contents($server['http_host'].'call/metayota/write_script?resourcename='.$resourceName);//?==
        }
    }
}


function getResourceDependencies($resourcename, $database) {
    $sql = "SELECT id,dependencies,name,title,hash,version,version_txt FROM resource WHERE name=:name";
    $resource = $database->getByQuery($sql,array('name'=>$resourcename));
    $allDependencies = array();
    if (!empty($resource)) {
        
        if (!empty($resource['dependencies'])) {
            $dependencies = json_decode($resource['dependencies'], true);
            
            foreach($dependencies as $dependency) {
                $resourceDependencies = getResourceDependencies($dependency['name'], $database);
                
                foreach($resourceDependencies as $resourceDependency) {
                    $allDependencies[$resourceDependency['name']] = $resourceDependency;
                }
            }
            unset($resource['dependencies']);
        }
        $allDependencies[$resource['name']] = $resource;
    }
    return array_values($allDependencies);
}


function getLanguagesCommaseparated($db) {
    $languages = $db->getAll('language');
    $result = [];
    foreach($languages as $language) {
        $langKey = $language['language_key'];
        $result[] = $langKey;
    }
    return join(',',$result);
}

function syncTranslations($repositoryURL, $serverDB, $resourcename) {
    $languages = getLanguagesCommaseparated($serverDB);
    $url = "$repositoryURL/call/rc.sync/get_texts?resourcename=".$resourcename.'&languages='.$languages;
    $remoteTextsDict = json_decode(file_get_contents($url),true);
    foreach($remoteTextsDict as $langKey => $remoteTexts) {
        foreach($remoteTexts as $translation_key => $translation_text) {
            $translation = $serverDB->get('translation',['language'=>$langKey,'translation_key'=>$translation_key]);
            if (empty($translation)) {
                $serverDB->insert('translation',['translation'=>$translation_text,'language'=>$langKey,'translation_key'=>$translation_key]);
            } else if (empty($translation['translation'])) {
                $serverDB->update('translation',['translation'=>$translation_text],['language'=>$langKey,'translation_key'=>$translation_key]);
            }
        }
    }
}

function getAllTranslationKeys($db, $resourceName) {
    $allTranslationKeys = [];
    $resource = $db->getByName('resource', $resourceName);

    if (!$resource) {
        return $allTranslationKeys;
    }

    // Add top-level translation keys
    if (!empty($resource['title'])) {
        $allTranslationKeys[] = $resource['title'];
    }
    if (!empty($resource['documentation'])) {
        $allTranslationKeys[] = $resource['documentation'];
    }

    // Process parameters
    if (!empty($resource['parameters'])) {
        $parameters = json_decode($resource['parameters'], true);
        foreach ($parameters as $parameter) {
            // Add parameter-level translations
            if (!empty($parameter['title'])) {
                $allTranslationKeys[] = $parameter['title'];
            }
            if (!empty($parameter['documentation'])) {
                $allTranslationKeys[] = $parameter['documentation'];
            }

            // Process validators
            if (!empty($parameter['validators'])) {
                foreach ($parameter['validators'] as $validator) {
                    if (!empty($validator['options']) && !empty($validator['options']['title'])) {
                        $allTranslationKeys[] = $validator['options']['title'];
                    }
                }
            }

            // Process translate_settings
            $parameterType = $db->getByName('parametertype', $parameter['type']);
            if (!empty($parameterType['translate_settings'])) {
                $translateSettings = json_decode($parameterType['translate_settings'], true);
                foreach ($translateSettings as $translateSetting) {
                    if (!str_contains($translateSetting, ":")) {
                        // Direct key
                        if (isset($parameter[$translateSetting])) {
                            $allTranslationKeys[] = $parameter[$translateSetting];
                        }
                    } else {
                        // Nested key, use recursive function
                        recursive_collect_keys($parameter, explode(':', $translateSetting), $allTranslationKeys);
                    }
                }
            }

            // Process options arrays explicitly
            if (!empty($parameter['options']['options'])) {
                foreach ($parameter['options']['options'] as $option) {
                    if (!empty($option['name'])) {
                        $allTranslationKeys[] = $option['name'];
                    }
                    if (!empty($option['value']) && is_string($option['value'])) {
                        $allTranslationKeys[] = $option['value'];
                    }
                }
            }

            // Include suffix or other translatable values in options
            if (!empty($parameter['options'])) {
                foreach ($parameter['options'] as $key => $value) {
                    if (is_string($value)) {
                        $allTranslationKeys[] = $value;
                    } elseif (is_array($value) || is_object($value)) {
                        recursive_collect_keys($value, [], $allTranslationKeys);
                    }
                }
            }
        }
    }

    // Process implementation for inline translations
    if (!empty($resource['implementation'])) {
        $implementations = json_decode($resource['implementation'], true);
        foreach ($implementations as $implementation) {
            // Recognize msg(), translate() calls
            $pattern1 = '/(msg|translate)\(\s*[\'"]([^\'"]+)[\'"]/';
            preg_match_all($pattern1, $implementation, $matches1);
            foreach ($matches1[2] as $match) {
                $allTranslationKeys[] = $match;
            }

            // Recognize <t>...</t> tags
            $pattern2 = '/<t>([^<]+)<\/t>/';
            preg_match_all($pattern2, $implementation, $matches2);
            foreach ($matches2[1] as $match) {
                $allTranslationKeys[] = $match;
            }
        }
    }

    // Filter unique and valid keys
    $allTranslationKeys = array_filter($allTranslationKeys);
    $allTranslationKeys = array_unique($allTranslationKeys);
    $allTranslationKeys = array_filter($allTranslationKeys, function ($value) {
        return preg_match('/^[a-z0-9_]+$/', $value);
    });

    return array_values($allTranslationKeys);
}


/**
 * Recursively collects translation keys from nested structures based on translate settings.
 */
function recursive_collect_keys(&$array, $keys, &$allTranslationKeys) {
    if (empty($keys)) {
        // If no specific path is provided, traverse the array/object
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_string($value)) {
                    $allTranslationKeys[] = $value;
                } elseif (is_array($value) || is_object($value)) {
                    recursive_collect_keys($value, [], $allTranslationKeys);
                }
            }
        } elseif (is_string($array)) {
            $allTranslationKeys[] = $array;
        }
        return;
    }

    $key = array_shift($keys);

    if (isset($array[$key])) {
        if (empty($keys)) {
            // Collect value if this is the final key
            if (is_array($array[$key])) {
                foreach ($array[$key] as $item) {
                    if (is_string($item)) {
                        $allTranslationKeys[] = $item;
                    } elseif (is_array($item) || is_object($item)) {
                        recursive_collect_keys($item, [], $allTranslationKeys);
                    }
                }
            } elseif (is_string($array[$key])) {
                $allTranslationKeys[] = $array[$key];
            }
        } else {
            // Continue traversing nested structures
            recursive_collect_keys($array[$key], $keys, $allTranslationKeys);
        }
    }
}


?>