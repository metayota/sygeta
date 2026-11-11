<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
// sendCacheHeaders();

function getLanguageAndRedirect() {
    $browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $defaultLanguage = 'en';
    
    $currentUrl = $_SERVER['REQUEST_URI'];
    
    // Prüfen, ob die URL bereits mit einem Sprachcode beginnt
    if (preg_match('#^/[a-z]{2}/#', $currentUrl)) {
        return; // Keine Weiterleitung notwendig
    }

    global $db;
    $languages = $db->getAll('language');
    
    // Suche nach passender Sprache
    $selectedLanguage = $defaultLanguage;
    foreach ($browserLanguages as $browserLang) {
        $browserLang = substr($browserLang, 0, 2); // Nur die ersten zwei Zeichen
        foreach ($languages as $lang) {
            if ($lang['language_key'] === $browserLang) {
                $selectedLanguage = $browserLang;
                break 2; // Beende beide Schleifen
            }
        }
    }
    
    // Weiterleitung zur neuen URL mit Sprachcode (301 permanent redirect)
    $newUrl = '/' . $selectedLanguage . $currentUrl;
    header('Location: ' . $newUrl, true, 301);
    exit();
}


$GLOBALS['language'] = !empty($_GET['language']) ? $_GET['language'] : 'de';
if (strlen($GLOBALS['language']) > 2) {
    die('unsecure request');
}
getLanguageAndRedirect();

$allowedPaths = ['view-contract', 'register', 'forgot-password', 'partner-registration', 'terms-and-conditions','reset-password','event-invitation','self-disclosure','book-meeting', 'invitation', 'property-detail', 'mail-settings', 'confirm-event'];
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$pathSegments = explode('/', $currentPath);

// Check if path starts with /crm/ (after language code)
$isCrmPath = isset($pathSegments[1]) && $pathSegments[1] === 'crm';

if (empty($_SESSION['loggedInUser']) && !$isCrmPath) {
    if (count($pathSegments) === 1 && strlen($pathSegments[0]) === 2) {
        include('../login-screen/html.php');
        exit();
    } else {
        if (!in_array($pathSegments[1], $allowedPaths)) {
            header('Location: /' . $GLOBALS['language'] . '/');
            exit();
        }
    }
}

$appSettings = $db->get('app_settings');

?><!doctype html>
<html lang="en">
<head>
<title><?= translate($appSettings['app_name']) ?></title>
<base href="/">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="index, follow" />



<!-- Favicons -->
<!--link rel="shortcut icon" href="/favicon.ico"-->
<link rel="icon" type="image/png" sizes="32x32" href="/call/image.resize?w=180&h=180&format=png&src=/uploads/favicons/<?= $appSettings['icon'] ?>">
<link rel="icon" type="image/png" sizes="16x16" href="/call/image.resize?w=180&h=180&format=png&src=/uploads/favicons/<?= $appSettings['icon'] ?>">

<!-- Apple Touch Icon -->
<link rel="apple-touch-icon" sizes="180x180" href="/call/image.resize?w=180&h=180&format=png&src=/uploads/favicons/<?= $appSettings['icon'] ?>">

<!-- Android/Manifest -->
<!--link rel="manifest" href="/site.webmanifest"-->

<!-- Safari Mask Icon -->
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">

<!-- Theme Color -->
<meta name="theme-color" content="#ffffff">

<!-- Open Graph Meta Tags -->
<meta property="og:title" content="<?= htmlentities(translate($appSettings['app_name'])) ?>">
<meta property="og:description" content="<?= htmlentities(translate($appSettings['meta_description'])) ?>">
<meta property="og:image" content="/image.resize?format=jpg&src=/favicon/<?= $appSettings['share_image'] ?>">
<meta property="og:url" content="<?= $appSettings['app_url'] ?>">
<meta property="og:type" content="website">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlentities(translate($appSettings['app_name'])) ?>">
<meta name="twitter:description" content="<?= htmlentities(translate($appSettings['meta_description'])) ?>">
<meta name="twitter:image" content="/image.resize?format=jpg&w=1200&h=600&crop=true&src=/favicon/<?= $appSettings['share_image'] ?>">

<?php if ($appSettings['robots_allowed']): ?>
    <!-- Allow full indexing and following of links -->
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
<?php else: ?>
    <!-- Only allow indexing, no link following -->
    <meta name="robots" content="index, nofollow">
    <meta name="googlebot" content="index, nofollow">
    <meta name="bingbot" content="index, nofollow">
<?php endif; ?>


