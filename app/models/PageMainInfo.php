<?php
/**
 *This class is used to fetch page_main_info collection from database.
 */
use \Phalcon\Mvc\Model;

class PageMainInfo extends Model
{
    public function getSource()
    {
        return "page_main_info";
    }

    public function delRecords($id, $field)
    {
        $query = new Phalcon\Mvc\Model\Query("DELETE FROM PageMainInfo WHERE $field = '$id'", $this->getDI());
        $query->execute();
    }

}