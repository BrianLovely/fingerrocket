<?php

ob_start();

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
    public $targetScore = 1000;
    public $winnerId = NULL;
    public $gameStatus = 'active';
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
    public $affinityRules = array();
    public $dh;
    
    


    // Methods

    /** Returns the handler ID from `$this->id`; uses no arguments. @return mixed The handler ID. */
    public function getId(){
        return $this->id;
    }

    /** Stores `$id` in `$this->id`; uses the supplied ID. @param mixed $id Handler ID. @return void */
    public function setId($id){
        $this->id = $id;
    }

    /** Returns the to-hit value from `$this->toHit`; uses no arguments. @return mixed The to-hit value. */
    public function getToHit(){
        return $this->toHit;
    }

    /** Assigns the local `$toHit` value to `$this->toHit`; `$toHIt` is declared but not referenced. @param mixed $toHIt Declared hit value. @return void */
    public function setToHit($toHIt){
        $this->toHit = $toHit;
    }

    /** Prints a browser alert using `$msg`; returns no value. @param string $msg Alert text. @return void */
    public function showAlert($msg){
        echo '<script>alert("' . $msg . '")</script>';
    }

    /** Returns the first fortress from `$this->f1`; uses no arguments. @return mixed The first fortress. */
    public function getF1(){
        return $this->f1;
    }

    /** Stores `$fortress` as `$this->f1`; uses the supplied fortress. @param mixed $fortress First fortress. @return void */
    public function setF1($fortress){
        $this->f1 = $fortress;
    }

    /** Returns the second fortress from `$this->f2`; uses no arguments. @return mixed The second fortress. */
    public function getF2(){
        return $this->f2;
    }

    /** Stores `$fortress` as `$this->f2`; uses the supplied fortress. @param mixed $fortress Second fortress. @return void */
    public function setF2($fortress){
        $this->f2 = $fortress;
    }

    /** Returns the current player from `$this->player`; uses no arguments. @return mixed The current player. */
    public function getPlayer(){
        return $this->player;
    }

    /** Stores `$player` in `$this->player`; uses the supplied player object. @param mixed $player Current player. @return void */
    public function setPlayer($player){
        $this->player = $player;
    }

    /** Returns the opponent from `$this->opponent`; uses no arguments. @return mixed The opponent. */
    public function getOpponent(){
        return $this->opponent;
    }

    /** Stores `$opponent` in `$this->opponent`; uses the supplied player object. @param mixed $opponent Opposing player. @return void */
    public function setOpponent($opponent){
        $this->opponent = $opponent;
    }

    /** Opens the MySQL connection and creates a debris handler; uses no arguments or return value. @return void */
    public function __construct(){
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->mysqli = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
        //$this->mysqli = new mysqli('127.0.0.1:3308', 'fingerrocket', 'L@sirena23', 'studiobl_fingerrocket');
        $this->mysqli->set_charset('utf8mb4');
        $this->dh = new debrisHandler();
    }
 
    /** Loads a fortress row and builds a Fortress object; uses `$fid` and `$this->mysqli`. @param string $fid Fortress ID. @return Fortress Loaded fortress. */
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

    

    /** Prints a fixed diagnostic message; uses no variables and returns no value. @return void */
    public function sanityCheck(){
        print "sanity check<br/>";
    }

    /** Switches `$this->playerUp` between player and opponent; returns no value. @return void */
    public function togglePlayer(){
        if($this->playerUp == 'player'){
            $this->playerUp = 'opponent';
        } else {
            $this->playerUp = 'player';
        }
    }

    /** Rolls an integer from 1 through `$dieType`; uses the die size. @param int $dieType Highest roll. @return int Random roll. */
    private function rollDie($dieType){
        return rand(1, $dieType);
    }

    /** Rolls damage using `$dieType` via `rollDie`; returns the roll. @param int $dieType Highest damage roll. @return int Damage roll. */
    public function getDamage($dieType){
        return $this->rollDie($dieType);
    }

    /** Encodes `$this->gameLog` as a JSON array string; uses the log entries. @return string Encoded game log. */
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

    /** Decodes `$json` into `$this->gameLog`; uses the JSON string and returns no value. @param string $json Encoded game log. @return void */
    public function convertJsonToLog($json){
        $this->gameLog = json_decode($json);
    }


    /** Trims and persists `$this->gameLog` for `$this->id`; returns no value. @return void */
    public function storeGameLog(){
        if(count($this->gameLog) > 100){
            $temp = array_slice($this->gameLog, -50);
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

    /** Resolves `$this->playerUp` to a player ID using `$this->p1` and `$this->p2`; returns that ID. @return mixed Current turn player ID. */
    public function getTurnPlayerId(){
        if($this->playerUp === 'player'){
            return $this->p1;
        }
        if($this->playerUp === 'opponent'){
            return $this->p2;
        }
        return $this->playerUp;
    }

    /** Checks the active game row to see whether `$pId` owns the turn in `$hId`; returns a boolean. @param string $pId Player ID. @param string $hId Game handler ID. @return bool Whether it is the player's turn. */
    public function isPlayerTurn($pId, $hId){
        $sql = "SELECT p1, p2, playerUp, gameStatus FROM gamehandler WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $hId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if($row === NULL || $row['gameStatus'] !== 'active'){
            return false;
        }
        $turnPlayerId = $row['playerUp'];
        if($turnPlayerId === 'player'){
            $turnPlayerId = $row['p1'];
        } elseif($turnPlayerId === 'opponent'){
            $turnPlayerId = $row['p2'];
        }
        return $turnPlayerId === $pId;
    }

    /** Persists `$playerId` as the next turn and updates `$this->playerUp`; returns no value. @param string $playerId Next player ID. @return void */
    public function storeTurn($playerId){
        $sql = "UPDATE gamehandler SET playerUp = ? WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $playerId, $this->id);
        $stmt->execute();
        $stmt->close();
        $this->playerUp = $playerId;
    }

    /** Randomly pairs strong and weak materials for rocket types 0 through 7; returns the rules array. @return array Generated affinity rules. */
    public function generateAffinityRules(){
        $rules = array();
        for($rocketType = 0; $rocketType <= 7; $rocketType++){
            $materials = range(0, 8);
            shuffle($materials);
            $rules[(string)$rocketType] = array('strong' => $materials[0], 'weak' => $materials[1]);
        }
        return $rules;
    }

    /** Generates and persists affinity rules when `$this->affinityRules` is empty; returns the rules array. @return array Current affinity rules. */
    public function ensureAffinityRules(){
        if(empty($this->affinityRules)){
            $this->affinityRules = $this->generateAffinityRules();
            $affinities = json_encode($this->affinityRules);
            $stmt = $this->mysqli->prepare("UPDATE gamehandler SET affinities = ? WHERE id = ?");
            $stmt->bind_param("ss", $affinities, $this->id);
            $stmt->execute();
            $stmt->close();
        }
        return $this->affinityRules;
    }

    /** Appends `$string` to both game-log arrays; returns no value. @param string $string Log entry. @return void */
    public function addToGameLog($string){
        array_push($this->gameLog, $string);
        array_push($this->gameLogBuffer, $string);
        }



    /** Reads and prints the stored game log for handler ID 1; uses the database and returns no value. @return void */
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


    /** Updates a fortress name in the database; uses `$id`, `$name`, and `$this->mysqli`. @param string $id Fortress ID. @param string $name New name. @return void */
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

    /** Persists the armory JSON for fortress `$id`; uses `$armory` and `$this->mysqli`. @param string $id Fortress ID. @param string $armory Encoded armory. @return void */
    public function storeArmory($id, $armory){
        $sql = "UPDATE fortress SET 
        armory=?
        WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $armory, $id);
        $stmt->execute();
        $stmt->close();
        
    }

    /** Persists cladding values for fortress `$id`; uses the supplied values and `$this->mysqli`. @param string $id Fortress ID. @param int $damageResistance Damage resistance. @param int $cladding Current cladding. @param int $storedCladding Stored cladding. @return void */
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
    /** Inserts this handler's game state into the database; uses handler and player/fortress state. @return bool Whether the insert succeeded. */
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
        $targetScore = $this->targetScore;
        $gameStatus = $this->gameStatus;
        $sql = 'INSERT INTO gamehandler (id, p1, p2, f1, f2, basePoints, playerUp, targetScore, gameStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $statement = $this->mysqli->prepare($sql);
        $statement->bind_param('sssssisis', $id, $p1, $p2, $f1, $f2, $basePoints, $playerUp, $targetScore, $gameStatus);
        $success = $statement->execute();
        $statement->close();
        return $success;

    }

    

    /** Inserts the handler's initial ID, score, fortresses, log, and turn into the database; returns no value. @return void */
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

    
    

    

    /** Persists fortress combat and inventory values; uses all supplied fields and `$this->mysqli`. @param string $id Fortress ID. @param string $armory Encoded armory. @param int $cladding Current cladding. @param int $storedCladding Stored cladding. @param int $damageResistance Damage resistance. @param int $points Score. @param int $flak Flak count. @return void */
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

    /** Rolls fortress `$fortress` flak, updates scores/log state when it destroys a rocket, and reports survival. @param Fortress $fortress Defending fortress. @return bool Whether the rocket survives. */
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

    /** Logs a cluster attack and resolves five finger rockets; uses both fortress objects and handler combat state. @param Fortress $attacker Attacking fortress. @param Fortress $defender Defending fortress. @return void */
    public function handleCluster($attacker, $defender){
        $this->addToGameLog($attacker->getName() . " attacks " . $defender->getName() . " with a cluster rocket!");
        $clusterCount = 5;
        $this->addToGameLog("The cluster rocket splits into " . $clusterCount . " finger rockets!");
        
        $this->storeGameLog();
        while($clusterCount > 0 && $this->gameStatus === 'active'){
            $temp = new fingerRocket();
            $temp->id = uniqid();
            $this->handleCombat($temp);
            $clusterCount --;
        }

    }

    /** Compares random attack and defense rolls using `$toHit`, `$hitRes`, `$cladding`, and `$this->hitBonus`; returns whether the attack hits. @param int $toHit Attack bonus. @param int $hitRes Hit resistance. @param int $cladding Cladding defense. @return bool Whether the attack hits. */
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

    /** Applies rocket damage, affinity modifiers, score awards, and log entries using the current players and `$rocket`; returns no value. @param mixed $attacker Attacking fortress argument (current player fortress is used). @param mixed $defender Defending fortress argument (opponent fortress is used). @param mixed $rocket Rocket and its combat values. @return void */
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

    /** Delegates debris generation for `$type` and optional `$material` to `$this->dh`; returns generated debris. @param int $type Debris category. @param mixed $material Optional material filter. @return mixed Generated debris. */
    public function handleDebris($type, $material = NULL){
        $debris = $this->dh->handleDebris($type, $material);
        return $debris;
    }//End function handleDebris

    /** Extracts the material name from `$fortress` cladding text; returns the final word. @param Fortress $fortress Fortress to inspect. @return string Material name. */
    public function getMaterial($fortress){
        $temp = $fortress->convertCladdingToString();
        $pieces = explode(" ", $temp);
        $material = array_pop($pieces);
        return $material;
    }

    /** Adds one recovery message per debris item to the game log; uses item names and fortress name. @param array $debris Recovered debris objects. @param Fortress $fortress Recovering fortress. @return void */
    public function logDebris($debris, $fortress){
        $name = $fortress->getName();
        $debrisName;
        foreach($debris as $k => $v){
            $debrisName = $v->getName();
            $this->addToGameLog("The " . $name . " recovers a " . $debrisName . ".");
        }
    }

    /** Checks whether `$material` is strong or weak against `$rocketType` in `$this->affinityRules`; returns true, false, or null. @param int|string $rocketType Rocket type ID. @param int|string $material Material ID. @return bool|null Affinity result, or null when neutral. */
    public function handleAffinity($rocketType, $material){
        $rules = $this->affinityRules[(string)$rocketType] ?? array();
        if(isset($rules['strong']) && $material == $rules['strong']){
            return true;
        }
        if(isset($rules['weak']) && $material == $rules['weak']){
            return false;
        }
        return NULL;
    }

    /** Resolves an attack, turn transition, score, debris, winner, and log updates using `$rocket` and current handler state. @param mixed $rocket Rocket to resolve. @return bool|null False for rejected input; otherwise null after resolution. */
    public function handleCombat($rocket){
        //echo $this->getId() . " handling combat<br/>";
        /* Ensure lurking POST vars don't trigger unwanted log entries */
        if ($rocket === NULL || $this->gameStatus !== 'active') {
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
            if($this->gameStatus === 'active'){
                $this->playerUp = $this->opponent->getId();
                $this->storeTurn($this->playerUp);
            }
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
        $this->player->fortress->store();
        $this->opponent->fortress->store();
        $this->playerUp = $this->opponent->getId();
        $this->storeTurn($this->playerUp);
        $this->declareWinnerIfTargetReached($this->player->getId());
        $this->storeGameLog();

    }//End function handleCombat

    /** Ends the game when either fortress reaches `$this->targetScore`, persists the winner, and returns its ID or null. @param string $attackingPlayerId Attacker ID used to break a tied score. @return string|null Winner ID, or null while no winner exists. */
    public function declareWinnerIfTargetReached($attackingPlayerId){
        if($this->gameStatus !== 'active' || $this->player === NULL || $this->opponent === NULL){
            return NULL;
        }

        $playerPoints = $this->player->fortress->getPoints();
        $opponentPoints = $this->opponent->fortress->getPoints();
        $playerReachedTarget = $playerPoints >= $this->targetScore;
        $opponentReachedTarget = $opponentPoints >= $this->targetScore;
        if(!$playerReachedTarget && !$opponentReachedTarget){
            return NULL;
        }

        if($playerReachedTarget && $opponentReachedTarget){
            if($playerPoints === $opponentPoints){
                $this->winnerId = $attackingPlayerId;
            } else {
                $this->winnerId = $playerPoints > $opponentPoints ? $this->player->getId() : $this->opponent->getId();
            }
        } else {
            $this->winnerId = $playerReachedTarget ? $this->player->getId() : $this->opponent->getId();
        }

        $this->gameStatus = 'won';
        $winnerName = $this->winnerId === $this->player->getId()
            ? $this->player->fortress->getName()
            : $this->opponent->fortress->getName();
        $this->addToGameLog($winnerName . ' reached the target score of ' . $this->targetScore . ' and wins!');

        $statement = $this->mysqli->prepare("UPDATE gamehandler SET winnerId = ?, gameStatus = 'won' WHERE id = ? AND gameStatus = 'active'");
        $statement->bind_param('ss', $this->winnerId, $this->id);
        $statement->execute();
        $statement->close();
        return $this->winnerId;
    }

    // Pass in player id, get f1 & f2 assignments for player & opponent
    /** Maps `$id` to player/opponent fortress slots using `$this->f1` and `$this->f2`; returns both slot labels. @param string $id Player ID. @return array Slot labels or null values. */
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
    /** Packages handler, players, fortresses, turn, score, status, and selected log entries as JSON. @param bool $log Whether to package the full log instead of the buffer. @return string Encoded game state. */
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
        $array['playerUp'] = $this->getTurnPlayerId();
        $array['targetScore'] = $this->targetScore;
        $array['winnerId'] = $this->winnerId;
        $array['gameStatus'] = $this->gameStatus;
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
/** Fetches one player row by `$pId` using `$this->mysqli`; returns the associative database row. @param string $pId Player ID. @return array|null Player row, or null when absent. */
function selectPlayerData($pId){
    $sql = 'SELECT * FROM `players` WHERE id="' . $pId  .'"';
    $result = $this->mysqli->query($sql);
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
   
    return $row;
}//End function selectPlayerData

    /** Loads player `$pId` and its credentials/inventory into `$this->player`; returns no value. @param string $pId Player ID. @return void */
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
/** Loads handler row `$hId` into this object's game, player, fortress, and affinity state; returns no value. @param string $hId Handler ID. @return void */
function selectCombatHandler($hId){
    $sql = 'SELECT * FROM `gamehandler` WHERE id="' . $hId  .'"';
    $result = $this->mysqli->query($sql);
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
    $this->id = $row['id'];
    $this->playerUp = $row['playerUp'];
    $this->targetScore = (int)$row['targetScore'];
    $this->winnerId = $row['winnerId'];
    $this->gameStatus = $row['gameStatus'];
    $this->gameLog = json_decode($row['gameLog']);
    $this->affinityRules = !empty($row['affinities']) ? json_decode($row['affinities'], true) : array();
    $this->ensureAffinityRules();
    $this->p1 = $row['p1'];
    $this->f1 = $row['f1'];
    $this->p2 = $row['p2'];
    $this->f2 = $row['f2'];
    
}//End function selectCombatHandler

//Find combat handler by player ID
/** Fetches active game rows involving `$pId`, including fortress names and scores; returns the rows as an array. @param string $pId Player ID. @return array Matching game rows. */
public function findCombatHandlers($pId){
    $sql = 'SELECT gamehandler.*, f1.name AS f1Name, f1.points AS f1Points, f2.name AS f2Name, f2.points AS f2Points
            FROM `gamehandler`
            LEFT JOIN `fortress` AS f1 ON gamehandler.f1 = f1.id
            LEFT JOIN `fortress` AS f2 ON gamehandler.f2 = f2.id
            WHERE (gamehandler.p1 = ? OR gamehandler.p2 = ?) AND gamehandler.gameStatus <> \'ended\'';
    $statement = $this->mysqli->prepare($sql);
    $statement->bind_param('ss', $pId, $pId);
    $statement->execute();
    $result = $statement->get_result();
    $hArray = [];
    while ($row = $result->fetch_assoc()) {
        $hArray[] = $row;
    }
    $result->free();
    $statement->close();
    return $hArray;
    
}//End function findCombatHandlers

//Confirm a handler exists
/** Checks whether `$pId` belongs to a non-ended game; returns a boolean. @param string $pId Player ID. @return bool Whether a matching game exists. */
public function handlerExists($pId){
    $statement = $this->mysqli->prepare('SELECT 1 FROM `gamehandler` WHERE (p1 = ? OR p2 = ?) AND gameStatus <> \'ended\' LIMIT 1');
    $statement->bind_param('ss', $pId, $pId);
    $statement->execute();
    $exists = $statement->get_result()->num_rows > 0;
    $statement->close();
    return $exists;
}//End function handlerExists



    //Select friend by ID
    /** Loads the player identified by `$fId` into `$this->opponent`; returns no value. @param string $fId Opponent player ID. @return void */
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
/** Places `$fortress` in the first empty `$this->f1` or `$this->f2` slot; returns no value. @param mixed $fortress Fortress value to assign. @return void */
public function putFortressInEmptySlot($fortress){
    if($this->f1 == NULL){
        $this->f1 = $fortress;
    }elseif($this->f2 == NULL){
        $this->f2 = $fortress;
    }
}//End function checkSlots

//Check if all fortress slots are empty
/** Checks whether either fortress slot is empty using `$this->f1` and `$this->f2`; returns the result. @return bool Whether at least one slot is empty. */
public function allFortressSlotsEmpty(){
    $empty = false;
    if($this->f1 == NULL || $this->f2 == NULL){
        $empty = true;
    }
    return $empty;
}

/** Fetches the database row for fortress `$fId` using `$this->mysqli`; returns the row. @param string $fId Fortress ID. @return array|null Fortress row, or null when absent. */
function selectFortress($fId){
    $sql = 'SELECT * FROM `fortress` WHERE id="' . $fId  .'"';
    $result = $this->mysqli->query($sql);
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
    return $row;
}//End function selectFortress

//Find fortress in slots
/** Finds the fortress matching `$fId` in `$this->f1` or `$this->f2`; returns it or null. @param string $fId Fortress ID. @return mixed Matching fortress, or null. */
public function findFortressInSlots($fId){
    if($this->f1 !== NULL && $this->f1->getId() == $fId){
        return $this->f1;
    }elseif($this->f2 !== NULL && $this->f2->getId() == $fId){
        return $this->f2;
    }
    return NULL;
}//End function findFortressInSlots

//Slot fortress into empty slot
/** Assigns `$fortress` to `$this->checkSlots`; returns no value. @param mixed $fortress Fortress to assign. @return void */
public function slotFortress($fortress){
    $this->checkSlots = $fortress;
}//End function slotFortress




/** Creates, names, and stores a new Fortress object; uses a generated ID and database state. @return Fortress The new fortress. */
public function createFortress(){
        $temp = new Fortress();
        $tempId = uniqid();
        $temp->store();
        $temp->setRandomName();
        $random = $temp->getName();
        $this->storeFortressName($tempId, $random);
        
        return $temp;
}//End function createFortress

/** Builds a Fortress object from database row `$row`, including its armory; returns the object. @param array $row Fortress database row. @return Fortress Hydrated fortress. */
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
/** Creates missing fortresses or loads existing ones for `$playerId`; returns the player's assigned slot or null. @param string $playerId Player ID. @return string|null Player fortress slot, or null when newly assigned. */
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
/** Creates a new player object with default state; uses no arguments. @return player New player object. */
public function createPlayer(){
    $temp = new player();
    return $temp;
}//End function createPlayer

//Load DB data from sql row into existing handler player object
/** Copies player identity and account fields from `$row` into `$this->player`; returns no value. @param array $row Player database row. @return void */
public function loadPlayer($row){
    $this->player->id = $row['id'];
    $this->player->username = $row['username'];
    $this->player->pass = $row['pass'];
    $this->player->fId = $row['fId'];
}//End function loadPlayer

//Player and opponent are stored as ids in p1 and p2
//Fortress ids are always stored so P1's fortress is F1, etc.
/** Loads a game between `$pId` and `$oId`, hydrates both players and fortresses, and returns whether a matching game was found. @param string $pId Player ID. @param string $oId Opponent ID. @return bool Whether game state was loaded. */
public function findHandlerForBothPlayers($pId, $oId){
    //echo "find handlers sanity<br/>";
    $sql = 'SELECT * FROM `gamehandler` WHERE (p1="' . $pId . '" AND p2="' . $oId . '") OR (p1="' . $oId . '" AND p2="' . $pId . '")';
    $result = $this->mysqli->query($sql);
    if ($result === FALSE || $result->num_rows === 0) {
        return FALSE;
    }
    $row = $result->fetch_assoc(); 
    mysqli_free_result($result);
    $this->p1 = $row['p1'];
    $this->p2 = $row['p2'];
    $this->playerUp = $row['playerUp'];
    $this->targetScore = (int)$row['targetScore'];
    $this->winnerId = $row['winnerId'];
    $this->gameStatus = $row['gameStatus'];
    $this->affinityRules = !empty($row['affinities']) ? json_decode($row['affinities'], true) : array();
    $this->ensureAffinityRules();
    $this->selectFriend($oId);
    $this->selectPlayer($pId);
    $playerFortressId = $row['p1'] === $pId ? $row['f1'] : $row['f2'];
    $opponentFortressId = $row['p1'] === $pId ? $row['f2'] : $row['f1'];
    $tFRow = $this->selectFortress($playerFortressId);
    $tempFortress = $this->setUpFortress($tFRow);
    $this->setF1($tempFortress);
    $this->player->setFortress($tempFortress);
    unset($tFRow);
    unset($tempFortress);
    $tFRow = $this->selectFortress($opponentFortressId);
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

/** Loads session-selected game state and checks that `$playerId` owns the active turn; defaults IDs from `$_SESSION`. @param string|null $playerId Optional player ID. @param string|null $handlerId Optional handler ID. @return bool Whether an active session game is available. */
public function findActiveSessionGame($playerId = NULL, $handlerId = NULL){
    $playerId = $playerId ?? ($_SESSION['playerId'] ?? NULL);
    $handlerId = $handlerId ?? ($_SESSION['handlerId'] ?? NULL);
    if($playerId === NULL || $handlerId === NULL || !$this->isPlayerTurn($playerId, $handlerId)){
        return false;
    }
    $gameState = json_decode($this->loadGameFromOptions($playerId, $handlerId), true);
    return !isset($gameState['error']) && $this->gameStatus === 'active';
}


//Find fortress names in available handlers
/** Formats matching game rows for `$pId`, orienting fortress names and scores from that player's perspective. @param array $assoc Game rows with joined fortress details. @param string $pId Player ID. @return array Formatted game choices. */
public function packageGameChoices($assoc, $pId){
    $fArray = [];
    foreach($assoc as $val){
        $tArray = [];
        if($val['p1'] == $pId){
            $playerFortressName = $val['f1Name'];
            $playerFortressPoints = $val['f1Points'];
            $opponentFortressName = $val['f2Name'];
            $opponentFortressPoints = $val['f2Points'];
        }elseif($val['p2'] == $pId){
            $playerFortressName = $val['f2Name'];
            $playerFortressPoints = $val['f2Points'];
            $opponentFortressName = $val['f1Name'];
            $opponentFortressPoints = $val['f1Points'];
        }else{
            continue;
        }
        if($playerFortressName === NULL || $opponentFortressName === NULL){
            continue;
        }
        $tArray['handlerId'] = $val['id'];
        $tArray['targetScore'] = (int)$val['targetScore'];
        $tArray['winnerId'] = $val['winnerId'];
        $tArray['gameStatus'] = $val['gameStatus'];
        $tArray['playerFortressName'] = $playerFortressName;
        $tArray['playerFortressPoints'] = $playerFortressPoints;
        $tArray['opponentFortressName'] = $opponentFortressName;
        $tArray['opponentFortressPoints'] = $opponentFortressPoints;
        $fArray[] = $tArray;
    }
    return $fArray;
}//End function packageGameChoices

/** Loads game `$hId` for player `$pId` and packages it as JSON, or returns a JSON error. @param string $pId Player ID. @param string $hId Handler ID. @return string JSON game state or error. */
public function loadGameFromOptions($pId, $hId){ 
    $this->selectCombatHandler($hId);
    if($pId !== $this->p1 && $pId !== $this->p2){
        return json_encode(array('error' => 'You are not a player in this game.'));
    }
    $this->selectPlayer($pId);
    $playerIsFirst = $this->p1 == $pId;
    $friendId = $playerIsFirst ? $this->p2 : $this->p1;
    $playerFortressId = $playerIsFirst ? $this->f1 : $this->f2;
    $opponentFortressId = $playerIsFirst ? $this->f2 : $this->f1;
    $this->selectFriend($friendId);

    $statement = $this->mysqli->prepare('SELECT * FROM `fortress` WHERE id IN (?, ?)');
    $statement->bind_param('ss', $playerFortressId, $opponentFortressId);
    $statement->execute();
    $result = $statement->get_result();
    $fortressRows = array();
    while($row = $result->fetch_assoc()){
        $fortressRows[$row['id']] = $row;
    }
    $result->free();
    $statement->close();

    if(!isset($fortressRows[$playerFortressId], $fortressRows[$opponentFortressId])){
        return json_encode(array('error' => 'Game fortress data is unavailable.'));
    }
    $this->player->setFortress($this->setUpFortress($fortressRows[$playerFortressId]));
    $this->opponent->setFortress($this->setUpFortress($fortressRows[$opponentFortressId]));
    $_SESSION['playerId'] = $this->player->getId();
    $_SESSION['friendId'] = $this->opponent->getId();
    return $this->package();
}//End function loadGameFromOptions

//Check if player exists. Return Bool and if yes game choices
/** Authenticates `$username` and `$pass`, sets the player session, and returns player/game choices as JSON. @param string $username Login name. @param string $pass Password. @return string JSON authentication and game-choice data. */
function authPlayer($username, $pass){
        $tArray['playerExists'] = FALSE;
        $tArray['handlerExists'] = FALSE;

        /* Get matching player if any */
        $statement = $this->mysqli->prepare('SELECT id FROM `players` WHERE username = ? AND pass = ? LIMIT 1');
        $statement->bind_param('ss', $username, $pass);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        $result->free();
        $statement->close();

        if($row !== NULL && !empty($row)){
            $tArray['playerExists'] = TRUE;
            $_SESSION['playerId'] = $row['id'];
            $tArray['playerId'] = $row['id'];
            $handlers = $this->findCombatHandlers($row['id']);
            if(!empty($handlers)){
                $tArray['handlerExists'] = true;
                $tArray['fortresses'] = $this->packageGameChoices($handlers, $row['id']);
            }
        }

        return json_encode($tArray);
        
    } //End function authPlayer

/** Restores the player session from `$_SESSION`, verifies the player, and returns available game choices as JSON. @return string JSON login-restoration data. */
function restorePlayerLogin(){
        $tArray['playerExists'] = FALSE;
        $tArray['handlerExists'] = FALSE;
        $playerId = $_SESSION['playerId'] ?? NULL;
        if($playerId === NULL){
            return json_encode($tArray);
        }

        $statement = $this->mysqli->prepare('SELECT id FROM `players` WHERE id = ? LIMIT 1');
        $statement->bind_param('s', $playerId);
        $statement->execute();
        $row = $statement->get_result()->fetch_assoc();
        $statement->close();
        if($row === NULL){
            unset($_SESSION['playerId']);
            return json_encode($tArray);
        }

        $tArray['playerExists'] = TRUE;
        $tArray['playerId'] = $playerId;
        $handlers = $this->findCombatHandlers($playerId);
        if(!empty($handlers)){
            $tArray['handlerExists'] = TRUE;
            $tArray['fortresses'] = $this->packageGameChoices($handlers, $playerId);
        }
        return json_encode($tArray);
    } //End function restorePlayerLogin


    /** Checks the players table for `$email` used as a username; returns whether it is unique. @param string $email Email/username to check. @return bool Whether the value is unused. */
    public function isEmailUnique($email){
        $unique = true;
        $sql = 'SELECT * FROM `players` WHERE username="' . $email .'"';
        $result = $this->mysqli->query($sql);
        if(mysqli_num_rows($result) > 0){
            $unique = false;
        }
        return $unique;
    }

    /** Validates `$email` with PHP's email filter; returns whether it is non-empty and valid. @param string $email Email address. @return bool Whether the email is valid. */
    public function validateEmail($email){
        $valid = false;
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
            $valid = true;
        }
        return $valid;
    }

    /** Checks whether `$pass` is longer than seven characters; returns the result. @param string $pass Password to check. @return bool Whether the password meets the length rule. */
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

if(isset($_POST['logout'])){
    $_SESSION = array();
    if(ini_get('session.use_cookies')){
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', array(
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => $cookie['samesite'] ?? 'Lax'
        ));
    }
    session_destroy();
    header('Content-Type: application/json');
    echo json_encode(array('success' => true));
    exit;
}

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

