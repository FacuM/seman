<?php

class Database extends PDO {
    public bool $isUsable = true;

    public function __construct() {
        if (!isset($_SERVER['MYSQL_PASSWORD'])) {
            $_SERVER['MYSQL_PASSWORD'] = '';
        }

        try {
            parent::__construct(
                'mysql:' . 
                    'host='     . $_SERVER['MYSQL_HOSTNAME']    . ';' . 
                    'dbname='   . $_SERVER['MYSQL_DATABASE']    . ';' . 
                    'charset='  . $_SERVER['MYSQL_ENCODING'], 
                $_SERVER['MYSQL_USERNAME'], 
                $_SERVER['MYSQL_PASSWORD'],
                [ PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ]
            );
        } catch (PDOException $exception) {
            error_log($exception->getMessage());

            $this->isUsable = false;
        }
    }
}

?>