<?php

namespace REALD\ORMapping;

class OrmResCollection{

    protected $_buffer = [];

    public function put($data, $firsted = false){
        if($firsted){
            $this->_buffer = $data;
        }
        else{
            $this->_buffer[] = $data;
        }
        return $this;
    }

    public function toArray(){
        return (array)$this->_buffer;
    }

    public function out($name = null){
        return $this->_buffer;
    }
}