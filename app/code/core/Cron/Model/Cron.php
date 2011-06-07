<?php
/**
 * class Cron_Model_Cron
 * 
 * @package Cron
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Cron_Model_Cron extends Core_Model_Abstract
{
    const SCHEDULE_AHEAD_FOR = 60;
    const MAX_RUN_INTERVAL = 1200; //20 mins
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_WAITING = 'waiting';
    const STATUS_SUCCESS = 'success';
    const STATUS_MISSED = 'missed';
    const STATUS_ERROR = 'error';

    protected $_jobs = array();
    protected $_scheduledJobs = array();
    
    protected $_cronJobsConfigSource = 'db';

    protected function _construct()
    {
        $this->_init('cron/cron');
    }

    /**
     * Init all cron jobs from database
     */
    public function initCronJobs()
    {
        $this->_scheduleCronJobs();
    }

    /**
     * Schedule the cron jobs which are configured to run with in the current time and SCHEDULE_AHEAD_FOR interval
     * 
     */
    protected function _scheduleCronJobs()
    {
        $jobs = $this->getJobs();
        
        foreach($jobs as $job) {
            if($this->_isJobScheduled($job->getJobId())) {
                continue;
            }
            $job->setCronExpr($job->getCronExprString());
            $now = strtotime(date('Y-m-d H:i')); //Round seconds to minute..
            $timeAhead = $now + (self::SCHEDULE_AHEAD_FOR * 60);
            for ($time = $now; $time < $timeAhead; $time += 60) {
                
                if($job->trySchedule($time)) {
                    $this->getResource()->scheduleJob($job->getJobId(), $job->getJobName() .'/'. $time, self::STATUS_PENDING, date('Y-m-d H:i:s', $time));
                }
            }
        }
    }

    /**
     * Check whether the cron job is scheduled
     * 
     * @param type $jobId
     * @return type 
     */
    protected function _isJobScheduled($jobId)
    {
        if(!$this->_scheduledJobs) {
            $this->_scheduledJobs = $this->getResource()->getScheduledCronJobs();
        }
        
        foreach($this->_scheduledJobs as $scheduledJob) {
            if($scheduledJob->getJobId() == $jobId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the cron jobs configured either from xml config.xml file or database
     *
     * @return array 
     */
    public function getJobs()
    {
        if(!empty($this->_jobs)) {
            return $this->_jobs;
        }

        switch ($this->_cronJobsConfigSource)
        {
            case 'file':
                $this->_jobs = $this->_getJobsFromXml();
            break;
            case 'db':
                $this->_jobs =$this->_getJobsFromDb();
            break;
        }
        return $this->_jobs;
    }

    /**
     * Get the cron jobs from Xml file, etc/config.xml
     * 
     * @return type 
     */
    protected function _getJobsFromXml()
    {
        $_configFile = App_Main::getModuleDir('etc', 'Cron') . DS . 'config.xml';
        if(!file_exists($_configFile)) {
            App_Main::exception('Core', 'unable ot find the config file for the cron module. Could not continue');
            die();
        }
        $_xml = simplexml_load_file($_configFile)->crontab;

        $jobs = array();
        foreach($_xml->jobs->children() as $job) {

            $cronJob = App_Main::getModel('cron/job');
            $cronJob->setJobCode($job->getName());
            if($job->schedule && $job->schedule->cron_expr) {
                $cronJob->setCronExprString((string) $job->schedule->cron_expr);
            }
            $cronJob->setModule((string)$job->run->module);
            if($job->run->action) {
                $cronJob->setAction((string)$job->run->action);
            }
            //$cronJob['params'] = (string)$job->run->function;
            $jobs[] = $cronJob;
        }
        return $jobs;
    }

    /**
     * Collect the cronjobs from database
     * 
     * @return array 
     */
    protected function _getJobsFromDb()
    {
        return $this->getResource()->getJobs();
    }

    /**
     * Process the cheduled cron jobs and execute them according to the current time 
     * @todo make it possilbbe to specify the buffer time and then process the job so that the timings are exactly correct
     * 
     * @return bool 
     */
    public function dispatch()
    {
        $this->getResource()->markMissedJobs(date('Y-m-d H:i:s',time() - (60*self::SCHEDULE_AHEAD_FOR)));
        $dispatchStartTime = now();
        $jobs = $this->getResource()->getScheduledCronJobs();
        
        foreach($jobs as $job) {
            $schedules = $job->getSchedules();
            $schedule = !empty($schedules) ? array_shift($schedules) : false;
            if(!$schedule) { continue; }
            if(time() - strtotime($dispatchStartTime) > self::MAX_RUN_INTERVAL) { return; } //exit if the current running time exeeds maximum

            $scheduleId = $schedule['schedule_id'];
            $timeDiff = strtotime($schedule['scheduled_at']) - time();
            if($timeDiff > self::MAX_RUN_INTERVAL) { return; } //return if the time interval is greater than the max run time
            if($timeDiff > 10) {
                $this->getResource()->updateJobStatus($scheduleId, self::STATUS_WAITING);
                sleep($timeDiff);
            }
            
            $this->getResource()->updateJobStatus($scheduleId, self::STATUS_RUNNING);
            $modelInstance = App_Main::getModel($job->getModule());
            if($job->getAction()) {
                $action = $job->getAction();
                try {
                    $modelInstance->$action();
                } catch(Exception $e) {
                    $this->getResource()->updateJobStatus($scheduleId, self::STATUS_ERROR, null, $e->__toString());
                }
            }
            $this->getResource()->updateJobStatus($scheduleId, self::STATUS_SUCCESS);
        }
        $this->getResource()->markMissedJobs($dispatchStartTime);
        return true;
    }

    /**
     * Remove a scheduled job from the que
     * @param int $scheduleId
     * @param int $jobId
     * @return Cron_Model_Cron 
     */
    public function removeSchduledJob($scheduleId = null, $jobId = null)
    {
        if($scheduleId) {
            $this->getResource()->removeScheduledByScheduleId($scheduleId);
        } else if($jobId) {
            $this->getResource()->removeScheduledByJobId($jobId);
        }
        return $this;
    }
}
?>
