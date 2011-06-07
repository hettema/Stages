<?php
/**
 * class Core_Model_Translate_Csv
 * Translation wraper object for the Zend_Translate_Adapter_Csv
 * 
 * @package Core
 * @subpackage Translate
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Core_Model_Translate_Csv extends Zend_Translate_Adapter_Csv
{

    public function  __construct()
    {
        $args = func_get_args();
        $options = array('delimiter' => ",", 'length'=>0, 'enclosure'=>'"', 'scan'=>'directory', 'ignore'=> 'email');
        $data = !empty ($args[0][0]) ? $args[0][0] : App_Main::getBaseDir('locale');
        $locale = !empty ($args[0][1]) ? $args[0][1] : App_Main::getWebsite()->getLocale();

        App_Main::setErrorHandler();
        try {
        parent::__construct($data, $locale, $options);
        } catch (Exception $e) {}
    }

    public function getLocaleData($locale = null)
    {
        if(empty ($locale)) {
            $locale = App_Main::getWebsite()->getLocale();
        }
        if(!empty($this->_translate[$locale])) {
            return $this->_translate[$locale];
        }
        return false;
    }
}
?>
