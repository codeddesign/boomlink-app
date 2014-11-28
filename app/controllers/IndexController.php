<?php
use \Phalcon\Mvc\View;

/**
 * IndexController
 *
 * The class is used for manage home page
 *
 *
 * @author     codeddesign
 * @link       http://codeddesign.org/
 * @package    linkShare
 */
class IndexController extends BaseController
{

    /**
     * initialize()
     *
     * @desc initialize function for IndexController
     *
     * This function will work first and initialize all the values
     *
     * @author codeddesign
     * @access public
     *
     */
    public function initialize()
    {
        $this->view->setVar("page", "dashboard"); // Used for page identification (For template Engine)
        $this->view->setVar("active_link", $this->getCampaignLinks()); // Get Total Active Liks
        $this->view->setVar("crawled", $this->getPagesCrawled());// Get Total Pages Crawled
        $this->view->setVar("main_graph", $this->getHomeGraphValues()); // collecting main graph values
        $this->view->setVar("global_rank_changes", $this->getGlobalRankings());// collecting global rank graph values
        $this->view->setVar("global_rank", $this->getGlobalRankChange());
    }

    /**
     * indexAction()
     *
     * @desc indexAction function for IndexController
     *
     * This function is used for display the views.
     *
     * @author codeddesign
     * @access public
     *
     */
    public function indexAction()
    {
        if(!$this->session->has("user-id")) {
            $this->response->redirect($this->app_link.'/login');
        }

        $domains_crawled = DomainsToCrawl::count(); // Query for fetching whole values from the collection ""DomainsToCrawl"
        //$domains_crawled = count($domains_to_crawl);// Count the mongo collection "DomainsToCrawl"
        $this->view->setVar('domains_crawled', $domains_crawled); // Get total domains crawled
        $this->view->setTemplateAfter("dashboard"); // call the view dashboard after the main layout initialized
    }


    /**
     * getCampaignLinks()
     *
     * @desc getCampaignLinks function for IndexController
     *
     * This function is used for find the total number of capaign links present
     *
     * @author codeddesign
     * @access public
     *
     */
    public function getCampaignLinks()
    {
        // Fetch the data grater than current date.
        $date = date("Y-m-d");
        $records = PagesToCampaign::count("EndDate > '$date'");
        return $records;
    }

    /**
     * getPagesCrawled()
     *
     * @desc getPagesCrawled function for IndexController
     *
     * This function is used for find the data of the graph "pages crawled" for a week
     * @return
     * @author codeddesign
     * @access public
     *
     */
    public function getPagesCrawled()
    {
        $end_date = date('Y-m-d'); // Current Date
        $start = date('Y-m-d', strtotime('-7 day', strtotime($end_date))); // subtract -7 days from current date
        //$rangeQuery = array('conditions' => array('date' => array('$gte' => new MongoDate(strtotime($start)), '$lt' => new MongoDate(strtotime($end_date1))))); 
        $records = StatusDomain::find("date >= '$start' AND date <= '$end_date'"); // Fetching the values from the collection "status_domain"
        $return_array = array();

        for ($i = 0; $i < 7; $i++) {
            $return_array[date('m/d', strtotime("-$i day", strtotime($end_date)))] = 0;
            // initializing array with last 7 days [month/date] and initialize with '0' 
        }
        foreach ($records as $value) {
            // see the date manual page for format options
            $date = date("m/d", strtotime($value->date));
            $return_array[$date] = $return_array[$date] + 1;
            // increment the value of the array when its match

        }
        return $return_array;
    }

