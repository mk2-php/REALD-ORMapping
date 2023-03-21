<?php

namespace REALD\ORMapping;

use PDO;

class Orm{

    public $tableName;
    public $_pdo;
    public $drive = "normal";

    public function __construct($option = null){

        if(!$option){
            return;
        }

        $this->setDatabase($option);
    }

    public function setDatabase($driveName, $option = []){
        OrmStatic::addConnect($driveName, $option);
        return $this;
    }

    public function query($sql, $bind = []){
        return OrmStatic::query($this->drive, $sql, $bind);
    }

    public function queryConvert($std){
        $res = [];
        while($row = $std->fetch(PDO::FETCH_OBJ)){
            $res[] = $row;
        }
        return $res;
    }

    public function log(){
        return OrmStatic::log();
    }

    public function select($option = null){
        
        $ormSelect = new OrmSelect($this, $this->tableName);

        if($option){
            return $ormSelect->select($option);
        }
        else{
            return $ormSelect;
        }
    }

    public function database($database){
        return new OrmMigrateDatabase($this, $database);
    }

    public function table($tableName = null){
        if($tableName){
            $this->tableName = $tableName;
        }
        return new ormMigrateTable($this, $tableName);
    }

    public function view($viewName){
        return new ormMigrateView($this, $viewName);
    }

    public function insert($option = null){
        $ormInsert = new OrmInsert($this, $this->tableName);
        
        if($option){
            return $ormInsert->insert($option);
        }
        else{
            return $ormInsert;
        }
    }

    public function update(){
        $ormUpdate = new OrmUpdate($this, $this->tableName);
        return $ormUpdate;
    }

    public function begin(){
        OrmStatic::transaction(OrmStatic::TRANSACTION_BEGIN);
        return $this;
    }

    public function commit(){
        OrmStatic::transaction(OrmStatic::TRANSACTION_COMMIT);
        return $this;
    }

    public function rollback(){
        OrmStatic::transaction(OrmStatic::TRANSACTION_ROLLBACK);
        return $this;
    }

}
