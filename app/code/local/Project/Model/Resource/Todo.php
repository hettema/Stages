<?php
/**
 * class Project_Model_Resource_Todo
 * 
 * @package Project
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Resource_Todo extends Core_Model_Resource_Abstract
{
    protected $tbl_main = 'stages_todo_entity';

    protected function  _construct()
    {
         $this->_init($this->tbl_main, 'todo_id');
    }
}

?>