    /**
     * getHomeGraphValues()
     *
     * @desc getHomeGraphValues function for IndexController
     *
     * This function is used for find the data of the main graph for a day
     * @return Array containing time and its tracked details
     *
     * @author codeddesign
     * @access public
     *
     */
    function getHomeGraphValues()
    {

        $time = strtotime('00:00'); // start time 
        $twenty_minutes_count = 10; // adding by 10min  
        $time_array = array();
        $time_array["00-00"] = 0; // initialize first time 00-00 as '0';

        // The loop used for setting the time by 10
        for ($i = 1; $i < 72; $i++) {
            $endTime = date("H-i", strtotime("+$twenty_minutes_count minutes", $time)); // $endTim= "Hour-Minutes"
            $time_array[$endTime] = 0; // setting array index with $endTim and initialize with '0'
            $twenty_minutes_count = $twenty_minutes_count + 10; // increment current time with 10 minutes
        }

        $time_array["24-00"] = 0; //initialize last hour with '0' 
        $current_date = date("Y-m-d"); // Get current date
        $records = TrackVisitors::find("Date = '$current_date'"); // From the collection "track_visitors"

        foreach ($records as $key => $value) {

            //$time_string = date('H-i', $value->DateTime); // convert time to the format "Hour-Minutes" from the database

            $time_string = date("H-i", strtotime($value->DateTime));
            $split_time = @split("-", $time_string); // split the time to hour /minutes and stored;.$split_time[1] gives minutes 

            if ($split_time[1] < 10 && $split_time[1] >= 0) // check whether the minutes between 10 and '0'
            {
                $array_index = $split_time[0] . "-00";  // append -00 minutes to current hour.
            } else if ($split_time[1] < 60 && $split_time[1] >= 10)// check whether the minutes between 60 and 10
            {
                $array_index = $split_time[0] . "-10"; // append -10 minutes to current hour.
            } else if ($split_time[1] > 60)  // check whether the minutesgreater than 60
            {
                $array_index = ($split_time[0] == 24) ? $split_time[0] : $split_time[0] + 1; // if hour equals to 24 then assign array index by current.Otherwise increment by 1
                if ($array_index < 10) {
                    $array_index = "0" . $array_index; // append 0 by current index.Used for follow the array index styling. That is hour '5' will be converted to 05
                }
                $array_index = $array_index . "-00"; //append hour by zero minutes
            }
            $time_array[$array_index] = $time_array[$array_index] + 1; // Increment array value if any matching index is occur. Matching values came from database
        }
        return $time_array;
    }

    /**
     * generate_traffic_reportAction()
     *
     * @desc Generate Traffic reports option for IndexController
     *
     * This function is used for making the traffic report CSVs
     *
     * @author codeddesign
     * @access public
     *
     */
    public function generate_traffic_reportAction()
    {
        $request = $this->request; // get the request type  
        if ($request->isPost()) // Make sure that the request is POST       
        {
            $main_graph = $this->getHomeGraphValues(); // Get the Tracked data          
            $send_dom = array(); //initialize array 

            // setting csv headings start
            $send_dom[0][0] = 'ANALYTIC TRAFFIC';
            $send_dom[1][0] = 'Currently Tracking ' . @array_sum(@$main_graph) . ' Users';
            $send_dom[2][0] = 'Time';
            $send_dom[2][1] = 'No. Of Users';
            // setting csv headings End
            $j = 3;
            foreach ($main_graph as $key => $value) {
                $send_dom[$j][0] = @str_replace("-", ":", @$key); // replacing - by : .ie Hour:minutes
                $send_dom[$j][1] = @$value; // assing csv values
                $j++;
            }
            $this->array_to_csv_download($send_dom, 'Traffic Report.csv'); // export csv using the function array_to_csv_download();
        }
        $this->view->disable(); // prevent the view reload
    }

