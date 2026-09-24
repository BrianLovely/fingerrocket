<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'rocket.php';
include 'fortress.php';
include 'player.php';
include 'debris.php';
include 'debrisHandler.php';
include 'module.php';




class combatHandler
{
    // Properties
    public $id = 1;
    public $basePoints = 5;
    public $p1 = NULL;
    public $p2 = NULL;
    public $f1 = NULL;
    public $dbf1 = NULL;
    public $f2 = NULL;
    public $dbf2 = NULL;
    public $player = NULL;
    public $opponent = NULL;
    public $gameLog = array();
    public $gameLogBuffer = array();
    public $mysqli;
    public $resistRoll = 10;
    public $hitBonus = 10;
    public $playerUp = "player";
    public $dh;
    
    


    // Methods

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getToHit(){
        return $this->toHit;
    }

    public function setToHit($toHIt){
        $this->toHit = $toHit;
    }

    public function showAlert($msg){
        echo '<script>alert("' . $msg . '")</script>';
    }

    public function getF1(){
        return $this->f1;
    }

    public function setF1($fortress){
        $this->f1 = $fortress;
    }

    public function getF2(){
        return $this->f2;
    }

    public function setF2($fortress){
        $this->f2 = $fortress;
    }

    public function getPlayer(){
        return $this->player;
    }

    public function setPlayer($player){
        $this->player = $player;
    }

    public function getOpponent(){
        return $this->opponent;
    }

    public function setOpponent($opponent){
        $this->opponent = $opponent;
    }

    public function __construct(){
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->mysqli = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
        //$this->mysqli = new mysqli('127.0.0.1:3308', 'fingerrocket', 'L@sirena23', 'studiobl_fingerrocket');
        $this->mysqli->set_charset('utf8mb4');
        $this->dh = new debrisHandler();
    }
 
    public function retrieveFortress($fid){
        $sql = 'SELECT * FROM fortress WHERE id="' . $fid . '"';
        $result = $this->mysqli->query($sql);
        $row = $result->fetch_assoc(); 
        $temp = new Fortress();
        $temp->setFlak($row['flak']);
        $temp->setDamageResistance($row['damageResistance']);
        $temp->setHitResistance($row['hitResistance']);
        $temp->setPoints($row['points']);
        $temp->setId($row['id']);
        $temp->setStoredCladding($row['storedCladding']);
        $temp->setCladding($row['cladding']);
        $temp->setName($row['name']);
        $temp->setDamage($row['damage']);
        $temp->stockArmory($row['armory']);
        $jString = $temp->convertArmoryToJson();
        $temp->setJsonArmory($jString);
        $temp->setCombatHandler($this);
        return $temp;
    }

    

    public function sanityCheck(){
        print "sanity check<br/>";
    }

    public function togglePlayer(){
        if($this->playerUp == 'player'){
            $this->playerUp = 'opponent';
        } else {
            $this->playerUp = 'player';
        }
    }

    private function rollDie($dieType){
        return rand(1, $dieType);
    }

    public function getDamage($dieType){
        return $this->rollDie($dieType);
    }

    public function convertLogToJson(){
        $temp = "[";
        $i = 1;
        foreach($this->gameLog as $key=>$val){
            $temp = $temp . json_encode($val);
            if($i < count($this->gameLog)){
                $temp = $temp . ",";
            }
            $i++;
        }
        $temp = $temp . "]";
        return $temp;
    }

    public function convertJsonToLog($json){
        $this->gameLog = json_decode($json);
    }


    public function storeGameLog(){
        if(count($this->gameLog) > 100){
            $temp = array_slice($this->gameLog, -1, 50);
            unset($this->gameLog);
            $this->gameLog = $temp;
        }
        $gameLog = $this->convertLogToJson();
        $id = $this->id;
        $sql = "UPDATE gamehandler SET gameLog = ? WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $gameLog, $id);
        $stmt->execute();
        //echo "storeGameLog sanity: " . $this->id . "<br/>";
    }

    public function addToGameLog($string){
        array_push($this->gameLog, $string);
        array_push($this->gameLogBuffer, $string);
        }



