<?php
error_reporting(E_ALL);
include 'config.php';
// Cron to check for connection on manual campaigns
$result = array();
// process cron job for domains with status 1 (completed crawl domains)
$query = "SELECT * FROM lists WHERE cron_processed = 0";
$result = mysql_query($query) or die(mysql_error());
$result = mysql_fetch_array($result);
//$list_table= $collection->count(array('cron_processed' =>array('$ne' => 1)));

// check if page has an active connection
if(!empty($result))
{
    $result = mysql_query($query) or die(mysql_error());
    //$list_array = $collection->find(array('cron_processed' =>array('$ne' => 1)));
    while ($list = mysql_fetch_array($result))
    {
            $output = "";
            $array_connected = array();
            $list_urls = explode(",", $list['ListURL']);
            foreach ($list_urls as $url) 
            {
                // using current curl embedded script to check connection
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                $output = curl_exec($curl);
                curl_close($curl);
                $DOM = new DOMDocument();
                libxml_use_internal_errors(true);
                $DOM->loadHTML( $output);
                //get all H1
                $items = $DOM->getElementsByTagName('articleconf');
                if(@$items->length)
                {
                    $array_connected[]=$url;
                    echo $url." = Connected<br/>";
                }
                else
                {
                        $original = $url;
                        $parsed = parse_url($url);
                        if(strpos($url,'www') == false)
                        {
               
                                $url = $parsed['scheme']."://www.". $parsed['host'].@$parsed['path'];
                                $curl = curl_init($url);
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                                $output = curl_exec($curl);
                                curl_close($curl);
                                
                                $DOM = new DOMDocument();
                                libxml_use_internal_errors(true);
                                $DOM->loadHTML( $output);
                                //get all H1
                                $items = $DOM->getElementsByTagName('articleconf');
                                if(@$items->length)
                                {
                                    $array_connected[]=$original;
                                    echo $url." = Connected<br/>";
                                    continue;
                                }
                                else
                                {
                                    $url = $parsed['scheme']."://www.". $parsed['host'].@$parsed['path']."/";
                                    $curl = curl_init($url);
                                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                                    $output = curl_exec($curl);
                                    curl_close($curl);

                                    $DOM = new DOMDocument();
                                    libxml_use_internal_errors(true);
                                    $DOM->loadHTML( $output);
                                    //get all H1
                                    $items = $DOM->getElementsByTagName('articleconf');
                                    if(@$items->length)
                                    {
                                        $array_connected[]=$original;
                                        echo $url." = Connected<br/>";
                                    }
                                    else
                                    {
                                        echo $url." =Disconnected<br/>";
                                    } 
                                }
                                
                        }
                }
            }
                //$collection->update(array("_id" => $list['_id']),array('$set' => array('cron_processed' => 1, 'pages_connected'=>$array_connected)));
                $array_connected = implode(",", $array_connected);
                $query = "UPDATE  lists SET cron_processed = '1', pages_connected = '$array_connected' WHERE id ='".$list['id']."'";
                mysql_query($query) or die(mysql_error());

    }

}
else
{
    echo "Restarted";
    //$collection->update(array("cron_processed" => 1),array('$set' => array('cron_processed' => 0)),array("multiple" => true));
    $query = "UPDATE lists SET cron_processed = '0'";
    mysql_query($query) or die(mysql_error());
}

    
