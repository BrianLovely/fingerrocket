<?php
class FingerRocket
{
// Properties
    public $id = 0;
    public $typeId = 0;
    public $name = "Finger Rocket";
    public $toHit = 20;
    public $criticalChance = 2;
    public $dieType = 4;
    public $debrisChance = 5;
    public $debrisValue = 1;
    public $recipe = '{"nCone": 1,"Payload": 1,"pModule": 1, "Falanges": 1}';

    // Methods

    public function __construct(array $arguments = array()) {
        
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                
                $this->{$property} = $argument;
            }
        }
        
    }


    public function getName(){
        return $this->name;
    }

    public function getId(){
        return $this->id;
    }

    public function getTypeId(){
        return $this->typeId;
    }

    public function getToHit(){
        return $this->toHit;
    }

    public function getcriticalChance(){
        return $this->criticalChance;
    }

    public function getDieType(){
        return $this->dieType;
    }

    public function getDebrisChance(){
        return $this->debrisChance;
    }

    public function getDebrisValue(){
        return $this->debrisValue;
    }
}//End class FingerRocket

class Dart extends FingerRocket{
    public $id = 0;
    public $typeId = 1;
    public $name = "Dart";
    public $toHit = 30;
    public $criticalChance = 2;
    public $dieType = 6;
    public $debrisChance = 5;
    public $debrisValue = 1;
    public $recipe = '{"nCone": 1,"Payload": 1,"pModule": 1, "bMat": 1}';
}
class Flechette extends FingerRocket{
    public $id = 0;
    public $typeId = 2;
    public $name = "Flechette";
    public $toHit = 40;
    public $criticalChance = 2;
    public $dieType = 6;
    public $debrisChance = 5;
    public $debrisValue = 1;
    public $recipe = '{"nCone": 1,"Payload": 1,"pModule": 1, "Needle": 1}';
}
class Bolt extends FingerRocket{
    public $id = 0;
    public $typeId = 3;
    public $name = "Bolt";
    public $toHit = 50;
    public $criticalChance = 2;
    public $dieType = 8;
    public $debrisChance = 10;
    public $debrisValue = 2;
    public $recipe = '{"nCone": 1,"Payload": 1,"pModule": 1, "Latch": 1}';
};
class ClusterRocket extends FingerRocket{
    public $id = 0;
    public $name = "Cluster Rocket";
};
class ICYMI extends FingerRocket{
    public $id = 0;
    public $typeId = 4;
    public $name = "ICYMI";
    public $toHit = 60;
    public $criticalChance = 5;
    public $dieType = 10;
    public $debrisChance = 10;
    public $debrisValue = 2;
    public $recipe = '{"nCone": 1,"Payload": 1,"pModule": 1, "Highlighter": 1}';
};
class ICBM extends FingerRocket{
    public $id = 0;
    public $typeId = 5;
    public $name = "ICBM";
    public $toHit = 70;
    public $criticalChance = 5;
    public $dieType = 12;
    public $debrisChance = 10;
    public $debrisValue = 2;
    
};
class TCB extends FingerRocket{
    public $id = 0;
    public $typeId = 6;
    public $name = "TCB";
    public $toHit = 80;
    public $criticalChance = 8;
    public $dieType = 15;
    public $debrisChance = 15;
    public $debrisValue = 3;
    public $recipe = '{"nCone": 1,"Payload": 1,"pModule": 1, "Lightning": 1, "Chain": 1}';
};
class CanOfWhoopAss extends FingerRocket{
    public $id = 0;
    public $typeId = 7;
    public $name = "Can of Whoop Ass";
    public $toHit = 90;
    public $criticalChance = 10;
    public $dieType = 20;
    public $debrisChance = 20;
    public $debrisValue = 3;
    public $recipe = '{"nCone": 1,"Payload": 1,"pModule": 1, "jEdge": 1, "Label": 1}';
};

?>