    public function displayGameLog(){
        $sql = "SELECT `gameLog` FROM `gamehandler` WHERE id = 1";
        $result = $this->mysqli->query($sql);
        while($row = $result->fetch_array(MYSQLI_ASSOC)){
            $tempArray = json_decode($row['gameLog']);
            $logArray = array_reverse($tempArray);
            foreach($logArray as $key => $val){
                print "<p>" . $val . "</p>";
                
            }
        }
        
    }


    public function storeFortressName($id, $name){
        //echo $id . " " . $name . "<br/>";
        $sql = "UPDATE fortress SET 
        name=?
        WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $name, $id);
        $stmt->execute();
        //printf("Affected rows (UPDATE): %d\n", $this->mysqli->affected_rows);
    }

    public function storeArmory($id, $armory){
        $sql = "UPDATE fortress SET 
        armory=?
        WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $armory, $id);
        $stmt->execute();
        $stmt->close();
        
    }

    public function storeCladding($id, $damageResistance, $cladding, $storedCladding){
       
        $sql = "UPDATE fortress SET 
        damageResistance=?,
        cladding=?,
        storedCladding=?
        WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iiis", $damageResistance, $cladding, $storedCladding, $id);
        $stmt->execute();
        $stmt->close();
        
    }

    //Players and fortresses are stored as IDs
    //IDs are always stored so that p1's fortress is f1, etc.
    public function store(){
        $id = $this->id;
        $p1 = $this->p1;
        $p2 = $this->p2;
        $success = false;
        if($this->player !== NULL && $this->player->fortress != NULL){
            $f1 = $this->player->fortress->getId();
        } else {
            $f1 = $this->f1;
        }
        if($this->opponent !== NULL && $this->opponent->fortress != NULL){
            $f2 = $this->opponent->fortress->getId();
        } else {
            $f2 = $this->f2;
        }
        $basePoints = $this->basePoints;
        $playerUp = $this->playerUp;
        $sql = "INSERT INTO gamehandler (id, p1, p2, f1, f2, basePoints, playerUp) VALUES ('" . $id . "','" . $p1 . "','" . $p2 . "','" . $f1 . "','" . $f2 . "','" . $basePoints . "','" . $playerUp . "')";
        if ($this->mysqli->query($sql) === TRUE) {
            $success = true;
        } 
        return $success;

    }

    

    public function storeNewHandler(){
        $id = $this->getId();
        $basePoints = $this->basePoints;
        $f1 = $this->f1;
        $f2 = $this->f2;
        $gameLog = $this->gameLog;
        $playerUp = $this->playerUp;
        $sql = "INSERT INTO `gamehandler`(`id`, `basePoints`, `f1`, `f2`, `gameLog`, `playerUp`) VALUES ('" . $id . "','"  . $basePoints . "','" . $f1 . "','" . $f2 . "','" . $gameLog . "','" . $playerUp . "')";
       if ($this->mysqli->query($sql) === TRUE) {
            
        } else {
           
        }
    }//End function storeNewHandler

    
    

    

    public function storeFortress($id, $armory, $cladding, $storedCladding, $damageResistance, $points, $flak){
        $hitResistance = $damageResistance;
        $sql = "UPDATE fortress SET 
        armory=?,
        cladding=?,
        storedCladding=?,
        damageResistance=?,
        hitResistance=?,
        points=?,
        flak=?
        WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("siiiiiis", $armory, $cladding, $storedCladding, $damageResistance, $hitResistance, $points, $flak, $id);
       
        $stmt->execute();
        /* close statement */
        $stmt->close();
        
    }

    public function handleFlak($fortress){
        $flak = $fortress->getFlak();
        $roll = $this->rollDie(10);
        $points = $this->basePoints;
        $pointRoll = 0;
        if($roll <= $flak){
            $this->addToGameLog("The rocket is destroyed by " . $fortress->getName() . "'s flak!");
            $fortress->removeFlak(1);
            
            $pointRoll = $this->rollDie(5);
            $points = $points + $pointRoll;
            $fortress->addPoints($points);
            $this->addToGameLog($fortress->getName() . " is awarded " . $points . " points!");
            $this->storeGameLog();
            return false;
        } else {
            return true;
        }
    }

    public function handleCluster($attacker, $defender){
        $this->addToGameLog($attacker->getName() . " attacks " . $defender->getName() . " with a cluster rocket!");
        $clusterCount = 5;
        $this->addToGameLog("The cluster rocket splits into " . $clusterCount . " finger rockets!");
        
        $this->storeGameLog();
        while($clusterCount > 0){
            $temp = new fingerRocket();
            $temp->id = uniqid();
            $this->handleCombat($temp);
            $clusterCount --;
        }

    }

    public function isHit($toHit, $hitRes, $cladding){
        $roll = $this->rollDie(10);
        $res = $roll + $hitRes + $cladding;
        $hitSave = $this->rollDie(10);
        $hitChance = $hitSave + $toHit + $this->hitBonus;
        //$this->addToGameLog("Defender resistance D10 roll: " . $roll . " + damageRes: " . $hitRes . " + Cladding: " . $cladding . " Total: " . $res);
        //$this->addToGameLog("Rocket toHit: " . $toHit . " + D10 roll: " . $hitSave . " Total: " . $hitChance); 
        if($res < $hitChance){
            $this->addToGameLog("It's a hit!");
            return true;
        } elseif($res >= $hitChance){
            $this->addToGameLog("It's a miss!");
            return false;
        }
    }

    public function handleDamage($attacker, $defender, $rocket){
        //echo "handleDamage sanity<br/>";
        $attacker = $this->player->getFortress();
        $defender = $this->opponent->getFortress();
        $cladding = $defender->getCladding();
        $damageRoll = $this->rollDie($rocket->dieType);
        if($damageRoll <= $defender->getDamageResistance() ){
            $damageRoll = $damageRoll / 2;
        }
        $damage = $damageRoll * 10;
        $affinity = $this->handleAffinity($rocket->getTypeId(), $this->player->fortress->getCladding());
        if($affinity == false){
            $this->addToGameLog("Ouch, that really hurt!");
            $damage = $damage + 5;
        }
        if($affinity == true){
            $this->addToGameLog("Man, that cladding is tough!");
            $damage = $damage - 5;
        }
        $defender->applyDamage($damage);
        $this->addToGameLog($defender->getName() . "'s " .  $defender->convertCladdingToString($defender->getCladding()) . " cladding takes " . $damage . " damage!");
        $changedCladding = $defender->getCladding();
        if($cladding != $changedCladding){
            $this->addToGameLog($defender->getName() . "'s " . $defender->convertCladdingToString($cladding) . " cladding is degraded to " . $defender->convertCladdingToString($changedCladding));
        }
        $damageRes = $this->opponent->fortress->getDamageResistance();
        $points = $rocket->toHit - $damageRes;
        $points = $points + $this->basePoints;
        $attacker->addPoints($points);
                
        $this->addToGameLog($attacker->getName() . " is awarded " . $points . " points!");
    }

    public function handleDebris($type, $material = NULL){
        $debris = $this->dh->handleDebris($type, $material);
        return $debris;
    }//End function handleDebris

    public function getMaterial($fortress){
        $temp = $fortress->convertCladdingToString();
        $pieces = explode(" ", $temp);
        $material = array_pop($pieces);
        return $material;
    }

    public function logDebris($debris, $fortress){
        $name = $fortress->getName();
        $debrisName;
        foreach($debris as $k => $v){
            $debrisName = $v->getName();
            $this->addToGameLog("The " . $name . " recovers a " . $debrisName . ".");
        }
    }

    public function handleAffinity($rocketType, $material){
        $affinity = NULL;
        if($rocketType == 3 && $material == 1){
            $affinity = true;
        }
        if($rocketType == 4 && $material == 2){
            $affinity = true;
        }
        if($rocketType == 5 && $material == 3){
            $affinity = true;
        }
        if($rocketType == 0 && $material == 3){
            $affinity = false;
        }
        if($rocketType == 6 && $material == 3){
            $affinity = true;
        }
        if($rocketType == 1 && $material == 5){
            $affinity = false;
        }
        if($rocketType == 2 && $material == 6){
            $affinity = false;
        }
        if($rocketType == 3 && $material == 7){
            $affinity = false;
        }
        if($rocketType == 4 && $material == 8){
            $affinity = true;
        }
        return $affinity;
    }

    public function handleCombat($rocket){
        //echo $this->getId() . " handling combat<br/>";
        /* Ensure lurking POST vars don't trigger unwanted log entries */
        if ($rocket === NULL) {
            return false;
        }
        if(strlen($rocket->id) < 2){
            return false;
        }
        $attacker = $this->player->getFortress();
        $defender = $this->opponent->getFortress();
        $this->togglePlayer();
        
        if($rocket->name == "Cluster Rocket"){
            $this->handleCluster($attacker, $defender);
            return;
        }
        $this->addToGameLog($attacker->getName() . " attacks " . $defender->getName() . " with a " . $rocket->name . "!");
        //echo "handle combat sanity<br/>";
        if($this->handleFlak($defender)){
            //echo "Rocket not destroyed by flak<br/>";
            $cladding = $this->opponent->fortress->getCladding();
            if($this->isHit($rocket->toHit, $this->opponent->fortress->getHitResistance(), $cladding)){
                //echo "it is a hit!<br/>";
                $this->handleDamage($attacker, $defender, $rocket);
                $rDebris = $this->handleDebris(0);
                if(count($rDebris) > 0){
                    //echo "debris: " . json_encode($rDebris) . "<br/>";
                    $this->player->processDebris($rDebris);
                    //echo "rocket debris sanity<br/>";
                    $this->logDebris($rDebris, $attacker);
                }
                
                $material = $this->getMaterial($defender);
                $cDebris = $this->handleDebris(1, $material);
                $this->opponent->processDebris($cDebris);
                $this->logDebris($cDebris, $defender);
                //remember debris is returned as an array. Add debris must account for that
            } else {
                //echo "it is a miss!<br/>";
                $damRes = $this->rollDie(100);
                $points = $damRes - $rocket->toHit;
                $points = max(0, $points + $this->basePoints);
                $defender->addPoints($points);
                $this->addToGameLog($defender->getName() . " is awarded " . $points . " points!");
            }
        }   
        //echo "handleCombat sanity<br/>";
        $this->storeGameLog();
        $this->player->fortress->store();
        $this->opponent->fortress->store();
        

    }//End function handleCombat

    // Pass in player id, get f1 & f2 assignments for player & opponent
    public function getSlots($id){
        $slots['player'] = NULL;
        $slots['opponent'] = NULL;
        if($this->f1 == $id){
            $slots['player'] = "f1";
            $slots['opponent'] = "f2";
        } elseif($this->f2 == $id){
            $slots['player'] = "f2";
            $slots['opponent'] = "f1";
        }
        return $slots;
    }

    
    //Package handler, players, and fortresses for passing to page
    public function package($log = true){
        $array = [];
        $array['handlerId'] = $this->id;
        if($this->f1 != NULL){
            $array['f1'] = $this->player->fortress->package();
            
        }
        if($this->f2 != NULL){
            $array['f2'] = $this->opponent->fortress->package();
        }
        
        if($this->player != NULL){
            $array['player'] = $this->player->package();
        }
        
        if($this->opponent != NULL){
            $array['opponent'] = $this->opponent->package();
        }
        $array['playerUp'] = $this->playerUp;
        if($this->gameLog != NULL && $log == true){
            
            $array['log'] = array_reverse($this->gameLog);
        }
        if($this->gameLogBuffer != NULL && $log == false){
            $array['log'] = array_reverse($this->gameLogBuffer);
        }
        $array = json_encode($array);
        //Clear the buffer
        array_slice($this->gameLogBuffer, 0);
        return $array;
    }

    
