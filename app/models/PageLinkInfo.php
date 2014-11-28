<?php
/**
 *This class is used to fetch page_link_info collection from database.
 */
use \Phalcon\Mvc\Model;

class PageLinkInfo extends Model
{
    /**
     *This method is used to fetch page_link_info collection from database.
     */
    public function getSource()
    {
        return "page_link_info";
    }

}