<?php
// controls for crons
use \Phalcon\Mvc\View;

/**
 *This class is used to set the Cron jobs.
 */
class CronController extends BaseController
{
    public $no_of_csv = 5; // Define number of csv processing at a time

    public function initialize()
    {

    }

    /**
     *This function is used to process the crons and to check curl_embedded domains.
     */
    public function indexAction()
    {
        $this->view->disable();
        $link_limit_query = array('conditions' => array('cron_processed' => array('$ne' => (int)1)));
        $list_table = 0;
        $list_table = Lists::count($link_limit_query);
        if ($list_table) {
            $link_limit_query = array('conditions' => array('cron_processed' => array('$ne' => (int)1)), 'limit' => $this->no_of_csv);
            $list_array = Lists::find($link_limit_query);
            $count = 0;
            foreach ($list_array as $key => $list) {
                $array_connected = array();
                foreach ($list->ListURL as $url) {
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                    $output = curl_exec($curl);
                    curl_close($curl);

                    $DOM = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $DOM->loadHTML($output);

                    //get all H1
                    $items = $DOM->getElementsByTagName('linkshareconnect');
                    if (@$items->length) {
                        $array_connected[] = $count;
                        echo $url . " = yes<br/>;";
                    } else {
                        echo $url . " =No<br/>";
                    }
                    $count++;
                }

                $arr = array('conditions' => array('_id' => new MongoId($list->_id)), 'limit' => 1);
                $update_link = Lists::findFirst($arr);
                $update_link->pages_connected = $array_connected;
                $update_link->cron_processed = 1;
                $update_link->cron_processed_date = new MongoDate();
                $update_link->save();

            }
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            Lists::updateData();
            echo "empty";
        }

    }
}