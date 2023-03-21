<?php

namespace REALD\ORMapping;

use PDO;

class OrmSelect{

    private $_table;
    private $_query = [];
    private $_bind = [];

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

    public function select($fields){
        $this->_query[] = [
            "type" => "select",
            "fields" => $fields,
        ];

        return $this;
    }
    
    public function orderBy($column, $sort){

        $this->_query[] = [
            "type" => "orderby",
            "column" => $column,
            "sort" => $sort,
        ];

        return $this;
    }

    public function limit($index){

        $this->_query[] = [
            "type" => "limit",
            "limit" => $index,
        ];

        return $this;
    }

    public function page($pageCount, $index){

        $this->_query[] = [
            "type" => "page",
            "pageCount" => $pageCount,
            "index"=>$index,
        ];

        return $this;        
    }

    public function join($tableName, $callback){

        $this->_query[] = [
            "type" => "join",
            "tableName" => $tableName,
            "callback"=>$callback,
        ];

        return $this;
    }

    public function union($ormObject){

        $this->_query[] = [
            "type" => "union",
            "ormObject" => $ormObject,
        ];

        return $this;
    }

    public function leftJoin($tableName, $callback){

        $this->_query[] = [
            "type" => "leftJoin",
            "tableName" => $tableName,
            "callback"=>$callback,
        ];

        return $this;
    }

    public function rightJoin($tableName, $callback){

        $this->_query[] = [
            "type" => "rightJoin",
            "tableName" => $tableName,
            "callback"=>$callback,
        ];

        return $this;
    }

    public function toSql(){

        $select = "*";
        $where = "";
        $orderby = "";
        $limit = "";
        $offset = "";
        $join = "";
        $union = "";

        foreach($this->_query as $q_){
            if($q_["type"] == "select"){
                
                if($select == "*"){
                    $select = "";
                }

                foreach($q_["fields"] as $s_){
                    if($select){
                        $select .= ", ";
                    }
                    $select .= $s_;
                }
            }
            else if($q_["type"] == "where"){

                if($where != ""){
                    $where .= " " . $q_["join"] . " ";
                }

                $where .= $q_["column"] ." ". $q_["operand"] . " ?";
                $this->_bind[] = $q_["value"];
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
                    $this->_bind[] = $q_["values"][$ind];
                }

                $where .= $q_["column"] . " IN (" . $valueStr . ")";
            }
            else if($q_["type"] == "orderby"){
                if($orderby){
                    $orderby .= ", ";
                }
                $orderby .=  $q_["column"] . " ". $q_["sort"];
            }
            else if($q_["type"] == "limit"){
                $limit = $q_["limit"];
            }
            else if($q_["type"] == "page"){
                $offset = ($q_["pageCount"] * ($q_["limit"] - 1));
                $limit = $q_["pageCount"];
            }
            else if(
                $q_["type"] == "join" || 
                $q_["type"] == "leftJoin" ||
                $q_["type"] == "rightJoin" 
            ){
                $joinSelect = new OrmSelectJoin;
                $q_["callback"]($joinSelect);

                if($q_["type"] == "join"){
                    $joinMode = "INNER";
                }
                else if($q_["type"] == "leftJoin"){
                    $joinMode = "LEFT";
                }
                else if($q_["type"] == "rightJoin"){
                    $joinMode = "RIGHT";
                }

                $join .= " ". $joinMode . " JOIN " . $q_["tableName"] ." ON ". $joinSelect->toSql();
            }
            else if($q_["type"] == "union"){
                $union = $q_["ormObject"]->toSql();
                $addBind = $q_["ormObject"] ->toBind();
                $this->_bind = array_merge($this->_bind, $addBind);
            }
        }

        $sql = "SELECT ".$select." FROM ". $this->_table;
        
        if($join){
            $sql .= $join;
        }

        if($where){
            $sql .= " WHERE ". $where;
        }

        if($union){
            $sql .= " UNION ". $union;
        }

        if($orderby){
            $sql .= " ORDER BY ". $orderby;
        }

        if($limit){
            if($offset){
                $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
            }
            else{
                $sql .= " LIMIT " . $limit;
            }
        }

        return $sql;
    }

    public function toBind(){
        return $this->_bind;
    }

    public function get($first = false){
        $sql = $this->toSql();

        $std = $this->context->query($sql, $this->_bind);
        
        $res = new OrmResCollection;

        while($row = $std->fetch(PDO::FETCH_OBJ)){
            $res->put($row, $first);
        }

        // bind reset
        $this->_bind = [];

        return $res;
    }

    public function first(){   
        
        $this->limit(1);

        return $this->get(true);
    }

    public function count(){
        $res = $this->select(["count(*) as count"])
            ->first();

        return $res->out()->count;
    }

    public function lists($keyName, $valueName){

        $this->select([$keyName, $valueName]);

        $buffer = $this->get()->out();

        $res = [];

        foreach($buffer as $b_){
            $res[$b_->{$keyName}] = $b_->{$valueName};
        }

        return $res;
    }

}

class OrmSelectJoin{

    private $_query = [];
    private $_bind = [];

    public function on($column, $operand, $value, $join = "AND"){

        $this->_query[] = [
            "type"=>"on",
            "column"=>$column,
            "operand"=>$operand,
            "value"=>$value,
            "join"=>$join,
        ];

        return $this;
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

    public function toSql(){

        $where = "";

        foreach($this->_query as $q_){
            if($q_["type"] == "where" || $q_["type"] == "on"){

                if($where != ""){
                    $where .= " " . $q_["join"] . " ";
                }

                if($q_["type"] == "on"){
                    $where .= $q_["column"] ." ". $q_["operand"] . " " . $q_["value"];
                }
                else if($q_["type"] == "where"){
                    $where .= $q_["column"] ." ". $q_["operand"] . " ?";
                }
                
                $this->_bind[] = $q_["value"];
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
                    $this->_bind[] = $q_["values"][$ind];
                }

                $where .= $q_["column"] . " IN (" . $valueStr . ")";
            }
        }

        return $where;
    }

}