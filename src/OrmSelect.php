<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * OrmSelect
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Class for extracting record information from table.
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */

namespace Reald\Orm;

use PDO;

class OrmSelect{

    private $_table;
    private $_query = [];
    private $_bind = [];
    private $_paging = null;

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
     * Method for specifying record extraction conditions
     * 
     * @param String $column Condition column name
     * @param String $operand operator
     * @param String $value conditional value
     * @param String $join Logical operators with other conditions (AND/OR)
     * @return OrmSelect $this
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
     * Raw with record extraction condition specification.
     * 
     * @param String $raw SQL conditional statemen(raw
     * @param String $join Logical operators with other conditions (AND/OR)
     * @return OrmSelect $this
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
     * Method for specifying record extraction conditions
     * Combine with other condition with OR condition
     * 
     * @param String $column Condition column name
     * @param String $operand operator
     * @param String $value conditional value
     * @return OrmSelect $this
     */
    public function whereOr($column, $operand, $value){
        return $this->where($column, $operand, $value, "OR");
    }


    /**
     * whereIn
     * 
     * Multi-value condition specification using IN clause.
     * 
     * @param String $column Condition column name
     * @param String $values Condition value (specified by array value)
     * @param String $join Logical operators with other conditions (AND/OR)
     * @return OrmSelect $this
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
     * select
     * 
     * Methods for Enumeration of Extraction Columns
     * Used for output by partial extraction or CASE statement instead of all columns
     * 
     * @param Array $fiels Output columns (specified in an array)
     * @return OrmSelect $this
     */
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

    public function limit($limit, $offset = null){

        $this->_query[] = [
            "type" => "limit",
            "limit" => $limit,
            "offset" => $offset,
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
            else if($q_["type"] == "whereraw"){

                if($where != ""){
                    $where .= " " . $q_["join"] . " ";
                }

                $where .= $q_["raw"];
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
                if($q_["offset"]){
                    $offset = $q_["offset"];
                }
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

                $_bind = $joinSelect->toBind();

                foreach($_bind as $b_){
                    $this->_bind[] = $b_;
                }
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

        if($this->_paging){
            $res->putPaging($this->_paging);
        }

        // bind reset
        $this->_bind = [];

        return $res;
    }

    public function paginate($pageCount, $pageIndex){

        $totalCount = $this->count();

        $this->_deleteLastQuery();
        $this->_deleteLastQuery();

        $this->limit($pageCount, $pageCount * ($pageIndex - 1));

        $res = $this->get();

        $res->putTotalPage(ceil($totalCount/$pageCount));

        return $res;
    }

    private function _deleteLastQuery(){
        unset($this->_query[count($this->_query)-1]);
        return $this;   
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
                
                if($q_["type"] == "where"){
                    $this->_bind[] = $q_["value"];
                }
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

    public function toBind(){
        return $this->_bind;
    }
}