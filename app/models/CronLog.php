<?php
/**
 *This class is used to fetch cown_log collection from database.
 */

use \Phalcon\Mvc\Collection;

class CronLog extends Collection
{
    /**
     *This function is used to fetch cown_log collection from database which stores log details of crons.
     */
    public function getSource()
    {
        return "crown_log";
    }

}