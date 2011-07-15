<?php
/**
 * class Stages_Model_User
 * User object model to handle the user data and user actions
 * 
 * @package Stages
 * @category Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author <aliaseldhose@ceegees.in>
 */
class Stages_Model_User extends Stages_Model_Abstract
{

    protected $_bcPeople = false;
    protected $_bcProjects = false;
    protected $_bcMsIndex = false;

    protected function _construct()
    {
        $this->_init('stages/user');
    }

    /**
     *
     * @return string user name 
     */
    public function getname()
    {
        return $this->getFirstname() .' '. $this->getLastname();
    }
    
    ########################## BC DATA MANGEMENT ########################################
    /**
     * Load the user information from Basecamp
     * 
     * firstname
     * lastname
     * email
     * title
     * phone_home
     * phone_mobile
     * phone_office
     * phone_fax
     * bc_id
     * bc_client_id
     * bc_company_id
     * bc_avatar
     * bc_profile_url
     * 
     * @return Stages_Model_User
     */
    public function loadUserInfoFromBc()
    {
        $userXml = $this->getBcConnect()->getMe();
        if(empty ($userXml['body'])) { return false; }

        $userInfo = $this->getParsedUserInfo($userXml['body']);
        $this->addData($userInfo);
        return $this;
    }

    /**
     * Parse the user inforrmation from basecamp
     * 
     * @param SimpleXMLElement $userXml
     * @return array $data associative array of user information
     * - first-name -> firstname
     * - last-name -> lastname
     * - email-address -> email
     * - title -> title
     * - phone-number-home -> phone_home
     * - phone-number-mobile -> phone_mobile
     * - phone-number-office -> phone_office
     * - phone-number-fax -> phone_fax
     * - id -> bc_id
     * - client_id  -> bc_client_id
     * - company-id  -> bc_company_id
     * - avatar-url  -> bc_avatar
     */
    public function getParsedUserInfo($userXml)
    {
        $userInfo = $this->_getHelper()->XMLToArray($userXml);

        $data['firstname'] = @$userInfo['first-name'];
        $data['lastname'] = @$userInfo['last-name'];
        $data['email'] = @$userInfo['email-address'];
        $data['title'] = @$userInfo['title'];
        $data['phone_home'] = @$userInfo['phone-number-home'];
        $data['phone_mobile'] = @$userInfo['phone-number-mobile'];
        $data['phone_office'] = @$userInfo['phone-number-office'];
        $data['phone_fax'] = @$userInfo['phone-number-fax'];
        $data['bc_id'] = @$userInfo['id'];
        $data['bc_client_id'] = @$userInfo['client_id'];
        $data['bc_company_id'] = @$userInfo['company-id'];
        $data['bc_avatar'] = @$userInfo['avatar-url'];
        $data['bc_profile_url'] = @$userInfo[''];

        return $data;
    }

    /**
     * Get the Basecamp users profile information
     * 
     * @return array  Basecamp users details as associative array
     *              - firstname
     *              - lastname
     *              - email
     *              - title
     *              - phone_home
     *              - phone_mobile
     *              - phone_office
     *              - phone_fax
     *              - bc_id
     *              - bc_client_id
     *              - bc_company_id
     *              - bc_avatar
     */
    public function getPeople()
    {
        if(!empty($this->_bcPeople)) { return $this->_bcPeople; }
        
        //get the basecamp users
        $respXml = $this->getBcConnect()->getPeople();
        if(empty ($respXml['body'])) { return false; }
        $_respXml = simplexml_load_string($respXml['body']);

        //load the user info for each of the user
        foreach($_respXml->person as $_xml) {
            $data = $this->getParsedUserInfo($_xml);
            if(empty($data['bc_id'])) { continue; }
            if(!empty($userData[$data['bc_id']])) { continue; }
            $userData[] = $data;
        }
        $this->_bcPeople = $userData;
        
        return $this->_bcPeople;
    }

