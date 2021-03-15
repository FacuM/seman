<?php

function reply($data) {
    print json_encode($data, JSON_NUMERIC_CHECK);

    exit();
}

?>