<?php

if (!isset($initializeDatabase) || $initializeDatabase) {
    require_once 'includes/database.php';

    $database = new Database();
}

require_once 'includes/functions.php';
require_once 'includes/constants.php';

require_once 'vendor/autoload.php';

?>