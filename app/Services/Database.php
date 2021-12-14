<?php

namespace App\Services;

use MongoDB\Client as DB;

class  Database
{

    protected $connection;


    // constructer For Database Connection
    public function __construct($collection)
    {
        $connection_string = 'mongodb+srv://kashmiriii:kashmiriii@image-hosting-service.hx6kr.mongodb.net/users';
        $conn= new DB($connection_string);
        $this->connection  = $conn->imageHostingService->$collection;
    }

    // Get Conection
    public function getConnection()
    {
        return $this->connection;
    }
}

