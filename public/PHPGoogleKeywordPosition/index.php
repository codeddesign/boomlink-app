<?php
/**
 * Format array proxies:
 * array('host' => 'HOST', 'port' => 'PORT', 'username' => 'USERNAME', 'password' => 'PASSWORD');
 */
include("RankChecker.class.php");
$newGoogleRankChecker 	= new GoogleRankChecker();
$newquery 		= 'BeachTop';
$useproxies 		= FALSE;
$arrayproxies 		= array();
$googledata 		= $newGoogleRankChecker->find($newquery, $useproxies, $arrayproxies);
var_dump($googledata);
?>
