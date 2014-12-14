<?php
use \Phalcon\Mvc\View;

/**
 *This class is used to generate the Manual Campaigns.
 */
class ManualCampaignController extends BaseController
{
    /**
     *This function is used to initialize the view .
     */
    public function initialize()
    {
        $this->view->setVar("page", "manual_campaign");
    }

    /**
     *This function is used to set values of dropdowns for Base Domain and Manual List.
     */
    public function indexAction()
    {

        $get_domains = StatusDomain::find();
        $get_lists = Lists::find("ListType='MANUAL'");

        $this->view->setVar('get_domains', $get_domains);
        $this->view->setVar('get_lists', $get_lists);
    }

    public function showAction($postId)
    {

    }

    /**
     *This function is used to generate search results of the search form.
     */
    public function generateAction()
    {
        $request = $this->request;
        if ($request->isPost()) {

            $campaign_name = $request->getPost('campaign_name');

            $domain_ids = $_POST['domain_ids'];
            $domain_ids = json_decode($domain_ids);
            $domain_ids = array_map('intval', $domain_ids);

            $page_urls = $_POST['page_urls'];
            $page_urls = json_decode($page_urls);

            $lists = $_POST['lists'];
            $lists = json_decode($lists);
            $lists = implode("','", $lists);

            //$rangeQuery = array('conditions' => array('Name' => array( '$in' => $lists )));

            $get_lists = Lists::find("Name IN('$lists')");
            $pages = array();
            $connected_page = array();
            foreach ($get_lists as $list) {
                $temp_pages = explode(",", $list->ListURL);
                $temp_pages_connected = explode(",", $list->pages_connected);
                foreach ($temp_pages as $key => $url) {
                    $pages[$key] = $url;
                }
                foreach (@$temp_pages_connected as $key => $page_connected) {
                    $connected_page[] = $page_connected;
                }
            }
            $output = '';

            if (!empty($pages)) {
                $output .= '
				
			<div class="listtitlebar">
				<div class="pagetitlebar-title">HOCHGELADEN\'S WEBSITE</div>
			</div>
			<div class="listtitlesubbar">
				<div class="webaddressbar">Website-Adresse</div>
				<div class="listsetuplinks">Setup-Links</div>
				<div class="connectionlevel">Verbindung aufgebaut</div>
			</div>
            <form name="build-manual-campaign" action="'.$this->app_link.'/manual_campaign/build" method="post" onsubmit="return check_domain_selected();">
			<input type="hidden" name="campaign_name" value="' . $campaign_name . '" />
                        <input type="hidden" name="keywords" value="' . $request->getPost('keywords') . '" />
			<input type="hidden" name="start_date" value="' . $request->getPost('start_date') . '" />
			<input type="hidden" name="end_date" value="' . $request->getPost('end_date') . '" />';
                foreach ($page_urls as $p) {
                    $output .= '<input type="hidden" name="page_urls[]" value="' . $p . '"/>';
                }

                $i = 0;
                foreach ($pages as $key => $page_url) {
                    $class = "connectfailed";
                    if (in_array($page_url, $connected_page)) {
                        $class = "connectsuccess";
                    }
                    $output .= '<div class="resultslist">
                    <input type="checkbox" name="page_url_checkbox[]" id="page_url_checkbox_' . $i . '" class="css-checkbox page_url_checkbox" value="' . $page_url . '$%' . @$page_url->DomainURLIDX . '$%' . $i . '"/>
                    <label for="page_url_checkbox_' . $i . '" class="css-label"></label>
                    <input type="hidden" name="page_url_' . $i . '" id="page_url_' . $i . '" value="' . $page_url . '" />
					<input type="hidden" name="manual_camp_domain[]" value="" />
                    <div class="sitelisttitle">' . substr($page_url, 0, 50) . '</div>
                    <a href="#site' . $i . '" class="droparrow"></a>
                    <div class="' . $class . '"></div>
                    <a href="#" class="setuplinksbutton" data-reveal-id="setuplinksModal' . $i . '" data-animation="none"></a>
                    </div>   


               <div id="setuplinksModal' . $i . '" class="reveal-modal">
                        <div class="modal-blueheadline"></div>
                        <div class="modal-midline">
                                    <div class="modal-midlinetext">Wählen Sie alle zutreffenden:</div>
                        </div>

                        <div class="modal-setuplinksarea">
                            <div class="modallistwrap">
                            	<div class="modal-setupbox" style="margin-bottom:10px;">
                                                <div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">Stichworte For Analytik…</div>
                    				<input type="textbox" id="textbox_keywords_analytics' . $i . '" class="modal-setupboxone" name="textbox_keywords_analytics' . $i . '">
            					</div>
            <div class="modal-setupbox" style="margin-bottom:35px;margin-top:15px;">
                <div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">anchor text…
                </div>
                <input type="text box" id="textbox_anchor_text' . $i . '" class="modal-setupboxone" name="textbox_anchor_text' . $i . '">

            </div>

            <div class="modal-setupbox" style="margin-bottom:35px;">
                <div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">image url…</div>
                <input type="text box" id="textbox_image_url' . $i . '" class="modal-setupboxone" name="textbox_image_url' . $i . '">
            </div>

            <div class="modal-setupbox" style="margin-bottom:35px;">
                <div class="modallisttext" style="margin-top:-14px;line-height:0px;margin-left:0px;">myvideo video ID</div>
                    <input type="textbox" id="textbox_video_url' . $i . '" class="modal-setupboxone" name="textbox_video_url' . $i . '">
            </div>
                <div class="modallisttext">HTML embed</div>							
                <div class="modal-setupbox" style="height:150px;margin-bottom:0px;">
                <textarea  id="textbox_html' . $i . ' area1" name="textbox_html' . $i . '" class="modal-setupboxtwo ckeditor"></textarea>
            </div>
            </div>
            </div>

                                        






							<div class="modal-bottomline">
								<div class="close-reveal-modal modal-savecontent"><div class="modalcheckbox"></div></div>
						</div>
						</div>
						
                </div>
                <div id="site' . $i . '" class="dropdownuploadlist" style="display:none;">
                    <div class="dropdownbluebg">
                        <div class="fulldomainurl">vollständigen domain-url:</div>
                        <div class="sitesactualurl">' . $page_url . '</div>
                    </div>
                </div>';
                    $i++;
                }

                $output .= '<input type="hidden" name="total_page_urls" id="total_page_urls" value="' . $i . '" />
               <div class="underlistbar"></div>';
                if ($this->session->has("user-role") && $this->session->get("user-role") == 'master')
                    $output .= '<button type="" class="buildcampbutton" name="buildcampbutton" style="border:none; cursor:pointer"></button>';
                $output .= '</form>';
            } else {
                $output .= '<div align="center" class="pagetitlebar-title" style="color:red; width:100%">
				Kein Ergebnis gefunden mit den eingegebenen Kriterien. Bitte suchen Sie erneut.
			</div>';
            }
            $this->view->disable();
            echo $output;
        }
    }

