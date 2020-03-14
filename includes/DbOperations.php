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

        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUserPasswordByEmail($email);
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }
                else{
                    return USER_PASSWORD_DO_NOT_MATCH;
                }
            }
            else{
                return USER_NOT_FOUND;
            }
        }

        private function getUserPasswordByEmail($email){
            $stmt = $this->con->prepare("Select  password From users where email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($password);
            $stmt->fetch();
            return $password;
        }

        public function getAllUsers(){
            $stmt = $this->con->prepare("Select  id, email, name, school from users");
            
            $stmt->execute();
            $stmt->bind_result($id, $email,$name, $school);
            $users = array();
            while($stmt->fetch()){
                $user = array(
                    "id" => $id,
                    "email" => $email,
                    "name" => $name,
                    "school" => $school
                );
                array_push($users, $user);
            }
            
            return $users;
        }

        public function updateUser($id, $email, $name, $school){
            $stmt = $this->con->prepare("Update users Set email = ?, name = ?, school = ? where id = ?");
            $stmt->bind_param("sssi", $email, $name, $school, $id );
            if($stmt->execute()){
                return true;
            }
            return false;

        }

        public function updatePassword($email, $currentPassword, $newPassword){
            $hashed_password = $this->getUserPasswordByEmail($email);
            if(password_verify($currentPassword, $hashed_password)){
                $hash_password = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("Update users set password = ? where email = ?");
                $stmt-> bind_param("ss", $hash_password,$email);
                if($stmt->execute()){
                    return PASSWORD_CHANGED;
                }
                return PASSWORD_DO_NOT_CHANGED;
            }
            else{
                return PASSWORD_DO_NOT_MATCH;
            }
        }


        public function deleteUser($id){
            $stmt = $this->con->prepare("Delete from users where id = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute())
                return true;
            return false;
        }

        public function getUserByEmail($email){
            $stmt = $this->con->prepare("Select  id, email, name, school from users where email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($id, $email,$name, $school);
            $stmt->fetch();
            $user = array(
                "id" => $id,
                "email" => $email,
                "name" => $name,
                "school" => $school
            );
            return $user;
        }
        
        private function isEmailExist($email){
            $stmt =  $this->con->prepare("Select id from users where email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }

        public function getAllQuotes(){
            $stmt = $this->con->prepare("Select * From quotes");
            
            $stmt->execute();
            $stmt->bind_result($id, $author, $quote_text, $thumbnail, $created_at, $updated_at);
            $quotes = array();
            while($stmt->fetch()){
                $quote = array(
                    "id" => $id,
                    "author" => $author,
                    "thumbnail" => $thumbnail,
                    "quote" => $quote_text, 
                    "created_at" => $created_at,
                    "updated_at" => $updated_at
                );
                array_push($quotes, $quote);
            }
            
            return $quotes;
        }
    }