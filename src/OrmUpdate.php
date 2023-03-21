<?php

namespace REALD\ORMapping;

class OrmUpdate{

    private $context;
    private $_table;
    private $_query = [];

    public function __construct(&$context, $tableName){
        $this->context = $context;
        $this->_table = $tableName;
    }

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

    public function whereOr($column, $operand, $value){
        return $this->where($column, $operand, $value, "OR");
    }

    public function whereIn($column, $values, $join = "AND"){

        $this->_query[] = [
            "type"=>"wherein",
            "column"=>$column,
            "values"=>$values,
            "join"=>$join,
        ];

        return $this;
    }

    public function update($updateData){

        $bind = [];
        $updateSqlStr = "";
        $ind = 0;
        foreach($updateData as $column => $value){

            if($ind){
                $updateSqlStr .= ",";
            }

            $updateSqlStr .= $column . "=?";

            $bind[] = $value;
            $ind++;
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

        return $this;
    }

}