    /**
     *This function is used to get Pages for selected Base domain.
     */
    public function get_domain_pagesAction()
    {
        $request = $this->request;
        if ($request->isPost()) {

            $domainss = $_POST['domains'];
            $domains = json_decode($domainss);
            $domains = array_map('intval', $domains);
            $domains = implode(",", $domains);

            $arr = array();
            $options = array();
            //$rangeQuery = array('conditions' => array('DomainURLIDX' => array( '$in' => $domains )));
            $total = PageMainInfo::count("DomainURLIDX IN ($domains)");

            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = 10;
            $skip = ($page - 1) * $limit;
            $next = ($page + 1);

            if ($page > 0) {
                if ($page * $limit < $total) {
                    $options = array(
                        "current_page" => $next,
                        'button_enable' => 1
                    );
                } else {
                    $options = array(
                        "current_page" => $next,
                        'button_enable' => 0
                    );
                }
            }

            //$rangeQuery = array('conditions' => array('DomainURLIDX' => array( '$in' => $domains )),'skip'=>$skip, 'limit'=>$limit);
            $cursor = PageMainInfo::find("DomainURLIDX IN($domains) LIMIT $skip, $limit");

            $i = 0;
            foreach ($cursor as $dom) {
                $arr[$i] = $dom->PageURL . '@#' . $dom->DomainURLIDX;
                $i++;
            }
            $this->view->disable();
            echo json_encode(array("record" => $arr, "options" => $options));
        }

    }


    /**
     *This function is used to get Pages for selected Base domain.
     */
    public function getAutocompleteAction()
    {
        $return_arr = array();
        $request = $this->request;
        if ($request->isPost()) {
            $domain_ids = json_decode($_POST['domains'],1);
            $page_url = trim($_POST['cat_text']);

            $cursor = PageMainInfo::find("DomainURLIDX IN(".implode(',', $domain_ids).") AND PageURL LIKE '%$page_url%' LIMIT 5 ");
            foreach ($cursor as $dom) {
                $return_arr[] = array(
                    'label' => $dom->PageURL,
                    'id' => $dom->PageURL . '@#' . $dom->DomainURLIDX
                );
            }

            $this->jsonResponse($return_arr);
        }

    }

    /**
     *This function is used to store Manual Campaign into database.
     */
    public function buildAction()
    {
        $get_domains = StatusDomain::find();
        $get_lists = Lists::find("ListType='MANUAL'");
        $this->view->setVar('get_domains', $get_domains);
        $this->view->setVar('get_lists', $get_lists);

        $request = $this->request;
        if ($request->isPost()) {

            $unique_id = md5(date('Y-m-d H:i:s'));
            $master_campaign = new MasterCampaign();
            $master_campaign->Name = $_POST['campaign_name'];
            $master_campaign->StartDate = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
            $master_campaign->EndDate = date("Y-m-d H:i:s", strtotime($_POST['end_date']));
            $master_campaign->CampaignType = 'MANUAL';
            $master_campaign->unique_id = $unique_id;
            if ($master_campaign->save() == false) {
                foreach ($master_campaign->getMessages() as $message) {
                    echo $message->getMessage() . '<br>';
                }
            }

            if (isset($_POST['page_urls']) && $_POST['page_urls']) {
                foreach ($_POST['page_urls'] as $value) {
                    $campaing_url = explode("@#", $value);
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
            }

            $this->flash->success('Das Handbuch Kampagne "' . $_POST['campaign_name'] . '" Erfolgreich aufgebaut');

        }
    }

}