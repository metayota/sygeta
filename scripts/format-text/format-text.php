<?php namespace Metayota; include_once('../metayota/library.php');  
function formatText($text) {
    if (empty($text)) {
        return '';
    }
    $text = preg_replace('/^\s{0,1}-\s{0,1}(.*)(\n|$)/m', '<li>$1</li>', $text);
    $text = preg_replace('/<\/?ul[^>]*>/', '', $text);
    $text = preg_replace('/((?:<li>.*?<\/li>\s*)+)/s', '<ul>$1</ul>', $text);
    $text = preg_replace('/\n+/', "\n", $text);
    $pattern_title = "/\*\*([^*]+)\*\*/";
    $pattern_title_h2 = "/\*\*\*([^*]+)\*\*\*/";
    $pattern_bold = "/\*([^*]+)\*/";
    $text = preg_replace_callback(
    $pattern_title_h2,
    function ($matches) {
        return '<h2>'.trim($matches[1]).'</h2>';
    },
    $text
    );
    $text = preg_replace($pattern_title_h2, "<h2>$1</h2>\n", $text);
    $text = preg_replace($pattern_title, "<h3>$1</h3>\n", $text);
    $paragraphs = preg_split('/\n/', $text);
    $paragraphs = array_filter($paragraphs, 'trim');
    $text = implode("\n", $paragraphs);
    $text = str_replace("\n\n","",$text);
    $pattern = "/^(?!<.*>)(.*)$/m";
    $replacement = "<p>$1</p>";
    $text = preg_replace($pattern, $replacement, $text);
    $text = preg_replace($pattern_bold, '<b>$1</b>', $text);
    $text = preg_replace_callback(
    '/<ul>.*?<\/ul>/s',
    function ($matches) {
        // Entfernen Sie <p> und </p> innerhalb jedes <li>-Blocks
        return str_replace(['<p>', '</p>'], '', $matches[0]);
    },
    $text
    );
    
    return $text;
}
?>