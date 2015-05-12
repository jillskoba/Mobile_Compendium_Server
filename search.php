<?php

/**
 * This is the search page. It takes an email and queries the database for all characters who match the user tied to the email
 * 
 * ...
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__.'/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['email'])) {
            $email = $post['email'];
            $db->echoUserDataJson($email);
        }
     } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}