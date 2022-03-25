<?php

namespace deinBerichtsheft\Model;
use PDO;
use PDOException;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

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
            $_SESSION["userUid"] = $user[0]["userUid"];
            $_SESSION["userEmail"] = $user[0]["userEmail"];
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
            $mail->addReplyTo('deinberichtsheftde@gmail.com', 'Support');
            $mail->ContentType;

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Passwort zur&uuml;cksetzen';
            $mail->Body    = '<html lang="en"><head>
              <!-- Required meta tags -->
              <meta charset="utf-8">
              <meta name="viewport" content="width=device-width, initial-scale=1">    
              <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
            </head>
            <body class="d-flex flex-column h-100">
             
              <nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-primary shadow">
                <div class="container d-flex justify-content-center px-4">
                    <span class="navbar-brand">deinBerichtsheft.de</span>
                </div>
            </nav>
            <main class="flex-shrink-0">
              <div class="container">
                <div class="card shadow text-center mt-3">
                    <div class="card-body">
                      <h5 class="card-title">Passwort vergessen?</h5>
                      <p class="card-text">Kein Problem, hier kannst du dein Passwort ändern:</p>
                      <a class="btn btn-primary" href=' . $url . '>Passwort ändern</a>
                    </div>
                    <div class="card-footer text-muted">
                        deinBerichtsheft.de
                    </div>
                </div>
              </div>
              </main>
              <footer class="footer mt-auto py-3 px-4 bg-primary shadow-footer">
                <div class="container d-flex justify-content-between">
                  <a class="text-white text-decoration-none" href="#">Impressum</a>
                  <a class="text-white text-decoration-none" href="#">Datenschutz</a>
                </div>
              </footer>
              <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
            </body></html>';

        //$mail->send();
        var_dump($url);
  
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("E-Mail konnte nicht gesendet werden. {$mail->ErrorInfo}"); 
            die;
        }

        $_SESSION["pwdResetReqeuestSuccessfull"] = true;
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
        
        $_SESSION["registeredSuccessfully"] = true;

        return true;
    }
}