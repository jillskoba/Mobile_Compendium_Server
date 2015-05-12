<?php

/**
 * This is the friend progress page. It queries tbl_progress2 for all results that match a given character_id value
 * 
 * ...
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__.'/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['characterID'])) {
            $characterID = $post['characterID'];
            $db->echoFriendCharacterProgress($characterID);
        }
     } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}