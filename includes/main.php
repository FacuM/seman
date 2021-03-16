<?php

require_once 'includes/constants.php';

$missingVariables = ''; $requiredCount = count(REQUIRED_ENVIRONMENT_VARIABLES);
foreach (REQUIRED_ENVIRONMENT_VARIABLES as $index => $requiredKey) {
    if (!isset($_SERVER[$requiredKey])) {
        $missingVariables .= $requiredKey . (
            $index < $requiredCount - 1
                ? (
                    $index == $requiredCount - 2
                        ? ' and '   // if it's just one step before the end
                        : ', '      // if it's not the last key, but not near the end
                )
                : '.' // if it's the last key
        );
    }
}

if (!empty($missingVariables)) {
    print
        'Couldn\'t complete startup: <br>
         <br>
         The following required environment variables are missing: ' . $missingVariables . ' <br>
         <br>
         Please supply them, restart the server, and try again.';

    exit();
}

if (!isset($initializeDatabase) || $initializeDatabase) {
    require_once 'includes/database.php';

    $database = new Database();
}

require_once 'includes/functions.php';

require_once 'vendor/autoload.php';

?>