    /**
     * Get the basecamp projects to whom the user have got acces to
     * If the $reload flag is true the projects will be loaded directly from BC
     * insted of loading from the local DB cache
     * 
     * @todo add a flag to reload projects based on the last load time 
     * 
     * @param bool $reload
     * @return array $projects array of basecamp project each instances of Project_Model_Project
     */
    public function getProjects($reload = false)
    {
        if(empty($this->_bcProjects) || $reload) {
            $this->_bcProjects = App_Main::getSingleton('project/project')->loadProjectsFromBc();
        }
        $this->_bcProjects = empty($this->_bcProjects) ? array() : $this->_bcProjects;
        return $this->_bcProjects;
    }
    
    /**
     * Add the project to the already loaded project list array
     * 
     * @param Project_Model_Project $project 
     * @return Stages_Model_User
     */
    public function addProjectToStack(Project_Model_Project $project)
    {
        if(!$this->getProjectById($project->getBcId())) {
            $this->_bcProjects[] = $project;
        }
        return $this;
    }

    /**
     * Get the project list with basic project info 
     * used in the frontend to search and autosuggest project names
     * 
     * @return array Proect details customized 
     */
    public function getProjectsJsList()
    {
        $projects = $this->getProjects();
        if(empty ($projects)) { return false; }
        $return = array();
        foreach($projects as $project) {            
            $return[] = $project->prepareDataForJson();
        }
        return $return;
    }

    /**
     * Get the milestone title index for search autosuggestion
     * 
     * @param bool $fromBc
     * @return array milestone title index               
     */
    public function getMilestoneIndex($fromBc = false)
    { 
        if(!empty($this->_bcMsIndex) && !$fromBc) { return $this->_bcMsIndex; }
        //get the basecamp projects
        $projects  = $this->getProjects();
        if(empty ($projects)) { return array(); }
        
        $milestones = array();
        //get milestones for each project
        foreach($projects as $project) {
            $projectMilestones = $project->getMilestones($fromBc, !$fromBc);
            $milestones = array_merge($milestones, $projectMilestones);
        }
        $msIndex = array();
        //create the title index of milestones
        foreach($milestones as $milestone) {
            $msIndex[] = $milestone->getTitle();
        }
        $this->_bcMsIndex = array_unique($msIndex);
        return $this->_bcMsIndex;
    }

    /**
     * Get the project from the locally loaded project collection by using the project identifier
     * 
     * @param int $id
     * @param string $field
     * @return Project_Model_Project 
     */
    public function getProjectById($id, $field = 'bc_id')
    {
        foreach($this->getProjects() as $project) {
            if($project->getData($field) == $id) { return $project; }
        }
        return false;
    }

    ########################## USER ACCOUNT RELATED #########################

    /**
     * Complete the user signup once the user have entered the username and password
     * Before this step the user is prompted to enter his basecamp host and acces 
     * tocken which is stored in the session
     * 
     * @return Stages_Model_User 
     */
    public function completeSignup()
    {
        if(!$this->getUsername() || !$this->getPassword()) {
            return false;
        }
        //Load the user info from Basecamp
        $this->loadUserInfoFromBc();
        if($this->getBcId()) {
            $this->createNewAccount();
        }
        //$this->setBcHost(@$userInfo['']);
        //$this->setBcAuthToken(@$userInfo['']);
        return $this;
    }

    /**
     * Create new user account
     * 
     * @return Stages_Model_User 
     */
    function createNewAccount()
    {        
        //check mandatory fields
        if(!$this->getEmail() || !$this->getPassword()) {
            App_Main::getSession()->addError('Error, all fields are mandatory');
            return false;
        }
        //replace unsupported characters from the url string
        if($this->_getResource()->checkExistingUsername($this->getUsername())) {
            return App_Main::getSession()->addError('Username is already registered');
        }

        $passwordHash = App_Main::getHelper('core')->getHash($this->getPassword(), 2);
        $this->setPassword($passwordHash);
        $this->setIsAppUser(1);
        $this->setAddedDate(now());
        $this->setUpdatedDate(now());
        $this->setLastVisited(now());

        $this->save();
        
        //$this->sendNewAccountEmail();
        return $this;
    }

    /**
     * Check the existing username
     * @param string $username
     * @return bool 
     */
    public function checkExistingUsername($username)
    {
        if($this->_getResource()->checkExistingUsername($username)) {
            return true;
        }
        return false;
    }
    
