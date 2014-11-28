<?php
use \Phalcon\Mvc\Model\Validator\Uniqueness;
use \Phalcon\Mvc\Model\Validator\PresenceOf;
use \Phalcon\Mvc\Model;

/**
 *This class is used to fetch master_campaign collection from database.
 */
class MasterCampaign extends Model
{
    public function getSource()
    {
        return "master_campaign";
    }

    /**
     *This function is used to validate Campaign Name.
     */
    public function validation()
    {
        $this->validate(new PresenceOf(
            array(
                'field' => 'Name',
                'message' => 'The Campaign Name is required'
            )
        ));

        return $this->validationHasFailed() != true;
    }

}