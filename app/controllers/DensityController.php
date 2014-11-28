<?php
set_time_limit(0);

use \Phalcon\Mvc\View;
use \Phalcon\DB;

class DensityController extends BaseController
{
    public function initialize()
    {
        $this->view->setVar("page", "density");
        if ($this->dispatcher->getActionName() === "index") {
            $this->get_user_history();
            $this->view->setVar("action_name", $this->dispatcher->getActionName());

            $this->density_page_workflow();

            $this->get_crawler_info();
        }
    }

    public function get_crawler_info()
    {
        $query = "SELECT * FROM _sitemap_cron_config";
        $info = $this->db->fetchOne($query, Db::FETCH_ASSOC);

        //checking for nodejs server - no longer needed
        /*
        $link = $info["nodejs_server"];
        $response = implode("", file($link));
        $response = strtolower(trim($response));
        if($response === "nothing to do.") {
            $nodejs_status = "ONLINE";
        } else {
            $nodejs_status = "OFFLINE";
        }*/

        //checking for cronjob worker:
        /*$host = "http://".$info["cronjob_server"];
        $path = $info["path_to_process_php"];
        $path = substr($path, 0, strrpos($path, "/"));
        $link = $host.$path."/report_status.php";

        $cron_status = implode("", file($link));
        $cron_status = trim($cron_status);*/

        $cron_status = 'on';
        //set for view:
        $this->view->setVar("cron_status", $cron_status);
        //$this->view->setVar("nodejs_status", $nodejs_status); // - not being used
        $this->view->setVar("current_job", $info["current_job"]);
        $this->view->setVar("current_hours", $info["hours"]);
        $this->view->setVar("current_seconds", $info["seconds"]);
        $this->view->setVar("cron_config_id", $info["id"]);
        $this->view->setVar("current_browser", $info["browser"]);
    }

    public function savecronconfigAction($id, $hours, $seconds, $browser_id)
    {
        //needed:
        $this->view->disable();

        //get browser name from db by id:
        $query = "SELECT id, name FROM _sitemap_browsers WHERE id='" . $browser_id . "'";
        $browser = $this->db->fetchOne($query, Db::FETCH_ASSOC);
        $browser_name = trim($browser["name"]);

        //update config:
        $table = "_sitemap_cron_config";
        $query = "UPDATE " . $table . " SET hours='" . $hours . "', seconds='" . $seconds . "', browser='" . $browser_name . "' WHERE id='" . $id . "'";
        $this->db->execute($query);

        exit("Changes saved.");
    }

    public function get_user_history()
    {
        $user_id = $this->session->get("user-id");
        $user_history = $this->db->fetchAll("SELECT * FROM _sitemap_user_history WHERE user_id='" . $user_id . "'", Db::FETCH_OBJ);
        if (count($user_history) < 1) {
            $user_history = array();
        }

        $this->view->setVar("user_history", $user_history);
    }

