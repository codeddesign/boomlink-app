<?php
use \Phalcon\Mvc\View;

/**
 *This class is used to provide appserver interface.
 */
class AppserverController extends BaseController
{

    public function initialize()
    {

    }

    /**
     *This function is used to use achor text, image and video urls of campaigns.
     */
    public function indexAction()
    {

        if (isset( $_GET['brand'] ) && $_GET['brand']) {
            $brand = $this->addhttp( $_GET['brand'] );

            if (isset( $_GET['path'] ) && $_GET['path']) {
                $path = $_GET['path'];
            } else {
                $path = "/";
            }

            if ($path[0] == '/') {
                $htttp_base_url_www = $this->get_domain( $brand ) . $_GET['path'];
            } else {
                $htttp_base_url_www = $this->get_domain( $brand ) . "/" . $_GET['path'];
            }

            $url_split = parse_url( $htttp_base_url_www );

            $this->handleNewLink( $htttp_base_url_www );

            $htttp_base_url = $url_split['scheme'] . "://" . preg_replace( '#^www\.(.+\.)#i', '$1', $url_split['host'] ) . $path;
            $this->printData( $htttp_base_url_www, $htttp_base_url, "ESI" );
        } else {
            $protocol = ( isset( $_REQUEST['protocol'] ) ) ? $_REQUEST['protocol'] : $_POST['protocol'];
            $host     = ( isset( $_REQUEST['host'] ) ) ? $_REQUEST['host'] : $_POST['host'];
            $req_uri  = ( isset( $_REQUEST['REQUEST_URI'] ) ) ? $_REQUEST['REQUEST_URI'] : $_SERVER['REQUEST_URI'];

            $htttp_base_url = $protocol . $host . $req_uri;
            if (strpos( $htttp_base_url, 'www-' ) != false) {
            } else {
                $htttp_base_url = $protocol . "www." . $host . $req_uri;
            }
        }

        $htttp_base_url     = ( substr( $htttp_base_url, - 1 ) == '/' ) ? substr( $htttp_base_url, 0, - 1 ) : $htttp_base_url;
        $htttp_base_url_www = $htttp_base_url;

        // Remove www and add http
        //$htttp_base_url = $_REQUEST['protocol'].$_REQUEST['host'].$_REQUEST['REQUEST_URI'];
        $parse = parse_url( $htttp_base_url );

        $htttp_base_url = $parse['scheme'] . "://" . preg_replace( '#^www\.(.+\.)#i', '$1', $parse['host'] ) . $parse['path'];

        $htttp_base_url = ( substr( $htttp_base_url, - 1 ) == '/' ) ? substr( $htttp_base_url, 0, - 1 ) : $htttp_base_url;
        //$htttp_base_url = $parse['scheme']."://".preg_replace('#^www\.(.+\.)#i', '$1', $parse['host']) . $parse['path'];
        /*
        echo $htttp_base_url.'<br/>';
        $parsed = parse_url($htttp_base_url);

        if (isset($parsed['scheme']) && $parsed['scheme']) 
        {
            echo $parsed['scheme'];
            $htttp_base_url = $parse['scheme']."://".$htttp_base_url;
        }
        else
        {
            $htttp_base_url = $_REQUEST['protocol'].$_REQUEST['host'].$_REQUEST['REQUEST_URI'];
            $htttp_base_url = (substr($htttp_base_url, -1) == '/') ? substr($htttp_base_url, 0, -1) : $htttp_base_url;
            $htttp_base_url = preg_replace('#^www\.(.+\.)#i', '$1', $parse['host']) . $parse['path'];
        }*/
        $this->printData( $htttp_base_url_www, $htttp_base_url );
    }


    public function get_domain( $url )
    {
        $pieces = parse_url( $url );
        $domain = isset( $pieces['host'] ) ? $pieces['host'] : '';
        if (preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs )) {
            if (stripos( $url, 'www.' ) === false) {
                return $pieces['scheme'] . "://www." . $regs['domain'];
            }

            return $pieces['scheme'] . "://" . $regs['domain'];
        }

