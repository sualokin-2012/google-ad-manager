<?php

    // // Connection Variables
    // $host = "localhost";
    // $user = "root";
    // $password = "fnxmvotmdnjem";
    // $database = "gam";

    // // Connect to MySQL Database
    // $conn = new mysqli($host, $user, $password, $database);

    // // Check connection
    // if($conn->connect_error) {
    //     die("Connection failed: ".$conn->connect_error);
    // }

    // printf("DB Connection success");

    class DbConfig
    {
        // Connection Variables
        public $host = "localhost";
        public $user = "root";
        public $password = "fnxmvotmdnjem";
        public $database = "gam";
        public $conn;

        public function dbTest()
        {
            print "DB TEST success\n";
        }

        public function dbConnect()
        {
            // Connect to MySQL Database
            self::$conn = new mysqli(self::$host, self::$user, self::$password, self::$database);

            // Check connection
            if(self::$conn->connect_error) 
            {
                die("Connection failed: ".self::$conn->connect_error);    
            }

            print "DB Connection success";

            return self::$conn;
        }
    
    }
