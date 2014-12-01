<?php
use \Phalcon\Mvc\Model;

class CrawlerAgents extends Model
{
    public function getSource()
    {
        return "crawler_agents";
    }
}