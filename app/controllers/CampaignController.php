<?php
use \Phalcon\Mvc\View;

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
        if (isset($_GET['name']) && trim($_GET['name']) != '') {
            $this->flash->success('The Standard Campaign "' . $_GET['name'] . '" is built successfully');
        }
    }

    /**
     *This function is used to save build campaigns in the database.
     */
    public function build_campaignAction()
    {
        $get_domains = StatusDomain::find();
        $get_lists = Lists::find("ListType='STANDARD'");
        $this->view->setVar('get_domains', $get_domains);
        $this->view->setVar('get_lists', $get_lists);
        $request = $this->request;
        if ($request->isPost()) {

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

            if (isset($_POST['page_urls']) && $_POST['page_urls']) {
                foreach ($_POST['page_urls'] as $value) {
                    $campaing_url = split("@#", $value);
                    foreach ($_POST['page_url_checkbox'] as $urls) {
                        $page_to_campaign = new PagesToCampaign();
                        $url_split = explode("$%", $urls);
                        $page_to_campaign->unique_id = $unique_id;
                        $page_to_campaign->campaign_url = $campaing_url[0];
                        //$page_to_campaign->campaign_url_DomainURLIDX = $campaing_url[1];
                        $page_to_campaign->main_url = (substr($url_split[0], -1) == '/') ? substr($url_split[0], 0, -1) : $url_split[0];
                        $page_to_campaign->url_ref = $url_split[0];
                        $page_to_campaign->DomainURLIDX = $campaing_url[1];
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
                }
            } else {
                foreach ($_POST['main_domain_url'] as $value) {
                    foreach ($_POST['page_url_checkbox'] as $urls) {
                        $page_to_campaign = new PagesToCampaign();
                        $url_split = explode("$%", $urls);
                        $page_to_campaign->unique_id = $unique_id;
                        $page_to_campaign->campaign_url = $value;
                        $page_to_campaign->main_url = (substr($url_split[0], -1) == '/') ? substr($url_split[0], 0, -1) : $url_split[0];
                        $page_to_campaign->url_ref = $url_split[0];
                        $page_to_campaign->DomainURLIDX = $url_split[1];
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
                }
            }

            header('Location: '.$this->app_link.'/campaign?name=' . $_POST['campaign_name']);
        }
    }

    /**
     *This function is used to get categories of Pages.
     */
    function get_categoriesAction()
    {
        $request = $this->request;
        if ($request->isPost()) {

            $domainss = $_POST['domains'];
            $domains = json_decode($domainss);
            $domains = array_map('intval', $domains);

            $pages = $_POST['pages'];
            $pages = json_decode($pages);
            $domains = implode(",", $domains);
            $pages = implode(",", $pages);
            $arr = array();

            //$rangeQuery = array('conditions' => array('DomainURLIDX' => array( '$in' => $domains ),'PageURL'=>array( '$in' => $pages )),'limit'=>1);
            $cursor = PageMainInfo::find("DomainURLIDX IN($domains) AND PageURL IN('$pages') LIMIT 1");

            $i = 0;
            foreach ($cursor as $dom) {
                $arr[$i] = $dom->SiteCate1;
                $i++;
                $arr[$i] = $dom->SiteCate2;
                $i++;
                $arr[$i] = $dom->SiteCate3;
                $i++;
            }
            $this->view->disable();
            echo json_encode(array_unique($arr));
        }
    }

    /**
     *This function is used to get searched results of the search form.
     */
    public function generateAction()
    {
        $campaign_name = $_POST['campaign_name'];
        $domain_ids = $_POST['domain_ids'];
        $domain_ids = json_decode($domain_ids);
        $domain_ids = array_map('intval', $domain_ids);

        $page_urls_with_id = $_POST['page_urls_with_id'];
        $page_urls_with_id = json_decode($page_urls_with_id);
        $main_domain_url = $_POST['main_domain_url'];
        $main_domain_url = json_decode($main_domain_url);
        $pages_per_domain = (is_numeric($_POST['pages_per_domain'])) ? $_POST['pages_per_domain'] : 3;
        $categories = $_POST['categories'];
        $categories = json_decode($categories);
        $this->output = '';
        $this->output .= '
                <div class="listtitlebar">
                        <div class="pagetitlebar-title">PASSENDEN WEBSITE\'S</div>
                </div>
                <div class="listtitlesubbar">
                        <div class="webaddressbar">INTERNET ADRESSE</div>
                        <div class="bestmatchbar">BEST MATCH</div>
                </div>
                
                <form name="build-campaign" action="'.$this->app_link.'/campaign/build_campaign" method="post" onsubmit="return check_domain_selected();">
                    <input type="hidden" name="campaign_name" value="' . $campaign_name . '" />
                    <input type="hidden" name="keywords" value="' . @$_POST['keywords'] . '" />
                    <input type="hidden" name="start_date" value="' . $_POST['start_date'] . '" />
                    <input type="hidden" name="end_date" value="' . $_POST['end_date'] . '" />';

        foreach ($page_urls_with_id as $p) {
            $this->output .= '<input type="hidden" name="page_urls[]" value="' . $p . '"/>';
        }
        foreach ($main_domain_url as $main_url) {
            $this->output .= '<input type="hidden" value="' . $main_url . '" name="main_domain_url[]"/>';
        }


        $this->makeOutput($domain_ids, $categories, $pages_per_domain);
        $this->output .= '<input type="hidden" name="total_page_urls" id="total_page_urls" value="' . $this->counter . '" />
                                <div class="underlistbar"></div>';
        if ($this->session->has("user-role") && $this->session->get("user-role") == 'master')
            $this->output .= '<button type="" class="buildcampbutton" name="buildcampbutton" style="border:none; cursor:pointer"></button>';
        $this->output .= '</form>';

        echo $this->output;
        exit;

    }

    /**
     *This function is used to get KWSentiment values of Pages.
     */
    public function SentimentValueAction()
    {
        $page_url = $_POST['page_url'];
        $get_page_sentiments = PageKwInfo::find(array('PageURL' => $page_url, "limit" => 1));
        $result = 0;
        foreach ($get_page_sentiments as $value) {
            $result = ucfirst($value->KWSentiment);
        }
        echo json_encode(array("KWSentiment" => $result));
        exit;
    }

    /**
     *This function is used to check whether any campaign already exists with the same name.
     */
    public function isCampaignExistsAction()
    {
        $total = MasterCampaign::count("Name = '" . $_POST['campaign_name'] . "'");
        if ($total) {
            echo json_encode(array("status" => "error"));
        } else {
            echo json_encode(array("status" => "sucss"));
        }
        exit;
    }

    /**
     *This function is used to get keywords of Automated Campaign.
     */
    public function getPreviousKeywordsAction()
    {
        $keywords = PagesToCampaign::find("url_ref = '" . $_POST['page_url'] . "'");
        $result = "";
        $i = 0;
        foreach ($keywords as $value) {
            $i++;
            $result .= ' <li><a href="javascript:setKeywords(' . $i . ')">' . $value->keywords . '</a>
                <input type="hidden" value="' . $value->achor_text . '" id="achor_text_' . $i . '"/>
                <input type="hidden" value="' . $value->html_embed . '" id="html_text_' . $i . '"/>
            </li>';
        }
        echo json_encode(array("record" => $result));
        exit;
    }

    /**
     *This function is used to generate search results with its values like Pageranks, back links, social share count etc..
     */
    public function makeOutput($domain_ids, $categories, $pages_per_domain)
    {
        $domain_ids_temp = $domain_ids;
        $domain_ids = implode(",", $domain_ids);
        //$query = array('conditions' => array('DomainURLIDX' => array( '$nin' => $domain_ids )),"sort" => array('Points'=>-1),'limit'=> $this->records_per_search);
        $query = "DomainURLIDX NOT IN($domain_ids) ORDER BY Points ASC LIMIT $this->records_per_search";
        if (!empty($categories)) {
            //$query = array('conditions' => array('DomainURLIDX' => array( '$nin' => $domain_ids ),'$or'=>array(array('SiteCate1' =>$categories[0]),array('SiteCate2' => $categories[1]),array('SiteCate3' =>$categories[2]))),"sort" => array('Points'=>-1),'limit'=>$this->records_per_search);
            $query = "DomainURLIDX NOT IN($domain_ids) AND SiteCate1 = '$categories[0]' OR SiteCate2 = '$categories[1]' OR SiteCate3 = '$categories[2]' ORDER BY Points ASC LIMIT $this->records_per_search";
        }

        $cursor = PageMainInfo::find($query);
        foreach ($cursor as $page_url) {
            $DomainURLIDX = $page_url->DomainURLIDX;
            if (array_key_exists($DomainURLIDX, $this->temp_domain_ids)) {
                $this->temp_domain_ids[$DomainURLIDX] = $this->temp_domain_ids[$DomainURLIDX] + 1;
            } else {
                $this->temp_domain_ids[$DomainURLIDX] = 1;
            }
            if ($this->temp_domain_ids[$DomainURLIDX] > $pages_per_domain) {
                $this->greater_ids[] = $DomainURLIDX;
                continue;
            }
            $percentage = 0;
            if ($page_url->Points > 0) {
                $percentage = ($page_url->Points * 100) / 100;
                if ($percentage <= 0) {
                    $percentage = 0;
                }
            }
            //Domain age
            $timestamp_start = strtotime(date('m/d/Y h:i:s'));
            //$temp_date = date('Y-m-d H:i:s', );
            $timestamp_end = strtotime(date('m/d/Y h:i:s', strtotime($page_url->date)));

            $difference = abs($timestamp_end - $timestamp_start);
            $domain_age = floor($difference / (60 * 60 * 24 * 365));
            //Sentiments

            $this->output .= '
                    <div class="resultslist">
                        <input type="checkbox" name="page_url_checkbox[]" id="page_url_checkbox_' . $this->counter . '" class="css-checkbox page_url_checkbox" value="' . $page_url->PageURL . '$%' . $page_url->DomainURLIDX . '$%' . $this->counter . '"/>
                        <label for="page_url_checkbox_' . $this->counter . '" class="css-label"></label>

                        <div class="sitelisttitle">' . substr($page_url->PageURL, 0, 50) . '</div>
                        <a href="#site' . $this->counter . '" class="droparrow" onClick=javascript:getSentiment(' . $this->counter . ')></a>
                        <div class="matchrating">
                            <div class="matchrating-progress" style="width:' . $percentage . '%"></div>
                        </div>
                        <a href="#" onClick=javascript:getPreviousKeywords(' . $this->counter . ') class="setuplinksbutton" data-reveal-id="setuplinksModal' . $this->counter . '" data-animation="none"></a>
                    </div>
                    <div id="setuplinksModal' . $this->counter . '" class="reveal-modal">
                        <div class="modal-blueheadline"></div>
                        <div class="modal-midline">
                                    <div class="modal-midlinetext">Wählen Sie alle zutreffenden:</div>
                        </div>

                        <div class="modal-setuplinksarea">
                            <div class="modallistwrap">
                            	<div class="modal-setupbox" style="margin-bottom:10px;">
                					<div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">Stichworte For Analytik…</div>
                    				<input type="textbox" id="textbox_keywords_analytics' . $this->counter . '" class="modal-setupboxone" name="textbox_keywords_analytics' . $this->counter . '">
            					</div>
                                <div class="modal-setupbox" style="margin-bottom:35px;margin-top:15px;">
                                    <div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">anchor text…
                                    </div>
                                    <input type="text box" id="textbox_anchor_text' . $this->counter . '" class="modal-setupboxone" name="textbox_anchor_text' . $this->counter . '">

                                </div>';
            $this->output .= ' <div class="anchorsuggestion">
                <div class="anchorsuggestion-title">Letzte Keywörter</div>
                <ul id="keyword_lists_' . $this->counter . '">';
            $this->output .= '</ul>
            </div>

            <div class="modal-setupbox" style="margin-bottom:35px;">
                <div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">image url…</div>
                <input type="text box" id="textbox_image_url' . $this->counter . '" class="modal-setupboxone" name="textbox_image_url' . $this->counter . '">
            </div>

            <div class="modal-setupbox" style="margin-bottom:35px;">
                <div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">myvideo video ID</div>
                    <input type="textbox" id="textbox_video_url' . $this->counter . '" class="modal-setupboxone" name="textbox_video_url' . $this->counter . '">
            </div>
                <div class="modallisttext">HTML embed</div>							
                <div class="modal-setupbox" style="height:150px;margin-bottom:0px;">
                <textarea  id="textbox_html' . $this->counter . ' area1" name="textbox_html' . $this->counter . '" class="modal-setupboxtwo ckeditor"></textarea>
            </div>
            </div>
            </div>


            <div class="modal-bottomline">
                <div class="close-reveal-modal modal-savecontent"><div class="modalcheckbox"></div></div>
            </div>
            </div>
                <div id="site' . $this->counter . '" class="dropdownseo" style="display:none;">
                    <div class="dropdownbluebg">
                        <div class="fulldomainurl">full domain url:</div>
                        <div class="sitesactualurl" id="actual_url_' . $this->counter . '">' . $page_url->PageURL . '</div>
                    </div>
                                        <div class="dropdownpr">
                        <div class="dropdownsub-titles">PAGERANK</div>
                        <div class="dropdownsub-results">' . ($page_url->GooglePageRank) . '</div>
                    </div>
                    <div class="dropdowncat">
                        <div class="dropdownsub-titles">CATEGORY</div>
                        <div class="dropdownsub-results">' . $page_url->SiteCate1 . '</div>
                    </div>
                    <div class="dropdownil">
                        <div class="dropdownsub-titles">TOTAL BACKLINKS</div>
                        <div class="dropdownsub-results">' . $page_url->TotalBacklinks . '</div>
                    </div>
                    <div class="dropdowwt" style="width:448px;margin-left:34px;">
                        <div class="dropdownsub-titles">SENTIMENT VALUE</div>
                        <div class="dropdownsub-results" id="sentimental_value_' . $this->counter . '"></div>

                    </div>
                    <div class="dropdowol">
                        <div class="dropdownsub-titles">OUTGOING LINKS</div>
                        <div class="dropdownsub-results">' . $page_url->TotalOutgoingLinksCnt . '</div>
                    </div>
                    <div class="dropdowss" style="margin-left:34px;">
                        <div class="dropdownsub-titles">SOCIAL SHARES</div>
                        <div class="dropdownsub-results">' . ($page_url->TwitterShareCnt + $page_url->FacebookShareCnt) . '</div>
                    </div>';
            $this->output .= '</div>';
            $this->counter++;
        }
        if (!empty($this->greater_ids)) {
            $this->greater_ids = array_unique($this->greater_ids);
            $domain_ids = array_merge($domain_ids_temp, $this->greater_ids);
            $domain_ids_temp = $domain_ids;
            $domain_ids = implode(",", $domain_ids);
            $query = "DomainURLIDX NOT IN($domain_ids) ORDER BY Points ASC LIMIT $this->records_per_search";
            if (!empty($categories)) {
                //$query = array('conditions' => array('DomainURLIDX' => array( '$nin' => $domain_ids ),'$or'=>array(array('SiteCate1' =>$categories[0]),array('SiteCate2' => $categories[1]),array('SiteCate3' =>$categories[2]))));
                $query = "DomainURLIDX NOT IN($domain_ids) AND SiteCate1 = '$categories[0]' OR SiteCate2 = '$categories[1]' OR SiteCate3 = '$categories[2]' ORDER BY Points ASC LIMIT $this->records_per_search";
            }
            $cursor_count = PageMainInfo::count($query);
            if ($cursor_count > 1 && $this->counter < $this->records_per_search) {
                $this->makeOutput($domain_ids_temp, $categories, $pages_per_domain);
            } else {
                return TRUE;
            }
        }
    }
}
