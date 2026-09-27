<?php

class debrisHandler{

//Methods
// Copies each `$property`/`$argument` pair from `$arguments` onto this handler; returns nothing.
public function __construct(array $arguments = array()) {
        
    if (!empty($arguments)) {
        foreach ($arguments as $property => $argument) {
                
            $this->{$property} = $argument;
        }

    }
}

// Rolls a die with `$dieType` sides; returns an integer from 1 through `$dieType`.
private function rollDie($dieType){
    return rand(1, $dieType);
}

// Rolls for a debris piece count using `$this->rollDie()`; returns an integer from zero through three.
public function howManyPieces(){
    $number = 0;
    $roll = $this->rollDie(100);
    if($roll > 15 && $roll <= 55){
       $number = 1;
    }
    if($roll > 55 && $roll <= 85){
        $number = 2;
    }
    if($roll > 85){
        $number = 3;
    }
    return $number;
}

// Randomly creates a rocket debris object, assigns it a unique ID, and returns that object.
public function getRocketDebris(){
    $roll = $this->rollDie(100);
    if($roll <= 5){
        $type = new Cone();
    } elseif($roll <= 10){
        $type = new gSystem();
    } elseif($roll <= 15){
        $type = new Fins();
    } elseif($roll <= 25){
        $type = new Tube();
    } elseif($roll <= 30){
        $type = new Explosive();
    } elseif($roll <= 35){
        $type = new hShield();
    } elseif($roll <= 40){
        $type = new Nozzle();
    } elseif($roll <= 45){
        $type = new fTank();
    } elseif($roll <= 50){
        $type = new Phalanges();
    } elseif($roll <= 55){
        $type = new bMat();
    } elseif($roll <= 60){
        $type = new Needle();
    } elseif($roll <= 65){
        $type = new Latch();
    } elseif($roll <= 70){
        $type = new Highlighter();
    } elseif($roll <= 75){
        $type = new Itinerary();
    } elseif($roll <= 80){
        $type = new Phrasebook();
    } elseif($roll <= 85){
        $type = new Lightning();
    } elseif($roll <= 90){
        $type = new Chain();
    } elseif($roll <= 95){
        $type = new jEdge();
    } else {
        $type = new Label();
    }

    $random = uniqid();
    $type->setId($random);
    return $type;
}//End function getRocketDebris

// Rolls for blueprint debris using `$this->rollDie()`; returns whether a blueprint is generated.
public function blueprintRoll(){
    $die = $this->rollDie(10);
    $isThere = FALSE;
    if($die <= 6){
        $isThere = TRUE;
    }
    return $isThere;
}

//pass in fortress->convertCladdingToString()
// Uses `$material` and a random roll to create cladding debris; returns the selected debris object.
public function getCladdingDebris($material){
    $type;
    $roll = $this->rollDie(100);
    if($roll > 0 && $roll <= 40){
        $type = new Rivet();
    }
    if($roll > 40 && $roll <= 50){
        $type = new Fragment();
        $type->setMaterial($material);
    }
    if($roll > 50 && $roll <= 80){
        $type = new PlateFragment();
        $type->setMaterial($material);
    }
    if($roll > 80 && $roll <= 90){
        $type = new Strut();
    }
    if($roll > 90 && $roll <= 100){
        $type = new Brace();
    }
    return $type;
}//End function getCladdingDebris

// Uses `$type`, optional `$material`, and debris/blueprint rolls to collect generated pieces; returns an array of debris objects.
public function handleDebris($type, $material = NULL){
    $mat;
    if($material != NULL){
        //In case material is damaged or badly damaged
        $mArray = explode(" ", $material);
        $mat = end($mArray);
    }
    $debris = [];
    $count = $this->howManyPieces();
    for($i=0;$i<$count;$i++){
        if($type == 0){
            $debris[] = $this->getRocketDebris();
        }
        if($type == 1){
            $debris[] = $this->getCladdingDebris($mat);
        }
    }
    $isBlueprint = $this->blueprintRoll();
    if($isBlueprint){
        $temp = new Blueprint();
        $temp->setModule();
        $random = uniqid();
        $temp->setId($random);
        $debris[] = $temp;
    }
    return $debris;
}







}//End class debrisHandler



?>