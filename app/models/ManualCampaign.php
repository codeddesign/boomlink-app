<?php
use \Phalcon\Mvc\Model\Validator\Uniqueness;
use \Phalcon\Mvc\Model\Validator\PresenceOf;
use \Phalcon\Mvc\Collection;

/**
 *This class is used to fetch manual_campaign collection from database.
 */
class ManualCampaign extends Collection
{
    public function getSource()
    {
        return "manual_campaign";
    }

    /**
     *This function is used to validate campaign name and page URLs.
     */
    public function validation()
    {
        $this->validate(new PresenceOf(
            array(
                'field' => 'Name',
                'message' => 'The Campaign Name is required'
            )
        ));
        $this->validate(new PresenceOf(
            array(
                'field' => 'PageURL',
                'message' => 'Please select Page URLs'
            )
        ));

        return $this->validationHasFailed() != true;
    }

}