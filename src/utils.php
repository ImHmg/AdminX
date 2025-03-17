<?php 

    function isDatabaseDetailsAvailable() {
        if(
            isset($_COOKIE['server']) 
            && isset($_COOKIE['port']) 
            && isset($_COOKIE['user']) 
        ) {
            return true;
        }
        return false;
    }

    function isDatabaseDetailsAvailableInPost() {
        if(
            isset($_POST['server']) 
            && isset($_POST['port']) 
            && isset($_POST['user']) 
        ) {
            return true;
        }
        return false;
    }

    function setDatabaseDetails() {
        if(!isDatabaseDetailsAvailableInPost()) {
            return false;
        }
        setcookie('server', $_POST['server']);
        setcookie('port', $_POST['port']);
        setcookie('user', $_POST['user']);
        setcookie('password', isset($_POST['password']) ? $_POST['password'] : "");
        setcookie('schema', isset($_POST['schema']) ? $_POST['schema'] : null);
        return true;
    }

    function clearDatabaseDetails() {
        setcookie('server', null);
        setcookie('port', null);
        setcookie('user', null);
        setcookie('password', null);
        setcookie('schema', null);
    }

    function setSchema() {
        setcookie('schema', isset($_POST['schema']) ? $_POST['schema'] : null);
    }

    function getDatabaseDetails() {
        if(isDatabaseDetailsAvailableInPost()) {
            return [
                'server'   => $_POST['server'] ?? null,
                'port'     => $_POST['port'] ?? null,
                'user'     => $_POST['user'] ?? null,
                'password' => $_POST['password'] ?? "",
                'schema' => $_POST['schema'] ?? null,
            ];
        }
        return [
            'server'   => $_COOKIE['server'] ?? null,
            'port'     => $_COOKIE['port'] ?? null,
            'user'     => $_COOKIE['user'] ?? null,
            'password' => $_COOKIE['password'] ?? "",
            'schema' => $_COOKIE['schema'] ?? null,
        ];
    }
    
?>