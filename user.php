<?php
class User extends CI_Controller
{
	public $appKey;
	public $appSecret;
	public $callbackUrl;

    function __construct()
    {
        parent::__construct();
		 $this->load->helper('url'); 
		 
		 $this->appKey ="";
		 $this->appSecret ="";
		 $this->callbackUrl = "";
	}
function index(){
           
            echo '<form id="linkedin_connect_form" action="initiate" method="post">';
            echo '<input type="submit" value="Login with LinkedIn" />';
            echo '</form>';
  
        }
        
        function initiate(){
                  // setup before redirecting to Linkedin for authentication.
                 $linkedin_config = array(
                     'appKey'       => $this->appKey,
                     'appSecret'    => $this->appSecret,
                     'callbackUrl'  => $this->callbackUrl
                 );
                
				$this->session->unset_userdata('linked_token');
				$this->session->unset_userdata('authorized');
				
                $this->load->library('linkedin', $linkedin_config);
                $this->linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
                $token = $this->linkedin->retrieveTokenRequest();
                
                $this->session->set_flashdata('oauth_request_token_secret',$token['linkedin']['oauth_token_secret']);
                $this->session->set_flashdata('oauth_request_token',$token['linkedin']['oauth_token']);
                $this->session->set_userdata("linked_token",$token['linkedin']);
				
				
                $link = "https://api.linkedin.com/uas/oauth/authorize?oauth_token=". $token['linkedin']['oauth_token'];  
                redirect($link);
 }
        
        function cancel() {
          echo 'Linkedin user cancelled login';            
        }
        
        function logout() {
                session_unset();
                $_SESSION = array();
				redirect("/user/");
                
        }
        
 function data(){
   
                    $linkedin_config = array(
                     'appKey'       => $this->appKey,
                     'appSecret'    => $this->appSecret,
                     'callbackUrl'  => $this->callbackUrl
                 );
                
				
				$linked_token =  $this->session->userdata('linked_token');
				$this->load->library('linkedin', $linkedin_config);
				
				$oauth_token = $this->session->flashdata('oauth_request_token');
                $oauth_token_secret = $this->session->flashdata('oauth_request_token_secret');
  
                $oauth_verifier = $this->input->get('oauth_verifier');
				$oauth_token =  $linked_token['oauth_token'];
				$oauth_token_secret = $linked_token['oauth_token_secret'];
				
				
				$response = $this->linkedin->retrieveTokenAccess($oauth_token, $oauth_token_secret, $oauth_verifier);
				$this->session->set_userdata('linkedin_access',$response['linkedin']);
				
				$this->session->set_userdata('authorized',TRUE);
				redirect("/user/searchpeople/");    
        }  

	function searchpeople()
	{
		$linkedin_config = array(
                     'appKey'       => $this->appKey,
                     'appSecret'    => $this->appSecret,
                     'callbackUrl'  => $this->callbackUrl
                 );
				 
		 if($this->session->userdata('authorized')) {
		 			$this->load->library('linkedin', $linkedin_config);
			 		$this->linkedin->setTokenAccess($this->session->userdata('linkedin_access'));
			    	$this->linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON);
				    $response = $this->linkedin->searchPeople("?keywords=Developer");
					 /*********Print The Member Lists***********/
					 $members_array = array();
						 if($response['success'] === TRUE)
				 		{
							$result = $response['linkedin'];
							$json = json_decode($result, true);
						 	if(count($json['people']['values']))
								foreach($json['people']['values'] as $item) 
								{
									array_push($members_array,$item["id"]);
									//echo $item["id"]."<br>";
								}
				 		}

					 $response = $this->linkedin->connections('~/connections:(id,first-name,last-name,picture-url,site-Standard-Profile-Request)');
            		 if($response['success'] === TRUE) 
            		 {
            		 	$resultall = $response['linkedin'];
						$jsonall = json_decode($resultall, true);
             			foreach($jsonall['values'] as $connection) 
             			{
             				if(in_array($connection['id'], $members_array))
								{
									if(array_key_exists('pictureUrl',$connection))	
									echo "<table><tr><td><img src='".$connection['pictureUrl']."'></td><td>".$connection['firstName']."&nbsp;".$connection['lastName']."</td><td><a href='".$connection['siteStandardProfileRequest']['url']."' target='_blank'>View Profile</a></td></tr><table>";
									else
									echo "<table><tr><td>No Image</td><td>".$connection['firstName']."&nbsp;".$connection['lastName']."</td><td><a href='".$connection['siteStandardProfileRequest']['url']."' target='_blank'>View Profile</a></td></tr><table>";
								}	
            			}
					}
						 
					/********************/
				 
                echo '<br><br>';
                echo '<form id="linkedin_connect_form" action="../logout" method="post">';
                echo '<input type="submit" value="Logout from LinkedIn" />';
                echo '</form>';
                
                } 
	}
}




/* End of file user.php */
/* Location: ./application/controllers/user.php */
