<?php

define('REQUIRED_ENVIRONMENT_VARIABLES', [
	'MYSQL_HOSTNAME',
	'MYSQL_PORT',
	'MYSQL_USERNAME',
	'MYSQL_DATABASE',
	'MYSQL_ENCODING'
]);

define('SERVICE_MIME', 'application/json');

define('IMAGE_UPLOAD_PATH', 'uploads/img');

define('SNMP_OIDS', [
    'ACTIVE_PROCESSES'  => '1.3.6.1.2.1.25.1.6.0',
    'ACTIVE_SESSIONS'   => '1.3.6.1.2.1.25.1.5.0'
]);

define('GAUGE_TYPE', [
    'PROCESSES' => 'PROCESSES',
    'SESSIONS'  => 'SESSIONS'
]);

define('IMAGE_VALID_MIMES', [
    'image/jpg',
    'image/jpeg',
    'image/gif',
    'image/png'
]);

define('HTTP_STATUS', [
    'OK'                => 200,
    'CREATED'           => 201,
    'BAD_REQUEST'       => 400,
    'FORBIDDEN'         => 403,
    'NOT_FOUND'         => 404,
    'DATABASE_ERROR'    => -1000
]);

?>