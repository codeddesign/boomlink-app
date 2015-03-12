<?php
use \Phalcon\Mvc\View;
use \Phalcon\Db;

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
        unset($data['id']);

        $query['insert'] = 'INSERT INTO algorithms (id, user_id, title, is_public, config)';
        $query['values'] = 'VALUES (:id, :user_id, :title, :is_public, :config)';
        $query['duplicate'] = 'ON DUPLICATE KEY UPDATE user_id=VALUES(user_id), title=VALUES(title), is_public=VALUES(is_public), config=VALUES(config)';

        $statement = $this->db->prepare(implode(' ', $query));
        $this->db->executePrepared($statement, array(
            'id' => (trim($backup['id']) === '') ? NULL : $backup['id'],
            'user_id' => $this->session->get('user-id'),
            'title' => $backup['title'],
            'is_public' => $backup['public'],
            'config' => json_encode($data, 1),
        ), array());

        return $this->db->lastInsertId();
    }

    public function deleteAction( $id )
    {
        $userRole = $this->session->get( "user-role" );
        $userId   = $this->session->get( "user-id" );

        //needed:
        $this->view->disable();

        $query = "SELECT id FROM algorithms WHERE id='" . $id . "'";
        if ($userRole !== 'master') {
            $query .= ' AND user_id="' . $userId . '"';
        }

        $status = "failed";
        $msg    = "Failed to remove algorithm.";

        $result = $this->db->fetchAll( $query, Db::FETCH_ASSOC );
        if (count( $result ) > 0) {
            $query  = "DELETE FROM algorithms WHERE id='" . $id . "'";
            $result = $this->db->execute( $query );
            if ($result) {
                $status = "sucss";
                $msg    = "Algorithm was removed successfully.";
            }
        }

        echo json_encode( array( "deleted" => $status, "msg" => $msg ) );
    }
}