<?php

    class DbOperations{
        private $con;

        function __construct(){
            require_once dirname(__FILE__).'/DbConnect.php';
            $db = new DbConnect;
            $this->con = $db->connect();

        }

        public function createUser($email, $password, $name, $school){
            if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("Insert into users (email, password, name, school) value(?,?,?,?)");

                $stmt->bind_param("ssss",$email,$password,$name, $school);
                if($stmt->execute()){
                    return USER_CREATED;
                }
                else{
                    return USER_FAILURE;    
                }
            }

            return USER_EXISTS;

        }

        
        private function isEmailExist($email){
            $stmt =  $this->con->prepare("Select id from users where email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }
    }