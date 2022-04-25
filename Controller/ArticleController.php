<?php
	
namespace deinBerichtsheft\Controller;

use DateTime;
use deinBerichtsheft\Model\ArticleModel;

class ArticleController
{
    protected $view;
    private $db;

    private $articleValidData = array();
    private $articleErrors = array();
    private $articleLabels = array("articleDate" => "Datum", "articleText" => "Eintrag");	

    public function __construct($view) 
    {
        $this->db = new ArticleModel();
        $this->view = $view;
    }

    public function showNewArticleForm()
    {   
        $reportId = $_GET["reportId"];
        $reportName = $_GET["reportName"];
        
        $this->view->setVars([
            'articleLabels' => $this->articleLabels,
            'articleValidData' => $this->articleValidData,
            'articleErrors' => $this->articleErrors,
            'reportName' => $reportName,
            'reportId' => $reportId
        ]);
    }
    public function showEditArticleConfirmation()
    {
        $reportId = $_GET["reportId"];
        $reportName = $_GET["reportName"];
        $this->view->setVars([
            'reportId' => $reportId,
            'reportName' => $reportName
        ]);
    }

    public function showNewArticleConfirmation()
    {
        $reportId = $_GET["reportId"];
        $reportName = $_GET["reportName"];
        $this->view->setVars([
            'reportId' => $reportId,
            'reportName' => $reportName
        ]);
    }

    public function showDeleteArticleConfirmation()
    {
        $reportId = $_GET["reportId"];
        $reportName = $_GET["reportName"];
        $articleId = $_GET['articleId'];
        //TODO: DELETE WHERE REPORT ID, REPORT NAME, ARTICLE ID AND USER ID
        $this->db->deleteArticleById($articleId);

        $this->view->setVars([
            'reportId' => $reportId,
            'reportName' => $reportName
        ]);
    }

    public function showEditArticleForm()
    {
        $articleId = $_GET['articleId'];
        $reportId = $_GET["reportId"];
        $reportName = $_GET["reportName"];

        $article = $this->db->getArticleById($articleId);
    
        $this->view->setVars([
            'articleLabels' => $this->articleLabels,
            'articleValidData' => $this->articleValidData,
            'articleErrors' => $this->articleErrors,
            'article' => $article,
            'reportId' => $reportId,
            'reportName' => $reportName
        ]);

    }

    public function validateNewArticleForm(){

        $reportId = $_GET["reportId"];

        foreach ($this->articleLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                if(!isset($_POST["articleDate"]) || empty($_POST["articleDate"])) {
                    $this->articleErrors["articleDate"] = "Bitte Datum für Artikel angeben.";
                }
                if(!isset($_POST["articleText"]) || empty($_POST["articleText"])){
                    $this->articleErrors["articleText"] = "Bitte Inhalt für Artikel angeben.";
                }
            } else {
                $this->articleValidData[$index] = $_POST[$index];
            }       
        }

        if (count($this->articleErrors) > 0) {
            $this->view->setDoMethodName("showNewarticleForm");
            $this->showNewarticleForm();
        } else {   
            array_push($this->articleValidData, $reportId);  
            if(!$this->db->setNewarticle($this->articleValidData)) {
                new \deinBerichtsheft\Library\ErrorMsg('Beim erstellen deines Eintrags ist ein Fehler aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
                die;           
            } else{
                $this->view->setDoMethodName("showNewArticleConfirmation");
                $this->showNewarticleConfirmation();
            }
        }
    }

    public function validateUpdateArticleForm(){

        $articleId = $_GET["articleId"];

        foreach ($this->articleLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                if(!isset($_POST["articleDate"]) || empty($_POST["articleDate"])) {
                    $this->articleErrors["articleDate"] = "Bitte Datum für Artikel angeben.";
                }
                if(!isset($_POST["articleText"]) || empty($_POST["articleText"])){
                    $this->articleErrors["articleText"] = "Bitte Inhalt für Artikel angeben.";
                }
            } else {
                $this->articleValidData[$index] = $_POST[$index];
            }       
        }

        if (count($this->articleErrors) > 0) {
            $this->view->setDoMethodName("showNewarticleForm");
            $this->showNewarticleForm();
        } else {   
            array_push($this->articleValidData, $articleId);  
            if(!$this->db->updateArticle($this->articleValidData)) {
                new \deinBerichtsheft\Library\ErrorMsg('Beim erstellen deines Eintrags ist ein Fehler aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
                die;           
            } else{
                $this->view->setDoMethodName("showEditArticleConfirmation");
                $this->showEditArticleConfirmation();
            }
        }
    }

}
?>