//Select player data by ID 
function selectPlayerData($pId){
    $sql = 'SELECT * FROM `players` WHERE id="' . $pId  .'"';
    $result = $this->mysqli->query($sql);
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
   
    return $row;
}//End function selectPlayerData

    public function selectPlayer($pId){
        $fData = $this->selectPlayerData($pId);
        $temp = new player();
        $temp->id = $fData['id'];
        $temp->setUserName($fData['username']);
        $temp->setPass($fData['pass']);
        $temp->itemArray = isset($fData['items']) && !empty($fData['items'])
            ? json_decode($fData['items'], true)
            : $temp->itemArray;
        $this->setPlayer($temp);
        //echo "select player sanity check<br/>";
    }
    
//Select combathandler by ID
function selectCombatHandler($hId){
    $sql = 'SELECT * FROM `gamehandler` WHERE id="' . $hId  .'"';
    $result = $this->mysqli->query($sql);
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
    $this->id = $row['id'];
    $this->playerUp = $row['playerUp'];
    $this->gameLog = json_decode($row['gameLog']);
    $this->p1 = $row['p1'];
    $this->f1 = $row['f1'];
    $this->p2 = $row['p2'];
    $this->f2 = $row['f2'];
    
}//End function selectCombatHandler

//Find combat handler by player ID
public function findCombatHandlers($pId){
    $sql = 'SELECT * FROM `gamehandler` WHERE p1="' . $pId  .'" OR p2="' . $pId . '"';
    $result = $this->mysqli->query($sql);
    $hArray = [];
    if(mysqli_num_rows($result) > 0){
        while ($row = $result->fetch_assoc()) {
            array_push($hArray, $row);
        }
        mysqli_free_result($result);
    }
    return $hArray;
    
}//End function findCombatHandlers

