<?php

class CrawlProjectController extends BaseController
{
    protected $domain_name;

    function initialize()
    {
        $this->view->setVar('page', 'crawl_project');
    }

    function indexAction()
    {
        $this->view->setVar('projects', StatusDomain::find());
    }

    function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->response->redirect($this->app_link . '/crawl_project');
        }

        $output = array('error' => 1);

        # some filtering:
        if (strlen(trim($_POST['title'])) == 0) {
            $output['msg'] = 'Error: project title can\'t be empty.';
            $this->jsonResponse($output);
        }

        $this->domain_name = $this->getHost($_POST['link']);
        if (!$this->domain_name) {
            $output['msg'] = 'Error: invalid link.';
            $this->jsonResponse($output);
        }

        $resp = StatusDomain::find('domain_name = \'' . $this->domain_name . '\'');
        if (count($resp) > 0) {
            $output['msg'] = 'Error: this domain exist for project \'' . $resp->getFirst()->project_title . '\'.';
            $this->jsonResponse($output);
        }

        $this->addProject();
        $this->jsonResponse(array('error' => 0, 'msg' => 'Success: Your project was saved!'));

        return true;
    }

    /**
     * @param $link
     * @return bool
     */
    private function linkHasScheme($link)
    {
        return (strtolower(substr($link, 0, 4)) === 'http');
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    private function getHost($url)
    {
        $url = trim($url);

        // parse_url() won't work properly if 'http' is missing:
        if (!$this->linkHasScheme($url)) {
            $url = 'http://' . $url;
        }

        $parts = parse_url($url);
        if (!$parts) {
            return false;
        }

        if (array_key_exists("host", $parts)) {
            $result = str_ireplace("www.", "", $parts["host"]);
            if (stripos($result, '.') === false) {
                return false;
            }

            $parts = explode('.', $result);
            foreach ($parts as $p_no => $part) {
                if (strlen($part) < 2) {
                    return false;
                }
            }

            return $result;
        }

        return false;
    }

    /* adds the new project's needed information to a few db tables */
    private function addProject()
    {
        $p_title = $_POST['title'];
        $p_link = $_POST['link'];

        // status domain:
        $statement = $this->db->prepare('INSERT INTO status_domain (user_id, project_title, domain_name, DomainURL, config) VALUES (:user_id, :title, :domain, :domain_url, :config)');
        $this->db->executePrepared($statement, array(
            'user_id' => $this->session->get('user-id'),
            'title' => $p_title,
            'domain' => $this->domain_name,
            'domain_url' => $p_link,
            'config' => '',
        ), array());

        // to be added:
        $data = array(
            'idx' => $this->db->lastInsertId(),
            'domain_url' => $p_link,
        );

        // domains to crawl:
        $statement = $this->db->prepare('INSERT INTO domains_to_crawl (idx, DomainURL) VALUES (:idx, :domain_url)');
        $this->db->executePrepared($statement, $data, array());

        // status domain - add main link
        $statement = $this->db->prepare('INSERT INTO page_main_info (DomainURLIDX, PageURL) VALUES (:idx, :domain_url)');
        $this->db->executePrepared($statement, $data, array());
    }
}