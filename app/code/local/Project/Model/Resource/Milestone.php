<?php
/**
 * class Project_Model_Resource_Milestone
 * 
 * @package Project
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Project_Model_Resource_Milestone extends Core_Model_Resource_Abstract
{
    protected $tbl_user = 'stages_milestone_entity';

    protected function  _construct()
    {
         $this->_init($this->tbl_user, 'milestone_id');
    }
}

?>