//Confirm a handler exists
public function handlerExists($pId){
    $sql = 'SELECT * FROM `gamehandler` WHERE p1="' . $pId  .'" OR p2="' . $pId . '"';
    $result = $this->mysqli->query($sql);
    $hArray = [];
    if(mysqli_num_rows($result) > 0){
        return true;
    }elseif(mysqli_num_rows($result) == 0){
        return false;
    }
}//End function handlerExists



    //Select friend by ID
    public function selectFriend($fId){
        $fData = $this->selectPlayerData($fId);
        $temp = new player();
        $temp->id = $fData['id'];
        $temp->setUserName($fData['username']);
        $temp->setPass($fData['pass']);
        $this->setOpponent($temp);
        //echo "select friend sanity check<br/>";
    }

//Find empty handler slot for fortress
public function putFortressInEmptySlot($fortress){
    if($this->f1 == NULL){
        $this->f1 = $fortress;
    }elseif($this->f2 == NULL){
        $this->f2 = $fortress;
    }
}//End function checkSlots

//Check if all fortress slots are empty
public function allFortressSlotsEmpty(){
    $empty = false;
    if($this->f1 == NULL || $this->f2 == NULL){
        $empty = true;
    }
    return $empty;
}

function selectFortress($fId){
    $sql = 'SELECT * FROM `fortress` WHERE id="' . $fId  .'"';
    $result = $this->mysqli->query($sql);
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
    return $row;
}//End function selectFortress

