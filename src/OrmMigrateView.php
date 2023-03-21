<?php

namespace REALD\ORMapping;

class OrmMigrateView{

    private $context;
    private $_view;

    public function __construct(&$context, $ViewName){
        $this->context = $context;
        $this->_view = $ViewName;
    }

    public function create($viewSql, $option = []){

        $ifNotExists = "";
        if(!empty($option["ifNotExists"])){
            $ifNotExists = "IF NOT EXISTS";
        }

        $sql = "CREATE VIEW " . $ifNotExists . " " . $this->_view . " AS " . $viewSql;

        $this->context->query($sql);

        return $this;
    }

    public function drop($ifExistsFlg = false){
        
        $ifExists = "";
        if($ifExistsFlg){
            $ifExists = "IF EXISTS";
        }

        $sql = "DROP VIEW " . $ifExists ." ". $this->_view;

        $this->context->query($sql);

        return $this;
    }
}