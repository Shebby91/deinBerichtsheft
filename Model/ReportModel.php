<?php

namespace deinBerichtsheft\Model;

use DateTime;
use PDO;
use PDOException;

class ReportModel extends Database
{
    public function setNewReport($values)
    {
        $currentDate = new DateTime("Now");
        $date = $currentDate->format('Y-m-d H:i:s');
        $sql = "INSERT INTO report (`userId`, `reportName`, `reportCreatedAt`, `reportDescription`) VALUES (:user, :reportName, :reportDate, :reportDescription);";

        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(
                ":user" => $_SESSION["userId"], 
                ":reportName" => $values["reportName"],
                ":reportDate" => $date,
                ":reportDescription" => $values["reportDescription"]
            ));
            $sth = null;
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Schreiben der Daten.", $e); 
            die;
        }
  
        return true;
    }

    public function getUserReports()
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('SELECT * FROM report WHERE userId = ? ORDER BY reportId DESC;');
            $sth->execute(array($_SESSION["userId"]));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $reports = $sth->fetchAll(PDO::FETCH_ASSOC);

        if($reports == null || empty($reports)) {
            return false;
        } else {
            return $reports;
        }
        return false;
    }

    public function deleteUserReports($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('DELETE FROM article WHERE reportId = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Löschen der Daten.", $e); 
            die;
        }

        $sth = null;

        try {
            $sth = $pdo->prepare('DELETE FROM report WHERE reportId = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Löschen der Daten.", $e); 
            die;
        }
        $sth = null;

        return true;
    }

    public function updateReport($values)
    {

        $sql = "UPDATE report SET reportName = :reportName, reportDescription = :reportDescription WHERE reportId = :reportId;";
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(
                ":reportName" => $values["reportName"],
                ":reportDescription" => $values["reportDescription"],
                ":reportId" => $values[0]
            ));
            $sth = null;
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Schreiben der Daten.", $e); 
            die;
        }
  
        return true;
    }

    public function getReportById($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('SELECT * FROM report WHERE reportId = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $report = $sth->fetchAll(PDO::FETCH_ASSOC);

        if($report == null || empty($report)) {
            return false;
        } else {
            return $report;
        }
        return false;
    }

}