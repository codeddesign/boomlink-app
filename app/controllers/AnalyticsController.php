<?php
// controls for analytics page
use \Phalcon\Mvc\View;

/**
 *This class is used to provide the analytics of domain and keyword rankings.
 */
class AnalyticsController extends BaseController
{
    /**
     *This function is used to initialize view for analytics page.
     */
    public function initialize()
    {
        $this->view->setVar("page", "analytics");
    }

    /**
     *This function is used to fetch database table for domains.
     */
    public function getSource()
    {
        return "status_domain";
    }

    /**
     *This function is index function of analytics.
     */
    public function indexAction()
    {
        $this->setDropDown();
    }

    /**
     *This function is used to set the dropdowns values and making graph on basis of these values to know how much visitors were on which domain in which time period.
     */
    public function setDropDown($id = 0, $extra = "")
    {
        $is_admin = false;
        if ($this->session->get("user-role") !== "normal") {
            $is_admin = true;
        }
        $user_domains = $this->session->get("user-domains");
        if(!is_array($user_domains)) {
            $user_domains = array();
        }

        $drop1_html = "";
        $drop1_htm2 = "";
        $domains = StatusDomain::find(); // Get all domain url from the main table
        $drop1_html = '<select id="analyticschoosedomain" onchange="javascript:jumbScreen()" size="1" name="choice"><option value="">Wählen Sie Domäne:</option>';
        foreach ($domains as $url) {
            $temp_html = "";
            if ($id == $url->DomainURLIDX) {
                $temp_html = "selected = selected";
            }

            //filter domains:
            if (in_array($url->DomainURLIDX, $user_domains) or $is_admin) {
                $drop1_html .= '<option value="' . $url->DomainURLIDX . '" ' . $temp_html . '>' . $url->DomainURL . '</option>';
            }
        }
        $drop1_html .= '</select>';

        $timeArray = array("24 Stunden", "7 Tage", "28 Tage", "3 Monate", "6 Monate"); // drop down values for time periods.
        $drop1_htm2 = '<select name="choice" size="1" onchange="javascript:jumbScreen()" id="analyticschoosetime">
                        <option value="">Zeitplan:</option>';
        foreach ($timeArray as $value) {
            $temp_html = "";
            if ($extra == str_replace(" ", "-", $value)) {
                $temp_html = "selected = selected";
            }
            $drop1_htm2 .= '<option value="' . str_replace(" ", "-", $value) . '" ' . $temp_html . '>' . $value . '</option>';
        }
        $drop1_htm2 .= '</select>';

        $record_type = "";
        switch (str_replace(" ", "-", $extra)) {
            case "24-Stunden": // track visitors during last 24 hours
            {
                $record_type = "bytime";
                $time_array = array();
                for ($i = 0; $i <= 24; $i++) {
                    $time_array[$i] = 0;
                }
                $current_date = date("Y-m-d");
                $current_date = date("Y-m-d", strtotime($current_date));
                //$rangeQuery = array('conditions' => array('DomainURLIDX' =>(int)$id,'Date' => $current_date));
                $records = TrackVisitors::find("Date = '$current_date' AND DomainURLIDX = '$id'");

                foreach ($records as $value) {
                    $hour = date("H", strtotime($value->date));
                    //$hour = date('H', $value->DateTime->sec);
                    $hour = ltrim($hour, '0');
                    if (!$hour || trim($hour) == "")
                        $hour = 0;
                    $time_array[$hour] = ($time_array[$hour] + 1); // Remove this *3. This is only for testing
                }
                break;
            }
            case "7-Tage": // track visitors during last 7 days
            {
                $record_type = "7-days";
                $end_date = date("Y-m-d");
                $end_date_loop = $end_date;
                $start_date = date('Y-m-d', strtotime('-6 day', strtotime($end_date)));
                $start_date_loop = $start_date;

                //$rangeQuery = array('conditions' => array('DomainURLIDX' =>(int)$id,'DateTime' => array('$gte' => new MongoDate(strtotime($start_date)))));

                $record_array = TrackVisitors::find("DomainURLIDX = '$id' AND DateTime >= '$start_date'");
                $date_array = array();
                $date_array[$start_date_loop] = 0;
                $i = 0;
                while ($start_date_loop < $end_date_loop) {
                    $start_date_loop = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($start_date)));
                    $date_array[$start_date_loop] = "0";
                    $i++;
                }
                foreach ($record_array as $value) {
                    $date_array[$value->Date] = $date_array[$value->Date] + 1;
                }
                break;
            }