    /**
     * array_to_csv_download()
     *
     * @desc Generate csv files using array of values
     *
     * This function is used for making  CSVs
     * @param $array : Array of values, $filename:optional csv file name, $delimiter: optional delimeter used for split strings
     * @author codeddesign
     * @access public
     *
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
     * getGlobalRankings()
     *
     * @desc Generate Global rank values
     *
     * This function is used for arranging values for global rank
     *
     * @author codeddesign
     * @access public
     * @return array of values
     */
    public function getGlobalRankings()
    {
        $return_array = array();
        $end_date = date("Y-m-d");
        $domainss = array();
        //$end_date = date('m/d/Y h:i:s A');
        //$end = new MongoDate(strtotime($end_date));
        //$rangeQuery = array('conditions' => array('EndDate' => array('$gte' => $end)));
        $get_domains = PagesToCampaign::find("EndDate >= '$end_date'");
        foreach ($get_domains as $domain) {
            if ($domain->DomainURLIDX) {
                $domainss[] = $domain->DomainURLIDX;
            }
        }

        for ($i = 0; $i < 7; $i++) {
            $return_array[date('m/d', strtotime("-$i day", strtotime($end_date)))] = 0;
        }
        $domains = array_unique($domainss);

        $j = 0;
        for ($i = 1; $i <= 7; $i++) {
            $array_index = "";
            $yesterday = date('m/d/Y', strtotime("-$i day"));
            //$yest = new MongoDate(strtotime($yesterday));
            if ($i > 1) {
                $yest_end = date('m/d/Y', strtotime("-$j day"));
                $array_index = $yest_end = date('m/d', strtotime("-$j day"));
                //$yest_end = new MongoDate(strtotime($yest_end));
            } else {
                $array_index = date('m/d');
                $yest_end = date('m/d/Y');
            }


            foreach ($domains as $value) {
                //Latest
                //$query = array('conditions' => array('DomainURLIDX' => (int)$value),"sort" => array('Date'=>-1),'limit'=>2);
                $get_domain_ranks = DomainRankings::find("DomainURLIDX = '$value' ORDER BY Date DESC LIMIT 2");
                $diff = 0;
                $latest_rank = 0;
                if (count($get_domain_ranks) > 0) {
                    $domain_rank = array();
                    foreach ($get_domain_ranks as $domain_r) {
                        $domain_rank[] = $domain_r->Rank;
                    }
                    if (@is_numeric($domain_rank[1]) && @is_numeric($domain_rank[0]))
                        $diff = ($domain_rank[0] - $domain_rank[1]);
                    else
                        $diff = $domain_rank[0];
                    if (is_numeric($domain_rank[0]))
                        $latest_rank = $domain_rank[0];
                }

                //$query = array('conditions' => array('DomainURLIDX' => (int)$value,'Date' => array('$gte' => $yest,'$lt' => $yest_end)),'sort'=>array('Date'=>-1),'limit'=>1);
                $get_domain_ranks_24 = DomainRankings::find("DomainURLIDX = '$value' AND Date >='$yesterday' AND Date <='$yesterday'");
                $diff_24 = 0;
                if (count($get_domain_ranks_24) > 0) {
                    $domain_rank = array();
                    foreach ($get_domain_ranks_24 as $domain_r) {
                        $domain_rank[] = $domain_r->Rank;
                    }
                    if (is_numeric($domain_rank[0]) && is_numeric($latest_rank))
                        $diff_24 = ($latest_rank - $domain_rank[0]);
                    else
                        $diff_24 = $domain_rank[0];
                }
                $return_array[$array_index] = $return_array[$array_index] + $diff_24;
            }


            $j++;
        }
        return $return_array;
    }


