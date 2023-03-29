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
     * queryReset
     * @return OrmSelect $this
     */
    public function queryReset(){
        $this->_query = [];
        return $this;
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
    
    /**
     * orderBy
     * 
     * Method for sorting record list acquisition.
     * Generate ORDER BY clause in requesting SQL.
     * 
     * @param String $column Sort target column name
     * @param String $sort Specify ascending/descending order (asc/desc)
     * @return OrmSelect $this
     */
    public function orderBy($column, $sort){

        $this->_query[] = [
            "type" => "orderby",
            "column" => $column,
            "sort" => $sort,
        ];

        return $this;
    }

    /**
     * limit
     * 
     * Specify ascending/descending order
     * Generate LIMIT clause for request SQL
     * 
     * @param Int $limit Record extraction limit number of records
     * @param Int $offset Record acquisition start position
     * @return OrmSelect $this
     */
    public function limit($limit, $offset = null){

        $this->_query[] = [
            "type" => "limit",
            "limit" => $limit,
            "offset" => $offset,
        ];

        return $this;
    }

    /**
     * join
     * 
     * Methods for inner joining with other tables
     * Generate INNER JOIN clause in request SQL
     * 
     * @param String $tableName table name to join
     * @param function $callback A callback that specifies the join condition
     * @return OrmSelect $this
     */
    public function join($tableName, $callback){

        $this->_query[] = [
            "type" => "join",
            "tableName" => $tableName,
            "callback"=>$callback,
        ];

        return $this;
    }

    /**
     * leftJoin
     * 
     * Methods for outer joining with other tables.
     * Generate LEFT JOIN clause in request SQL
     * 
     * @param String $tableName table name to join
     * @param function $callback A callback that specifies the join condition
     * @return OrmSelect $this
     */
    public function leftJoin($tableName, $callback){

        $this->_query[] = [
            "type" => "leftJoin",
            "tableName" => $tableName,
            "callback"=>$callback,
        ];

        return $this;
    }

    /**
     * rightJoin
     * 
     * Methods for outer joining with other tables.
     * Generate RIGHT JOIN clause in request SQL
     * 
     * @param String $tableName table name to join
     * @param function $callback A callback that specifies the join condition
     * @return OrmSelect $this
     */
    public function rightJoin($tableName, $callback){

        $this->_query[] = [
            "type" => "rightJoin",
            "tableName" => $tableName,
            "callback"=>$callback,
        ];

        return $this;
    }

    /**
     * union
     * 
     * Integrate other search results
     * Generate a UNION clause in the request SQL
     * 
     * @param OrmSelect $ormObject OrmSelect class object for other search criteria
     * @return OrmSelect $this
     */
    public function union($ormObject){

        $this->_query[] = [
            "type" => "union",
            "ormObject" => $ormObject,
        ];

        return $this;
    }

    /**
     * toSql
     * 
     * A method that generates a SQL statement.
     * Automatically generate SQL statements based on search conditions specified by various method chains
     * 
     * @return String SQL Code
     */
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

    /**
     * toBind
     * 
     * Get bound value
     * Check the bound value in the toSQL() method here
     * 
     * @return Array bind data
     */
    public function toBind(){
        return $this->_bind;
    }

    /**
     * get
     * 
     * Get table record information.
     * 
     * @param Boolean $first Flag to respond only the first record
     * @return OrmResCollection Record acquisition result class
     */
    public function get($first = false){

        // Execute pre-record handler
        $this->context->handleSelectBefore($this);

        $sql = $this->toSql();

        $std = $this->context->query($sql, $this->_bind);
        
        if($first){
            $row = $std->fetch(PDO::FETCH_OBJ);
            $res = new OrmResCollection($row);
        }
        else{
            $res = [];
            while($row = $std->fetch(PDO::FETCH_OBJ)){
                $res[] = new OrmResCollection($row);
            }
        }

        // bind reset
        $this->_bind = [];

        // query reset
        $this->queryReset();

        // Execute handler after record retrieval
        $resBuff = $this->context->handleSelectAfter($res);

        if($resBuff){
            // Overwrite the response if there is a return value from the handler
            $res = $resBuff;
        }

        return $res;
    }

    /**
     * paginate
     * 
     * Record retrieval including paging results
     * 
     * @param Int $pageCount Number of records per page
     * @param Int $pageIndex page number to refer to
     * @return OrmResCollection Record acquisition result class
     */
    public function paginate($pageCount, $pageIndex){

        $totalCount = $this->count();

        $this->limit($pageCount, $pageCount * ($pageIndex - 1));

        $buff = $this->get();

        $res = new OrmResPaginate($buff, $totalCount, $pageCount);

        return $res;
    }

    /**
     * first
     * 
     * Get only one record information
     * 
     * @return OrmResCollection Record acquisition result class
     */
    public function first(){   
        $this->limit(1);
        return $this->get(true);
    }

    /**
     * count
     * 
     * Output the number of acquired records
     * 
     * @return Int number of records
     */
    public function count(){
        $res = $this
            ->select(["count(*) as count"])
            ->first()
        ;
        return $res->count;
    }

    /**
     * lists
     * 
     * Get record results in list format
     * 
     * @param String $keyName Key column name
     * @param String $valueName column name as value
     * @return Array list data
     */
    public function lists($keyName, $valueName){

        $this->select([$keyName, $valueName]);

        $buffer = $this->get();

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