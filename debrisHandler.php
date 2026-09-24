<?php

class debrisHandler{

//Methods
public function __construct(array $arguments = array()) {
        
    if (!empty($arguments)) {
        foreach ($arguments as $property => $argument) {
                
            $this->{$property} = $argument;
        }

    }
}

private function rollDie($dieType){
    return rand(1, $dieType);
}

public function howManyPieces(){
    $number = 0;
    $roll = $this->rollDie(10);
    if($roll > 4 && $roll <= 7 ){
       $number = 1;
    }
    if($roll > 7 && $roll <= 9){
        $number = 2;
    }
    if($roll > 9){
        $number = 3;
    }
    return $number;
}

public function getRocketDebris(){
    $type;
    $roll = $this->rollDie(100);
    if($roll > 0 && $roll <= 5){
        $type = new Cone();
    }
    if($roll > 5 && $roll <= 10){
        $type = new gSystem();
    }
    if($roll > 1 && $roll <= 15){
        $type = new Fins();
    }
    if($roll > 15 && $roll <= 25){
        $type = new Tube();
    }
    if($roll > 25 && $roll <= 30){
        $type = new Explosive();
    }
    if($roll > 30 && $roll <= 35){
        $type = new hShield();
    }
    if($roll > 35 && $roll <= 40){
        $type = new Nozzle();
    }
    if($roll > 40 && $roll <= 45){
        $type = new fTank();
    }
    if($roll > 45 && $roll <= 50){
        $type = new Falanges();
    }
    if($roll > 50 && $roll <= 55){
        $type = new bMat();
    }
    if($roll > 55 && $roll <= 60){
        $type = new Needle();
    }
    if($roll > 60 && $roll <= 65){
        $type = new Latch();
    }
    if($roll > 65 && $roll <= 70){
        $type = new Highlighter();
    }
    if($roll > 70 && $roll <= 75){
        $type = new Itinerary();
    }
    if($roll > 75 && $roll <= 80){
        $type = new Phrasebook();
    }
    if($roll > 80 && $roll <= 85){
        $type = new Lightning();
    }
    if($roll > 85 && $roll <= 90){
        $type = new Chain();
    }
    if($roll > 90 && $roll <= 95){
        $type = new jEdge();
    }
    if($roll > 95 && $roll <= 100){
        $type = new Label();
    }

    $random = uniqid();
    $type->setId($random);
    return $type;
}//End function getRocketDebris

public function blueprintRoll(){
    $die = $this->rollDie(10);
    $isThere = FALSE;
    if($die <= 4){
        $isThere = TRUE;
    }
    return $isThere;
}

//pass in fortress->convertCladdingToString()
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