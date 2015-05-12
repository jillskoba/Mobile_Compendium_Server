<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'jillvzpf_compend');
define('DB_PASSWORD', 'UraB114m[k;*');
define('DB_DATABASE', 'jillvzpf_compendium');

class database {
    protected $conn = null;
    public function __construct() {
        // Create connection
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        // Check connection
        if (!$this->conn) {
            throw new Exception("Connection failed: " . mysqli_connect_error());
        }
    }
	
	// ----------------------------------------- MISC CHECKS ------------------------------------------------
	
	// check if email exists
    public function emailExists($email) {
        $escapedEmail = $this->conn->real_escape_string($email);
        $result = $this->conn->query("SELECT * FROM tbl_user WHERE user_email = '{$escapedEmail}'");
        if (!$result) {
            throw new Exception("Error checking if email exists: email {$email}, sql error: " . $this->conn->error);
        }
        return $result->num_rows > 0;
    }
	
	// check if character name exists
    public function characterExists($character, $server) {
        $escapedCharacter = $this->conn->real_escape_string($character);
		$escapedServer = $this->conn->real_escape_string($server);
        $result = $this->conn->query("SELECT * FROM tbl_character WHERE character_name = '{$escapedCharacter}' AND server_id = '{$escapedServer}'");
        if (!$result) {
            throw new Exception("Error checking if character exists: email {$character}, sql error: " . $this->conn->error);
        }
        return $result->num_rows > 0;
    }
    
    // check if password matches
    public function passwordMatch($email, $password) {
        $escapedEmail = $this->conn->real_escape_string($email);
        $escapedPassword = $this->conn->real_escape_string($password);
        $result = $this->conn->query("SELECT * FROM tbl_user WHERE user_email = '{$escapedEmail}' AND user_pass = '{$escapedPassword}'");
        if (!$result) {
            throw new Exception("Error checking login credentials: email {$email}, password {$password}, sql error: " . $this->conn->error);
        }
        return $result->num_rows > 0;
    }
	
	
	
	// ----------------------------------------- MISC FETCHES ------------------------------------------------
    
    public function getUserIdFromEmail($email) {
        $escapedEmail = $this->conn->real_escape_string($email);
        
        $result = $this->conn->query("SELECT user_id FROM tbl_user WHERE user_email = '{$escapedEmail}'");
        if (!$result) {
            throw new Exception("Error looking up user email {$email}, sql error: " . $this->conn->error);
        }
        
        $row = $result->fetch_assoc();
        if (!$row) {
            throw new Exception("No user found matching email {$email}");
        }
        return $row['user_id'];
    }
	
	
	
	// ----------------------------------------- FUNCTIONALITY ------------------------------------------------
    
    // add the user's credentials to the DB, and add their first character
    public function registerUser($email, $password, $character, $server) {
        // escape variables to prevent sql injection
        $escapedEmail = $this->conn->real_escape_string($email);
        $escapedPassword = $this->conn->real_escape_string($password);
        $escapedCharacter = $this->conn->real_escape_string($character);
        $escapedServer = $this->conn->real_escape_string($server);

        if (!$this->conn->query("INSERT INTO tbl_user(user_email,user_pass) VALUES ('{$escapedEmail}', '{$escapedPassword}')")) {
            throw new Exception("Error inserting user: email {$email}, password {$password}, sql error: " . $this->conn->error);
        }
        
        $userID = $this->getUserIdFromEmail($email);
        
        if (!$this->conn->query("INSERT INTO tbl_character(user_id,character_name, server_id) VALUES ('{$userID}', '{$escapedCharacter}', '{$escapedServer}')")) {
            throw new Exception("Error inserting character: User ID{$userID}, character name {$character}, character server {$server}, sql error: " . $this->conn->error);
        }
		
		$characterResult = $this->conn->query("SELECT character_id, character_name FROM tbl_character WHERE character_name = '{$escapedCharacter}'");
        if (!$characterResult) {
            throw new Exception("Error checking tbl_character array, sql error: " . $this->conn->error);
        }
        
        while ($row = $characterResult->fetch_assoc()) {
            $characterID = $row["character_id"];
        }
        
        $result = $this->conn->query("SELECT dragon_id FROM tbl_dragon");
        if (!$result) {
            throw new Exception("Error checking tbl_dragon array, sql error: " . $this->conn->error);
        }
        
        while ($row = $result->fetch_assoc()) {
            $dragonID = $row["dragon_id"];
            $progressQuery = $this->conn->query("INSERT INTO tbl_progress2(dragon_id,character_id,bool) VALUES ('{$dragonID}', '{$characterID}', ('0'))");
            if (!$progressQuery) {
                throw new Exception("Error inserting progress: Dragon ID{$dragonID}, Character ID {$characterID}, sql error: " . $this->conn->error);
            }
        }
    }
    
