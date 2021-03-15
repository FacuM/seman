<?php

define('SERVICE_MIME', 'application/json');

define('IMAGE_UPLOAD_PATH', 'uploads/img');

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