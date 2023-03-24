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

use Reald\Orm\Orm;

$o1 = new Orm();

$o1->setDatabase("normal", [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => 3306,
    "user" => "root",
    "pass" => "admin1234",
    "database"=>"testdb_000001",
    "charset" => "utf8mb4",
]);

$o1->tableName = "test01";

$res = $o1->select()->get();

echo "--------------------------------------";

echo "<pre>";
print_r($res->toArray());
echo "</pre>";

$res = $o1->select()->first();

echo "--------------------------------------";

echo "<pre>";
print_r($res->toArray());
echo "</pre>";


$res = $o1->select([
    "id",
    "CASE
        WHEN status = 0 THEN
            'OK'
        ELSE
            'NG!!'
    END AS status",
    "name",
])->get();

echo "--------------------------------------";

echo "<pre>";
print_r($res->toArray());
echo "</pre>";

$res = $o1->select([
    "id",
    "status",
    "name",
])
    ->where("id","=",2)
    ->whereOr("id","=",4)
    ->get();

echo "--------------------------------------";

echo "<pre>";
print_r($res->toArray());
echo "</pre>";

$res = $o1->select([
    "id",
    "status",
    "name",
])
    ->orderBy("id","desc")->get();

echo "--------------------------------------";

echo "<pre>";
print_r($res->toArray());
echo "</pre>";

$std1 = $o1->select()
    ->join("test02",function($std2){
        $std2->on("test02.test01_id","=","test01.id");
        $std2->where("test02.disabled_flg", "=", 0);
    })
;

echo "--------------------------------------";

echo "<pre>";
print_r($std1->get()->toArray());
echo "</pre>";

$std1->where("id","=",2);

echo "--------------------------------------";

echo "<pre>";
print_r($std1->get()->toArray());
echo "</pre>";

$res = $o1->select()
    ->count()
;

echo "--------------------------------------";

echo "<pre>";
print_r($res);
echo "</pre>";

$res = $o1->select()
    ->paginate(2,3)
;


echo "--------------------------------------";

echo "<pre>";
print_r($res);
echo "</pre>";