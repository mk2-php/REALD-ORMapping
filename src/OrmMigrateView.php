<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmMigrateDatabase
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Database view management class.
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

class OrmMigrateView{

    private $context;
    private $_view;

    /**
     * __construct
     * 
     * Constructor for OrmMigrateView class.
     * 
     * @param Orm &$context Orm class as context
     * @param String $ViewName View name
     */
    public function __construct(&$context, $ViewName){
        $this->context = $context;
        $this->_view = $ViewName;
    }

    /**
     * create
     * 
     * Methods for creating database views
     * 
     * @param String $viewSql SQL for views (sub-query)
     * @param Array $option View setting option information
     * @return OrmMigrateView $this
     */
    public function create($viewSql, $option = []){

        $ifNotExists = "";
        if(!empty($option["ifNotExists"])){
            $ifNotExists = "IF NOT EXISTS";
        }

        $sql = "CREATE VIEW " . $ifNotExists . " " . $this->_view . " AS " . $viewSql;

        $this->context->query($sql);

        return $this;
    }

    /**
     * drop
     * 
     * Methods for deleting database views
     * 
     * @param Boolean $ifExistsFlg Flag for adding "IF EXISTS" statement
     * @return OrmMigrateView $this
     */
    public function drop($ifExistsFlg = false){
        
        $ifExists = "";
        if($ifExistsFlg){
            $ifExists = "IF EXISTS";
        }

        $sql = "DROP VIEW " . $ifExists ." ". $this->_view;

        $this->context->query($sql);

        return $this;
    }
}