if(isset($_POST['refresh_playerId']) && isset($_POST['refresh_handlerId'])){
    echo $ch->loadGameFromOptions($_POST['refresh_playerId'], $_POST['refresh_handlerId']);
    $_SESSION['playerId'] = $_POST['refresh_playerId'];
    $_SESSION['handlerId'] = $_POST['refresh_handlerId'];
    unset($_POST['refresh_playerId']);
    unset($_POST['refresh_handlerId']);
}//End game state refresh

if(isset($_POST['craft_blueprint_id'])){
    $actionPlayerId = $_POST['action_playerId'] ?? NULL;
    $actionHandlerId = $_POST['action_handlerId'] ?? NULL;
    if(!$ch->findActiveSessionGame($actionPlayerId, $actionHandlerId)){
        echo json_encode(array('error' => 'It is not your turn, or this game is no longer active.'));
    } else {
        $craft = $ch->player->craftBlueprint($_POST['craft_blueprint_id']);
        if(!empty($craft['success'])){
            $ch->storeTurn($ch->opponent->getId());
        }
        $response = json_decode($ch->package(), true);
        $response['craft'] = $craft;
        echo json_encode($response);
    }
    unset($_POST['craft_blueprint_id']);
}//End blueprint crafting



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
if(isset($_POST['restore_login'])){
    echo $ch->restorePlayerLogin();
    unset($_POST['restore_login']);
}

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
    $targetScore = filter_var($_POST['new_game_targetScore'] ?? 1000, FILTER_VALIDATE_INT);
    if($targetScore === false || $targetScore < 1 || $targetScore > 1000000000){
        $newGame['success'] = false;
        $newGame['error'] = 'Choose a target score between 1 and 1,000,000,000.';
    } else {
        $ch->targetScore = $targetScore;
        $newGame['success'] = $ch->store();
        $newGame['targetScore'] = $targetScore;
    }
    echo json_encode($newGame);
}

