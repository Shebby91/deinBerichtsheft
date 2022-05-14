<?php
	
namespace deinBerichtsheft\Controller;

use deinBerichtsheft\Model\UserModel;

class UserController
{
    protected $view;
    private $db;

    private $registrationValidData = array();
    private $registrationErrors = array();
    private $registrationLabels = array("userName" => "Benutzername", "userEmail" => "E-Mail-Adresse", "userPwd" => "Passwort", "userPwdRepeat" => "Passwort wiederholen");	

    private $loginValidData = array();
    private $loginErrors = array();
    private $loginLabels = array("userName" => "Benutzername", "userPwd" => "Passwort");	

    private $resetPwdSecurityData = array();
    private $resetPwdValidData = array();
    private $resetPwdErrors = array();
    private $resetPwdLabels = array("userPwd" => "Neues Passwort", "userPwdRepeat" => "Passwort wiederholen", "selector" => null, "validator" => null);	

    private $requestPwdResetValidData = array();
    private $requestPwdResetErrors = array();
    private $requestPwdResetLabels = array("userEmail" => "E-Mail-Adresse");	

    public function __construct($view) 
    {
        $this->db = new UserModel();
        $this->view = $view;
    }

    public function showRegistrationForm()
    {   
        if(isset($_SESSION["userId"])) {
            header("location: ../index.php?controller=report&do=showReportOverview");
        }

        $this->view->setVars([
            'registrationLabels' => $this->registrationLabels,
            'registrationValidData' => $this->registrationValidData,
            'registrationErrors' => $this->registrationErrors
        ]);
    }    

    public function showLoginForm()
    {   
        if(isset($_SESSION["userId"])) {
            header("location: ../index.php?controller=report&do=showReportOverview");
        }

        $this->view->setVars([
            'loginLabels' => $this->loginLabels,
            'loginValidData' => $this->loginValidData,
            'loginErrors' => $this->loginErrors
        ]);
    }

    public function showResetPasswordForm()
    {
        if(!isset($_GET["selector"]) || !isset($_GET["validator"])) {
            new \deinBerichtsheft\Library\ErrorMsg('Deine Anfrage ist nicht valide. Bitte wende dich an den Support oder versuche es erneut.'); 
            die;
        } else if (empty($_GET["selector"]) || empty($_GET["validator"])){
            new \deinBerichtsheft\Library\ErrorMsg('Deine Anfrage ist nicht valide. Bitte wende dich an den Support oder versuche es erneut.'); 
            die;
        } else if(ctype_xdigit($_GET["selector"]) !== true ||  ctype_xdigit($_GET["validator"]) !== true) {
            new \deinBerichtsheft\Library\ErrorMsg('Deine Anfrage ist nicht valide. Bitte wende dich an den Support oder versuche es erneut.'); 
            die;
        } else {
            $this->resetPwdSecurityData["selector"] =  $_GET["selector"];
            $this->resetPwdSecurityData["validator"] =  $_GET["validator"];
            $this->view->setVars([
                'resetPwdLabels' => $this->resetPwdLabels,
                'resetPwdValidData' => $this->resetPwdValidData,
                'resetPwdErrors' => $this->resetPwdErrors,
                'resetPwdSecurityData' => $this->resetPwdSecurityData
            ]);
        }
    }

    public function showRequestPasswordResetForm()
    {
        if(isset($_SESSION["userId"])) {
            header("location: ../index.php?controller=report&do=showReportOverview");
        }

        $this->view->setVars([
            'requestPwdResetLabels' => $this->requestPwdResetLabels,
            'requestPwdResetValidData' => $this->requestPwdResetValidData,
            'requestPwdResetErrors' => $this->requestPwdResetErrors
        ]);
    }

