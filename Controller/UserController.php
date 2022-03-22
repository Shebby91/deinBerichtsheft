<?php
	
namespace deinBerichtsheft\Controller;

use deinBerichtsheft\Model\UserModel;

class UserController
{
    protected $view;
    private $db;

    private $registrationValidData = array();
    private $registrationErrors = array();
    private $registrationLabels = array("userName" => "Name", "userEmail" => "E-Mail-Adresse", "userPwd" => "Passwort", "userPwdRepeat" => "Passwort wiederholen");	

    private $loginValidData = array();
    private $loginErrors = array();
    private $loginLabels = array("userEmail" => "E-Mail-Adresse", "userPwd" => "Passwort");	

    public function __construct($view) 
    {
        $this->db = new UserModel();
        $this->view = $view;
    }

    public function showRegistrationForm()
    {
        $this->view->setVars([
            'registrationLabels' => $this->registrationLabels,
            'registrationValidData' => $this->registrationValidData,
            'registrationErrors' => $this->registrationErrors
        ]);
    }    

    public function showLoginForm()
    {
        $this->view->setVars([
            'loginLabels' => $this->loginLabels,
            'loginValidData' => $this->loginValidData,
            'loginErrors' => $this->loginErrors
        ]);
    }

    public function showRegistrationConfirmation()
    {
        
    }

    public function showLoginConfirmation()
    {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }
    }

    function showLogout() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }
        session_unset();
        session_destroy();
    }

    public function validateRegistrationForm(){
        foreach ($this->registrationLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                $this->registrationErrors[$index] = "Bitte " . $value . " angeben";
            } else if ($index == "userEmail" && !filter_var($_POST[$index], FILTER_VALIDATE_EMAIL)){
                $this->registrationErrors[$index] = "E-Mail-Adresse ist ung&uuml;ltig";
            } else if ($index == "userEmail" && $this->db->checkEmailExist($_POST[$index])){
                $this->registrationErrors[$index] = "E-Mail-Adresse existiert bereits. Bitte andere E-Mail-Adresse angeben.";
            } else if ($index == "userPwd" && strlen($_POST[$index]) <= 7) {
                $this->registrationErrors[$index] = "Passwort ist zu kurz. Bitte mindestens 8 Zeichen verwenden.";
            } else if ($index == "userPwdRepeat") {
                if($_POST[$index] !== $_POST["userPwd"]){
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
            if ($this->db->setUser($this->registrationValidData)) {
                $this->view->setDoMethodName("showRegistrationConfirmation");
                $this->showRegistrationConfirmation();
            }
        }
    }

    public function validateLoginForm(){
        foreach ($this->loginLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                $this->loginErrors[$index] = "Bitte " . $value . " angeben";
            } else if ($index == "userEmail" && !filter_var($_POST[$index], FILTER_VALIDATE_EMAIL)){
                $this->loginErrors[$index] = "E-Mail-Adresse ist ung&uuml;ltig.";
            } else if ($index == "userPwd" && !$this->db->checkPwd(array("userPwd" => $_POST[$index], "userEmail" => $_POST["userEmail"]))) {
                $this->loginErrors[$index] = "E-Mail-Adresse oder Passwort falsch.";
            } else {
                $this->loginValidData[$index] = $_POST[$index];
            }       
        }

        if (count($this->loginErrors) > 0) {
            $this->view->setDoMethodName("showLoginForm");
            $this->showLoginForm();
        } else {
            if($this->db->getUser($this->loginValidData["userEmail"])) {
                $this->view->setDoMethodName("showLoginConfirmation");
                $this->showLoginConfirmation();
            } else{
                new \deinBerichtsheft\Library\ErrorMsg('Bei deinem Login ist ein Fehler Aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
            }
        }
    }
}
?>