        return false;
    }


    public function printData( $htttp_base_url_www, $htttp_base_url, $action = "normal" )
    {
        $htttp_base_url     = stripslashes( $htttp_base_url );
        $htttp_base_url_www = stripslashes( $htttp_base_url_www );
        $urls               = "'$htttp_base_url_www', '$htttp_base_url'";

        $date         = date( 'Y-m-d' );
        $campaing_url = PagesToCampaign::find( "main_url IN($urls) AND EndDate >= '$date' AND StartDate <= '$date'" );
        //echo "main_url IN($urls) AND EndDate >= '$date' AND StartDate >= '$date'";
        $achor_text          = "";
        $image_url           = "";
        $video_url           = "";
        $html_embed          = "";
        $html_embed_continer = "";
        if ($action == "ESI") {
            foreach ($campaing_url as $value) {
                if (trim( $value->achor_text ) != "") {
                    $html_embed .= '<li><a href="' . $value->campaign_url . '">' . $value->achor_text . '</a></li>';
                }
                if (trim( $value->image_url != "" )) {
                    $html_embed .= '<li><a href="' . $value->campaign_url . '"><img src="' . $value->image_url . '"/></a></li>';
                }
                if (trim( $value->video_url != "" )) {
                    $html_embed .= '<li><iframe width="560" height="315" src="//www.myvideo.de/embed/' . $value->video_url . '" frameborder="0" allowfullscreen></iframe></li>';
                }
                if (trim( $value->html_embed != "" )) {
                    $html_embed .= '<li>' . $value->html_embed . '</li>';
                }
            }

            if ($html_embed != "") {
                // Comment Thomas section class name and div class name changed!
                // removed the <article class="brand" Tag>
                $html_embed_continer = '<section class="box module_group psdcontainer clearfix has_header">
                                        <header class="module_header">
                                            <h2>Empfehlungen</h2>
                                        </header>
                      
                                    <div class="psdblock block-itemlist view-relatedlinks">
									
                                        
                                            <ul>';
                // Comment Thomas, if possible here an if else if <img= and give them a tag like
                // <figure class="teaser-figure"> </figure>
                // finally an images should be displayed an figure class tag : <figure class="teaser-figure"><img alt="Der letzte Bulle - Staffel 5" src="http://www.sat1.de_SAT_teaser_140x79.jpg" /></figure>
                $html_embed_continer .= $html_embed;
                $html_embed_continer .= '         </ul>
                                            
                                        </div>
                                    </section>';
            }

            echo $html_embed_continer;
        } else {
            foreach ($campaing_url as $value) {
                if (trim( $value->achor_text ) != "") {
                    $achor_text .= ( trim( $value->campaign_url != "" ) ) ? '<a href="' . $value->campaign_url . '">' . $value->achor_text . '</a><br/>' : "";
                }

                $image_url .= ( trim( $value->image_url != "" ) ) ? '<a href="' . $value->campaign_url . '"><img src="' . $value->image_url . '"/></a><br/>' : "";
                $video_url .= ( trim( $value->video_url != "" ) ) ? '<iframe width="560" height="315" src="//www.myvideo.de/embed/' . $value->video_url . '" frameborder="0" allowfullscreen></iframe>' : "";
                $html_embed .= @$value->html_embed;
            }

            echo $achor_text . $image_url . $video_url . $html_embed;
        }
        // Comment Thomas - If possible decline the tag??!?? if said I didn't understand what they whant exactly
        // ORIGINAL MESSAGE FROM THEM: remove the <articleconf> tag! If an identification is required, please write out your own CSS classes or check on the class {{view-related links}}, it is unique for this element.
        //  echo '<articleconf style="position:fixed;font-size:0;color:transparent;text-indent:-9999px;">1</articleconf>';
        exit;
    }

    function addhttp( $url )
    {
        if ( ! preg_match( "~^(?:f|ht)tps?://~i", $url )) {
            $url = "http://" . $url;
        }
        return $url;
    }

    private function handleNewLink( $htttp_base_url_www )
    {
        $linkInDb = PageMainInfo::find( 'PageURL = \'' . $htttp_base_url_www . '\'' )->toArray();
        if (count( $linkInDb )) {
            return false;
        }

        $domain  = self::getHost( $htttp_base_url_www );
        $domains = StatusDomain::find( 'domain_name = \'' . $domain . '\'' )->toArray();
        if ( ! count( $domains )) {
            return false;
        }

        $domain_id = $domains[0]['DomainURLIDX'];
        $parts     = parse_url( $htttp_base_url_www );
        if ( ! isset( $parts['path'] )) {
            return false;
        }

        $depth = count( explode( "/", $parts['path'] ) );
        $q = "INSERT INTO PageMainInfo (DomainURLIDX, PageURL, depth, external_links, internal_links, no_follow_links, h1,h2,h3,h4,h5,h6, http_code) VALUES('".$domain_id."', '".$htttp_base_url_www."', '".$depth."', 0,0,0,0,0,0,0,0,0,0,0)";

        $query = new Phalcon\Mvc\Model\Query($q, $this->getDI());
        $query->execute();
        return true;
    }

    public static function linkHasScheme( $link )
    {
        return ( strtolower( substr( $link, 0, 4 ) ) === 'http' );
    }

    public static function getHost( $url )
    {
        $url = trim( $url );

        // parse_url() won't work properly if 'http' is missing:
        if ( ! self::linkHasScheme( $url )) {
            $url = 'http://' . $url;
        }

        $parts = parse_url( $url );
        if (isset( $parts["host"] )) {
            return str_ireplace( "www.", "", $parts["host"] );
        }

        // this should never happen:
        return false;
    }

}