<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmSelect
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Dedicated class for updating table records.
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

class OrmUpdate{

    private $context;
    private $_table;
    private $_query = [];

    /**
     * __construct
     * 
     * Constructor for OrmMigrateView class.
     * 
     * @param Orm &$context Orm class as context
     * @param String $tableName table name
     */
    public function __construct(&$context, $tableName){
        $this->context = $context;
        $this->_table = $tableName;
    }

    /**
     * where
     * 
     * A method that specifies the conditions for the records to be updated.
     * Generate WHERE clause in request SQL.
     * 
     * @param String $column Condition column name
     * @param String $operand operator
     * @param String $value conditional value
     * @param String $join Logical operators with other conditions (AND/OR)
     * @return OrmUpdate $this
     */
    public function where($column, $operand, $value, $join = "AND"){

        $this->_query[] = [
            "type"=>"where",
            "column"=>$column,
            "operand"=>$operand,
            "value"=>$value,
            "join"=>$join,
        ];

        return $this;
    }

    /**
     * whereRaw
     * 
     * Raw with Conditional specification of records to be updated.
     * 
     * @param String $raw SQL conditional statemen(raw
     * @param String $join Logical operators with other conditions (AND/OR)
     * @return OrmUpdate $this
     */
    public function whereRaw($raw, $join = "AND"){

        $this->_query[] = [
            "type"=>"whereraw",
            "raw"=>$raw,
            "join"=>$join,
        ];

        return $this;
    }

    /**
     * whereOr
     * 
     * Condition specification method for update target record
     * Combine with other condition with OR condition
     * 
     * @param String $column Condition column name
     * @param String $operand operator
     * @param String $value conditional value
     * @return OrmUpdate $this
     */
    public function whereOr($column, $operand, $value){
        return $this->where($column, $operand, $value, "OR");
    }

    /**
     * whereIn
     * 
     * 
     * 
     * @param String $column Condition column name
     * @param String $values Condition value (specified by array value)
     * @param String $join Logical operators with other conditions (AND/OR)
     * @return OrmUpdate $this
     */
    public function whereIn($column, $values, $join = "AND"){

        $this->_query[] = [
            "type"=>"wherein",
            "column"=>$column,
            "values"=>$values,
            "join"=>$join,
        ];

        return $this;
    }

    /**
     * update
     * 
     * Method for record update processing
     * 
     * @param Array $updateData record update
     * @return OrmUpdate 
     */
    public function update($updateData){

        // Execute pre-record update handler
        $buff = $this->context->handleUpdateBefore($updateData);
        if($buff){
            // Overwrite the response if there is a return value from the handler
            $updateData = $buff;
        }

        $bind = [];
        $updateSqlStr = "";
        $ind = 0;
        foreach($updateData as $column => $value){

            if($ind){
                $updateSqlStr .= ",";
            }

            $updateSqlStr .= $column . "= ?";

            $bind[] = $value;
            $ind++;
        }

        if($this->context->updateDateColumn){

            if($ind){
                $updateSqlStr .= ",";
            }

            $updateSqlStr .= $this->context->updateDateColumn . "= ?";
            $bind[] = Date("Y/m/d H:i:s");
        }

        $where = "";

        foreach($this->_query as $q_){
            if($q_["type"] == "where"){

                if($where != ""){
                    $where .= " " . $q_["join"] . " ";
                }

                $where .= $q_["column"] ." ". $q_["operand"] . " ?";
                $bind[] = $q_["value"];
            }
            else if($q_["type"] == "wherein"){

                if($where != ""){
                    $where .= " " . $q_["join"] . " ";
                }

                $valueStr = "";
                for($ind = 0 ; $ind < count($q_["values"]) ; $ind++){
                    if($ind){
                        $valueStr .= ",";
                    }
                    $valueStr .= "?";
                    $bind[] = $q_["values"][$ind];
                }

                $where .= $q_["column"] . " IN (" . $valueStr . ")";
            }
        }

        $sql = "UPDATE ". $this->_table . " SET ". $updateSqlStr. " WHERE ". $where;

        $this->context->query($sql, $bind);

        // Execute handler after record update is completed
        $this->context->handleUpdateAfter($this);

        return $this;
    }

}