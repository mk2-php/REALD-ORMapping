<?php

namespace Reald\Orm;

class OrmStatic{

    public const TRANSACTION_BEGIN = "BEGIN;";
    public const TRANSACTION_ROLLBACK = "ROLLBACK;";
    public const TRANSACTION_COMMIT = "COMMIT;";
    
    private static $_pdo = [];
    private static $_log = [];

    public static function addConnect($drivename, $option){

        if($option["driver"] == "mysql"){
            self::$_pdo[$drivename] = OrmMySql::connect($option);
        }
        else if($option["driver"] == "sqlite"){
            self::$_pdo[$drivename]  = OrmSqLite::connect($option);
        }
        else if($option["driver"] == "pgsql"){
            self::$_pdo[$drivename]  = OrmPgSQL::connect($option);    
        }

    }

    public static function query($driveName, $sql, $bind = []){

        $std = self::$_pdo[$driveName]->prepare($sql);
        $std->execute($bind);

        self::$_log[] = [
            "date"=>date("Y/m/d H:i:s"),
            "drive"=>$driveName,
            "sql"=>$sql,
            "bind"=>$bind,
        ];

        return $std;
    }

    public static function transaction($mode){
        foreach(self::$_pdo as $drive => $p_){
            self::query($drive, $mode);
        }
    }

    public static function getConnect($driveName){
        return self::$_pdo[$driveName];
    }

    public static function log(){
        return self::$_log;
    }
}