<?php
/**
 * class Stages_Helper_Data
 * 
 * @package Stages
 * @category Helper
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Stages_Helper_Data extends Core_Helper_Abstract
{
    /**
     * Parse the xml to array
     * - nodeName=>nodeValue
     * 
     * @param SimpleXMLElement $xml
     * @return array 
     */
    public function XMLToArray($xml)
    {
        $return = null;
        if (!$xml instanceof SimpleXMLElement) {
            if(is_string($xml)) {
                $xml = simplexml_load_string($xml);
            } else {
                return false;
            }
        }

        foreach ($xml->children() as $element => $value) {
            if (!$value instanceof SimpleXMLElement) { continue; }

            $children = (array)$value->children();
            if(!empty($children['@attributes'])) { unset($children['@attributes']); } //unset the attributes returned (if any) along with children

            if (count($children) > 0) {
                if(count($xml->$element) > 1) {
                    $return[$element][] = self::XMLToArray($value);
                } else {
                    $return[$element] = self::XMLToArray($value);
                }
            } else {
                $return[$element] = (string)$value;
            }
        }

        if (is_array($return)) {
            return $return;
        } else {
            return false;
        }
    }
    
    /**
     * http://php.net/manual/en/ref.simplexml.php
     * @param type $xmlObject
     * @param type $out
     * @return type 
     */
    public function xml2array ($xmlObject, $out = array())
    {
        foreach ((array)$xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? $this->xml2array ($node) : $node;
        }
        return $out;
    }

    /**
     * Format the date for frontend javascript
     * - format 'n/j/Y' eg. 2/9/2011 || 5/19/2010
     * 
     * @param int $ts
     * @return string 
     */
    public function formatDateForJs($ts)
    {
        return date('n/j/Y', $ts);
    }

    /**
     * Format the date string from javascript into system used model
     * 'n/j/Y' -> 'Y-m-d'
     * @param type $strDt
     * @return type 
     */
    public function formatDateFromJs($strDt)
    {
        $params = explode('/', $strDt);
        return date('Y-m-d', mktime(null, null, null, $params[0], $params[1], $params[2]));
    }
    
    public function processMilestoneStats($milestone, $todoData = array())
    {
        $todoCount = !empty($todoData['count']) ? $todoData['count'] : 0;
        $completed =  !empty($todoData['completed']) ? $todoData['completed'] : 0;
        $uncompleted = !empty($todoData['uncompleted']) ? $todoData['uncompleted'] : 0;
        
        $status = Project_Model_Milestone::MS_STATUS_NOTSTARTED;
        if($todoCount == 0) { return $status; }
        
        if(!empty($todoData['comments']) || !empty($todoData['hours'])) {
            $status = Project_Model_Milestone::MS_STATUS_STARTED;
        }
        
        if($uncompleted == 0 || $milestone->getBcStatus() == 1) {
            $status = Project_Model_Milestone::MS_STATUS_FINISHED;
        } else if($uncompleted > 0 && $completed > 0) {
            $status = Project_Model_Milestone::MS_STATUS_STARTED;
        }
        if($status != Project_Model_Milestone::MS_STATUS_FINISHED && strtotime($milestone->getMilestoneDate()) < time()) {
            $status = Project_Model_Milestone::MS_STATUS_OVERDUE;
        }
        return $status;
    }
}
?>
