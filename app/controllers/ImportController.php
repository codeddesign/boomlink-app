<?php
// controls for import-export page
use \Phalcon\Mvc\View;

class ImportController extends BaseController
{
    protected $domain_name;
    /**
     *This function is used to initialize view for import-export page.
     */
    public function initialize()
    {
        $this->view->setVar("page", "import");
    }

    /**
     *This function is used to get total number of standard and manual list uploaded.
     */
    public function indexAction()
    {
        $all_manual_lists = Lists::find("ListType = 'MANUAL'");
        $this->view->setVar('all_manual_lists', $all_manual_lists);

        foreach ($all_manual_lists as $list) {
            //$name = $list->Name;
            //$info = MasterCampaign::find("Name='".$name."'");
            //$unique_id = $info->
        }

        $all_standard_lists = DomainsToCrawl::count();
        $this->view->setVar('all_standard_lists', $all_standard_lists);

        $get_manual_campaigns = MasterCampaign::find("CampaignType= 'STANDARD'");
        $get_automated_campaigns = MasterCampaign::find("CampaignType= 'MANUAL'");

        $this->view->setVar('get_automated_campaigns', $get_automated_campaigns);
        $this->view->setVar('get_manual_campaigns', $get_manual_campaigns);

        $get_domains = StatusDomain::find();
        $this->view->setVar('get_domains', $get_domains);
    }

    /**
     *This function is used to delete manual list uploaded.
     */
    public function delete_manual_listAction($postid)
    {
        $list = Lists::findfirst("id ='$postid'");
        $msg = "Deleted";
        if ($list->delete() == false) {
            echo "Sorry, we can't delete the List right now: \n";
            foreach ($list->getMessages() as $message) {
                $msg = $message;
            }
        }

        echo json_encode(array("deleted" => "sucss", "msg" => $msg));
        exit;
    }

    public function showAction($postId)
    {

    }

    /**
     *This function is used to upload standard and manual lists.
     */
    public function upload_fileAction($type)
    {
        $request = $this->request;
        if ($request->isPost()) {
            $list_txt = $request->getPost('list_txt');
            $list_type = $request->getPost('list_type');
            $text = trim($list_txt);
            $textAr = explode("\n", $text);
            $textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind

            if ($list_type == "MANUAL") //check if manual list is uploaded
            {
                $list_title = trim($request->getPost('list_title'));
                if ($list_title != '') { // check if title of list is eneterd
                    if ($this->isListExist($list_title) == 0) // check if any list exists with same title
                    {
                        $page_urls = array();
                        foreach ($textAr as $line) {
                            $url = (substr(rtrim($line), -1) == '/') ? substr(rtrim($line), 0, -1) : rtrim($line); //remove back slash of urls
                            $url = str_replace("www.", "", $url);
                            if (!$this->isListURLExist($url) && !in_array($url, $page_urls)) {
                                if ($line != '')
                                    $page_urls[] = $url;
                            }
                        }
                        if (!empty($page_urls)) {
                            $list = new Lists();
                            $list->user_id = $this->session->get("user-id"); // by BOOVAD
                            $list->Name = $list_title;
                            $list->ListType = $list_type;
                            $list->ListType = $list_type;
                            $list->cron_processed = 0;
                            $list->pages_connected = "";
                            $list->ListURL = implode(",", $page_urls);
                            if (!$list->save()) {
                                foreach ($list->getMessages() as $value) {
                                    echo $value->getMessage();
                                }
                            } else {
                                $this->flash->success('Manual List is uploaded successfully.');
                            }
                        } else
                            $this->flash->success('Please enter Unique Manual List URLs.');
                    } else
                        $this->flash->success('Manual List already exists with the entered Title.');
                } else
                    $this->flash->success('Please enter Manual List Title.');
            }//check if standard list is uploaded.
            else {
                $result = DomainsToCrawl::find();
                $idx = array();
                foreach ($result as $row) {
                    $idx[] = $row->idx;
                }

                $i = max($idx) + 1;
                $counter = 0;
                foreach ($textAr as $line) {
                    $url = (substr(rtrim($line), -1) == '/') ? substr(rtrim($line), 0, -1) : rtrim($line); // remove back slash from end of urls
                    $url = str_replace("www.", "", $url); // remove www from URL uploaded
                    if (!$this->isDomainExist($url)) {
                        if ($line != '') {
                            $manual_list = new DomainsToCrawl();
                            $manual_list->DomainURL = $url;
                            $manual_list->idx = $i++;
                            if (!$manual_list->save()) {
                                foreach ($manual_list->getMessages() as $value) {
                                    echo $value->getMessage();
                                }
                            } else {
                                $counter++;
                            }
                        }

                    } else {
                        $this->flash->success($url . " is already exists");
                    }
                }
                if ($counter != 0) {
                    $this->flash->success('Total ' . $counter . ' new record{s} saved successfully.');
                }


            }


        }

        $all_manual_lists = Lists::find("ListType = 'MANUAL'");
        $this->view->setVar('all_manual_lists', $all_manual_lists);
        $all_standard_lists = DomainsToCrawl::count();
        $this->view->setVar('all_standard_lists', $all_standard_lists);

        $get_manual_campaigns = MasterCampaign::find("CampaignType= 'STANDARD'");
        $get_automated_campaigns = MasterCampaign::find("CampaignType= 'MANUAL'");

        $this->view->setVar('get_automated_campaigns', $get_automated_campaigns);
        $this->view->setVar('get_manual_campaigns', $get_manual_campaigns);

        $get_domains = StatusDomain::find();
        $this->view->setVar('get_domains', $get_domains);
    }

