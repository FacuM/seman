<?php

function reply($data) {
    print json_encode($data, JSON_NUMERIC_CHECK);

    exit();
}

function getServerStatus($serverId) {
    global $database;

    $result = [];

    foreach ([ 'PROCESSES', 'SESSIONS'] as $statusType) {
        $statement = $database->prepare(
            'SELECT
                `created`   AS `label`,
                `value`     AS `data`
             FROM   `sm_status_history`
             WHERE  `serverId`  = :serverId
             AND    `type`      = :type'
        );

        $statement->execute([
            'serverId'  => $serverId,
            'type'      => $statusType
        ]);

        $result[strtolower($statusType)] = $statement->fetchAll();
    }

    return $result;
}

function saveServerStatus($serverId, $type, $value) {
    global $database;

    $statement = $database->prepare(
        'INSERT INTO `sm_status_history` (
            `serverId`,
            `type`,
            `value`
         ) VALUES (
            :serverId,
            :type,
            :value
         )'
    );

    $statement->execute([
        'serverId'  => $serverId,
        'type'      => $type,
        'value'     => $value
    ]);

    return $statement->rowCount() > -1;
}

function getSnmpGaugeValue($gaugeData) {
    return explode(' ', $gaugeData)[1];
}

?>