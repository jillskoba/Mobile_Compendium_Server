<?php

/**
 * This is the delete character page. It takes the character id and deletes the character from the database
 * 
 * ...
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__ . '/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['characterID'])) {
            $characterID = $post['characterID'];
            $db->deleteCharacter($characterID);
        }
     } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}
