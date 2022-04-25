<?php

namespace deinBerichtsheft\Model;

use DateTime;
use PDO;
use PDOException;

class ArticleModel extends Database
{
    public function setNewArticle($values)
    {

        $sql = "INSERT INTO article (`articleText`, `reportId`, `articleDate`) VALUES (:articleText, :reportId, :articleDate);";

        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(
                ":articleText" => $values["articleText"],
                ":reportId" => $values[0],
                ":articleDate" => $values["articleDate"]
            ));
            $sth = null;
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Schreiben der Daten.", $e); 
            die;
        }
  
        return true;
    }

    public function updateArticle($values)
    {

        $sql = "UPDATE article SET articleText = :articleText, articleDate = :articleDate WHERE articleId = :articleId;";
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare($sql);
            $sth->execute(array(
                ":articleText" => $values["articleText"],
                ":articleDate" => $values["articleDate"],
                ":articleId" => $values[0]
            ));
            $sth = null;
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Schreiben der Daten.", $e); 
            die;
        }
  
        return true;
    }

    public function getReportArticles($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('SELECT * FROM article WHERE reportId = ? ORDER BY articleDate DESC;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $articles = $sth->fetchAll(PDO::FETCH_ASSOC);

        if($articles == null || empty($articles)) {
            return false;
        } else {
            return $articles;
        }
        return false;
    }

    public function getArticleById($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('SELECT * FROM article WHERE articleId = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Abrufen der Daten.", $e); 
            die;
        }

        if($sth->rowCount() == 0){
            return false;
        }

        $article = $sth->fetchAll(PDO::FETCH_ASSOC);

        if($article == null || empty($article)) {
            return false;
        } else {
            return $article;
        }
        return false;
    }

    public function deleteArticleById($values)
    {
        $pdo = $this->linkDB();

        try {
            $sth = $pdo->prepare('DELETE FROM article WHERE articleId = ?;');
            $sth->execute(array($values));
        } catch (PDOException $e) {
            new \deinBerichtsheft\Library\ErrorMsg("Fehler beim Löschen der Daten.", $e); 
            die;
        }

        $sth = null;

        return true;
    }

}