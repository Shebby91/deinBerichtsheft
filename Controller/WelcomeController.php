<?php

namespace deinBerichtsheft\Controller;

class WelcomeController
{
    function showWelcome() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }
    }

    function showProjects() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }
    }

    function showTutorials() {
        if(!isset($_SESSION["userUid"])) {
            header("location: ../index.php?controller=User&do=showLoginForm");
        }
    }

}
