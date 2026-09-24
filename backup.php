<?php
        error_reporting(0);
        mysqli_report(MYSQLI_REPORT_OFF);
        $mysqli = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
        if ($mysqli->connect_errno) {
            throw new RuntimeException('mysqli connection error: ' . $mysqli->connect_error);
        }
        
        /* Set the desired charset after establishing a connection */
        $mysqli->set_charset('utf8mb4');
        if ($mysqli->errno) {
            throw new RuntimeException('mysqli error: ' . $mysqli->error);
        }

function console_log($output, $with_script_tags = true) {
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
    ');';
    if ($with_script_tags) {
    $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
    }


class FingerRocket
{
// Properties
    public $id;
    public $typeId = 0;
    public $name = "Finger Rocket";
    public $toHit = 20;
    public $criticalChance = 1;
    public $dieType = 4;

    // Methods

    public function __construct(array $arguments = array()) {
        print "constructing finger rocket";
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                
                $this->{$property} = $argument;
            }
        }
        
    }
}




class Dart extends FingerRocket{}
class Flechette extends FingerRocket{}
class Bolt extends FingerRocket{};
class ICYMI extends FingerRocket{};
class ICBM extends FingerRocket{};
class TCB extends FingerRocket{};
class CanOfWhoopAss extends FingerRocket{};

