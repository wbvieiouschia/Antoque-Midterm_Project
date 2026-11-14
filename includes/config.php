<?php
// includes/config.php — change the system name HERE, once.
if (!defined('APP_NAME')) {
    define('APP_NAME', 'Sprout Scout');
}
if (!defined('APP_TAGLINE')) {
    define('APP_TAGLINE', 'Watch your fields grow');
}

// Planting statuses, in lifecycle order. Used by the dashboard tracker and the planting page.
if (!defined('PLANTING_STATUSES')) {
    define('PLANTING_STATUSES', ['Planned', 'Planted', 'Growing', 'Harvested', 'Failed']);
}