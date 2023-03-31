<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmInsert
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * A class for registering records in a database table.
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

class OrmInsert{

    private $context;
    private $_table;

    /**
     * __construct
     * 
     * Constructor of this class
     * 
     * @param Orm &$context Orm class as context
     * @param String $tablename Table name for record registration
     */
    public function __construct(&$context, $tableName){
        $this->context = $context;
        $this->_table = $tableName;
    }

    /**
     * insert
     * 
     * Method for registering records.
     * 
     * @param $insertData Record information to be registered
     * @return $this
     */
    public function insert($insertData){

        // Execution of pre-record handler
        $buff = $this->context->handleInsertBefore($insertData);
        if($buff){
            // Overwrite the response if there is a return value from the handler
            $insertData = $buff;
        }

        $bind = [];
        $columns = "";
        $values = "";
        $ind = 0;

        foreach($insertData as $column => $value){

            if($ind){
                $columns .= ",";
                $values .= ",";
            }

            $columns .= $column;
            $values .= "?";

            $bind[] = $value;

            $ind++;
        }   

        $nowDate = Date("Y/m/d H:i:s");

        if($this->context->createDateColumn){
            if($ind){
                $columns .= ",";
                $values .= ",";
            }
            $columns .= $this->context->createDateColumn;
            $values .= "?";
            $bind[] = $nowDate;
        }

        if($this->context->updateDateColumn){
            if($ind){
                $columns .= ",";
                $values .= ",";
            }
            $columns .= $this->context->updateDateColumn;
            $values .= "?";
            $bind[] = $nowDate;
        }

        $sql = "INSERT INTO ". $this->_table. "(" . $columns .") VALUES (" . $values .")";

        $res = $this->context->query($sql, $bind);

        // Execution of handler after record registration is completed
        $this->context->handleInsertAfter($this);

        return $this;
    }

    /**
     * lastInsertId
     * 
     * Get insertID after record registration.
     * + This method is only available for tables with auto increment as the primary ID
     * 
     * @return int last insertID
     */
    public function lastInsertId(){
        return $this->context->getConnect()->lastInsertId();
    }
}