    public function showLoginConfirmation()
    {
    
        if(!isset($_SESSION["userId"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }

        if(isset($_SESSION["loginConfirmationShown"])) {
            header("location: ../index.php?controller=report&do=showReportOverview");
        }

        $_SESSION["loginConfirmationShown"] = true;
        

    }

    public function showRegistrationConfirmation()
    {       
        if(!isset($_SESSION["registeredSuccessfully"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }

        session_unset();
        session_destroy();
    }

    public function showResetPasswordConfirmation()
    {
        if(!isset($_SESSION["pwdResetSuccessfull"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }

        session_unset();
        session_destroy();
    }
    
    public function showRequestPasswordResetConfirmation()
    {

        if(!isset($_SESSION["pwdResetReqeuestSuccessfull"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        } 
        
        session_unset();
        session_destroy();

        session_start();
        $expires = date("U") + 900;
        $_SESSION["mailSend"] = $expires;
    }

    function showLogoutConfirmation() {
        
        if(!isset($_SESSION["userId"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }

        session_unset();
        session_destroy();
    }

    public function showDataPrivacyInformation()
    {       

    }

    public function validateRegistrationForm(){
        foreach ($this->registrationLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                if(!isset($_POST["userName"]) || empty($_POST["userName"]) || !isset($_POST["userEmail"]) || empty($_POST["userEmail"])) {
                    $this->registrationErrors["userName"] = "Bitte Benutzernamen und E-Mail-Adresse angeben.";
                    $this->registrationErrors["userEmail"] = "Bitte Benutzernamen und E-Mail-Adresse angeben.";
                }
                $this->registrationErrors[$index] = "Bitte " . $value . " angeben";
            } else if ($index == "userName" && $this->db->checkUserNameExist($_POST[$index])){
                $this->registrationErrors[$index] = "Benzutzername oder E-Mail-Adresse bereits vergeben.";
                $this->registrationErrors["userEmail"] = "Benzutzername oder E-Mail-Adresse bereits vergeben.";
            } else if ($index == "userEmail" && !filter_var($_POST[$index], FILTER_VALIDATE_EMAIL)){
                $this->registrationErrors[$index] = "E-Mail-Adresse ist ung&uuml;ltig";
            } else if ($index == "userEmail" && $this->db->checkEmailExist($_POST[$index])){
                $this->registrationErrors[$index] = "Benzutzername oder E-Mail-Adresse bereits vergeben.";
                $this->registrationErrors["userName"] = "Benzutzername oder E-Mail-Adresse bereits vergeben.";
            } else if ($index == "userPwd" && strlen($_POST[$index]) <= 7) {
                $this->registrationErrors[$index] = "Passwort ist zu kurz. Bitte mindestens 8 Zeichen verwenden.";
            } else if ($index == "userPwdRepeat") {
                if($_POST[$index] !== $_POST["userPwd"]) {
                    $this->registrationErrors[$index] = "Passwörter stimmen nicht überein.";
                } 
            } else {
                $this->registrationValidData[$index] = $_POST[$index];
            }       
        }

        if (count($this->registrationErrors) > 0) {
            $this->view->setDoMethodName("showRegistrationForm");
            $this->showRegistrationForm();
        } else {
            if (!$this->db->setUser($this->registrationValidData)) {
                new \deinBerichtsheft\Library\ErrorMsg('Bei deiner Registrierung ist ein Fehler aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
                die;
            } else{
                $this->view->setDoMethodName("showRegistrationConfirmation");
                $this->showRegistrationConfirmation();
            }
        }
    }

    public function validateLoginForm(){
        foreach ($this->loginLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                if(!isset($_POST["userName"]) || empty($_POST["userName"]) || !isset($_POST["userPwd"]) || empty($_POST["userPwd"])) {
                    $this->loginErrors["userName"] = "Bitte Benutzernamen und Passwort angeben.";
                    $this->loginErrors["userPwd"] = "Bitte Benutzernamen und Passwort angeben.";
                }
            } else if ($index == "userName" && !$this->db->checkUserNameExist($_POST[$index])){
                $this->loginErrors[$index] = "Benutzername oder Passwort falsch.";
                $this->loginErrors["userPwd"] = "Benutzername oder Passwort falsch.";
                if(!isset($_SESSION["wrongPwd"])) {
                    $_SESSION["wrongPwd"] = 1;
                } else {
                    $_SESSION["wrongPwd"] += 1;
                }
                
            } else if ($index == "userPwd" && !$this->db->checkPwd(array("userPwd" => $_POST[$index], "userName" => $_POST["userName"]))) {     
                $this->loginErrors["userName"] = "Benutzername oder Passwort falsch.";
                $this->loginErrors[$index] = "Benutzername oder Passwort falsch.";
                if(!isset($_SESSION["wrongPwd"])) {
                    $_SESSION["wrongPwd"] = 1;
                } else {
                    $_SESSION["wrongPwd"] += 1;
                }
            } else {
                $this->loginValidData[$index] = $_POST[$index];
            }       
        }

        if (isset($_SESSION["wrongPwd"])){
            if($_SESSION["wrongPwd"] >= 3) {
                if(!isset($_SESSION["wrongPwdExpires"])) {
                    $expires = date("U") + 900;
                    $_SESSION["wrongPwdExpires"] = $expires;
                }
                
                if($_SESSION["wrongPwdExpires"] == 0) {
                    $expires = date("U") + 900;
                    $_SESSION["wrongPwdExpires"] = $expires;
                } else {
                    if($_SESSION["wrongPwdExpires"] > date("U")){
                        $this->loginErrors["userPwd"] = "Zu viele Fehlversuche. Bitt versuche es später erneut.";
                        $this->loginErrors["userName"] = "";
                    } else {
                        $_SESSION["wrongPwd"] = 1;
                        $_SESSION["wrongPwdExpires"] = 0;
                    }
                }
            }
        }

        if (count($this->loginErrors) > 0) {
            $this->view->setDoMethodName("showLoginForm");
            $this->showLoginForm();
        } else {
            if(!$this->db->getUser($this->loginValidData["userName"])) {
                new \deinBerichtsheft\Library\ErrorMsg('Bei deinem Login ist ein Fehler aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
                die;           
            } else{
                $this->view->setDoMethodName("showLoginConfirmation");
                $this->showLoginConfirmation();
            }
        }
    }

    public function validateRequestPwdResetForm(){
        foreach ($this->requestPwdResetLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                $this->requestPwdResetErrors[$index] = $value . " angeben.";
            } else if ($index == "userEmail" && !filter_var($_POST[$index], FILTER_VALIDATE_EMAIL)){
                $this->requestPwdResetErrors[$index] = "E-Mail-Adresse ist ung&uuml;ltig";
            } else if ($index == "userEmail" && !$this->db->checkEmailExist($_POST[$index])){
                $this->requestPwdResetErrors[$index] = "E-Mail-Adresse ist ung&uuml;ltig";
            } else if ($index == "userEmail" && $_SESSION["mailSend"] > date("U")){
                $this->requestPwdResetErrors[$index] = "Es wurde bereits eine E-Mail versendet. Bitte prüfe dein Postfach oder versuche es später erneut.";
            } else {
                $this->requestPwdResetValidData[$index] = $_POST[$index];
            }       
        }

        if (count($this->requestPwdResetErrors) > 0) {
            $this->view->setDoMethodName("showRequestPasswordResetForm");
            $this->showRequestPasswordResetForm();
        } else {
            if(!$this->db->generateResetToken($this->requestPwdResetValidData["userEmail"])) {
                new \deinBerichtsheft\Library\ErrorMsg('Deine Anfrage ist nicht valide. Bitte wende dich an den Support oder versuche es erneut.'); 
                die;
            } else {
                header("location: ../index.php?controller=user&do=showRequestPasswordResetConfirmation");
            }
        }
    }

    public function validateResetPasswordForm(){
        foreach ($this->resetPwdLabels as $index => $value) {
            if ($index == "userPwd" && empty($_POST["userPwd"])){
                $this->resetPwdErrors[$index] = $value . " angeben";
            } else if ($index == "userPwdRepeat" && empty($_POST["userPwdRepeat"])){
                $this->resetPwdErrors[$index] = $value . " angeben";
            } else if ($index == "userPwd" && strlen($_POST[$index]) <= 7 || $index == "userPwdRepeat" && strlen($_POST[$index]) <= 7) {
                $this->resetPwdErrors[$index] = "Passwort ist zu kurz. Bitte mindestens 8 Zeichen verwenden.";
            } else if ($index == "userPwdRepeat") {
                if($_POST[$index] !== $_POST["userPwd"]) {
                    $this->resetPwdErrors[$index] = "Passwörter stimmen nicht überein.";
                    $this->resetPwdErrors["userPwd"] = "Passwörter stimmen nicht überein.";
                }
            } else if ($index == "selector" && empty($_POST["selector"])){
                $this->resetPwdErrors[$index] = "Security Error";
            } else if ($index == "validator" && empty($_POST["validator"])){
                $this->resetPwdErrors[$index] = "Security Error";
            } else {
                $this->resetPwdValidData[$index] = $_POST[$index];
            }    
        }

        if (count($this->resetPwdErrors) > 0) {
            $this->view->setVars(["selector" => $this->resetPwdValidData["selector"], "validator" => $this->resetPwdValidData["validator"]]);
            $this->view->setDoMethodName("showResetPasswordForm");
            $this->showResetPasswordForm();
        } else {
            if (!$this->db->setNewPwd($this->resetPwdValidData)) {
                new \deinBerichtsheft\Library\ErrorMsg('Deine Anfrage ist nicht valide. Bitte wende dich an den Support oder versuche es erneut.'); 
                die;
            } else {
                $this->view->setDoMethodName("showResetPasswordConfirmation");
                $this->showResetPasswordConfirmation();      
            }
        }  
    }
}
?>