<meta name="revisit-after" content="1 week" />
<script type="text/javascript" src="/resource/framework.javascript"></script>
<script type="text/javascript">
Resource.appendToAllRequests({'language':'<?= $GLOBALS['language'] ?>','u':'1'})
Tag.registerAndLoad('app')
</script>

    

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@160..700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/resource/index.css">
<link rel="apple-touch-icon-precomposed" href="/images/icons/apple-touch-icon.png">
<link rel="icon" href="/images/icons/favicon.ico">
    <script src="https://d3js.org/d3.v7.min.js"></script>

</head>
<body class="material">

<script id="main-script" type="text/javascript">
document.body.appendChild(Tag.render('app').node);
document.getElementById('main-script').remove();
</script>



<!--script>
(function() {
    // Remove any existing virtual cursor
    const existingCursor = document.getElementById('virtualCursor');
    if (existingCursor) existingCursor.remove();
    
    const existingStyle = document.getElementById('virtualCursorStyle');
    if (existingStyle) existingStyle.remove();

    // Add CSS styles
    const style = document.createElement('style');
    style.id = 'virtualCursorStyle';
    style.textContent = `
        body {
            cursor: none !important;
        }
        
        * {
            cursor: none !important;
        }

        .virtual-cursor {
            position: fixed;
            width: 36px;
            height: 36px;
            pointer-events: none;
            z-index: 999999;
            opacity: 1;
            background: none;
            border: none;
            filter: drop-shadow(2px 2px 3px rgba(0,0,0,0.3));
        }

        .virtual-cursor svg {
            width: 100%;
            height: 100%;
            background: none;
        }
    `;
    document.head.appendChild(style);

    // Create cursor element with your SVG
    const cursor = document.createElement('div');
    cursor.className = 'virtual-cursor';
    cursor.id = 'virtualCursor';
    cursor.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" height="36" width="36" viewBox="0 0 48 48">
            <path d="M35.7464 41.325L21.3436 26.9221L13.4497 34.9925L6.64697 6.64673L34.9926 13.4498L26.9221 21.3436L41.3249 35.7465L35.7464 41.325Z" fill="#ffffff" stroke="#000000" stroke-width="2"></path>
        </svg>
    `;
    document.body.appendChild(cursor);

    // Simple smooth cursor
    class SmoothCursor {
        constructor() {
            this.cursor = cursor;
            this.mouse = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
            this.cursorPos = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
            this.ease = 0.15; // Smoothing factor
            this.animationId = null;
            
            this.init();
        }

        init() {
            // Event listeners
            this.mouseMoveHandler = (e) => this.updateMouse(e);
            this.mouseEnterHandler = () => this.showCursor();
            this.mouseLeaveHandler = () => this.hideCursor();

            document.addEventListener('mousemove', this.mouseMoveHandler);
            document.addEventListener('mouseenter', this.mouseEnterHandler);
            document.addEventListener('mouseleave', this.mouseLeaveHandler);
            
            // Start animation loop
            this.animate();
        }

        updateMouse(e) {
            this.mouse.x = e.clientX;
            this.mouse.y = e.clientY;
        }

        showCursor() {
            this.cursor.style.opacity = '1';
        }

        hideCursor() {
            this.cursor.style.opacity = '0';
        }

        animate() {
            // Smooth cursor movement with easing
            this.cursorPos.x += (this.mouse.x - this.cursorPos.x) * this.ease;
            this.cursorPos.y += (this.mouse.y - this.cursorPos.y) * this.ease;

            // Update cursor position
            this.cursor.style.left = (this.cursorPos.x - 3) + 'px';
            this.cursor.style.top = (this.cursorPos.y - 3) + 'px';

            this.animationId = requestAnimationFrame(() => this.animate());
        }

        destroy() {
            // Clean up event listeners
            document.removeEventListener('mousemove', this.mouseMoveHandler);
            document.removeEventListener('mouseenter', this.mouseEnterHandler);
            document.removeEventListener('mouseleave', this.mouseLeaveHandler);
            
            // Cancel animation
            if (this.animationId) {
                cancelAnimationFrame(this.animationId);
            }
            
            // Remove elements
            this.cursor.remove();
            document.getElementById('virtualCursorStyle').remove();
            
            // Restore original cursor
            document.body.style.cursor = '';
        }
    }

    // Initialize the cursor
    window.smoothCursor = new SmoothCursor();
    
    console.log('Simple smooth cursor activated! Run "smoothCursor.destroy()" to remove it.');
})();
</script-->


</body>
</html>