    // generate progress table info for new characters
    public function addProgressFromChar($character) {
        $escapedCharacter = $this->conn->real_escape_string($character);
        
        $characterResult = $this->conn->query("SELECT character_id, character_name FROM tbl_character WHERE character_name = '{$escapedCharacter}'");
        if (!$characterResult) {
            throw new Exception("Error checking tbl_character array, sql error: " . $this->conn->error);
        }
        
        while ($row = $characterResult->fetch_assoc()) {
            $characterID = $row["character_id"];
        }
        
        $result = $this->conn->query("SELECT dragon_id FROM tbl_dragon");
        if (!$result) {
            throw new Exception("Error checking tbl_dragon array, sql error: " . $this->conn->error);
        }
        
        while ($row = $result->fetch_assoc()) {
            $dragonID = $row["dragon_id"];
            $progressQuery = $this->conn->query("INSERT INTO tbl_progress2(dragon_id,character_id,bool) VALUES ('{$dragonID}', '{$characterID}', ('0'))");
            if (!$progressQuery) {
                throw new Exception("Error inserting progress: Dragon ID{$dragonID}, Character ID {$characterID}, sql error: " . $this->conn->error);
            }
        }
    }
    
    // update progress table when checkbox is checked / unchecked
    public function updateProgressFromChar($characterID, $dragonID, $bool) {
        $escapedCharacter = $this->conn->real_escape_string($characterID);
        $escapedDragon = $this->conn->real_escape_string($dragonID);
        $escapedBool = $this->conn->real_escape_string($bool);
        $result = $this->conn->query("SELECT bool FROM tbl_progress2 WHERE character_id = '{$escapedCharacter}' AND dragon_id = '{$escapedDragon}'");
         if (!$result) {
            throw new Exception("Error searching for progress: Character ID {$characterID}, Dragon ID {$dragonID}, sql error: " . $this->conn->error);
        }
        
        while ($row = $result->fetch_assoc()) {
            $progressQuery = $this->conn->query("UPDATE tbl_progress2 SET bool='{$escapedBool}' WHERE dragon_id = '{$escapedDragon}' AND character_id = '{$escapedCharacter}'");
            if (!$progressQuery) {
                throw new Exception("Error inserting progress: Dragon ID{$dragonID}, Character ID {$characterID}, bool {$bool}, sql error: " . $this->conn->error);
            }
        }
    }
    
    
    // update all server-side data on progress2 table when application first starts (to fully sync any offline changes)
    public function updateAllProgress($progress) {
	$json = json_decode($progress, true);
        foreach ($json as $data) {
            $characterID = $data['character_id'];
            $dragonID = $data['dragon_id'];
            $bool = $data['bool'];
            $q = "UPDATE tbl_progress2 SET bool='{$bool}' WHERE dragon_id = '{$dragonID}' AND character_id = '{$characterID}'";
            $progressQuery = $this->conn->query($q);	
            if (!$progressQuery) {
                throw new Exception("Error inserting progress: Dragon ID{$dragonID}, Character ID {$characterID}, bool {$bool}, sql error: " . $this->conn->error);
            }
       }
    }


