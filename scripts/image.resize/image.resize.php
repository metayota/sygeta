<?php namespace Metayota; include_once('../metayota/library.php');  

ini_set('display_errors', true);

$h = isset($_GET['h']) ? (int)$_GET['h'] : 3000;
$w = isset($_GET['w']) ? (int)$_GET['w'] : null;
$src = isset($_GET['src']) ? $_GET['src'] : null;
$cropzoom = isset($_GET['crop']) ? true : false;
$mask = isset($_GET['mask']) ? $_GET['mask'] : null;
$format = isset($_GET['format']) ? $_GET['format'] : 'avif'; // Default: AVIF

if (!$src) {
    http_response_code(400);
    die("Fehlender Parameter: src");
}

$srcPath = realpath('../../' . $src);
if (!$srcPath || !file_exists($srcPath)) {
    http_response_code(404);
    die("Datei nicht gefunden.");
}



if (str_ends_with($src, '.svg')) {
    header("Content-Type: image/svg+xml");
    echo file_get_contents($srcPath);
    exit;
}

$filename = '../../cache/' . crc32($src . $w . $h . ($cropzoom ? 'cy' : 'cn') . $mask . $format);

switch ($format) {
    case 'png':
        header("Content-Type: image/png");
        break;
    case 'jpg':
        header("Content-Type: image/jpeg");
        break;
    case 'webp':
        header("Content-Type: image/webp");
        break;
    case 'avif':
    default:
        header("Content-Type: image/avif");
        break;
}

if (!file_exists($filename)) {
    $image = resizeImage($srcPath, $w, $h, \Imagick::FILTER_HANNING, 1, true, $cropzoom, $mask, $format);
    if (!$image) {
        http_response_code(500);
        die("Fehler bei der Bildverarbeitung.");
    } else {
        $img = $image->getImageBlob();

        
        file_put_contents($filename, $img);
    }
} else {
    $img = file_get_contents($filename);
}


header('Cache-Control: public, max-age=86400');
header('Connection: keep-alive');
header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()));
header('Pragma: cache');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 1)) . ' GMT');

echo $img;

function resizeImage($imagePath, $width, $height, $filterType, $blur, $bestFit, $cropZoom, $maskPNG = null, $format = 'avif')
{
    $realPath = realpath($imagePath);
    if (!$realPath) {
        return false;
    }

    $isPdf = str_ends_with(strtolower($imagePath), '.pdf');
    $imagick = new \Imagick();

    if ($isPdf) {
        $imagick->setResolution(300, 300); // High DPI for better quality
        $imagick->readImage($realPath . '[0]'); // First page only
    } else {
        $imagick->readImage($realPath);
    }

    $imagick->setImageInterpolateMethod(\Imagick::INTERPOLATE_SPLINE);
    $imagick->setImageFormat($format);

    $imgWidth = $imagick->getImageWidth();
    $imgHeight = $imagick->getImageHeight();

    if ($width === null) {
        $width = $imgWidth;
    }
    if ($height === null) {
        $height = $imgHeight;
    }

    if ($cropZoom) {
        $wantedWidthHeightFactor = $width / $height;
        $actualWidthHeightFactor = $imgWidth / $imgHeight;
        if ($wantedWidthHeightFactor > $actualWidthHeightFactor) {
            $newHeight = round($imgWidth * $height / $width);
            $imagick->cropimage($imgWidth, $newHeight, 0, round(($imgHeight - $newHeight) / 2));
        } else {
            $newWidth = round($imgHeight * $width / $height);
            $imagick->cropimage($newWidth, $imgHeight, round(($imgWidth - $newWidth) / 2), 0);
        }
    }

    $imagick->resizeImage($width, $height, $filterType, $blur, $bestFit);

    if ($format === 'jpg') {
        $imagick->setImageCompressionQuality(75);
    }

    return $imagick;
}

?>