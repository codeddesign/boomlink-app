<?php
/**
 *This class is used to fetch search_result_log collection from database.
 */
use \Phalcon\Mvc\Collection;

class SearchResultLog extends Collection
{
    public function getSource()
    {
        return "search_result_log";
    }

}