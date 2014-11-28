<?php
/**
 *This class is used to fetch users collection from database.
 */
use \Phalcon\Mvc\Model;

class Administration extends Model
{
    public function getSource()
    {
        return "users";
    }
}