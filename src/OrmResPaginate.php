<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmResPaginate
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Record acquisition result class. (on Paginate)
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

class OrmResPaginate{

    private $_ormResCollection;
    private $_buffer = [];

    public function __construct($_ormResCollection, $totalCount, $pageIndex){
        $this->_ormResCollection = $_ormResCollection;
        $this->totalPage = ceil($totalCount/$pageIndex);
        $this->pageIndex = $pageIndex;
        $this->total = $totalCount;
    }

    public function __get($name){
        if(!empty($this->_buffer[$name])){
            return $this->_buffer[$name];
        }
    }

    public function __set($name, $value){
        return $this->_buffer[$name] = $value;
    }

    public function get(){
        return $this->_ormResCollection;
    }
    
    public function toArray(){

        $_ormResCollection = [];
        foreach($this->_ormResCollection as $r_){
            $_ormResCollection[] = $r_->toArray();
        }
        
        return [
            "list" => $_ormResCollection,
            "totalPage" => $this->totalPage,
            "pageIndex" => $this->pageIndex,
            "total" => $this->total,
        ];
    }
}