    public function density_page_workflow()
    {
        //needed information
        $user_id = $this->session->get("user-id");
        $user_type = $this->session->get("user-role");
        $user_editing = $this->session->get("user-editing");

        //and sets:
        $is_admin = ($user_type === "normal") ? false : true;
        if ($is_admin) {
            $user_editing = true;
        }

        //first we get granted user's domains - this one contains idx's of domains:
        $granted_domains = $this->session->get("user-domains");
        if (isset($granted_domains[0]) && $granted_domains[0] === "") {
            unset($granted_domains[0]); //remove the one that is not having anything set;
        }

        //now we get status_domain links
        $status_domains = $this->get_status_domain_list();

        //then we get domains added inside _domain_info:
        $query = "SELECT * FROM _sitemap_domain_info";
        $domain_info = $this->db->fetchAll($query, Db::FETCH_ASSOC);
        $domain_info_names = array(); //keeps track of the domain name from this table;

        //check to see if domains from _domain_info are in status_domain_list and update with "idx" if present
        foreach ($domain_info as $d_no => $info) {
            $domain_id = $info["id"];
            $domain = $info["domain_name"];
            $domain_info_names[] = $domain; //save domain name

            foreach ($status_domains as $d_no2 => $info2) {
                $s_domain = $info2["domain"];
                $idx = $info2["idx"];

                if ($s_domain === $domain and $info["idx"] == "0") {
                    //run query:
                    $query = "UPDATE _sitemap_domain_info SET idx='" . $idx . "', create_report='0' WHERE id='" . $domain_id . "'";
                    $this->db->execute($query);

                    //also update the array:
                    $domain_info[$d_no]["idx"] = $idx;
                }
            }
        }

        //remove from $status_domain the domains that are NOT granted:
        if (!$is_admin) {
            foreach ($status_domains as $d_no => $info) {
                $idx = $info["idx"];

                if (!in_array($idx, $granted_domains)) {
                    unset($status_domains[$d_no]);
                }
            }
        }

        //remove from $status_domain the domains that are ALREADY in _sitemap_domain_info:
        foreach ($status_domains as $d_no => $info) {
            $domain_name = $info["domain"];

            if (in_array($domain_name, $domain_info_names)) {
                unset($status_domains[$d_no]);
            }
        }

        //grabbing domain information that will be showed:
        $user_domains = $domain_extra = $domain_count = array();

        foreach ($domain_info as $d_no => $info) {
            $idx = $info["idx"];
            $domain_user_id = $info["user_id"];

            //ONLY: for ADMINS, if it's a GRANTED domain or domain ADDED by THIS user:
            if ($is_admin or in_array($idx, $granted_domains) or $domain_user_id === $user_id) {
                $domain_extra[$info["id"]] = $this->get_main_page_info($info["id"], $info["domain_name"]);

                //get no of parsed & not-parsed links:
                $result = $this->db->fetchAll("SELECT count(CASE WHEN parsed_status>=0 then 1 else null end) as total_links, count(CASE WHEN parsed_status=0 then 1 else null end) as NotParsed, count(CASE WHEN parsed_status>=1 then 1 else null end) as Parsed1, count(CASE WHEN parsed_status='2' then 1 else null end) as Parsed2 FROM _sitemap_links WHERE domain_id='" . $info["id"] . "'", Db::FETCH_ASSOC);
                $domain_count[$info["id"]]["parsed1"] = $result[0]["Parsed1"]; // parsed_status>=1
                $domain_count[$info["id"]]["parsed2"] = $result[0]["Parsed2"]; // parsed_status = 2
                $domain_count[$info["id"]]["not_parsed"] = $result[0]["NotParsed"]; //parsed_status = 0
                $domain_count[$info["id"]]["total_links"] = $result[0]["total_links"]; //parsed_status>=0

                //build up new array:
                $user_domains[$d_no] = $domain_info[$d_no];
            }
        }

        //get available browsers:
        $query = "SELECT * FROM _sitemap_browsers";
        $browsers = $this->db->fetchAll($query, Db::FETCH_ASSOC);

        //view sets:
        $this->view->setVar("available_browsers", $browsers);
        $this->view->setVar("user_editing", $user_editing);
        $this->view->setVar("user_domains", $user_domains);
        $this->view->setVar("domain_extra", $domain_extra);
        $this->view->setVar("domain_count", $domain_count);
        $this->view->setVar("status_domains", $status_domains);
    }

    public function get_main_page_info($domain_id, $domain_name)
    {
        $query = "SELECT id FROM _sitemap_links WHERE domain_id='" . $domain_id . "'";
        $query .= " AND ( page_url LIKE '%" . $domain_name . "' OR page_url LIKE '%" . $domain_name . "/')";
        $query .= " LIMIT 1";

        $result = $this->db->fetchOne($query, Db::FETCH_ASSOC);
        $main_page_id = $result["id"];

        //export-like action:
        $o = new ExportController();
        $r = $o->DomainAction($domain_id, $main_page_id);

        //out:
        $this->view->enable(); // - needed

        return $r;
    }

