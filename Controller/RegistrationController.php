<?php
	
namespace deinBerichtsheft\Controller;

use deinBerichtsheft\Model\RegistrationModel;

class RegistrationController
{
    protected $view;
    private $db;
    private $validData = array();
    private $errors = array();
    private $labels = array("userName" => "Name", "userEmail" => "E-Mail-Adresse", "userPwd" => "Passwort", "userPwdRepeat" => "Passwort wiederholen");	
    
    public function __construct($view) 
    {
        $this->db = new RegistrationModel();
        $this->view = $view;
    }
     
    public function showRegistrationForm()
    {
        $this->view->setVars([
            'labels' => $this->labels,
            'validData' => $this->validData,
            'errors' => $this->errors
        ]);
    }

    public function showConfirmation()
    {
        
    }

    public function validateForm(){
        foreach ($this->labels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                $this->errors[$index] = "Bitte " . $value . " angeben";
            } else if ($index == "userEmail" && !filter_var($_POST[$index], FILTER_VALIDATE_EMAIL)){
                $this->errors[$index] = "E-Mail-Adresse ist ung&uuml;ltig";
            } else if ($index == "userEmail" && $this->db->checkEmailExist($_POST[$index])){
                $this->errors[$index] = "E-Mail-Adresse existiert bereits. Bitte andere E-Mail-Adresse angeben.";
            } else if ($index == "userPwd" && strlen($_POST[$index]) <= 7) {
                $this->errors[$index] = "Passwort ist zu kurz. Bitte mindestens 8 Zeichen verwenden.";
            } else if ($index == "userPwdRepeat") {
                if($_POST[$index] !== $_POST["userPwd"]){
                    $this->errors[$index] = "Passwörter stimmen nicht überein.";
                } 
            } else {
                $this->validData[$index] = $_POST[$index];
            }       
        }

        if (count($this->errors) > 0) {
            $this->view->setDoMethodName("showRegistrationForm");
            $this->showRegistrationForm();
        } else {
            if ($this->db->writeRegistrationData($this->validData)) {
                $this->view->setDoMethodName("showConfirmation");
                $this->showConfirmation();
            }
        }
    }
}
?>