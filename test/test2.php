<?php

require "../src/Orm.php";
require "../src/OrmStatic.php";
require "../src/OrmMySql.php";
require "../src/OrmSelect.php";
require "../src/OrmResCollection.php";
require "../src/OrmMigrateDatabase.php";
require "../src/OrmMigrateTable.php";
require "../src/OrmMigrateView.php";
require "../src/OrmInsert.php";
require "../src/OrmUpdate.php";

use REALD\ORMapping\Orm;
use REALD\ORMapping\OrmStatic;

OrmStatic::addConnect("normal",[
    "driver" => "mysql",
    "host" => "localhost",
    "port" => 3306,
    "user" => "root",
    "pass" => "admin1234",
    "database" => "ormtest_20230320",
    "charset" => "utf8mb4",
]);

$o1 = new Orm();
$o2 = new Orm();
/*
$o1->view("view01")
    ->drop()
    ->create("select * from table01",[
        "ifNotExists" => true,
    ])
;
*/
$o1->begin();

$o1->tableName = "table01";

$o1->update()
    ->where("id","=",7)
    ->update([
        "name" => "更新　次郎",
        "status" => 2,
        "update_at" => date("Y/m/d H:i:s"),
    ])
;

/*
$res = $o1->table("table01")
    ->insert([
        "name"=>"新規登録テストテキスト001",
        "status" => 1,
        "create_at"=>date("Y/m/d H:i:s"),
        "update_at"=>date("Y/m/d H:i:s"),
    ])
//    ->lastInsertId()
;

$o2->table("table02")
    ->insert([
        "table01_id"=>6,
        "code"=>"99999999999",
        "create_at"=>date("Y/m/d H:i:s"),
        "update_at"=>date("Y/m/d H:i:s"),
    ])
;
*/



$o1->commit();


echo "<pre>";
print_r($o1->log());
