<?php
/**
 *This class is used to fetch domains_to_crawl collection from database.
 */
use \Phalcon\Mvc\Model;

class DomainsToCrawl extends Model
{
    public function delRecords($id, $field)
    {
        echo "DELETE FROM DomainsToCrawl WHERE $field = '$id'";
        $query = new Phalcon\Mvc\Model\Query("DELETE FROM DomainsToCrawl WHERE $field = '$id'", $this->getDI());
        $query->execute();
    }
}