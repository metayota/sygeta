<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

$tag = $_GET['tag'];

// Regular expression pattern for characters to keep
// This pattern will match characters NOT in the set [a-zA-Z_.-] and replace them
$pattern = '/[^a-zA-Z_.-]/';

// Replace characters not matching the pattern with an empty string
$filteredTag = preg_replace($pattern, '', $tag);

$filteredParams = json_encode(json_decode($_GET['params']));

if(strstr(strtolower($_SERVER['HTTP_USER_AGENT']), "googlebot") || empty($_SESSION['loggedInUser'])) {

    
    $static = $db->get('static_content',array('path'=>$_SERVER['REQUEST_URI']));
    if (!empty($static)) {
        
        die($static['html']);
    }
}

echo '<!doctype html>'."\n";
echo '<html lang="en">'."\n";
echo '<head>'."\n";
echo '    <base href="/" target="_blank">'."\n";
echo '    <meta charset="utf-8">'."\n";
echo '    <meta http-equiv="X-UA-Compatible" content="IE=edge">'."\n";
echo '    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">'."\n";
echo '    <title>Metayota</title>'."\n";
if (getConfig('wordpress')) {
    echo '    <link rel="stylesheet" type="text/css" href="/wp-content/plugins/metayota/editor_scripts/index/index.css">'."\n";
    echo '    <script type="text/javascript" src="/wp-content/plugins/metayota/scripts/framework/framework.javascript"></script>'."\n";

} else {
    echo '    <link rel="stylesheet" type="text/css" href="/resource/print-index.css">'."\n";
    echo '    <script type="text/javascript" src="/resource/framework.javascript"></script>'."\n";
}

echo '    <script type="text/javascript">'."\n";
if (getConfig('wordpress')) {
    echo "    Resource.wp = true\n";
    echo "    Resource.scriptsPath = '/wp-content/plugins/metayota/scripts/'\n";
}
if (isset($GLOBALS['language'])) {
    echo "        Resource.appendToAllRequests({'language':'".$GLOBALS['language']."'})\n";
} else {
    echo "        Resource.appendToAllRequests({'language':'en'})\n";
}
echo "        Tag.clear('".$filteredTag."')\n";
echo "        Tag.registerAndLoad('".$filteredTag."')\n";
echo "        let event = function(e) { alert('test') };\n";
echo "        let params = {}\n";
if (getConfig('wordpress') && !empty($filteredParams)) {
    echo '        params = '.stripslashes($filteredParams)."\n";
} else if (!empty($filteredParams)) {
    echo '        params = '.$filteredParams."\n";
}

echo '    </script>'."\n";
echo '</head>'."\n";
$layout_name = isset($_GET['layout_name']) && preg_match('/^[a-z_-]+$/', $_GET['layout_name'])
    ? $_GET['layout_name']
    : '';
echo '<body class="'.$layout_name.'">'."\n";
echo '    <script type="text/javascript">'."\n";
echo "         let expr = new Expression('window.parent.postMessage(event,\'https://www.metayota.com\')', { jscontext: true });\n";
echo "         let exprs = {event:expr};\n";
echo "         Resource.loaded('".$filteredTag."').then(result=> {\n";
echo "              let tag = TagModel.renderTag('".$filteredTag."', params, null, { }, exprs)\n";
echo '              document.body.appendChild(tag.node);'."\n";
echo '        });'."\n";
echo '    </script>'."\n";
echo '</body>'."\n";
echo '</html>'."\n";
?>