class Fortress
{
    // Properties
    public $armory = [];
    public $flak = 2;
    public $damageResistance = 5;
    public $hitResistance = 5;
    public $points = 150;
    public $id = "";
    public $cladding = 25;
    // Keep track of undamaged cladding to determine if downgraded //
    public $storedCladding = 25;
    public $combatHandler;
    public $name = "";
    public $damage = "undamaged";
    public $jsonArmory = '[
    {
        "name": "Finger Rocket",
        "id": "",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4"
    }, 
    {
        "name": "Finger Rocket",
        "id": "",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4"
    },
    {
        "name": "Finger Rocket",
        "id": "",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4"
    },
    {
        "name": "Finger Rocket",
        "id": "",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4"
    },
    {
        "name": "Finger Rocket",
        "id": "",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4"
    }
        ]';

    // Methods

    public function __construct(array $arguments = array()) {
        
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                
                $this->{$property} = $argument;
            }
        }
        
    }
    public function getJsonArmory(){
        return $this->jsonArmory;
    }

    public function setJsonArmory($jsonArmory){
        $this->jsonArmory = $jsonArmory;
    }

    public function getFlak(){
        return $this->flak;
    }

    public function setFlak($flak){
        $this->flak = $flak;
    }

    public function getArmory(){
        return $this->armory;
    }

    public function setArmory($armory){
        $this->armory = $armory;
    }

    public function getDamageResistance(){
        return $this->damageResistance;
    }
    public function setDamageResistance($damageResistance){
        $this->damageResistance = $damageResistance;
    }

    public function getHitResistance(){
        return $this->hitResistance;
    }
    public function setHitResistance($hitResistance){
        $this->hitResistance = $hitResistance;
    }

    public function getPoints(){
        return $this->points;
    }
    public function setPoints($points){
        $this->points = $points;
    }

    public function getId(){
        return $this->id;
    }
    public function setId($id){
        $this->id = $id;
    }

    public function getCladding(){
        return $this->cladding;
    }
    public function setCladding($cladding){
        $this->cladding = $cladding;
    }

    public function getStoredCladding(){
        return $this->storedCladding;
    }
    public function setStoredCladding($storedCladding){
        $this->storedCladding = $storedCladding;
    }

    public function getName(){
        return $this->name;
    }
    public function setName($name){
        
        $this->name = $name;
    }

    public function getDamage(){
        return $this->damage;
    }
    public function setDamage($damage){
        $this->damage = $damage;
    }

    public function setCombatHandler($handler){
        $this->combatHandler = $handler;
    }

    public function convertCladdingToString($cladding){
        
        if($cladding < 10){
            print "Wood";
        } elseif($cladding > 10 &&  $cladding <= 20){
            print "Vanadium";
        } elseif($cladding > 20 && $cladding <= 30){
            print "Iron";
        } elseif($cladding >30 && $cladding <= 40){
            print "Titanium";
        } elseif($cladding > 40 && $cladding <= 50){
            print "Chromium";
        } elseif($cladding > 50 && $cladding <= 60){
            print "Steel";
        } elseif($cladding > 60 && $cladding <= 70){
            print "Tungsten";
        } elseif($cladding > 70 && $cladding <= 80){
            print "Admantium";
        } elseif($cladding < 0){
            $this->cladding = 0;
            print "Wood";
        };

    }

    public function applyDamage($damage){
        $tempDamage = $this->cladding - $damage;
        $this->cladding = $tempDamage;
    }

    public function addPoints($points){
        $tempPoints = $this->points + $points;
        $this->points = $tempPoints;
    }

    public function removePoints($points){
        $tempPoints = $this->points - $points;
        $this->points = $tempPoints;
    }

    public function displayRockets(){
        foreach($this->armory as $key => $val){
            print '<option value="' . $val->id . '">' . $val->name . '</option>';
        }

    }

    

  
    public function stockArmory($armoryJson){
        $armoryArray = json_decode($armoryJson, true);
        $count = count($armoryArray);
        
        print "Count: " . $count;
        for($i = 0; $i < $count; $i++){
           
           $temp = new FingerRocket($armoryArray[$i]);
           $this->armory[] = $temp;
        }
    }


   public function getRocket($rocketId){
        $count = count($this->armory);
        foreach($this->armory as $key => $val){
            if($val->id == $rocketId){
                return $val;
                /*unset($this->armory[$key]);*/
            }
        }
    }

    public function attack($rocketId){
        
        $rocket = $this->getRocket($rocketId);
        $combatHandler->handleCombat($this, $rocket);
    }

    public function addFingerRocket($armoryArray){
        $myFingerRocket = new FingerRocket($armoryArray);
        $myFingerRocket->id = uniqid();
        $this->armory[] = $myFingerRocket;
    }

    public function addDart(){
        $myDart = new Dart();
        $myDart->typeId = 1;
        $myDart->name = "Dart";
        $myDart->criticalChance = 2;
        $myDart->dieType = 5;
        $myDart->id = uniqid();
        $this->armory[] = $myDart;
    }

    public function addFlechette(){
        $myFlechette = new Dart();
        $myFlechette->typeId = 2;
        $myFlechette->name = "Flechette";
        $myFlechette->criticalChance = 2;
        $myFlechette->dieType = 6;
        $myFlechette->id = uniqid();
        $this->armory[] = $myFlechette;
    }

    public function addBolt(){
        $myBolt = new Bolt();
        $myBolt->typeId = 3;
        $myBolt->name = "Dart";
        $myBolt->toHit = 30;
        $myBolt->criticalChance = 2;
        $myBolt->dieType = 7;
        $myBolt->id = uniqid();
        $this->armory[] = $myBolt;
    }

    public function addICYMI(){
        $myICYMI = new ICYMI();
        $myICYMI->typeId = 4;
        $myICYMI->name = "ICYMI";
        $myICYMI->toHit = 30;
        $myICYMI->criticalChance = 5;
        $myICYMI->dieType = 8;
        $myICYMI->id = uniqid();
        $this->armory[] = $myICYMI;
    }

    public function addICBM(){
        $myICBM = new ICBM();
        $myICBM->typeId = 5;
        $myICBM->name = "ICBM";
        $myICBM->toHit = 30;
        $myICBM->criticalChance = 5;
        $myICBM->dieType = 9;
        $myICBM->id = uniqid();
        $this->armory[] = $myICBM;
    }

    public function addTCB(){
        $myTCB = new TCB();
        $myTCB->typeId = 6;
        $myTCB->name = "TCB";
        $myTCB->toHit = 40;
        $myTCB->criticalChance = 8;
        $myTCB->dieType = 12;
        $myTCB->id = uniqid();
        $this->armory[] = $myTCB;
    }

    public function addCanOfWhoopAss(){
        $myCOWA = new CanOfWhoopAss();
        $myCOWA->typeId = 7;
        $myCOWA->name = "Can of Whoop Ass";
        $myCOWA->toHit = 50;
        $myCOWA->criticalChance = 10;
        $myCOWA->dieType = 20;
        $myCOWA->id = uniqid();
        $this->armory[] = $myCOWA;
    }

    public function updateCladding($type){
        
        $tempCost = 0;
        switch($type){
            case 1:
                $tempCost = 20;
                if($this->points >= $tempCost){
                    $this->cladding = 20;
                    $this->storedCladding = 20;
                    $this->removePoints($tempCost);
                }
                    break;

            case 2:
                $tempCost = 30;
                if($this->points >= $tempCost){
                    $this->cladding = 30;
                    $this->storedCladding = 30;
                    $this->removePoints($tempCost);
                }
                    break;

            case 3:
                $tempCost = 40;
                
                if($this->points >= $tempCost){
                    $this->cladding = 40;
                    $this->storedCladding = 40;
                    $this->removePoints($tempCost);
                }
                    break;

            case 4:
                $tempCost = 50;
                if($this->points >= $tempCost){
                    $this->cladding = 50;
                    $this->storedCladding = 50;
                    $this->removePoints($tempCost);
                }
                    break;

            case 5:
                $tempCost = 60;
                if($this->points >= $tempCost){
                    $this->cladding = 60;
                    $this->storedCladding = 60;
                    $this->removePoints($tempCost);
                }
                    break;

            case 6:
                $tempCost = 70;
                if($this->points >= $tempCost){
                    $this->cladding = 70;
                    $this->storedCladding = 70;
                    $this->removePoints($tempCost);
                }
                    break;

            case 7:
                $tempCost = 70;
                if($this->points >= $tempCost){
                    $this->cladding = 70;
                    $this->storedCladding = 70;
                    $this->removePoints($tempCost);
                }
                    break;
                }
$this->combatHandler->storeFortress($this->id);



        }

  

    public function addToArmory($type){
        
        $tempCost = 0;
        switch($type){

            case 0:
                $tempCost = 1;
                if($this->points >= $tempCost){
                    $this->addFingerRocket();
                    $this->removePoints($tempCost);
                }
                break;

                case 1:
                    $tempCost = 3;
                    if($this->points >= $tempCost){
                        $this->addDart();
                        $this->removePoints($tempCost);
                    }
                    break;

                case 2:
                    $tempCost = 5;
                    if($this->points >= $tempCost){
                        $this->addFlechette();
                        $this->removePoints($tempCost);
                    }
                    break;

                case 3:
                    $tempCost = 7;
                    if($this->points >= $tempCost){
                        $this->addBolt();
                        $this->removePoints($tempCost);
                    }
                    break;

                case 4:
                    $tempCost = 9;
                    if($this->points >= $tempCost){
                        $this->addICYMI();
                        $this->removePoints($tempCost);
                    }
                    break;

                case 5:
                    $tempCost = 11;
                    if($this->points >= $tempCost){
                        $this->addICBM();
                        $this->removePoints($tempCost);
                    }
                    break;

                case 6:
                    $tempCost = 13;
                    if($this->points >= $tempCost){
                        $this->addTCB();
                        $this->removePoints($tempCost);
                    }
                    break;

                case 7:
                    $tempCost = 15;
                    if($this->points >= $tempCost){
                        $this->addCanOfWhoopAss();
                        $this->removePoints($tempCost);
                    }
                    break;

        }
        /*$combatHandler->storeFortress($this);*/

    }



}

