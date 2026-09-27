<?php
class clusterRocket
{
// Properties
    public $id;
    public $cluster = [];

    // Methods

    // Copies each `$property`/`$argument` pair from `$arguments` onto this cluster rocket; returns nothing.
    public function __construct(array $arguments = array()) {
        
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                
                $this->{$property} = $argument;
            }
        }
        
    }
}


?>