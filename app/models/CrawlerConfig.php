<?php
use \Phalcon\Mvc\Model;

class CrawlerConfig extends Model
{
    public function getSource()
    {
        return 'crawler_config';
    }
}