class combatHandler
{
    // Properties
    public $id =1;
    public $basePoints = 5;
    public $players = [];
    public $f1;
    public $f2;
    public $gameLog = [];
    public $mysqli;
    


    // Methods

    public function __construct(){
        error_reporting(0);
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->mysqli = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
        if ($this->mysqli->connect_errno) {
            throw new RuntimeException('mysqli connection error: ' . $mysqli->connect_error);
        }
        
        /* Set the desired charset after establishing a connection */
        $this->mysqli->set_charset('utf8mb4');
        if ($this->mysqli->errno) {
            throw new RuntimeException('mysqli error: ' . $mysqli->error);
        }
    }

    private function rollDie($dieType){
        return rand(1, $dieType);
    }

    public function getDamage($dieType){
        return $this->rollDie($dieType);
    }

    public function storeGameLog(){
        $gameLog = json_encode($this->gameLog);
        
        $id = $this->id;
       
        $sql = "UPDATE gamehandler SET gameLog = ? WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("si", $gameLog, $id);
        $stmt->execute();
        
    }

    public function addToGameLog($string){
        $this->gameLog[] = $string;
        $this->storeGameLog();
    }

    public function displayGameLog(){
    
        $sql = "SELECT `gameLog` FROM `gamehandler` WHERE id = 1";
        
        $result = $this->mysqli->query($sql);
        
        
        while($row = $result->fetch_array(MYSQLI_ASSOC)){
            $logArray = json_decode($row['gameLog']);
            
            foreach($logArray as $key => $val){
                print "<p>" . $val . "</p>";
            }
        }
    }

