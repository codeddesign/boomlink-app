<?php
use \Phalcon\Mvc\View;
use \Phalcon\Mvc\Controller;

class QuestionsController extends Controller {
    function initialize() {
        $this->view->setVar("page", "documentation");
    }

    function indexAction() {
        $this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
    }
}