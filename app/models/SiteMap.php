<?php

/**
 *This class is used to fetch track_visitors collection from database.
 */
use \Phalcon\Mvc\Model;

class SiteMap extends Model
{
    public function getSource()
    {
        return "site_xmlmap";
    }
}