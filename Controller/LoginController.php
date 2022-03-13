<?php
	
namespace deinBerichtsheft\Controller;

use deinBerichtsheft\Model\LoginModel;

class LoginController
{
    protected $view;
    private $db;
    private $validData = array();
    private $errors = array();
    private $labels = array("userEmail" => "E-Mail-Adresse", "userPwd" => "Passwort");	
    
    public function __construct($view) 
    {
        $this->db = new LoginModel();
        $this->view = $view;
    }
     
    public function showLoginForm()
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
                $this->errors[$index] = "E-Mail-Adresse ist ung&uuml;ltig.";
            } else if ($index == "userPwd" && !$this->db->checkPwd(array("userPwd" => $_POST[$index], "userEmail" => $_POST["userEmail"]))) {
                $this->errors[$index] = "E-Mail-Adresse oder Passwort falsch.";
            } else {
                $this->validData[$index] = $_POST[$index];
            }       
        }

        if (count($this->errors) > 0) {
            $this->view->setDoMethodName("showLoginForm");
            $this->showLoginForm();
        } else {
            if($this->db->userLogin($this->validData["userEmail"])) {
                $this->view->setDoMethodName("showConfirmation");
                $this->showConfirmation();
            } else{
                new \deinBerichtsheft\Library\ErrorMsg('Bei deinem Login ist ein Fehler Aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
            }
        }
    }
}
?>