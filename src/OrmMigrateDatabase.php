<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmMigrateDatabase
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Dedicated class for database operations (create, drop, etc.)
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

class OrmMigrateDatabase{

    private $_database;

    /**
     * __construct
     * 
     * Constructor for OrmMigrateDatabase class.
     * 
     * @param Orm &$context Orm class as context
     * @param String $database Database name
     */
    public function __construct(&$context, $database){
        $this->context = $context;
        $this->_database = $database;
    }

    /**
     * create
     * 
     * Methods for creating database.
     * 
     * @param Array $option Build database configuration option information
     * @return OrmMigrateDatabase $this
     */
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

    /**
     * drop
     * 
     * Methods for deleting database
     * 
     * @param Boolean $ifExistsFlg Flag for adding "IF EXISTS" statement
     * @return OrmMigrateDatabase $this
     */
    public function drop($ifExistsFlg = false){

        $ifExists = "";

        if($ifExistsFlg){
            $ifExists = "IF EXISTS";
        }

        $sql = "DROP DATABASE " . $ifExists . " " . $this->_database;

        $this->context->query($sql);

        return $this;
    }

    /**
     * use
     * 
     * Select target database
     * @return OrmMigrateDatabase $this
     */
    public function use(){

        $sql = "USE ". $this->_database;

        $this->context->query($sql);

        return $this;
    }

    /**
     * exists
     * 
     * Existence check of target database
     * @return Boolean Database existence check result
     */
    public function exists(){

        $sql = "show databases;";

        $std = $this->context->query($sql);

        $res = $this->context->queryConvert($std);

        $exists = false;
        
        foreach($res as $r_){
            if($r_->Database == $this->_database){
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    /**
     * show
     * 
     * Get database information details
     * 
     * @param Boolean $ifNotExistsFlg Flag for adding "IF NOT EXISTS" statement
     * @return Array Database details
     */
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