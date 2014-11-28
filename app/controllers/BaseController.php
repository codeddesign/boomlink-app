<?php
use \Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    public function beforeExecuteRoute()
    {
        # sets:
        $pages_do_logging = array_flip($this->pages_do_log);
        $pages_no_auth = array_flip($this->pages_no_auth);
        $log_skip_actions = array_flip($this->log_skip_actions);

        # get controller:
        $controller = strtolower($this->dispatcher->getControllerName());
        if (!$this->session->has('user-id') and !array_key_exists($controller, $pages_no_auth)) {
            $this->response->redirect($this->app_link);
        }

        // logging:
        if (array_key_exists($controller, $pages_do_logging)) {
            $action = strtolower($this->dispatcher->getActionName());

            //avoid actions like viewing page ("index" - in our case)
            if (!array_key_exists($action, $log_skip_actions)) {
                $params = $this->dispatcher->getParams();

                //prepare information:
                $user_id = $this->session->get("user-id");
                $user_name = $this->session->get("user-name");
                $user_role = $this->session->get("user-role");
                $page = $controller;
                $page_action = $action;
                $action_params = json_encode($params);

                //save info into table:
                $query = "INSERT INTO log_action (user_id,user_name,user_role,page,page_action,action_params) VALUES ('" . $user_id . "','" . $user_name . "','" . $user_role . "','" . $page . "','" . $page_action . "','" . $action_params . "')";
                $this->db->execute($query);
            }
        }
    }

    public function indexAction()
    {
        $this->response->redirect($this->app_link);
    }
}