<?php

/**
 * @author Ashish Tiwari
 *
 */
App::uses('Component', 'Controller');

class CommonComponent extends Component {

	var $components = array("Auth","Session");
	
	function Query($table=NULL,$type=NULL,$array) 
	{					 
	 	$this->$table = ClassRegistry::init($table);	 		
	 	$result = $this->$table->find($type,$array);
//		echo $this->sql();
		return $result;
    }
	
   function mask_email( $email, $mask_char, $percent=50 )
	{
	
		list( $user, $domain ) = preg_split("/@/", $email );
	
		$len = strlen( $user );
	
		$mask_count = floor( $len * $percent /100 );
	
		$offset = floor( ( $len - $mask_count ) / 2 );
	
		$masked = substr( $user, 0, $offset )
				.str_repeat( $mask_char, $mask_count )
				.substr( $user, $mask_count+$offset );
	
	
		return( $masked.'@'.$domain );
	} 
	
	function hide_phone($phone) 
	{	
		if(!empty($phone) and strlen($phone)>9)		
		{
			$first=substr($phone, 0, -7);
			$second='*****';
			$third=substr($phone, 8, 2);
			$star=$first.$second.$third;
						
			return $star;
		}	
	}
	
	public function sms($mobile,$message)
    {
        //Comment below line when using online
		//return true;
		
        $ch = curl_init('http://www.smsjust.com/sms/user/urlsms.php');        
        $username = "trackproperty";
        $password = "track999";
        $destination = $mobile;
        $source    = 'TrackP';    
        
        $content =          
        'username='.rawurlencode($username).
        '&pass='.rawurlencode($password).
        '&dest_mobileno='.rawurlencode($destination).
        '&senderid='.rawurlencode($source).
        '&message='.rawurlencode($message).
		'&response=Y';
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec ($ch);
        curl_close ($ch);
        return $output;      
        
    }   

	/*********************************************************************/	
	/*
	@ Mohammad Masood
	@ retun an opt code with specified length
	@ For generating a random code
	@ 04-05-2016
	*/
	public function otpCode($length = 6) 
	{
		$characters = '0123456789';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	function isValidSupervisor($operator_id=NULL){
	
		$data=$this->Query("User","first",
		array(
			'conditions'=>array(
				'User.id !='=>BOOL_FALSE,
				'User.id'=>$operator_id,
				'User.is_deleted'=>BOOL_FALSE,
				'User.supervisor_id'=>$this->Session->read("Auth.User.id"),
			)
			)
		);
		if(!empty($data))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
		
	function PackageDetail($id=NULL){
	
		$data=$this->Query("User","first",
		array(
			'conditions'=>array(
				'User.id !='=>BOOL_FALSE,
				'User.id'=>$id,
				'User.is_deleted'=>BOOL_FALSE,
				'User.role_id'=>CUSTOMER_ROLE_ID,			
			)
			)
		);
		if(!empty($data))
		{
				
			if($data['User']['package_id']==FREE_PACKAGE_ID)
			{				
				$package_detail=$this->Query("CustomerPackage","first",
				array(
				'conditions'=>array(
				'CustomerPackage.id !='=>BOOL_FALSE,
				'User.is_deleted'=>BOOL_FALSE,
				'CustomerPackage.user_id'=>$id,			
				),
				'order'=>array('CustomerPackage.id'=>'desc')				
				));
				
				if(!empty($package_detail))
				{					
					$date1=date_create(date('Y-m-d',strtotime($package_detail['CustomerPackage']['created'])));
					$date2=date_create(date('Y-m-d'));
					
					//echo '<pre>';				
					$diff=date_diff($date1,$date2);				
					$difference=$diff->format('%R%a');
					
					if($difference <= DELETE_NOTIFY_BEFORE and $difference > 0)
					{
						return $this->Session->setFlash('Your account will be deleted after '.abs($difference).' days , please upgrade to paid account','error');	
						return false;
						
					}
					else if($difference >= DELETE_NOTIFY_BEFORE and $difference > 0)
					{
						return $this->Session->setFlash('Please upgrade to paid account , otherwise your account will be deleted shortly ','error');
						return false;
						
					}
					/*
					else if($difference <=0 )
					{
						return $this->Session->setFlash('Your account will be deleted soon, please upgrade to paid account','error');
						return false;
						
					}
					*/
					
					//echo $diff->format('%R%a');
					//echo '</pre>';
					//echo 'Free';
					//exit;	
				}
				
			}

		}
				
	}
	
}
