<?php

namespace deinBerichtsheft\Library;

use deinBerichtsheft\Library\View;

class ErrorMsg
{
    protected $view;

    public function __construct($msg = 'Ihre Anfrage konnte nicht verarbeitet werden', $ex = '')
    {
        $this->view = new View(dirname(__DIR__).DIRECTORY_SEPARATOR.'Views'
        , 'Error', 'showErrMsg');
        $this->view->setVars([
            'error' => $msg,
            'debug' => $ex

        ]);
        $this->view->render();
    }

}