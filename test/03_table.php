<?php

require "../src/Orm.php";

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

$o1->createDateColumn = "create_at";
$o1->updateDateColumn = "update_at";

$databaseName = "testdb_000001";

$o1->database($databaseName)
    ->drop(true)
    ->create([
        "ifNotExists" => true,
    ])
    ->use()
;

$table = $o1->table("test01");

$table->create([
        "id"=>[
            "type"=>"int",
            "length"=>11,
            "primaryKey" => true,
            "notNull"=>true,
            "autoIncrement"=>true,
            "comment"=>"ID(Primary Key)"
        ],
        "name"=>[
            "type"=>"varchar",
            "length"=>255,
            "notNull"=>true,
            "comment"=>"Name",
        ],
        "create_at"=>[
            "type"=>"datetime",
            "notNull"=>true,
            "comment"=>"Create Date",
        ],
        "update_at"=>[
            "type"=>"datetime",
            "notNull"=>true,
            "comment"=>"Update Date",
        ],
    ],[
        "ifNotExists" => true,
    ])
;

$table->addColumn([
    "status" => [
        "type"=>"int",
        "length"=>2,
        "default"=>0,
        "comment"=>"Status",
        "after"=>"name",
    ],
    "_memo" => [
        "type"=>"text",
        "comment"=>"memo(before..)",
        "after"=>"status",
    ],
    "dammy_column"=>[
        "type"=>"varchar",
        "length"=>255,
        "after"=>"_memo",
    ],
]);

$table->changeColumn([
    "memo" => [
        "before"=>"_memo",
        "type"=>"text",
        "comment"=>"memo",
    ],
]);

$table->dropColumn([
    "dammy_column",
]);

$table->insert([
    "name"=>"山田　太郎",
    "status"=>0,
    "memo"=>"概要1テキストテキストテキスト",
])
->insert([
    "name"=>"鈴木　次郎",
    "status"=>0,
    "memo"=>"概要2テキストテキストテキスト",
])
->insert([
    "name"=>"吉田　三郎",
    "status"=>0,
    "memo"=>"概要3テキストテキストテキスト",
])
->insert([
    "name"=>"山田　花子",
    "status"=>0,
    "memo"=>"概要4テキストテキストテキスト",
])
->insert([
    "name"=>"黒田　五郎",
    "status"=>0,
    "memo"=>"概要5テキストテキストテキスト",
])
;

sleep(2);

$table->update()
    ->where("name","=","山田　花子")
    ->update([
        "status"=>1,
        "memo"=>"概要4テキストテキストテキスト(update)",
    ])
;

$o1->createDateColumn = null;
$o1->updateDateColumn = null;

$table2 = $o1->table("test02");

$table2->create([
        "test01_id"=>[
            "type"=>"int",
            "length"=>11,
            "notNull"=>true,
            "comment"=>"teet01_id(FK)",
        ],
        "disabled_flg"=>[
            "type"=>"tinyint",
            "length"=>2,
            "notNull"=>true,
            "default"=>0,
            "comment"=>"disabled flg",
        ],
        "froms"=>[
            "type"=>"varchar",
            "length"=>6,
            "comment"=>"from area",
        ],
        "ages"=>[
            "type"=>"int",
            "length"=>3,
            "comment"=>"Age",
        ],
    ],[
        "ifNotExists" => true,
    ])
;

$table->insert([
    "test01_id"=>"1",
    "froms"=>"東京都",
    "ages"=>36,
])
->insert([
    "test01_id"=>"2",
    "froms"=>"大阪府",
    "ages"=>41,
    "disabled_flg"=>1,
])
->insert([
    "test01_id"=>"2",
    "froms"=>"大分県",
    "ages"=>40,
])
;

echo "<pre>";
print_r($o1->log());
echo "</pre>";