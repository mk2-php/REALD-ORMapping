<?php

namespace Reald\Orm;

class OrmResCollection{

    protected $_buffer = [];
    protected $_pageTotal = null;

    public function put($data, $firsted = false){
        if($firsted){
            $this->_buffer = $data;
        }
        else{
            $this->_buffer[] = $data;
        }
        return $this;
    }

    public function putTotalPage($totalPage){
        $this->_pageTotal = $totalPage;
    }

    public function toArray(){
        return (array)$this->_buffer;
    }

    public function out($name = null){
        return $this->_buffer;
    }

    public function getTotalPage(){
        return $this->_pageTotal;
    }
}