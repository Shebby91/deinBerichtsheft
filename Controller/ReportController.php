<?php
	
namespace deinBerichtsheft\Controller;

use deinBerichtsheft\Model\ReportModel;
use deinBerichtsheft\Model\ArticleModel;

class ReportController
{
    protected $view;
    private $db;

    private $reportValidData = array();
    private $reportErrors = array();
    private $reportLabels = array("reportName" => "Berichtsheftname", "reportDescription" => "Beschreibung");	

    public function __construct($view) 
    {
        $this->db = new ReportModel();
        $this->view = $view;
    }

    public function showNewReportForm()
    {   
        $this->view->setVars([
            'reportLabels' => $this->reportLabels,
            'reportValidData' => $this->reportValidData,
            'reportErrors' => $this->reportErrors
        ]);
    }

    public function showEditReportForm()
    {   
        $reportId = $_GET['reportId'];
        $reportName = $_GET['reportName'];
        $report = $this->db->getReportById($reportId);

        $this->view->setVars([
            'reportLabels' => $this->reportLabels,
            'reportValidData' => $this->reportValidData,
            'reportErrors' => $this->reportErrors,
            'report' => $report,
            'reportId' => $reportId,
            'reportName' => $reportName
        ]);
    }

    public function showNewReportConfirmation()
    {

    }

    public function showEditReportConfirmation()
    {
        $reportId = $_GET["reportId"];
        $reportName = $_GET["reportName"];
        $this->view->setVars([
            'reportId' => $reportId,
            'reportName' => $reportName
        ]);

    }

    public function showDeleteReportConfirmation()
    {
        //TODO: DELETE WHERE REPORT ID AND USER ID
        $reportId = $_GET['reportId'];
        $this->db->deleteUserReports($reportId);
    }
    //ADD AMOUNT OF ENTRIES
    function showReportOverview() {
        if(!isset($_SESSION["userId"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }
        
        $userReports = $this->db->getUserReports();

        $this->view->setVars([
            'reportOverviewData' => $userReports
        ]);
    }

    public function showReportDetail()
    {
        if(!isset($_SESSION["userId"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }

        $reportId = $_GET["reportId"];
        $reportName = $_GET["reportName"];
        $this->db = new ArticleModel();
        $reportArticles = $this->db->getReportArticles($reportId);

        $this->view->setVars([
            'reportDetailData' => $reportArticles,
            'reportName' => $reportName,
            'reportId' => $reportId
        ]);

    }

    public function validateNewReportForm(){
        foreach ($this->reportLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                if(!isset($_POST["reportName"]) || empty($_POST["reportName"])) {
                    $this->reportErrors["reportName"] = "Bitte Name für Berichtsheft angeben.";
                }
                if(!isset($_POST["reportDescription"]) || empty($_POST["reportDescription"])){
                    $this->reportErrors["reportDescription"] = "Bitte Beschreibung für Berichtsheft angeben.";
                }
            } else {
                $this->reportValidData[$index] = $_POST[$index];
            }       
        }

        if (count($this->reportErrors) > 0) {
            $this->view->setDoMethodName("showNewReportForm");
            $this->showNewReportForm();
        } else {       
            if(!$this->db->setNewReport($this->reportValidData)) {
                new \deinBerichtsheft\Library\ErrorMsg('Beim erstellen deines Berichtshefts ist ein Fehler aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
                die;           
            } else{
                $this->view->setDoMethodName("showNewReportConfirmation");
                $this->showNewReportConfirmation();
            }
        }
    }

    public function validateEditReportForm(){

        $reportId = $_GET["reportId"];

        foreach ($this->reportLabels as $index => $value) {
            if (!isset($_POST[$index]) || empty($_POST[$index])) {
                if(!isset($_POST["reportName"]) || empty($_POST["reportName"])) {
                    $this->reportErrors["reportName"] = "Bitte Name für Berichtsheft angeben.";
                }
                if(!isset($_POST["reportDescription"]) || empty($_POST["reportDescription"])){
                    $this->reportErrors["reportDescription"] = "Bitte Beschreibung für Berichtsheft angeben.";
                }
            } else {
                $this->reportValidData[$index] = $_POST[$index];
            }       
        }

        if (count($this->reportErrors) > 0) {
            $this->view->setDoMethodName("showNewReportForm");
            $this->showNewReportForm();
        } else {     
            array_push($this->reportValidData, $reportId);    
            if(!$this->db->updateReport($this->reportValidData)) {
                new \deinBerichtsheft\Library\ErrorMsg('Beim erstellen deines Berichtshefts ist ein Fehler aufgetreten. Bitte versuche es erneut oder wende dich an den Support.'); 
                die;           
            } else{
                $this->view->setDoMethodName("showEditReportConfirmation");
                $this->showEditReportConfirmation();
            }
        }
    }

}
?>