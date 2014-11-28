<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
<title>Site Rankings Cron</title>
<link href="css/style.css" rel="stylesheet" type="text/css">

</head>
<body>

<?php 
// checking keyword rankings for analytics page
include("../PHPGoogleKeywordPosition/RankChecker.class.php");
include("../GooglePR/GooglePR.php");   
$gpr = new GooglePR(); 
$newGoogleRankChecker = new GoogleRankChecker();
$useproxies = FALSE;
$arrayproxies = array();
?>

<?php
// connection to database
$mongo = new Mongo("mongodb://95.85.63.246:27017");
$db = $mongo->selectDb("site_analysis");

// checking both page and domain rankings
$keyword_rankings = $db->keyword_rankings->find(array("Date" => array('$gte' => new MongoDate(strtotime(date('m/d/Y'))))))->count();
$domain_rankings = $db->domain_rankings->find(array("Date" => array('$gte' => new MongoDate(strtotime(date('m/d/Y'))))))->count();

if($keyword_rankings==0 && $domain_rankings==0)
{
	// locating all domains within active campaigns
	$domains = $db->status_domain->find();
	
	//Insert Domain Rankings
	foreach(@$domains as $d)
	{
		$pagerank = $gpr->getPagerank($d['DomainURL']);
		if($pagerank!='')
			$pagerank = (int)$pagerank;
		else
			$pagerank = 'NA';
		$db->domain_rankings->insert(array("DomainURLIDX" => (int)$d['DomainURLIDX'],"DomainURL" => $d['DomainURL'],"Rank" => $pagerank,"Date" => new MongoDate(strtotime(date('m/d/Y h:i:s A')))));
		
	}
	
	
	//Insert Domain And Keyword Rankings For Campaigns
	$end_date = date('m/d/Y h:i:s A',strtotime(date("Y-m-d")));
	$end = new MongoDate(strtotime($end_date));
	$get_campaigns = $db->master_campaign->find(array('EndDate' => array('$gte' => $end)));
	foreach(@$get_campaigns as $camp)
	{
		$domain_id=0; $domain_url='';
		$links = $db->pages_to_campaign->find(array('unique_id'=>$camp['unique_id']));
		$i=1;
		foreach(@$links as $link)
		{
			$domain_id = $link['DomainURLIDX']; $domain_url = $link['main_url'];
			$get_domain = $db->status_domain->find(array("DomainURLIDX" => (int)$link['DomainURLIDX']))->limit(1);
			foreach(@$get_domain as $d)
			{
				$domain_id = $d['DomainURLIDX'];
				$domain_url = $d['DomainURL'];
			}
			
			$pagerank = $gpr->getPagerank($domain_url);
			if($pagerank!='')
				$pagerank = (int)$pagerank;
			else
				$pagerank = 'NA';
			$db->domain_rankings->insert(array("DomainURLIDX" => (int)$domain_id,"DomainURL" => $domain_url,"Rank" => $pagerank,"Date" => new MongoDate(strtotime(date('m/d/Y h:i:s A')))));
			if(@$link['keywords_for_analytics']!='')
			{
				$newquery = $link['keywords_for_analytics'];
				$googledata = $newGoogleRankChecker->find($newquery, $useproxies, $arrayproxies);
				if($array_key = array_search($domain_url,$googledata))
				{
					$rank = (int)$googledata[$array_key]['rank'];
				}
				else
					$rank = 'NA';
				
				$db->keyword_rankings->insert(array("DomainURLIDX" => (int)$domain_id,"DomainURL" => $domain_url,"PageURL" => $link['main_url'],"Keyword" => $link['keywords_for_analytics'],"Rank" => $rank,"Date" => new MongoDate(strtotime(date('m/d/Y h:i:s A')))));
			}
			$i++;
		}
	}
}
else
	echo 'Script is already runned today.';

?>

</body>
</html>