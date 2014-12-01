<?php

class CrawlerControlController extends BaseController
{
    function initialize()
    {
        $this->view->setVar('page', 'crawler_control');
    }

    function indexAction()
    {
        $config = CrawlerConfig::findFirst();
        $this->view->setVar('crawler_config', isset($config->config) ? json_decode($config->config, 1) : array());
        $this->view->setVar('config_id', isset($config->id) ? $config->id : '');

        $agents = CrawlerAgents::find();
        $this->view->setVar('crawler_agents', ($agents !== null and count($agents) > 0) ? $agents : array());
    }

    function settingsAction()
    {
        if (!$this->request->isPost()) {
            return $this->response->redirect($this->app_link . '/crawler_control');
        }

        $atOnce = trim($_POST['atOnce']);
        $maxDepth = trim($_POST['maxDepth']);
        $botName = trim($_POST['botName']);
        $cId = trim($_POST['cid']);

        // some filtering:
        $output = array('error' => 1);
        if (!is_numeric($atOnce) OR !is_numeric($maxDepth)) {
            $output['msg'] = 'Error: Links at once & max depth must be numeric.';
            $this->jsonResponse($output);
        }

        if ($botName == -1) {
            $output['msg'] = 'Error: Please select a user agent.';
            $this->jsonResponse($output);
        }

        $agent = CrawlerAgents::findFirst('id=' . $botName);
        if (!isset($agent->name)) {
            $output['msg'] = 'Error: Unknown user agent.';
            $this->jsonResponse($output);
        }

        $id = $this->saveSettings($cId, $atOnce, $maxDepth, $agent->name);
        $this->jsonResponse(array('error' => 0, 'msg' => 'Your settings were saved!', 'cid' => $id));

        return true;
    }

    /**
     * @param $cId
     * @param $atOnce
     * @param $maxDepth
     * @param $agent_name
     * @return mixed
     */
    private function saveSettings($cId, $atOnce, $maxDepth, $agent_name)
    {
        $data = array(
            'id' => $cId,
            'config' => json_encode(
                array(
                    'atOnce' => $atOnce,
                    'maxDepth' => $maxDepth,
                    'botName' => $agent_name,
                )
            ),
        );

        // toggle case:
        $q = 'INSERT INTO crawler_config (id, config) VALUES (:id, :config)';
        if ($data['id'] !== '' AND $data['id'] !== '-1') {
            $q = 'UPDATE crawler_config SET config=:config WHERE id=:id';
        }

        $statement = $this->db->prepare($q);
        $this->db->executePrepared($statement, $data, array());

        return $this->db->lastInsertId();
    }
}