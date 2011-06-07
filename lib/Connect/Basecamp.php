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
        $tocken = '15ee922302f54f8cf2244f4f433ece7fb4bb46ce';
        $url = 'https://pbi03a08.basecamphq.com/';
        $this->setUsername('pbi03a08');
        $this->setPassword('capiri123');
        $this->setBaseurl($url);
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
