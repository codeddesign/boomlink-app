<?php
set_time_limit(0);
use \Phalcon\Mvc\View;
use \Phalcon\Db;

class ExportController extends BaseController
{
    public function initialize()
    {
        $this->view->disable();
    }

    public function indexAction()
    {

    }

    public function logAction()
    {
        $this->view->disable();

        $statement = 'SELECT * FROM log_action';
        $results = $this->db->fetchAll($statement, Db::FETCH_ASSOC);

        $i = 0;
        $remove_chars = array('"', "[", "]");
        foreach ($results as $entry) {
            $results[$i]["action_params"] = str_replace($remove_chars, '', $entry["action_params"]);
            $i++;
        }

        $this->array_to_csv_download($results, "action_log.csv", $delimiter = ",");
    }

    function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",")
    {
        // open raw memory as file so no temp files needed, you might run out of memory though
        $f = fopen('php://memory', 'w');
        fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF));

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

    public function pagelinksAction()
    {
        //echo "<pre>";

        if (!isset($_GET["page"]) or !isset($_GET["export"])) {
            exit("Nothing to do..");
        } else {
            $page_id = $_GET["page"];
            $export_type = $_GET["export"];

            if (!isset($_GET["which"])) {
                $which = false;
            } else {
                $which = $_GET["which"];

                //fallback case:
                if ($which !== "internal" and $which !== "external") {
                    $which = false;
                }
            }
        }

        $prefix = "_sitemap_";
        $query = "SELECT page_url FROM " . $prefix . "links WHERE id='" . $page_id . "'";
        $selected_link = $this->db->fetchOne($query, Db::FETCH_ASSOC);
        $selected_link = $selected_link["page_url"];

        $query = "SELECT external.* FROM " . $prefix . "links_external as external WHERE external.link_id='" . $page_id . "'";
        if (!$which or $which === "external") {
            $result["external"] = $this->db->fetchAll($query, Db::FETCH_ASSOC);
        }

        $query = "SELECT internal.* FROM " . $prefix . "links_internal as internal WHERE internal.link_id='" . $page_id . "'";
        if (!$which or $which === "internal") {
            $result["internal"] = $this->db->fetchAll($query, Db::FETCH_ASSOC);
        }

        //cleanup:
        foreach ($result as $link_type => $link_list) {
            foreach ($link_list as $link_no => $info) {
                unset($result[$link_type][$link_no]["id"]);
                unset($result[$link_type][$link_no]["link_id"]);

                //show only path:
                if ($link_type === "internal" && $export_type !== "csv") {
                    $parsed = parse_url($info["href"]);
                    if (!isset($parsed["path"])) {
                        $path = "/";
                    } else {
                        $path = $parsed["path"];
                    }

                    $result[$link_type][$link_no]["href"] = $path;
                }
            }
        }

        //show:
        switch ($export_type) {
            //developer only:
            case "test":
                echo "<pre>";
                print_r($result);
                break;
            case "html":
                $out = "";
                $added = 0;
                foreach ($result as $link_type => $link_list) {
                    $out .= "<table border='1' style='float: left;margin-left: 50px;'>";
                    $out .= "<tr><td colspan='2' style='text-align: center;font-weight: bold;'>" . strtoupper($link_type) . "</td></tr>";

                    foreach ($link_list as $link_no => $info) {
                        $out .= "<tr><td>" . $info["href_text"] . "</td><td>" . $info["href"] . "</td></tr>";
                        $added++;
                    }

                    $out .= "<tr><td colspan='2'><a href='".$this->page_link."/export/pagelinks?page=" . $page_id . "&export=csv&which=" . $link_type . "' class='dropdownbox' style='text-align: center;' id='show_btn'>EXPORT</a></td></tr>";
                    $out .= "</table>";
                }

                if ($added === 0) {
                    $out = "No information for this link.";
                }

                echo $out;
                break;
            case "csv":
                $csv = array();
                $k = 0;

                foreach ($result as $link_type => $link_list) {
                    foreach ($link_list as $link_no => $info) {
                        $csv[$k]["main"] = $selected_link;
                        $csv[$k]["type"] = strtoupper($link_type);
                        $csv[$k]["href_text"] = $info["href_text"];
                        $csv[$k]["href"] = $info["href"];
                        $k++;
                    }
                }

                $this->array_to_csv_download($csv, "page_data_" . $export_type . "_" . date("dmy") . ".csv", $delimiter = ",");
                break;
        }
    }

    public function singlePageAction($domain_id, $page_id, $type = "none")
    {
        //$type = "debug";
        $result = $this->DomainAction($domain_id, $page_id);

        if (!$result or count($result) < 1 or !isset($result["domain_name"]) or $result["domain_name"] === "") {
            exit("no info");
        }

        switch ($type) {
            case 'debug':
                echo "<pre>";
                print_r($result);
                break;
            case 'html':
                //prepare information:
                $parts = parse_url($result["page_url"]);
                $page_path = $parts["path"];

                $hosting_company = $result["hosting_company"];
                if (strlen($hosting_company) > 18) {
                    $hosting_company = substr($hosting_company, 0, 18) . "..";
                }

                $description = $result["description"];
                if (strlen($description) > 20) {
                    $description = substr($description, 0, 20) . "..";
                }

                $page_trackers = $result["page_trackers"];
                if (strlen($page_trackers) > 20) {
                    $page_trackers = substr($page_trackers, 0, 20) . "..";
                }

                //section title:
                $out = '<div class="listtitlebar">
                    <div class="pagetitlebar">
                        <div class="pagetitlebar-title">' . $result["page_url"] . '</div>
                        <div id="page-info-load" style="display: none;" class="loading"></div>
                    </div>
                </div>';

                //fields titles:
                $out .= '<div class="listtitlesubbar" style="height:45px;width: 3050px;">
                    <div class="analyticswebaddressbar" style="margin-left:69px;margin-right:2px;">DOMAIN</div>
                    <div class="analyticswebaddresstime" style="margin-left: 145px;">SERVER IP</div>
                    <div class="analyticswebaddresstwentyfour" style="margin-left: 50px;">LOCATION</div>
                    <div class="analyticswebaddressseven" style="margin-left: 30px;">HOSTING COMPANY</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 30px;">GOOGLE RANK</div>

                    <div class="analyticswebaddressthirty" style="margin-left: 60px;">DESCRIPTION</div>
                    <!--<div class="analyticswebaddressthirty" style="margin-left: 60px;">PAGE TYPE</div>-->
                    <div class="analyticswebaddressthirty" style="margin-left: 60px;">HTTP CODE</div> <!-- style="margin-left: 30px;"-->
                    <div class="analyticswebaddressthirty" style="margin-left: 40px;">CHARSET</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 30px;">LANGUAGE</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">SERVER CONFIG</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 30px;">CACHED</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 40px;">LOAD TIME</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">PAGE WEIGHT</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 30px;">INTERNAL LINKS</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">EXTERNAL LINKS</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 30px;">FOLLOW LINKS</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">NO FOLLOW LINKS</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 40px;">H1</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 50px;">H2</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 50px;">H3</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 50px;">H4</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 50px;">H5</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 50px;">H6</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 50px;">INDEXED GOOGLE</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 30px;">INDEXED BING</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 60px;">PAGE TRACKERS</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 40px;">GOOGLE+</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">TWITTER</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">FB SHARES</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">FB LIKES</div>
                    <div class="analyticswebaddressthirty" style="margin-left: 20px;">FB COMMENTS</div>
                </div>';

                //fields values:
                $out .= '<div class="resultslist">
    <div class="greennetwork" style="margin-left:20px;margin-top:12px;visibility: hidden;" title="Completed"></div>
    <div class="analyticslisturl" style="width: 160px;">' . $result["domain_name"] . '</div>
    <div class="analyticsrankspot" style="width: 110px;">' . $result["server_ip"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["server_location"] . '</div>
    <div class="analyticsrankspot" style="width: 140px;cursor: help;" title="' . $result["hosting_company"] . '">' . $hosting_company . '</div>
    <div class="analyticsrankspot" style="width: 70px;">' . $result["google_rank"] . '</div>
    <div class="analyticsrankspot" style="width: 160px;cursor: help;" title="' . $result["description"] . '">' . $description . '</div>';

                //$out .= '<div class="analyticsrankspot" style="width: 80px;">' . $result["page_type"] . '</div>';
                $out .= '<div class="analyticsrankspot" style="width: 80px;">' . $result["http_code"] . '</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["charset"] . '</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["content_language"] . '</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["server_config"] . '</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["cached"] . '</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["load_time"] . 's</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["page_weight"] . 'kb</div>
    <div class="analyticsrankspot" style="width: 100px;">' . $result["internal_links"] . '</div>
    <div class="analyticsrankspot" style="width: 100px;">' . $result["external_links"] . '</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["follow_links"] . '</div>
    <div class="analyticsrankspot" style="width: 110px;">' . $result["no_follow_links"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["h1"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["h2"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["h3"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["h4"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["h5"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["h6"] . '</div>
    <div class="analyticsrankspot" style="width: 110px;">' . $result["indexed_google"] . '</div>
    <div class="analyticsrankspot" style="width: 100px;">' . $result["indexed_bing"] . '</div>
    <div class="analyticsrankspot" style="width: 150px;cursor: help;" title="' . $result["page_trackers"] . '">' . $page_trackers . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["googlep"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["twitter"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["fb_shares"] . '</div>
    <div class="analyticsrankspot" style="width: 60px;">' . $result["fb_likes"] . '</div>
    <div class="analyticsrankspot" style="width: 80px;">' . $result["fb_comments"] . '</div>
</div>';
                echo $out;
                break;

        }
    }

    public function DomainAction($domain_id, $page_id = false)
    {
        if (!isset($domain_id)) {
            exit();
        }

        //for developer:
        $debug = false;

        $table_prefix = '_sitemap_';
        $tables_suffix = array(
            "domain_info",
            "links",
            "links_headers",
            // "links_hrefs", // - removed
            "links_info",
            "links_social",
        );

        $skip_tables = array(
            "domain_info",
            "links",
        );

        // get fields for all tables:
        $tables_fields = array();
        foreach ($tables_suffix as $t_no => $suffix) {
            $table_name = $table_prefix . $suffix;

            $query = "SHOW COLUMNS FROM " . $table_name;
            $results = $this->db->fetchAll($query, Db::FETCH_ASSOC);
            foreach ($results as $entry) {
                $tables_fields[$table_name][] = $entry["Field"];
            }
        }

        // get information of the domain:
        $table_name = $table_prefix . "domain_info";
        $query = "SELECT * FROM " . $table_name . " WHERE id=" . $domain_id;
        $domain_info = $this->db->fetchAll($query, Db::FETCH_ASSOC);
        $domain_info = $domain_info[0];
        $all_info["domain_info"] = $domain_info;

        // then get all links for DOMAIN ID selected:
        $table_name = $table_prefix . "links";
        $query = "SELECT * FROM " . $table_name . " WHERE domain_id=" . $domain_id . " AND parsed_status!=0";
        if ($page_id !== false) {
            $query .= " AND id='" . $page_id . "'";
        }

        $links = $this->db->fetchAll($query, Db::FETCH_ASSOC);
        $all_info["links"] = $links;

        // then buildup full info from all tables per link!
        foreach ($links as $entry) {
            $link_id = $entry["id"];

            foreach ($tables_suffix as $t_no => $suffix) {
                $table_name = $table_prefix . $suffix;

                if (!in_array($suffix, $skip_tables)) {
                    $query = "SELECT * FROM " . $table_name . " WHERE link_id=" . $link_id;
                    $info = $this->db->fetchAll($query, Db::FETCH_ASSOC);
                    if (count($info) > 0) {
                        $all_info[$suffix][] = $info[0];
                    }
                }
            }
        }

        $link_info = array();
        //merge links information from all tables:
        foreach ($all_info["links"] as $link_no => $info) {
            //init:
            $temp = $domain_info;
            $temp["page_type"] = ''; //this value is needed for the undefined fields which are referring to it;

            //building:
            $temp = array_merge($temp, $info);
            $temp = array_merge($temp, $all_info["links_headers"][$link_no]);
            $temp = array_merge($temp, $all_info["links_info"][$link_no]);
            $temp = array_merge($temp, $all_info["links_social"][$link_no]);

            //save
            $link_info[$link_no] = $temp;
        }

        //field title => field name from db
        //the field names that are not already set need to have "?" as a value!
        $csv_fields = Array(
            "domain" => "domain_name",
            "page" => "page_url",
            "description" => "description",
            "internal links" => "internal_links",
            "external links" => "external_links",
            "follow links" => "follow_links",
            "No-follow links" => "no_follow_links",
            "h1" => "h1",
            "h2" => "h2",
            "h3" => "h3",
            "h4" => "h4",
            "h5" => "h5",
            "h6" => "h6",
            "indexed Google?" => "indexed_google",
            "indexed Bing?" => "indexed_bing",
            "Load Time" => "load_time",
            "Page Size" => "page_weight",
            "HTTP Code" => "http_code",
            "Charset" => "charset",
            "Page Trackers" => "page_trackers",
            "Pagerank" => "google_rank",
            "Server IP" => "server_ip",
            "Server Location" => "server_location",
            "Hosting Company" => "hosting_company",
            "Cache" => "cached",
            "Content Language" => "content_language",
            "Page Type" => "page_type", // to discuss - this has to be renamed;
            "Server configuration" => "server_config",
            "Google Plus" => "googlep",
            "Facebook Shares" => "fb_shares",
            "Facebook Likes" => "fb_likes",
            "Facebook Comments" => "fb_comments",
            "Twitter Shares" => "twitter",
        );


        foreach ($csv_fields as $c_title => $f_name) {
            foreach ($link_info as $link_no => $arr) {
                foreach ($arr as $arr_key => $value) {
                    if ($arr_key === $f_name) {

                        if ($f_name === "indexed_google" or $f_name === "indexed_bing" or $f_name === "cached") {
                            $value = ($value === "0") ? "false" : "true";
                        }

                        if ($f_name === "page_trackers") {
                            $replace = array("{", "}", '"');
                            $value = str_replace($replace, "", $value);
                            $value = str_replace(":", "=", $value);
                            $value = str_replace(",", ";", $value);
                        }

                        $value = str_replace('"', '\'', $value);
                        $rows[$link_no][$c_title] = $value;
                    }
                }
            }
        }

        //make a default set of empty values in case nothing set "yet":
        if (!isset($rows[0]) and $page_id !== false) {
            $link = 0;
            foreach ($csv_fields as $c_title => $f_name) {
                $rows[$link][$c_title] = "";
            }
        }

        if (isset($rows[0]) and $page_id !== false) {
            foreach ($rows[0] as $r_key => $value) {
                $f_name = $csv_fields[$r_key];
                $new_rows[$f_name] = $value;
            }

            return $new_rows;
        }

        if ($debug) {
            ECHO "<PRE>";
            print_r($rows);
            exit();
        }

        $file_name = "report_" . $domain_info["domain_name"] . ".csv";
        $this->array_to_csv_download($rows, $file_name, $delimiter = ",");
    }

    public function wordsAction($history_id, $type = "csv")
    {
        if (!isset($history_id)) {
            exit();
        }

        //get history information:
        $query = "SELECT * FROM _sitemap_user_history WHERE id='" . $history_id . "'";
        $result = $this->db->fetchOne($query, Db::FETCH_ASSOC);

        $domain_id = $result["domain_id"];
        $title = $result["entry_name"];
        $density_arr = json_decode($result["density_result"], true);

        //get domain name:
        $query = "SELECT domain_name FROM _sitemap_domain_info WHERE id='" . $domain_id . "'";
        $result = $this->db->fetchOne($query, Db::FETCH_ASSOC);
        $domain_name = $result["domain_name"];

        $body = "
        <center><table border='1' style='font-size: 12px;'>
            <tr><td colspan='3' style='text-align: center;'><b>Results on domain:</b> " . $domain_name . "</td></tr>
            <tr style='font-weight: bold;'><td width='100px;'>WORD</td><td width='100px;'>PERCENTAGE</td><td>LINK</td></tr>
        ";
        foreach ($density_arr as $word => $r) {
            if (is_array($r)) {
                foreach ($r as $r_no => $info) {
                    if ($word === "") {
                        $border = " style='background-color: #D2D2D2;'";
                    } else {
                        $border = '';
                    }

                    $body .= "<tr><td" . $border . ">" . $word . "</td><td>" . $info["percentage"] . "</td><td>" . $info["page_url"] . "</td></tr>";
                    $word = "";
                }
            } else {
                $body .= "<tr><td>" . $word . "</td><td colspan='2'>No results found.</td>";
            }
        }

        $body .= "</table></center>";

        //out:
        if ($type === "html") {
            echo json_encode(array("history_id" => $history_id, "title" => $title, "body" => $body,));
        }

        if ($type === "csv") {
            $k = 0;
            foreach ($density_arr as $word => $r) {
                if (is_array($r)) {
                    foreach ($r as $r_no => $info) {
                        $csv[$k]["word"] = $word;
                        $csv[$k]["percentage"] = $info["percentage"];
                        $csv[$k]["page_url"] = $info["page_url"];

                        $k++;
                    }
                }
            }

            $this->array_to_csv_download($csv, "density_" . date("dmy") . ".csv", $delimiter = ",");
        }
    }
}
