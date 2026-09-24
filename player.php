<?php

class player
{
//Properties
public $id;
public $hId;
public $username;
public $pass;
public $fId;
public $fortress;
public $itemArray = array (
  array(), //Rocket Debris
  array(), //Cladding Debris
  array(), //Blueprints
  array() //Modules
);

//Methods


public function processDebris($array){
    foreach($array as $k => $v){
        $this->addDebris($v);
    }
    $this->store();
}//End function processDebris

public function addDebris($debris){
    //Type determines which subarray debris is pushed to
    $subArray = $debris->getType();
    array_push($this->itemArray[$subArray], $debris);
}//End function addDebris



public function getDebrisById($id){
    $default = false;
    //Iterate through subarrays
    foreach($this->itemArray as $k => $v){
        //Iterate through items in each subarray
        foreach($v as $a -> $b){
            if($b->getId == $id){
                $debris = $b;
                array_splice($this->itemArray[$k], $a, 1);
                return $debris;
            }
        }
    }
    return $default;
}

public function getDebrisByTypes($type, $typeId){
    $default = false;
    foreach($this->itemArray[$type] as $k => $v){
        if($v->getTypeId == $typeId){
            $debris = $v;
            array_splice($this->itemArray[$type], $b, 1);
            return $debris;
        }
    }
    return $default;
}

public function removeDebrisByTypeId($typeId, $count = 1){
    $default = false;
    $items = array();
    foreach($this->itemArray as $k => $v){
        foreach($v as $a => $b){
            if($b->getTypeId == $typeId){
                $stock++;
                array_push($items, $b);
                array_splice($this->itemArray[$k], $b, 1);
                if(count($items) == $count){
                    return $items;
                }
            }
        }
    }
    
    return $default;
}

public function removeMaterialsByTypeId($typeId, $material, $count = 1){
    $default = false;
    $items = array();
    foreach($this->itemArray as $k => $v){
        foreach($v as $a => $b){
            if($b->getTypeId == $typeId && $b->getMaterial == $material){
                $stock++;
                array_push($items, $b);
                array_splice($this->itemArray[$k], $b, 1);
                if(count($items) == $count){
                    return $items;
                }
            }
        }
    }
    
    return $default;
}





public function getId(){
    return $this->id;
}

public function setId($id){
    $this->id = $id;
}

public function setUniqueId(){
    $this->id = uniqid();
}

public function gethId(){
    return $this->hId;
}

public function sethId($hId){
    $this->hId = $hId;
}

public function getUserName(){
    return $this->username;
}

public function setUserName($username){
    $this->username = $username;
}

public function getPass(){
    return $this->pass;
}

public function setPass($pass){
    $this->pass = $pass;
}

public function getfId(){
    return $this->fId;
}

public function setfId($fId){
    $this->fId = $fId;
}

public function getFortress(){
    return $this->fortress;
}

public function setFortress($fObject){
    $this->fortress = $fObject;
}


public function __construct(array $arguments = array()) {
        
    if (!empty($arguments)) {
        foreach ($arguments as $property => $argument) {
                
            $this->{$property} = $argument;
        }

    }
}

public function readBlueprint($bluePrintId){

}

public function hasItem($typeId, $count = 1){
    $success = false;
    $stock = 0;
    foreach($this->itemArray as $k => $v){
        foreach($v as $a => $b){
            if($b->getTypeId == $typeId){
                $stock++;
            }
        }
    }
    if($stock >= $count){
        $success = true;
    }
    return $success;
}

public function hasMaterials($typeId, $material, $count = 1){
    $success = false;
    $stock = 0;
    foreach($this->itemArray as $k => $v){
        foreach($v as $a => $b){
            if($b->getTypeId == $typeId && $b->getMaterial == $material){
                $stock++;
            }
        }
    }
    if($stock >= $count){
        $success = true;
    }
    return $success;
}

//Special create to handle cladding materials
public function construct($typeId, $material){
    switch($typeId){
        case 1003:
            $rivets = $this->hasMaterials(29, $material, 25);
            $plates = $this->hasMaterials(34, $material,  9);
            $struts = $this->hasMaterials(30, $material, 5);
            $braces = $this->hasMaterials(31, $material, 4);
            if($rivets && $plates && $struts && $braces){
                $this->removeMaterialsByTypeId(29, $material, 25);
                $this->removeMaterialsByTypeId(34, $material, 9);
                $this->removeMaterialsByTypeId(30, $material, 5);
                $this->removeMaterialsByTypeId(31, $material, 4);
                $temp = new Bulwark();
                array_push($this->itemArray[3], $temp);
            }
            break;

            case 1004:
            $rivets = $this->hasMaterials(29, $material, 25);
            $plates = $this->hasMaterials(34, $material, 8);
            $struts = $this->hasMaterials(30, $material, 6);
            $braces = $this->hasMaterials(31, $material, 4);
            if($rivets && $plates && $struts && $braces){
                $this->removeMaterialsByTypeId(29, $material, 25);
                $this->removeMaterialsByTypeId(34, $material, 8);
                $this->removeMaterialsByTypeId(30, $material, 6);
                $this->removeMaterialsByTypeId(31, $material, 4);
                $temp = new Buttress();
                array_push($this->itemArray[3], $temp);
            }
            break;

            case 1005:
            $rivets = $this->hasMaterials(29, $material, 12);
            $plates = $this->hasMaterials(34, $material, 4);
            $braces = $this->hasMaterials(31, $material, 6);
            if($rivets && $plates && $struts && $braces){
                $this->removeMaterialsByTypeId(29, $material, 12);
                $this->removeMaterialsByTypeId(34, $material, 4);
                $this->removeMaterialsByTypeId(31, $material, 6);
                $temp = new Rampart();
                array_push($this->itemArray[3], $temp);
            }
            break;

            case 1006:
            $bulwarks = $this->hasMaterials(1003, $material, 4);
            $buttresses = $this->hasMaterials(1004, $material, 2);
            if($rivets && $plates && $struts && $braces){
                $this->removeMaterialsByTypeId(1003, $material, 4);
                $this->removeMaterialsByTypeId(1004, $material, 2);
                $temp = new Bastion();
                array_push($this->itemArray[3], $temp);
            }
            break;

            case 1007:
                $bastions = $this->hasMaterials(1004, $material, 2);
                $bulwarks = $this->hasMaterials(1003, $material, 3);
                $ramparts = $this->hasMaterials(1005, $material, 6);
                if($rivets && $plates && $struts && $braces){
                    $this->removeMaterialsByTypeId(1004, $material, 2);
                    $this->removeMaterialsByTypeId(1003, $material, 3);
                    $this->removeMaterialsByTypeId(1005, $material, 6);
                    $temp = new Cladding();
                    array_push($this->itemArray[3], $temp);
                }
            break;




    }

}//End function construct
public function create($typeId){
    $success = false;
    switch($typeId){

        case 1000:
            $cone = $this->hasItem(10);
            $gSystem = $this->hasItem(11);
            $tube = $this->hasItem(13);
            if($cone && $gSystem && $tube){
                $this->removeDebrisByTypeId(10);
                $this->removeDebrisByTypeId(11);
                $this->removeDebrisByTypeId(13);
                $temp = new nCone();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 1001:
            $explosive = $this->hasItem(14);
            $hShield = $this->hasItem(15);
            $tube = $this->hasItem(13);
            if($explosive && $hShield && $tube){
                $this->removeDebrisByTypeId(14);
                $this->removeDebrisByTypeId(15);
                $this->removeDebrisByTypeId(13);
                $temp = new Payload();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 1002:
            $fins = $this->hasItem(12);
            $fTank = $this->hasItem(17);
            $nozzle = $this->hasItem(16);
            if($explosive && $heatShield && $tube){
                $this->removeDebrisByTypeId(12);
                $this->removeDebrisByTypeId(17);
                $this->removeDebrisByTypeId(16);
                $temp = new pModule();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 0:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $Falanges = $this->hasItem(18);
            if($nCone && $Payload && $pModule && $Falanges){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(18);
                $temp = new FingerRocket();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 1:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $bMat = $this->hasItem(19);
            if($nCone && $Payload && $pModule && $bMat){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(19);
                $temp = new Dart();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 2:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $Needle = $this->hasItem(20);
            if($nCone && $Payload && $pModule && $Needle){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(20);
                $temp = new Flechette();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 3:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $Latch = $this->hasItem(21);
            if($nCone && $Payload && $pModule && $Latch){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(21);
                $temp = new Bolt();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 4:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $Highlighter = $this->hasItem(22);
            if($nCone && $Payload && $pModule && $Highlighter){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(22);
                $temp = new ICYMI();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 5:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $Itinerary = $this->hasItem(23);
            $pBook = $this->hasItem(24);
            if($nCone && $Payload && $pModule && $Itinerary && $pBook){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(23);
                $this->removeDebrisByTypeId(24);
                $temp = new ICBM();
                array_push($this->itemArray[3], $temp);
            }
            break;
    
        case 6:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $Lightning = $this->hasItem(25);
            $Chain = $this->hasItem(26);
            if($nCone && $Payload && $pModule && $Lightning && $Chain){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(25);
                $this->removeDebrisByTypeId(26);
                $temp = new TCB();
                array_push($this->itemArray[3], $temp);
            }
            break;

        case 7:
            $nCone = $this->hasItem(1000);
            $Payload = $this->hasItem(1001);
            $pModule = $this->hasItem(1002);
            $jEdge = $this->hasItem(27);
            $Label = $this->hasItem(28);
            if($nCone && $Payload && $pModule && $jEdge && $Label){
                $this->removeDebrisByTypeId(1000);
                $this->removeDebrisByTypeId(1001);
                $this->removeDebrisByTypeId(1002);
                $this->removeDebrisByTypeId(27);
                $this->removeDebrisByTypeId(28);
                $temp = new CanOfWhoopAss();
                array_push($this->itemArray[3], $temp);
            }
            break;







    }
}

public function package(){
    $temp = [];
    $temp['id'] = $this->getId();
    $temp['username'] = $this->getUserName();
    $temp['pass'] = $this->getPass();
    $temp['items'] = json_encode($this->itemArray);
    return $temp;
}

public function store(){
    error_reporting(0);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $mysqli = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
        /*$this->mysqli = new mysqli('localhost:/tmp/mysql8.sock', 'fingerrocket', 'L@sirena23', 'studiobl_fingerrocket');*/
        
        if ($mysqli->connect_errno) {
            throw new RuntimeException('mysqli connection error: ' . $mysqli->connect_error);
        }
        /* Set the desired charset after establishing a connection */
        $mysqli->set_charset('utf8mb4');
        if ($mysqli->errno) {
            throw new RuntimeException('mysqli error: ' . $mysqli->error);
        }
        
        $id = $this->getId();
        $username = $this->getUserName();
        $pass = $this->getPass();
        $items = json_encode($this->itemArray);
        $sql = "UPDATE players SET 
        username=?,
        pass=?,
        items=?
        WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssss", $username, $pass, $items, $id);
        $stmt->execute();
        $stmt->close();
}//End function store

public function storeNew(){
    $success = false;
    error_reporting(0);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $mysqli = new mysqli('localhost', 'root', 'root', 'db_fingerrocket');
        /*$this->mysqli = new mysqli('localhost:/tmp/mysql8.sock', 'fingerrocket', 'L@sirena23', 'studiobl_fingerrocket');*/
        if ($mysqli->connect_errno) {
            throw new RuntimeException('mysqli connection error: ' . $mysqli->connect_error);
        }
        /* Set the desired charset after establishing a connection */
        $mysqli->set_charset('utf8mb4');
        if ($mysqli->errno) {
            throw new RuntimeException('mysqli error: ' . $mysqli->error);
        }
        $id = $this->getId();
        $username = $this->getUserName();
        $pass = $this->getPass();
        $sql = "INSERT INTO players (id, username, pass) VALUES ('" . $id . "','" . $username . "','" . $pass . "')";
       
        if ($mysqli->query($sql) === TRUE) {
            $success = true;
        } 
        $mysqli->close();
        return $success;
}//End function storeNew



}//Close class player

?>



