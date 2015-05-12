<?php

/**
 * This is the add character page. It takes an email, character name, and server value and adds a character entry into the database
 * 
 * ...
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__ . '/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['email']) && !empty($post['character']) && !empty($post['server'])) {
			$email = $post['email'];
            $character = $post['character'];
            $server = $post['server'];
			if ($db->characterExists($character, $server)) { // error out that character name already exists
				echo "1";
			} else { // no character of the same name already exists, so continue to creation
				$db->insertCharacter($email, $character, $server);
			}  
        }
     } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}
