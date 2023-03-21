<?php

require "../src/Orm.php";
require "../src/OrmMySql.php";
require "../src/OrmSelect.php";
require "../src/OrmResCollection.php";
require "../src/OrmMigrateDatabase.php";
require "../src/OrmMigrateTable.php";

use REALD\ORMapping\Orm;

$o1 = new Orm();
$o1->setDatabase([
    "driver" => "mysql",
    "host" => "localhost",
    "port" => 3306,
    "user" => "root",
    "pass" => "admin1234",
//    "database" => "ormtest_20230320",
    "charset" => "utf8mb4",
//    "table" => "table01",
]);

/*
$o1->createDatabase("original_db_20230321",[
    "ifNotExists" => true,
]);
*/

$o1->database("original_db_20230321")
    ->create()
    ->use()
;

$o1->table("table03")
    ->drop(true)
    ->create([
        "id"=>[
            "type" => "int",
            "length" => 11,
            "primaryKey" => true,
            "notNull" => true,
            "autoIncrement" => true,
            "comment"=>"ID(PK)",
        ],
        "name"=>[
            "type" => "varchar",
            "length" => 255,
            "comment"=>"名前",
        ],
        "status"=>[
            "type" => "tinyint",
            "length" => 2,
            "default" => 0,
            "comment"=>"ステータス",
        ],
        "create_at"=>[
            "type" => "datetime",
            "notNull" => true,
            "comment"=>"作成日",
        ],
        "update_at"=>[
            "type" => "datetime",
            "notNull" => true,
            "comment"=>"更新日",
        ],
    ],
    [
        "ifNotExists" => true,
        "collate" => "utf8mb4_general_ci",
        "comment"=>"テストテーブル03",
    ])
;

$o1->table("table03")
    ->addColumn([
        "memo" => [
            "type"=>"varchar",
            "length"=>22,
            "comment"=>"備考欄",
            "after"=>"status",
        ],
        "name_kana" => [
            "type"=>"varchar",
            "length"=>255,
            "comment"=>"名前かな",
            "after"=>"name",
        ],
        "dmy" => [
            "type"=>"int",
            "length"=>11,
            "comment"=>"削除対象ダミーカラム",
        ],
    ])
;

$o1->table("table03")
    ->changeColumn([
        "memo_2"=>[
            "before"=>"memo",
            "type"=>"text",
            "comment"=>"備考欄(改定)",
        ],
    ])
;

$o1->table("table03")
    ->dropColumn([
        "dmy",
    ])
;

echo "<pre>";
print_r($o1->log());



/*
$o2 = $o1;
$std2 = $o2->select([
    "'' as id",
    "'' as create_at",
    "'' as name",
    "'' as code",
    "table01.status",
])
    ->where("status","=",1);
$std = $o1->select([
    "table01.id",
    "table01.create_at",
    "table01.name",
    "table01.status",
])
;
/*
    ->leftJoin("table02", function($std2){
        $std2->on("table02.table01_id", "=", "table01.id");
    })
    ->where("status","=",0)
    ->union($std2)
;
/*
$std->select([
    "CASE
        WHEN id > 6 THEN
            \"OK\"
        ELSE
            \"NG\"
    END AS status"
]);
/*
    ->where("id","=",2)
    ->whereOr("id","=",3)
    */
//    ->whereIN("id",[1,4])
//    ->where("name","IS", NULL)
//    ->where("name","LIKE","%太郎%")
//    ->count();
//    ->lists("create_at","id")
//    $std->orderBy("create_at","desc");

/*
$res = $std->first();

    echo "<pre>";
print_r($res);

print_r($o1->log());
*/