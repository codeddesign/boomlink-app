<?php
use \Phalcon\Mvc\View;
use \Phalcon\Db;

class CampaignController extends BaseController
{
    public $output = "";
    public $counter = 0;
    public $temp_domain_ids = array();
    public $greater_ids = array();
    public $records_per_search = 10;

    /**
     *This function is used to initialize view of administrative pge.
     */
    public function initialize()
    {
        $this->view->setVar("page", "campaign");
    }

    /**
     *This function is used to prepare the drop down vales for Domains and lists of search form.
     */
    public function indexAction()
    {
        $get_domains = StatusDomain::find();
        $get_lists = Lists::find("ListType = 'STANDARD'");
        $this->view->setVar('get_domains', $get_domains);
        $this->view->setVar('get_lists', $get_lists);
        $this->view->setVar('algorithms', Algorithms::find());

        if (isset($_GET['name']) && trim($_GET['name']) != '') {
            $this->flash->success('The Standard Campaign "' . $_GET['name'] . '" is built successfully');
        }
    }

    /**
     * @param $reg_date
     * @return int
     */
    private function getDomainAgeByDate($reg_date)
    {
        $date = new DateTime($reg_date);
        $now = new DateTime();
        return $now->diff($date)->y;
    }

    /**
     * @param $domains
     * @return array
     */
    private function getDomainsAge($domains)
    {
        $temp = array();
        foreach ($domains as $domain) {
            $reg_date = strtolower(trim($domain->registration_date));
            $reg_date = ($reg_date == 0) ? '' : $reg_date;

            $temp[$domain->DomainURLIDX] = ($reg_date == '') ? 0 : $this->getDomainAgeByDate($reg_date);
        }

        return $temp;
    }

    /**
     *This function is used to save build campaigns in the database.
     */
    public function build_campaignAction()
    {
        $this->view->setVar('get_domains', StatusDomain::find());
        $this->view->setVar('get_lists', Lists::find("ListType='STANDARD'"));

        if ($this->request->isPost()) {
            $unique_id = md5(date('Y-m-d H:i:s'));
            $master_campaign = new MasterCampaign();
            $master_campaign->Name = $_POST['campaign_name'];
            $master_campaign->StartDate = date("Y-m-d H:i:s", strtotime($_POST['start_date'] . " 00:00:00"));
            $master_campaign->EndDate = date("Y-m-d H:i:s", strtotime($_POST['end_date'] . " 00:00:00"));
            $master_campaign->CampaignType = 'STANDARD';
            $master_campaign->unique_id = $unique_id;
            if ($master_campaign->save() == false) {
                foreach ($master_campaign->getMessages() as $message) {
                    echo $message->getMessage() . '<br>';
                }
            }

            // ..
            $auto_complete_url = trim($_POST['auto_complete_url']);
            $selected_domain_id = trim($_POST['selected_domain_id']);

            if (isset($_POST['page_url_checkbox']) and count($_POST['page_url_checkbox'])) {
                foreach ($_POST['page_url_checkbox'] as $urls) {
                    $page_to_campaign = new PagesToCampaign();
                    $url_split = explode("$%", $urls);
                    $page_to_campaign->unique_id = $unique_id;
                    $page_to_campaign->campaign_url = $auto_complete_url;
                    $page_to_campaign->DomainURLIDX = $selected_domain_id;
                    $page_to_campaign->main_url = (substr($url_split[0], -1) == '/') ? substr($url_split[0], 0, -1) : $url_split[0];
                    $page_to_campaign->url_ref = $url_split[0];
                    $page_to_campaign->StartDate = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
                    $page_to_campaign->EndDate = date("Y-m-d H:i:s", strtotime($_POST['end_date']));

                    if (isset($_POST['keywords']) && $_POST['keywords']) {
                        $page_to_campaign->keywords = $_POST['keywords'];
                    }

                    if (isset($_POST['textbox_anchor_text' . $url_split[2]]) && $_POST['textbox_anchor_text' . $url_split[2]]) {
                        $page_to_campaign->achor_text = $_POST['textbox_anchor_text' . $url_split[2]];
                    }

                    if (isset($_POST['textbox_image_url' . $url_split[2]]) && $_POST['textbox_image_url' . $url_split[2]]) {
                        $page_to_campaign->image_url = $_POST['textbox_image_url' . $url_split[2]];
                    }

                    if (isset($_POST['textbox_video_url' . $url_split[2]]) && $_POST['textbox_video_url' . $url_split[2]]) {
                        $page_to_campaign->video_url = $_POST['textbox_video_url' . $url_split[2]];
                    }

                    if (isset($_POST['textbox_html' . $url_split[2]]) && $_POST['textbox_html' . $url_split[2]]) {
                        $page_to_campaign->html_embed = $_POST['textbox_html' . $url_split[2]];
                    }

                    if (isset($_POST['textbox_keywords_analytics' . $url_split[2]]) && $_POST['textbox_keywords_analytics' . $url_split[2]]) {
                        $page_to_campaign->keywords_for_analytics = $_POST['textbox_keywords_analytics' . $url_split[2]];
                    }

                    $page_to_campaign->save();
                }

                return $this->response->redirect($this->app_link . '/campaign?name=' . addslashes($_POST['campaign_name']));
            }
        }

        return $this->response->redirect($this->app_link . '/campaign');
    }

