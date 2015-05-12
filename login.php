<?php

/**
 * This is the login page. It takes a given email and password, and checks against the database for valid credentials
 * 
 * ...
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__.'/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['email']) && !empty($post['password'])) {
            $email = $post['email'];
            $password = $post['password'];
                if ($db->passwordMatch($email, $password)) {
                    $db->populateAppStorage($email);
                }
                else {
                    echo "1"; // 1 = invalid credentials
                }
        }
     } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}