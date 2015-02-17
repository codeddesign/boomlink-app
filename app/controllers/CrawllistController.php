<?php
// controls for crawllist page
use \Phalcon\Mvc\View;

class CrawllistController extends BaseController
{
    /**
     *This function is used to initialize view for crawllist page.
     */
    public function initialize()
    {
        $this->view->setVar("page", "crawllist");
    }

    public function getSource()
    {
        return "status_domain";
    }

    /**
     *This function is used to set the variables to show the crawled domains and their pages.
     */
    public function indexAction()
    {
        $domains = StatusDomain::find();// Get all domain url from the main table
        //$domains = StatusDomain::find();// Get all domain url from the main table
        $domain_details = array();
        foreach ($domains as $value) {
            $sub_urls = array();
            $sub_urls = $this->getSubUrls($value->DomainURLIDX);
            $domain_details[] = array(
                'main_url' => $value->DomainURL,
                'suburls' => $sub_urls,
                'Status' => $value->Status,
                'DomainURLIDX' => $value->DomainURLIDX,
                'page_limit' => (@$value->page_limit) ? $value->page_limit : 3,
                'total_suburls' => PageMainInfo::count("DomainURLIDX = '".$value->DomainURLIDX."'"),
                'total_suburls_crawled' => PageMainInfo::count("DomainURLIDX = '".$value->DomainURLIDX."' and parsed_status='1'"),
            );
        }

        $this->view->setVar("domain_details", $domain_details);
    }

    /**
     *This function is used to get the crawled page urls of doains.
     */
    function getSubUrls($DomainURLIDX)
    {
        $domains = PageMainInfo::find("DomainURLIDX = '$DomainURLIDX' LIMIT 100");
        $domain_details = array();
        foreach ($domains as $value) {
            $domain_details[] = array(
                'PageURL' => $value->PageURL,
                'link_limit' => (@$value->link_limit != '') ? @$value->link_limit : 1,
                '_id' => $value->id
            );
        }
        return $domain_details;
    }

    /**
     *This function is used to set the link limit for the crawled pages.
     */
    function setLimitsAction($value, $id)
    {
        $update_link = PageMainInfo::findFirst("id = '$id'");
        $update_link->link_limit = (int)$value;
        $update_link->save();
        exit;
    }

    /**
     *This function is used to delete the selected crawled domain and its pages.
     */
    function delPagesAction($DomainURLIDX, $MainURL)
    {
        $this->view->disable();
        $request = $this->request;
        if ($request->isPost()) {
            $statu_domain_obj = new StatusDomain();
            $statu_domain_obj->delRecords($DomainURLIDX, "DomainURLIDX");

            $page_main_info_obj = new PageMainInfo();
            $page_main_info_obj->delRecords($DomainURLIDX, "DomainURLIDX");

            $MainURL_VAR = "http://" . $MainURL;
            $domain_to_crawl_obj = new DomainsToCrawl();
            $domain_to_crawl_obj->delRecords($MainURL_VAR, "DomainURL");

            $domain_to_crawl_obj = new DomainsToCrawl();
            $domain_to_crawl_obj->delRecords($MainURL, "DomainURL");
        }
    }

    /**
     *This function is used to generate csv of crawled domains list.
     */
    function export_csvAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            $domains = StatusDomain::find(); // Get all domain url from the main table
            $domain_details = array();
            foreach ($domains as $value) {
                $domain_details[] = array(
                    'main_url' => $value->DomainURL,
                    'suburls' => $this->getSubUrls($value->DomainURLIDX),
                    'DomainURLIDX' => $value->DomainURLIDX
                );
            }
            $send_dom = array();
            $send_dom[0][0] = 'CRAWLED SITES';
            $send_dom[1][0] = 'Website Address';
            $send_dom[1][1] = 'Page URL';
            $send_dom[1][2] = 'Link Limit';
            $j = 2;
            foreach ($domain_details as $d) {
                $send_dom[$j][0] = @$d['main_url'];
                $k = 0;
                foreach ($d['suburls'] as $sub) {
                    if ($k > 0)
                        $send_dom[$j][0] = '';
                    $send_dom[$j][1] = $sub['PageURL'];
                    $send_dom[$j][2] = $sub['link_limit'];
                    $j++;
                    $k++;
                }
                $j++;
                $send_dom[$j][0] = '';
                $send_dom[$j][1] = '';
                $send_dom[$j][2] = '';
                $j++;
            }
            $this->array_to_csv_download($send_dom, 'export_crawllist.csv');
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
}