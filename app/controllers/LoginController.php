<?php
// controls for login page
use \Phalcon\Mvc\View;

/**
 *This class is used to provide the login page at website.
 */
class LoginController extends BaseController
{
    /**
     *This function is used to initialize the view for the login page.
     */
    public function initialize()
    {
        $this->view->setVar("page", "login");
    }

    public function indexAction()
    {
        if ($this->session->has('user-id')) {
            $this->response->redirect($this->app_link);
        }

        $this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
    }

    /**
     *This function is used to call function to authenticate the user credentials entered.
     */
    public function authAction()
    {
        $this->autheticateUserAction($_POST['username'], $_POST['password']);
        exit;
    }

    /**
     *This function is used to authenticate the user credentials entered.
     */
    public function autheticateUserAction($username, $password)
    {
        //$arr = array('conditions' => array('username' => $username, 'password'=>md5($password)),'limit'=>1);
        $password = md5($password);
        $user = Administration::findfirst("username = '$username' AND password = '$password'");
        if (isset($user->id) && $user->id) {
            $this->session->set("user-id", $user->id);
            echo json_encode(array("auth" => "sucss", "msg" => "Redirecting ! !"));
        } else {
            echo json_encode(array('auth' => 'error', 'msg' => 'User Name Or Password Error'));
        }

    }

}