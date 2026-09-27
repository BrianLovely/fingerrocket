<?php
class Cladding
{
// Properties
    public $typeId = 0;
    public $name = "Wood";
    public $hitResistance = 5;
    public $damageResistance = 5;
    public $cost = 0;
    public $debrisChance = 5;
    public $debrisValue = 1;
    public $highDamage = 0;
    public $lowDamage = 0;

    // Methods

    // Copies each `$property`/`$argument` pair from `$arguments` onto this cladding; returns nothing.
    public function __construct(array $arguments = array()) {
        
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                
                $this->{$property} = $argument;
            }
        }
        
    }

    // Returns `$this->debrisChance`; uses no arguments.
    public function getDebrisChance(){
        return $this->debrisChance;
    }

    // Returns `$this->debrisValue`; uses no arguments.
    public function getDebrisValue(){
        return $this->debrisValue;
    }

    // Returns `$this->highDamage`; uses no arguments.
    private function getHighDamage(){
        return $this->highDamage;
    }

    // Returns `$this->lowDamage`; uses no arguments.
    private function getLowDamage(){
        return $this->lowDamage;

    }

    // Returns `$this->damageResistance`; uses no arguments.
    public function getDamageResistance(){
        return $this->damageResistance;
    }

    // Stores `$dr` in `$this->damageResistance`; returns nothing.
    public function setDamageResistance($dr){
        $this->damageResistance = $dr;
    }

    // Returns `$this->hitResistance`; uses no arguments.
    public function getHitResistance(){
        return $this->hitResistance;
    }

    // Stores `$hr` in `$this->hitResistance`; returns nothing.
    public function setHitResistance($hr){
        $this->hitResistance = $hr;
    }

    // Builds a damage-state prefix from `$this->damageResistance`, `$this->highDamage`, and `$this->lowDamage`, then appends `$this->name`; returns the display name.
    public function getName(){
        $modString = "";
        $lowHigh = $this->getHighDamage() - 3;
        $highLow = $this->getLowDamage() + 3;
        if($this->getDamageResistance() >= $this->getLowDamage() && $this->getDamageResistance() < $highLow){
            $modString = "badly damaged ";
        } elseif ($this->getDamageResistance() >= $highLow && $this->getDamageResistance() <= $lowHigh){
            $modString = "damaged ";
        }
        $name = $modString . $this->name;
        return $name;
    }

// Uses `$damage` and the object's damage/hit resistance values to update damage state and determine destruction; returns a boolean.
    public function isDestroyed($damage){
        $tempDR = $this->getDamageResistance() - $damage;
        $this->setDamageResistance($tempDR);
        $tempHR = $this->getHitResistance() - $damage;
        $this->setHitResistance($tempHR);
        return $this->getDamageResistance() < $this->getLowDamage();
    }
}

    

class Vanadium extends Cladding{
    public $typeId = 1;
    public $name = "Vanadium";
    public $hitResistance = 20;
    public $damageResistance = 20;
    public $cost = 20;
    public $debrisChance = 5;
    public $debrisValue = 2;
    public $highDamage = 23;
    public $lowDamage = 14;
};
class Iron extends Cladding{
    public $typeId = 2;
    public $name = "Iron";
    public $hitResistance = 30;
    public $damageResistance = 30;
    public $cost = 30;
    public $debrisChance = 5;
    public $debrisValue = 2;
    public $highDamage = 33;
    public $lowDamage = 24;
};
class Titanium extends Cladding{
    public $typeId = 3;
    public $name = "Titanium";
    public $hitResistance = 40;
    public $damageResistance = 40;
    public $cost = 40;
    public $debrisChance = 10;
    public $debrisValue = 2;
    public $highDamage = 43;
    public $lowDamage = 34;
};
class Chromium extends Cladding{
    public $typeId = 4;
    public $name = "Chromium";
    public $hitResistance = 50;
    public $damageResistance = 50;
    public $cost = 50;
    public $debrisChance = 10;
    public $debrisValue = 2;
    public $highDamage = 53;
    public $lowDamage = 44;
};
class Steel extends Cladding{
    public $typeId = 5;
    public $name = "Steel";
    public $hitResistance = 60;
    public $damageResistance = 60;
    public $cost = 60;
    public $debrisChance = 10;
    public $debrisValue = 3;
    public $highDamage = 63;
    public $lowDamage = 54;
};
class Tungsten extends Cladding{
    public $typeId = 6;
    public $name = "Tungsten";
    public $hitResistance = 70;
    public $damageResistance = 70;
    public $cost = 70;
    public $debrisChance = 15;
    public $debrisValue = 3;
    public $highDamage = 73;
    public $lowDamage = 64;
};
class Mithril extends Cladding{
    public $typeId = 7;
    public $name = "Mithril";
    public $hitResistance = 80;
    public $damageResistance = 80;
    public $cost = 80;
    public $debrisChance = 15;
    public $debrisValue = 4;
    public $highDamage = 83;
    public $lowDamage = 74;
};
class Admantium extends Cladding{
    public $typeId = 8;
    public $name = "Admantium";
    public $hitResistance = 90;
    public $damageResistance = 590;
    public $cost = 90;
    public $debrisChance = 20;
    public $debrisValue = 4;
    public $highDamage = 93;
    public $lowDamage = 84;
};


?>