<?php
class module{

//Properties
public $id;
public $type; //Rocket, Cladding
public $recipe;
public $name; //Nose, Payload, Propulsion, Bulwark, Bastion, Buttress, Rampart
public $isComplex = FALSE;
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

public function setRecipe($recipe){
    $this->recipe = $recipe;
}

public function getRecipe(){
    return $this->recipe;
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

class nCone extends Module{
    public $name = "Nose Cone";
    public $type = 0;
    public $recipe = '{"cone": 1,"gSystem": 1,"tube": 1}';
    public $typeId = 1000;
}//End class nCone

class Payload extends Module{
    public $name = "Payload";
    public $type = 0;
    public $recipe = '{"Explosive": 1,"Heat Shield": 1,"Tube": 1}';
    public $typeId = 1001;
}//End class Payload

class pModule extends Module{
    public $name = "Propulsion Module";
    public $type = 0;
    public $recipe = '{"Fins": 1,"Fuel Tank": 1,"Nozzle": 1}';
    public $typeId = 1002;
}//End class pModule



class Bulwark extends Module{
    public $name = "Bulwark";
    public $type = 1;
    public $material;
    public $recipe = '{"Rivet":25, "Plate":9, "Strut":5,"Brace":4}';
    public $typeId = 1003;

    public function setMaterial($material){
        $this->material = $material;
    }

    public function getMaterial(){
        return $this->material;
    }

    public function getFullName(){
        $material = $this->getMaterial();
        $temp = $this->getName();
        $name = $meterial . " " . $temp;
        return $name;
    }
}//End class Bulwark

class Buttress extends Bulwark{
    public $name = "Buttress";
    public $type = 1;
    public $material;
    public $recipe = '{"Rivet":25, "Plate":8, "Strut":6,"Brace":4}';
    public $typeId = 1004;
}//End class Buttress

class Rampart extends Bulwark{
    public $name = "Rampart";
    public $type = 1;
    public $material;
    public $recipe = '{"Rivet":12, "Plate":4, "Brace":6}';
    public $typeId = 1005;
}//End class Rampart

class Bastion extends Bulwark{
    public $name = "Bastion";
    public $type = 1;
    public $material;
    public $recipe = '{"Bulwark":4, "Buttress":2';
    public $isComplex = TRUE;
    public $typeId = 1006;
}//End class Bastion

class Cladding extends Module{
    public $name = "Cladding";
    public $type = 1;
    public $material;
    public $recipe = '{"Bastion":2, "Bulwark":3, "Rampart":6}';
    public $isComplex = TRUE;
    public $typeId = 1007;
}//End class Cladding




?>