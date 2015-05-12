<?php

/**
 * This is the update page. It changes the status of the 'bool' column based on a given character_id, and dragon_id
 * 
 * ...
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__.'/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['characterID']) && !empty($post['dragonID']) && !empty($post['bool'])) {
            $characterID = $post['characterID'];
            $dragonID = $post['dragonID'];
            $bool = $post['bool'];
            $db->updateProgressFromChar($characterID, $dragonID, $bool);
        }
     } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}