<?php namespace Metayota; include_once('../metayota/library.php');  
function slugify($title) {
    // Replace any non-word characters with a hyphen
    $slug = preg_replace('/[^\w]+/', '-', $title);

    // Remove any leading or trailing hyphens
    $slug = trim($slug, '-');

    // Replace multiple consecutive hyphens with a single hyphen
    $slug = preg_replace('/-{2,}/', '-', $slug);

    // Convert to lowercase
    $slug = strtolower($slug);

    return $slug;
}

function slugifyAlphaNumeric($title) {
    // Replace any characters that are NOT a-z, A-Z, or 0-9 with a hyphen
    $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', $title);

    // Remove any leading or trailing hyphens
    $slug = trim($slug, '-');

    // Replace multiple consecutive hyphens with a single hyphen
    $slug = preg_replace('/-{2,}/', '-', $slug);

    // Convert to lowercase
    $slug = strtolower($slug);

    return $slug;
}

function slugifyWithUnderscore($title) {
    // Replace any non-word characters with an underscore
    $slug = preg_replace('/[^\w]+/', '_', $title);

    // Remove any leading or trailing underscores
    $slug = trim($slug, '_');

    // Replace multiple consecutive underscores with a single underscore
    $slug = preg_replace('/_{2,}/', '_', $slug);

    // Convert to lowercase
    $slug = strtolower($slug);

    return $slug;
}


function slugifyUnique($title, $table, $column, $db) {
    // Initial slugify
    $slug = slugify($title);
    $originalSlug = $slug;
    $counter = 1;

    // Check if the slug exists in the database
    while ($db->get($table, array($column => $slug))) {
        // If it exists, append a number and increment
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}
?>