<?php
/**
 *This class is used to provide link to Logout from the site.
 */
use \Phalcon\Mvc\View;
use \Phalcon\Http\Response;

class LogoutController extends BaseController
{
    /**
     *This function is used to call function to initialize the variables that may in use.
     */
    public function initialize()
    {

    }

    /**
     *This function is used to destroy the user's sessions.
     */
    public function indexAction()
    {
        $this->session->destroy();

        //An HTTP Redirect
        $this->response->redirect($this->app_link);

        //Disable the view to avoid rendering
        $this->view->disable();
    }
}