if(isset($_POST['new_target_score']) && isset($_POST['handlerId'])){
    $playerId = $_SESSION['playerId'] ?? NULL;
    $loadedGame = $playerId !== NULL
        ? json_decode($ch->loadGameFromOptions($playerId, $_POST['handlerId']), true)
        : array('error' => 'Your session has expired. Log in again.');
    $newTargetScore = filter_var($_POST['new_target_score'], FILTER_VALIDATE_INT);
    if(isset($loadedGame['error'])){
        echo json_encode($loadedGame);
    } elseif($ch->gameStatus !== 'won'){
        echo json_encode(array('error' => 'Only a completed game can continue with a new target.'));
    } elseif($newTargetScore === false || $newTargetScore <= max($ch->player->fortress->getPoints(), $ch->opponent->fortress->getPoints()) || $newTargetScore > 1000000000){
        echo json_encode(array('error' => 'The next target must be higher than both current scores and no more than 1,000,000,000.'));
    } else {
        $ch->targetScore = $newTargetScore;
        $ch->winnerId = NULL;
        $ch->gameStatus = 'active';
        $ch->addToGameLog('The players set a new target score of ' . $newTargetScore . '.');
        $statement = $ch->mysqli->prepare("UPDATE gamehandler SET targetScore = ?, winnerId = NULL, gameStatus = 'active' WHERE id = ? AND gameStatus = 'won'");
        $statement->bind_param('is', $newTargetScore, $ch->id);
        $statement->execute();
        $statement->close();
        $ch->storeGameLog();
        echo $ch->package();
    }
    unset($_POST['new_target_score'], $_POST['handlerId']);
}

