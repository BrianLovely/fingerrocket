<?php
class debris{

//Properties
public $id;
public $type; //0 = rocket, 1 = cladding, 2 = Blueprint, 3 = module

public $name;
public $typeId;

public function setId($id){
    $this->id = $id;
}

public function getId(){
    return $this->id;
}

public function setType($type){
    $this->type = $type;
}

public function getType(){
    return $this->type;
}

public function setTypeId($id){
    $this->typeId = $id;
}

public function getTypeId(){
    return $this->typeId;
}


public function setLowerRange($lowerRange){
    $this->range[0] = $lowerRange;
}

public function getLowerRange(){
    return $this->range[0];
}

public function setUpperRange($upperRange){
    $this->range[1] = $upperRange;
}

public function getUpperRange(){
    return $this->range[1];
}

public function setName($name){
    $this->name = $name;
}

public function getName(){
    return $this->name;
}




//Methods
public function __construct(array $arguments = array()) {
        
    if (!empty($arguments)) {
        foreach ($arguments as $property => $argument) {
                
            $this->{$property} = $argument;
        }

    }
}




}//End class debris

class Cone extends debris{
    public $name = "Cone";
    public $type = 0;
    public $typeId = 10;
};//Close class Cone

class gSystem extends debris{
    public $name = "Guidance System";
    public $type = 0;
    public $typeId = 11;
};//Close class gSystem

class Fins extends debris{
    public $name = "Fins";
    public $type = 0;
    public $typeId = 12;
};//Close class Fins

class Tube extends debris{
    public $name = "Tube";
    public $type = 0;
    public $typeId = 13;
};//Close class Tube

class Explosive extends debris{
    public $name = "Explosive";
    public $type = 0;
    public $typeId = 14;
};//Close class Explosive

class hShield extends debris{
    public $name = "Heat Shield";
    public $type = 0;
    public $typeId = 15;
};//Close class hShield

class Nozzle extends debris{
    public $name = "Nozzle";
    public $type = 0;
    public $typeId = 16;
};//Close class Nozzle

class fTank extends debris{
    public $name = "Fuel Tank";
    public $type = 0;
    public $typeId = 17;
};//Close class fTank

class Falanges extends debris{
    public $name = "Falanges";
    public $type = 0;
    public $typeId = 18;
};//Close class Falanges

class bMat extends debris{
    public $name = "Beer Mat";
    public $type = 0;
    public $typeId = 19;
};//Close class bMat

class Needle extends debris{
    public $name = "Needle";
    public $type = 0;
    public $typeId = 20;
};//Close class Needle

class Latch extends debris{
    public $name = "Latch";
    public $type = 0;
    public $typeId = 21;
};//Close class Latch

class Highlighter extends debris{
    public $name = "Highlighter";
    public $type = 0;
    public $typeId = 22;
};//Close class Highlighter

class Itinerary extends debris{
    public $name = "Itinerary";
    public $type = 0;
    public $typeId = 23;
};//Close class Itinerary

class pBook extends debris{
    public $name = "Phrasebook";
    public $type = 0;
    public $typeId = 24;
};//Close class pBook

class Lightning extends debris{
    public $name = "Lightning";
    public $type = 0;
    public $typeId = 25;
};//Close class Lightning

class Chain extends debris{
    public $name = "Chain";
    public $type = 0;
    public $typeId = 26;
};//Close class Chain

class jEdge extends debris{
    public $name = "Jagged Edge";
    public $type = 0;
    public $typeId = 27;
};//Close class jEdge

class Label extends debris{
    public $name = "Label";
    public $type = 0;
    public $typeId = 28;
};//Close class Label

class Rivet extends debris{
    public $name = "Rivet";
    public $type = 1;
    public $typeId = 29;
};//Close class Rivet

class Strut extends debris{
    public $name = "Strut";
    public $type = 1;
    public $typeId = 30;
};//Close class Strut

class Brace extends debris{
    public $name = "Brace";
    public $type = 1;
    public $typeId = 31;
};//Close class Brace

class Blueprint extends debris{
    public $name = "Blueprint";
    public $type = 2;
    public $typeId = 32;
    public $module;
    public $modules = [
        "Plate",
        "Bulwark",
        "Bastion",
        "Rampart",
        "Cladding",
        "Nose Cone",
        "Payload Module",
        "Propulsion Module"


    ];

    public function setModule(){
        $this->module = $this->modules[array_rand($this->modules)];
    }
}

class Fragment extends debris{
    public $material;
    public $isPlate = FALSE;
    public $typeId = 33;

    public function setMaterial($material){
        $this->material = $material;
    }

    public function getMaterial(){
        return $this->material;
    }

    public function setIsPlate($isPlate){
        $this->isPlate = $isPlate;
    }

    public function getIsPlate(){
        return $this->isPlate;
    }

    public function getName(){
        $frag = "Fragment";
        if($this->isPlate){
            $frag = "Plate";
        }
        $material = $this->getMaterial();
        $temp = $frag . " of " . $material;
        return $temp;
    }
    public $name = "Fragment";
    public $type = 1;

    
    
};//Close class Fragment

class Plate extends Fragment{
    public $isPlate = TRUE;
    public $typeId = 34;
}


?>