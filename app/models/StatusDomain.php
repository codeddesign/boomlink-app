<?php
use \Phalcon\Mvc\Model;

/**
 *This class is used to fetch status_domain collection from database.
 */
class StatusDomain extends Model
{
    /* @returns: object;
     * @info: used in administration.phtml
     */

    public function getDomainsList()
    {
        $query = new Phalcon\Mvc\Model\Query("SELECT DomainURLIDX as idx, DomainURL as url FROM StatusDomain", $this->getDI());
        $domains = $query->execute();
        if (is_array($domains)) {
            $domains = array();
        }

        return $domains;
    }

    function getCrawledLists()
    {
        $query = new Phalcon\Mvc\Model\Query("SELECT DomainURL FROM StatusDomain", $this->getDI());

        // Execute the query returning a result if any
        $cars = $query->execute();
        echo count($cars);
        foreach ($cars as $key => $value) {
            echo $value->DomainURL;
        }
        exit;

    }

    public function delRecords($id, $field)
    {
        $query = new Phalcon\Mvc\Model\Query("DELETE FROM StatusDomain WHERE $field = '$id'", $this->getDI());
        $query->execute();
    }

}