<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmTrait
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Orm class trait.
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

// require if you didn't use a package management tool such as "commposer".
require_once "OrmStatic.php";
require_once "OrmSelect.php";
require_once "OrmMigrateDatabase.php";
require_once "OrmMigrateTable.php";
require_once "OrmMigrateView.php";
require_once "OrmInsert.php";
require_once "OrmUpdate.php";
require_once "OrmResCollection.php";

use PDO;

trait OrmTrait{

    public $tableName;
    public $_pdo;
    public $drive = "normal";

    public $createDateColumn = null;
    public $updateDateColumn = null;

    /**
     * __construct
     * 
     * Constructor for Orm class.
     * If you specify the argument $option, it becomes an independent connection destination.
     * 
     * @param $option = null Destination database information
     */
    public function __construct($option = null){

        if($option){
            $this->drive = hash("sha256", uniqId().date("YmdHis"));
            $this->setDatabase($this->drive, $option);
        }

    }

    /**
     * setDatabase

     * This is a method to change the connection destination of the database.
     * If you do not specify the argument $option, 
     * it will switch to the already established connection destination

     * @param $drivename Connection name of the connection destination database
     * @param $option Database connection destination information
     * @return $this 
     */
    public function setDatabase($driveName, $option = []){
        
        if($option){
            OrmStatic::addConnect($driveName, $option);
        }

        $this->drive = $driveName;

        return $this;
    }

    /**
     * existDriver
     * 
     * @return Boolean
     */
    public function existDriver(){
        return OrmStatic::existDriver($this->drive);
    }

    /**
     * query
     * 
     * Method used to send SQL requests directly to the database.
     * Returns a PDOStatment object similar to PDO::query.
     * 
     * @param $sql SQL code to send.
     * @param $bind = [] Value to bind (specified by array value)
     * @return PDOStatement PDOStatement object
     */
    public function query($sql, $bind = []){
        return OrmStatic::query($this->drive, $sql, $bind);
    }

    /**
     * queryConvert
     * 
     * Methods to get results from PDOStatement objects.
     * 
     * @param $std PDOStatement object
     * @return Array Listed results from a PDOStatement object
     */
    public function queryConvert($std){
        $res = [];
        while($row = $std->fetch(PDO::FETCH_OBJ)){
            $res[] = $row;
        }
        return $res;
    }

    /**
     * log
     * 
     * Prints a log of all SQL requests.
     * @return Array SQL send log list
     */
    public function log(){
        return OrmStatic::log();
    }

    /**
     * select
     * 
     * Methods for retrieving table records
     * OrmSelect object is returned as a return value.
     * @param Array $option = null If the acquisition target column is specified, specify it with an array value
     * @return OrmSelect OrmSelect Class Object
     */
    public function select($option = null){
        
        // OrmSelect class object initialization
        $ormSelect = new OrmSelect($this, $this->tableName);

        if($option){
            return $ormSelect->select($option);
        }
        else{
            return $ormSelect;
        }
    }

    /**
     * database
     * 
     * Methods for performing database operations
     * Returns OrmMigrateDatabase object as return value
     * @param String $database database name
     * @return OrmMigrateDatabase OrmMigrateDatabase Class Object
     */
    public function database($database){
        // OrmMIgrateDatabase class object initialization
        return new OrmMigrateDatabase($this, $database);
    }

    /**
     * table
     * 
     * Methods for performing table operations.(create, alter, drop ete)
     * Returns OrmMigrateTable class object as return value
     * 
     * @param String $tableName = null table name(Use default table name if not specified)
     * @return OrmMigrateTable OrmMigrateTable Class Object
     */
    public function table($tableName = null){

        if($tableName){
            $this->tableName = $tableName;
        }

        // ormMigrateTable class object initialization
        return new OrmMigrateTable($this, $tableName);
    }

    /**
     * view
     * 
     * Methods for database view management.
     * Returns OrmMigrateView class object as return value
     * 
     * @param String $viewName
     * @return OrmMigrateView OrmMigrateView Class Object
     */
    public function view($viewName){
        return new OrmMigrateView($this, $viewName);
    }

    /**
     * insert
     * 
     * Method for registering records to the table
     * Returns OrmInsert class object as return value
     * 
     * @param Array $option = null Data to record
     * @return OrmInsert OrmInsert Class Object
     */
    public function insert($option = null){
        $ormInsert = new OrmInsert($this, $this->tableName);
        
        if($option){
            return $ormInsert->insert($option);
        }
        else{
            return $ormInsert;
        }
    }

    /**
     * update
     * 
     * A method for updating records to the database table
     * Returns OrmUpdate class object as return value
     * @return OrmUpdate OrmUpdate Class Object
     */
    public function update(){
        $ormUpdate = new OrmUpdate($this, $this->tableName);
        return $ormUpdate;
    }

    /**
     * begin
     * 
     * Start a transaction.
     * Transaction starts are queued for all connected drivers using realdOrm.
     * @return $this
     */
    public function begin(){
        OrmStatic::transaction(OrmStatic::TRANSACTION_BEGIN);
        return $this;
    }

    /**
     * commit
     * 
     * Commit a transaction.
     * Transaction commit are queued for all connected drivers using realdOrm.
     * @return $this
     */
    public function commit(){
        OrmStatic::transaction(OrmStatic::TRANSACTION_COMMIT);
        return $this;
    }

    /**
     * rollback
     * 
     * Rollback a transaction.
     * Transaction rollback are queued for all connected drivers using realdOrm.
     * @return $this
     */
    public function rollback(){
        OrmStatic::transaction(OrmStatic::TRANSACTION_ROLLBACK);
        return $this;
    }

    /**
     * handleSelectBefore
     * 
     * This handler is executed before the record retrieval process starts
     * By overriding this handler, you can insert a callback before the acquisition process.
     * 
     * @param OrmSelect $std OrmSelect Class Object
     * @return void
     */
    public function handleSelectBefore($std){}

    /**
     * handleSelectAfter
     * 
     * This handler is executed after the record retrieval process starts
     * By overriding this handler, you can insert a callback after the acquisition process.
     * * If the OrmResCollection class object of the argument is returned as 
     *   the return value, the returned content will be the actual output result.
     * 
     * @param OrmResCollection $response OrmResCollection Class Object
     * @return void
     */
    public function handleSelectAfter($response){}

    /**
     * handleInsertBefore
     * 
     * 
     * 
     * @param Array $insertData insert data
     * @return void
     */
    public function handleInsertBefore($insertData){}
    
    /**
     * handleInsertAfter
     * 
     * @param OrmInsert OrmInsert class object
     * @return void
     */
    public function handleInsertAfter($std){}

    /**
     * handleUpdateBefore
     * 
     * 
     * @param Array $oupdateData update data
     * @return void
     */
    public function handleUpdateBefore($updateData){}

    /**
     * handleUpdateAfter
     * 
     * 
     * @param OrmUpdate OrmUpdate Class Object
     * @return void
     */
    public function handleUpdateAfter($std){}

}