if(isset($_POST['end_game']) && isset($_POST['handlerId'])){
    $playerId = $_SESSION['playerId'] ?? NULL;
    $loadedGame = $playerId !== NULL
        ? json_decode($ch->loadGameFromOptions($playerId, $_POST['handlerId']), true)
        : array('error' => 'Your session has expired. Log in again.');
    if(isset($loadedGame['error'])){
        echo json_encode($loadedGame);
    } elseif($ch->gameStatus !== 'won'){
        echo json_encode(array('error' => 'Only a completed game can be ended.'));
    } else {
        $ch->gameStatus = 'ended';
        $ch->addToGameLog('The players ended the game.');
        $statement = $ch->mysqli->prepare("UPDATE gamehandler SET gameStatus = 'ended' WHERE id = ? AND gameStatus = 'won'");
        $statement->bind_param('s', $ch->id);
        $statement->execute();
        $statement->close();
        $ch->storeGameLog();
        echo $ch->package();
    }
    unset($_POST['end_game'], $_POST['handlerId']);
}

//Attack!
//$_POST['player_rockets'] = "12345";
//$_SESSION['playerId'] = "681f762053cb6";
//$_SESSION['friendId'] = "681f762053cb5";
 if(isset($_POST['player_rockets'])){
    $playerId = $_POST['attack_pId'] ?? NULL;
    $handlerId = $_POST['attack_hId'] ?? NULL;
    if ($playerId === NULL || $handlerId === NULL || !$ch->isPlayerTurn($playerId, $handlerId)) {
        echo json_encode(['error' => 'It is not your turn. Wait for the other player to attack.']);
    } else {
        $gameState = json_decode($ch->loadGameFromOptions($playerId, $handlerId), true);
        if(isset($gameState['error'])){
            echo json_encode($gameState);
        } else {
            $rocket = $ch->player->fortress->getRocket($_POST['player_rockets']);
            if ($rocket === NULL) {
                echo json_encode(['error' => 'That rocket is no longer available. Refresh the game state and choose another rocket.']);
            } else {
                $ch->handleCombat($rocket);
                echo $ch->package(false);
            }
        }
    }
    unset($_POST['player_rockets']);
 };

