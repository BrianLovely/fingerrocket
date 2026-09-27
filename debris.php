<?php
class debris{

//Properties
public $id;
public $type; //0 = rocket, 1 = cladding, 2 = Blueprint, 3 = module

public $name;
public $typeId;

// Stores `$id` in `$this->id`; returns nothing.
public function setId($id){
    $this->id = $id;
}

// Returns `$this->id`; uses no arguments.
public function getId(){
    return $this->id;
}

// Stores `$type` in `$this->type`; returns nothing.
public function setType($type){
    $this->type = $type;
}

// Returns `$this->type`; uses no arguments.
public function getType(){
    return $this->type;
}

// Stores `$id` in `$this->typeId`; returns nothing.
public function setTypeId($id){
    $this->typeId = $id;
}

// Returns `$this->typeId`; uses no arguments.
public function getTypeId(){
    return $this->typeId;
}


// Stores `$lowerRange` in the first element of `$this->range`; returns nothing.
public function setLowerRange($lowerRange){
    $this->range[0] = $lowerRange;
}

// Returns the first element of `$this->range`; uses no arguments.
public function getLowerRange(){
    return $this->range[0];
}

// Stores `$upperRange` in the second element of `$this->range`; returns nothing.
public function setUpperRange($upperRange){
    $this->range[1] = $upperRange;
}

// Returns the second element of `$this->range`; uses no arguments.
public function getUpperRange(){
    return $this->range[1];
}

// Stores `$name` in `$this->name`; returns nothing.
public function setName($name){
    $this->name = $name;
}

// Returns `$this->name`; uses no arguments.
public function getName(){
    return $this->name;
}




//Methods
// Copies each `$property`/`$argument` pair from `$arguments` onto this object; returns nothing.
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

class Phalanges extends debris{
    public $name = "Phalanges";
    public $type = 0;
    public $typeId = 18;
};//Close class Phalanges

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

class Phrasebook extends debris{
    public $name = "Phrasebook";
    public $type = 0;
    public $typeId = 24;
};//Close class Phrasebook

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
        "Finger Rocket",
        "Dart",
        "Fletchette",
        "Bolt",
        "ICYMI",
        "ICBM",
        "Plate",
        "Bulwark",
        "Bastion",
        "Rampart",
        "Cladding",
        "Nose Cone",
        "Payload Module",
        "Propulsion Module"


    ];

    // Chooses a random entry from `$this->modules`, storing it in `$this->module` and `$this->name`; returns nothing.
    public function setModule(){
        $this->module = $this->modules[array_rand($this->modules)];
        $this->name = $this->module;
    }
}

class Raw extends debris{
    public $name = "Raw";
    public $type = 1;
    public $typeId = 35;
    public $material;

    // Validates `$material` against the supported materials, stores it in `$this->material` when valid, and returns a success boolean.
    public function setMaterial($material){
        $materials = array("Wood", "Vanadium", "Iron", "Titanium", "Chromium", "Steel", "Tungsten", "Mithril", "Admantium");
        if(in_array($material, $materials, true)){
            $this->material = $material;
            return true;
        }
        return false;
    }

    // Returns `$this->material`; uses no arguments.
    public function getMaterial(){
        return $this->material;
    }

    // Returns `$this->name` with `$this->material` appended when set; uses no arguments.
    public function getName(){
        return $this->material ? $this->name . " " . $this->material : $this->name;
    }
}

class Fragment extends debris{
    public $name = "Fragment";
    public $type = 1;
    public $typeId = 33;
    public $material;
    public $isPlate = false;

    // Stores `$material` in `$this->material`; returns nothing.
    public function setMaterial($material){
        $this->material = $material;
    }

    // Returns `$this->material`; uses no arguments.
    public function getMaterial(){
        return $this->material;
    }

    // Stores `$isPlate` in `$this->isPlate`; returns nothing.
    public function setIsPlate($isPlate){
        $this->isPlate = $isPlate;
    }

    // Returns `$this->isPlate`; uses no arguments.
    public function getIsPlate(){
        return $this->isPlate;
    }

    // Builds a display name from `$this->name`, `$this->isPlate`, and `$this->material`; returns the resulting string.
    public function getName(){
        return ($this->isPlate ? "Plate" : $this->name) . ($this->material ? " " . $this->material : "");
    }
}



class PlateFragment extends Fragment{
    public $typeId = 34;
    public $isPlate = true;
}


?>