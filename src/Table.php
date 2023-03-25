<?php

/**
 * ==============================================================================
 * 
 * Reald/Orm
 * 
 * Table
 * 
 * OR mapping for database operations dedicated to the web framework "Reald".
 * Table class object (Data-Access-Object) for framework "Reald".
 * 
 * Author : Masato Nakatsuji.
 * Since  : 2023,03.24
 * 
 * ==============================================================================
 */
namespace Reald\Orm;

use Reald\Core\CoreBlock;
use Reald\Core\Config;

class Table extends CoreBlock{

    // Horizontal deployment from OrmTrait
    use OrmTrait;
    
    public function __construct(){
        parent::__construct();

        if(!$this->existDriver()){
            $getDrive = Config::get("config.database.". $this->drive);
            $this->setDatabase($this->drive, $getDrive);
        }
    }
}