//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_gunShop'] = 4;
//$_POST['player_quan'] = 2;
if(isset($_POST['player_gunShop'])){
    $actionPlayerId = $_POST['action_playerId'] ?? NULL;
    $actionHandlerId = $_POST['action_handlerId'] ?? NULL;
    if(!$ch->findActiveSessionGame($actionPlayerId, $actionHandlerId)){
        echo json_encode(array('error' => 'It is not your turn, or this game is no longer active.'));
    } else {
        $ch->player->fortress->addToArmory($_POST['player_gunShop'], $_POST['player_quan']);
        $ch->storeTurn($ch->opponent->getId());
        echo $ch->package(false);
    }

    
 };

//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_claddingShop'] = 4;
 if(isset($_POST['player_claddingShop'])){
    $actionPlayerId = $_POST['action_playerId'] ?? NULL;
    $actionHandlerId = $_POST['action_handlerId'] ?? NULL;
    if(!$ch->findActiveSessionGame($actionPlayerId, $actionHandlerId)){
        echo json_encode(array('error' => 'It is not your turn, or this game is no longer active.'));
    } else {
        $ch->player->fortress->updateCladding($_POST['player_claddingShop']);
        $ch->addToGameLog($ch->player->fortress->getName() . " is upgrading it's cladding to " . $ch->player->fortress->convertCladdingToString());
        $ch->storeTurn($ch->opponent->getId());
        $ch->storeGameLog();
        echo $ch->package();
    }
    unset($_POST['player_claddingShop']);
 };

//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_flakShop'] = 4;
 if(isset($_POST['player_flakShop'])){
    $actionPlayerId = $_POST['action_playerId'] ?? NULL;
    $actionHandlerId = $_POST['action_handlerId'] ?? NULL;
    if(!$ch->findActiveSessionGame($actionPlayerId, $actionHandlerId)){
        echo json_encode(array('error' => 'It is not your turn, or this game is no longer active.'));
    } else {
        $ch->player->fortress->addFlak($_POST['player_flakShop']);
        $ch->storeTurn($ch->opponent->getId());
        echo $ch->package(false);
    }
    unset($_POST['player_flakShop']);
 };


//$_SESSION['playerId'] = "681f762053cb6";
//$_POST['friendId'] = "681f762053cb5";
//$_POST['player_nameChange'] = "Remorseless Bastion of Negativity";
 if(isset($_POST['player_nameChange'])){
    if(!$ch->findActiveSessionGame()){
        echo json_encode(array('error' => 'This game is no longer active.'));
    } else {
        $id = $ch->player->fortress->getId();
        $ch->storeFortressName($id, $_POST['player_nameChange']);
        echo $ch->package(false);
    }
 }




?>