<?php
class mainModel extends Database
{
    public $db;

    public function __construct()
    {
        $this->db=Database::getInstance();
    }
}