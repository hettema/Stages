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
}
?>