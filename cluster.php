<?php
class clusterRocket
{
// Properties
    public $id;
    public $cluster = [];

    // Methods

    public function __construct(array $arguments = array()) {
        
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                
                $this->{$property} = $argument;
            }
        }
        
    }
}


?>