//Find fortress in slots
public function findFortressInSlots($fId){
    if($this->f1 !== NULL && $this->f1->getId() == $fId){
        return $this->f1;
    }elseif($this->f2 !== NULL && $this->f2->getId() == $fId){
        return $this->f2;
    }
    return NULL;
}//End function findFortressInSlots

//Slot fortress into empty slot
public function slotFortress($fortress){
    $this->checkSlots = $fortress;
}//End function slotFortress




public function createFortress(){
        $temp = new Fortress();
        $tempId = uniqid();
        $temp->store();
        $temp->setRandomName();
        $random = $temp->getName();
        $this->storeFortressName($tempId, $random);
        
        return $temp;
}//End function createFortress

public function setUpFortress($row){
    $tempFortress = new Fortress();
    $tempFortress->setId($row['id']);
    $tempFortress->setFlak($row['flak']);
    $tempFortress->setHitResistance($row['hitResistance']);
    $tempFortress->setDamageResistance($row['damageResistance']);
    $tempFortress->setPoints($row['points']);
    $tempFortress->setCladding($row['cladding']);
    $tempFortress->setStoredCladding($row['storedCladding']);
    $tempFortress->setName($row['name']);
    //$tempFortress->debrisChance->$row['debrisChance'];
    //$tempFortress->debrisValue->$row['debrisValue'];
    $tempFortress->stockArmory($row['armory']);
    return $tempFortress;
}//End function setUpPlayerFortress

