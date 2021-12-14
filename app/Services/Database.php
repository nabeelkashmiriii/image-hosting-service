<?php

namespace App\Services;

use MongoDB\Client as DB;

class  Database
{

    protected $connection;


    // constructer For Database Connection
    public function __construct($collection)
    {
        $connection_string = 'mongodb+srv://pakistan:pakistan@cluster0.hx6kr.mongodb.net/imageHostingService?retryWrites=true&w=majority';
        $conn= new DB($connection_string);
        $this->connection  = $conn->imageHostingService->$collection;
    }

    // Get Conection
    public function getConnection()
    {
        return $this->connection;
    }
}

