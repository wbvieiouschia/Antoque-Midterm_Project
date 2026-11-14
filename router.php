<?php
// Router for PHP's built-in server: hides private files, serves everything else normally.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$blocked = '#(^|/)\.'                          // .env, .git, .gitignore ...
         . '|^/(database|tests|vendor|docs|includes|models)(/|$)'
         . '|^/(make_admin|router)\.php$'
         . '|\.(sql|md|json|lock|xml|db|sh|example)$#i';

if (preg_match($blocked, $path)) {
    http_response_code(404);
    echo 'Not found';
    return true;
}
return false; // let the server serve the file or run the PHP page
