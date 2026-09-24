<?php
class Fortress
{
    // Properties
    public $nameListPrefix = [
        "Red",
        "Green",
        "Blue",
        "Black",
        "Grey",
        "Filthy",
        "Smelly",
        "Terrifying",
        "Remorseless",
        "Iron",
        "Forbidden",
        "Bug-infested"

    ];
    public $nameList = [
        "Fortress",
        "Donjon",
        "Citadel",
        "Castle",
        "Redoubt",
        "Bastion",
        "Fastness",
        "Alcazar",
        "Stronghold"
    ];
    public $nameListSuffix = [
        "of Evil",
        "of Doom",
        "of Hunger",
        "of Despair",
        "of Death",
        "of Pain",
        "of Destruction",
        "of Lost Souls"
    ];
    public $armory = [];
    public $flak = 2;
    public $damageResistance = 5;
    public $hitResistance = 5;
    public $points = 150;
    public $id = "";
    public $cladding = 0;
    // Keep track of undamaged cladding to determine if downgraded //
    public $storedCladding = 0;
    public $name = "";
    public $damage = "undamaged";
    public $combatHandler;
    public $claddingDebris = 0;
    public $rocketDebris = 0;
    public $jsonArmory = '[
    {
        "name": "Finger Rocket",
        "id": "12345",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4",
        "debrisChance": "5",
        "debrisValue": "1"
    }, 
    {
        "name": "Finger Rocket",
        "id": "12346",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4",
        "debrisChance": "5",
        "debrisValue": "1"
    },
    {
        "name": "Finger Rocket",
        "id": "12347",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4",
        "debrisChance": "5",
        "debrisValue": "1"
    },
    {
        "name": "Finger Rocket",
        "id": "12348",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4",
        "debrisChance": "5",
        "debrisValue": "1"
    },
    {
        "name": "Finger Rocket",
        "id": "12349",
        "typeId": "0",
        "toHit": "20",
        "criticalChance": "1",
        "dieType": "4",
        "debrisChance": "5",
        "debrisValue": "1"
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
        unset($this->jsonArmory);
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

    public function setCombatHandler($combatHandler){
        $this->combatHandler = $combatHandler;
    }

    public function getCladdingDebris(){
        return $this->claddingDebris;
    }

    public function setCladdingDebris($cr){
        $this->claddingDebris = $cr;
    }

    public function getRocketDebris(){
        return $this->rocketDebris;
    }

    public function setRocketDebris($rd){
        $this->rocketDebris = $rd;
    }

  
    public function setRandomName(){
        $randPrefix = $this->nameListPrefix[array_rand($this->nameListPrefix, 1)];
        $randType  = $this->nameList[array_rand($this->nameList, 1)];
        $randSuffix = $this->nameListSuffix[array_rand($this->nameListSuffix, 1)];
        $name = $randPrefix . " " . $randType . " " . $randSuffix;
        $this->setName($name);
    }

    public function convertCladdingToString($damage = FALSE){
        $damRes = $this->getDamageResistance();
        if($damRes <= 10){
            return "Wood";
        } elseif($damRes > 10 &&  $damRes <= 13){
            if($damage){
                $this->setCladding(1);
                $this->setStoredCladding(1);
                $this->setHitResistance(12);
            }
            return "badly damaged Vanadium";
        } elseif($damRes > 13 &&  $damRes <= 16){
            if($damage){
                $this->setCladding(1);
                $this->setStoredCladding(1);
                $this->setHitResistance(15);
            }
            return "damaged Vanadium";
        } elseif($damRes > 16 &&  $damRes <= 20){
            if($damage){
                $this->setCladding(1);
                $this->setStoredCladding(1);
                $this->setHitResistance(18);
            }
            return "Vanadium";
        } elseif($damRes > 20 && $damRes <= 23){
            if($damage){
                $this->setCladding(2);
                $this->setStoredCladding(2);
                $this->setHitResistance(22);
            }
            return "badly damaged Iron";
        } elseif($damRes > 23 && $damRes <= 26){
            if($damage){
                $this->setCladding(2);
                $this->setStoredCladding(2);
                $this->setHitResistance(25);
            }
            return "damaged Iron";
        } elseif($damRes > 26 && $damRes <= 30){
            if($damage){
                $this->setCladding(2);
                $this->setStoredCladding(2);
                $this->setHitResistance(28);
            }
            return "Iron";
        } elseif($damRes > 30 && $damRes <= 33){
            if($damage){
                $this->setCladding(3);
                $this->setStoredCladding(3);
                $this->setHitResistance(32);
            }
            return "badly damaged Titanium";
        } elseif($damRes >33 && $damRes <= 36){
            if($damage){
                $this->setCladding(3);
                $this->setStoredCladding(3);
                $this->setHitResistance(35);
            }
            return "damaged Titanium";
        } elseif($damRes >36 && $damRes <= 40){
            if($damage){
                $this->setCladding(3);
                $this->setStoredCladding(3);
                $this->setHitResistance(38);
            }
            return "Titanium";
        } elseif($damRes > 40 && $damRes <= 43){
            if($damage){
                $this->setCladding(4);
                $this->setStoredCladding(4);
                $this->setHitResistance(42);
            }
            return "badly damaged Chromium";
        } elseif($damRes > 43 && $damRes <= 46){
            if($damage){
                $this->setCladding(4);
                $this->setStoredCladding(4);
                $this->setHitResistance(45);
            }
            return "damaged Chromium";
        } elseif($damRes > 46 && $damRes <= 50){
            if($damage){
                $this->setCladding(4);
                $this->setStoredCladding(4);
                $this->setHitResistance(48);
            }
            return "Chromium";
        } elseif($damRes > 50 && $damRes <= 53){
            if($damage){
                $this->setCladding(5);
                $this->setStoredCladding(5);
                $this->setHitResistance(52);
            }
            return "badly damaged Steel";
        } elseif($damRes > 53 && $damRes <= 56){
            if($damage){
                $this->setCladding(5);
                $this->setStoredCladding(5);
                $this->setHitResistance(55);
            }
            return "damaged Steel";
        } elseif($damRes > 56 && $damRes <= 60){
            if($damage){
                $this->setCladding(5);
                $this->setStoredCladding(5);
                $this->setHitResistance(58);
            }
            return "Steel";
        } elseif($damRes > 60 && $damRes <= 63){
            if($damage){
                $this->setCladding(6);
                $this->setStoredCladding(6);
                $this->setHitResistance(62);
            }
            return "badly damaged Tungsten";
        } elseif($damRes > 63 && $damRes <= 66){
            if($damage){
                $this->setCladding(6);
                $this->setStoredCladding(6);
                $this->setHitResistance(65);
            }
            return "damaged Tungsten";
        } elseif($damRes > 66 && $damRes <= 70){
            if($damage){
                $this->setCladding(6);
                $this->setStoredCladding(6);
                $this->setHitResistance(68);
            }
            return "Tungsten";
        } elseif($damRes > 70 && $damRes <= 73){
            if($damage){
                $this->setCladding(8);
                $this->setStoredCladding(8);
                $this->setHitResistance(72);
            }
            return "badly damaged Mithril";
        } elseif($damRes > 73 && $damRes <= 76){
            if($damage){
                $this->setCladding(8);
                $this->setStoredCladding(8);
                $this->setHitResistance(75);
            }
            return "damaged Mithril";
        } elseif($damRes > 76 && $damRes <= 80){
            if($damage){
                $this->setCladding(8);
                $this->setStoredCladding(8);
                $this->setHitResistance(78);
            }
            return "Mithril";
        } elseif($damRes >80 && $damRes <= 83){
            if($damage){
                $this->setCladding(9);
                $this->setStoredCladding(9);
                $this->setHitResistance(82);
            }
            return "badly damaged Admantium";
        } elseif($damRes >83 && $damRes <= 86){
            if($damage){
                $this->setCladding(9);
                $this->setStoredCladding(9);
                $this->setHitResistance(85);
            }
            return "damaged Admantium";
        } elseif($damRes >86 && $damRes <= 90){
            if($damage){
                $this->setCladding(9);
                $this->setStoredCladding(9);
                $this->setHitResistance(88);
            }
            return "Admantium";
        } elseif($damRes < 0){
            $this->setCladding(0);
            $this->setStoredCladding(0);
            $this->setHitResistance(5);
            $this->setDamageResistance(5);
            return "Wood";
        };

    }

    

    public function applyDamage($damage){
        $tempRes = $this->getDamageResistance() - $damage;
        if($tempRes < 0){
            $tempRes = 0;
        }
        $this->setDamageResistance($tempRes);
        $this->convertCladdingToString(TRUE);
    }



    public function addPoints($points){
        $temp = $this->getPoints();
        $temp = $temp + $points;
        $this->setPoints($temp);
    }

    public function removePoints($points){
        $temp = $this->getPoints();
        $temp = $temp - $points;
        $this->setPoints($temp);
    }

    public function addFlak($flak){
        $temp = $this->getFlak();
        $temp = $temp + $flak;
        $this->setFlak($temp);
        $cost = $flak * 5;
        $this->removePoints($cost);
        //echo "add flak sanity<br/>";
        $this->store();
    }

    public function removeFlak($flak){
        $temp = $this->getFlak();
        $temp = $temp - $flak;
        $this->setFlak($temp);
        $this->store();
    }

    public function displayRockets(){
        foreach($this->armory as $key => $val){
            print '<option value="' . $val->id . '">' . $val->name . '</option>';
        }

    }

    
    public function convertArmoryToJson(){
        $temp = json_encode($this->armory);
        return $temp;
    }
  
    public function stockArmory($armoryJson){ 
        $armoryArray = json_decode($armoryJson, true);  
        $count = count($armoryArray);
        for($i = 0; $i < $count; $i++){
           $temp = new FingerRocket();
           $temp->id = $armoryArray[$i]['id'];
           $temp->typeId = $armoryArray[$i]['typeId'];
           $temp->name = $armoryArray[$i]['name'];
           $temp->toHit = $armoryArray[$i]['toHit'];
           $temp->criticalChance = $armoryArray[$i]['criticalChance'];
           $temp->dieType = $armoryArray[$i]['dieType'];
           $temp->debrisChance = $armoryArray[$i]['debrisChance'] ?? $temp->debrisChance;
           $temp->debrisValue = $armoryArray[$i]['debrisValue'] ?? $temp->debrisValue;
           if(strlen($temp->id) < 3){
            $temp->id = uniqid();
            $tempString = $this->convertArmoryToJson();
            $this->setJsonArmory($tempString);
           }
           
           $this->armory[] = $temp;
        }  
    }
  
 

   public function getRocket($rocketId){ 
        $i = 0;
        $typeId = 0;
        //echo "getting rocket: " . $rocketId . "<br/>";
        //echo "armory starts at: " . var_dump($this->armory) . "<br/>";
        foreach($this->armory as $key => $val){
            if($val->id == $rocketId){  
                //echo "match";  
                $typeId = $val->getTypeId();
                $this->payForRocket($typeId);
                array_splice($this->armory, $i, 1);
                //echo "getRocket sanity<br/>";
                return $val;
            }
            $i++;
        }
    }

    public function payForRocket($typeId){
        //echo "payForRocket sanity typeId: " . $typeId . "<br/>";
        $temp = 1;
        switch($typeId){
            case 0:
                $temp = 1;
                break;

            case 1:
                $temp = 3;
                break;

            case 2:
                $temp = 5;
                break;
            
           case 3:
                $temp = 7;
                break;

            case 4:
                $temp = 9;
                break;
            
             case 5:
                $temp = 11;
                break;
             
            case 6:
                $temp = 13;
                break;

            case 7:
                $temp = 15;
                break; 

            case 8:
                $temp = 5;
                break;
            }
        $tempPoints = $this->getPoints() - $temp;
        $this->setPoints($tempPoints); 
        $this->store(); 
        
    } 

    public function attack($rocketId){
        
        $rocket = $this->getRocket($rocketId);
        $combatHandler->handleCombat($this, $rocket);
    }

    public function addFingerRocket(){
        $myFingerRocket = new FingerRocket();
        $myFingerRocket->id = uniqid();
        $this->armory[] = $myFingerRocket;
    }

    public function addDart(){
        $myDart = new Dart();
        $myDart->typeId = 1;
        $myDart->name = "Dart";
        $myDart->toHit = 30;
        $myDart->criticalChance = 2;
        $myDart->dieType = 5;
        $myDart->id = uniqid();
        $this->armory[] = $myDart;
    }

    public function addFlechette(){
        $myFlechette = new Flechette();
        $myFlechette->typeId = 2;
        $myFlechette->name = "Flechette";
        $myFlechette->toHit = 40;
        $myFlechette->criticalChance = 2;
        $myFlechette->dieType = 6;
        $myFlechette->id = uniqid();
        $this->armory[] = $myFlechette;
    }

    public function addBolt(){
        $myBolt = new Bolt();
        $myBolt->typeId = 3;
        $myBolt->name = "Bolt";
        $myBolt->toHit = 50;
        $myBolt->criticalChance = 2;
        $myBolt->dieType = 8;
        $myBolt->id = uniqid();
        $this->armory[] = $myBolt;
    }

    public function addClusterRocket(){
        $myCluster = new ClusterRocket();
        $myCluster->id = uniqid();
        $myCluster->name = "Cluster Rocket";
        $this->armory[] = $myCluster;
    }

    public function addICYMI(){
        $myICYMI = new ICYMI();
        $myICYMI->typeId = 4;
        $myICYMI->name = "ICYMI";
        $myICYMI->toHit = 60;
        $myICYMI->criticalChance = 5;
        $myICYMI->dieType = 10;
        $myICYMI->id = uniqid();
        $this->armory[] = $myICYMI;
        return $myICYMI;
    }

    public function addICBM(){
        $myICBM = new ICBM();
        $myICBM->typeId = 5;
        $myICBM->name = "ICBM";
        $myICBM->toHit = 70;
        $myICBM->criticalChance = 5;
        $myICBM->dieType = 12;
        $myICBM->id = uniqid();
        $this->armory[] = $myICBM;
    }

    public function addTCB(){
        $myTCB = new TCB();
        $myTCB->typeId = 6;
        $myTCB->name = "TCB";
        $myTCB->toHit = 80;
        $myTCB->criticalChance = 8;
        $myTCB->dieType = 15;
        $myTCB->id = uniqid();
        $this->armory[] = $myTCB;
    }

    public function addCanOfWhoopAss(){
        $myCOWA = new CanOfWhoopAss();
        $myCOWA->typeId = 7;
        $myCOWA->name = "Can of Whoop Ass";
        $myCOWA->toHit = 90;
        $myCOWA->criticalChance = 10;
        $myCOWA->dieType = 20;
        $myCOWA->id = uniqid();
        $this->armory[] = $myCOWA;
    }

    public function updateCladding($type, $purchase = TRUE){
        //echo "update cladding type: " . $type . "<br/>";
        $tempCost = 0;
        switch($type){
            case 1:
                /* Vanadium */
                $tempCost = 20;
                if($this->points >= $tempCost){
                    $this->cladding = 1;
                    $this->storedCladding = 1;
                    $this->setDamageResistance(20);
                    $this->setHitResistance(20);
                }
                break;

            case 2:
                $tempCost = 30;
                /* Iron */
                if($this->points >= $tempCost){
                    $this->cladding = 2;
                    $this->storedCladding = 2;
                    $this->setDamageResistance(30);
                    $this->setHitResistance(30);
                }
                break;

            case 3:
                /* Titanium */
                $tempCost = 40;
                if($this->points >= $tempCost){
                    $this->cladding = 3;
                    $this->storedCladding = 3;
                    $this->setDamageResistance(40);
                    $this->setHitResistance(40);
                }
                    break;

            case 4:
                /* Chromium */
                $tempCost = 50;
                if($this->points >= $tempCost){
                    //echo "buying chromium<br/>";
                    $this->cladding = 4;
                    $this->storedCladding = 4;
                    $this->setDamageResistance(50);
                    $this->setHitResistance(50);
                }
                    break;

            case 5:
                /* Steel */
                $tempCost = 60;
                if($this->points >= $tempCost){
                    $this->cladding = 5;
                    $this->storedCladding = 5;
                    $this->setDamageResistance(60);
                    $this->setHitResistance(60);
                }
                    break;

            case 6:
                /* Tungsten */
                $tempCost = 70;
                if($this->points >= $tempCost){
                    $this->cladding = 6;
                    $this->storedCladding = 6;
                    $this->setDamageResistance(70);
                    $this->setHitResistance(70);
                }
                    break;

            case 7:
                /* Mithril */
                $tempCost = 80;
                if($this->points >= $tempCost){
                    $this->cladding = 7;
                    $this->storedCladding = 7;
                    $this->setDamageResistance(80);
                    $this->setHitResistance(80);
                }
                    break;
            case 8:
                    /* Admantium */
                    $tempCost = 90;
                    if($this->points >= $tempCost){
                        $this->cladding = 8;
                        $this->storedCladding = 8;
                        $this->setDamageResistance(90);
                        $this->setHitResistance(90);
                    }
                         break;
                }
                if($purchase){
                    $this->removePoints($tempCost);
                }
                $this->store(); 
        }

        public function sortArmory(){
            $tempArray = usort(
                $this->getArmory(), 
                fn(array $a, array $b): int => $b['typeId'] <=> $a['typeId']
            );
            $this->setArmory([]);
            $this->setArmory($tempArray);
        }

    public function addToArmory($type, $quan = 1){
        //echo "addToArmory: " . $quan . " " . $type . "<br/>";
        $tempCost = 0;
        for($x=0; $x < $quan; $x++){
            //echo "should see this nine times</br>";
            switch($type){

                case 0:
                    $tempCost = 1;
                    if($this->points >= $tempCost){
                        $this->addFingerRocket();
                       
                    }
                    break;

                    case 1:
                        $tempCost = 3;
                        if($this->points >= $tempCost){
                            $this->addDart();
                            
                        }
                        break;

                    case 2:
                        $tempCost = 5;
                        if($this->points >= $tempCost){
                            $this->addFlechette();
                           
                        }
                        break;

                    case 3:
                        $tempCost = 7;
                        if($this->points >= $tempCost){
                            $this->addBolt();
                         
                        }
                        break;

                    case 4:
                        $tempCost = 9;
                        if($this->points >= $tempCost){
                            //echo $x . "buying an ICYMI<br/>";
                            $this->addICYMI();
                           
                        }
                        break;

                    case 5:
                        $tempCost = 11;
                        if($this->points >= $tempCost){
                            $this->addICBM();
                        
                        }
                        break;

                    case 6:
                        $tempCost = 13;
                        if($this->points >= $tempCost){
                            $this->addTCB();
                            
                        }
                        break;

                    case 7:
                        $tempCost = 15;
                        if($this->points >= $tempCost){
                            $this->addCanOfWhoopAss();
                          
                        }
                        break;

                    case 8:
                        $tempCost = 5;
                        if($this->points >= $tempCost){
                            $this->addClusterRocket();
                           
                        }
                        break;
        }
        $this->payForRocket($type);
    }
        $temp = $this->convertArmoryToJson();
        $this->setJsonArmory($temp);
        $this->store();

    }

    

    public function package(){
        $array = [];
        $array['id'] = $this->getId();
        $array['flak'] = $this->getFlak();
        $array['armory'] = $this->getArmory();
        $array['cladding'] = $this->convertCladdingToString();
        $array['name'] = $this->getName();
        $array['points'] = $this->getPoints();
        return $array;
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
        $armory = $this->convertArmoryToJson();
        //echo "armory: " . $armory . "<br/>";
        $cladding = $this->getCladding();
        $storedCladding = $this->getStoredCladding();
        $damageResistance = $this->getDamageResistance();
        $hitResistance = $this->getHitResistance();
        $points = $this->getPoints();
        $flak = $this->getFlak();
        //echo "id: " . $this->getId() . " armory " . $this->getJsonArmory() . "<br/> cladding: " . $this->getCladding() . " storedCladding: " . $this->getStoredCladding();
        //echo "damageResistance: " . $this->getDamageResistance() . " hitResistance: " . $this->getHitResistance() . " points: " . $this->getPoints() . " flak: " . $this->getFlak();
       //$alt = "UPDATE fortress SET 'armory'='" . $this->getJsonArmory() . "','cladding'='" . $this->getCladding() . "','storedCladding'='" . $this->getStoredCladding . "','damageResistance'='" . "','hitResistance'='" . $this->getHitResistance() . "','points'='" . $this->getPoints() . "','flak'='" . $this->getFlak() . "' WHERE 'id'='" . $this->getId() . "'";
        //echo $alt;
        $sql = "UPDATE fortress SET 
        armory=?,
        cladding=?,
        storedCladding=?,
        damageResistance=?,
        hitResistance=?,
        points=?,
        flak=?
        WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("siiiiiis", $armory, $cladding, $storedCladding, $damageResistance, $hitResistance, $points, $flak, $id);
        $stmt->execute();
        
        /* close statement */
        $stmt->close();
        
       
    }

    public function storeNew(){
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
        $armory = $this->getJsonArmory();
        $name = $this->getName();
        $cladding = $this->getCladding();
        $storedCladding = $this->getStoredCladding();
        $damageResistance = $this->getDamageResistance();
        $hitResistance = $this->getHitResistance();
        $points = $this->getPoints();
        $flak = $this->getFlak();
        $sql = "INSERT INTO `fortress`(`id`, `armory`, `name`, `cladding`, `storedCladding`, `damageResistance`, `hitResistance`, `points`, `flak`) VALUES ('" . $id . "','" . $armory . "','" . $name . "','" . $cladding . "','" . $storedCladding . "','"   . $damageResistance . "','"  . $hitResistance . "','"  . $points . "','"  . $flak . "')";
        if ($mysqli->query($sql) === TRUE) {
            
        } else {
            
        }
    }

    



}

?>