//After loading handler, load any fortresses
public function setUpFortresses($playerId){
    $playerFortress = NULL;

    if($this->f1 == NULL){
        $tempFortress = $this->createFortress();
        $tempFortress->pId = $playerId;
        $tempFortress->setRandomName();
        $uniq = uniqid();
        $tempFortress->setId($uniq);
        $this->setF1($tempFortress);
        $tempFortress->storeNew();
    }elseif($this->f1 != NULL){
        $tempFortress = $this->retrieveFortress($this->dbf1);
        $this->setF1($tempFortress);
        $playerFortress = "f1";
    }

    if($this->f2 == NULL){
        $tempFortress = $this->createFortress();
        $tempFortress->pId = $playerId;
        $tempFortress->setRandomName();
        $uniq = uniqid();
        $tempFortress->setId($uniq);
        $tempFortress->storeNew();
        $this->setF2($tempFortress);
    }elseif($this->f2 != NULL){
        $tempFortress = $this->retrieveFortress($this->dbf2);
        $this->setF2($tempFortress);
        $playerFortress = "f2";
    }

    return $playerFortress;
}//End function setUpFortresses

//Create player
public function createPlayer(){
    $temp = new player();
    return $temp;
}//End function createPlayer

//Load DB data from sql row into existing handler player object
public function loadPlayer($row){
    $this->player->id = $row['id'];
    $this->player->username = $row['username'];
    $this->player->pass = $row['pass'];
    $this->player->fId = $row['fId'];
}//End function loadPlayer

//Player and opponent are stored as ids in p1 and p2
//Fortress ids are always stored so P1's fortress is F1, etc.
public function findHandlerForBothPlayers($pId, $oId){
    //echo "find handlers sanity<br/>";
    $sql = 'SELECT * FROM `gamehandler` WHERE (p1="' . $pId . '" AND p2="' . $oId . '") OR (p1="' . $oId . '" AND p2="' . $pId . '")';
    $result = $this->mysqli->query($sql);
    if ($result === FALSE || $result->num_rows === 0) {
        return FALSE;
    }
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
    $this->selectFriend($oId);
    $this->selectPlayer($pId);
    $tFRow = $this->selectFortress($row['f1']);
    $tempFortress = $this->setUpFortress($tFRow);
    //echo "find both sanity check: " . $pId . " & " . $oId . " f1: " . $row['f1'] . "<br/>";
    $this->setF1($tempFortress);
    $this->player->setFortress($tempFortress);
    unset($tFRow);
    unset($tempFortress);
    $tFRow = $this->selectFortress($row['f2']);
    $tempFortress = $this->setUpFortress($tFRow);
    $this->setF2($tempFortress);
    $this->opponent->setFortress($tempFortress);
    $this->setId($row['id']);
    if($row['gameLog'] != NULL){
        $this->convertJsonToLog($row['gameLog']);
    }
    return TRUE;
    
    //echo "find both handler id: " . $row['id'] . "<br/>";
    //Later handle what if there's no such handler
} 


//Find fortress names in available handlers
public function packageGameChoices($assoc, $pId){
    $fArray = [];
    foreach($assoc as $key=>$val){
        $tArray = [];
        if($val['p1'] == $pId){
            $pFRow = $this->selectFortress($val['f1']);
            $oFRow = $this->selectFortress($val['f2']);
        }elseif($val['p2'] == $pId){
            $pFRow = $this->selectFortress($val['f2']);
            $oFRow = $this->selectFortress($val['f1']);
        }else{
            continue;
        }
        if($pFRow === NULL || $oFRow === NULL){
            continue;
        }
        $tArray['handlerId'] = $val['id'];
        $tArray['playerFortressName'] = $pFRow['name'];
        $tArray['playerFortressPoints'] = $pFRow['points'];
        $tArray['opponentFortressName'] = $oFRow['name'];
        $tArray['opponentFortressPoints'] = $oFRow['points'];
        $fArray[] = $tArray;
    }
    return $fArray;
}//End function packageGameChoices

