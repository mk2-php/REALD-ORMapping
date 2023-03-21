<?php

namespace REALD\ORMapping;

class OrmMigrateTable{

    private $context;
    private $_table;

    public function __construct(&$context, $tableName){
        $this->context = $context;
        $this->_table = $tableName;
    }

    public function create($option = [], $tableOpt = []){

        $ifNotExists = "";
        $comment = "";
        $collate = "";
        $engine = "";

        if(!empty($tableOpt["ifNotExists"])){
            $ifNotExists = "IF NOT EXISTS";
        }

        if(!empty($tableOpt["comment"])){
            $comment = "COMMENT '". $tableOpt["comment"]."'";
        }

        if(!empty($tableOpt["collate"])){
            $collate = "COLLATE ". $tableOpt["collate"];
        }
        else{
            $collate = "COLLATE utf8mb4_general_ci";
        }

        if(!empty($tableOpt["ENGINE"])){
            $engine = "ENGINE = " . $tableOpt["ENGINE"];
        }
        else{
            $engine = "ENGINE = InnoDB";
        }

        $sqlColumns = "";
        $ind = 0;
        $primaryKeys = [];
        foreach($option as $column => $o_){

            if($ind){
                $sqlColumns .= ",";
            }

            $buffStr = $this->_setColumn($column, $o_);

            if(isset($o_["primaryKey"])){
                $primaryKeys[] = $column;
            }

            $sqlColumns .= $buffStr;

            $ind++;
        }

        if($primaryKeys){
            $sqlColumns .= ", PRIMARY KEY(". join(",", $primaryKeys) . ") ";
        }

        $sql = "CREATE TABLE ". $ifNotExists . " " . $this->_table . "(" . $sqlColumns . ") ";

        if($engine){
            $sql .= $engine . " ";
        }

        if($collate){
            $sql .= $collate ." ";            
        }

        if($comment){
            $sql .= $comment ." ";
        }

        $this->context->query($sql);

        return $this;
    }

    public function addColumn($option = []){

        $columOptionStr = "";
        $ind = 0;
        foreach($option as $column => $o_){
            
            if($ind){
                $columOptionStr .= ",";
            }

            $offset = "";
            if(!empty($o_["before"])){
                $offset = "BEFORE ". $o_["before"];
            }
            else if(!empty($o_["after"])){
                $offset = "AFTER ". $o_["after"];
            }

            $columOptionStr .= " ADD COLUMN " . $this->_setColumn($column, $o_) ." " .$offset;

            $ind++;
        }

        $sql = "ALTER TABLE ". $this->_table . $columOptionStr;

        $this->context->query($sql);

        return $this;
    }

    
    public function changeColumn($option = []){

        $columOptionStr = "";
        $ind = 0;
        foreach($option as $column => $o_){
        
            if(empty($o_["before"])){
                continue;
            }

            if($ind){
                $columOptionStr .= ",";
            }

            $before_column = $o_["before"];

            $columOptionStr .= " CHANGE " . $before_column . " " . $this->_setColumn($column, $o_);

            $ind++;
        }

        $sql = "ALTER TABLE ". $this->_table . $columOptionStr;      

        $this->context->query($sql);

        return $this;

    }

    public function dropColumn($option = []){

        $delColumStr = "";
        $ind = 0;
        foreach($option as $o_){

            if($ind){
                $delColumStr .= ",";
            }

            $delColumStr .= " DROP COLUMN " . $o_;           

            $ind++;
        }

        $sql = "ALTER TABLE ". $this->_table . $delColumStr;      

        $this->context->query($sql);

        return $this;

    }

    private function _setColumn($column, $option){

        $buffStr = $column . " ";
        if(isset($option["type"])){
            $buffStr .= $option["type"];                
            if(isset($option["length"])){
                $buffStr .= "(" . $option["length"] . ")";
            }
            $buffStr .= " ";
        }

        if(isset($option["notNull"])){
            $buffStr .= "NOT NULL ";
        }

        if(isset($option["default"])){
            $buffStr .= "DEFAULT '". $option["default"] ."' ";
        }
        
        if(isset($option["autoIncrement"])){
            $buffStr .= "AUTO_INCREMENT ";
        }

        if(isset($option["comment"])){
            $buffStr .= "COMMENT '" . $option["comment"] ."' ";
        }

        return $buffStr;
    }



    public function drop($ifExistsFlg = false){

        $ifExists = "";

        if($ifExistsFlg){
            $ifExists = "IF EXISTS";
        }

        $sql = "DROP TABLE " . $ifExists . " " . $this->_table;

        $this->context->query($sql);

        return $this;
    }

    public function insert($option = null){
        return $this->context->insert($option);
    }
}