    public function updateArmory($attacker){
        $id = $attacker->id;
        $armory = json_encode($attacker->armory);
        $sql = "UPDATE fortress SET armory = ? WHERE id = ?";
        $stmt = $this->mysqli->prepare($sql);
        
        $stmt->bind_param("ss", $armory, $id);
        $stmt->execute();
    }

    public function storeFortress($fid){
        
        $fortress;
        
        for($i = 0; $i < count($this->players); $i++){
            if($this->players[$i]->getId() == $fid){
                $fortress = $this->players[$i];
            }
        }

        $id = $fortress->getId();
        $name = $fortress->getName();
        
        $armory = json_encode($fortress->getArmory());
        
        $cladding = $fortress->getCladding();
        $damage = $fortress->getDamage();
        $damageResistance = $fortress->getDamageResistance();
        $flak = $fortress->getFlak();
        $hitResistance = $fortress->getHitResistance();
        $name = $fortress->getName();
        $points = $fortress->getPoints();
        $storedCladding = $fortress->getStoredCladding();
        
        
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $sql = "UPDATE `fortress` SET 
        `armory` = ?,
        `cladding` = ?,
        `damage` = ?,
        `damageResistance` = ?,
        `flak` = ?,
        `hitResistance` = ?,
        `name` = ?,
        `points` = ?,
        `storedCladding` = ?
        WHERE `id` = ?";
        
        $stmt = $this->mysqli->prepare($sql);
        
        
        $stmt->bind_param('ssssiiiiii', $id, $name, $armory, $damage, $cladding, $damageResistance, $flak, $hitResistance, $points, $storedCladding);
        
        
        $stmt->execute();
        
    }

    public function storeFortressName($fid){
        
        $fortress;
        for($i = 0; $i < count($this->players); $i++){
            if($this->players[$i]->getId() == $fid){
                $fortress = $this->players[$i];
            }
        }
        print "ready to build sql ";
        $id = $fortress->getId();
        print "id: " . $id;
        $name = $fortress->getName();
        print "name: " . $name;
        $sql = "UPDATE `fortress` SET 
        `name` = ?
        WHERE `id` = ?";
        $stmt = $this->mysqli->prepare($sql);
        print "statement prepared ";
        $stmt->bind_param('ss', $id, $name);
        print "parameters bound";
        
        $stmt->execute();
        
    }

    public function storeFortressHitRes($fortress){
        
        $id = $fortress->getId;
       
        
        $hitResistance = $fortress->getHitResistance;
        
        $sql = "UPDATE `fortress` SET 
        `hitResistance` = ?,
        WHERE `id` = ?";
        $stmt = $this->mysqli->prepare($sql);
        
        $stmt->bind_param('is', $hitResistance, $id);
       
        
        $stmt->execute();
    }

