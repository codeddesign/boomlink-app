<?php
use \Phalcon\Mvc\Model;

class PagesToCampaign extends Model
{
    public function getSource()
    {
        return "pages_to_campaign";
    }
}