    //getting domains that he is allowed to edit (or view in case of "density page")
    public function get_status_domain_list()
    {
        $out = array();

        //get status domains
        $query = "SELECT DomainURL as domain, DomainURLIDX as idx from status_domain";
        $results_1 = $this->db->fetchAll($query, Db::FETCH_ASSOC);

        foreach ($results_1 as $r_no => $r) {
            $parts = parse_url($r["domain"]);
            $domain = str_ireplace("www.", "", $parts["host"]);

            //check to not be already added:
            $out[$r_no]["idx"] = $r["idx"];
            $out[$r_no]["domain"] = $domain;
        }

        return $out;
    }

    public function IndexAction()
    {

    }

    public function uploadAction()
    {
        $this->view->disable();
        $request = $this->request;

        //here we are saving files to be processed - temporary;
        $temp_dir = "temp_files/";
        $user_id = $this->session->get("user-id");

        //pattern for saving history:
        $history_query = "INSERT INTO _sitemap_user_history (user_id, domain_id, action_type, entry_name, density_result) VALUES ('%d','%d','%s', '%s', '%s')";
        $upload_file = true;

        if ($request->isPost()) {
            $action = $request->getPost("action");

            switch ($action) {
                case "csv_list":
                    $upload_file = false;
                    $domain_id = $request->getPost("domain_id");

                    #handle words list from user:
                    $words = $request->getPost("csv_list");
                    $words = explode("\n", $words);
                    $words = $this->get_words($words);

                    //make the search in the table
                    $density = $this->get_words_density($words, $domain_id);
                    //$density = json_encode($density, JSON_UNESCAPED_UNICODE);

                    //for php 5.3:
                    $density = json_encode($density);
                    $density = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
                            $sym = mb_convert_encoding(
                                pack('H*', $matches[1]),
                                'UTF-8',
                                'UTF-16'
                            );
                            return $sym;
                        },
                        $density
                    );

                    //save into history
                    $query = sprintf($history_query, $user_id, $domain_id, $action, "wordslist_" . rand(100, 999), $density);
                    $this->db->execute($query);
                    echo json_encode(array("history_id" => $this->db->lastInsertId()));
                    break;

                //this section occures when a domain is being imported from the other table:
                case "domain_id":
                    $upload_file = false;
                    $status_domain_id = $request->getPost("domain_id");
                    $status_domain_name = $this->get_status_domain_name($status_domain_id);
                    $result = $this->check_save_domain($status_domain_name, $status_domain_id);
                    if (!$result["existed"]) {
                        echo "The domain <b>" . $status_domain_name . "</b> - has been added.<br/>";
                    } else {
                        echo "Failed to add <b>" . $status_domain_name . "</b> - might be already added.<br/>";
                    }
                    break;
            }
        }

        //check for uploaded files:
        if ($request->hasFiles() && $upload_file !== false) {
            foreach ($this->request->getUploadedFiles() as $file) {
                //check the file type - not needed

                //continue / not
                $file_name = $file->getName();
                $file_path = $temp_dir . $file_name . "_" . time();
                $file->moveTo($file_path);

                //prepare history query:
                switch ($action) {
                    case "xml_file":
                        $domain_id = "";
                        $query = sprintf($history_query, $user_id, $domain_id, $action, $file_name, "");

                        //save into history
                        $this->db->execute($query);
                        //$last_history_id = $this->db->lastInsertId(); // last history_id - not used;

                        $links = $this->get_xml_links($file_path); //grab links
                        $new_links = $this->get_xml_links_with_domains($links); //grab with domains

                        //save domain and links
                        $msg = $this->save_xml_links($new_links);

                        if ($msg === "no new") {
                            echo "No new links to be added.";
                        } else if ($msg === false) {
                            echo "Failed to parse links";
                        } else {
                            echo "<b>Uploaded:</b> " . $file_name . "<br/>";
                            echo "<b>Total (new) links found:</b> " . $msg . " on " . count($new_links) . " domain/s</br>";
                        }
                        break;
                    case "csv_file":
                        $domain_id = $request->getPost("domain_id");

                        //parse csv file - getting words:
                        $words = $this->get_csvs_words($file_path);

                        ///make the search in the table
                        $density = $this->get_words_density($words, $domain_id);
                        //$density = json_encode($density, JSON_UNESCAPED_UNICODE);


                        //for php 5.3:
                        $density = json_encode($density);
                        $density = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
                                $sym = mb_convert_encoding(
                                    pack('H*', $matches[1]),
                                    'UTF-8',
                                    'UTF-16'
                                );
                                return $sym;
                            },
                            $density
                        );

                        //save into history
                        $query = sprintf($history_query, $user_id, $domain_id, $action, $file_name, $density);
                        $this->db->execute($query);
                        echo json_encode(array("history_id" => $this->db->lastInsertId()));
                        break;
                }

                //remove file from temp directory:
                unlink($file_path);
            }
        }
    }

    // Functions used by Actions

    public function get_words($lines)
    {
        $words = array();

        foreach ($lines as $l_no => $line) {
            $word = trim($line);

            if (strlen($word) > 0) {
                $words[] = $word;
            }
        }

        return $words;
    }

    public function get_words_density($word_list, $domain_id)
    {
        $sortable = $save = array();

        //get words from list one by one
        foreach ($word_list as $no => $searched_word) {
            $searched_word = trim($searched_word);

            //first we search it into db
            $query = "SELECT info.*, links.id, links.page_url, links.domain_id FROM _sitemap_links_info as info, _sitemap_links as links WHERE density LIKE '%" . ($searched_word) . "%' AND info.link_id=links.id AND links.domain_id='" . $domain_id . "'";;
            $result = $this->db->fetchAll($query, Db::FETCH_OBJ);

            //get results
            foreach ($result as $r_no => $r) {
                $content = $r->density;

                //get total words:
                $words = $words_temp = array();
                $words_temp = explode(" ", $content);
                $regexp = "'/^\p{L}[\p{L} _.-]+$/u'";
                foreach ($words_temp as $c_on => $word) {
                    if (preg_match($regexp, $word, $matched)) {
                        $words[] = strtolower($matched[0]);
                    }
                }

                print_r($words);
                exit();
                $total_words = count($words);

                //change value of total_words if expression (e.g. "some words"):
                $expression = false;
                if (stripos($searched_word, " ") !== false) {
                    #deprecated:
                    /*$in_word = substr_count($searched_word, " ");
                    $total_words = ceil($total_words/($in_word+1));*/
                    $expression = true;
                }

                //how many times is the word/expression in text?
                $occurrences = 0;
                if ($expression) {
                    $occurrences = substr_count(strtolower($content), strtolower($searched_word));
                } else {
                    $words_temp2 = array_count_values($words);
                    $tempo_word = strtolower($searched_word);
                    if (array_key_exists($tempo_word, $words_temp2)) {
                        $occurrences = $words_temp2[$tempo_word];
                    }
                }

                //skip maine page:
                $parse = parse_url($r->page_url);
                if ($parse["path"] !== "/" && $occurrences != 0) {
                    $percentage = number_format(($occurrences * 100 / $total_words), 2);
                    $save[$searched_word][$r_no]["page_id"] = $r->link_id;
                    $save[$searched_word][$r_no]["page_url"] = $r->page_url;
                    $save[$searched_word][$r_no]["percentage"] = $percentage;

                    #removable information - debugging purposes:
                    //$save[$searched_word][$r_no]["w_occ"] = $occurrences;
                    //$save[$searched_word][$r_no]["w_total"] = $total_words;
                    //$save[$searched_word][$r_no]["content"] = $content;

                    //needed to grab only first 2:
                    $sortable[$searched_word][$r_no] = $percentage;
                }
            }
        }

        //check for first 2 in the sortable array:
        $max_links = 10;
        $first_2 = array();
        foreach ($sortable as $word => $arr) {
            uasort($arr, array($this, "sort_by_percentage_desc"));
            $total_results = count($arr);
            if ($total_results > $max_links) {
                $first_2[$word] = array_slice($arr, 0, $max_links, TRUE); //use TRUE to keep keys. We need this because this are the page ids
            } else {
                $first_2[$word] = $arr;
            }
        }

        //remove the rest of results:
        foreach ($save as $searched_word => $info) {
            foreach ($info as $key => $val) {
                if (!array_key_exists($key, $first_2[$searched_word])) {
                    unset($save[$searched_word][$key]);
                }
            }
        }

        //add to array the words that were not found:
        foreach ($word_list as $w_no => $word) {
            $word = trim($word);
            if (!array_key_exists($word, $save)) {
                $save[$word] = "Word not found.";
            }
        }

        #todo - buildup query for google api (cpc)

        return $save;
    }

    public function get_status_domain_name($idx)
    {
        $domain = false;
        $query = "SELECT DomainURL as domain, DomainURLIDX as idx from status_domain WHERE DomainURLIDX='" . $idx . "'";
        $result = $this->db->fetchOne($query, Db::FETCH_ASSOC);

        if ($result) {
            $parts = parse_url($result["domain"]);
            $domain = str_ireplace("www.", "", $parts["host"]);
        }

        return $domain;
    }

    public function check_save_domain($domain_name, $idx)
    {
        $user_id = $this->session->get("user-id");
        $exists = false;
        $last_domain_id = false;

        //first we check for domain:
        $result = $this->db->fetchOne("SELECT * FROM _sitemap_domain_info WHERE domain_name='" . $domain_name . "'");
        if (is_array($result)) {
            $last_domain_id = $result["id"];
            $exists = true;
        }

        //then we save the domain
        if (!$exists) {
            $query = "INSERT INTO _sitemap_domain_info (idx, user_id, domain_name, create_report) VALUES ('" . $idx . "','" . $user_id . "', '" . $domain_name . "', '0')";
            try {
                $this->db->execute($query);
                $last_domain_id = $this->db->lastInsertId();
            } catch (PDOException $e) {
                //$err_msg = $e->getMessage();
            }
        }

        return array("last_domain_id" => $last_domain_id, "existed" => $exists);
    }

    public function get_xml_links($path_to_file)
    {
        //grab links:
        $links = array();
        $content = implode("", file($path_to_file));

        //file encoding handler:
        $encoding = mb_detect_encoding($content);
        if (strtolower($encoding) !== "utf-8") {
            $content = utf8_encode($content);
        }

        if (preg_match_all("/>((http|https):\/\/[^\s]+)</", $content, $matched)) {
            $links = $matched[1];
        }

        if (count($links) > 0) {
            //echo "<pre>";
            //print_r($links);
            //echo "</pre>";
        }

        return $links;
    }

    public function get_xml_links_with_domains($links)
    {
        $new_one = array();

        foreach ($links as $link_on => $link) {
            $parts = parse_url($link);
            if (array_key_exists("host", $parts)) {
                $temp_domain = $parts["host"];
            } else {
                $temp_domain = $link;
            }

            $temp_domain = str_ireplace("www.", "", $temp_domain);
            $new_one[$temp_domain][] = $link;
        }

        return $new_one;
    }

    public function save_xml_links($list)
    {
        $message = false;

        foreach ($list as $d_key => $domain) {
            $result = $this->check_save_domain($d_key, 0);
            $last_domain_id = $result["last_domain_id"];
            $exists = $result["existed"];

            $current_links = array();
            if ($exists) {
                $query = "SELECT page_url FROM _sitemap_links WHERE domain_id=" . $last_domain_id;
                $result = $this->db->fetchAll($query, Db::FETCH_ASSOC);
                foreach ($result as $r_no => $r) {
                    $current_links[$r["page_url"]] = "";
                }
            }


            //now we prepare query for all links found:
            $run_query = false;
            $query = "INSERT INTO _sitemap_links (domain_id, page_url) VALUES ";
            $values = "";
            $total = 0;
            foreach ($domain as $d_name => $link) {
                if (!array_key_exists($link, $current_links)) {
                    $values .= "( '" . $last_domain_id . "','" . $link . "'),";
                    $run_query = true;
                    $total++;
                }
            }


            //insert them:
            if ($run_query) {
                $values = substr($values, 0, (strlen($values) - 1)); // remove last ","
                $this->db->execute($query . $values);
                $message = $total;
            } else {
                $message = "no new";
            }
        }

        return $message;
    }

    public function get_csvs_words($path_to_file)
    {
        $lines = implode("", file($path_to_file));

        //file encoding handler:
        $encoding = mb_detect_encoding($lines);
        if (strtolower($encoding) !== "utf-8") {
            $lines = utf8_encode($lines);
        }

        $lines = explode("\n", $lines);

        #TODO parse CSV file - not needed - now it gets only a simple file *.txt

        //get words:
        return $this->get_words($lines);
    }

    //expects array:

    public function sort_by_percentage_desc($a, $b)
    {
        if ($a == $b) {
            return 0;
        }

        return ($a < $b) ? 1 : -1;
    }

    // arguments: array, number - FUNCTION NOT BEING USED ANYMORE

    public function save_words_deprecated($words, $last_id)
    {
        $words = implode("\n", $words);
        $query = "INSERT INTO _sitemap_user_words (history_id, words) VALUES ";
        $values = "('" . $last_id . "', '" . $words . "')";

        $this->db->execute($query . $values);
    }

    // argument: array;

    public function get_words_density_deprecated($word_list, $domain_id)
    {
        $save = array();

        foreach ($word_list as $no => $searched_word) {
            $save_temp = array();

            $searched_word = trim($searched_word);
            $query = "SELECT info.*, links.id, links.page_url, links.domain_id FROM _sitemap_links_info as info, _sitemap_links as links WHERE density LIKE '%|" . ($searched_word) . "|%' AND info.link_id=links.id AND links.domain_id='" . $domain_id . "'";
            $result = $this->db->fetchAll($query, Db::FETCH_OBJ);

            foreach ($result as $r_no => $r) {
                $page_id = $r->link_id;
                $lines = explode("\n", $r->density); //split density content line by line

                //get line by line:
                foreach ($lines as $l_no => $line) {
                    $line = trim($line);

                    //check for line's length first:
                    if (strlen($line) > 0) {
                        if ($line[0] === "|") {
                            $line = substr($line, 1); //remove first "|" + trimming
                        }

                        //split line:
                        list($c_word, $percentage) = explode("|", $line);

                        //check for word:
                        if (strtolower($searched_word) === $c_word) {
                            $save_temp[$searched_word][$page_id]["perc"] = $percentage;
                            $save_temp[$searched_word][$page_id]["link"] = $r->page_url;
                        }
                    }
                }
            }

            //sort the array descending and get only first $max_links
            $max_links = 2;
            foreach ($save_temp as $word_name => $arr) {
                uasort($arr, array($this, "sort_by_percentage_desc"));
                $total_results = count($arr);
                if ($total_results > $max_links) {
                    $save[$word_name] = array_slice($arr, 0, $max_links, TRUE); //use TRUE to keep keys. We need this because this are the page ids
                } else {
                    $save[$word_name] = $arr;
                }
            }
        }

        //add the not found words:
        foreach ($word_list as $no => $word) {
            if (!array_key_exists($word, $save)) {
                $save[$word] = array();
            }
        }

        //show off:
        return $save;
    }

    public function getjsonAction()
    {
        $this->view->disable();

        $messages = array(
            "domain" => array("label" => "No domain selected", "id" => ""),
            "keyword" => array("label" => "Please input keword", "id" => ""),
            "results" => array("label" => "No results.", "id" => ""),
        );

        $domain_id = $_GET["domain"];
        if ($domain_id === "0") {
            exit(json_encode($messages["domain"]));
        }

        $searched = trim($_GET["searched"]);
        if (strlen($searched) < 1) {
            exit(json_encode($messages["keyword"]));
        }

        //some cleanup
        $remove = array("_", "-");
        $searched = str_replace($remove, " ", $searched);

        //prepare cases:
        $cases[] = $searched;
        if (stripos($searched, " ") !== false) {
            $cases[] = str_replace(" ", "_", $searched);
            $cases[] = str_replace(" ", "-", $searched);
            $cases[] = str_replace(" ", "%", $searched);
        }

        //buildup query:
        $query = "SELECT id, page_url FROM _sitemap_links WHERE domain_id='" . $domain_id . "' AND (";
        foreach ($cases as $c_no => $case) {
            $query .= "page_url LIKE '%" . $case . "%' OR ";
        }
        $query = substr($query, 0, strrpos($query, "OR "));
        $query .= ") AND parsed_status !=0 LIMIT 50";

        //get result:
        $result = $this->db->fetchAll($query, Db::FETCH_ASSOC);

        foreach ($result as $r_no => $arr) {
            $json[$r_no]["label"] = $arr["page_url"];
            $json[$r_no]["id"] = $arr["id"];
        }

        if (isset($json)) {
            //debugg:
            //echo "<pre>";
            //print_r($result);
            exit(json_encode($json));
        } else {
            exit(json_encode($messages["results"]));
        }
    }

    public function getdomainreportAction($domain_id = 0)
    {
        $this->view->disable();
        $query = "SELECT report_file FROM _sitemap_domain_info WHERE id=" . $domain_id;
        $result = $this->db->fetchOne($query, Db::FETCH_ASSOC);

        //pre-checks:
        if (!isset($result["report_file"])) {
            exit("No report file.");
        }

        //remove spaces, just in case:
        $file_name = trim($result["report_file"]);

        //safety check:
        if (strlen($file_name) > 0 and stripos($file_name, ".php") === false and stripos($file_name, ".csv") !== true) {
            header("Location: http://107.170.94.168/extract_body/" . $file_name);
        }
    }

    public function removeDomainAction($id)
    {
        $this->view->disable();
        $_prefix = "_sitemap_";
        #first we remove domain from domain_info:
        $query = "DELETE FROM " . $_prefix . "domain_info WHERE id=" . $id;
        $this->db->execute($query);

        //now let's get all link_id s:
        $page_ids = array();
        $query = "SELECT id FROM " . $_prefix . "links WHERE domain_id=" . $id;
        $result = $this->db->fetchAll($query, Db::FETCH_ASSOC);
        foreach ($result as $_no => $r) {
            $page_ids[$r["id"]] = "";
        }

        #remove all links information:
        if (count($page_ids) > 0) {
            $tables = array(
                "links_external",
                "links_headers",
                "links_info",
                "links_internal",
                "links_social",
            );


            foreach ($tables as $t_no => $table) {
                #not needed this check:
                //if($table === "links") {
                //    $field_name = "id";
                //} else {
                $field_name = "link_id";
                //}
                $query = "DELETE FROM " . $_prefix . $table . " WHERE " . $field_name . " in (";
                foreach ($page_ids as $p_id => $null) {
                    $query .= $p_id . ",";
                }
                $query = substr($query, 0, strrpos($query, ","));
                $query .= ")";
                $this->db->execute($query);
            }

            //time to remove links:
            $query = "DELETE FROM " . $_prefix . "links WHERE domain_id=" . $id;
            $this->db->execute($query);
        }

        echo json_encode(array("msg" => "success"));
    }
}