    public function handleCombat($attacker, $rocket){
        
        if($attacker->id == "f1"){
            $attacker = $this->f1;
            $defender = $this->f2;
        }elseif($attacker->id == "f2"){
            $attacker = $this->f2;
            $defender = $this->$f1;
        }
        
        $this->gameLog[] = $attacker->getName() . " attacks!";
        $toHit = $this->rollDie(20) + $defender->getHitResistance();
        
        if($toHit >= $rocket->toHit){
            $this->gameLog[] = "It's a hit!";
            $damage = $this->rollDie($rocket->dieType);
            $defender->applyDamage($damage);
            $attacker->addPoints($this->basePoints);
            $attacker->addPoints($damage);
        } elseif($toHit < $rocket->toHit){
            $this->gameLog[] = "It's a miss!";
        }
        $this->storeGameLog();
        

    }


}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="styles/fr.css" /> 
        <title>Finger Rocket Game</title>
        
    </head>
    <body>
    <h1>Finger Rocket Game</h1>
      <?php

        
        
        $combatHandler = new combatHandler();
        
       
        

        


        
        $sql = "SELECT * FROM fortress";
        $result = $mysqli->query($sql);
        
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
            
            $combatHandler->players[] = $temp;
            
        }
        
        $combatHandler->f1 = $combatHandler->players[0];
        $combatHandler->f2 = $combatHandler->players[1];
       
        $combatHandler->storeFortress($combatHandler->f1->getId());
        $combatHandler->f1->setCombatHandler($combatHandler);
        $combatHandler->f2->setCombatHandler($combatHandler);
       
        
        
        // Hamdle POST vars for Fortress One
        if(isset($_POST['f1_gunShop'])){
           
            $combatHandler->f1->addToArmory($_POST['f1_gunShop']);
            unset($_POST['f1_gunShop']);
        };

        if(isset($_POST['f1_claddingShop'])){
            $combatHandler->f1->updateCladding($_POST['f1_claddingShop']);
            unset($_POST['f1_claddingShop']);
        };

        if(isset($_POST['f1_rockets'])){
            
            $attacker = $combatHandler->f1;
            $rocket = $combatHandler->f1->getRocket($_POST['f1_rockets']);
            $combatHandler->handleCombat($attacker, $rocket);
            $combatHandler->updateArmory($attacker);
            
        }

        // Hamdle POST vars for Fortress Two
        if(isset($_POST['f2_gunShop'])){
            $combatHandler->f2->addToArmory($_POST['f2_gunShop']);
            unset($_POST['f2_gunShop']);
        };

        if(isset($_POST['f2_claddingShop'])){
            $combatHandler->f2->updateCladding($_POST['f2_claddingShop']);
            unset($_POST['f2_claddingShop']);
        };

        if(isset($_POST['f2_rockets'])){
            $attacker = $combatHandler->f2;
            $rocket = $combatHandler->f2->getRocket($_POST['f2_rockets']);
            $combatHandler->handleCombat($attacker, $rocket);
            $combatHandler->updateArmory($attacker);
            
        }

        

       
      ?>
     
<div class="container">
<div class="column">
            <div class="fortress" id = "f1">
                <h2 id="f1_heading"> <?php print $combatHandler->f1->name; ?></h2>
             
                <!--<img src="images/mountain_castle.jpg"/>-->
                <div class = "points">
                    <label for="f1_points">Points</label>
                    <input readonly="true" value="<?php print $combatHandler->f1->getPoints(); ?>" id="f1_points" />
                </div>

                <div class = "cladding">
                    <?php print " fortress one cladding: " . $combatHandler->f1->cladding; ?>
                    <label for="f1_cladding">Cladding material</label>
                    
                    <input readonly="true" id="f1_cladding" value = "<?php print $combatHandler->f1->convertCladdingToString($combatHandler->f1->cladding); ?>"/>
                </div>
                <div class="emplacement">
                <form action="index.php" method="post">
                    <label for="f1_rockets">Rockets</label>
                    <select id = "f1_rockets" name="f1_rockets">
                        <option>Fire a rocket</option>
                        <?php
                            $combatHandler->f1->displayRockets();
                        ?>
                    </select>
                    <button type="submit" class="attack" id="f1_attack">Attack!</button>
                </form>
                </div>
                <div id="f1_error"></div>
                <h3>Buy a Rocket</h3>
                
                <div class="gunshop">
                <form action="index.php" method="post">
                    <label for = "f1_gunShop">Choose a rocket to buy</label>
                    <select id = "f1_gunShop" name="f1_gunShop">
                        <option>Choose a rocket</option>
                        <option value="0">Finger Rocket (cost: 1 pt)</option>
                        <option value="1">Dart (cost: 3 pt)</option>
                        <option value="2">Fletchette (cost: 5 pt)</option>
                        <option value="3">Bolt (cost: 7 pt)</option>
                        <option value="4">ICYMI  (cost: 9 pt)</option>
                        <option value="5">ICBM (cost: 11 pt)</option>
                        <option value="6">TCB (cost: 13 pt)</option>
                        <option value="7">Can of Whoop Ass (cost: 15 pt)</option>
                    </select>
                    <button type="submit" id="f1_buy">Buy rocket!</button>
                </form>
                </div>

                <div class="cladding">
                <form action="index.php" method="post">
                    <label for = "f1_claddingShop">Choose cladding to buy</label>
                    <select id = "f1_claddingShop" name="f1_claddingShop">
                        <option>Choose cladding</option>
                        <option value="1">Vanadium (cost: 20pt)</option>
                        <option value="2">Iron (cost: 30pt)</option>
                        <option value="3">Titanium (cost: 40pt)</option>
                        <option value="4">Chromium (cost: 50pt)</option>
                        <option value="5">Steel (cost: 60pt)</option>
                        <option value="6">Tungsten (cost: 70pt)</option>
                        <option value="7">Admantium (cost: 80pt)</option>
                    </select>
                    <button type="submit" id="f1_buy_cladding">Buy cladding!</button>
                </form>
                </div>

            </div>
        </div>
  

