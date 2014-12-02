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
        $this->view->setVar('crawler_path', isset($config->crawler_path) ? $config->crawler_path : '');

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
        $crawlerPath = trim($_POST['crawlerPath']);

        $output = array('error' => 1);
        if ('exists' !== $this->statusAction('exists', $crawlerPath, true)) {
            $output['msg'] = 'Error: The crawler was not detected on that path.';
            $this->jsonResponse($output);
        }

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

        $id = $this->saveSettings($cId, $crawlerPath, $atOnce, $maxDepth, $agent->name);
        $this->jsonResponse(array('error' => 0, 'msg' => 'Your settings were saved!', 'cid' => $id));

        return true;
    }

    function statusAction($op = 'exists', $link = false, $returns = false)
    {
        if (!$link) {
            $config = CrawlerConfig::findFirst();
            if ($config === null OR !isset($config->crawler_path) OR strlen(trim($config->crawler_path)) == 0) {
                $this->jsonResponse('you don\'t have a crawler path setup.');
            }

            $link = $config->crawler_path;
        }

        $curl_obj = new CurlController();
        $response = $curl_obj->getResponse($link . '?op=' . $op);
        if ($returns) {
            return $response;
        }

        $this->jsonResponse($response);

        return true;
    }

    /**
     * @param $cId
     * @param $atOnce
     * @param $maxDepth
     * @param $agent_name
     * @return mixed
     */
    private function saveSettings($cId, $crawlerPath, $atOnce, $maxDepth, $agent_name)
    {
        $data = array(
            'id' => $cId,
            'crawler_path' => $crawlerPath,
            'config' => json_encode(
                array(
                    'atOnce' => $atOnce,
                    'maxDepth' => $maxDepth,
                    'botName' => $agent_name,
                )
            ),
        );

        // toggle case:
        $q = 'INSERT INTO crawler_config (id, crawler_path, config) VALUES (:id, :crawler_path, :config)';
        if ($data['id'] !== '' AND $data['id'] !== '-1') {
            $q = 'UPDATE crawler_config SET crawler_path=:crawler_path, config=:config WHERE id=:id';
        }

        $statement = $this->db->prepare($q);
        $this->db->executePrepared($statement, $data, array());

        return $this->db->lastInsertId();
    }
}