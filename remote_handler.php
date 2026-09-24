<?php

include 'rocket.php';
include 'fortress.php';

class combatHandler
{
    // Properties
    public $id =1;
    public $basePoints = 5;
    public $f1 = NULL;
    public $f2 = NULL;
    public $gameLog = [];
    public $mysqli;
    public $resistRoll = 10;
    public $playerUp = "f1";
    
    


    // Methods

    public function __construct(){
        
        
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        /*$this->mysqli = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');*/
        $this->mysqli = new mysqli('127.0.0.1:3308', 'fingerrocket', 'L@sirena23', 'studiobl_fingerrocket');
        $this->mysqli->set_charset('utf8mb4');
        

        $sql = "SELECT * FROM fortress";
        $result = $this->mysqli->query($sql);
        while($row = $result->fetch_array(MYSQLI_ASSOC)){
            $temp = new Fortress();
            $temp->setFlak($row['flak']);
            $temp->setDamageResistance($row['damageResistance']);
            $temp->setHitResistance($row['hitResistance']);
            $temp->setPoints($row['points']);
            $temp->setId($row['id']);
            $temp->setCladding($row['cladding']);
            $temp->setStoredCladding($row['storedCladding']);
            $temp->setName($row['name']);
            $temp->setDamage($row['damage']);
            $temp->stockArmory($row['armory']);
            
            $jString = $temp->convertArmoryToJson();
            $temp->setJsonArmory($jString);
            $temp->setCombatHandler($this);
            if(is_null($this->f1)){
                $this->f1 = $temp;
            } elseif(!is_null($this->f1)) {
                $this->f2 = $temp;
            }
        }
        
        $sql = "SELECT `gameLog` FROM `gamehandler` WHERE id = 1";
        $result = $this->mysqli->query($sql);
        while($row = $result->fetch_array(MYSQLI_ASSOC)){
            $logArray = json_decode($row['gameLog']);
            unset($this->gameLog);
            $this->gameLog = $logArray;
        } 
    }

    public function sanityCheck(){
        print "sanity check<br/>";
    }

    public function togglePlayer(){
        if($this->playerUp = 'f1'){
            $this->playerUp = 'f2';
        } else {
            $this->playerUp = 'f1';
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


    public function storeGameLog(){
        if(count($this->gameLog) > 100){
            $temp = array_slice($this->gameLog, -1, 50);
            unset($this->gameLog);
            $this->gameLog = $temp;
        }
        $gameLog = $this->convertLogToJson();
        $basePoints = 10;
        $id = $this->id;
        $sql = "UPDATE gamehandler SET gameLog = ?, basePoints = ? WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sis", $gameLog, $basePoints, $id);
        $stmt->execute();
    }


    public function getToHit(){
        return $this->toHit;
    }

    public function setToHit($toHIt){
        $this->toHit = $toHit;
    }

    public function addToGameLog($string){
        $this->gameLog[] = $string;
        
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
        if($id == "f1"){
            $this->f1->setName($name);
        } else { $this->f2->setName($name);
        }
        $sql = "UPDATE fortress SET 
        name=?
        WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $name, $id);
        $stmt->execute();
       
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

    

    public function storeFortress($id, $armory, $cladding, $storedCladding, $damageResistance, $points, $flak){
        $sql = "UPDATE fortress SET 
        armory=?,
        cladding=?,
        storedCladding=?,
        damageResistance=?,
        points=?,
        flak=?
        WHERE id=?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("siiiiis", $armory, $cladding, $storedCladding, $damageResistance, $points, $flak, $id);
       
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
            $this->handleCombat($attacker, $temp);
            $clusterCount --;
        }

    }

