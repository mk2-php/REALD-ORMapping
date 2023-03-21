<?php

namespace REALD\ORMapping;

use PDO;

class OrmMySql{

    public static function connect($option){

		$host = "127.0.0.1";

		if(!empty($option["host"])){
			$host = $option["host"];
		}
	
		$port = "";
		if(!empty($option["port"])){
			$port = $option["port"];
		}
	
		$username = "";
		if(!empty($option["user"])){
			$username = $option["user"];
		}
	
		$password = "";
		if(!empty($option["pass"])){
			$password = $option["pass"];
		}
	
		$database = "";
		if(!empty($option["database"])){
			$database = $option["database"];
		}
	
		$encoding = "utf8";
		if(!empty($option["charset"])){
			$encoding = $option["charset"];
		}
	
		$prefix = "";
		if(!empty($option["prefix"])){
			$prefix = $option["prefix"];
		}
	
		if(!empty($option["option"])){
			$options = $option["option"];
		}
	
		$pdoStr = "mysql:host=" . $host;
		if($port){
			$pdoStr .= ":".$port;
		}
	
		if($database){
			$pdoStr .= ";dbname=". $database;
		}
	
		if($encoding){
			$pdoStr .= ";charset=". $encoding;
		}

		$makePdo = new PDO($pdoStr, $username, $password);
	
		if(!empty($options)){
			if(is_array($options)){
				foreach($options as $field=>$value){
					$makePdo->setAttribute($field,$value);
				}
			}	
		}

        if(!empty($option["database"])){
            $makePdo->query("USE ". $option["database"]);
        }

        if(!empty($option["table"])){
            $context->_table = $option["table"];
        }

		return $makePdo;
    }
}