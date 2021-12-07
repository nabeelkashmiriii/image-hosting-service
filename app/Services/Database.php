<?php

namespace App\Services;

use MongoDB\Client as DB;

class  Database
{

    protected $connection;


// constructer For Database Connection
    public function __construct($collection)
    {
        $this->connection = (new DB)->imageHostingService->$collection;
    }


    // Get Conection
    public function getConnection(){
        return $this->connection;
    }
}
