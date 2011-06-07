<?php
/**
 * class Connect_Prefinery
 * Prefinery class file.
 *
 * Class for working with the Prefinery API's.
 * Read the docs at: http://app.prefinery.com/api
 *
 * @package Core
 * @category Lib-Object
 * @author Dmitry Avseyenko <polsad@gmail.com>
 * @copyright Copyright (c) 2010, Inspirativ
 */
class Connect_Prefinery {
    
    private $api_host = NULL;
    private $api_key = NULL;
    private $api_secure = NULL;
    private $api_protocol = NULL;
    private $api_proxy = NULL;
    private $http_res = NULL;

    /**
     * @access public
     * @param string $api_host prefinery host
     * @param string $api_key prefinery api key
     * @param bool $api_secure
     * @param string $api_proxy proxy server
     */
    public function  __construct($api_host, $api_key, $api_secure = false, $api_proxy = NULL) {
        $this->api_host = $api_host;
        $this->api_key = $api_key;
        $this->api_secure = $api_secure;
        $this->api_protocol = ($api_secure == false) ? 'http' : 'https';
        $this->api_proxy = $api_proxy;
    }

    /**
     * List resources
     *
     * $resources = $prefinery->getResources();
     *
     * @access public
     * @return array
     */
    public function getResources() {
        $url = 'betas.xml';
        $xml = $this->sendRequest($url);

        $result = array();
        if ($this->http_res == 200) {
            $result = $this->getResponse($xml);
        }
        return $result;
    }

    /**
     * Find resource by id
     *
     * $resource = $prefinery->getResourceById(74);
     *
     * @access public
     * @param int $resource prefinery resource
     * @return array
     */
    public function getResourceById($id) {
        $url = "betas/{$id}.xml";
        $xml = $this->sendRequest($url);

        $result = array();
        if ($this->http_res == 200) {
            $result = $this->getResponse($xml);
        }
        return $result;
    }

    /**
     * List testers
     *
     * $testers = $prefinery->getTesters(74);
     * $testers = $prefinery->getTesters(74, 2);
     *
     * @access public
     * @param int $resource prefinery resource
     * @param int $page optional page number
     * @return array
     */
    public function getTesters($resource, $page = NULL) {
        $url = 'testers.xml';
        $params = ($page == NULL) ? array() : array('page' => $page);
        $xml = $this->sendRequest($url, $resource, $params);

        $result = array();
        if ($this->http_res == 200) {
            $result = $this->getResponse($xml);
        }
        return $result;
    }

    /**
     * Find tester by email
     *
     * $tester = $prefinery->getTesterByEmail(74, 'justin@prefinery.com');
     *
     * @access public
     * @param int $resource prefinery resource
     * @param string $email email
     * @return array
     */
    public function getTesterByEmail($resource, $email) {
        $url = 'testers.xml';
        $xml = $this->sendRequest($url, $resource, array('email' => $email));

        $result = array();
        if ($this->http_res == 200) {
            $result = $this->getResponse($xml);
            $result = $result['tester'];
        }
        return $result;
    }

    /**
     * Find tester by id
     *
     * $tester = $prefinery->getTesterById(100, 12323);
     *
     * @access public
     * @param int $resource prefinery resource
     * @param int $id tester id
     * @return array
     */
    public function getTesterById($resource, $id) {
        $url = "testers/{$id}.xml";
        $xml = $this->sendRequest($url, $resource);

        $result = array();
        if ($this->http_res == 200) {
            $result = $this->getResponse($xml);
        }
        return $result;
    }

    /**
     * Create new tester
     *
     * $profile = array('first-name' => 'Justin', 'last-name' => 'Britten');
     * $tester = $prefinery->createTester(74, 'justin@prefinery.com', 'active', 'TECHCRUNCH', $profile);
     *
     * @access public
     * @param int $resource prefinery resource
     * @param string $email tester email
     * @param string $status status
     * @param string $icode invitation code
     * @param array $profile array with profile data
     * @return array
     */
    public function createTester($resource, $email, $status, $icode, $profile) {
        $dom = new DOMDocument('1.0', 'UTF-8');

        $tester = $this->newDomElement($dom, 'tester');
        $this->newDomElement($dom, 'email', $tester, $email);
        $this->newDomElement($dom, 'invitation-code', $tester, $icode);
        $this->newDomElement($dom, 'status', $tester, $status);

        $pnode = $this->newDomElement($dom, 'profile', $tester);
        $this->newDomElements($dom, $profile, $pnode);
        $data = $dom->saveXML();

        $url = 'testers';
        $xml = $this->sendRequest($url, $resource, array(), $data);

        $result = array();
        if ($this->http_res == 201) {
            $result = $this->getResponse($xml);
        }
        return $result;
    }

