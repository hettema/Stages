<?php
/**
 * class Connect_Basecamp
 * Basecamp Connect API wraper
 * 
 * @package Core
 * @subpackage Basecamp
 * @category Lib-Object
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
require('Basecamp'. DS .'Basecamp.class.php');
class Connect_Basecamp extends Basecamp
{
    public function createProject($name)
    {
        /*$body = array(
                  'project'=>array(
                    'name'=>$name
                    )
                );

        $this->setupRequestBody($body);

        $response = $this->processRequest("{$this->baseurl}projects/projects.xml",'POST');*/
            $body = array(

        'project'=>array(

            'name' => $name )

        );

    $this->setupRequestBody($body);

    $this->processRequest("{$this->baseurl}projects.xml", 'POST'); 
    }
}
?>
