<?php

/**
 * This is the registration page. It takes an email and password from the user and registers them
 * 
 * 
 * 
 */
require_once(__DIR__ . '/classes/database.class.php');
require_once(__DIR__.'/crossdomain_support.php');
$post = json_decode(file_get_contents('php://input'), true);
if ($post) {
    try {
        $db = new database();
        if (!empty($post['email']) && !empty($post['password'])) {
            // we have email and password 
            // do we have a server/character?
            if (!empty($post['character']) && !empty($post['server'])) {
              // both are filled in - check if entered character already exists
			  if ($db->characterExists($post['character'], $post['server'])) { // character name already exists... produce error
				echo "1";
			  } else {
				$db->registerUser($post['email'], $post['password'], $post['character'], $post['server']);
                $db->populateAppStorage($post['email']); 
			  }
            } 
            elseif (empty($post['character']) && empty($post['server'])) {
                // both are empty - check email exists
                if ($db->emailExists($post['email'])) {
                    echo "0"; // 0 = email exists
                }
                else {
                    echo "1"; // 1 = email doesn't exist
                }
            }
            else {
                // one of them (character or server) was empty
                throw new Exception("Either character or server was missing in POST: ".print_r($post, true));
            }
        }
        else {
            // one of them (email or password) was empty
            throw new Exception("Either email or password was missing in POST: ".print_r($post, true));
        }
    } catch (Exception $ex) {
         var_dump($ex);
        echo "2"; // 2 = unknown error
    }
}
