<?php
/**
 * @author Sergio Gayarre Garasa <sergio.gayarre@businessdecision.com>
 * @desc This class is an intermediate class to connect with SeaFile Web API through requests by curl.
 */

class Client2 {

        private $sUrl;
        private $sToken;
        private $oRestCurlClient = null;
        private static $accountsList = [];
        
        public function __construct($sHost)
        {
            if((!$sHost)||is_null($sHost))
                throw new Exception("Host is undefined host");
            $this->sUrl = rtrim($sHost, '/');
            $this->oRestCurlClient = new  \Curl\Curl();
        }
    
        public function getCurlClient(){
             return  $this->oRestCurlClient;
        }

        public function getAuthToken(){
            return $this->sToken;
        }
       
        public function endSeaFileAction(){
           $this->getCurlClient()->close();
        }

       /**
         * ping calls to a REST API of SeaFile  to see if we have response from the server
         * @return bool true/false if the response is correct or not.
         */
        
        public function ping()
        {
            $url = $this->sUrl."/api2/ping/"; 
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $this->getCurlClient()->setOpt(CURLOPT_HTTPGET,1);
            $this->getCurlClient()->setOpt(CURLOPT_CUSTOMREQUEST,'GET');
            $this->getCurlClient()->get($url); 
            $bResult = ($this->getCurlClient()->response === "pong") ? true : false;
            
            return $bResult;
       }
       
      /**
       * generateNewToken calls to a REST API of SeaFile  to generate a new token for the user
       * @param string $sUsername: relative to the email of the user who is trying to log in
       * @param string $sPassword: relative to the user's password who is trying  to log in 
       * @return string/null $sToken: relative to the string returned by the api when somebody has logged in properly
       */
    
       
        public function generateNewToken($sUsername, $sPassword)
        {
            $sToken = null;
            $aData = ["username"=> $sUsername,"password"=>$sPassword];
            $sGetToken = $this->getAuthToken();

            if ($sGetToken!= null && !empty($sGetToken)) {
                  return $sGetToken;
            } else {
                $this->getCurlClient()->post($this->sUrl."/api2/auth-token/",$aData);
                $sReponse = $this->getCurlClient()->response->token;
                 
                if (!empty($sReponse)&& is_string($sReponse)) {
                     $this->getCurlClient()->setBasicAuthentication($sUsername,$sPassword);
                     $sToken= $sReponse;
                } else {
                     $sToken = null;
                }
                return $sToken;
            }
        }
       
      /**
       * login calls to a REST API to create an account on SeaFile
       * @param string $sUsername: relative to the email given for the new user
       * @param string $sPassword: relative to the password given for the new user
       * @return bool string with hardcoded token from configuration to make it easier and  faster 
       */
        
        public function login($sUsername,$sPassword, $token = null)
        {
        
            if (is_string($token) && !is_null($token)) {
                $tokenAuth = $token;
            } else { 
                $tokenAuth = $this->generateNewToken($sUsername, $sPassword);
            }
        
            $this->sToken = $tokenAuth;
            return $tokenAuth;
        } 

        /**
         * createAccount calls to a REST API to create an account on SeaFile
         * @param string $sEmail: relative to the email given for the new user
         * @param string $sPassword: relative to the password given for the new user
         * @throws Exception when error is found
         * @return bool true or false specifying if the account was created 
         */

