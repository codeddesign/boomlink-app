<?php

class AlgoController extends BaseController
{
    function initialize()
    {
        $this->view->setVar('page', 'algo');
    }

    function indexAction()
    {
        $this->view->setVar('algorithms', Algorithms::find());
    }

    function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->response->redirect($this->page_link . '/algo');
        }

        /* prepare data: */
        $data = $_POST;
        foreach ($data as $d_key => $d_val) {
            $data[$d_key] = trim($d_val);
        }

        // handle checkboxes:
        $checkboxes_names = array(
            'public',
            'sentiment_positive',
            'sentiment_neutral',
            'sentiment_negative',
        );

        $output = array('error' => 1);
        foreach ($checkboxes_names as $c_no => $name) {
            $data[$name] = !isset($_POST[$name]) ? 0 : 1;
        }

        // pre-checks:
        if (strlen($data['title']) == 0) {
            $output['msg'] = 'The title can\'t be empty.';
            $this->jsonResponse($output);
        }

        $aid = $this->saveData($data);
        $this->jsonResponse(array('error' => 0, 'msg' => 'Success: Your algorithm was saved.', 'aid' => $aid));

        return true;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function saveData($data)
    {
        $backup = $data;
        unset($data['title']);
        unset($data['public']);

        $statement = $this->db->prepare('INSERT INTO algorithms (user_id, title, is_public, config) VALUES (:user_id, :title, :is_public, :config)');
        $this->db->executePrepared($statement, array(
            'user_id' => $this->session->get('user-id'),
            'title' => $backup['title'],
            'is_public' => $backup['public'],
            'config' => json_encode($data, 1),
        ), array());

        return $this->db->lastInsertId();
    }
}