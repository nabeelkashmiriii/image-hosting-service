<?php

namespace App\Services;

use MongoDB\Client as DB;

class  Database
{

    protected $connection;


    // constructer For Database Connection
    public function __construct($collection)
    {
        $connection_string = 'mongodb://kashmiriii:kashmiriii@image-hosting-service-shard-00-00.hx6kr.mongodb.net:27017,image-hosting-service-shard-00-01.hx6kr.mongodb.net:27017,image-hosting-service-shard-00-02.hx6kr.mongodb.net:27017/test?replicaSet=atlas-v8ytco-shard-0&ssl=true&authSource=admin';
        $conn= new DB($connection_string);
        $this->connection  = $conn->imageHostingService->$collection;
    }

    // Get Conection
    public function getConnection()
    {
        return $this->connection;
    }
}

