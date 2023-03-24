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

    protected $_buffer = [];
    protected $_pageTotal = null;

    /**
     * put
     * 
     * @param $data
     * @param $firsted
     */
    public function put($data, $firsted = false){
        if($firsted){
            $this->_buffer = $data;
        }
        else{
            $this->_buffer[] = $data;
        }
        return $this;
    }

    /**
     * putTotalPage
     * 
     * @param $totalPath
     */
    public function putTotalPage($totalPage){
        $this->_pageTotal = $totalPage;
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

    /**
     * out
     * 
     * Output the record acquisition result as it is.
     * 
     * @return StdClass
     */
    public function out($name = null){
        return $this->_buffer;
    }

    /**
     * getTotalPage
     * 
     * Get total number of pages.
     * 
     * @return int
     */
    public function getTotalPage(){
        return $this->_pageTotal;
    }
}