public function loadGameFromOptions($pId, $hId){ 
    $this->selectCombatHandler($hId);
    $this->selectPlayer($pId);
   // IDs in p1, p2, f1, f2 may not be in correct slots. Use $pId to sort them out
    if($this->p1 != $pId){
        //If player ID isn't in P1, 
        $this->selectFriend($this->p1);
        $tempRow = $this->selectFortress($this->f1);
        $temp = $this->setUpFortress($tempRow);
        $this->opponent->setFortress($temp);
        $tempRow = $this->selectFortress($this->f2);
        $temp = $this->setUpFortress($tempRow);
        $this->player->setFortress($temp);


    }elseif($this->p1 == $pId){
        $this->selectFriend($this->p2);
        $tempRow = $this->selectFortress($this->f2);
        $temp = $this->setUpFortress($tempRow);
        $this->opponent->setFortress($temp);
        $tempRow = $this->selectFortress($this->f1);
        $temp = $this->setUpFortress($tempRow);
        $this->player->setFortress($temp);
    }
    $_SESSION['playerId'] = $this->player->getId();
    $_SESSION['friendId'] = $this->opponent->getId();
    return $this->package();
}//End function loadGameFromOptions

//Check if player exists. Return Bool and if yes game choices
function authPlayer($username, $pass){
        $tArray['playerExists'] = FALSE;
        $tArray['handlerExists'] = FALSE;

        /* Get matching player if any */
        $sql = 'SELECT * FROM `players` WHERE username="' . $username . '" AND pass="' . $pass .'"';
        $result = $this->mysqli->query($sql);
        $row = $result->fetch_assoc();
        mysqli_free_result($result);

        if($row !== NULL && !empty($row)){
            $tArray['playerExists'] = TRUE;
            $_SESSION['playerId'] = $row['id'];
            $tArray['playerId'] = $row['id'];
            $handlerExists = $this->handlerExists($row['id']);
            if($handlerExists){
                $handlers = $this->findCombatHandlers($row['id']);
                $tArray['handlerExists'] = true;
                $tArray['fortresses'] = $this->packageGameChoices($handlers, $row['id']);
            }
        }

        return json_encode($tArray);
        
    } //End function authPlayer


    public function isEmailUnique($email){
        $unique = true;
        $sql = 'SELECT * FROM `players` WHERE username="' . $email .'"';
        $result = $this->mysqli->query($sql);
        if(mysqli_num_rows($result) > 0){
            $unique = false;
        }
        return $unique;
    }

    public function validateEmail($email){
        $valid = false;
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
            $valid = true;
        }
        return $valid;
    }

    public function validatePass($pass){
        $valid = false;
        if(strlen($pass) > 7 ){
            $valid = true;
        }
        return $valid;
    }




} /* End Object */
$ch = new combatHandler();
$ch->id = uniqid();


/* 
We'll reset that ID if the desired handler is stored in the DB.
We need both player & friend ID to find existing handler
*/


if (isset($_POST['playerId'])) {
    if (!isset($_SESSION['count'])) {
        $_SESSION['playerId'] = $_POST['playerId'];
        $_SESSION['hId'] = $_POST['hId'];
    }
}


//$_POST['options_playerId'] = "681f762053cb6";
//$_POST['options_handlerId'] = "68443e5fd233b";
if(isset($_POST['options_playerId'])){
    echo $ch->loadGameFromOptions($_POST['options_playerId'], $_POST['options_handlerId']);
    $_SESSION['playerId'] = $_POST['options_playerId'];
    $_SESSION['handlerId'] = $_POST['options_handlerId'];
    unset($_POST['options_playerId']);
    unset($_POST['options_handlerId']);
}//End handler choose game option



if (isset($_POST['fr_test'])) {
   //echo $ch->package();
    unset($_POST['fr_test']);
} 
    /*$attacker = $ch->opponent;
    $rocket = $ch->opponent->getRocket("681a19b71c47e");
    $ch->handleCombat($attacker, $rocket);
    echo $ch->package();*/

//$_POST['username'] = "jeff";
//$_POST['pass'] = "frpass";
// Handle player login
if(isset($_POST['username']) && isset($_POST['pass'])){
    $checkJson = $ch->authPlayer($_POST['username'], $_POST['pass']); 
    $checkArray = json_decode($checkJson, true);
    if($checkArray['playerExists']){
        $_SESSION['playerId'] = $checkArray['playerId'];
    }
   echo $checkJson; 
   unset($_POST['username']);
   unset($_POST['pass']);
} //End handle player login