    /**
     * Login with the submitted username::password
     * @param string $username
     * @param string $password
     * @return Stages_Model_User 
     */
    function login($username, $password)
    {
        if(!$username || !$password) { App_Main::getSession()->addError('Please provide a valid username and password'); return false; }
        if($this->authenticate($username, $password)) {            
            return $this;
        }
        return false;
    }
    
    /**
     * Authenticate the user with the username::password
     * @param string $username
     * @param string $password
     * @return boolean 
     */
    function authenticate($username, $password)
    {
        $result = false;
        try {
            $this->loadByUsername($username);
            if ($this->getId() && App_Main::getHelper('core')->validateHash($password, $this->getPassword())) {
                /*if ($this->getIsActive() != '1') {
                    App_Main::throwException('This account is inactive.');
                }*/
                $result = true;
            } else {
                App_Main::getSession()->addError("username/password not valid");
            }
        } catch (Core_Exception $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) { $this->unsetData(); }
        return $result;
    }
    
    /**
     * Load the user data into the object
     * 
     * @param string $username
     * @return Stages_Model_User 
     */
    public function loadByUsername($username)
    {
        $this->setData($this->getResource()->loadByUsername($username));
        return $this;
    }

    /**
     * Set the user object in session
     * 
     * @param Stages_Model_User $object
     * @return type 
     */
    public function setSessionUser($object = false)
    {
        if($object instanceof Stages_Model_User && $object->getId()) {
            return App_Main::getSession()->setUser($object);
        } elseif($this->getId()) {
            return App_Main::getSession()->setUser($this);
        }
        return false;
    }

    /**
     * Reset the current password
     * If no arguments are passed the password will be reset by the system and 
     * a mail will be sent to the registered email address, so that the user can 
     * reset the autogenerated password on next login
     * 
     * @param string $pass
     * @param bool $sendEmail
     * @param bool $changeOnNextLogin
     * @return Stages_Model_User 
     */
    public function resetPassword($pass = null, $sendEmail = true, $changeOnNextLogin = true)
    {
        if(!$this->getUserId()) {
            App_Main::getSession()->addError('Unable to find your email in our database');
            return false;
        }
        $passwordNew = !empty($pass) ? $pass : App_Main::getHelper('core')->getRandomString(8);
        $passwordHash = App_Main::getHelper('core')->gethash($passwordNew, 2);

        $this->setPassword($passwordHash);
        //Update the password into the user table
        $this->_getResource()->resetPassword($this->getId(), $passwordHash, $changeOnNextLogin);
        if($sendEmail) {
            $this->sendNewPasswordEmail($passwordNew);
        }
        return $this;
    }

    /**
     * Send the new account email with welcome message
     */
    private function sendNewAccountEmail() { }

    /**
     * Send the new password to the user
     * @param string $password
     * @return bool 
     */
    private function sendNewPasswordEmail($password)
    {
        $mail = $this->_getMail();
        $mail->setTemplate('widfy/change_password.phtml');
        $mail->setToEmail($this->getEmail());
        $mail->setToName($this->getName());
        $mail->setSubject('WIDFY password reset');
        $mail->setTemplateVar(array('user_name'=> $this->getName(),
                                    'new_password'=> $password
                                   )
                             );
        try {
            $mail->send();
        } catch(Exception $e) {
            App_Main::getSession()->addError('Erorr sending password reset email '. $e->getMessage());
        }
        return true;
    }

    /**
     * Grant the tester action for beta functionalities for the user
     * Uses prefinery API to keep track of the beta users
     * @todo this is not used as of now as the prefinery module is not enabled
     * 
     * @param integer $prefineryId
     */
    public function grantTesterAccess($prefineryId = null)
    {
        $this->_getResource()->grantTesterAccess($this, $prefineryId);
        return $this;
    }

    public function getJsonData()
    {
        return Zend_json::encode(array('user_id'=> $this->getId(),
                                       'bc_id'=> $this->getBcId(),
                                       'name'=>$this->getName(),
                                       'avatar'=>$this->getBcAvatar(),
                                       'title'=>mb_substr($this->getTitle(), 0, 60)));
    }
}

?>
