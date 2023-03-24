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
    "charset" => "utf8mb4",
]);

$databaseName = "testdb_000001";

$o1->database($databaseName)
    ->create([
        "ifNotExists" => true,
    ])
    ->use()
;

echo "<pre>";
print_r($o1->log());
echo "</pre>";