    //get character data based on user's email
    public function echoUserDataJson($email) {
        $escapedEmail = $this->conn->real_escape_string($email);
        $result = $this->conn->query("SELECT character_id, character_name, server_name FROM tbl_character, tbl_user, tbl_server WHERE tbl_user.user_id = tbl_character.user_id AND tbl_character.server_id = tbl_server.server_id AND tbl_user.user_email = '{$escapedEmail}'");
        if (!$result) {
            throw new Exception("Error checking if email exists: email {$email}, sql error: " . $this->conn->error);
        }
        $userData = [];
        while ($row = $result->fetch_assoc()) {
            $userData[] = $row;
        }
        echo json_encode($userData);
    }
    
    
    //get all rows from tbl_progress2 equal to characterID
    public function echoFriendCharacterProgress($characterID) {
        $escapedCharacterID = $this->conn->real_escape_string($characterID);
        $result = $this->conn->query("SELECT tbl_progress2.character_id, dragon_th, tbl_family.family_id, family_name, colour_name, tbl_progress2.dragon_id, tbl_dragon.dragon_name, bool 
        FROM tbl_progress2, tbl_user, tbl_character, tbl_family, tbl_colour, tbl_dragon 
        WHERE tbl_progress2.character_id = tbl_character.character_id 
        AND tbl_dragon.family_id = tbl_family.family_id 
        AND tbl_character.user_id = tbl_user.user_id 
        AND tbl_dragon.dragon_id = tbl_progress2.dragon_id 
        AND tbl_dragon.colour_id = tbl_colour.colour_id 
        AND bool = '1' 
        AND tbl_progress2.character_id = '{$escapedCharacterID}' GROUP BY tbl_dragon.dragon_id ORDER BY tbl_dragon.dragon_id");
        if (!$result) {
            throw new Exception("Error obtaining dragon list: email {$characterID}, sql error: " . $this->conn->error);
        }
        $progressData = [];
        while ($row = $result->fetch_assoc()) {
            $progressData[] = $row;
        }
        echo json_encode($progressData);
        //echo ($email);
    }
    
    
    // add character to tbl_character using given character name, server name, and user ID obtained from given email
    public function insertCharacter($email, $character, $server) {
        $escapedEmail = $this->conn->real_escape_string($email);
        $escapedCharacter = $this->conn->real_escape_string($character);
        $escapedServer = $this->conn->real_escape_string($server);
        
        $userID = $this->getUserIdFromEmail($escapedEmail);
        
        if (!$this->conn->query("INSERT INTO tbl_character(user_id,character_name, server_id) VALUES ('{$userID}', '{$escapedCharacter}', '{$escapedServer}')")) {
            throw new Exception("Error inserting character: User ID{$userID}, character name {$character}, character server {$server}, sql error: " . $this->conn->error);
        }
        
        $characterResult = $this->conn->query("SELECT character_id, character_name FROM tbl_character WHERE character_name = '{$escapedCharacter}'");
        if (!$characterResult) {
            throw new Exception("Error checking tbl_character array, sql error: " . $this->conn->error);
        }
        
        while ($row = $characterResult->fetch_assoc()) {
            $characterID = $row["character_id"];
        }
        
        $result = $this->conn->query("SELECT dragon_id FROM tbl_dragon");
        if (!$result) {
            throw new Exception("Error checking tbl_dragon array, sql error: " . $this->conn->error);
        }
        
        while ($row = $result->fetch_assoc()) {
            $dragonID = $row["dragon_id"];
            $progressQuery = $this->conn->query("INSERT INTO tbl_progress2(dragon_id,character_id,bool) VALUES ('{$dragonID}', '{$characterID}', ('0'))");
            if (!$progressQuery) {
                throw new Exception("Error inserting progress: Dragon ID{$dragonID}, Character ID {$characterID}, sql error: " . $this->conn->error);
            }
        }
        
        //----------------------GET CHARACTERS----------------------------
        $characterData = $this->conn->query("SELECT character_id, character_name, server_name 
        FROM tbl_user, tbl_character, tbl_server 
        WHERE tbl_server.server_id = tbl_character.server_id 
        AND tbl_user.user_id = tbl_character.user_id 
        AND tbl_character.user_id = '{$userID}'");
        
        $characterJSON = array();
        while ($row = $characterData->fetch_assoc()) {
            $characterJSON[] = array(
		'character_id' => $row['character_id'],
		'character_name' => $row['character_name'],
		'server_name' => $row['server_name']
            );
        }
        
        
        //----------------------GET PROGRESS----------------------------
        $progressData = $this->conn->query("SELECT tbl_progress2.character_id, tbl_family.family_name, colour_name, tbl_progress2.dragon_id, tbl_dragon.dragon_name, bool 
        FROM tbl_progress2, tbl_character, tbl_family, tbl_colour, tbl_dragon 
        WHERE tbl_progress2.character_id = tbl_character.character_id 
        AND tbl_dragon.family_id = tbl_family.family_id 
        AND tbl_character.user_id = '{$userID}'
        AND tbl_dragon.dragon_id = tbl_progress2.dragon_id 
        AND tbl_dragon.colour_id = tbl_colour.colour_id 
        ORDER BY tbl_character.character_id, tbl_dragon.dragon_id");
        
        $progressJSON = array();
        while ($row = $progressData->fetch_assoc()) {
            $progressJSON[] = array(
		'character_id' => $row['character_id'],
		'dragon_id' => $row['dragon_id'],
		'dragon_name' => $row['dragon_name'],
		'colour_name' => $row['colour_name'],
		'family_name' => $row['family_name'],
		'bool' => (bool)$row['bool']
            );
        }
        $returnData = array(
            'characterQuery' => $characterJSON,
            'progressQuery' => $progressJSON
        );
        echo json_encode($returnData);
    }
    
    
    // delete character based on passed character ID
    public function deleteCharacter($characterID) {
        $escapedCharacterID = $this->conn->real_escape_string($characterID);
        
        $userResult = $this->conn->query("SELECT character_id, user_id FROM tbl_character WHERE character_id = '{$escapedCharacterID}'");
        if (!$userResult) {
            throw new Exception("Error checking tbl_character array, sql error: " . $this->conn->error);
        }
        
        while ($row = $userResult->fetch_assoc()) {
            $userID = $row["user_id"];
        }
        
        if (!$this->conn->query("DELETE FROM tbl_character WHERE character_id= '{$escapedCharacterID}'")) {
            throw new Exception("Error deleting character: Character ID{$escapedCharacterID}, sql error: " . $this->conn->error);
        }
        
        if (!$this->conn->query("DELETE FROM tbl_progress2 WHERE character_id= '{$escapedCharacterID}'")) {
            throw new Exception("Error deleting progress: Character ID{$escapedCharacterID}, sql error: " . $this->conn->error);
        }
        
        
        //----------------------GET CHARACTERS----------------------------
        $characterData = $this->conn->query("SELECT character_id, character_name, server_name 
        FROM tbl_user, tbl_character, tbl_server 
        WHERE tbl_server.server_id = tbl_character.server_id 
        AND tbl_user.user_id = tbl_character.user_id 
        AND tbl_character.user_id = '{$userID}'");
        
        $characterJSON = array();
        while ($row = $characterData->fetch_assoc()) {
            $characterJSON[] = array(
		'character_id' => $row['character_id'],
		'character_name' => $row['character_name'],
		'server_name' => $row['server_name']
            );
        }
        
        
        //----------------------GET PROGRESS----------------------------
        $progressData = $this->conn->query("SELECT tbl_progress2.character_id, tbl_family.family_name, colour_name, tbl_progress2.dragon_id, tbl_dragon.dragon_name, bool 
        FROM tbl_progress2, tbl_character, tbl_family, tbl_colour, tbl_dragon 
        WHERE tbl_progress2.character_id = tbl_character.character_id 
        AND tbl_dragon.family_id = tbl_family.family_id 
        AND tbl_character.user_id = '{$userID}'
        AND tbl_dragon.dragon_id = tbl_progress2.dragon_id 
        AND tbl_dragon.colour_id = tbl_colour.colour_id 
        ORDER BY tbl_character.character_id, tbl_dragon.dragon_id");
        
        $progressJSON = array();
        while ($row = $progressData->fetch_assoc()) {
            $progressJSON[] = array(
		'character_id' => $row['character_id'],
		'dragon_id' => $row['dragon_id'],
		'dragon_name' => $row['dragon_name'],
		'colour_name' => $row['colour_name'],
		'family_name' => $row['family_name'],
		'bool' => (bool)$row['bool']
            );
        }
        $returnData = array(
            'characterQuery' => $characterJSON,
            'progressQuery' => $progressJSON
        );
        echo json_encode($returnData);
    }
    
    
    
    public function populateAppStorage($email) {
        
        //----------------------GET USER ID----------------------------
        $escapedEmail = $this->conn->real_escape_string($email);
        $user = $this->conn->query("SELECT user_id FROM tbl_user WHERE user_email = '{$escapedEmail}'");
        
        $userID = [];
        while ($row = $user->fetch_assoc()) {
            $userID = $row['user_id'];
        }
        
        //----------------------GET CHARACTERS----------------------------
        $characterData = $this->conn->query("SELECT character_id, character_name, server_name 
        FROM tbl_user, tbl_character, tbl_server 
        WHERE tbl_server.server_id = tbl_character.server_id 
        AND tbl_user.user_id = tbl_character.user_id 
        AND tbl_character.user_id = '{$userID}'");
        
        $characterJSON = array();
        while ($row = $characterData->fetch_assoc()) {
            $characterJSON[] = array(
		'character_id' => $row['character_id'],
		'character_name' => $row['character_name'],
		'server_name' => $row['server_name']
            );
        }
        
        
        //----------------------GET PROGRESS----------------------------
        $progressData = $this->conn->query("SELECT tbl_progress2.character_id, tbl_family.family_name, colour_name, tbl_progress2.dragon_id, tbl_dragon.dragon_name, bool 
        FROM tbl_progress2, tbl_character, tbl_family, tbl_colour, tbl_dragon 
        WHERE tbl_progress2.character_id = tbl_character.character_id 
        AND tbl_dragon.family_id = tbl_family.family_id 
        AND tbl_character.user_id = '{$userID}'
        AND tbl_dragon.dragon_id = tbl_progress2.dragon_id 
        AND tbl_dragon.colour_id = tbl_colour.colour_id 
        ORDER BY tbl_character.character_id, tbl_dragon.dragon_id");
        
        $progressJSON = array();
        while ($row = $progressData->fetch_assoc()) {
            $progressJSON[] = array(
		'character_id' => $row['character_id'],
		'dragon_id' => $row['dragon_id'],
		'dragon_name' => $row['dragon_name'],
		'colour_name' => $row['colour_name'],
		'family_name' => $row['family_name'],
		'bool' => (bool)$row['bool']
            );
        }
        
        //----------------------GET FAQs----------------------------
        $faqData = $this->conn->query("SELECT * FROM tbl_faq");
        
        $faqsJSON = array();
        while ($row = $faqData->fetch_assoc()) {
            $faqsJSON[] = array(
		'faq_id' => $row['faq_id'],
		'faq_title' => $row['faq_title'],
		'faq_content' => $row['faq_content']
            );
        }
        
        //----------------------GET Dragons----------------------------
        $dragonData = $this->conn->query("SELECT tbl_dragon.dragon_id, dragon_name, dragon_th, dragon_lv, dragon_img, dragon_desc, tbl_family.family_id, 
        family_name, family_runspd, family_swimspd, family_flightspd, family_gldspd, colour_name FROM tbl_dragon, tbl_family, tbl_zone, tbl_field, tbl_l_location, 
        tbl_egg, tbl_l_egg, tbl_colour WHERE tbl_dragon.dragon_id = tbl_l_location.dragon_id AND tbl_dragon.dragon_id = tbl_l_egg.dragon_id 
        AND tbl_dragon.family_id = tbl_family.family_id AND tbl_l_location.zone_id = tbl_zone.zone_id AND tbl_l_location.field_id = tbl_field.field_id 
        AND tbl_l_egg.egg_id = tbl_egg.egg_id AND tbl_colour.colour_id = tbl_dragon.colour_id GROUP BY tbl_dragon.dragon_id");
        
        $dragonsJSON = array();
        while ($row = $dragonData->fetch_assoc()) {
            $zoneData = $this->conn->query("SELECT zone_name, tbl_zone.zone_id, field_name FROM tbl_l_location, tbl_zone, tbl_field, tbl_dragon
            WHERE tbl_dragon.dragon_id = tbl_l_location.dragon_id AND tbl_zone.zone_id = tbl_l_location.zone_id AND tbl_l_location.field_id = tbl_field.field_id    
            AND tbl_l_location.dragon_id =".$row['dragon_id']);

            $eggData = $this->conn->query("SELECT egg_name FROM tbl_l_egg, tbl_egg, tbl_dragon WHERE tbl_dragon.dragon_id = tbl_l_egg.dragon_id 
            AND tbl_egg.egg_id = tbl_l_egg.egg_id AND tbl_l_egg.dragon_id =".$row['dragon_id']);
            
            $locationsArray = array();
            while ($row2 = $zoneData->fetch_assoc()) {
                $locationsArray[] = array(
			'zone_id' => $row2['zone_id'],
			'zone_name' => $row2['zone_name'],
			'field_name' => $row2['field_name']
		);
            }
            
            $eggArray = array();
            while ($row3 = $eggData->fetch_assoc()) {
                $eggArray[] = array(
                    'egg_name' => $row3['egg_name']
		);
            }
            
            $dragonsJSON[] = array(
		'dragon_id' => $row['dragon_id'],
		'dragon_name' => $row['dragon_name'],
		'dragon_lv' => $row['dragon_lv'],
		'dragon_img' => $row['dragon_img'],
		'dragon_th' => $row['dragon_th'],
		'dragon_desc' => $row['dragon_desc'],
		'family_id' => $row['family_id'],
		'family_name' => $row['family_name'],
		'family_runspd' => $row['family_runspd'],
		'family_swimspd' => $row['family_swimspd'],
		'family_flightspd' => $row['family_flightspd'],
		'family_gldspd' => $row['family_gldspd'],
		'colour_name' => $row['colour_name'],
		'zones' => $locationsArray,
		'eggs' => $eggArray
            );
        }
        
        //----------------------Combine results---------------------------
        $returnData = array(
            'characterQuery' => $characterJSON,
            'progressQuery' => $progressJSON,
            'faqsQuery' => $faqsJSON,
            'dragonsQuery' => $dragonsJSON
        );
        echo json_encode($returnData);
    }
}
