<?php
/**
 * class Cron_Model_Resource_Cron
 * 
 * @package Cron
 * @category Resource-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Cron_Model_Resource_Cron extends Core_Model_Resource_Abstract
{
    protected $tbl_cron_job = 'cron_job_entity';
    protected $tbl_cron_job_schedule = 'cron_job_schedule';

    protected function  _construct()
    {
        $this->_init($this->tbl_cron_job, 'job_id');
    }
    
    /**
     * Get the cron jobs from databse
     * 
     * @param array $filters
     * @return array 
     */
    public function getJobs($filters = array())
    {
        $query = $this->_getQuery();
        $query->resetQuery();
        $filters['status'] = !isset($filters['status']) ? 1 : $filters['status'];
        
        $query->queryColumn(array("tbl_job.*",
                                   /*"tbl_job_schedule.job_code",
                                   "tbl_job_schedule.status",
                                   "tbl_job_schedule.messages",
                                   "tbl_job_schedule.created_at",
                                   "tbl_job_schedule.scheduled_at",
                                   "tbl_job_schedule.executed_at",
                                   "tbl_job_schedule.finished_at"*/
                                   ));
        foreach ($filters as $filter=>$value) {
            if(empty($value)) continue;

            switch ($filter)
            {
                case 'name':
                    $query->queryCondition("tbl_job.job_name LIKE '%". $value ."%'");
                break;

                case 'status':
                    $query->queryCondition("tbl_job.status =". $value);
                break;

                case 'is_scheduled':
                    $query->queryCondition("tbl_job_schedule.scheduled_at > '". date('Y-m-d H:i:s', time() - 10) ."'");//keeps a 10s escape time
                    $query->queryCondition("tbl_job_schedule.status ='". Cron_Model_Cron::STATUS_PENDING ."'");
                break;

                case 'is_schedule_active':
                    $query->queryCondition("tbl_job_schedule.scheduled_at > '". now() ."'");
                    $query->queryCondition("(tbl_job_schedule.status ='". Cron_Model_Cron::STATUS_PENDING ."' || tbl_job_schedule.status ='". Cron_Model_Cron::STATUS_RUNNING ."')");
                break;

                case 'is_finished':
                    $query->queryCondition("tbl_job_schedule.scheduled_at < '". now() ."'");
                    $query->queryCondition("tbl_job_schedule.status ='". Cron_Model_Cron::STATUS_SUCCESS ."'");
                break;

                case 'is_running':
                    $query->queryCondition("tbl_job_schedule.status ='". Cron_Model_Cron::STATUS_RUNNING ."'");
                break;

                case 'is_error':
                    $query->queryCondition("tbl_job_schedule.status ='". Cron_Model_Cron::STATUS_ERROR ."'");
                break;
            }
        }
        $query->queryCondition("tbl_job.job_id","GROUP");
        $query->queryCondition("tbl_job_schedule.scheduled_at ASC","ORDER");
        $query->queryTable($this->tbl_cron_job ." AS tbl_job");
        $query->queryTable("LEFT JOIN ". $this->tbl_cron_job_schedule ." AS tbl_job_schedule ON tbl_job_schedule.job_id = tbl_job.job_id");

        $results = $this->_getReadAdapter()->fetchAll($query->prepareQuery());
        $colection = array();
        if(empty($results)) { return $colection; }

        foreach($results as $result) {
            $result['schedules'] = $this->_getReadAdapter()->fetchAll("SELECT schedule_id, job_code, status, messages, created_at, scheduled_at, executed_at, finished_at FROM ". $this->tbl_cron_job_schedule ." WHERE job_id=". $result['job_id'] . " AND scheduled_at >= '". now() ."' ORDER BY scheduled_at ASC");
            $colection[] = App_Main::getModel('cron/job', $result);
        }
        return $colection;
    }

    /**
     * Get the scheduled cronjobs
     * 
     * @return array 
     */
    public function getScheduledCronJobs()
    {
        return $this->getJobs(array('is_schedule_active'=>true));
    }

    /**
     * Get the cron jobs which are finished
     * 
     * @return type 
     */
    public function getFinishedJobs()
    {
        return $this->getJobs(array('is_finished'=>true));
    }

    /**
     * Schedule a cronjob to run at a specified time
     * 
     * @param int $jobId
     * @param string $jobCode
     * @param int $status
     * @param string $scheduledAt 
     */
    public function scheduleJob($jobId, $jobCode, $status, $scheduledAt)
    {
        $data = array();
        $data['job_id'] = $jobId;
        $data['job_code'] = $this->_prepareValueForSave($jobCode);
        $data['status'] = $this->_prepareValueForSave($status);
        $data['created_at'] = $this->_prepareValueForSave(now());
        $data['scheduled_at'] = $this->_prepareValueForSave($scheduledAt);
        $this->_getWriteAdapter()->insert($this->tbl_cron_job_schedule, $data);
    }

    /**
     * Update the status of a scheduled job
     * 
     * @param int $scheduleId
     * @param int $status
     * @param int $jobId
     * @param string $message
     * @return Cron_Model_Resource_Cron 
     */
    public function updateJobStatus($scheduleId, $status, $jobId = null, $message = null)
    {
        switch ($status)
        {
            case Cron_Model_Cron::STATUS_SUCCESS:
                $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_cron_job_schedule ." SET finished_at = '". now() ."', status='". Cron_Model_Cron::STATUS_SUCCESS ."' WHERE schedule_id = ". $scheduleId);

                $result = $this->_getReadAdapter()->fetchRow("SELECT job_id, executed_at FROM ". $this->tbl_cron_job_schedule ." WHERE schedule_id = ". $scheduleId);
                $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_cron_job ." SET lastrun_at = '". $result['executed_at'] ."' WHERE job_id=". $result['job_id']);
            break;
            case Cron_Model_Cron::STATUS_MISSED:
            break;
            case Cron_Model_Cron::STATUS_WAITING:
                $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_cron_job_schedule ." SET status='". Cron_Model_Cron::STATUS_WAITING ."' WHERE schedule_id = ". $scheduleId);
            break;
            case Cron_Model_Cron::STATUS_RUNNING:
                $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_cron_job_schedule ." SET executed_at = '". now() ."', status='". Cron_Model_Cron::STATUS_RUNNING ."' WHERE schedule_id = ". $scheduleId);
            break;
            case Cron_Model_Cron::STATUS_ERROR:
                $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_cron_job_schedule ." SET finished_at = '". now() ."', status='". Cron_Model_Cron::STATUS_ERROR ."', message=". $this->_prepareValueForSave($message) ." WHERE schedule_id = ". $scheduleId);
            break;                
        }
        return $this;
    }

    /**
     * Mark missed jobs if any in the databse
     * 
     * @param type $scheduleStartTime 
     */
    public function markMissedJobs($scheduleStartTime = false)
    {
        $this->_getWriteAdapter()->query("UPDATE ". $this->tbl_cron_job_schedule ." SET status='". Cron_Model_Cron::STATUS_MISSED ."' WHERE executed_at ='0000-00-00 00:00:00' AND scheduled_at > '". $scheduleStartTime ."' AND scheduled_at < '". now() ."'");
    }

    /**
     * Remove scheduled job by schedule id
     * 
     * @param type $scheduleId 
     */
    public function removeScheduledByScheduleId($scheduleId)
    {
        $this->_getWriteAdapter()->query("DELETE FROM ". $this->tbl_cron_job_schedule ." WHERE schedule_id = ". $scheduleId);
    }

    /**
     * Remove scheduled job by job id
     * 
     * @param string $scheduleId 
     */
    public function removeScheduledByJobId($jobId)
    {
        $this->_getWriteAdapter()->query("DELETE FROM ". $this->tbl_cron_job_schedule ." WHERE job_id = ". $jobId ." AND scheduled_at >= '". now() ."'");
    }
}

?>
