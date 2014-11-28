<?php
use \Phalcon\Mvc\Model;
class KeywordRankings extends Model
{
    public function getSource()
    {
        return "keyword_rankings";
    }
}