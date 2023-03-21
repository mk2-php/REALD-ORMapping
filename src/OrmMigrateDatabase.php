<?php

namespace REALD\ORMapping;

class OrmMigrateDatabase{

    private $_database;

    public function __construct(&$context, $database){
        $this->context = $context;
        $this->_database = $database;
    }

    public function create($option = null){

        $character = "";
        $collate = "";

        if(is_array($option)){

            if(!empty($option["character"])){
                $character = " CHARACTER SET " . $option["character"];
            }
            if(!empty($option["collate"])){
                $collate = " COLLATE " . $option["collate"];
            }
        }

        $sql = "CREATE DATABASE IF NOT EXISTS ". $this->_database;

        if($character){
            $sql .= $character;
        }

        if($collate){
            $sql .= $collate;
        }
        
        $this->context->query($sql);

        return $this;
    }

    public function drop($ifExistsFlg = false){

        $ifExists = "";

        if($ifExistsFlg){
            $ifExists = "IF EXISTS";
        }

        $sql = "DROP DATABASE " . $ifExists . " " . $this->_database;

        $this->context->query($sql);

        return $this;
    }

    public function use(){

        $sql = "USE ". $this->_database;

        $this->context->query($sql);

        return $this;
    }

    public function show($ifNotExistsFlg = false){

        $ifNotExists = "";
        if($ifNotExistsFlg){
            $ifNotExists = "IF NOT EXISTS";
        }

        $sql ="SHOW CREATE DATABASE " . $ifNotExists . " " . $this->_database;

        $std = $this->context->query($sql);

        return $this->context->queryConvert($std);
    }
}