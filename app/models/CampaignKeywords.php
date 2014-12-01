<?php
use \Phalcon\Mvc\Collection;

/**
 *This class is used to fetch campaign_keywords collection from database.
 */
class CampaignKeywords extends Collection
{
    /**
     *This function is used to fetch campaign_keywords collection from database.
     */
    public function getSource()
    {
        return "campaign_keywords";
    }
}