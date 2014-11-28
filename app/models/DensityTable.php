<?php
use \Phalcon\Mvc\Model\Validator\Uniqueness;
use \Phalcon\Mvc\Model\Validator\PresenceOf;
use \Phalcon\Mvc\Model;

/**
 *This class is used to fetch master_campaign collection from database.
 */
class DensityTable extends Model
{
    public function getSource()
    {
        return "page_main_info_body";
    }
}