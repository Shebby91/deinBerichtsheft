<?php

namespace deinBerichtsheft\Controller;

class WelcomeController
{
    function showWelcome() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=Login&do=showLoginForm");
        }
    }

    function showProjects() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=Login&do=showLoginForm");
        }
    }

    function showTutorials() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=Login&do=showLoginForm");
        }
    }

    function showLogout() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=Login&do=showLoginForm");
        }
        session_unset();
        session_destroy();
    }
}