    public function handleCombat($attacker, $rocket){
        /* Ensure lurking POST vars don't trigger unwanted log entries */
        if(strlen($rocket->id) < 2){
            return false;
        }
        $this->togglePlayer();
        if($attacker->id == "f1"){
            $attacker = $this->f1;
            $defender = $this->f2;
        }elseif($attacker->id == "f2"){
            $attacker = $this->f2;
            $defender = $this->f1;
        }
        if($rocket->name == "Cluster Rocket"){
            $this->handleCluster($attacker, $defender);
            return;
        }
        $this->addToGameLog($attacker->getName() . " attacks " . $defender->getName() . " with a " . $rocket->name . "!");
        if($this->handleFlak($defender)){
            
            $cladding = $defender->getCladding();
            
            $roll = $this->rollDie(10);
            $hitRes = $roll + $defender->getHitResistance() + $defender->getCladding();
            $hitSave = $this->rollDie(10);
            $toHit = $hitSave + $rocket->toHit;
            $this->addToGameLog("Defender resistance D10 roll: " . $roll . " + damageRes: " . $defender->getHitResistance() . " + Cladding: " . $defender->getCladding() . " Total: " . $hitRes);
            $this->addToGameLog("Rocket toHit: " . $rocket->toHit . " + D10 roll: " . $hitSave . " Total: " . $toHit); 
            
            if($hitRes < $toHit){
               
                $this->addToGameLog("It's a hit!");
                
                $damageRoll = $this->rollDie($rocket->dieType);
                $damRes = $this->rollDie(100);
                if($damageRoll <= $defender->getDamageResistance() ){
                    $damageRoll = $damageRoll / 2;
                }
                $damage = $damageRoll * 10;
            $this->addToGameLog($defender->getName() . "'s " .  $defender->convertCladdingToString($defender->getCladding()) . " cladding takes " . $damage . " damage!");
            $changedCladding = $defender->getCladding();
                if($cladding != $changedCladding){
                    $this->addToGameLog($defender->getName() . "'s " . $defender->convertCladdingToString($cladding) . " cladding is degraded to " . $defender->convertCladdingToString($changedCladding));
                }
                $defender->applyDamage($damage);
                $points = $rocket->toHit - $damageRes;
                $points = $points + $this->basePoints;
                $attacker->addPoints($points);
                
                $this->addToGameLog($attacker->getName() . " is awarded " . $points . " points!");
            } elseif($hitRes >= $toHit){
              
                $this->addToGameLog("It's a miss!");
                $points = $damageRes - $rocket->toHit;
                
                $points = $points + $this->basePoints;
                $defender->addPoints($points);
                $this->addToGameLog($defender->getName() . " is awarded " . $points . " points!");
            }
            
        }   
            
        $this->storeGameLog();
        
        $this->storeFortress(
            $attacker->getId(),
            $attacker->getJsonArmory(),
            $attacker->getCladding(),
            $attacker->getStoredCladding(),
            $attacker->getDamageResistance(),
            $attacker->getPoints(),
            $attacker->getFlak()
        ); 
        $this->storeFortress(
            $defender->getId(),
            $defender->getJsonArmory(),
            $defender->getCladding(),
            $defender->getStoredCladding(),
            $defender->getDamageResistance(),
            $defender->getPoints(),
            $defender->getFlak()
        );
        

    }

    public function package(){
        $array = [];
        $array['f1'] = $this->f1->package();
        $array['f1_damRes'] = $this->f1->getDamageResistance();
        $array['f1_clad'] = $this->f1->getCladding();
        $array['f2'] = $this->f2->package();
        $array['playerUp'] = $this->playerUp;
        $array['log'] = array_reverse($this->gameLog);
        $array = json_encode($array);
        return $array;
    }

    
}

$combatHandler = new combatHandler();
if (isset($_POST['fr_test'])) {
    echo $combatHandler->package();
    unset($_POST['fr_test']);
} else {
    
}

// Handle POST vars for Fortress One
if(isset($_POST['f1_gunShop'])){
    $combatHandler->f1->addToArmory($_POST['f1_gunShop'], $_POST['f1_quan']);
    echo $combatHandler->package();
 };

 if(isset($_POST['f1_claddingShop'])){
    $combatHandler->f1->updateCladding($_POST['f1_claddingShop']);
    echo $combatHandler->package();
    unset($_POST['f1_claddingShop']);
 };

 if(isset($_POST['f1_flakShop'])){
    $combatHandler->f1->addFlak($_POST['f1_flakShop']);
    echo $combatHandler->package();
    unset($_POST['f1_flakShop']);
 };

 if(isset($_POST['f1_rockets'])){
    $attacker = $combatHandler->f1;
    $rocket = $combatHandler->f1->getRocket($_POST['f1_rockets']);
    $combatHandler->handleCombat($attacker, $rocket);
    echo $combatHandler->package();
    unset($_POST['f1_rockets']);
 };

 if(isset($_POST['f1_nameChange'])){
    $combatHandler->storeFortressName('f1', $_POST['f1_nameChange']);
    echo $combatHandler->package();
 }


 // Handle POST vars for Fortress Two
if(isset($_POST['f2_gunShop'])){
    $combatHandler->f2->addToArmory($_POST['f2_gunShop'], $_POST['f2_quan']);
    echo $combatHandler->package();
   
 };

 if(isset($_POST['f2_claddingShop'])){
    $combatHandler->f2->updateCladding($_POST['f2_claddingShop']);
    echo $combatHandler->package();
    unset($_POST['f2_claddingShop']);
 };

 if(isset($_POST['f2_flakShop'])){
    $combatHandler->f2->addFlak($_POST['f2_flakShop']);
    echo $combatHandler->package();
    unset($_POST['f2_flakShop']);
 };

 if(isset($_POST['f2_rockets'])){
    $attacker = $combatHandler->f2;
    $rocket = $combatHandler->f2->getRocket($_POST['f2_rockets']);
    $combatHandler->handleCombat($attacker, $rocket);
    echo $combatHandler->package();
    unset($_POST['f2_rockets']);
 };

 if(isset($_POST['f2_nameChange'])){
    $combatHandler->storeFortressName('f2', $_POST['f2_nameChange']);
    echo $combatHandler->package();
 }

?>