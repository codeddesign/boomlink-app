<?php

use \Phalcon\Mvc\Model\Validator\PresenceOf;
use \Phalcon\Mvc\Model;

/**
 *This class is used to fetch lists collection from database.
 */
class Lists extends Model
{
    public function getSource()
    {
        return "lists";
    }

    /**
     *This function is used to validate Name of list.
     */
    public function validation()
    {
        $this->validate(new PresenceOf(
            array(
                'field' => 'Name',
                'message' => 'Please enter List Title.'
            )
        ));

        return $this->validationHasFailed() != true;
    }

    /**
     *This function is used to update lists collection by crons.
     */
    public function updateData()
    {
        $mongo = new Mongo("mongodb://95.85.63.246:27017");
        $db = $mongo->site_analysis;
        $collection = $db->lists;
        $collection->update(array("cron_processed" => 1), array('$set' => array('cron_processed' => 0)), array("multiple" => true));
    }
}
