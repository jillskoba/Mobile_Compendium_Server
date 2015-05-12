<?php

/**
 * This is the populate page. It takes progress on the application on start, and sends it to the server
 * 
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__.'/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['progress'])) {
            $db->updateAllProgress($post['progress']);
        }
     } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}