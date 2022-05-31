<?php

namespace deinBerichtsheft\Model;
use PDO;
use PDOException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'Library/Mailer/autoload.php';

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

    public function checkUserNameExist($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('SELECT userName FROM user WHERE userName = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $userName = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        if($userName[0]["userName"] == $values){
            return true;
        }
  
        return false;
    }

    public function checkPwd($values)
    {
        $sql = "Select userPwd FROM user WHERE userName = :name;";

        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(":name" => $values["userName"]));
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
            $sth = $pdo->prepare('SELECT * FROM user WHERE userName = ?;');
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
            $_SESSION["userId"] = $user[0]["userId"];
            $_SESSION["userEmail"] = $user[0]["userEmail"];
            $_SESSION["tableView"] = false;
            if($user[0]["userImg"] !== null){
                $_SESSION["userImg"] = $user[0]["userImg"];
            }
            return true;
        }

        return false;
    }

    public function generateResetToken($values)
    {

        $pdo = $this->linkDB();

        $selector =bin2hex(random_bytes(8));

        $token = random_bytes(32);

        $url = "http://localhost/index.php?controller=User&do=showResetPasswordForm&selector=" . $selector . "&validator=". bin2hex($token);

        $expires = date("U") + 1800;

        $sql = "DELETE FROM pwdReset WHERE pwdResetEmail = ? ;";
    
        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array($values));
            $sth = null;
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Schreiben der Daten.", $e); 
            die;
        }

        $sql = "INSERT INTO pwdReset (`pwdResetEmail`, `pwdResetSelector`, `pwdResetToken`, `pwdResetExpires`) VALUES (
            :email, :selector, :token, :expires);";

        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(
                ":email" => $values,
                ":selector" => $selector,
                ":token" => $hashedToken,
                ":expires" => $expires));
            $sth = null;
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Schreiben der Daten.", $e); 
            die;
        }

        /*
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //Enable verbose debug output $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'deinberichtsheftde@gmail.com';                     //SMTP username
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;             //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom('deinberichtsheftde@gmail.com', 'deinBerichtsheft.de');   
            $mail->addAddress('sgrauthoff@gmail.com');               //Name is optional
            $mail->addReplyTo('deinberichtsheftde@gmail.com', 'Support');
            

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Passwort ändern';
            $mail->CharSet;
            $mail->ContentType;
            $mail->Body    = '
              <body>
                <div class="container">
                  <h2 style="margin: 0px 0px 8px 0px; color: #666;">Du möchtest dein Passwort ändern?</h2>
                  <h4 style="margin: 00px; color: #666;">Kein Problem, hier kannst du dein Passwort ändern:</h4><br>
                  <a style="display: inline-block;
                            font-weight: 400;
                            line-height: 1.5;
                            color: #212529;
                            text-align: center;
                            text-decoration: none;
                            vertical-align: middle;
                            cursor: pointer;
                    	    -webkit-user-select: none;
                    	    -moz-user-select: none;
                    	    user-select: none;
                    	    background-color: transparent;
                    	    border: 1px solid transparent;
                    	    padding: 0.375rem 0.75rem;
                    	    font-size: 1rem;
                    	    border-radius: 0.25rem;
                    	    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                            color: #fff;
                            background-color: #0d6efd;
                            border-color: #0d6efd;
                            " href=' . $url . '>Passwort ändern</a><br><br><br>
                  <span style="color: #666;">Liebe Grüße</span><br>
                  <span style="color: #666;">deinBerichtsheft.de</span>

                </div>
              </body>';

        $mail->send();

        } catch (Exception $e) {
            new \deinBerichtsheft\Library\ErrorMsg("E-Mail konnte nicht gesendet werden. {$mail->ErrorInfo}"); 
            die;
        }
    */
        $_SESSION["pwdResetReqeuestSuccessfull"] = true;
        $_SESSION["url"] =  $url;
        return true;

    }

    public function setNewPwd($values)
    {   
        $pdo = $this->linkDB();
        $currentDate = date("U");
        try {
            $sth = $pdo->prepare('SELECT * FROM pwdReset WHERE pwdResetSelector = :selector AND pwdResetExpires >= :currentDate;');
            $sth->execute(array(
                ":selector" => $values["selector"],
                ":currentDate" => $currentDate));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $securityData = $sth->fetchAll(PDO::FETCH_ASSOC);

        if($securityData == null || empty($securityData)) {
            return false;
        } else {

            $tokenBin = hex2bin($values["validator"]);
            $tokenCheck = password_verify($tokenBin, $securityData[0]["pwdResetToken"]);

            if($tokenCheck == false) {
                return false;
            } else if ($tokenCheck == true) {
                try {
                    $sth = $pdo->prepare('SELECT * FROM user WHERE userEmail = :email;');
                    $sth->execute(array(":email" => $securityData[0]["pwdResetEmail"]));
                } catch (PDOException $e) {
                    new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
                    die;
                }

                if($sth->rowCount() == 0){
                    return false;
                }
        
                $user = $sth->fetchAll(PDO::FETCH_ASSOC);

                if ($user == null || empty($user)) {
                    return false;
                }
                
                $newHashedPwd = password_hash($values["userPwd"], PASSWORD_DEFAULT);

                try {
                    $sth = $pdo->prepare('UPDATE user SET userPwd = :pwd WHERE userEmail = :email;');
                    $sth->execute(array(":pwd" => $newHashedPwd, ":email" => $user[0]["userEmail"]));
                } catch (PDOException $e) {
                    new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
                    die;
                }

                try {
                    $sth = $pdo->prepare('DELETE FROM pwdReset WHERE pwdResetEmail = :email;');
                    $sth->execute(array(":email" => $user[0]["userEmail"]));
                } catch (PDOException $e) {
                    new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
                    die;
                }

                try {
                    $sth = $pdo->prepare('SELECT * FROM pwdReset WHERE pwdResetEmail = :email;');
                    $sth->execute(array(":email" => $user[0]["userEmail"]));
                } catch (PDOException $e) {
                    new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
                    die;
                }

                if($sth->rowCount() !== 0){
                    return false;
                }
                
                $_SESSION["pwdResetSuccessfull"] = true;

                return true;
            }
        }
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

        /*
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //Enable verbose debug output $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'deinberichtsheftde@gmail.com';                     //SMTP username
            $mail->Password   = 'dB.deietS';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;             //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom('deinberichtsheftde@gmail.com', 'deinBerichtsheft.de');   
            $mail->addAddress('sgrauthoff@gmail.com');               //Name is optional
            $mail->addReplyTo('deinberichtsheftde@gmail.com', 'Support@deinBerichsheft.de');
            

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Erfolgreich registriert';
            $mail->CharSet;
            $mail->ContentType;
            $mail->Body = '
              <body>
                <div class="container">
                  <h2 style="margin: 0px 0px 8px 0px; color: #666;">Herzlichen Glückwunsch '.$values["userName"].'!</h2>
                  <h4 style="margin: 0px; color: #666;">Du hast dich erfolgreich bei deinBerichtsheft.de registriert.<br>
                  Viel Spaß beim Berichtsheft schreiben, hier geht\'s zum Login:</h4><br>
                  <a style="display: inline-block;
                            font-weight: 400;
                            line-height: 1.5;
                            color: #212529;
                            text-align: center;
                            text-decoration: none;
                            vertical-align: middle;
                            cursor: pointer;
                    	    -webkit-user-select: none;
                    	    -moz-user-select: none;
                    	    user-select: none;
                    	    background-color: transparent;
                    	    border: 1px solid transparent;
                    	    padding: 0.375rem 0.75rem;
                    	    font-size: 1rem;
                    	    border-radius: 0.25rem;
                    	    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                            color: #fff;
                            background-color: #0d6efd;
                            border-color: #0d6efd;
                            "href="http://localhost/index.php?controller=User&do=showLoginForm">Einloggen</a><br><br><br>
                  <span style="color: #666;">Liebe Grüße</span><br>
                  <span style="color: #666;">deinBerichtsheft.de</span>

                </div>
              </body>';

        $mail->send();

        } catch (Exception $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Es ist ein Fehler aufgetreten. Bitte prüfe deiner Zugangsdaten. {$mail->ErrorInfo}"); 
            die;
        }
*/
        $_SESSION["registeredSuccessfully"] = true;

        return true;
    }

    public function updateUser($values)
    {
        
        
        $user = $_SESSION["userName"];

        if ($user == null || empty($user)) {
            return false;
        }
    
        $pdo = $this->linkDB();

        try {
            if(!isset($values["userImg"])){
                $sth = $pdo->prepare('UPDATE user SET userName = :user, userEmail = :mail WHERE userId = :id;');
                $sth->execute(array(":user" => $values["userName"], ":mail" => $values["userEmail"], ":id" => $_SESSION["userId"]));
            } else {
                $sth = $pdo->prepare('UPDATE user SET userName = :user, userEmail = :mail, userImg = :img WHERE userId = :id;');
                $sth->execute(array(":user" => $values["userName"], ":mail" => $values["userEmail"], ":img" => $values["userImg"], ":id" => $_SESSION["userId"]));
            }

        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        $_SESSION["userName"] = $values["userName"];
        $_SESSION["userEmail"] = $values["userEmail"];

        if(isset($values["userImg"])){
            $_SESSION["userImg"] = $values["userImg"];
        }
        
        return true;
    }
}