        public function createAccount($sEmail,$sPassword){

            $url = $this->sUrl . sprintf("/api2/accounts/%s/", urlencode($sEmail)); 

            if (!$sEmail) {
                throw new Exception ("Email for creating account is being missed",Exception::BAD_REQUEST);
            }

            if (!$sPassword) {
                throw new Exception ("Password for creating account is being missed",Exception::BAD_REQUEST);
            }

            $aData = array("password"  => $sPassword);       
            /* we set up all curls headers needed to make a put request */
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_INFILESIZE,-1);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, false);
            $this->getCurlClient()->put($url,$aData);
            $bResult = ($this->getCurlClient()->response === "success") ? true : false;

            return $bResult;
        }
       
        public function getListLDAP(){
           
            $url = $this->sUrl."/api2/accounts/?start=-1&limit=-1&scope=LDAP"; 
            /* we set up headers for curl */
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $this->getCurlClient()->setOpt(CURLOPT_HTTPGET,1);
            $this->getCurlClient()->setOpt(CURLOPT_CUSTOMREQUEST,'GET');
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->get($url);
            $aResult=$this->getCurlClient()->response; //we obtain the curl response, it returns an array of objects
            $aList = [];
           
            if (!empty($aResult)) {
                foreach ($aResult as $oData) 
                   array_push($aList,$oData->email);
            }

            return $aList;
        }
       
        public function getListDb(){
           
           $url = $this->sUrl."/api2/accounts/?start=-1&limit=-1&scope=DB"; 
            
            /* we set up headers for curl */
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $this->getCurlClient()->setOpt(CURLOPT_HTTPGET,1);
            $this->getCurlClient()->setOpt(CURLOPT_CUSTOMREQUEST,'GET');
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->get($url);
            
            $aResult=$this->getCurlClient()->response; //we obtain the curl response, it returns an array of objects
            $aList = [];
           
            if(!empty($aResult)){
               foreach($aResult as $oData){ 
                   array_push($aList,$oData->email);
               }
            }

           return $aList;
        }

        public function getAccountsList(){
           
            if (empty(self::$accountsList)) {
                $url = $this->sUrl."/api2/accounts/?start=-1&limit=-1&scope=DB"; 
                /* we set up headers for curl */
                $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
                $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, true);
                $this->getCurlClient()->setOpt(CURLOPT_HTTPGET,1);
                $this->getCurlClient()->setOpt(CURLOPT_CUSTOMREQUEST,'GET');
                $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
                $this->getCurlClient()->get($url);

                $aResult=$this->getCurlClient()->response; //we obtain the curl response, it returns an array of objects
                $aList = [];

                if (!empty($aResult)) {
                   foreach($aResult as $oData) { 
                       $aList[] =$oData->email;
                   }
                }

                $url = $this->sUrl."/api2/accounts/?start=-1&limit=-1&scope=LDAP";
                $this->getCurlClient()->get($url);
                $aResult2 = $this->getCurlClient()->response;

                if (!empty($aResult2)) {
                   foreach($aResult2 as $key => $oData2){ 
                       if (!in_array($aDota2->email,$aList)) {
                            $aList[] = $oData2->email;
                       }
                   }
                }

                $sListMails = implode(",", $aList);
                self::$accountsList = $aList;
                return $aList;
                
            } else {
               return self::$accountsList;
            }
        }

      /**
       * deleteAccount calls to a REST API to delete an email account on SeaFile
       * @param string $sEmail: relative to the email given for the new user
       * @throws Exception when error is found
       * @return bool true or false specifying if the account was created
       */

        public function deleteAccount($sEmail)
        {
            $url = $this->sUrl.sprintf("/api2/accounts/%s/", urlencode($sEmail));
            $aData = [];

            if (!$sEmail) 
                throw new Exception ("Email address is wrong",Exception::BAD_REQUEST);
           
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_POSTFIELDS,$aData);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->delete($url,$aData);
            $bResult = ($this->getCurlClient()->response === "success") ? true : false;
            
            return $bResult;
        }

      /**
       * createLibrary calls to a REST API to add new library or repo in seafile.
       * @param string $sNameLibrary: relative to the the name of the library we want to create
       * @param string $sPasswordLibrary: relative to the password given for encrypting the library)
       * @throws Exception when error is found
       * @return string specifying if the library was created 
       */

        public function createLibrary($sNameLibrary,$sPasswordLibrary = null){

            $url = $this->sUrl."/api2/repos/";
            $sNameLibrary = urlencode($sNameLibrary);
            $aData = array("name"=>$sNameLibrary,"desc"=> "new repo");

            if(!is_null($sPasswordLibrary)  && !empty($sPasswordLibrary))
                $aData["passwd"] = $sPasswordLibrary;

            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->post($url,$aData);

            if($this->getCurlClient()->response->repo_id!= null && $this->getCurlClient()->response->repo_id!="")
                return $this->getCurlClient()->response->repo_id;
            else
                return '';   	
        }

       /**
        * shareLibraryToGroup calls to a REST API to share a library to a group
        * @param string $sIdRepo: identifier of the repo we want to share 
        * @param integer $iIdGroup: relative to the identifier of the group given
        * @param array $aListEmail: list of mails from the group where we want to share the folder, Everybody from the group is able to see it.
        * @param string $sPermission: it establishes the permission given for the folder, it could be "r" (read) or "rw" (read/write)
        * @throws Exception when error is found.
        * @return bool true /false specifying if the library was shared successfuly to the group.
        */

        public function shareLibraryToGroup($sIdRepo,$iIdGroup,$aListEmail,$sPermission){
    
	    $sListMails="";
	    if (is_array($aListEmail) && !empty($aListEmail))
	        $sListMails = implode(", ", $aListEmail);
	    else
	        throw new Exception ("The param passed as parameter must be an array containing mails",Exception::BAD_REQUEST);
	      
	    if ($sPermission != "rw" && $sPermission != "r")
	       throw new Exception ("Permissions given are wrong",Exception::BAD_REQUEST);

	    if(is_integer($iIdGroup))
	        $sIdGroup = (String) $iIdGroup;
	    else
	        throw new Exception ("Wrong parameter passed as identifier for group, it must be an integer",Exception::BAD_REQUEST);
	      
	    if(!is_string($sIdRepo))
	        throw new Exception ("The identifier of the repo or library must be an string",Exception::BAD_REQUEST);
	      
	    $aData = [];
	    $url = $this->sUrl."/api2/shared-repos/".urlencode($sIdRepo)."/?share_type=group&user=" .urlencode($sListMails)."&group_id=".urlencode($sIdGroup)."&permission=".urlencode($sPermission);
	    $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_INFILESIZE,-1);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, false);
            $this->getCurlClient()->put($url,$aData);

	    if( $this->getCurlClient()->response === "success")
	        return true;
	    else
	        return false;   
        }
        
        /**
         * shareLibraryPersonally unshares a personal folder from a user/s given.
         * @param string $sIdRepo: identifier of the library we want to unshare
         * @param array $iIdGroup: no necessary param, by default takes value zero.
         * @param array $aListEmails: mail address of the users where we want to share this folder as personal
         * @param string $sPermission: it establishes the permission given for the folder, it could be "r" (read) or "rw" (read/write)
         * @throws Exception when error is found
         * @return bool true /false specifying if the library has been unshared
         */
        
        public function shareLibraryPersonally($sIdRepo, $iIdGroup, $aListEmail, $sPermission) {
            if (is_array($aListEmail)) {
                $sListMails = implode(",", $aListEmail);
            } else {
                throw new Exception("The param passed as parameter must be an array containing mails", Exception::BAD_REQUEST);
            }

            if ($sPermission != "rw" && $sPermission != "r") 
                throw new Exception("Permissions given are wrong", Exception::BAD_REQUEST);
            

            if(!is_string($sIdRepo)) 
                throw new Exception ("The identifier of the repo or library must be an string", Exception::BAD_REQUEST);
            

            $aData = [];
            $url = $this->sUrl."/api2/shared-repos/".urlencode($sIdRepo)."/?share_type=personal&user=".urlencode($sListMails)."&permission=".urlencode($sPermission);
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_INFILESIZE,-1);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, false);
            $this->getCurlClient()->put($url,$aData);

            $bResult = ($this->getCurlClient()->response === "success") ? true : false;

            return $bResult;
        }

      /**
       * unshareLibraryPersonally unshares a personal folder from a user given.
       * @param string $sIdRepo: identifier of the library we want to unshare
       * @param string $sEmailAddress: identifier of the user we want to unshare the personal folder.
       * @throws Exception when error is found
       * @return bool true /false specifying if the library has been unshared
       */
        
  	public function unshareLibraryPersonally($sIdRepo,$sEmailAddress)
        {
            if (!is_string($sIdRepo)) 
                throw new Exception ("The identifier of the repo or library must be an string",Exception::BAD_REQUEST);
           
            $aData = [];
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_POSTFIELDS,$aData);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);     
            $url = $this->sUrl."/api2/shared-repos/".$sIdRepo."/?share_type=personal&user=".$sEmailAddress."&group_id=0";
            $this->getCurlClient()->delete($url,$aData);
            $bResult = false;
            $bResult = ($this->getCurlClient()->response === 'success') ? true : false;

            return $bResult;     
  	}

      /**
       * unshareLibraryToGroup unshares a folder already created from a group, making it dissapear from the group.
       * @param string $sIdRepo: identifier of the library we want to unshare
       * @param integer $iIdGroup: identifier of the group where is the folder located
       * @throws Exception when error is found
       * @return bool true /false specifying if the library has been unshared  from the group 
       */

   	public function unshareLibraryToGroup($sIdRepo, $iIdGroup) {
  	
            if(!is_string($sIdRepo))
               throw new Exception ("The identifier of the repo or library must be an string",Exception::BAD_REQUEST);

            if(is_integer($iIdGroup))
                $sIdGroup = (String) $iIdGroup;
            else
               throw new Exception ("Wrong parameter passed as identifier for group, it must be an integer",Exception::BAD_REQUEST);

            $url = $this->sUrl."/api2/shared-repos/".urlencode($sIdRepo)."/?share_type=group&group_id=".urlencode($sIdGroup);

            $aData = [];
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->delete($url);

            if ($this->getCurlClient()->response === "success") {
                return true;
            } else {
                return false;
            }	
  	}


      /**
       * deleteLibrary calls to a REST API to delete either a library or a repo from the seafile by providing the repo's identifier
       * @param string $sIdRepo: relative to the the identifier of the repo we want to delete
       * @throws Exception when error is found
       * @return bool true /false specifying if the library was deleted.
       */

        public function deleteLibrary($sIdRepo){
            $url = $this->sUrl . sprintf("/api2/repos/%s/",$sIdRepo);
            $aData = [];
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_POSTFIELDS,$aData);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->delete($url,$aData);
            $bResult = false;
            $bResult = ($this->getCurlClient()->response === 'success') ? true : false;

            return $bResult;
        }

      /**
       * getListLibraries calls to a REST API to get a full list of libraries already created in SeaFile
       * @return array of objects 
       */
    
        public function getListLibraries(){
            $url = $this->sUrl."/api2/repos/";
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $this->getCurlClient()->setOpt(CURLOPT_HTTPGET,1);
            $this->getCurlClient()->setOpt(CURLOPT_CUSTOMREQUEST,'GET');
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->get($url);
            if (($this->getCurlClient()->response!="") && ($this->getCurlClient()->response!=null)) {
                return $this->getCurlClient()->response;
            } else {
                return [];	
            }
        }

      /**
       * createGroup  calls to a REST API function to be able to add a new group on seafile by providing the group's name
       * @param string $sGroupName: relative to the the name given for the group.
       * @throws Exception when error is found
       * @return int/null specifying the group id that has been created 
       */

        public function createGroup($sGroupName){
        
            $url = $this->sUrl."/api2/groups/";
            $aData = array('group_name' => $sGroupName);
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_INFILESIZE,-1);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, false);
            $this->getCurlClient()->put($url,$aData);

            if (($this->getCurlClient()->response->group_id!=null) && (!empty($this->getCurlClient()->response->group_id))) {
                return $this->getCurlClient()->response->group_id;
            } else {
                return null;
            }
        }
        
        
        /**
          * createGroup  calls to a REST API function to be able to remove a group on seafile
          * @param int $iIdGroup: relative to the the name given for the group.
          * @throws Exception when error is found
          * @return bool true or false 
          */
        public function deleteGroup($iIdGroup){
            
            if (is_integer($iIdGroup)) 
                $sIdGroup = (String) $iIdGroup;
            else 
               throw new Exception ("Wrong parameter passed as identifier for group"); 
            
            $url = $this->sUrl."/api2/groups/".$sIdGroup."/";
            $aData = [];
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_POSTFIELDS,$aData);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->delete($url,$aData);
            $bResult = ($this->getCurlClient()->response === "success") ? true : false;
            
            return $bResult;
        }


      /**
       * getGroupList calls to a REST API to get a full list of groups from seafile
       * @return array of objects stdclass 
       */

        public function getGroupList(){

            $url = $this->sUrl."/api2/groups/";
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, true);
            $this->getCurlClient()->setOpt(CURLOPT_HTTPGET,1);
            $this->getCurlClient()->setOpt(CURLOPT_CUSTOMREQUEST,'GET');
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->get($url);

            $aResult=[];
        
            if (!empty($this->getCurlClient()->response) && !is_null($this->getCurlClient()->response)) {
                $aResult = $this->getCurlClient()->response; 
                return $aResult->groups;
            } else {
                return $aResult;
            }
        }

       /**
        * addMemberToGroup($iIdGroup,$sEmailMember) calls to a REST API function to be able to add a new member already registered on seafile to a group
        * @param int $iIdGroup: relative to the the identifier of the group where the user will be added
        * @param string $sEmailMember: relative to the the mail address of the user that we want to add to the group. Please make sure this email is already registered on seafile, otherwise an exception will be raised
        * @throws Exception when error is found
        * @return bool true /false specifying if the member has been added successfully or not.
        */

        public function addMemberToGroup($iIdGroup,$sEmailMember){

            if(is_integer($iIdGroup))
                $sIdGroup = (String) $iIdGroup;
            else
                throw new Exception ("Wrong parameter passed as identifier for group, it must be an integer",Exception::NOT_FOUND);

            $url = $this->sUrl."/api2/groups/".$sIdGroup."/members/";
            $aData = [];
            $aData["user_name"] = $sEmailMember;
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_INFILESIZE,-1);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->setOpt(CURLOPT_FOLLOWLOCATION, false);
            $this->getCurlClient()->put($url,$aData);

            if ($this->getCurlClient()->response->success === true)
                return true;
            else
                return false;
      
        }

       /**
        * deleteMemberFromGroup($iIdGroup,$sEmailMember) calls to a REST API function to be able to delete a member from a group given 
        * @param int $iIdGroup: relative group's identifer where the user will be removed from.
        * @param string $sEmailMember: relative to the the mail address to be deleted from the group
        * @throws Exception when error is found
        * @return bool true /false specifying if the member has been deleted. 
        */
   
        public function deleteMemberFromGroup($iIdGroup,$sEmailMember){

            if(is_integer($iIdGroup))
                $sIdGroup = (String) $iIdGroup;
            else
                throw new Exception ("Wrong parameter passed as identifier for group, it must be an integer",Exception::NOT_FOUND);

            $url = $this->sUrl."/api2/groups/".$sIdGroup."/members/";

            $aData=[];
            $aData["user_name"] = $sEmailMember;
            $this->getCurlClient()->setOpt(CURLOPT_HTTPHEADER ,array('Authorization: Token '.$this->getAuthToken()));
            $this->getCurlClient()->setOpt(CURLOPT_POSTFIELDS,$aData);
            $this->getCurlClient()->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->getCurlClient()->delete($url,$aData);
            $bResult = ($this->getCurlClient()->response->success === true) ? true : false;
            
            return $bResult;
        }
}