    /**
     * Update tester
     *
     * $profile = array('city' => 'Austin', 'state' => 'TX');
     * $tester = $prefinery->updateTester(74, 1259, NULL, NULL, $profile);
     *
     * @access public
     * @param int $resource prefinery resource
     * @param int $id tester id
     * @param string optional $email tester email
     * @param string optional $status status
     * @param array optional $profile array with profile data
     * @return array
     */
    public function updateTester($resource, $id, $email = NULL, $status = NULL, $profile = NULL) {
        $result = $this->getTesterById($resource, $id);
        if ($tester != NULL) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $tnode = $this->newDomElement($dom, 'tester');

            $email = ($email == NULL) ? $tester['email'] : $email;
            $this->newDomElement($dom, 'email', $tnode, $email);

            $status = ($status == NULL) ? $tester['status'] : $status;
            $this->newDomElement($dom, 'status', $tnode, $status);

            $buff = array();
            if ($profile != NULL) {
                foreach ($tester['profile'] as $k => $v) {
                    $buff[$k] = ($profile[$k] == NULL) ? $v : $profile[$k];
                }
            }
            else {
                $buff = $tester['profile'];
            }

            $pnode = $this->newDomElement($dom, 'profile', $tnode);
            $this->newDomElements($dom, $buff, $pnode);

            $data = $dom->saveXML();

            $url = "testers/{$id}.xml";
            $xml = $this->sendRequest($url, $resource, array(), $data, 'PUT');

            $result = array();
            if ($this->http_res == 200) {
                $result = $this->getResponse($xml);
            }
        }
        return $result;
    }

    /**
     * Delete tester
     *
     * $prefinery->deleteTester(74, 1259);
     *
     * @access public
     * @param int $resource prefinery resource
     * @param int $id tester id
     * @return bool
     */
    public function deleteTester($resource, $id) {
        $url = "testers/{$id}.xml";
        $data = '';
        $xml = $this->sendRequest($url, $resource, array(), $data, 'DELETE');

        $result = false;
        if ($this->http_res == 200) {
            $result = true;
        }
        return $result;
    }

    /**
     * Verify invitation code
     *
     * $result = $prefinery->verifyInvitationCode(74, 1259, 'TECHCRUNCH');
     *
     * @access public
     * @param int $resource prefinery resource
     * @param int $id tester id
     * @param string $code invitation code
     * @return bool
     */
    public function verifyInvitationCode($resource, $id, $code) {
        $url = "testers/{$id}/verify.xml";
        $xml = $this->sendRequest($url, $resource, array('invitation_code' => $code));

        $result = false;
        if ($this->http_res == 200) {
            /**
             * If user exist, always return HTTP request 200...
             */
            //$tester = $this->getResponse($xml);
            //if ($tester['invitation-code'] == $code) {
                $result = true;
            //}
        }
        return $result;
    }

    /**
     * Checkin tester by email
     *
     * @access public
     * @param int $resource prefinery resource
     * @param string $email email
     * @return bool
     */
    public function checkinTesterByEmail($resource, $email) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $checkin = $this->newDomElement($dom, 'checkin');
        $this->newDomElement($dom, 'email', $checkin, $email);
        $data = $dom->saveXML();

        $url = "checkins.xml";
        $xml = $this->sendRequest($url, $resource, array(), $data, 'POST');

        $result = false;
        if ($this->http_res == 200) {
            $result = true;
        }
        return $result;
    }

    /**
     * Checkin tester by id
     *
     * @access public
     * @param int $resource prefinery resource
     * @param int $id tester id
     * @return bool
     */
    public function checkinTesterById($resource, $id) {
        $url = "testers/{$id}/checkin.xml";
        $xml = $this->sendRequest($url, $resource, array(), array(), 'POST');

        $result = false;
        if ($this->http_res == 200) {
            $result = true;
        }
        return $result;
    }

    /**
     * List of checkins for a tester.
     *
     * @access public
     * @param int $resource prefinery resource
     * @param int $id tester id
     * @return array
     */
    public function getCheckinsTester($resource, $id) {
        $url = "testers/{$id}/checkins.xml";
        $xml = $this->sendRequest($url, $resource, array());

        $result = array();
        if ($this->http_res == 200) {
            $result = $this->getResponse($xml);
        }
        return $result;
    }

    /**
     * Send request to prefinery
     *
     * @access private
     * @param string $url
     * @param int $resource
     * @param array $params
     * @param array $data
     * @param string $method
     * @return mixed
     */
    private function sendRequest($url, $resource = NULL, $params = array(), $data = NULL, $method = NULL) {
        /**
         * Create params string
         */
        if ($params != NULL) {
            $urlstr = array();
            foreach($params as $k => $v) {
                $urlstr[] = $k.'='.$v;
            }
            $urlstr =  implode('&', $urlstr);
            $urlstr = '&'.$urlstr;
        }
        else {
            $urlstr = '';
        }

        $ch  = curl_init();
        if ($resource != NULL) {
            $url = "{$this->api_protocol}://{$this->api_host}.prefinery.com/api/v1/betas/{$resource}/{$url}?api_key={$this->api_key}{$urlstr}";
        }
        else {
            $url = "{$this->api_protocol}://{$this->api_host}.prefinery.com/api/v1/{$url}?api_key={$this->api_key}{$urlstr}";
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // Turning off the server and peer verification(TrustManager Concept).
        if ($this->api_secure == true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        // If set proxy server
        if ($this->api_proxy != NULL) {
            curl_setopt($ch, CURLOPT_PROXY, $this->api_proxy);
        }
        if ($method != NULL) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        if ($data != NULL) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        // Getting response from server
        $response = curl_exec($ch);
        $headers  = curl_getinfo($ch);
        $this->http_res = $headers['http_code'];

        return $response;
    }

    /**
     * Convert prefinery response from XML to array
     *
     * @access private
     * @param string $xml
     * @return array
     */
    private function getResponse($xml) {
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $response = $this->xmlToArray($doc->documentElement);
        return $response;
    }

    /**
     * Convert DomDocument to array
     * @param object $node dom node
     * @return array
     */
    private function xmlToArray($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->xmlToArray($child);
                    if(isset($child->tagName)) {
                        $t = $child->tagName;
                        if(!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    }
                    elseif($v) {
                        $output = (string) $v;
                    }
                }
                if(is_array($output)) {
                    foreach ($output as $t => $v) {
                        if(is_array($v) && sizeof($v)==1 && $t!='@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

    /**
     * Add new element to DomDocument
     *
     * @access private
     * @param object $dom DomDocument
     * @param string $node node title
     * @param string $root optional parent node
     * @param string $value optional node value
     * @return object
     */
    private function newDomElement(&$dom, $node, $root = NULL, $value = NULL) {
        if ($value == NULL)
            $nnode = $dom->createElement($node);
        else
            $nnode = $dom->createElement($node, (string)$value);
        if ($root != NULL)
            $root->appendChild($nnode);
        else
            $dom->appendChild($nnode);
        return $nnode;
    }

    /**
     * Add new elements to DomDocument
     *
     * @access private
     * @param object $dom DomDocument
     * @param array $array elements ('title' => 'value')
     * @param object $node parent node
     * @return void
     */
    private function newDomElements(&$dom, $array, &$node) {
        foreach ($array as $k => $v) {
            if ($v == NULL) {
                $nnode = $dom->createElement($k);
            }
            else {
                if (is_array($v)) {
                    foreach ($v as $key=>$value) {
                        $nnode = $dom->createElement($key, $value);
                    }
                } else {
                    $nnode = $dom->createElement($k, $v);
                }
            }
            $node->appendChild($nnode);
        }
    }
}
