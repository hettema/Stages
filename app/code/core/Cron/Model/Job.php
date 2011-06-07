<?php
/**
 * class Cron_Model_Job
 * 
 * @package Cron
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Cron_Model_Job extends Core_Model_Object
{
    public function setCronExpr($expr)
    {
        $e = preg_split('#\s+#', $expr, null, PREG_SPLIT_NO_EMPTY);
        if (sizeof($e)<5 || sizeof($e)>6) {
            throw App_Main::exception('Core', 'Invalid cron expression: '.$expr);
        }

        $this->setCronExprArr($e);
        return $this;
    }

    /**
     * Checks the observer's cron expression against time
     *
     * Supports $this->setCronExpr('* 0-5,10-59/5 2-10,15-25 january-june/2 mon-fri')
     *
     * @param Varien_Event $event
     * @return boolean
     */
    public function trySchedule($time)
    {
        $e = $this->getCronExprArr();
        if (!$e || !$time) {
            return false;
        }
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $match = $this->matchCronExpression($e[0], date('i', $time))
            && $this->matchCronExpression($e[1], date('H', $time))
            && $this->matchCronExpression($e[2], date('d', $time))
            && $this->matchCronExpression($e[3], date('F', $time))
            && $this->matchCronExpression($e[4], date('D', $time));

        if ($match) {
            $this->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
            $this->setScheduledAt(strftime('%Y-%m-%d %H:%M', $time));
        }
        return $match;
    }

    /**
     * match cron expression against the current time entry passed along
     *
     * 
     * $num
     *  - i
     *  - H
     *  - d
     *  - F 
     *  - D
     *
     * @param string $expr cron expression string 5,15, * 10 * *
     * @param int $num
     * @return string 
     */
    public function matchCronExpression($expr, $num)
    {
        // handle ALL match
        if ($expr==='*') {
            return true;
        }

        // handle multiple options
        if (strpos($expr,',')!==false) {
            foreach (explode(',',$expr) as $e) {
                if ($this->matchCronExpression($e, $num)) {
                    return true;
                }
            }
            return false;
        }

        // handle modulus
        if (strpos($expr,'/')!==false) {
            $e = explode('/', $expr);
            if (sizeof($e)!==2) {
                throw App_Main::exception('Core', "Invalid cron expression, expecting 'match/modulus': ".$expr);
            }
            if (!is_numeric($e[1])) {
                throw App_Main::exception('Core', "Invalid cron expression, expecting numeric modulus: ".$expr);
            }
            $expr = $e[0];
            $mod = $e[1];
        } else {
            $mod = 1;
        }

        // handle all match by modulus
        if ($expr==='*') {
            $from = 0;
            $to = 60;
        }
        // handle range
        elseif (strpos($expr,'-')!==false) {
            $e = explode('-', $expr);
            if (sizeof($e)!==2) {
                throw App_Main::exception('Core', "Invalid cron expression, expecting 'from-to' structure: ".$expr);
            }

            $from = $this->getNumeric($e[0]);
            $to = $this->getNumeric($e[1]);
        }
        // handle regular token
        else {
            $from = $this->getNumeric($expr);
            $to = $from;
        }

        if ($from===false || $to===false) {
            throw App_Main::exception('Core', "Invalid cron expression: ".$expr);
        }

        return ($num>=$from) && ($num<=$to) && ($num%$mod===0);
    }

    public function getNumeric($value)
    {
        static $data = array(
            'jan'=>1,
            'feb'=>2,
            'mar'=>3,
            'apr'=>4,
            'may'=>5,
            'jun'=>6,
            'jul'=>7,
            'aug'=>8,
            'sep'=>9,
            'oct'=>10,
            'nov'=>11,
            'dec'=>12,

            'sun'=>0,
            'mon'=>1,
            'tue'=>2,
            'wed'=>3,
            'thu'=>4,
            'fri'=>5,
            'sat'=>6,
        );

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(substr($value,0,3));
            if (isset($data[$value])) {
                return $data[$value];
            }
        }

        return false;
    }
}

?>
