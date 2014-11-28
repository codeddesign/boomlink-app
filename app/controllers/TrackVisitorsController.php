<?php
use \Phalcon\Mvc\View;

/**
 *This class is used to visitors on domains.
 */
class TrackVisitorsController extends BaseController
{

    public function initialize()
    {
    }

    /**
     *This function is used to store the details of visiors into our database.
     */
    public function indexAction()
    {
        if (@$_SERVER['HTTP_REFERER'] != '' && @$_SERVER['REMOTE_ADDR'] != '') {
            $track_visitors = new TrackVisitors();
            $url = @$_SERVER['HTTP_REFERER'];
            $base_url_parts = parse_url($url);
            $base_url_with_www = $base_url_parts['scheme'] . "://www." . $base_url_parts['host'];
            $base_url = $base_url_parts['scheme'] . "://" . $base_url_parts['host'];
            $domain_id = StatusDomain::findFirst("DomainURL = '$base_url_with_www'");
            if (!$domain_id->id) {
                $domain_id = StatusDomain::findFirst("DomainURL = '$base_url'");
            }
            $track_visitors->ip = @$_SERVER['REMOTE_ADDR'];
            $track_visitors->URL = @$_SERVER['HTTP_REFERER'];
            $track_visitors->DomainURLIDX = @$domain_id->DomainURLIDX;
            $track_visitors->DateTime = date("Y-m-d H:s:i");
            $track_visitors->Date = date('Y-m-d');
            $track_visitors->save();
        } else {
            echo '<script type="text/javascript">location.href = "'.$this->app_link.'";</script>';
        }
    }

    public function showAction()
    {

    }

    /**
     *This function is used to replace the strings.
     */
    function str_insert($str, $search, $insert)
    {
        $index = strpos($str, $search);
        if ($index === false) {
            return $str;
        }
        return substr_replace($str, $search . $insert, $index, strlen($search));
    }

}