            case "28-Tage": // track visitors during last 28 days (4 weeks)
            {
                $record_type = "28-days";
                $end_date = date("Y-m-d");
                $end_date_loop = $end_date;
                $start_date = date('Y-m-d', strtotime('-28 day', strtotime($end_date)));
                $start_date_loop = $start_date;
                //$rangeQuery = array('conditions' => array('DomainURLIDX' =>(int)$id,'DateTime' => array('$gte' => new MongoDate(strtotime($start_date)))));
                $record_array = TrackVisitors::find("DomainURLIDX = '$id' AND DateTime >= '$start_date'");
                $date_array = array();
                $date_array[$start_date_loop] = 0;
                $i = 0;
                while ($start_date_loop < $end_date_loop) {
                    $start_date_loop = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($start_date)));
                    $date_array[$start_date_loop] = "0";
                    $i++;
                }
                foreach ($record_array as $value) {
                    //$date = date('Y-m-d', $value->DateTime->sec);
                    //$date_array[$date] = $date_array[$date] + 1;
                    $date_array[$value->Date] = $date_array[$value->Date] + 1;
                }
                break;
            }

            case "3-Monate": // track visitors during last 3 months
            {
                $record_type = "3-months";
                $end_date = date("Y-m-d");
                $end_date_loop = $end_date;
                $start_date = date('Y-m-d', strtotime('-3 months', strtotime($end_date)));
                $start_date_loop = $start_date;
                $date_array = array();
                $i = 0;
                while ($start_date_loop < $end_date_loop) {
                    $start_date_loop = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($start_date)));
                    $date_array[$start_date_loop] = "0";
                    $i++;
                }

                // $rangeQuery = array('conditions' => array('DomainURLIDX' =>(int)$id,'DateTime' => array('$gte' => new MongoDate(strtotime($start_date)))));
                $record_array = TrackVisitors::find("DomainURLIDX = '$id' AND DateTime >= '$start_date'");
                foreach ($record_array as $value) {
                    $date = date("Y-m-d", strtotime($value->date));
                    if (array_key_exists($date, $date_array)) {
                        $date_array[$date] = $date_array[$date] + 1;
                    } else {
                        $date_array[$date] = 1;
                    }
                }
                break;
            }

            case "6-Monate": // get visitors that visited our domain during last 6 months
            {
                $record_type = "6-months";
                $end_date = date("Y-m-d");
                $end_date_loop = $end_date;
                $start_date = date('Y-m-d', strtotime('-6 months', strtotime($end_date)));
                $start_date_loop = $start_date;
                $date_array = array();
                $i = 0;
                while ($start_date_loop < $end_date_loop) {
                    $start_date_loop = date('Y-m-d', strtotime('+' . $i . ' day', strtotime($start_date)));
                    $date_array[$start_date_loop] = "0";
                    $i++;
                }

                //$rangeQuery = array('conditions' => array('DomainURLIDX' =>(int)$id,'DateTime' => array('$gte' => new MongoDate(strtotime($start_date)))));
                $record_array = TrackVisitors::find("DomainURLIDX = '$id' AND DateTime >= '$start_date'");
                foreach ($record_array as $value) {
                    $date = date("Y-m-d", strtotime($value->date));

                    if (array_key_exists($date, $date_array)) {
                        $date_array[$date] = $date_array[$date] + 1;
                    } else {
                        $date_array[$date] = 1;
                    }
                }
                break;
            }
        }
        $this->view->setVar("domain_drop_down1", $drop1_html);
        $this->view->setVar("domains", $domains);
        $this->view->setVar("time_array", $time_array);
        //$this->view->setVar("date_array" ,  $this->ConverDateJson($date_array));
        $this->view->setVar("date_array", $date_array);
        $this->view->setVar("record_type", $record_type);
        $this->view->setVar("domain_drop_down2", $drop1_htm2);
    }

    /**
     *This function is used to show the analytic graph.
     */
    public function showAction($postId = 0, $extra = "")
    {
        $this->setDropDown($postId, $extra);
        $view = new View();
        $view->setViewsDir('app/views/');
        $view->start();
        $view->render('analytics', 'analytics');
        $view->finish();
        echo $view->getContent();
    }

    /**
     *This function is used to convert time into Mongo ID.
     */
    public function timeToId($ts)
    {
        // turn it into hex
        $hexTs = dechex($ts);
        // pad it out to 8 chars
        $hexTs = str_pad($hexTs, 8, "0", STR_PAD_LEFT);
        // make an _id from it
        return new MongoId($hexTs . "0000000000000000");
    }

    /**
     *This function is used to generate CSV report of visiors during last 24 hours, 7 days and 4 weeks.
     */
    public function export_csvAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            $end_date = date('m/d/Y h:i:s');
            //$end = strtotime($end_date);
            //$rangeQuery = array('conditions' => array('EndDate' => array('$gte' => $end)));
            $get_domains = PagesToCampaign::find("EndDate >= '$end_date'");
            foreach ($get_domains as $domain) {
                $domainss[] = $domain->DomainURLIDX;
            }
            $domains = array_unique($domainss);

            //$query = array('conditions'=>array('CampaignType'=>'MANUAL','EndDate' => array('$gte' => $end)));
            //$get_manual_campaigns = ManualCampaign::find($query);

            $send_dom = array();
            $send_dom[0][0] = 'Website-Ranking ANALYTIK';
            $send_dom[1][0] = 'Website-Adresse';
            $send_dom[1][1] = 'Neueste';
            $send_dom[1][2] = '24 Stunden';
            $send_dom[1][3] = '7 Tage';
            $send_dom[1][4] = '4 Wochen';

            //Get domains rankings during last 24 hours, 7 days and 4 weeks.
            $j = 2;
            if (!empty($domains)) {
                foreach (@$domains as $value) {
                    $get_domain_url = StatusDomain::findFirst("DomainURLIDX='$value'");
                    if (!empty($get_domain_url))
                        $send_dom[$j][0] = $get_domain_url->DomainURL;

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
                    //24 Hours
                    $yesterday = date('m/d/Y', strtotime("-1 days"));
                    //$yest = new MongoDate(strtotime($yesterday));
                    $yest_end = date('m/d/Y');

                    //$query = array('conditions' => array('DomainURLIDX' => (int)$value,'Date' => array('$gte' => $yest,'$lt' => $yest_end)),'sort'=>array('Date'=>-1),'limit'=>1);
                    $get_domain_ranks_24 = DomainRankings::find("DomainURLIDX = '$value' AND Date >= '$yesterday' AND Date <= '$yest_end' ORDER BY Date DESC LIMIT 1");
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
                    //7 DAYS
                    $dyas7 = date('m/d/Y', strtotime("-7 days"));
                    //$day7 = new MongoDate(strtotime($dyas7));
                    $day7_end = date('m/d/Y', strtotime("-6 days"));
                    //$query = array('conditions' => array('DomainURLIDX' => (int)$value,'Date' => array('$gte' => $day7,'$lt' => $day7_end)),'sort'=>array('Date'=>-1),'limit'=>1);
                    $get_domain_ranks_7 = DomainRankings::find("DomainURLIDX ='$value' AND Date >= '$dyas7' AND Date <= '$day7_end' ORDER BY Date DESC LIMIT 1");
                    $diff_7 = 0;
                    if (count($get_domain_ranks_7) > 0) {
                        $domain_rank = array();
                        foreach ($get_domain_ranks_7 as $domain_r) {
                            $domain_rank[] = $domain_r->Rank;
                        }
                        if (is_numeric($domain_rank[0]) && is_numeric($latest_rank))
                            $diff_7 = ($latest_rank - $domain_rank[0]);
                        else
                            $diff_7 = $domain_rank[0];
                    }
                    //4 Weeks
                    $weeks4 = date('m/d/Y', strtotime("-4 weeks"));
                    //$week4 = new MongoDate(strtotime($weeks4));
                    $week_end = date('m/d/Y', strtotime("-4 weeks +1 Days"));

                    //$query = array('conditions' => array('DomainURLIDX' => (int)$value,'Date' => array('$gte' => $week4,'$lt'=>$week4_end)),'sort'=>array('Date'=>-1),'limit'=>1);
                    $get_domain_ranks_4 = DomainRankings::find("DomainURLIDX ='$value' AND Date >= '$weeks4' AND Date <= '$week_end' ORDER BY Date DESC LIMIT 1");

                    $diff_30 = 0;
                    if (count($get_domain_ranks_4) > 0) {
                        $domain_rank = array();
                        foreach ($get_domain_ranks_4 as $domain_r) {
                            $domain_rank[] = $domain_r->Rank;
                        }
                        if (is_numeric($domain_rank[0]) && is_numeric($latest_rank))
                            $diff_30 = ($latest_rank - $domain_rank[0]);
                        else
                            $diff_30 = $domain_rank[0];
                    }


                    $send_dom[$j][1] = $diff;
                    $send_dom[$j][2] = $diff_24;
                    $send_dom[$j][3] = $diff_7;
                    $send_dom[$j][4] = $diff_30;

                    //Get Keyword ranking of domains during last 24 hours, 7 days and 4 weeks.
                    $end_date = date('m/d/Y h:i:s');
                    //$rangeQuery = array('conditions' => array('EndDate' => array('$gte' => $end),'DomainURLIDX' => $value));
                    $get_campaigns = PagesToCampaign::find("EndDate >='$end_date' AND DomainURLIDX = '$value'");
                    $k = 0;
                    if (count($get_campaigns) > 0) {
                        foreach ($get_campaigns as $camp) {
                            $j++;
                            $keyword = '';
                            $send_dom[$j][0] = $camp->main_url;

                            if (@$camp->keywords_for_analytics != '')
                                $keyword = @$camp->keywords_for_analytics;

                            if ($keyword != '') {
                                //Latest
                                //$query = array('conditions' => array('Keyword'=>$keyword,'PageURL'=>$camp->main_url),"sort" => array('Date'=>-1),'limit'=>2);
                                $get_keyword_ranks = KeywordRankings::find("Keyword = '$keyword' AND PageURL = '$camp->main_url' ORDER BY Date DESC LIMIT 2");

                                $key_diff = 0;
                                $latest_key_rank = 0;
                                if (count($get_keyword_ranks) > 0) {
                                    $keyword_rank = array();
                                    foreach ($get_keyword_ranks as $key_r) {
                                        $keyword_rank[] = $key_r->Rank;
                                    }
                                    if (@is_numeric($keyword_rank[1]) && @is_numeric($keyword_rank[0]))
                                        $key_diff = ($keyword_rank[0] - $keyword_rank[1]);
                                    else
                                        $key_diff = $keyword_rank[0];
                                    if (is_numeric($keyword_rank[0]))
                                        $latest_key_rank = $keyword_rank[0];
                                }

                                //24 Hours
                                $yesterday = date('m/d/Y', strtotime("-1 days"));
                                //$yest = new MongoDate(strtotime($yesterday));
                                $yest_end = date('m/d/Y');

                                //$query = array('conditions' => array('Date' => array('$gte' => $yest,'$lt' => $yest_end),'Keyword'=>$keyword,'PageURL'=>$camp->main_url),'sort'=>array('Date'=>-1),'limit'=>1);
                                $get_keyword_ranks_24 = KeywordRankings::find("Date >= '$yesterday' AND Date < '$yest_end' AND Keyword = '$keyword' AND PageURL = '$camp->main_url' ORDER BY Date DESC LIMIT 1");
                                $key_diff_24 = 0;
                                if (count($get_keyword_ranks_24) > 0) {
                                    $keyword_rank = array();
                                    foreach ($get_keyword_ranks_24 as $key_r) {
                                        $keyword_rank[] = $key_r->Rank;
                                    }
                                    if (is_numeric($keyword_rank[0]) && is_numeric($latest_key_rank))
                                        $key_diff_24 = ($latest_key_rank - $keyword_rank[0]);
                                    else
                                        $key_diff_24 = $keyword_rank[0];
                                }

                                //7 DAYS
                                $dyas7 = date('m/d/Y', strtotime("-7 days"));
                                $day7_end = date('m/d/Y', strtotime("-6 days"));
                                //$query = array('conditions' => array('Date' => array('$gte' => $day7,'$lt' => $day7_end),'Keyword'=>$keyword,'PageURL'=>$camp->main_url),'sort'=>array('Date'=>-1),'limit'=>1);
                                $get_keyword_ranks_7 = KeywordRankings::find("Date >= '$dyas7' AND Date < '$day7_end' AND Keyword = '$keyword' AND PageURL = '$camp->main_url' ORDER BY Date DESC LIMIT 1");
                                $key_diff_7 = 0;
                                if (count($get_keyword_ranks_7) > 0) {
                                    $keyword_rank = array();
                                    foreach ($get_keyword_ranks_7 as $key_r) {
                                        $keyword_rank[] = $key_r->Rank;
                                    }
                                    if (is_numeric($keyword_rank[0]) && is_numeric($latest_key_rank))
                                        $key_diff_7 = ($latest_key_rank - $keyword_rank[0]);
                                    else
                                        $key_diff_7 = $keyword_rank[0];
                                }
                                //4 Weeks
                                $weeks4 = date('m/d/Y', strtotime("-4 weeks"));
                                $week_end = date('m/d/Y', strtotime("-4 weeks +1 Days"));

                                //$query = array('conditions' => array('Date' => array('$gte' => $week4,'$lt' => $week4_end),'Keyword'=>$keyword,'PageURL'=>$camp->main_url),'sort'=>array('Date'=>-1),'limit'=>1);
                                $get_keyword_ranks_4 = KeywordRankings::find("Date >= '$dyas7' AND Date < '$weeks4' AND Keyword = '$week_end' AND PageURL = '$camp->main_url' ORDER BY Date DESC LIMIT 1");
                                $key_diff_30 = 0;
                                if (count($get_keyword_ranks_4) > 0) {
                                    $keyword_rank = array();
                                    foreach ($get_keyword_ranks_4 as $key_r) {
                                        $keyword_rank[] = $key_r->Rank;
                                    }
                                    if (is_numeric($keyword_rank[0]) && is_numeric($latest_key_rank))
                                        $key_diff_30 = ($latest_key_rank - $keyword_rank[0]);
                                    else
                                        $key_diff_30 = $keyword_rank[0];
                                }
                                $j++;
                                $send_dom[$j][0] = $keyword;
                                $send_dom[$j][1] = $key_diff;
                                $send_dom[$j][2] = $key_diff_24;
                                $send_dom[$j][3] = $key_diff_7;
                                $send_dom[$j][4] = $key_diff_30;
                            }
                        }
                        $j++;
                        $send_dom[$j][0] = '';
                        $send_dom[$j][1] = '';
                        $send_dom[$j][2] = '';
                        $send_dom[$j][3] = '';
                        $send_dom[$j][4] = '';
                    }

                    $j++;
                }
            }
            /*if(!empty($get_manual_campaigns))
            {
                $j++;
                foreach(@$get_manual_campaigns as $camp)
                {
                    $send_dom[$j][0] = $camp->PageURL;

                    //Latest
                    $query = array('conditions' => array('DomainURL' => $camp->PageURL),"sort" => array('Date'=>-1),'limit'=>2);
                    $get_domain_ranks = DomainRankings::find($query);
                    $diff =0; $latest_rank =0;
                    if(count($get_domain_ranks) > 0)
                    {
                        $domain_rank = array();
                        foreach($get_domain_ranks as $domain_r)
                        {
                            $domain_rank[] = $domain_r->Rank;
                        }
                        if(@is_numeric($domain_rank[1]) && @is_numeric($domain_rank[0]))
                            $diff = ($domain_rank[0]-$domain_rank[1]);
                        else
                            $diff = $domain_rank[0];
                        if(is_numeric($domain_rank[0]))
                            $latest_rank = $domain_rank[0];
                    }
                    //24 Hours
                    $yesterday = date('m/d/Y',strtotime("-1 days"));
                    $yest = new MongoDate(strtotime($yesterday));
                    $yest_end = new MongoDate(strtotime(date('m/d/Y')));

                    $query = array('conditions' => array('DomainURL' => $camp->PageURL,'Date' => array('$gte' => $yest,'$lt' => $yest_end)),'sort'=>array('Date'=>-1),'limit'=>1);
                    $get_domain_ranks_24 = DomainRankings::find($query);
                    $diff_24 =0;
                    if(count($get_domain_ranks_24) > 0)
                    {
                        $domain_rank = array();
                        foreach($get_domain_ranks_24 as $domain_r)
                        {
                            $domain_rank[] = $domain_r->Rank;
                        }
                        if(is_numeric($domain_rank[0]) && is_numeric($latest_rank))
                            $diff_24 = ($latest_rank-$domain_rank[0]);
                        else
                            $diff_24 = $domain_rank[0];
                    }
                    //7 DAYS
                    $dyas7 = date('m/d/Y',strtotime("-7 days"));
                    $day7 = new MongoDate(strtotime($dyas7));
                    $day7_end = new MongoDate(strtotime(date('m/d/Y',strtotime("-6 days"))));
                    $query = array('conditions' => array('DomainURL' => $camp->PageURL,'Date' => array('$gte' => $day7,'$lt' => $day7_end)),'sort'=>array('Date'=>-1),'limit'=>1);
                    $get_domain_ranks_7 = DomainRankings::find($query);
                    $diff_7 =0;
                    if(count($get_domain_ranks_7) > 0)
                    {
                        $domain_rank = array();
                        foreach($get_domain_ranks_7 as $domain_r)
                        {
                            $domain_rank[] = $domain_r->Rank;
                        }
                        if(is_numeric($domain_rank[0]) && is_numeric($latest_rank))
                            $diff_7 = ($latest_rank-$domain_rank[0]);
                        else
                            $diff_7 = $domain_rank[0];
                    }
                    //4 Weeks
                    $weeks4 = date('m/d/Y',strtotime("-4 weeks"));
                    $week4 = new MongoDate(strtotime($weeks4));
                    $week_end = strtotime(date('m/d/Y',strtotime("-4 weeks +1 Days")));
                    $week4_end = new MongoDate($week_end);
                    $query = array('conditions' => array('DomainURL' => $camp->PageURL,'Date' => array('$gte' => $week4,'$lt'=>$week4_end)),'sort'=>array('Date'=>-1),'limit'=>1);
                    $get_domain_ranks_4 = DomainRankings::find($query);
                    $diff_30 =0;
                    if(count($get_domain_ranks_4) > 0)
                    {
                        $domain_rank = array();
                        foreach($get_domain_ranks_4 as $domain_r)
                        {
                            $domain_rank[] = $domain_r->Rank;
                        }
                        if(is_numeric($domain_rank[0]) && is_numeric($latest_rank))
                            $diff_30 = ($latest_rank-$domain_rank[0]);
                        else
                            $diff_30 = $domain_rank[0];
                    }


                    $send_dom[$j][1] = $diff;
                    $send_dom[$j][2] = $diff_24;
                    $send_dom[$j][3] = $diff_7;
                    $send_dom[$j][4] = $diff_30;


                            $j++;
                            $keyword='';
                            $send_dom[$j][0] = $camp->PageURL;
                            if(@$camp->keywords_for_analytics!='')
                                $keyword = @$camp->keywords_for_analytics;

                            if($keyword!='')
                            {
                                //Latest
                                    $query = array('conditions' => array('Keyword'=>$keyword,'PageURL'=>$camp->PageURL),"sort" => array('Date'=>-1),'limit'=>2);
                                    $get_keyword_ranks = KeywordRankings::find($query);

                                    $key_diff =0; $latest_key_rank =0;
                                    if(count($get_keyword_ranks) > 0)
                                    {
                                        $keyword_rank = array();
                                        foreach($get_keyword_ranks as $key_r)
                                        {
                                            $keyword_rank[] = $key_r->Rank;
                                        }
                                        if(@is_numeric($keyword_rank[1]) && @is_numeric($keyword_rank[0]))
                                            $key_diff = ($keyword_rank[0]-$keyword_rank[1]);
                                        else
                                            $key_diff = $keyword_rank[0];
                                        if(is_numeric($keyword_rank[0]))
                                            $latest_key_rank = $keyword_rank[0];
                                    }

                                    //24 Hours
                                    $yesterday = date('m/d/Y',strtotime("-1 days"));
                                    $yest = new MongoDate(strtotime($yesterday));
                                    $yest_end = new MongoDate(strtotime(date('m/d/Y')));

                                    $query = array('conditions' => array('Date' => array('$gte' => $yest,'$lt' => $yest_end),'Keyword'=>$keyword,'PageURL'=>$camp->PageURL),'sort'=>array('Date'=>-1),'limit'=>1);
                                    $get_keyword_ranks_24 = KeywordRankings::find($query);
                                    $key_diff_24 =0;
                                    if(count($get_keyword_ranks_24) > 0)
                                    {
                                        $keyword_rank = array();
                                        foreach($get_keyword_ranks_24 as $key_r)
                                        {
                                            $keyword_rank[] = $key_r->Rank;
                                        }
                                        if(is_numeric($keyword_rank[0]) && is_numeric($latest_key_rank))
                                            $key_diff_24 = ($latest_key_rank-$keyword_rank[0]);
                                        else
                                            $key_diff_24 = $keyword_rank[0];
                                    }

                                    //7 DAYS
                                    $dyas7 = date('m/d/Y',strtotime("-7 days"));
                                    $day7 = new MongoDate(strtotime($dyas7));
                                    $day7_end = new MongoDate(strtotime(date('m/d/Y',strtotime("-6 days"))));
                                    $query = array('conditions' => array('Date' => array('$gte' => $day7,'$lt' => $day7_end),'Keyword'=>$keyword,'PageURL'=>$camp->PageURL),'sort'=>array('Date'=>-1),'limit'=>1);
                                    $get_keyword_ranks_7 = KeywordRankings::find($query);
                                    $key_diff_7 =0;
                                    if(count($get_keyword_ranks_7) > 0)
                                    {
                                        $keyword_rank = array();
                                        foreach($get_keyword_ranks_7 as $key_r)
                                        {
                                            $keyword_rank[] = $key_r->Rank;
                                        }
                                        if(is_numeric($keyword_rank[0]) && is_numeric($latest_key_rank))
                                            $key_diff_7 = ($latest_key_rank-$keyword_rank[0]);
                                        else
                                            $key_diff_7 = $keyword_rank[0];
                                    }
                                    //4 Weeks
                                    $weeks4 = date('m/d/Y',strtotime("-4 weeks"));
                                    $week4 = new MongoDate(strtotime($weeks4));
                                    $week_end = strtotime(date('m/d/Y',strtotime("-4 weeks +1 Days")));
                                    $week4_end = new MongoDate($week_end);

                                    $query = array('conditions' => array('Date' => array('$gte' => $week4,'$lt' => $week4_end),'Keyword'=>$keyword,'PageURL'=>$camp->PageURL),'sort'=>array('Date'=>-1),'limit'=>1);
                                    $get_keyword_ranks_4 = KeywordRankings::find($query);
                                    $key_diff_30 =0;
                                    if(count($get_keyword_ranks_4) > 0)
                                    {
                                        $keyword_rank = array();
                                        foreach($get_keyword_ranks_4 as $key_r)
                                        {
                                            $keyword_rank[] = $key_r->Rank;
                                        }
                                        if(is_numeric($keyword_rank[0]) && is_numeric($latest_key_rank))
                                            $key_diff_30 = ($latest_key_rank-$keyword_rank[0]);
                                        else
                                            $key_diff_30 = $keyword_rank[0];
                                    }
                                    $j++;
                                    $send_dom[$j][0] = $keyword;
                                    $send_dom[$j][1] = $key_diff;
                                    $send_dom[$j][2] = $key_diff_24;
                                    $send_dom[$j][3] = $key_diff_7;
                                    $send_dom[$j][4] = $key_diff_30;
                            }

                        $j++;
                        $send_dom[$j][0] = '';
                        $send_dom[$j][1] = '';
                        $send_dom[$j][2] = '';
                        $send_dom[$j][3] = '';
                        $send_dom[$j][4] = '';


                    $j++;
                }

            }*/
            if (empty($domains)) {
                $send_dom[$j][0] = 'There is no live campaign present at the moment.';
            }
            $this->array_to_csv_download($send_dom, 'export_analytics.csv');
        }
        $this->view->disable();
    }

    /**
     *This function is used to write content into CSV file to download.
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
     *This function is used to encode date into JSON format.
     */
    function ConverDateJson($sourceArray)
    {
        $return = array();
        foreach ($sourceArray as $key => $value) {
            $return[] = array(
                "value" => $value,
                "date" => date('m/d/Y h:i:s A', strtotime($key))
            );
        }
        return json_encode($return);
    }
}