//$_POST['signup_username'] = "brian.lovely.23@gmail.com";
//$_POST['signup_pass'] = "L@sirena23";
//Handle new player signup
if(isset($_POST['signup_username']) && isset($_POST['signup_pass'])){
    $signupArray['success'] = false;
    $signupArray['usernameValid'] = $ch->validateEmail($_POST['signup_username']);
    $signupArray['passValid'] = $ch->validatePass($_POST['signup_pass']);
    $signupArray['emailUnique'] = $ch->isEmailUnique($_POST['signup_username']);
   
    if($signupArray['usernameValid'] && $signupArray['passValid'] && $signupArray['emailUnique']){
        $temp = new player();
        $temp->setUniqueId();
        $temp->setUserName($_POST['signup_username']);
        $temp->setPass($_POST['signup_pass']);
        $success = $temp->storeNew();
        if($success){
            $signupArray['success'] = true;
        }
    }
    echo json_encode($signupArray);
} //End player signup


//$_POST['friendId'] = "681f762053cb5";
//$_POST['playerId'] = "681f762053cb6";
/* Associate Player with Friend's ID */
if(isset($_POST['friendId'])){
    $ch->findHandlerForBothPlayers($_POST['playerId'], $_POST['friendId']);
    echo $ch->package();
    unset($_POST['friendId']);
    unset($_POST['playerId']);
}

//New Game
//$_POST['new_game_playerId'] = "685a8ce4b0e0a";
if(isset($_POST['new_game_playerId'])){
    $ch->selectPlayer($_POST['new_game_playerId']);
    $ch->p1 = $ch->player->getId();
    $success = $ch->store();
    $newGame['success'] = $success;
    echo json_encode($newGame);
}

//Attack!
//$_POST['player_rockets'] = "12345";
//$_SESSION['playerId'] = "681f762053cb6";
//$_SESSION['friendId'] = "681f762053cb5";
 if(isset($_POST['player_rockets'])){
    $ch->findHandlerForBothPlayers($_SESSION['playerId'], $_SESSION['friendId']);
    $rocket = $ch->player->fortress->getRocket($_POST['player_rockets']);
    if ($rocket === NULL) {
        echo json_encode(['error' => 'That rocket is no longer available. Refresh the game state and choose another rocket.']);
    } else {
        $ch->handleCombat($rocket);
        //pass false to package to only update log from buffer
        echo $ch->package(false);
    }
    unset($_POST['player_rockets']);
 };

//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_gunShop'] = 4;
//$_POST['player_quan'] = 2;
if(isset($_POST['player_gunShop'])){
    $ch->findHandlerForBothPlayers($_SESSION['playerId'], $_SESSION['friendId']);
    $ch->player->fortress->addToArmory($_POST['player_gunShop'], $_POST['player_quan']);
    echo $ch->package(false);

    
 };

//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_claddingShop'] = 4;
 if(isset($_POST['player_claddingShop'])){
    $ch->findHandlerForBothPlayers($_SESSION['playerId'], $_SESSION['friendId']);
    $ch->player->fortress->updateCladding($_POST['player_claddingShop']);
    //echo "cladding shop sanity<br/>";
    $ch->addToGameLog($ch->player->fortress->getName() . " is upgrading it's cladding to " . $ch->player->fortress->convertCladdingToString() . "<br/>");
    $ch->storeGameLog();
    echo $ch->package();
    unset($_POST['player_claddingShop']);
 };

//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_flakShop'] = 4;
 if(isset($_POST['player_flakShop'])){
    $ch->findHandlerForBothPlayers($_SESSION['playerId'], $_SESSION['friendId']);
    $ch->player->fortress->addFlak($_POST['player_flakShop']);
    echo $ch->package(false);
    unset($_POST['player_flakShop']);
 };


//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_nameChange'] = "Remorseless Bastion of Negativity";
 if(isset($_POST['player_nameChange'])){
    $ch->findHandlerForBothPlayers($_SESSION['playerId'], $_SESSION['friendId']);
    $id = $ch->player->fortress->getId();
    $ch->storeFortressName($id, $_POST['player_nameChange']);
    echo $ch->package(false);
 }




?>