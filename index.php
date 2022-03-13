<?php 
session_start();

spl_autoload_register(function ($className) {
    $fileName = __DIR__.'/'.str_replace('\\', DIRECTORY_SEPARATOR, substr($className, 17)).'.php';
    if (file_exists($fileName)) {
            include $fileName;
    }
});    
    
$controllerName = "";
$doMethodName = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controllerName = isset($_POST['controller']) && $_POST['controller'] ? $_POST['controller'] : "Login";
    $doMethodName = isset($_POST['do']) && $_POST['do'] ? $_POST['do'] : "showLoginForm";
} else {
    $controllerName = isset($_GET['controller']) && $_GET['controller'] ? $_GET['controller'] : "Login";
    $doMethodName = isset($_GET['do']) && $_GET['do'] ? $_GET['do'] : "showLoginForm";
    
}

$controllerClassName = 'deinBerichtsheft\\Controller\\'.ucfirst($controllerName).'Controller';

if (method_exists($controllerClassName, $doMethodName)) {

    $view = new \deinBerichtsheft\Library\View(__DIR__.DIRECTORY_SEPARATOR.'Views'
                , ucfirst($controllerName), $doMethodName);
    
    $controller = new $controllerClassName($view);
    $controller->$doMethodName();

    $view->render();

} else {
    new \deinBerichtsheft\Library\ErrorMsg('Page not found: '.$controllerClassName.'::'.$doMethodName); 
}
  
?>
       