    /*
     * Handles the keyword/s search via ajax:
     * */
    public function searchAction()
    {
        // post data:
        $backup_lod = $list_of_domains = json_decode(trim($_POST['list_of_domains']), 1);
        $campaign_name = trim($_POST['campaign_name']);
        $keywords = trim($_POST['keywords']);
        $selected_domain_id = trim($_POST['selected_domain']);
        $start_date = trim($_POST['start_date']);
        $end_date = trim($_POST['start_date']);
        $auto_complete_url = trim($_POST['auto_complete_url']);
        $pages_per_domain = trim($_POST['pages_per_domain']);

        $output = array('error' => 1);
        if (!isset($_POST['algorithm_id'])) {
            $output['msg'] = 'You must select and algorithm!';
            $this->jsonResponse($output);
        }
        $algorithm_id = trim($_POST['algorithm_id']);

        // determine if campaign exists:
        $total = MasterCampaign::count("Name = '" . $campaign_name . "'");
        if ($total) {
            $output['msg'] = 'Campaign already exists!';
            $this->jsonResponse($output);
        }

        // manage list of domains and their ids:
        if (!is_array($list_of_domains) AND count($list_of_domains) < 2) {
            $output['msg'] = 'Not enough domains to create a campaign!';
            $this->jsonResponse($output);
        }

        unset($list_of_domains[$selected_domain_id]);
        $list_of_domains = array_values(array_flip($list_of_domains));

        // manage searched keywords:
        if (!strlen($keywords)) {
            $output['msg'] = 'No keyword to search!';
            $this->jsonResponse($output);
        }

        // get algorithm config:
        $algorithm = $this->db->fetchOne(sprintf('SELECT * FROM algorithms WHERE id=%d', $algorithm_id), Db::FETCH_ASSOC);
        $algorithm_config = json_decode($algorithm['config'], 1);
        $sentimental_types = array();

        $enable_proxy_data = $enable_api_data = false;
        foreach ($algorithm_config as $key => $value) {
            switch ($key) {
                case 'sentiment_negative':
                    $generic_value = 0;
                    break;
                case 'sentiment_positive':
                    $generic_value = 1;
                    break;
                case 'sentiment_neutral':
                    $generic_value = 2;
                    break;
                default:
                    $generic_value = -1;
                    break;
            }

            if( (stripos($key, 'sentiment') !== false OR $key == 'incoming') and (int)$value !== 0) {
                $enable_api_data = true;
            }

            if( (stripos($key, 'page_rank') !== false OR $key=='share') AND (int)$value !== 0) {
                $enable_proxy_data = true;
            }

            if ($generic_value > -1 AND $value == 1) {
                $sentimental_types[] = $generic_value;
            }
        }

        /* build up query to search for keyword: */
        $result = array();
        foreach ($list_of_domains as $d_no => $domain_id) {
            $query['select'] = 'SELECT pmi.*, pmip.*, pmib.body, pmib.page_id, (SELECT IFNULL(GROUP_CONCAT(pmih.heading_text), "") FROM page_main_info_headings pmih WHERE pmib.page_id = pmih.page_id) as heading_text FROM  `page_main_info_body` pmib';

            $query['join_1'] = 'INNER JOIN page_main_info pmi ON pmib.page_id = pmi.id';
            $query['join_2'] = 'INNER JOIN page_main_info_points pmip ON pmib.page_id = pmip.page_id';

            $query['condition_0'] = 'WHERE pmib.body REGEXP "[[:<:]]' . addslashes($keywords) . '[[:>:]]"';
            //$query['condition_0'] = 'WHERE pmib.body LIKE "%' . addslashes($keywords) . '%"';
            //$query['condition_0'] = 'WHERE match(pmib.body) against (\''.addslashes($keywords).'\')';
            $query['condition_1a'] = 'AND pmi.parsed_status = 1';

            if($enable_api_data) {
                $query['condition_1b'] = 'AND pmi.api_data_status = 1';
            }

            if($enable_proxy_data)
            {
                $query['condition_1c'] = 'AND pmi.proxy_data_status = 1';
            }

            $query['condition_2'] = 'AND pmip.algo_id = ' . $algorithm['id'];

            $query['conditions_x'] = 'AND pmi.DomainURLIDX = ' . $domain_id;
            if (count($sentimental_types)) {
                $query['condition_y'] = 'AND pmi.sentimental_type IN (' . implode(', ', $sentimental_types) . ')';
            }

            $query['order'] = 'ORDER BY pmip.points DESC';
            $query['temp_limit'] = 'LIMIT ' . $pages_per_domain;

            // run query
            $temp = $this->db->fetchAll(implode(' ', $query), Db::FETCH_ASSOC);
            if (count($temp)) {
                $result = array_merge($result, $temp);
            }

            unset($temp);
            unset($query);
        }

        if (!count($result)) {
            $output['msg'] = 'No results returned!';
            $this->jsonResponse($output);
        }

        // get domains_age:
        $domains_age = $this->getDomainsAge(StatusDomain::find());

        // array -> uniqueness:
        $save_page_id = array();
        foreach ($result as $r_no => $r) {
            if (isset($save_page_id[$r['page_id']])) {
                unset($result[$r_no]);
            } else {
                // save as reference:
                $save_page_id[$r['page_id']] = '';

                // remove content:
                $result[$r_no]['body'] = null;

                // add more points:
                $check_by_keys = array(
                    'keyword_title' => 'page_title',
                    'keyword_meta' => 'description',
                    'keyword_h' => 'heading_text',
                );

                foreach ($check_by_keys as $algo_key => $result_key) {
                    if ($this->keywordExists($keywords, $result[$r_no][$result_key])) {
                        $result[$r_no]['points'] += $algorithm_config[$algo_key];
                        $result[$r_no][$result_key] = 1;
                    } else {
                        $result[$r_no][$result_key] = 0;
                    }
                }

                // no need to check content:
                $result[$r_no]['points'] += $algorithm_config['keyword_content'];
            }
        }

        // array -> sort descending by points:
        uasort($result, array($this, 'descendingSortByPoints'));

        // handle percentage and data for filtering:
        $first = false;
        $min_max = $results_filtered = array();
        $avoid_keys = array_flip(array('page_id', 'PageURL', 'keyword_title', 'keyword_description', 'keyword_headings'));

        foreach ($result as $s_no => $link) {
            if (!$first) {
                $first = $link['points'];
            }

            // save percentage to main array:
            if((int)$first !== 0) {
                $temp_perc = floor( $link['points'] * 100 / $first );
            } else {
                $temp_perc = 0;
            }

            $result[$s_no]['percentage'] = $percentage = $temp_perc;

            // build up array for JS:
            $incoming_links = $result[$s_no]['total_back_links'];
            $outgoing_links = $result[$s_no]['follow_links'] + $result[$s_no]['no_follow_links'];
            $google_rank = ($result[$s_no]['google_rank'] == null) ? 0 : $result[$s_no]['google_rank'];
            $share_count = $link['fb_shares'] + $link['fb_likes'] + $link['fb_comments'] + $link['tweeter'] + $link['google_plus'];

            $temp = array(
                'page_id' => (int)$result[$s_no]['page_id'],
                'PageURL' => $result[$s_no]['PageURL'],
                'keyword_title' => (int)$result[$s_no]['heading_text'],
                'keyword_description' => (int)$result[$s_no]['description'],
                'keyword_headings' => (int)$result[$s_no]['heading_text'],
                'percentage' => (int)$percentage,
                'incoming_links' => (int)$incoming_links,
                'outgoing_links' => (int)$outgoing_links,
                'google_rank' => (int)$google_rank,
                'share_count' => (int)$share_count,
                'sentiment' => (int)$result[$s_no]['sentimental_type'],
                'domain_age' => (int)$domains_age[$result[$s_no]['DomainURLIDX']],
            );
            $results_filtered[] = $temp;

            // handle minimum-maximum available in filtering:
            foreach ($temp as $key => $value) {
                if (!isset($avoid_keys[$key])) {
                    if (!isset($min_max[$key]['min']) OR $value <= $min_max[$key]['min']) {
                        $min_max[$key]['min'] = $value;
                    }

                    if (!isset($min_max[$key]['max']) or $value >= $min_max[$key]['max']) {
                        $min_max[$key]['max'] = $value;
                    }
                }
            }
        }

        // ..
        $this->view->disableLevel(View::LEVEL_MAIN_LAYOUT);
        $this->view->disableLevel(View::LEVEL_LAYOUT);

        echo $this->view->getRender('layouts', 'campaign_ajax', array(
            'results' => $result,
            'results_filtered' => $results_filtered,
            'min_max' => $min_max,
            'sentimental_types' => $sentimental_types,
            'campaign_name' => $campaign_name,
            'keywords' => $keywords,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'list_of_domains' => $backup_lod,
            'auto_complete_url' => $auto_complete_url,
            'selected_domain_id' => $selected_domain_id,
        ));
    }

    /**
     * @param $needle
     * @param $haystack
     * @return int
     */
    private function keywordExists($needle, $haystack)
    {
        return (preg_match('#(' . $needle . ')#is', $haystack));
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    private function descendingSortByPoints($a, $b)
    {
        if ($a['points'] == $b['points']) {
            return 0;
        }

        return ($a['points'] > $b['points']) ? -1 : 1;
    }

    /**
     * This function is used to get keywords of Automated Campaign.
     */
    public function getPreviousKeywordsAction()
    {
        $keywords = PagesToCampaign::find("url_ref = '" . trim($_POST['page_url']) . "'");
        $result = "";
        $i = 0;
        $pattern = '<li><a href="javascript:setKeywords(%d)">%s</a><input type="hidden" value="%s" id="achor_text_%d"/><input type="hidden" value="%s" id="html_text_%d"/></li>';
        foreach ($keywords as $value) {
            $i++;
            $result .= sprintf($pattern, $i, $value->keywords, $value->achor_text, $i, $value->html_embed, $i);
        }

        $this->jsonResponse(array('record' => $result));
    }
}
