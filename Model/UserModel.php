<?php

namespace deinBerichtsheft\Model;
use PDO;
use PDOException;

class UserModel extends Database
{
    public function checkEmailExist($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('SELECT userEmail FROM user WHERE userEmail = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $email = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        if($email[0]["userEmail"] == $values){
            return true;
        }
  
        return false;
    }

    public function checkPwd($values)
    {
        $sql = "Select userPwd FROM user WHERE userEmail = :email;";

        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(":email" => $values["userEmail"]));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $pwd = $sth->fetchAll(PDO::FETCH_ASSOC);

        $checkPwd = password_verify($values["userPwd"], $pwd[0]["userPwd"]);

        if($checkPwd){
            return true;
        }
        
        return false;
    }

    public function getUser($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('SELECT * FROM user WHERE userEmail = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
          
        }

        $user = $sth->fetchAll(PDO::FETCH_ASSOC);

        if($user == null || empty($user)) {
            return false;
        } else {
            $_SESSION["userName"] = $user[0]["userName"];
            $_SESSION["userUid"] = $user[0]["userUid"];
            $_SESSION["userEmail"] = $user[0]["userEmail"];
            return true;
        }

        return false;
    }

    public function setUser($values)
    {
        $guid = $this->createUUID();

        $sql = "INSERT INTO user (`userName`, `userEmail`, `userUid`, `userPwd`) VALUES (
             :name, :email, :guid, :pwd);";

        $hashedPwd = password_hash($values["userPwd"], PASSWORD_DEFAULT);

        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(
                ":name" => $values["userName"],
                ":email" => $values["userEmail"],
                ":guid" => $guid,
                ":pwd" => $hashedPwd));
            $sth = null;
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Schreiben der Daten.", $e); 
            die;
        }
                
        return true;
    }
}