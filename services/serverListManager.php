<?php

chdir('..');

require_once 'includes/main.php';

header('Content-Type: ' . SERVICE_MIME);

$request = json_decode(
    file_get_contents('php://input')
);

if (isset($request->operation)) {
    switch ($request->operation) {
        case 'getServers':
            if ($database->isUsable) {
                $output = [ 'status' => HTTP_STATUS['OK'] ];

                $statement = $database->query(
                    'SELECT     *
                     FROM       `sm_servers`
                     WHERE      `enabled`
                     ORDER BY   `order` ASC'
                );

                $output['result'] = $statement->fetchAll();

                reply($output);
            } else {
                reply([ 'status' => HTTP_STATUS['DATABASE_ERROR'] ]);
            }

            break;
        case 'getServer':
            if ($database->isUsable) {
                if (isset($request->values)) {
                    $values = &$request->values;

                    if (isset($values->id) && is_numeric($values->id)) {
                        reply([
                            'result' => getServer($values->id),
                            'status' => HTTP_STATUS['OK']
                        ]);
                    } else {
                        reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
                    }
                } else {
                    reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
                }
            } else {
                reply([ 'status' => HTTP_STATUS['DATABASE_ERROR'] ]);
            }

            break;
        case 'saveOrder':
            if (
                isset($request->values)
                &&
                isset($request->values->order)
                &&
                is_array($request->values->order)
            ) {
                $updateCount = 0;

                foreach ($request->values->order as $key => $value) {
                    $statement = $database->prepare(
                        'UPDATE `sm_servers`
                         SET    `order` = :order
                         WHERE  `id`    = :id'
                    );

                    $statement->execute([
                        'order' => $key,
                        'id'    => $value
                    ]);
                }

                reply([ 'status' => HTTP_STATUS['OK'] ]);
            } else {
                reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
            }

            break;
        case 'addServer':
            // FALLTHROUGH
        case 'editServer':
            if (isset($request->values)) {
                $values = (array) $request->values; // re-cast to Array

                $result = [
                    'id'    => null,
                    'image' => null
                ];

                // Workaround GUMP validator limitation.
                if (isset($values['hostname'])) {
                    $values['hostnameValidator'] = 'https://' . $values['hostname'];
                }

                $gump = new GUMP();

                $gump->validation_rules([
                    'description'       => 'required|min_len,1|max_len,65535',
                    'hostnameValidator' => 'required|valid_url',
                    'ipAddress'         => 'required|valid_ip'
                ]);

                $gump->set_fields_error_messages([
                    'description'           => [
                        'required'      => 'Please provide a description.',
                        'min_len,1'     => 'The description must have at least one character.',
                        'max_len,65535' => 'The description can\'t be over 65535 characters long.'
                    ],
                    'hostnameValidator'     => [
                        'required'      => 'Please provide a hostname.',
                        'valid_url'     => 'Please provide a valid hostname.'
                    ],
                    'ipAddress'             => [
                        'required'      => 'Please provide an IP address.',
                        'valid_ip'      => 'Please provide a valid IP address (can be either IPv4 or IPv6).'
                    ]
                ]);

                $gump->filter_rules([
                    'description'       => 'trim|sanitize_string',
                    'hostnameValidator' => 'trim|sanitize_string',
                    'ipAddress'         => 'trim|sanitize_string'
                ]);

                $validData  = $gump->run($values);
                $errors     = $gump->get_errors_array();

                $isImageValid = false;

                if (isset($values['image']) && !empty($values['image'])) {
                    $imageBinary = base64_decode(
                        $values['image']
                    );

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);

                    $mime = finfo_buffer($finfo, $imageBinary);

                    $isImageValid = in_array($mime, IMAGE_VALID_MIMES);

                    if ($isImageValid) {
                        $imageResource = imagecreatefromstring($imageBinary);

                        if ($imageResource === false) {
                            $errors['image'] = 'The provided image is either corrupt or unsupported.';
                        } else {
                            // unique ID + _ + . + extension (image/jpg => jpg)
                            $imageFilename = uniqid() . '_' . sha1($imageBinary) . '.' . explode('/', $mime)[1];

                            if (!file_exists(IMAGE_UPLOAD_PATH)) {
                                mkdir(IMAGE_UPLOAD_PATH, 0777, true);
                            }

                            file_put_contents(IMAGE_UPLOAD_PATH . '/' . $imageFilename, $imageBinary);
                        }
                    }
                }

                $changes = -1;

                if ($values['edit'] == null && !$isImageValid) {
                    $errors['image'] = 'The provided image isn\'t valid and you must provide one.';
                } else {
                    if ($values['edit'] == null) {
                        $statement = $database->prepare(
                            'INSERT INTO `sm_servers` (
                                `description`,
                                `hostname`,
                                `ip`,
                                `order`
                             ) VALUES (
                                :description,
                                :hostname,
                                :ip, IFNULL(
                                    (
                                        SELECT `order`
                                        FROM (
                                            SELECT      `order` + 1 AS `order`
                                            FROM        `sm_servers`
                                            ORDER BY    `order` DESC
                                            LIMIT       1
                                        ) AS newOrder
                                    )
                                , 0)
                             )'
                        );
                    } else {
                        $statement = $database->prepare(
                            'UPDATE `sm_servers`
                             SET
                                `description`   = :description,
                                `hostname`      = :hostname,
                                `ip`            = :ip
                             WHERE `id`         = :id'
                        );
                    }

                    $statement->bindValue('description' , $validData['description']);
                    $statement->bindValue('hostname'    , $validData['hostname']);
                    $statement->bindValue('ip'          , $validData['ipAddress']);

                    if ($values['edit'] != null) {
                        $statement->bindValue('id', $values['edit']);
                    }

                    $statement->execute();

                    $changes = $statement->rowCount();
                }

                $result['id']       = (
                    $values['edit'] == null
                        ? $database->lastInsertId()
                        : $values['edit']
                );

                if (isset($imageFilename) && $changes > -1) {
                    $statement = $database->prepare(
                        'UPDATE `sm_servers`
                         SET    `image` = :image
                         WHERE  `id`    = :id'
                    );

                    $statement->execute([
                        'image' => $imageFilename,
                        'id'    => $result['id']
                    ]);
                }

                $finalServer = getServer($result['id']);

                $result['image'] = $finalServer['image'];

                reply([
                    'result'    => $result,
                    'errors'    => $errors,
                    'status'    => (
                        $changes > -1 || !$isImageValid
                            ? (
                                count($errors) > 0
                                    ? HTTP_STATUS['BAD_REQUEST']
                                    : HTTP_STATUS['OK']
                            )
                            : HTTP_STATUS['DATABASE_ERROR']
                    )
                ]);
            } else {
                reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
            }

            break;
        case 'removeServer':
            if (isset($request->values->id) && is_numeric($request->values->id)) {
                $statement = $database->prepare(
                    'UPDATE `sm_servers`
                     SET    `enabled`   = FALSE
                     WHERE  `id`        = :id'
                );

                $statement->execute([ 'id' => $request->values->id ]);

                reply([ 'status' => HTTP_STATUS['OK'] ]);
            } else {
                reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
            }

            break;
        case 'getServerStatus':
            if ($database->isUsable) {
                if (isset($request->values)) {
                    $values = &$request->values;

                    if (isset($values->id) && is_numeric($values->id)) {
                        $output = [ 'status' => HTTP_STATUS['OK'] ];

                        $statement = $database->prepare(
                            'SELECT     *
                             FROM       `sm_servers`
                             WHERE      `id` = :id
                             AND        `enabled`'
                        );

                        $statement->execute([ 'id' => $values->id ]);

                        $server = $statement->fetch();

                        if ($server == null) {
                            $output == null;
                        } else {
                            $output = getServerStatus($values->id);

                            $processes = @snmpwalk($server['ip'], 'public', SNMP_OIDS['ACTIVE_PROCESSES']);

                            // If the first query failed, we might as well just assume that the next one will break too.
                            if ($processes !== false) {
                                $gaugeValue = getSnmpGaugeValue($processes[0]);

                                $output['processes'][] = [
                                    'label' => strftime('%Y-%m-%d %H:%M:%S'),
                                    'data'  => $gaugeValue
                                ];

                                saveServerStatus($values->id, GAUGE_TYPE['PROCESSES'], $gaugeValue);

                                $sessions = @snmpwalk($server['ip'], 'public', SNMP_OIDS['ACTIVE_SESSIONS']);

                                if ($sessions !== false) {
                                    $gaugeValue = getSnmpGaugeValue($sessions[0]);

                                    saveServerStatus($values->id, GAUGE_TYPE['SESSIONS'], $gaugeValue);

                                    $output['sessions'][] = [
                                        'label' => strftime('%Y-%m-%d %H:%M:%S'),
                                        'data'  => $gaugeValue
                                    ];
                                }
                            }

                            $output['hadIssues'] = false;

                            if ($processes === false || $sessions === false) {
                                $output['hadIssues'] = true;
                            }
                        }

                        reply([
                            'result'    => $output,
                            'status'    => HTTP_STATUS['OK']
                        ]);
                    } else {
                        reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
                    }
                } else {
                    reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
                }
            } else {
                reply([ 'status' => HTTP_STATUS['DATABASE_ERROR'] ]);
            }

            break;
        default:
            reply([ 'status' => HTTP_STATUS['BAD_REQUEST'] ]);
    }
} else {
    print json_encode([
        'status' => HTTP_STATUS['BAD_REQUEST']
    ]);
}

?>