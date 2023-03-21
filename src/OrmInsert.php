<?php

namespace REALD\ORMapping;

class OrmInsert{

    private $context;
    private $_table;

    public function __construct(&$context, $tableName){
        $this->context = $context;
        $this->_table = $tableName;
    }

    public function insert($insertData){

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

        $sql = "INSERT INTO ". $this->_table. "(" . $columns .") VALUES (" . $values .")";

        $this->context->query($sql, $bind);

        return $this;
    }

    public function lastInsertId(){
        return $this->context->_pdo->lastInsertId();
    }
}