    /**
     * getGlobalRankChange()
     *
     * @desc Generate Global rank values
     *
     * This function is used for get gloabal rank change
     *
     * @author codeddesign
     * @access public
     * @return array of values
     */
    public function getGlobalRankChange()
    {
        $end_date = date('m/d/Y h:i:s');
        //$end = new MongoDate(strtotime($end_date));
        //$rangeQuery = array('conditions' => array('EndDate' => array('$gte' => $end)));
        $get_domains = PagesToCampaign::find("EndDate >= '$end_date'");
        $domainss = array();
        foreach ($get_domains as $domain) {
            if ($domain->DomainURLIDX) {
                $domainss[] = $domain->DomainURLIDX;
            }
        }
        $domains = array_unique($domainss);
        $global_change = 0;
        $yesterday = date('m/d/Y', strtotime("-1 days"));
        //$yest = new MongoDate(strtotime($yesterday));
        //$yest_end = new MongoDate(strtotime(date('m/d/Y')));

        foreach ($domains as $value) {
            //Latest
            //$query = array('conditions' => array('DomainURLIDX' => (int)$value),"sort" => array('Date'=>-1),'limit'=>2);
            $get_domain_ranks = DomainRankings::find("DomainURLIDX ='$value' ORDER BY Date DESC LIMIT 2");
            $diff = 0;
            $latest_rank = 0;
            if (count($get_domain_ranks) > 0) {
                $domain_rank = array();
                foreach ($get_domain_ranks as $domain_r) {
                    $domain_rank[] = $domain_r->Rank;
                }
                if (@is_numeric($domain_rank[1]) && @is_numeric($domain_rank[0]))
                    $diff = ($domain_rank[0] - $domain_rank[1]);
                else
                    $diff = $domain_rank[0];
                if (is_numeric($domain_rank[0]))
                    $latest_rank = $domain_rank[0];
            }
            //24 Hours
            $yesterday = date('m/d/Y', strtotime("-1 days"));
            //$yest = new MongoDate(strtotime($yesterday));
            $yest_end = date('m/d/Y');
            //$query = array('conditions' => array('DomainURLIDX' => (int)$value,'Date' => array('$gte' => $yest,'$lt' => $yest_end)),'sort'=>array('Date'=>-1),'limit'=>1);
            $get_domain_ranks_24 = DomainRankings::find("DomainURLIDX = '$value' AND Date >='$yesterday' AND Date <='$yest_end' ORDER BY Date DESC LIMIT 1");
            $diff_24 = 0;
            if (count($get_domain_ranks_24) > 0) {
                $domain_rank = array();
                foreach ($get_domain_ranks_24 as $domain_r) {
                    $domain_rank[] = $domain_r->Rank;
                }
                if (is_numeric($domain_rank[0]) && is_numeric($latest_rank))
                    $diff_24 = ($latest_rank - $domain_rank[0]);
                else
                    $diff_24 = $domain_rank[0];
            }
            $global_change = $global_change + $diff_24;
        }
        return $global_change;
    }


    /**
     * getRankings()
     *
     * @desc Find the ranking of urls
     *
     * This function is used for fetching the ranking of every urls
     *
     * @author codeddesign
     * @access public
     * @return array of values
     */
    function getRankings()
    {
        $records = DomainRankings::find();
        return $records;
    }

    /**
     * generate_rank_reportAction()
     *
     * @desc Generate rank reports option for IndexController
     *
     * This function is used for making the rank report CSVs
     *
     * @author codeddesign
     * @access public
     *
     */
    public function generate_rank_reportAction()
    {
        $request = $this->request;// get the request type          
        if ($request->isPost()) // Make sure that the request is POST       
        {
            $main_graph = $this->getRankings(); // Get rankings from the function getRankings()
            $send_dom = array();
            //Start csv Headings
            $send_dom[0][0] = 'Site Urls';
            $send_dom[0][1] = 'Date Time';
            $send_dom[0][2] = 'Rank';
            //End csv Headings
            $j = 3;
            foreach ($main_graph as $key => $value) {
                $send_dom[$j][0] = @$value->DomainURL; // assigning domain base url
                $send_dom[$j][1] = date('Y-m-d', $value->Date->sec); // date
                $send_dom[$j][2] = @$value->Rank; // Rank   
                $j++;
            }
            $this->array_to_csv_download($send_dom, 'Rank Report.csv'); // Generate CSV
        }
        $this->view->disable();// prevent the view reload
    }

}