    /**
     *This function is used to generate csv of running campaigns selected.
     */
    public function export_campaignAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            $total_campaigns = $request->getPost('total_campaigns');
            $campaigns = array();
            $manual_campaigns = array();
            for ($i = 0; $i < $total_campaigns; $i++) {
                if ($request->getPost('campaign_checkbox_' . $i) == 'on') {
                    $campaigns[] = $request->getPost('campaign_' . $i);
                }
                if ($request->getPost('campaign_checkbox_manual_' . $i) == 'on') {
                    $manual_campaigns[] = $request->getPost('campaign_manual_' . $i);
                }
            }
            $campaigns = implode(",", $campaigns);
            //$rangeQuery1 = array('conditions' => array('Name' => array('$in' => $campaigns )));
            $camps1 = MasterCampaign::find("Name IN ($campaigns)");

            $send_camps = array();
            $send_camps[0][0] = 'Campaign Name';
            $send_camps[0][1] = 'Campaign Type';
            $send_camps[0][2] = 'Start Date';
            $send_camps[0][3] = 'End Date';
            $send_camps[0][4] = 'Campaign For';
            $send_camps[0][5] = 'Keywords';
            $send_camps[0][6] = 'Image URL';
            $send_camps[0][7] = 'Backlink URL';
            $send_camps[0][8] = 'Video URL';
            $j = 1;
            foreach (@$camps1 as $c1) {
                $i = 0;
                $send_camps[$j][$i] = @$c1->Name;
                $send_camps[$j][$i + 1] = @$c1->CampaignType;
                $send_camps[$j][$i + 2] = @date('m/d/Y', @$c1->StartDate);
                $send_camps[$j][$i + 3] = @date('m/d/Y', @$c1->EndDate);
                $links = PagesToCampaign::find("unique_id = '$c1->id'");
                $campaign_url = "";
                foreach ($links as $link) {
                    $campaign_url = $link->campaign_url;
                }
                $send_camps[$j][$i + 4] = @$campaign_url;
                $send_camps[$j][$i + 5] = (@$c1->keywords != '') ? @$c1->keywords : 'NA';
                $send_camps[$j][$i + 6] = 'NA';
                $send_camps[$j][$i + 7] = 'NA';
                $send_camps[$j][$i + 8] = 'NA';
                $j++;
                $send_camps[$j][$i + 0] = 'Page URL';
                $send_camps[$j][$i + 1] = 'Keywords';
                $send_camps[$j][$i + 2] = 'Image URL';
                $send_camps[$j][$i + 3] = 'Video URL';
                $send_camps[$j][$i + 4] = 'HTML Embedded';
                $j++;
                foreach ($links as $link) {
                    $send_camps[$j][$i + 0] = @$link->main_url;
                    $send_camps[$j][$i + 1] = (@$link->achor_text != '') ? @$link->achor_text : 'NA';
                    $send_camps[$j][$i + 2] = (@$link->image_url != '') ? @$link->image_url : 'NA';
                    $send_camps[$j][$i + 3] = (@$link->video_url != '') ? @$link->video_url : 'NA';
                    $send_camps[$j][$i + 4] = (@$link->html_embed != '') ? @$link->html_embed : 'NA';
                    $j++;
                }
                $send_camps[$j][$i + 0] = '';
                $j++;
            }
            $this->array_to_csv_download($send_camps, 'export_campaigns.csv');
        }
        $this->view->disable();
    }

    /**
     *This function is used to export campaigns by selecting dates.
     */
    public function export_campaign_by_dateAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            $start_date = date('m/d/Y', strtotime($request->getPost('start_date')));
            $end_date = date('m/d/Y', strtotime($request->getPost('end_date')));
            $camps1 = MasterCampaign::find("EndDate >= '$start_date' AND EndDate <= '$end_date'");

            $send_camps = array();
            $send_camps[0][0] = 'Campaign Name';
            $send_camps[0][1] = 'Campaign Type';
            $send_camps[0][2] = 'Start Date';
            $send_camps[0][3] = 'End Date';
            $send_camps[0][4] = 'Campaign For';
            $send_camps[0][5] = 'Keywords';
            $send_camps[0][6] = 'Image URL';
            $send_camps[0][7] = 'Backlink URL';
            $send_camps[0][8] = 'Video URL';
            $j = 1;
            foreach (@$camps1 as $c1) {
                $i = 0;
                $send_camps[$j][$i] = @$c1->Name;
                $send_camps[$j][$i + 1] = @$c1->CampaignType;
                $send_camps[$j][$i + 2] = @date('m/d/Y', @$c1->StartDate);
                $send_camps[$j][$i + 3] = @date('m/d/Y', @$c1->EndDate);
                $links = PagesToCampaign::find("unique_id= '$c1->id'");
                $campaign_url = "";
                foreach ($links as $link) {
                    $campaign_url = $link->campaign_url;
                }
                $send_camps[$j][$i + 4] = @$campaign_url;
                $send_camps[$j][$i + 5] = (@$c1->keywords != '') ? @$c1->keywords : 'NA';
                $send_camps[$j][$i + 6] = 'NA';
                $send_camps[$j][$i + 7] = 'NA';
                $send_camps[$j][$i + 8] = 'NA';
                $j++;
                $send_camps[$j][$i + 0] = 'Page URL';
                $send_camps[$j][$i + 1] = 'Keywords';
                $send_camps[$j][$i + 2] = 'Image URL';
                $send_camps[$j][$i + 3] = 'Video URL';
                $send_camps[$j][$i + 4] = 'HTML Embedded';
                $j++;
                foreach ($links as $link) {
                    $send_camps[$j][$i + 0] = @$link->main_url;
                    $send_camps[$j][$i + 1] = (@$link->achor_text != '') ? @$link->achor_text : 'NA';
                    $send_camps[$j][$i + 2] = (@$link->image_url != '') ? @$link->image_url : 'NA';
                    $send_camps[$j][$i + 3] = (@$link->video_url != '') ? @$link->video_url : 'NA';
                    $send_camps[$j][$i + 4] = (@$link->html_embed != '') ? @$link->html_embed : 'NA';
                    $j++;
                }
                $send_camps[$j][$i + 0] = '';
                $j++;
            }
            $this->array_to_csv_download($send_camps, 'export_campaigns_date.csv');
        }
        $this->view->disable();
    }

    /**
     *This function is used to export domains into csv file.
     */
    public function export_domainAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            $total_domains = $request->getPost('total_domains');
            $domain_ids = array();
            for ($i = 0; $i < $total_domains; $i++) {
                if ($request->getPost('domain_checkbox_' . $i) == 'on') {
                    $domain_ids[$i] = $request->getPost('domain_' . $i);
                }
            }
            $domain_ids = array_map('intval', $domain_ids);
            $domain_ids = implode(",", $domain_ids);
            //$rangeQuery = array('conditions' => array('DomainURLIDX' => array( '$in' => $domain_ids )),'limit' => 1000);
            $domain_pages = PageLinkInfo::find("DomainURLIDX IN ($domain_ids) LIMIT 1000");
            $send_dom = array();
            $send_dom[0][0] = 'Domain URL';
            $send_dom[0][1] = 'Page URL';
            $send_dom[0][2] = 'Links';
            $j = 1;
            foreach ($domain_pages as $d) {
                $i = 0;
                $send_dom[$j][$i] = @$d->DomainURL;
                $send_dom[$j][$i + 1] = @$d->PageURL;
                $send_dom[$j][$i + 2] = @$d->Link;
                $j++;
            }

            $this->array_to_csv_download($send_dom, 'export_domains.csv');
        }
        $this->view->disable();
    }

    /**
     *This function is used to write content into csv file.
     */
    function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",")
    {
        // open raw memory as file so no temp files needed, you might run out of memory though
        $f = fopen('php://memory', 'w');
        // loop over the input array
        foreach ($array as $line) {
            // generate csv lines from the inner arrays
            fputcsv($f, $line, $delimiter);
        }
        // rewrind the "file" with the csv lines
        fseek($f, 0);
        // tell the browser it's going to be a csv file
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachement; filename="' . $filename . '"');
        // make php send the generated csv lines to the browser
        fpassthru($f);
    }

    /**
     *This function is used to check whether domain URL already exists or not.
     */
    function isDomainExist($url)
    {
        $status = 0;
        $status = DomainsToCrawl::count("DomainURL = '$url'");
        return $status;
    }

    /**
     *This function is used to check uploaded list url already exists or not.
     */
    function isListURLExist($url)
    {
        $status = 0;
        $status = Lists::count("ListURL='$url'");
        return $status;
    }

    /**
     *This function is used to check whether list title already exists or not.
     */
    function isListExist($name)
    {
        $status = 0;
        $status = Lists::count("Name='$name'");
        return $status;
    }


    public function sitemapAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            $domain_url = $request->getPost('DomainURL');
            $urls = $request->getPost('URL');
            $urls = trim($urls);
            $urls = explode("\n", $urls);
            $urls = array_filter($urls, 'trim'); // remove any extra \r characters left behind
            if (!$domain_url) {
                $this->flash->success('Please enter Domain Url');
            }
            if (empty($urls)) {
                $this->flash->success('Please enter sitemaps');
            }
            if ($domain_url && !preg_match("/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/", $this->addhttp($domain_url))) {
                $this->flash->success('Invalid Domain Url');
            } else {
                if (!empty($urls) && $domain_url) {
                    foreach ($urls as $value) {
                        $site_map = new SiteMap();
                        $site_map->DomainURL = mysql_real_escape_string($domain_url);
                        $site_map->URL = $value;
                        if (!$site_map->save()) {
                            foreach ($site_map->getMessages() as $value) {
                                echo $value->getMessage();
                            }
                            exit;
                        }
                    }
                    $this->flash->success("$domain_url added successfully");
                }
            }
        }
    }

    function addhttp($url)
    {
        $parse = parse_url($url);
        $url = preg_replace('#^www\.(.+\.)#i', '$1', (isset($parse['host']) && $parse['host']) ? $parse['host'] : $parse['path']);
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }

    function createAction()
    {
        if (!$this->request->isPost()) {
            return $this->response->redirect($this->app_link . '/import');
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
        $statement = $this->db->prepare('INSERT INTO status_domain (user_id, project_title, domain_name, DomainURL) VALUES (:user_id, :title, :domain, :domain_url)');
        $this->db->executePrepared($statement, array(
            'user_id' => $this->session->get('user-id'),
            'title' => $p_title,
            'domain' => $this->domain_name,
            'domain_url' => $p_link,
        ), array());

        // to be added:
        $data = array(
            'idx' => $this->db->lastInsertId(),
            'domain_url' => $p_link,
        );

        /*// domains to crawl:
        $statement = $this->db->prepare('INSERT INTO domains_to_crawl (idx, DomainURL) VALUES (:idx, :domain_url)');
        $this->db->executePrepared($statement, $data, array());*/

        // status domain - add main link
        $statement = $this->db->prepare('INSERT INTO page_main_info (DomainURLIDX, PageURL) VALUES (:idx, :domain_url)');
        $this->db->executePrepared($statement, $data, array());
    }
}