</div>

    <div class="column">
            <div class="fortress" id = "f2">
                <h2 id="f2_heading"><?php print $combatHandler->f2->name; ?></h2>
            <!--<img src="images/amber_castle.jpg" /> -->
                <div class = "points">
                    <label for="f2_points">Points</label>
                    <input readonly="true" id="f2_points" value="<?php print $combatHandler->f2->points ?>" />
                </div>

                <div class = "cladding">
                    <label for="f2_cladding">Cladding material</label>
                    <input readonly="true" id="f2_cladding" value = "<?php print $combatHandler->f2->convertCladdingToString($fortressTwo->cladding); ?>"/>
                </div>

                <div class="emplacement">
                    <label for="f2_rockets">Rockets</label>
                    <select id = "f2_rockets" name="f2_rockets">
                        <option>Fire a rocket</option>
                        <?php
                            $combatHandler->f2->displayRockets();
                        ?>
                    </select>
                    <button type="submit" class = "attack" id="f2_attack">Attack!</button>
                </div>
                <div id="f2_error"></div>
                <h3>Buy a Rocket</h3>
                
                <div class="gunshop">
                    <form action="index.php" method="post">
                        <label for = "f2_gunShop">Choose a rocket to buy</label>
                        <select id = "f2_gunShop" name="f2_gunShop">
                            <option>Choose a rocket</option>
                            <option value="0">Finger Rocket (cost: 1 pt)</option>
                            <option value="1">Dart (cost: 3 pt)</option>
                            <option value="2">Fletchette (cost: 5 pt)</option>
                            <option value="3">Bolt (cost: 7 pt)</option>
                            <option value="4">ICYMI  (cost: 9 pt)</option>
                            <option value="5">ICBM (cost: 11 pt)</option>
                            <option value="6">TCB (cost: 13 pt)</option>
                            <option value="7">Can of Whoop Ass (cost: 15 pt)</option>
                        </select>
                        <button type="submit" id="f2_buy">Buy rocket!</button>
                    </form>
                </div>

                <div class="cladding">
                    <form action="index.php" method="post">
                        <label for = "f2_claddingShop">Choose cladding to buy</label>
                        <select id = "f2_claddingShop" name="f2_claddingShop">
                            <option>Choose cladding</option>
                            <option value="1">Vanadium (cost: 20pt)</option>
                            <option value="2">Iron (cost: 30pt)</option>
                            <option value="3">Titanium (cost: 40pt)</option>
                            <option value="4">Chromium (cost: 50pt)</option>
                            <option value="5">Steel (cost: 60pt)</option>
                            <option value="6">Tungsten (cost: 70pt)</option>
                            <option value="7">Admantium (cost: 80pt)</option>
                        </select>
                        <button type="submit" id="f2_buy_cladding">Buy cladding!</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <div class="column">
        <h2>Game Log</h2>
        <div id="gamelog">
        <?php
            $combatHandler->displayGameLog();
        ?>
        </div>
       
        <form action="index.php" method="post">
            <button type="submit">Save Game</button>
    </form>
    </div>
    </body>
    </html>