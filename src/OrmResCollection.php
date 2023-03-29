<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmResCollection
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Record acquisition result class.
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

class OrmResCollection{

    private $_buffer;

    /**
     * __construct
     * 
     * @param stdClass $data
     */
    public function __construct($data){
        $this->_buffer = $data;
    }

    /**
     * __get
     * 
     * @param String $name
     */
    public function __get($name){
        if(!empty($this->_buffer->{$name})){
            return $this->_buffer->{$name};
        }
    }

    /**
     * __set
     * 
     * @param String $name
     */
    public function __set($name, $value){
        $this->_buffer->{$name} = $value;
    }

    /**
     * toArray
     * 
     * Converts the record acquisition result to an associative array and outputs it.
     * 
     * @return Array
     */
    public function toArray(){
        return (array)$this->_buffer;
    }
}