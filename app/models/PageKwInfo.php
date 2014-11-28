<?php
/**
 *This class is used to fetch page_kw_info collection from database.
 */
use \Phalcon\Mvc\Collection;

class PageKwInfo extends Collection
{
    /**
     *This function is used to fetch page_kw_info collection from database.
     */
    public function getSource()
    {
        return "page_kw_info";
    }

}