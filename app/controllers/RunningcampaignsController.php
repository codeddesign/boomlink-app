<?php
/**
 *This class is used to get list all running manual and automated campaigns.
 */
use \Phalcon\Mvc\View;

class RunningcampaignsController extends BaseController
{
    /**
     *This function is used to initialize the page for running campigns.
     */
    public function initialize()
    {
        $this->view->setVar("page", "runningcampaigns");
    }

    /**
     *This function is used to get the running campaigns from database.
     */
    public function indexAction()
    {
        $end = date("Y-m-d"); // fetch live campaigns
        //$rangeQuery = array('conditions' => array('EndDate' => array('$gte' => $end)));
        $get_campaigns = MasterCampaign::find("EndDate >= $end");
        $this->view->setVar('get_campaigns', $get_campaigns);
    }

    /**
     *This function is used to delete the seleced campaign.
     */
    public function delete_campaignAction($postid, $campaign_type)
    {
        $id = $postid;
        //$arr = array('conditions' => array('_id' => new MongoId($id)),'limit'=>1);
        $campaign = MasterCampaign::findFirst("id = '$id'");
        //$arr = array('conditions' => array('unique_id' => $campaign->unique_id));
        $links = PagesToCampaign::find("unique_id = '$campaign->unique_id'");
        $campaign->delete();
        if (!empty($links)) {
            foreach ($links as $d) {

                if ($d->delete() == false) {
                    echo "Sorry, we can't delete the Domain right now: \n";
                    foreach ($d->getMessages() as $message) {
                        echo $message, "\n";
                    }
                }
            }
        }
        echo json_encode(array("deleted" => "sucss", "msg" => $msg));
        exit;
    }

    /**
     *This function is used to export the running campigns into CSV file.
     */
    public function export_csvAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            //$end_date = date('m/d/Y h:i:s A',strtotime(date("Y-m-d")));
            $end = date("Y-m-d");
            // $rangeQuery = array('conditions' => array('EndDate' => array('$gte' => $end)));
            $get_campaigns = MasterCampaign::find("EndDate >= '$end'");

            //$end_date = date('m/d/Y h:i:s A',strtotime(date("Y-m-d")));
            //$end = new MongoDate(strtotime($end_date));
            //$rangeQuery = array('conditions' => array('EndDate' => array('$gte' => $end)));
            // $get_manual_campaigns = ManualCampaign::find($rangeQuery);

            $send_dom = array();
            $send_dom[0][0] = 'RUNNING CAMPAIGNS';
            $send_dom[1][0] = 'Campaign Name';
            $send_dom[1][1] = 'End Date';
            $send_dom[1][2] = 'Campaign Type';
            //$send_dom[1][3] ='Category';
            $send_dom[1][4] = 'Campaign For';
            $send_dom[1][5] = 'Keywords';
            $j = 2;
            foreach ($get_campaigns as $camp) {
                $send_dom[$j][0] = $camp->Name;
                $send_dom[$j][1] = @date('m/d/Y', @$camp->EndDate->sec);
                $send_dom[$j][2] = @$camp->CampaignType;
                //$send_dom[$j][3] = @$camp->Category;

                $links = PagesToCampaign::find(array(array('unique_id' => $camp->unique_id)));
                $campaign_url = "";
                foreach ($links as $link) {
                    $campaign_url = $link->campaign_url;
                }

                $send_dom[$j][4] = @$campaign_url;
                $send_dom[$j][5] = (@$camp->keywords != '') ? @$camp->keywords : 'NA';
                $j++;
                $send_dom[$j][0] = 'Page URL';
                $send_dom[$j][1] = 'Keywords';
                $j++;
                foreach ($links as $link) {
                    $send_dom[$j][0] = @$link->main_url;
                    $send_dom[$j][1] = (@$link->achor_text != '') ? @$link->achor_text : 'NA';
                    $j++;
                }
                $j++;
            }
            /*foreach($get_manual_campaigns as $camp)
            {
                $send_dom[$j][0] = $camp->Name;
                $send_dom[$j][1] = @date('m/d/Y', @$camp->EndDate->sec);
                $send_dom[$j][2] = @$camp->CampaignType;
                //$send_dom[$j][3] = @$camp->Category;

                $send_dom[$j][4] = @$camp->PageURL;
                $send_dom[$j][5] = (@$camp->anchor_text!='') ? @$camp->anchor_text : 'NA';
                $j++;
            }*/
            $this->array_to_csv_download($send_dom, 'export_running_campaigns.csv');
        }
        $this->view->disable();
    }

    /**
     *This function is used to update the anchor text, image, video or embedded HTML of running campaigns.
     */
    function update_rcampaign_pagesAction()
    {
        $this->view->disable();
        $request = $this->request;
        if ($request->isPost()) {
            $id = $request->getPost('id');
            //$arr = array('conditions' => array('_id' => new MongoId($id)),'limit'=>1);
            $page_to_campaign = PagesToCampaign::findFirst("id = '$id'");
            $page_to_campaign->achor_text = $request->getPost('achor_text');
            $page_to_campaign->image_url = $request->getPost('image_url');
            $page_to_campaign->video_url = $request->getPost('video_url');
            $page_to_campaign->html_embed = $request->getPost('html_embed');
            if ($page_to_campaign->save() == false) {
                echo "Sorry, we can't edit the campaign right now";
            } else {
                echo "Campaign is updated successfully.";
            }
        }
    }

    function update_rcampaign_manual_pagesAction()
    {
        $this->view->disable();
        $request = $this->request;
        if ($request->isPost()) {
            $id = $request->getPost('id');
            //$arr = array('conditions' => array('_id' => new MongoId($id)),'limit'=>1);
            $page_to_campaign = ManualCampaign::findFirst("id = '$id'");
            $page_to_campaign->anchor_text = $request->getPost('achor_text');
            $page_to_campaign->image_url = $request->getPost('image_url');
            $page_to_campaign->video_url = $request->getPost('video_url');
            $page_to_campaign->backlink_url = $request->getPost('backlink_url');
            if ($page_to_campaign->save() == false) {
                echo "Sorry, we can't edit the campaign right now";
            } else {
                echo "Campaign is updated successfully.";
            }
        }
    }

    /**
     *This function is used to wrte content into CSV file.
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

}