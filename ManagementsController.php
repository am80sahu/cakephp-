<?php
/**
 * @name       ManagementController.php
 * @class      ManagementController
 * @category   Users
 * @package    Users
 * @date       12 November 2016
 */
 
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::import('Controller', 'Commons');
class ManagementsController extends AppController 
{
	public $helpers = array(
        'Item'
    );
  
   // var $components = array('Auth', 'Email');
    public $components = array('Paginator','Files','Img');
	public $uses = array('Upload','State','City','User');
     
    public function beforeFilter() {
        parent::beforeFilter();
		$authAllowedActions = array(
		'admin_login',
		'admin_logout',
		'admin_findAccount',										
		'admin_sendPasswordResetLink',
		'admin_generateRandomString',
		'admin_resetPassword',
		'admin_changePassword',
		'admin_unique_email',
		'admin_unique_mobile',
		'admin_getStockData',
		'loadMoreStock'
		);		
		
        $this->Auth->allow($authAllowedActions);        		
		if (!in_array($this->Auth->user('role_id'), array(ADMIN_ROLE_ID,CO_ADMIN_ROLE_ID))) 
		{
            $this->Auth->logout();
        }

        //set layout based on user session
        if ($this->Auth->user()) {
            $this->layout = 'admin/inner';
        } else {
            $this->layout = 'admin/outer';
        }
    }
	
	/**************************login******************************/
	
	public function admin_index() 
	{
		
	}
	
    //function for admin login   
    public function admin_login() 
	{
	
		$this->layout = 'admin/outer';		
		if ($this->request->is('post'))
		 {	 	
			
				$email = !empty($this->request->data ['User'] ['email']) ? trim($this->request->data ['User'] ['email']) : null;
				$password = !empty($this->request->data ['User'] ['password']) ? trim(AuthComponent::password($this->request->data['User']['password'])) : null;
				
				$type = 'first';
				$conditions = array(
					'User.email' => $email,
					'User.password' => $password,
					'User.role_id' => array(ADMIN_ROLE_ID,CO_ADMIN_ROLE_ID),
					'User.is_active' => BOOL_TRUE,
					'User.is_deleted' => BOOL_FALSE
				);
				$fields = NULL;
				$contain = NULL;
				$order = NULL;
				$group = NULL;
				$recursive = -1;
				$this->loadModel('User');
                $this->loadModel('UserProfile');				
				
				$userData = $this->User->getUserData($type, $conditions, $fields, $contain, $order, $group, $recursive);
	
				#get the user information
				if (!empty($userData)) {				
					$userArray['User']['email'] = $email;
					$userArray['User']['password'] = $password;
					
					$this->loadModel('UserSession');
					$logged_users=$this->UserSession->find('count',
					array('conditions'=>array(
						'UserSession.id !='=>BOOL_FALSE,
						'UserSession.is_deleted'=>BOOL_FALSE,
						'UserSession.is_active'=>BOOL_TRUE,
						'UserSession.user_id'=>$userData['User']['id'],
						'UserSession.role_id'=>ADMIN_ROLE_ID,
						'UserSession.current_time >='=>date('H:i:s',time() - 30),
						'DATE(UserSession.created)'=>date('Y-m-d'),
						'UserSession.logout_time'=>NULL,
						'UserSession.is_logged_out'=>BOOL_FALSE,

						),
					'recursive'=>-1
						
					));	
					
					if ($this->Auth->login()) {
						
						$UserProfile=$this->UserProfile->find('first',array('conditions'=>array('UserProfile.id !='=>BOOL_FALSE,'UserProfile.is_active !='=>BOOL_FALSE,'UserProfile.is_deleted !='=>BOOL_TRUE,'UserProfile.id'=>$userData['User']['user_profile_id'])));
						$this->Session->write('UserProfile', $UserProfile);
						$UserProfile=$this->Session->read('UserProfile');
					    							
						$this->User->id = $this->Session->read('Auth.User.id');
						//update below flag
						$saveableArray = array(
							'is_logged_in' => 1,
							'last_login' => date('Y-m-d H:i:s'),
							'ip_address' => trim($this->request->clientIp())
						);
						$this->User->save($saveableArray);
						
						$sess_array=array(
						'user_id'=>$this->Session->read('Auth.User.id'),
						'role_id'=>$this->Session->read('Auth.User.Role.id'),
						'login_time'=>date('H:i:s'),
						'ip_address'=>trim($this->request->clientIp()),
						);						
						$this->UserSession->create();
						$this->UserSession->save($sess_array);
						$this->Session->write('Auth.User.UserSession.id',$this->UserSession->getInsertID());
						
						$this->redirect($this->Auth->redirectUrl());
					}
					
						
				} else {
					$this->Session->setFlash(__("Invalid email address or password"), 'error');
				}
				
		}
    }

		
    //function for admin logout
    public function admin_logout() {
        if ($this->Auth->user('id')) {
            $this->loadModel('User');
			$this->loadModel('UserSession');			
            $this->User->id = $this->Auth->user('id');
            if ($this->User->saveField('is_logged_in', 0)) {
			
				$this->UserSession->id = $this->Session->read('Auth.User.UserSession.id');
				//update below flag
				$sess_array = array(
				'logout_time' => date('H:i:s'),
				'is_logged_out' =>BOOL_TRUE,				
				);
				$this->UserSession->save($sess_array);
				
                $this->Session->destroy('Auth.User');
                $this->redirect($this->Auth->logout());
            }
        } else {
            $this->redirect($this->Auth->logout());
        }
    }


/**********************dashboard***************************/	
    //function for admin dashboard
    public function admin_dashboard() {
		$this->admin_check_login();
		
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
            //get Traveller user count for admin dashboard 
            $type = 'count';
            $conditions = array(
                'User.name !=' => NULL,
                'User.is_active' => BOOL_TRUE,
                'User.is_deleted' => BOOL_FALSE
            );
            $fields = NUll;
            $contain = NULL;
            $order = array('User.name' => 'ASC');
            $group = NULL;
            $recursive = 1;
            $this->loadModel('User');
            $femaleUserCount = $this->User->getUserData($type, $conditions, $fields, $contain, $order, $group, $recursive);
            $this->set(compact('femaleUserCount'));

            //get Service provider user count for admin dashboard 
            $type = 'count';
            $conditions = array(
                'User.name !=' => NULL,
                'User.is_active' => BOOL_TRUE,
                'User.is_deleted' => BOOL_FALSE
            );
			
            $fields = NUll;
            $contain = NULL;
            $order = array('User.name' => 'ASC');
            $group = NULL;
            $recursive = 1;
            $this->loadModel('User');
            $maleUserCount = $this->User->getUserData($type, $conditions, $fields, $contain, $order, $group, $recursive);
            $this->set(compact('maleUserCount'));
		
        } else {
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }

	/**
	Reset Password
	**/
	/**
	Reset Password
	**/
	public function admin_changePassword()
	{
		$this->shop_check_login();								
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,UPDATE_PERMISSION_ID))) 
		{
		
			$this->loadModel('User');		
			
			if($this->Auth->user())	
			{				
				if($this->request->is('post') or $this->request->is('put'))
				{
					
					$old_password = AuthComponent::password($this->request->data['User']['old_password']);
					
					$this->User->id=$this->Auth->user('id');
					$userdata=$this->User->find('first',array('conditions'=>array(
					'User.id !='=>BOOL_FALSE,
					'User.role_id'=>ADMIN_ROLE_ID,
					'User.is_deleted'=>BOOL_FALSE,
					'User.is_active'=>BOOL_TRUE,
					'User.password'=>$old_password					
					),
					'recursive'=>-1
					));
					
					if(!empty($userdata))
					{
						
						if(isset($this->request->data['User']['new_password']) and !empty($this->request->data['User']['confirm_password']))
						{	
					
							if($this->request->data['User']['new_password']==$this->request->data['User']['confirm_password'])		
							{		
								$this->request->data['User']['password']=$this->request->data['User']['new_password'];					
								$this->request->data['User']['password_reset_token']=NULL;
								$this->request->data['User']['token_created_at']=NULL;				
								if($this->User->save($this->request->data['User']))
								{
								$this->Session->setFlash('Password reset successfully.','success');
								return $this->redirect(array('controller'=>'managements','action' => 'changePassword','admin'=>true,'ext'=>URL_EXTENSION));
								}
								else
								{
									$this->Session->setFlash('Error ! Password cannot be changed .try again ','error');
								}
							}	
							else
							{
							$this->Session->setFlash('Password does not match ','error');
							}		
						}	
				
					}
					else
					{
							
						$this->Session->setFlash('Your old password is incorrect.','error');
					}	
				}				
			}
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}	
	}
	
		
	public function admin_findAccount() {

	 $this->loadModel('User');
	 
		 if($this->request->is('post')) {	 	
		 
			 $userdata=$this->User->find('first',array(		
			 'conditions'=>array(
			 'email'=>$this->request->data['User']['email'],
			 'role_id'=>ADMIN_ROLE_ID,
			 'is_deleted'=>BOOL_FALSE,			 
			 ),			 
			 'recursive'=>-1	 
			 ));   		
			 
			 if(!empty($userdata)) {
			 
				$this->set(compact('userdata'));
				return $this->redirect(array('controller'=>'managements','action' => 'sendPasswordResetLink','admin'=>true,'ext'=>URL_EXTENSION,$this->Encryption->encrypt($userdata['User']['email'])));
				 
			 } else {
				$this->Session->setFlash('User Not Found','error');
			 }
		}	 
		
	}	
	
	public function admin_sendPasswordResetLink($email)
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		
		$this->loadModel('User');
		$email=$this->Encryption->decrypt($email);		
		$userdata = $this->User->findByEmailAndRoleId($email,ADMIN_ROLE_ID);		
		if(empty($userdata))
		{
			throw new NotFoundException('Invalid Admin USer');
		}
		
		$this->User->id=$userdata['User']['id'];
		$token=$this->generateRandomString($length = 8);
		$this->request->data['User']['password_reset_token']=$token;
		$this->request->data['User']['token_created_at']=date('Y-m-d H:i:s');
		
		if($this->User->save($this->request->data))
		{
			$link="<div align=\"center\" style=\"padding:10px;\"><span style=\"font-family:georgia,serif;\"><span style=\"font-size: 20px;\"><strong><a href=\"".Router::fullbaseUrl().Router::url(array('controller'=>'managements','action'=>'resetPassword','admin'=>true,'ext'=>URL_EXTENSION,base64_encode($email),base64_encode($token)))."\" style=\"padding:10px;color:#990000;background: #CEB43C;text-decoration:none;\">Reset Password</a></strong></span></span></div>";			

			
			$username=$userdata['User']['name'];
			$email='trackproperty@adsoftech.com';
			
			$to_email=$userdata['User']['email'];
			$templateId = FORGOT_PASSWORD_EMAIL_TEMPLATE_ID;
			$emailData ['receiver_email'] = !empty($to_email) ? trim($to_email) : NULL;
			$emailData ['sender_email'] = !empty($email) ? trim($email) : NULL;
			
			$emailData ['CUSTOMER_NAME'] = !empty($username) ? trim($username) : NULL;
			$emailData ['PASSWORD_RESET_LINK'] = !empty($link) ? trim($link) : NULL;
			
			$emailResult = $this->Email->ForgotPasswordMail($templateId, $emailData);
				
			$this->Session->setFlash('Password reset link has been sent to your email. Please check your email ', 'success');
			return $this->redirect(array('controller'=>'managements','action' => 'login','admin'=>true,'ext'=>URL_EXTENSION));
		}
		
	}	
	
	/*********************************************************************/	
	/*
	@ Mohammad Masood
	@ generateRandomString($length)
	@ retun string with specified length
	@ For generating a random password
	@ 04-05-2016
	*/
	public function generateRandomString($length = 8) 
	{
		
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	/**
	Reset Passwor
	**/
	public function admin_resetPassword($email,$token)
	{
		
		$this->loadModel('User');		
		$email=base64_decode($email);
		$token=base64_decode($token);	
		$userdata = $this->User->findByEmailAndPasswordResetToken($email,$token);
		if(!empty($userdata))
		{			
			$user_id=$userdata ['User']['id'];
			$this->request->data['User']['id']=$user_id;
			$this->set(compact('user_id'));
		}
		else
		{
			$this->Session->setFlash('Invalid Password Token ','error');
		}
		
		if($this->request->is('post') or $this->request->is('put'))
		{
		
			if(isset($this->request->data['User']['password']) and !empty($this->request->data['User']['password']))
			{	
			if($this->request->data['User']['password']==$this->request->data['User']['confirm_password'])		
				{
					$this->User->id=$this->request->data['User']['id'];
					$this->request->data['User']['password_reset_token']=NULL;
					$this->request->data['User']['token_created_at']=NULL;				
					
					if($this->User->save($this->request->data['User']))
					{
						$this->Session->setFlash('Password reset successfully. Now you can login here. ','success');
						return $this->redirect(array('controller'=>'managements','action' => 'login','admin'=>true,'ext'=>URL_EXTENSION));
					}
					else
					{
						$this->Session->setFlash('Error ! Password cannot be changed .try again ','error');
					}
				}	
				else
				{
					$this->Session->setFlash('Password does not match ','error');
				}		
			}
		}	
	}
	

	//////////////// User Management  /////////////////////////////	
	public function admin_usersList() 
	{
		$cond=array();
		$this->admin_check_login();		
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('User');	
							
			if(isset($this->request->data['User']))
			{					
				$this->Session->write('UserSearch',$this->request->data['User']);
			}
			else
			{	
				$this->request->data['User']=$this->Session->read('UserSearch');		
			}		
			if(isset($this->request->data['User']))				
			{			
				if(isset($this->request->data['User']['name']) and !empty($this->request->data['User']['name']))				
				{
					$cond['OR']['User.name LIKE']=$this->request->data['User']['name']."%";
					$cond['OR']['User.middle_name LIKE']=$this->request->data['User']['name']."%";
					$cond['OR']['User.last_name LIKE']=$this->request->data['User']['name']."%";
					$cond['OR']['User.email LIKE']=$this->request->data['User']['name']."%";
					$cond['OR']['User.mobile_no LIKE']=$this->request->data['User']['name']."%";
				}				
			}		
						
				$conditions = array(
					'User.id !=' => BOOL_FALSE,
					'User.is_deleted' => BOOL_FALSE,
					'User.role_id !=' => CUSTOMER_ROLE_ID
				);
				
				$conditions=array_merge($conditions,$cond);		
				
				$this->Paginator->settings = array(
					'User' => array(
						'conditions' => $conditions,
						'order' => array('User.id' => 'DESC'),
						'limit' => PAGINATION_LIMIT,
						'recursive' =>1
				));
				$users = $this->Paginator->paginate('User');
				$this->set(compact('users'));		
				
				
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/**
     * Function to view a single user detail. 
     * @created : Mohammad Masood
     * @name    : admin_viewUser
     * @acces   : public 
     * @param   : null
     * @return  : void
     * @created : 15 November 2016
     * @modified: 15 November 2016

     */
    public function admin_viewUser($id=NULL) 
	{
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('User');
			$id = $this->Encryption->decrypt($id);
			$this->User->id = $id;
			if(!$this->User->exists())
			{
				throw new NotFoundException("User Not Found");
			}
			$conditions = array(
					'User.id !=' => BOOL_FALSE,
					'User.id' => $id,
					'User.is_deleted' => BOOL_FALSE,					
			);			
		
            $user = $this->User->find('first',
				array(
                    'conditions' => $conditions,                    
            	));
			
            $this->set(compact('user'));
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	
	public function admin_addUser() 
	{
		$this->admin_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{		
			$this->loadModel('User');		
			$this->loadModel('State');		
			$this->loadModel('City');		
			$this->loadModel('Role');		
			$this->loadModel('Location');		
			
			$shops=$this->Location->getShopList();
			$this->set(compact('shops'));
			
			$cities=array();
			$this->set('cities',$cities);
			
			if ($this->request->is('post')) 
			{	
				$cities=$this->City->find('list',array(
				'conditions'=>array(
				'City.id !='=>BOOL_FALSE,				
				'City.is_deleted'=>BOOL_FALSE,
				'City.is_active'=>BOOL_TRUE,
				'City.state_id'=>$this->request->data['User']['state'],
				'City.is_district'=>BOOL_TRUE,
				)
				));			
				echo $this->set(compact('cities'));	
				
				if(!empty($this->request->data['User']['photo']['name']))
				{						
					$this->Img = $this->Components->load('Img');			
					$newName = strtotime("now");
					$rnd = rand(5, 15);
					$newName = $newName.$rnd;
					$ext = $this->Img->ext($this->request->data['User']['photo']['name']);			
					
					$filesize=$this->request->data['User']['photo']['size'];
					$max_size = 1024*1024;
					
					if($filesize > $max_size){
						$this->Session->setFlash("The file size must be less than 2 MB","error");
					}
					
					$origFile = $newName . '.' . $ext;
					$dst = $newName .  '.'.$ext;	
					$targetdir = WWW_ROOT . 'assets/images/users';			
					echo $upload = $this->Img->upload($this->request->data['User']['photo']['tmp_name'], $targetdir, $origFile);		
			
					if($upload == 'Success') 
					{	
						$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 'assets/images/users/thumbs/', $dst, 400, 400, 1, 0);
						$this->request->data['User']['photo'] = $dst;
					}
					else 
					{
						$this->Session->setFlash($upload, 'error');
						$this->request->data['User']['photo'] = '';
						
					}
				}
				else 
				{
					$this->request->data['User']['photo'] = '';
				}
								
				$this->request->data['User']['registered_by']=$this->Auth->User('id');				
				$this->request->data['User']['dob']=empty($this->request->data['User']['dob'])?'':date('Y-m-d',strtotime($this->request->data['User']['dob']));
				
				$this->User->create();			
				if ($this->User->save($this->request->data)) 
				{
					$id = $this->User->getInsertID();
					$user_id = 'CB'.sprintf('%04d', $id);
					$this->User->id=$id;
					
					$this->User->saveField('user_id',$user_id);
					
					$username=$this->request->data['User']['name'];
					$user_role = $this->Role->findById($this->request->data['User']['role_id']);		
					$role_name = $user_role['Role']['name'];			
					
					$email='trackproperty@adsoftech.com';
					$password = $this->request->data['User']['password'];
					
					$login_link=Router::fullbaseUrl().Router::url(array('controller'=>'managements','action'=>'login','admin'=>true,'ext'=>URL_EXTENSION));
										
					$to_email=$this->request->data['User']['email'];
					$templateId = USER_REG_EMAIL_TEMPLATE;
					$emailData ['receiver_email'] = !empty($to_email) ? trim($to_email) : NULL;
					$emailData ['sender_email'] = !empty($email) ? trim($email) : NULL;
					
					$emailData ['REG_NAME'] = !empty($username) ? trim($username) : NULL;
					$emailData ['USER_ROLE'] = !empty($role_name) ? trim($role_name) : NULL;
					$emailData ['USER_NAME'] = !empty($to_email) ? "Username : ".trim($this->request->data['User']['email']) : NULL;
					$emailData['PASSWORD'] = !empty($password) ? "Password : ".trim($password) : NULL;
					$emailData ['LOGIN_LINK'] = !empty($login_link) ? trim($login_link) : NULL;
					
					$emailResult = $this->Email->UserRegistrationMail($templateId, $emailData);
					
					$this->Session->setFlash('User has been saved', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'usersList','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('User could not be saved. Please, try again.', 'error');
				}
			}	
			
			$roles=$this->Role->find('list',array(
				'conditions'=>array(
				'Role.id NOT IN'=>array(BOOL_FALSE,CUSTOMER_ROLE_ID,CO_ADMIN_ROLE_ID),
				'Role.is_deleted'=>BOOL_FALSE,
				'Role.is_active'=>BOOL_TRUE,
				)
				));			
			echo $this->set(compact('roles'));
			
			$states=$this->State->find('list',array(
				'conditions'=>array(
				'State.id !='=>BOOL_FALSE,
				'State.is_deleted'=>BOOL_FALSE,
				'State.is_active'=>BOOL_TRUE,
				)
				));			
			echo $this->set(compact('states'));	
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	
	public function admin_editUser($id = null) 
	{
		$this->admin_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,UPDATE_PERMISSION_ID))) 
		{		
		
			$this->loadModel('User');		
			$this->loadModel('State');			
			$this->loadModel('City');	
			$this->loadModel('Role');	
			$this->loadModel('Location');	
			$id = $this->Encryption->decrypt($id);				
			$this->User->id=$id;		
			if (!$this->User->exists()) 
			{
				throw new NotFoundException('User Not Found');
			}
			
			
				$shops=$this->Location->getShopList();
			$this->set(compact('shops'));
			if ($this->request->is('post') || $this->request->is('put')) 
			{
				
				if(!empty($this->request->data['User']['photo']['name']))
				{						
					$this->Img = $this->Components->load('Img');			
					$newName = strtotime("now");
					$rnd = rand(5, 15);
					$newName = $newName.$rnd;
					$ext = $this->Img->ext($this->request->data['User']['photo']['name']);			
					
					$origFile = $newName . '.' . $ext;
					$dst = $newName .  '.jpg';	
					$targetdir = WWW_ROOT . 'assets/images/users';			
					$upload = $this->Img->upload($this->request->data['User']['photo']['tmp_name'], $targetdir, $origFile);					
					if($upload == 'Success') 
					{	
						$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 'assets/images/users/thumbs/', $dst, 400, 400, 1, 0);
						$this->request->data['User']['photo'] = $dst;
					}
					else 
					{
						$this->request->data['User']['photo'] = '';
					}
				}
				else 
				{
					unset($this->request->data['User']['photo']);
				}
				
				$this->request->data['User']['dob']=empty($this->request->data['User']['dob'])?'':date('Y-m-d',strtotime($this->request->data['User']['dob']));
				
				if ($this->User->save($this->request->data)) 
				{
					$this->Session->setFlash('User detail has been updated', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'usersList','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('User detail could not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->User->find('first', array('conditions' => array('User.id' => $id,'User.is_deleted'=>BOOL_FALSE)));
			}			
			
			$roles=$this->Role->find('list',array(
				'conditions'=>array(
				'Role.id NOT IN'=>array(BOOL_FALSE,CUSTOMER_ROLE_ID,CO_ADMIN_ROLE_ID),
				'Role.is_deleted'=>BOOL_FALSE,
				'Role.is_active'=>BOOL_TRUE,
				)
				));			
			echo $this->set(compact('roles'));
			
			$states=$this->State->find('list',array(
				'conditions'=>array(
				'State.id !='=>BOOL_FALSE,
				'State.is_deleted'=>BOOL_FALSE,
				'State.is_active'=>BOOL_TRUE,
				)
				));			
			echo $this->set(compact('states'));	
			
			$cities=$this->City->find('list',array(
				'conditions'=>array(
				'City.id !='=>BOOL_FALSE,
				'City.state_id'=>$this->request->data['User']['state'],
				'City.is_deleted'=>BOOL_FALSE,
				'City.is_active'=>BOOL_TRUE,
				)
				));			
			echo $this->set(compact('cities'));	
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
		
    }
	
	
	
	public function admin_resetUserSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('UserSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
			
	 public function admin_deleteUser($id = null) 
	 {
	 	
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{	
			$this->autoRender = FALSE;
			$this->layout = FALSE;						
			$this->admin_check_login();	
			
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('User');
			
			$this->User->id = $id;
			if (!$this->User->exists()) 
			{
				throw new NotFoundException('Invalid User');
			}
		   if ($this->User->saveField('is_deleted',BOOL_TRUE)) 
		   {
				$this->User->saveField('is_active',BOOL_FALSE);
				$this->Session->setFlash('User deleted','success');
				 return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('User was not deleted', 'error');
			return $this->redirect($this->referer());
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	public function admin_toggleUserStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('User');		
			$this->User->id = $id;
			
			if (!$this->User->exists()) 
			{
				throw new NotFoundException('Invalid User');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->User->saveField('is_active',$value)) 
		   {	   		
				$this->Session->setFlash('User '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('User was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}	
	
	//////////////// Customer Management  /////////////////////////////	
	public function admin_customersList() 
	{
		$cond=array();
		$this->admin_check_login();		
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('Customer');	
							
			if(isset($this->request->data['Customer']))
			{					
				$this->Session->write('CustomerSearch',$this->request->data['Customer']);
			}
			else
			{	
				$this->request->data['Customer']=$this->Session->read('CustomerSearch');		
			}		
			if(isset($this->request->data['Customer']))				
			{			
				if(isset($this->request->data['Customer']['name']) and !empty($this->request->data['Customer']['name']))				
				{
					$cond['OR']['Customer.name LIKE']=$this->request->data['Customer']['name']."%";
					$cond['OR']['Customer.contact_person LIKE']=$this->request->data['Customer']['name']."%";
					$cond['OR']['Customer.phone_no LIKE']=$this->request->data['Customer']['name']."%";
					$cond['OR']['Customer.email LIKE']=$this->request->data['Customer']['name']."%";
					$cond['OR']['Customer.mobile_no LIKE']=$this->request->data['Customer']['name']."%";
				}				
			}		
						
				$conditions = array(
					'Customer.id !=' => BOOL_FALSE,
					'Customer.is_deleted' => BOOL_FALSE,					
				);
				
				$conditions=array_merge($conditions,$cond);		
				
				$this->Paginator->settings = array(
					'Customer' => array(
						'conditions' => $conditions,
						'order' => array('Customer.id' => 'DESC'),
						'limit' => PAGINATION_LIMIT,
						'recursive' =>1
				));
				$customers = $this->Paginator->paginate('Customer');
				$this->set(compact('customers'));		
				
				
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/**
     * Function to view a single customer detail. 
     * @created : Mohammad Masood
     * @name    : admin_viewCustomer
     * @acces   : public 
     * @param   : null
     * @return  : void
     * @created : 15 November 2016
     * @modified: 15 November 2016

     */
    public function admin_viewCustomer($id=NULL) 
	{
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('Customer');
			$id = $this->Encryption->decrypt($id);
			$this->Customer->id = $id;
			if(!$this->Customer->exists())
			{
				throw new NotFoundException("Customer Not Found");
			}
			$conditions = array(
					'Customer.id !=' => BOOL_FALSE,
					'Customer.id' => $id,
					'Customer.is_deleted' => BOOL_FALSE,					
			);			
		
            $customer = $this->Customer->find('first',
				array(
                    'conditions' => $conditions,                    
            	));
			
            $this->set(compact('customer'));
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	
	public function admin_addCustomer() 
	{		
		$this->admin_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{		
			$this->loadModel('Customer');		
			$this->loadModel('Country');
			$this->loadModel('State');
			$this->loadModel('City');
			
			$states=array();
			$this->set('states',$states);
			
			$cities=array();
			$this->set('cities',$cities);	
						
			if ($this->request->is('post')) 
			{	
				$states=$this->State->find('list',array(
				'conditions'=>array(
				'State.id !='=>BOOL_FALSE,				
				'State.is_deleted'=>BOOL_FALSE,
				'State.is_active'=>BOOL_TRUE,
				'State.country_id'=>$this->request->data['Customer']['country'],
				)
				));			
				
				echo $this->set(compact('states'));
				
				$cities=$this->City->find('list',array(
				'conditions'=>array(
				'City.id !='=>BOOL_FALSE,				
				'City.is_deleted'=>BOOL_FALSE,
				'City.is_active'=>BOOL_TRUE,
				'City.state_id'=>$this->request->data['Customer']['state'],
				'City.is_district'=>BOOL_TRUE,
				)
				));			
				echo $this->set(compact('cities'));
				
				$this->request->data['Customer']['created_by']=$this->Auth->User('id');								
				$this->Customer->create();			
				if ($this->Customer->save($this->request->data)) 
				{
					$this->Session->setFlash('Customer has been saved', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'customersList','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('Customer could not be saved. Please, try again.', 'error');
				}
			}	
			
			$countries=$this->Country->find('list',array(
				'conditions'=>array(
				'Country.id !='=>BOOL_FALSE,
				'Country.is_deleted'=>BOOL_FALSE,
				'Country.is_active'=>BOOL_TRUE,
				)
				));			
			echo $this->set(compact('countries'));	
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	
	public function admin_editCustomer($id = null) 
	{
		$this->admin_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,UPDATE_PERMISSION_ID))) 
		{

			$this->loadModel('Customer');		
			$this->loadModel('Country');
			$this->loadModel('State');
			$this->loadModel('City');
				
			$id = $this->Encryption->decrypt($id);				
			$this->Customer->id=$id;		
			if (!$this->Customer->exists()) 
			{
				throw new NotFoundException('Customer Not Found');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{				
				$this->request->data['Customer']['modified_by']=$this->Auth->User('id');
				if ($this->Customer->save($this->request->data)) 
				{
					$this->Session->setFlash('Customer detail has been updated', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'customersList','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('Customer detail could not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->Customer->find('first', array('conditions' => array('Customer.id' => $id,'Customer.is_deleted'=>BOOL_FALSE)));
			}			
			
			$countries=$this->Country->find('list',array(
			'conditions'=>array(
			'Country.id !='=>BOOL_FALSE,
			'Country.is_deleted'=>BOOL_FALSE,
			'Country.is_active'=>BOOL_TRUE,
			)
			));			
			echo $this->set(compact('countries'));	
			
			$states=$this->State->find('list',array(
			'conditions'=>array(
			'State.id !='=>BOOL_FALSE,				
			'State.is_deleted'=>BOOL_FALSE,
			'State.is_active'=>BOOL_TRUE,
			'State.country_id'=>$this->request->data['Customer']['country'],
			)
			));			
			
			echo $this->set(compact('states'));
			
			$cities=$this->City->find('list',array(
			'conditions'=>array(
			'City.id !='=>BOOL_FALSE,				
			'City.is_deleted'=>BOOL_FALSE,
			'City.is_active'=>BOOL_TRUE,
			'City.state_id'=>$this->request->data['Customer']['state'],
			'City.is_district'=>BOOL_TRUE,
			)
			));			
			echo $this->set(compact('cities'));
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
    }	
	
	
	public function admin_resetCustomerSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('CustomerSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
			
	 public function admin_deleteCustomer($id = null) 
	 {
	 	
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{	
			$this->autoRender = FALSE;
			$this->layout = FALSE;						
			$this->admin_check_login();	
			
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('Customer');
			
			$this->Customer->id = $id;
			if (!$this->Customer->exists()) 
			{
				throw new NotFoundException('Invalid Customer');
			}
		   if ($this->Customer->saveField('is_deleted',BOOL_TRUE)) 
		   {
				$this->Customer->saveField('is_active',BOOL_FALSE);
				$this->Customer->saveField('modified_by',$this->Auth->User('id'));
				$this->Session->setFlash('Customer deleted','success');

				 return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Customer was not deleted', 'error');
			return $this->redirect($this->referer());
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	public function admin_toggleCustomerStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(UPDATE_PERMISSION_ID,DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('Customer');		
			$this->Customer->id = $id;
			
			if (!$this->Customer->exists()) 
			{
				throw new NotFoundException('Invalid Customer');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->Customer->saveField('is_active',$value)) 
		   {	
		   		$this->Customer->saveField('modified_by',$this->Auth->User('id'));   				
				$this->Session->setFlash('Customer '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Customer was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}
	/**
	@ Mohammad Masood
	@ Function to check unique email Id
	@ 06-06-2016
	**/
	public function admin_unique_email()
	{		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('User');
		
        if ($this->request->is('ajax')) 
		{
			
			$count=$this->User->find('count',array(
			'conditions'=>array(
				'User.email'=>$this->request->data['User']['email'],
				'User.is_deleted'=>BOOL_FALSE
				)				
			));
			
			if($count > 0)
			{
				echo 'false';
			}
			else
			{
				echo 'true';			
			}		
			
		}
	}
	/*
	Amit Sahu
	Unique email employee
	31.07.17
	*/
	public function admin_unique_email_emp()
	{		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('User');
		$this->loadModel('Employee');
		
        if ($this->request->is('ajax')) 
		{
			
			$count=$this->User->find('count',array(
			'conditions'=>array(
				'User.email'=>$this->request->data['Employee']['email'],
				'User.is_deleted'=>BOOL_FALSE
				)				
			));
			
			if($count > 0)
			{
				echo 'false';
			}
			else
			{
				$count1=$this->Employee->find('count',array(
				'conditions'=>array(
				'Employee.email'=>$this->request->data['Employee']['email'],
				'Employee.is_deleted'=>BOOL_FALSE
				)				
				));
				if($count1 > 0)
				{
					echo 'false';
				}else{
				echo 'true';	
				}	
			}		
			
		}
	}
	
	/**
	@ Mohammad Masood
	@ Function to check unique mobile number
	@ 06-06-2016
	**/
	public function admin_unique_mobile()
	{
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('User');
		
        if ($this->request->is('ajax')) 
		{			
			$count=$this->User->find('count',array(
			'conditions'=>array(
				'User.mobile_no'=>$this->request->data['User']['mobile_no'],
				'User.is_deleted'=>BOOL_FALSE
				)				
			));
			
			if($count > 0)
			{
				echo 'false';
			}
			else
			{
				echo 'true';			
			}
		}
	}	
	
	
	/////////////////// Product Categories////////////////////////////
	public function admin_productCategories() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('ProductCategory');
		
		
		if(isset($this->request->data['ProductCategory']))
		{					
		$this->Session->write('ProductCategorySearch',$this->request->data['ProductCategory']);
		}
		else
		{	
			$this->request->data['ProductCategory']=$this->Session->read('ProductCategorySearch');		
		}		
		if(isset($this->request->data['State']))				
		{			
			if(isset($this->request->data['ProductCategory']['name']) and !empty($this->request->data['ProductCategory']['name']))				
			{
				$cond['ProductCategory.name LIKE']="%".$this->request->data['ProductCategory']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'ProductCategory.id !=' => BOOL_FALSE,
			'ProductCategory.is_deleted' => BOOL_FALSE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'ProductCategory' => array(
				'conditions' => $conditions,
				'order' => array('ProductCategory.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$product_categories = $this->Paginator->paginate('ProductCategory');
		$this->set(compact('product_categories'));		
		
		
    }
	
	
	public function admin_resetProductCategorySearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('ProductCategorySearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_toggleProductCategoryStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(UPDATE_PERMISSION_ID,DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('ProductCategory');		
			$this->ProductCategory->id = $id;
			
			if (!$this->ProductCategory->exists()) 
			{
				throw new NotFoundException('Invalid Product Category');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->ProductCategory->saveField('is_active',$value)) 
		   {	   		
				$this->Session->setFlash('Product Category '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Product Category was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}
	
	public function admin_addProductCategory() {
		$this->admin_check_login();
		$this->loadModel('ProductCategory');
        
		if ($this->request->is('post')) 
		{	
			
			$this->ProductCategory->create();			
			if ($this->ProductCategory->save($this->request->data)) 
			{
				$this->Session->setFlash('The product category has been saved', 'success');
				return $this->redirect(array('controller'=>'managements','action' => 'productCategories','admin'=>true,'ext'=>URL_EXTENSION));
			} 
			else 
			{
				$this->Session->setFlash('The product category could not be saved. Please, try again.', 'error');
			}
		
        }		

    }


    public function admin_editProductCategory($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('ProductCategory');
		
		if (!$this->ProductCategory->exists($id)) 
		{
            throw new NotFoundException('Invalid Product Category');
        }
        
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			$this->ProductCategory->id=$id;
			if ($this->ProductCategory->save($this->request->data)) 
			{
				$this->Session->setFlash('The product category has been updated', 'success');
				return $this->redirect(array('controller'=>'managements','action' => 'productCategories','admin'=>true,'ext'=>URL_EXTENSION));
			} 
			else 
			{
				$this->Session->setFlash('The product category can not be updated. Please, try again.', 'error');
			}
		} 
		else 
		{
			$this->request->data = $this->ProductCategory->find('first', array('conditions' => array('ProductCategory.id' => $id)));
		}
    }

    public function admin_deleteProductCategory($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('ProductCategory');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->ProductCategory->id = $id;
			if (!$this->ProductCategory->exists()) 
			{
				throw new NotFoundException('Invalid Product Category');
			}
		   if ($this->ProductCategory->saveField('is_deleted',BOOL_TRUE)) 
		   {
				$this->ProductCategory->saveField('is_active',BOOL_FALSE);
				$this->Session->setFlash('Product Category deleted','success');	
				 return $this->redirect($this->referer());
		   }
		
	        $this->Session->setFlash('Product Category was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	///////////////////////Email Template/////////////////////////////////	
	
	/**
     * Function for listing the email templates. 
     * created Mohammad Masood
     * @name admin_emailTemplateList
     * @acces public 
     * @param  null
     * @return void
     * @created 14 November 2016
     * @modified 14 November 2016

     */
    public function admin_emailTemplateList() 
	{
		$cond=array();		
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('EmailTemplate');
			if(isset($this->request->data['EmailTemplate']))
			{					
				$this->Session->write('EmailTemplateSearch',$this->request->data['EmailTemplate']);
			}
			else
			{	
				$this->request->data['User']=$this->Session->read('EmailTemplateSearch');		
			}		
			if(isset($this->request->data['EmailTemplate']))				
			{			
				if(isset($this->request->data['EmailTemplate']['name']) and !empty($this->request->data['EmailTemplate']['name']))				
				{
					$cond['OR']['EmailTemplate.type LIKE']=$this->request->data['EmailTemplate']['name']."%";
					$cond['OR']['EmailTemplate.subject LIKE']=$this->request->data['EmailTemplate']['name']."%";					
					$cond['OR']['EmailTemplate.content LIKE']=$this->request->data['EmailTemplate']['name']."%";
				}				
			}		
						
				$conditions = array(
					'EmailTemplate.id !=' => BOOL_FALSE,
					'EmailTemplate.is_deleted' => BOOL_FALSE,					
				);
				
			$conditions=array_merge($conditions,$cond);			
            $this->Paginator->settings = array(
                'EmailTemplate' => array(
                    'conditions' => $conditions,
                    'order' => array('EmailTemplate.type' => 'ASC'),
                    'limit' => PAGINATION_LIMIT,
                    'recursive' => 0
            ));
            $emailTemplate = $this->Paginator->paginate('EmailTemplate');
            $this->set(compact('emailTemplate'));
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }

    
	/**
     * Function to view a single email template. 
     * created Mohammad Masood
     * @name admin_viewEmailTemplate
     * @acces public 
     * @param  null
     * @return void
     * @created 14 November 2016
     * @modified 14 November 2016

     */
    public function admin_viewEmailTemplate($id=NULL) 
	{
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('EmailTemplate');
			$id = $this->Encryption->decrypt($id);
			$this->EmailTemplate->id = $id;
			if(!$this->EmailTemplate->exists())
			{
				throw new NotFoundException("Email Template Not Found");
			}
			$conditions = array(
					'EmailTemplate.id !=' => BOOL_FALSE,
					'EmailTemplate.id' => $id,
					'EmailTemplate.is_deleted' => BOOL_FALSE,					
			);			
		
            $emailTemplate = $this->EmailTemplate->find('first',
				array(
                    'conditions' => $conditions,                    
            	));
			
            $this->set(compact('emailTemplate'));
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }

    /**
     * Function for activating/deactivating the email templates. 
     * admin_toggleEmailTemplateStatus method
     * @name admin_toggleEmailTemplateStatus
     * @acces public 
     * @param  $encodedEmailTemplateId(Email Template id in encoded form), $action(activae/de-activate)
     * @return void
     * @created 08 June 2016
     * @modified 08 June 2016
	 * @created Mohammad Masood
     */
    public function admin_toggleEmailTemplateStatus($encodedEmailTemplateId, $action) {
		$this->admin_check_login();	
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
            if (!empty($action)) 
			{
                $this->loadModel('EmailTemplate');
                $statusValue = (trim($action) == 'activate') ? 1 : 0;
                $this->EmailTemplate->id = $this->Encryption->decrypt($encodedEmailTemplateId);
                if ($this->EmailTemplate->saveField('is_active', $statusValue)) {
                    $this->Session->setFlash("Template status updated successfully", 'success');
                    $this->redirect($this->referer());
                } else {
                    $this->Session->setFlash("Request could not be processed, please try again", 'error');
                    $this->redirect($this->referer());
                }
            } 
			else 
			{
                $this->Session->setFlash("Unauthorized access", 'error');
                $this->redirect($this->referer());
            }
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
        }
    }
	
	public function admin_resetEmailTemplateSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('EmailTemplateSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
    
    /**
     * Function for listing the add pages. 
     * admin_addSiteContent method
     * @name admin_addSiteContent
     * @acces public 
     * @param  null
     * @return void
     * @created 02 June 2016
     * @modified 02 June 2016
	 * @created Mohammad Masood
     */
    public function admin_addEmailTemplate() 
	{
		$this->admin_check_login();	
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID, CREATE_PERMISSION_ID))) 
		{
			$this->loadModel('EmailTemplate');
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{
				$this->EmailTemplate->set($this->request->data);           
				if ($this->EmailTemplate->save($this->request->data['EmailTemplate'])) 
				{
					$this->Session->setFlash("Email template created successfuly", 'success');
					$this->redirect(array('controller'=>'managements','action'=>'emailTemplateList','admin'=>true,'ext'=>URL_EXTENSION));
					
				} 
				else 
				{
					$this->Session->setFlash("Email template could not be created", 'error');
				}
			}
		} 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
        }	
    }
	
	
	/**
     * Function for listing the add pages. 
     * admin_addSiteContent method
     * @name admin_addSiteContent
     * @acces public 
     * @param  null
     * @return void
     * @created 02 June 2016
     * @modified 02 June 2016
	 * @created Mohammad Masood
     */
    public function admin_editEmailTemplate($template_id) 
	{
		$this->admin_check_login();	
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
			$template_id=$this->Encryption->decrypt($template_id);
			$this->loadModel('EmailTemplate');
			$this->EmailTemplate->id = 	$template_id;
			
			if(!$this->EmailTemplate->exists())
			{
				throw new NotFoundException('Invalid Email Template . ');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{				       
				if ($this->EmailTemplate->save($this->request->data['EmailTemplate'])) 
				{
					$this->Session->setFlash("Email template updated successfuly", 'success');
					$this->redirect(array('controller'=>'managements','action'=>'emailTemplateList','admin'=>true,'ext'=>URL_EXTENSION));
					
				} 
				else 
				{
					$this->Session->setFlash("Email template could not be updated", 'error');
				}
			}
			else
			{
				$template_detail=$this->EmailTemplate->find('first',array(
				'conditions'=>array(
						'EmailTemplate.id'=>$template_id,
						'EmailTemplate.is_deleted'=>BOOL_FALSE,
						
						)
				));				
				$this->request->data=$template_detail;
			}
		} 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
        }	
    }
	
	/**
     * Function for deleting the email templates. 
     * admin_deleteEmailTemplate method
     * @name admin_deleteEmailTemplate
     * @acces public 
     * @param  $encodedEmailTemplateId(Email Template id in encoded form)
     * @return void
     * @created 08 June 2016
     * @modified 08 June 2016
	*@ created Mohammad Masood

     */
    public function admin_deleteEmailTemplate($encodedEmailTemplateId) 
	{
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID, DELETE_PERMISSION_ID))) 
		{           
			
			$this->loadModel('EmailTemplate');
			$this->EmailTemplate->id = $this->Encryption->decrypt($encodedEmailTemplateId);
			if(!$this->EmailTemplate->exists())
			{
				throw new NotFoundException("Invalid Email Template");
			}
			
			if ($this->EmailTemplate->saveField('is_deleted', BOOL_TRUE)) 
			{
				$this->EmailTemplate->saveField('is_active', BOOL_FALSE);
				$this->Session->setFlash("Template deleted successfully", 'success');
				$this->redirect($this->referer());
			} 
			else 
			{
				$this->Session->setFlash("Request could not be processed, please try again", 'error');
				$this->redirect($this->referer());
			}		
        } 
		else 
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
        }
    }
	
	/**
	* quick sql debug from controller dynamically
	* or statically from just about any other place in the script
	* @param bool $die: TRUE to output and die, FALSE to log to file and continue
	*/
	function sql($die = true) {
	if (isset($this->Controller)) {
		$object = $this->Controller->{$this->Controller->modelClass};
	} else {
		$object = ClassRegistry::init(defined('CLASS_USER')?CLASS_USER:'User');
	}
	
	$log = $object->getDataSource()->getLog(false, false);
	foreach ($log['log'] as $key => $value) {
		if (strpos($value['query'], 'SHOW ') === 0 || strpos($value['query'], 'SELECT CHARACTER_SET_NAME ') === 0) {
			unset($log['log'][$key]);
			continue;	
		}
	}
	// Output and die?
	if ($die) {
		debug($log);
		die();
	}
	// Log to file then and continue
	$log = print_r($log, true);
	CakeLog::write('sql', $log);
	}
	
	
	////////////////// Product Management///////////////////	
	public function admin_products() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Product');
		
		if(isset($this->request->data['Product']))
		{					
			$this->Session->write('ProductSearch',$this->request->data['Product']);
		}
		else
		{	
			$this->request->data['Product']=$this->Session->read('ProductSearch');		
		}		
		if(isset($this->request->data['Product']))				
		{			
			if(isset($this->request->data['Product']['name']) and !empty($this->request->data['Product']['name']))				
			{
				$cond['Product.name LIKE']="%".$this->request->data['Product']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Product.id !=' => BOOL_FALSE,
			'Product.is_deleted' => BOOL_FALSE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Product' => array(
				'conditions' => $conditions,
				'order' => array('Product.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$products = $this->Paginator->paginate('Product');
		$this->set(compact('products'));		
		
		
    }
	
	public function admin_viewProduct($id=NULL) 
	{
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('Product');
			$id = $this->Encryption->decrypt($id);
			$this->Product->id = $id;
			if(!$this->Product->exists())
			{
				throw new NotFoundException("Product Not Found");
			}
			$conditions = array(
					'Product.id !=' => BOOL_FALSE,
					'Product.id' => $id,
					'Product.is_deleted' => BOOL_FALSE,					
			);			
		
            $product = $this->Product->find('first',
				array(
                    'conditions' => $conditions,                    
            	));
			
            $this->set(compact('product'));
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	public function admin_resetProductSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('ProductSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_toggleProductStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(UPDATE_PERMISSION_ID,DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('Product');		
			$this->Product->id = $id;
			
			if (!$this->Product->exists()) 
			{
				throw new NotFoundException('Invalid Product');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->Product->saveField('is_active',$value)) 
		   {	   		
				$this->Session->setFlash('Product '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Product was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}
	
	public function admin_addProduct() {
		$this->admin_check_login();
		$this->loadModel('Product');
        $this->loadModel('ProductCategory');
		$this->loadModel('Supplier');
		$this->loadModel('MeasurementUnit');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			if ($this->request->is('post')) 
			{	
				
				if(!empty($this->request->data['Product']['photo']['name']))
					{						
						$this->Img = $this->Components->load('Img');			
						$newName = strtotime("now");
						$rnd = rand(5, 15);
						$newName = $newName.$rnd;
						$ext = $this->Img->ext($this->request->data['Product']['photo']['name']);			
						
						$origFile = $newName . '.' . $ext;
						$dst = $newName .  '.'.$ext;	
						$targetdir = WWW_ROOT . 'assets/images/products';			
						$upload = $this->Img->upload($this->request->data['Product']['photo']['tmp_name'], $targetdir, $origFile);					
						if($upload == 'Success') 
						{	
							$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 'assets/images/products/thumbs/', $dst, 400, 400, 1, 0);
							$this->request->data['Product']['photo'] = $dst;
						}
						else 
						{
							$this->request->data['Product']['photo'] = '';
						}
					}
					else 
					{
						$this->request->data['Product']['photo'] = '';
					}
								
				$this->Product->create();			
				if ($this->Product->save($this->request->data)) 
				{
					$this->Session->setFlash('The product has been saved', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'products','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The product could not be saved. Please, try again.', 'error');
				}
			
			}	
			
			$categories=$this->ProductCategory->find('list',array(
			'fields'=>array('ProductCategory.id','ProductCategory.name'),
			'conditions'=>array(
						'ProductCategory.id !='=>BOOL_FALSE,
						'ProductCategory.is_deleted'=>BOOL_FALSE,
						'ProductCategory.is_active'=>BOOL_TRUE,
						
					)
				));
			$this->set(compact('categories'));
			
			$suppliers=$this->Supplier->find('list',array(
			'fields'=>array('Supplier.id','Supplier.name'),
			'conditions'=>array(
						'Supplier.id !='=>BOOL_FALSE,
						'Supplier.is_deleted'=>BOOL_FALSE,
						'Supplier.is_active'=>BOOL_TRUE,
						
					)
				));
			$this->set(compact('suppliers'));
			
			$measurement_units=$this->MeasurementUnit->find('list',array(
			'fields'=>array('MeasurementUnit.id','MeasurementUnit.name'),
			'conditions'=>array(
						'MeasurementUnit.id !='=>BOOL_FALSE,
						'MeasurementUnit.is_deleted'=>BOOL_FALSE,
						'MeasurementUnit.is_active'=>BOOL_TRUE,
						
					)
				));
			$this->set(compact('measurement_units'));
		
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }


    public function admin_editProduct($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Product');
		$this->loadModel('ProductCategory');
		$this->loadModel('Supplier');
		$this->loadModel('MeasurementUnit');
		
		if (!$this->Product->exists($id)) 
		{
            throw new NotFoundException('Invalid Product');
        }
        
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,UPDATE_PERMISSION_ID))) 
		{
			if ($this->request->is('post') || $this->request->is('put')) 
			{
					if(!empty($this->request->data['Product']['photo']['name']))
					{						
						$this->Img = $this->Components->load('Img');			
						$newName = strtotime("now");
						$rnd = rand(5, 15);
						$newName = $newName.$rnd;
						$ext = $this->Img->ext($this->request->data['Product']['photo']['name']);			
						
						$origFile = $newName . '.' . $ext;
						$dst = $newName .  '.jpg';	
						$targetdir = WWW_ROOT . 'assets/images/products';			
						$upload = $this->Img->upload($this->request->data['Product']['photo']['tmp_name'], $targetdir, $origFile);					
						if($upload == 'Success') 
						{	
							$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 'assets/images/products/thumbs/', $dst, 400, 400, 1, 0);
							$this->request->data['Product']['photo'] = $dst;
						}
						else 
						{
							$this->request->data['Product']['photo'] = '';
						}
					}
					else 
					{
						unset($this->request->data['Product']['photo']);
					}
					
				$this->Product->id=$id;
				if ($this->Product->save($this->request->data)) 
				{
					$this->Session->setFlash('The product has been updated', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'products','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The product can not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->Product->find('first', array('conditions' => array('Product.id' => $id)));
			}
			
			$categories=$this->ProductCategory->find('list',array(
			'fields'=>array('ProductCategory.id','ProductCategory.name'),
			'conditions'=>array(
						'ProductCategory.id !='=>BOOL_FALSE,
						'ProductCategory.is_deleted'=>BOOL_FALSE,
						'ProductCategory.is_active'=>BOOL_TRUE,
						
					)
				));
			$this->set(compact('categories'));
			
			$suppliers=$this->Supplier->find('list',array(
			'fields'=>array('Supplier.id','Supplier.name'),
			'conditions'=>array(
						'Supplier.id !='=>BOOL_FALSE,
						'Supplier.is_deleted'=>BOOL_FALSE,
						'Supplier.is_active'=>BOOL_TRUE,
						
					)
				));
			$this->set(compact('suppliers'));
			
			$measurement_units=$this->MeasurementUnit->find('list',array(
			'fields'=>array('MeasurementUnit.id','MeasurementUnit.name'),
			'conditions'=>array(
						'MeasurementUnit.id !='=>BOOL_FALSE,
						'MeasurementUnit.is_deleted'=>BOOL_FALSE,
						'MeasurementUnit.is_active'=>BOOL_TRUE,
						
					)
				));
			$this->set(compact('measurement_units'));
		}
		else{
			
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }

    public function admin_deleteProduct($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('Product');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->Product->id = $id;
			if (!$this->Product->exists()) 
			{
				throw new NotFoundException('Invalid Product');
			}
		   if ($this->Product->saveField('is_deleted',BOOL_TRUE)) 
		   {
				$this->Product->saveField('is_active',BOOL_FALSE);
				$this->Session->setFlash('Product deleted','success');	
				 return $this->redirect($this->referer());
		   }
		
	        $this->Session->setFlash('Product was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	
	
	////////////////// Supplier Management///////////////////	
	public function admin_listSuppliers() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Supplier');
		
		if(isset($this->request->data['Supplier']))
		{					
			$this->Session->write('SupplierSearch',$this->request->data['Supplier']);
		}
		else
		{	
			$this->request->data['Supplier']=$this->Session->read('SupplierSearch');		
		}		
		if(isset($this->request->data['Supplier']))				
		{			
			if(isset($this->request->data['Supplier']['name']) and !empty($this->request->data['Supplier']['name']))				
			{
				$cond['Supplier.name LIKE']="%".$this->request->data['Supplier']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Supplier.id !=' => BOOL_FALSE,
			'Supplier.is_deleted' => BOOL_FALSE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Supplier' => array(
				'conditions' => $conditions,
				'order' => array('Supplier.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$suppliers = $this->Paginator->paginate('Supplier');
		$this->set(compact('suppliers'));		
		
		
    }
	
	public function admin_viewSupplier($id=NULL) 
	{
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('Supplier');
			$id = $this->Encryption->decrypt($id);
			$this->Supplier->id = $id;
			if(!$this->Supplier->exists())
			{
				throw new NotFoundException("Supplier Not Found");
			}
			$conditions = array(
					'Supplier.id !=' => BOOL_FALSE,
					'Supplier.id' => $id,
					'Supplier.is_deleted' => BOOL_FALSE,					
			);			
		
            $supplier = $this->Supplier->find('first',
				array(
                    'conditions' => $conditions,                    
            	));
			
            $this->set(compact('supplier'));
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	public function admin_resetSupplierSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SupplierSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_toggleSupplierStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(UPDATE_PERMISSION_ID,DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('Supplier');		
			$this->Supplier->id = $id;
			
			if (!$this->Supplier->exists()) 
			{
				throw new NotFoundException('Invalid Supplier');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->Supplier->saveField('is_active',$value)) 
		   {	   		
				$this->Session->setFlash('Supplier '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Supplier was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}
	
	public function admin_addSupplier() {
		$this->admin_check_login();
		$this->loadModel('Supplier');
		$this->loadModel('Country');
		$this->loadModel('State');
		$this->loadModel('City');

		$states=array();
		$this->set('states',$states);
		
		$cities=array();
		$this->set('cities',$cities);
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, CREATE_PERMISSION_ID))) 
		{
			if ($this->request->is('post')) 
			{		
				$states=$this->State->find('list',array(
				'conditions'=>array(
				'State.id !='=>BOOL_FALSE,				
				'State.is_deleted'=>BOOL_FALSE,
				'State.is_active'=>BOOL_TRUE,
				'State.country_id'=>$this->request->data['Supplier']['country'],
				)
				));			
				
				echo $this->set(compact('states'));
				
				$cities=$this->City->find('list',array(
				'conditions'=>array(
				'City.id !='=>BOOL_FALSE,				
				'City.is_deleted'=>BOOL_FALSE,
				'City.is_active'=>BOOL_TRUE,
				'City.state_id'=>$this->request->data['Supplier']['state'],
				'City.is_district'=>BOOL_TRUE,
				)
				));			
				echo $this->set(compact('cities'));
				
				$this->Supplier->create();			
				if ($this->Supplier->save($this->request->data)) 
				{
					$this->Session->setFlash('The Supplier has been saved', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'listSuppliers','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The Supplier could not be saved. Please, try again.', 'error');
				}
		
			}	
		
			$countries=$this->Country->find('list',array(
				'conditions'=>array(
				'Country.id !='=>BOOL_FALSE,
				'Country.is_deleted'=>BOOL_FALSE,
				'Country.is_active'=>BOOL_TRUE,
				)
				));			
			echo $this->set(compact('countries'));	
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
    }


    public function admin_editSupplier($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Supplier');
		$this->loadModel('Country');
		$this->loadModel('State');
		$this->loadModel('City');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
			if (!$this->Supplier->exists($id)) 
			{
				throw new NotFoundException('Invalid Supplier');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{		
				$this->Supplier->id=$id;
				if ($this->Supplier->save($this->request->data)) 
				{
					$this->Session->setFlash('The Supplier has been updated', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'listSuppliers','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The Supplier can not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->Supplier->find('first', array('conditions' => array('Supplier.id' => $id)));
			}		
		}	
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
		$countries=$this->Country->find('list',array(
			'conditions'=>array(
			'Country.id !='=>BOOL_FALSE,
			'Country.is_deleted'=>BOOL_FALSE,
			'Country.is_active'=>BOOL_TRUE,
			)
			));			
		echo $this->set(compact('countries'));	
		
		$states=$this->State->find('list',array(
		'conditions'=>array(
		'State.id !='=>BOOL_FALSE,				
		'State.is_deleted'=>BOOL_FALSE,
		'State.is_active'=>BOOL_TRUE,
		'State.country_id'=>$this->request->data['Supplier']['country'],
		)
		));			

		echo $this->set(compact('states'));

		$cities=$this->City->find('list',array(
		'conditions'=>array(
		'City.id !='=>BOOL_FALSE,				
		'City.is_deleted'=>BOOL_FALSE,
		'City.is_active'=>BOOL_TRUE,
		'City.state_id'=>$this->request->data['Supplier']['state'],
		'City.is_district'=>BOOL_TRUE,
		)
		));			
		echo $this->set(compact('cities'));
		
    }

    public function admin_deleteSupplier($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('Supplier');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->Supplier->id = $id;
			if (!$this->Supplier->exists()) 
			{
				throw new NotFoundException('Invalid Supplier');
			}
		   if ($this->Supplier->saveField('is_deleted',BOOL_TRUE)) 
		   {
				$this->Supplier->saveField('is_active',BOOL_FALSE);
				$this->Session->setFlash('Supplier deleted','success');	
				 return $this->redirect($this->referer());
		   }
		
	        $this->Session->setFlash('Supplier was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	
	
	////////////////// Measurement Units///////////////////	
	public function admin_listMeasurementUnits() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('MeasurementUnit');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			if(isset($this->request->data['MeasurementUnit']))
			{					
				$this->Session->write('MeasurementUnitSearch',$this->request->data['MeasurementUnit']);
			}
			else
			{	
				$this->request->data['MeasurementUnit']=$this->Session->read('MeasurementUnitSearch');		
			}		
			if(isset($this->request->data['MeasurementUnit']))				
			{			
				if(isset($this->request->data['MeasurementUnit']['name']) and !empty($this->request->data['MeasurementUnit']['name']))				
				{
					$cond['MeasurementUnit.name LIKE']="%".$this->request->data['MeasurementUnit']['name']."%";
				}				
			}		
			
							
			$conditions = array(
				'MeasurementUnit.id !=' => BOOL_FALSE,
				'MeasurementUnit.is_deleted' => BOOL_FALSE
			);
			
			$conditions=array_merge($conditions,$cond);
			
			$this->Paginator->settings = array(
				'MeasurementUnit' => array(
					'conditions' => $conditions,
					'order' => array('MeasurementUnit.id' => 'DESC'),
					'limit' => PAGINATION_LIMIT,
					'recursive' => 0
			));
			$measurement_units = $this->Paginator->paginate('MeasurementUnit');
			$this->set(compact('measurement_units'));		
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
		
		
    }
	
	public function admin_viewMeasurementUnit($id=NULL) 
	{
		$this->admin_check_login();				
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('MeasurementUnit');
			$id = $this->Encryption->decrypt($id);
			$this->MeasurementUnit->id = $id;
			if(!$this->MeasurementUnit->exists())
			{
				throw new NotFoundException("Measurement Unit Not Found");
			}
			$conditions = array(
					'MeasurementUnit.id !=' => BOOL_FALSE,
					'MeasurementUnit.id' => $id,
					'MeasurementUnit.is_deleted' => BOOL_FALSE,					
			);			
		
            $measurement_unit = $this->MeasurementUnit->find('first',
				array(
                    'conditions' => $conditions,                    
            	));
			
            $this->set(compact('measurement_unit'));
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	public function admin_resetMeasurementUnitSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('MeasurementUnitSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_toggleMeasurementUnitStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(UPDATE_PERMISSION_ID,DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('MeasurementUnit');		
			$this->MeasurementUnit->id = $id;
			
			if (!$this->MeasurementUnit->exists()) 
			{
				throw new NotFoundException('Invalid Measurement Unit');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->MeasurementUnit->saveField('is_active',$value)) 
		   {	   		
				$this->Session->setFlash('Measurement Unit '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Measurement Unit was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}
	
	public function admin_addMeasurementUnit() {
		$this->admin_check_login();
		$this->loadModel('MeasurementUnit');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, CREATE_PERMISSION_ID))) 
		{
			if ($this->request->is('post')) 
			{	
				$this->MeasurementUnit->create();			
				if ($this->MeasurementUnit->save($this->request->data)) 
				{
					$this->Session->setFlash('The Measurement Unit has been saved', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'listMeasurementUnits','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The Measurement Unit could not be saved. Please, try again.', 'error');
				}
		
			}	
			
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
    }


    public function admin_editMeasurementUnit($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('MeasurementUnit');
				
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
			if (!$this->MeasurementUnit->exists($id)) 
			{
				throw new NotFoundException('Invalid Measurement Unit');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{		
				$this->MeasurementUnit->id=$id;
				if ($this->MeasurementUnit->save($this->request->data)) 
				{
					$this->Session->setFlash('The Measurement Unit has been updated', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'listMeasurementUnits','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The Measurement Unit can not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->MeasurementUnit->find('first', array('conditions' => array('MeasurementUnit.id' => $id)));
			}		
		}	
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
    }

    public function admin_deleteMeasurementUnit($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('MeasurementUnit');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->MeasurementUnit->id = $id;
			if (!$this->MeasurementUnit->exists()) 
			{
				throw new NotFoundException('Invalid Measurement Unit');
			}
		   if ($this->MeasurementUnit->saveField('is_deleted',BOOL_TRUE)) 
		   {
				$this->MeasurementUnit->saveField('is_active',BOOL_FALSE);
				$this->Session->setFlash('Measurement Unit deleted','success');	
				 return $this->redirect($this->referer());
		   }
		
	        $this->Session->setFlash('Measurement Unit was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }


	////////////////// Purchases ///////////////////
	public function admin_listPurchases()
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Purchase');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			if(isset($this->request->data['Purchase']))
			{					
				$this->Session->write('PurchaseSearch',$this->request->data['Purchase']);
			}
			else
			{	
				$this->request->data['Purchase']=$this->Session->read('PurchaseSearch');		
			}		
			if(isset($this->request->data['Purchase']))				
			{			
				if(isset($this->request->data['Purchase']['name']) and !empty($this->request->data['Purchase']['name']))				
				{
					
					if(is_numeric($this->request->data['Purchase']['name'])){
					
					$cond['OR']['Purchase.total_amount']= $this->request->data['Purchase']['name'];
					$cond['OR']['Purchase.total_payment']= $this->request->data['Purchase']['name'];
					$cond['OR']['Purchase.total_balance']= $this->request->data['Purchase']['name'];
					}
					$cond['OR']['Supplier.name LIKE']="%".$this->request->data['Purchase']['name']."%";		
					$cond['OR']['DATE(Purchase.purchase_date)']= date('Y-m-d',strtotime($this->request->data['Purchase']['name']));			
				}				
			}		
			
							
			$conditions = array(
				'Purchase.id !=' => BOOL_FALSE,
				'Purchase.is_deleted' => BOOL_FALSE
			);
			
			$conditions=array_merge($conditions,$cond);
			
			$this->Paginator->settings = array(
				'Purchase' => array(
					'conditions' => $conditions,
					'order' => array('Purchase.id' => 'DESC'),
					'limit' => PAGINATION_LIMIT,
					'recursive' => 2
			));
			$purchases = $this->Paginator->paginate('Purchase');
			$this->set(compact('purchases'));		
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
		
		
    }
	
	public function admin_viewPurchase($id=NULL) 
	{
		$this->admin_check_login();
		$cond=array();
		$this->loadModel('Purchase');
		$this->loadModel('PurchaseDetail');
		$id = $this->Encryption->decrypt($id);
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID)))
		{
			if(isset($this->request->data['PurchaseDetail']))
			{
				$this->Session->write('PurchaseSearch',$this->request->data['PurchaseDetail']);
			}
			else
			{	
				$this->request->data['PurchaseDetail']=$this->Session->read('PurchaseSearch');		
			}		
			if(isset($this->request->data['PurchaseDetail']))				
			{			
				if(isset($this->request->data['PurchaseDetail']['name']) and !empty($this->request->data['PurchaseDetail']['name']))				
				{
					$cond['Product.name LIKE']="%".$this->request->data['PurchaseDetail']['name']."%";
				}				
			}		
			
			
			$purchase = $this->Purchase->find('first',array(
			'conditions' => array(
					'Purchase.id !='=>BOOL_FALSE,
					'Purchase.id'=>$id,
					'Purchase.is_deleted !='=>BOOL_TRUE,
					
				),
			
			));
			
			$this->set(compact('purchase'));
			
			$conditions = array(
				'PurchaseDetail.id !=' => BOOL_FALSE,
				'PurchaseDetail.is_deleted' => BOOL_FALSE,
				'PurchaseDetail.purchase_id' => $id
			);
			
			$conditions=array_merge($conditions,$cond);			
			$this->Paginator->settings = array(
				'PurchaseDetail' => array(
					'conditions' => $conditions,
					'order' => array('PurchaseDetail.id' => 'DESC'),
					'limit' => PAGINATION_LIMIT,
					'recursive' => 2
			));
			$purchase_details = $this->Paginator->paginate('PurchaseDetail');
			$this->set(compact('purchase_details'));		
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
		
    }
	public function admin_resetPurchaseSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('PurchaseSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_togglePurchaseStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(UPDATE_PERMISSION_ID,DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('Purchase');		
			$this->Purchase->id = $id;
			
			if (!$this->Purchase->exists()) 
			{
				throw new NotFoundException('Invalid Purchase');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->Purchase->saveField('is_active',$value)) 
		   {	   		
				$this->Session->setFlash('Purchase '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Purchase was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}
	
	public function admin_addPurchase() {
		$this->admin_check_login();
		$this->loadModel('Purchase');
		$this->loadModel('Product');
		$this->loadModel('PurchaseDetail');
		$this->loadModel('Supplier');
		$this->loadModel('PaymentTransaction');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, CREATE_PERMISSION_ID))) 
		{
			if ($this->request->is('post')) 
			{					
				$product_list = $this->Product->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'Product.supplier_id' => $this->request->data['Purchase']['supplier_id'],
					'Product.is_deleted' => BOOL_FALSE,
					'Product.is_active' => BOOL_TRUE,					
					),
					'order'=>array('Product.name'=>'ASC')
				));		
				
				$this->set(compact('product_list'));
				$this->request->data['Purchase']['purchase_date']=date('Y-m-d',strtotime($this->request->data['Purchase']['purchase_date']));
				$this->request->data['Purchase']['created_by']=$this->Auth->User('id');
				$this->Purchase->create();
				if ($this->Purchase->save($this->request->data)) 
				{
					$purchase_id = $this->Purchase->getInsertID();
					
					if($this->request->data['Purchase']['total_payment'] > 0){
						
						$this->request->data['PaymentTransaction']['type'] = PURCHASE_PAYMENT;
						$this->request->data['PaymentTransaction']['reference_id'] = $purchase_id;
						$this->request->data['PaymentTransaction']['supplier_id'] = $this->request->data['Purchase']['supplier_id'];
						$this->request->data['PaymentTransaction']['sub_total'] = $this->request->data['Purchase']['total_amount'];
						$this->request->data['PaymentTransaction']['payment'] = $this->request->data['Purchase']['total_payment'];
						$this->request->data['PaymentTransaction']['balance'] = $this->request->data['Purchase']['total_balance'];
						$this->request->data['PaymentTransaction']['notes'] = "First Payment";
						$this->request->data['PaymentTransaction']['created_by'] = $this->Session->read('Auth.User.id');
						
						$this->PaymentTransaction->create();
						$this->PaymentTransaction->save($this->request->data['PaymentTransaction']);
						
					}
					
					foreach($this->request->data['PurchaseDetail'] as $k)
					{
						$this->PurchaseDetail->create();
						$pDtail=array(
						'purchase_id'=>$purchase_id,
						'supplier_id'=>$this->request->data['Purchase']['supplier_id'],
						'product_id'=>$k['product_id'],
						'quantity'=>$k['quantity'],
						'purchase_price'=>$k['purchase_price'],
						'selling_price'=>$k['selling_price'],
						'total_amount'=>$k['total_amount'],
						'created_by'=>$this->Session->read('Auth.User.id'),
						
						);						
						
						if($this->PurchaseDetail->save($pDtail)){
							
							$prd=$this->Product->find('first',array(
							'conditions'=>array(
									'Product.id'=>$k['product_id'],
									'Product.is_deleted'=>BOOL_FALSE,
									
								),
							'recursive'	=> -1
								
							));
							
							$this->Product->id = $k['product_id'];							
							$this->Product->saveField('quantity',$prd['Product']['quantity'] + $k['quantity']);
						}
					}
					
					$this->Session->setFlash('The Purchase has been saved', 'success');
					
					return $this->redirect(array('controller'=>'managements','action' => 'listPurchases','admin'=>true,'ext'=>URL_EXTENSION));
					
				} 
				else 
				{
					$this->Session->setFlash('The Purchase could not be saved. Please, try again.', 'error');
				}
		
			}	
			
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
		$suppliers=$this->Supplier->find('list',array(
			'fields'=>array('Supplier.id','Supplier.name'),
			'conditions'=>array(
				'Supplier.id !='=>BOOL_FALSE,
				'Supplier.is_deleted'=>BOOL_FALSE,
				'Supplier.is_active'=>BOOL_TRUE,		
				)
		));
		
		$this->set(compact('suppliers'));
				
		
		
    }


    public function admin_editPurchase($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Purchase');		
		$this->loadModel('Product');
		$this->loadModel('PurchaseDetail');
		$this->loadModel('Supplier');
				
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
			if (!$this->Purchase->exists($id)) 
			{
				throw new NotFoundException('Invalid Purchase');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{					
				$this->request->data['Purchase']['purchase_date']=date('Y-m-d',strtotime($this->request->data['Purchase']['purchase_date']));
				$this->request->data['Purchase']['modified_by']=$this->Auth->User('id');	
				$this->Purchase->id=$id;
				if ($this->Purchase->save($this->request->data)) 
				{
					
					$purchase_id = $this->request->data['Purchase']['id'];

					$this->PurchaseDetail->virtualFields['total'] = 'SUM(PurchaseDetail.quantity)';
					$sum = $this->PurchaseDetail->find('all',array(
						'fields'=>array(
							'total'
							),
						'conditions'=>array(
							'PurchaseDetail.purchase_id' => $purchase_id,
							'PurchaseDetail.is_deleted' => BOOL_FALSE
							),
						'recursive'	=> -1
						));
					
					$this->PurchaseDetail->deleteAll(array('PurchaseDetail.purchase_id'=>$purchase_id),false);
					
					foreach($this->request->data['PurchaseDetail'] as $k)
					{
						
						
						$this->PurchaseDetail->create();	
						$pDtail=array(
						'purchase_id'=>$purchase_id,
						'supplier_id'=>$this->request->data['Purchase']['supplier_id'],
						'product_id'=>$k['product_id'],
						'quantity'=>$k['quantity'],
						'purchase_price'=>$k['purchase_price'],
						'selling_price'=>$k['selling_price'],
						'total_amount'=>$k['total_amount'],
						'modified_by'=>$this->Session->read('Auth.User.id'),
						
						);						
						
						$this->PurchaseDetail->save($pDtail);						
					}
					
					$this->Session->setFlash('The Purchase has been updated', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'listPurchases','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The Purchase can not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->Purchase->find('first', array(
					'conditions' => array(
							'Purchase.id' => $id,
							'Purchase.is_deleted' => BOOL_FALSE,
						),
					'contain'=>array(
						'PurchaseDetail'=>array(
							'conditions'=>array(
								'PurchaseDetail.is_deleted'=>BOOL_FALSE,
								)
							),
						)
					));
			
			}		
		}	
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
		$suppliers=$this->Supplier->find('list',array(
			'fields'=>array('Supplier.id','Supplier.name'),
			'conditions'=>array(
				'Supplier.id !='=>BOOL_FALSE,
				'Supplier.is_deleted'=>BOOL_FALSE,
				'Supplier.is_active'=>BOOL_TRUE,		
				)
		));
		$this->set(compact('suppliers'));	
		
		$product_list = $this->Product->find('list', array(
		'fields' => array('id','name'),
		'conditions' => array(
		'Product.supplier_id' => $this->request->data['Purchase']['supplier_id'],
		'Product.is_deleted' => BOOL_FALSE,
		'Product.is_active' => BOOL_TRUE,					
		),
		'order'=>array('Product.name'=>'ASC')
		));		
		
		$this->set(compact('product_list'));
    }

    public function admin_deletePurchase($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('Purchase');
		$this->loadModel('PurchaseDetail');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->Purchase->id = $id;
			$purchase_id = $id;

			if (!$this->Purchase->exists())
			{
				throw new NotFoundException('Invalid Purchase');
			}
		   if ($this->Purchase->saveField('is_deleted',BOOL_TRUE))
		   {
				$this->Purchase->saveField('is_active',BOOL_FALSE);
				
				$this->PurchaseDetail->updateAll(
				array(					
				'PurchaseDetail.is_deleted' => BOOL_TRUE,
				'PurchaseDetail.is_active' => BOOL_FALSE,
				),
				array('PurchaseDetail.purchase_id' => $purchase_id)
				);
				
				$this->Session->setFlash('Purchase deleted','success');	
				return $this->redirect($this->referer());
		   }

	        $this->Session->setFlash('Purchase was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	public function admin_editPurchaseDetail($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Purchase');		
		$this->loadModel('Product');
		$this->loadModel('PurchaseDetail');
		$this->loadModel('Supplier');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
			if (!$this->PurchaseDetail->exists($id)) 
			{
				throw new NotFoundException('Invalid Purchase Detail');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{	
				$purchaseDetail = $this->PurchaseDetail->find('first',array(
					'conditions' =>array(
					'PurchaseDetail.id'=>$id,
					'PurchaseDetail.is_deleted'=>BOOL_FALSE
					),
					'recursive'=> -1,
				));
				
				
				$this->request->data['PurchaseDetail']['modified_by']=$this->Session->read('Auth.User.id');	
				
				$this->PurchaseDetail->id=$id;
				if ($this->PurchaseDetail->save($this->request->data)) 
				{
					
					$this->Session->setFlash('The Purchase Detail has been updated', 'success');
					return $this->redirect($this->referer());
				} 
				else 
				{
					$this->Session->setFlash('The Purchase Detail can not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->PurchaseDetail->find('first', array('conditions' => array('PurchaseDetail.id' => $id)));
			
			}		
		}	
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
		$suppliers=$this->Supplier->find('list',array(
			'fields'=>array('Supplier.id','Supplier.name'),
			'conditions'=>array(
				'Supplier.id !='=>BOOL_FALSE,
				'Supplier.is_deleted'=>BOOL_FALSE,
				'Supplier.is_active'=>BOOL_TRUE,		
				'Supplier.id'=>$this->request->data['PurchaseDetail']['supplier_id'],
				)
		));
		$this->set(compact('suppliers'));	
		
		$product_list = $this->Product->find('list', array(
		'fields' => array('id','name'),
		'conditions' => array(
		'Product.supplier_id' => $this->request->data['PurchaseDetail']['supplier_id'],
		'Product.is_deleted' => BOOL_FALSE,
		'Product.is_active' => BOOL_TRUE,					
		),
		'order'=>array('Product.name'=>'ASC')
		));		
		
		$this->set(compact('product_list'));
    }
	
	
	public function admin_deletePurchaseDetail($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('PurchaseDetail');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->PurchaseDetail->id = $id;
			$purchase_id = $id;

			if (!$this->PurchaseDetail->exists())
			{
				throw new NotFoundException('Invalid Purchase Detail');
			}
		   if ($this->PurchaseDetail->saveField('is_deleted',BOOL_TRUE))
		   {
				$this->PurchaseDetail->saveField('is_active',BOOL_FALSE);			
				
				$this->Session->setFlash('Purchase Detail deleted','success');	
				return $this->redirect($this->referer());
		   }

	        $this->Session->setFlash('Purchase Detail was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	////////////////// Sales / Sales Detail ///////////////////	
	public function admin_listSales() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Sale');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			if(isset($this->request->data['Sale']))
			{					
				$this->Session->write('SaleSearch',$this->request->data['Sale']);
			}
			else
			{	
				$this->request->data['Sale']=$this->Session->read('SaleSearch');		
			}		
			if(isset($this->request->data['Sale']))				
			{			
				if(isset($this->request->data['Sale']['name']) and !empty($this->request->data['Sale']['name']))				
				{
					if(is_numeric($this->request->data['Sale']['name'])){
					
					$cond['OR']['Sale.total_amount']= $this->request->data['Sale']['name'];
					$cond['OR']['Sale.total_payment']= $this->request->data['Sale']['name'];
					$cond['OR']['Sale.total_balance']= $this->request->data['Sale']['name'];
					}
					$cond['OR']['Customer.name LIKE']="%".$this->request->data['Sale']['name']."%";
					$cond['OR']['DATE(Sale.sales_date)']= date('Y-m-d',strtotime($this->request->data['Sale']['name']));
				}				
			}		
			
							
			$conditions = array(
				'Sale.id !=' => BOOL_FALSE,
				'Sale.is_deleted' => BOOL_FALSE
			);
			
			$conditions=array_merge($conditions,$cond);
			
			$this->Paginator->settings = array(
				'Sale' => array(
					'conditions' => $conditions,
					'order' => array('Sale.id' => 'DESC'),
					'limit' => PAGINATION_LIMIT,
					'recursive' => 2
			));
			$sales = $this->Paginator->paginate('Sale');
			$this->set(compact('sales'));
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
		
		
    }
	
	public function admin_viewSale($id=NULL) 
	{
		$this->admin_check_login();
		$cond=array();
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		$id = $this->Encryption->decrypt($id);
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID)))
		{
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SaleSearch',$this->request->data['SalesDetail']);
			}
			else
			{	
				$this->request->data['SalesDetail']=$this->Session->read('SaleSearch');		
			}		
			if(isset($this->request->data['SalesDetail']))				
			{			
				if(isset($this->request->data['SalesDetail']['name']) and !empty($this->request->data['SalesDetail']['name']))				
				{
					$cond['OR']['Product.name LIKE']="%".$this->request->data['SalesDetail']['name']."%";
					$cond['OR']['Customer.name LIKE']="%".$this->request->data['SalesDetail']['name']."%";
				}				
			}		
			
			
			$sale = $this->Sale->find('first',array(
			'conditions' => array(
					'Sale.id !='=>BOOL_FALSE,
					'Sale.id'=>$id,
					'Sale.is_deleted !='=>BOOL_TRUE,
					
				),
			
			));
			
			$this->set(compact('sale'));
			
			$conditions = array(
				'SalesDetail.id !=' => BOOL_FALSE,
				'SalesDetail.is_deleted' => BOOL_FALSE,
				'SalesDetail.sales_id' => $id
			);
			
			$conditions=array_merge($conditions,$cond);			
			$this->Paginator->settings = array(
				'SalesDetail' => array(
					'conditions' => $conditions,
					'order' => array('SalesDetail.id' => 'DESC'),
					'limit' => PAGINATION_LIMIT,
					'recursive' => 2
			));
			
			$sales_details = $this->Paginator->paginate('SalesDetail');
			$this->set(compact('sales_details'));		
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
		
    }
	public function admin_resetSaleSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SaleSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_toggleSaleStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		if ($this->Access->checkPermission(array(UPDATE_PERMISSION_ID,DELETE_PERMISSION_ID))) 
		{	
		
			$id = $this->Encryption->decrypt($id);        
			$this->loadModel('Sale');		
			$this->Sale->id = $id;
			
			if (!$this->Sale->exists()) 
			{
				throw new NotFoundException('Invalid Sale');
			}
			
			if($action=='activate')
			{
				$value=BOOL_TRUE;
				$msg='Activated';
			}
			if($action=='deactivate')
			{
				$value=BOOL_FALSE;
				$msg='DeActivated';
			}
		   if ($this->Sale->saveField('is_active',$value)) 
		   {	   		
				$this->Session->setFlash('Sale '.$msg.'','success');
				return $this->redirect($this->referer());
		   }
			
			$this->Session->setFlash('Sale was not '.$msg.'', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

	}
	
	public function admin_addSale() {
		$this->admin_check_login();
		
		$this->loadModel('Product');		
		$this->loadModel('Customer');
		$this->loadModel('Supplier');
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		$this->loadModel('PaymentTransaction');
		
		$product_list = array();
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, CREATE_PERMISSION_ID))) 
		{
			if ($this->request->is('post')) 
			{	
				$temp = 0;
				foreach($this->request->data['SalesDetail'] as $k){
					
					if(!empty($k['supplier_id'])){
						
						$product_list[$temp] = $this->Product->find('list', array(
						'fields' => array('id','name'),
						'conditions' => array(
						'Product.supplier_id' => $k['supplier_id'],
						'Product.is_deleted' => BOOL_FALSE,
						'Product.is_active' => BOOL_TRUE,					
						),
						'order'=>array('Product.name'=>'ASC')
						));	
					}
					
					$temp++;	
				}
				
				$this->set(compact('product_list'));	
				
				$this->request->data['Sale']['sales_date']=date('Y-m-d',strtotime($this->request->data['Sale']['sales_date']));
				$this->request->data['Sale']['created_by']=$this->Auth->User('id');
				$this->Sale->create();
				if ($this->Sale->save($this->request->data)) 
				{
					
					$sales_id = $this->Sale->getInsertID();
					
					if($this->request->data['Sale']['total_payment'] > 0){
						
						$this->request->data['PaymentTransaction']['type'] = SALES_PAYMENT;
						$this->request->data['PaymentTransaction']['reference_id'] = $sales_id;
						$this->request->data['PaymentTransaction']['customer_id'] = $this->request->data['Sale']['customer_id'];
						$this->request->data['PaymentTransaction']['sub_total'] = $this->request->data['Sale']['total_amount'];
						$this->request->data['PaymentTransaction']['payment'] = $this->request->data['Sale']['total_payment'];
						$this->request->data['PaymentTransaction']['balance'] = $this->request->data['Sale']['total_balance'];
						$this->request->data['PaymentTransaction']['notes'] = "First Payment";
						$this->request->data['PaymentTransaction']['created_by'] = $this->Session->read('Auth.User.id');
						
						$this->PaymentTransaction->create();
						$this->PaymentTransaction->save($this->request->data['PaymentTransaction']);
						
					}
					
					foreach($this->request->data['SalesDetail'] as $k)
					{
						$this->SalesDetail->create();
						$pDtail=array(
						'sales_id'=>$sales_id,
						'customer_id'=>$this->request->data['Sale']['customer_id'],
						'supplier_id'=>$k['supplier_id'],
						'product_id'=>$k['product_id'],
						'quantity'=>$k['quantity'],
						'purchase_price'=>$k['purchase_price'],
						'selling_price'=>$k['selling_price'],
						'total_amount'=>$k['total_amount'],
						'created_by'=>$this->Session->read('Auth.User.id'),
						
						);						
						
						if($this->SalesDetail->save($pDtail)){
							
							$prd=$this->Product->find('first',array(
							'conditions'=>array(
									'Product.id'=>$k['product_id'],
									'Product.is_deleted'=>BOOL_FALSE,
									
								),
							'recursive'	=> -1
								
							));
							
							$this->Product->id = $k['product_id'];							
							$this->Product->saveField('quantity',$prd['Product']['quantity'] - $k['quantity']);
						}
					}
					
					$this->Session->setFlash('The Sale has been saved <a href="'.Router::fullbaseUrl().Router::url(array('controller'=>'managements','action'=>'printInvoice','admin'=>true,'ext'=>URL_EXTENSION,$this->Encryption->encrypt($sales_id))).'" class="btn btn-warning"  >Print Invoice</a>', 'success');
					
					return $this->redirect(array('controller'=>'managements','action' => 'listSales','admin'=>true,'ext'=>URL_EXTENSION));
					
				} 
				else 
				{
					$this->Session->setFlash('The Sales could not be saved. Please, try again.', 'error');
				}
		
			}	
			
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
		$suppliers_list=$this->Supplier->find('list',array(
			'fields'=>array('Supplier.id','Supplier.name'),
			'conditions'=>array(
				'Supplier.id !='=>BOOL_FALSE,
				'Supplier.is_deleted'=>BOOL_FALSE,
				'Supplier.is_active'=>BOOL_TRUE,		
				)
		));
		
		$this->set(compact('suppliers_list'));
		
		$customers=$this->Customer->find('list',array(
			'fields'=>array('Customer.id','Customer.name'),
			'conditions'=>array(
				'Customer.id !='=>BOOL_FALSE,
				'Customer.is_deleted'=>BOOL_FALSE,
				'Customer.is_active'=>BOOL_TRUE,		
				)
		));
		
		$this->set(compact('customers'));
				
		
		
    }

	public function admin_printInvoice($id=NULL){
		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
			$this->Sale->id = $id;
			if(!$this->Sale->exists()){
				throw new NotFoundException('Invalid Sale');
			}
			
			$sale = $this->Sale->find('first',array(
			'conditions' => array(
				'Sale.id' => $id,
				'Sale.id !=' => BOOL_FALSE,
				'Sale.is_deleted' => BOOL_FALSE,
				),			
			));
			
			$this->set(compact('sale'));
			
			$sales_detail = $this->SalesDetail->find('all',array(
			'conditions' => array(
				'SalesDetail.sales_id' => $id,
				'SalesDetail.id !=' => BOOL_FALSE,
				'SalesDetail.is_deleted' => BOOL_FALSE,
				),
			'recursive'	=> 2 
			));
			
			$this->set(compact('sales_detail'));
						
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
	}
    public function admin_editSale($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Sale');		
		$this->loadModel('SalesDetail');
		$this->loadModel('Product');
		$this->loadModel('Supplier');
		$this->loadModel('Customer');
				
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{
			if (!$this->Sale->exists($id)) 
			{
				throw new NotFoundException('Invalid Sale');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{	
				$this->request->data['Sale']['sales_date']=date('Y-m-d',strtotime($this->request->data['Sale']['sales_date']));
				$this->request->data['Sale']['modified_by']=$this->Auth->User('id');	
				$this->Sale->id=$id;
				if ($this->Sale->save($this->request->data)) 
				{
					
					$sales_id = $this->request->data['Sale']['id'];

					$this->SalesDetail->virtualFields['total'] = 'SUM(SalesDetail.quantity)';
					$sum = $this->SalesDetail->find('all',array(
						'fields'=>array(
							'total'
							),
						'conditions'=>array(
							'SalesDetail.sales_id' => $sales_id,
							'SalesDetail.is_deleted' => BOOL_FALSE
							),
						'recursive'	=> -1
						));
						
					$this->SalesDetail->deleteAll(array('SalesDetail.sales_id'=>$sales_id),false);
					
					foreach($this->request->data['SalesDetail'] as $k)
					{
						
						
						$this->SalesDetail->create();	
						$pDtail=array(
						'sales_id'=>$sales_id,
						'customer_id'=>$this->request->data['Sale']['customer_id'],
						'supplier_id'=>$k['supplier_id'],
						'product_id'=>$k['product_id'],
						'quantity'=>$k['quantity'],
						'purchase_price'=>$k['purchase_price'],
						'selling_price'=>$k['selling_price'],
						'total_amount'=>$k['total_amount'],
						'modified_by'=>$this->Session->read('Auth.User.id'),
						
						);						
						
						$this->SalesDetail->save($pDtail);						
					}
					
					$this->Session->setFlash('The Sale has been updated', 'success');
					return $this->redirect(array('controller'=>'managements','action' => 'listSales','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The Sale can not be updated. Please, try again.', 'error');
				}
			} 
			else 
			{
				$this->request->data = $this->Sale->find('first', array(
					'conditions' => array(
							'Sale.id' => $id,
							'Sale.is_deleted' => BOOL_FALSE,
						),
					'contain'=>array(
						'SalesDetail'=>array(
							'conditions'=>array(
								'SalesDetail.is_deleted'=>BOOL_FALSE,
								)
							),
						)
					));
				
			}		
		}	
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
		$suppliers_list=$this->Supplier->find('list',array(
			'fields'=>array('Supplier.id','Supplier.name'),
			'conditions'=>array(
				'Supplier.id !='=>BOOL_FALSE,
				'Supplier.is_deleted'=>BOOL_FALSE,
				'Supplier.is_active'=>BOOL_TRUE,		
				)
		));
		$this->set(compact('suppliers_list'));	
		
		$temp = 0;
		foreach($this->request->data['SalesDetail'] as $k){
			
			if(!empty($k['supplier_id'])){
				
				$product_list[$temp] = $this->Product->find('list', array(
				'fields' => array('id','name'),
				'conditions' => array(
				'Product.supplier_id' => $k['supplier_id'],
				'Product.is_deleted' => BOOL_FALSE,
				'Product.is_active' => BOOL_TRUE,					
				),
				'order'=>array('Product.name'=>'ASC')
				));	
			}
			
			$temp++;	
		}
		$this->set(compact('product_list'));

		$customers=$this->Customer->find('list',array(
			'fields'=>array('Customer.id','Customer.name'),
			'conditions'=>array(
				'Customer.id !='=>BOOL_FALSE,
				'Customer.is_deleted'=>BOOL_FALSE,
				'Customer.is_active'=>BOOL_TRUE,		
				)
		));
		
		$this->set(compact('customers'));
    }

    public function admin_deleteSale($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->Sale->id = $id;
			$sales_id = $id;

			if (!$this->Sale->exists())
			{
				throw new NotFoundException('Invalid Sales');
			}
		   if ($this->Sale->saveField('is_deleted',BOOL_TRUE))
		   {
				$this->Sale->saveField('is_active',BOOL_FALSE);
				
				$this->SalesDetail->updateAll(
				array(					
				'SalesDetail.is_deleted' => BOOL_TRUE,
				'SalesDetail.is_active' => BOOL_FALSE,
				),
				array('SalesDetail.sales_id' => $sales_id)
				);
				
				$this->Session->setFlash('Sales deleted','success');	
				return $this->redirect($this->referer());
		   }

	        $this->Session->setFlash('Sales was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	
	
	public function admin_addPayment($payment_type=NULL,$ref_id =NULL){
		
		$this->admin_check_login();
		$this->loadModel('Purchase');
		$this->loadModel('Sale');
		$this->loadModel('Supplier');
		$this->loadModel('PaymentTransaction');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, CREATE_PERMISSION_ID))) 
		{
			$type = $this->Encryption->decrypt($payment_type);		
			$id = $this->Encryption->decrypt($ref_id);
			
			$this->request->data['PaymentTransaction']['type'] = $type;
			$this->request->data['PaymentTransaction']['reference_id'] = $id;
			$this->request->data['PaymentTransaction']['created_by'] = $this->Session->read('Auth.User.id');
			
			if($type == PURCHASE_PAYMENT){

				if (!$this->Purchase->exists($id)) 
				{
					throw new NotFoundException('Invalid Purchase');
				}
				
				$purchase = $this->Purchase->find('first',array(
					'conditions'=>array(
						'Purchase.id !='=>BOOL_FALSE,
						'Purchase.id'=>$id,
						'Purchase.is_deleted'=>BOOL_FALSE,
					),
				));
				
				$this->set(compact('purchase'));
				if(!empty($purchase)){
					$this->request->data['PaymentTransaction']['supplier_id'] = $purchase['Supplier']['id'];
					$this->request->data['PaymentTransaction']['sub_total'] = $purchase['Purchase']['total_balance'];
					$this->request->data['PaymentTransaction']['balance'] = (isset($this->request->data['PaymentTransaction']['balance']) and !empty($this->request->data['PaymentTransaction']['balance']))?$this->request->data['PaymentTransaction']['balance']:$purchase['Purchase']['total_balance'];
				}
				
			}
			else if($type == SALES_PAYMENT){
				

				if (!$this->Sale->exists($id)) 
				{
					throw new NotFoundException('Invalid Sale');
				}
				
				$sale = $this->Sale->find('first',array(
					'conditions'=>array(
						'Sale.id !='=>BOOL_FALSE,
						'Sale.id'=>$id,
						'Sale.is_deleted'=>BOOL_FALSE,
					),
				));
				
				$this->set(compact('sale'));
				if(!empty($sale)){
					$this->request->data['PaymentTransaction']['customer_id'] = $sale['Customer']['id'];
					$this->request->data['PaymentTransaction']['sub_total'] = $sale['Sale']['total_balance'];
					$this->request->data['PaymentTransaction']['balance'] = (isset($this->request->data['PaymentTransaction']['balance']) and !empty($this->request->data['PaymentTransaction']['balance']))?$this->request->data['PaymentTransaction']['balance']:$sale['Sale']['total_balance'];
				}			
			}
			
			if($this->request->is('post')){
							
				$this->PaymentTransaction->create();
				if($this->PaymentTransaction->save($this->request->data['PaymentTransaction'])){
					
					if($this->request->data['PaymentTransaction']['type']== PURCHASE_PAYMENT)	{
						$this->Purchase->id = $id;
						$total_pay = $purchase['Purchase']['total_payment'] + $this->request->data['PaymentTransaction']['payment'];
						$total_balance = $purchase['Purchase']['total_amount'] - $total_pay;
						$p_arr =array(
							'total_payment'=>$total_pay,
							'total_balance'=>$total_balance,
							'modified_by'=>$this->Session->read('Auth.User.id'),
						);
					
						if($this->Purchase->save($p_arr)){
						
							$this->Session->setFlash("Payment Added Successfully", 'success');
							$this->redirect($this->referer());
						}
						
					}
					else if($this->request->data['PaymentTransaction']['type']== SALES_PAYMENT)	{
						$this->Sale->id = $id;
						$total_pay = $sale['Sale']['total_payment'] + $this->request->data['PaymentTransaction']['payment'];
						$total_balance = $sale['Sale']['total_amount'] - $total_pay;
						$p_arr =array(
							'total_payment'=>$total_pay,
							'total_balance'=>$total_balance,
							'modified_by'=>$this->Session->read('Auth.User.id'),
						);
					
						if($this->Sale->save($p_arr)){
						
							$this->Session->setFlash("Payment Added Successfully", 'success');
							$this->redirect($this->referer());
						}
						
					}
					
				}
				
			}

		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}

	}
	public function admin_paymentTransactions($payment_type=NULL,$ref_id =NULL){

		$this->loadModel('PaymentTransaction');		
		$cond=array();
		$this->admin_check_login();
		
		$type = $this->Encryption->decrypt($payment_type);		
		$id = $this->Encryption->decrypt($ref_id);
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			if(isset($this->request->data['PaymentTransaction']))
			{					
				$this->Session->write('PaymentTransactionSearch',$this->request->data['PaymentTransaction']);
			}
			else
			{	
				$this->request->data['PaymentTransaction']=$this->Session->read('PaymentTransactionSearch');		
			}		
			if(isset($this->request->data['PaymentTransaction']))				
			{			
				if(isset($this->request->data['PaymentTransaction']['name']) and !empty($this->request->data['PaymentTransaction']['name']))				
				{

					$cond['OR']['Customer.name LIKE']="%".$this->request->data['PaymentTransaction']['name']."%";
					$cond['OR']['Supplier.name LIKE']="%".$this->request->data['PaymentTransaction']['name']."%";
					$cond['OR']['PaymentTransaction.type']=$this->request->data['PaymentTransaction']['name'];
					
				}				
			}		
			
			if(!empty($type) and !empty($id)){
			
				$cond['PaymentTransaction.type']=$type;
				$cond['PaymentTransaction.reference_id']=$id;
			}
			
			$conditions = array(
				'PaymentTransaction.id !=' => BOOL_FALSE,
				'PaymentTransaction.is_deleted' => BOOL_FALSE,
			);
			
			$conditions=array_merge($conditions,$cond);
			
			$this->Paginator->settings = array(
				'PaymentTransaction' => array(
					'conditions' => $conditions,
					'order' => array('PaymentTransaction.id' => 'DESC'),
					'limit' => PAGINATION_LIMIT,
					'recursive' => 2
			));
			$payment_transactions = $this->Paginator->paginate('PaymentTransaction');
			$this->set(compact('payment_transactions'));		
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
	}
	
	public function admin_resetPaymentTransactionSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('PaymentTransactionSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function MysqlDate($date = NULL){
	
		if(!empty($date)){
			
			$new = date('Y-m-d',strtotime($date));			
			return $new;
		}
		
	}
	
	/*
	Amit Sahu
	30.01.17
	Item List
	*/
	public function admin_itemList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Item');
		$this->loadModel('Category');
		$this->loadModel('Publisher');
		$this->loadModel('Author');
		$this->loadModel('City');	
		$this->loadModel('Location');
		$this->loadModel('DiscountLevel');		
		$this->loadModel('Unit');	
        $this->loadModel('UserProfile');
		$UserProfile=$this->Session->read('UserProfile');
		
		
	    $unitsList=$this->Unit->find('list',array(
				'conditions'=>array(
				'Unit.id !='=>BOOL_FALSE,
				'Unit.is_deleted'=>BOOL_FALSE,
				'Unit.is_active'=>BOOL_TRUE,
				)
				));			
		$this->set(compact('unitsList'));	
			
	  
		if(isset($this->request->data['Item']))
		{					
		$this->Session->write('ItemSearch',$this->request->data['Item']);
		}
		else
		{	
		$this->request->data['Item']=$this->Session->read('ItemSearch');
		}	

		if(isset($this->request->data['Item']))				
		{			
			 
				if(isset($this->request->data['Item']['name']) and !empty($this->request->data['Item']['name']))				
			{
				$cond['Item.name LIKE']='%'.$this->request->data['Item']['name'].'%';
				
			}			 
		}
			
		
						
		$conditions = array(
			'Item.id !=' => BOOL_FALSE,
			'Item.is_deleted' => BOOL_FALSE,
			'Item.is_active' => BOOL_TRUE,
			'Item.user_profile_id' => $this->Session->read('Auth.User.user_profile_id')
		);
		
		$conditions=array_merge($conditions,$cond);
	
		$this->Paginator->settings = array(
			'Item' => array(
				'conditions' => $conditions,
				
				'order' => array('Item.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$items = $this->Paginator->paginate('Item');
		
	//	$ItId = $items['items'][];
		
		
		//echo'<pre>';print_r($items);exit;
		
		$this->set(compact('items'));
         $this->set(compact('UserProfile'));	    		
			
    }
	/*
	Amit Sahu
	reset Item search
	30.01.17
	*/
	public function admin_resetItemSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('ItemSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	08.02.17
	Stock Item
	*/
	
	public function admin_stockItem() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Item');
		$this->loadModel('Category');
		$this->loadModel('Publisher');
		$this->loadModel('Author');
		$this->loadModel('Location');
		
		
		$catList=$this->Category->getCategoryList();
		$this->set(compact('catList'));
		
		$publisherList=$this->Publisher->getPublisherList();
		$this->set(compact('publisherList'));
		
		$authorList=$this->Author->getAuthorList();
		$this->set(compact('authorList'));
		
		$locationsList=$this->Location->getShopList();
		$this->set(compact('locationsList'));
		
		$fields = array('Location.id','Location.name','Location.code');
		$conditions = array('Location.is_deleted !='=>BOOL_TRUE,'Location.is_active !='=>BOOL_FALSE);
		$locations=$this->Location->getAllLocation($fields,$conditions);
		$this->set(compact('locations'));
		
		$stockitems=array();
		if(isset($this->request->data['Item']))
		{					
		$this->Session->write('ItemStockSearch',$this->request->data['Item']);
		}
		else
		{	
		$this->request->data['Item']=$this->Session->read('ItemStockSearch');
		
		}		
		if(isset($this->request->data['Item']))				
		{	
			 if(isset($this->request->data['Item']['item_id']) and !empty($this->request->data['Item']['item_id']))				
			{
				$cond['Item.id']=$this->request->data['Item']['item_id'];
				$item=$this->Item->findById($this->request->data['Item']['name']);
				if(!empty($item))
				{
					$itemName=$item['Item']['name'];
				}
			}
			
			if(isset($this->request->data['Item']['cat_id']) and !empty($this->request->data['Item']['cat_id']))				
			{
				$cond['Item.category_id']=$this->request->data['Item']['cat_id'];
			}	

			if(isset($this->request->data['Item']['pub_id']) and !empty($this->request->data['Item']['pub_id']))				
			{
				$cond['Item.publisher_id']=$this->request->data['Item']['pub_id'];
			}

			if(isset($this->request->data['Item']['author_id']) and !empty($this->request->data['Item']['author_id']))				
			{
				$cond['Item.author_id']=$this->request->data['Item']['author_id'];
			}	
			
			$conditions = array(
			'Item.id !=' => BOOL_FALSE,
			'Item.is_deleted' => BOOL_FALSE,
			'Item.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Item' => array(
				'conditions' => $conditions,
				'order' => array('Item.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$stockitems = $this->Paginator->paginate('Item');
		
		}	

		$this->set(compact('stockitems'));
						
				
		
		
    }
		/*
	Amit Sahu
	reset ItemStock search
	30.01.17
	*/
	public function admin_resetItemStockSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('ItemStockSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	Update Stocks
	08.02.17
	*/
public function admin_updateStock()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Item');
		$this->loadModel('Stock');
		$this->loadModel('StockRemark');
		$this->loadModel('PurchaseSale');
		
					
		
		if ($this->request->is('ajax')) 
			{
			
				$id=$this->request->data['UpdateStock']['item_id'];
				$location_id=$this->request->data['UpdateStock']['location'];
				$qty=$this->request->data['UpdateStock']['qty'];
				$remark=$this->request->data['UpdateStock']['remark'];
				if(!empty($id))
					{
						$conditions=array('Stock.item_id'=>$id,'Stock.location_id'=>$location_id);
						$fields=array('Stock.id');
						$stockData=$this->Stock->getallStock($conditions,$fields);
						if(!empty($stockData))
						{
						$stockId=$stockData['Stock']['id'];
						$this->request->data['Stock']['id']=$stockData['Stock']['id'];
						$this->request->data['Stock']['quantity']=$qty;
						}else{
						$this->request->data['Stock']['item_id']=$id;
						$this->request->data['Stock']['quantity']=$qty;
						$this->request->data['Stock']['location_id']=$location_id;
						} 
						$this->Stock->create();
						if ($this->Stock->save($this->request->data)) 
							{
								// Update stock remark 
								$this->StockRemark->create();
								if(empty($stockId))
								{
								$stockId=$this->Stock->getInsertID();
								}
								$this->request->data['StockRemark']['stock_id']=$stockId;
								$this->request->data['StockRemark']['location_id']=$location_id;
								$this->request->data['StockRemark']['item_id']=$id;
								$this->request->data['StockRemark']['qty']=$qty;
								$this->request->data['StockRemark']['user_id']=$this->Session->read('Auth.User.id');
								$this->request->data['StockRemark']['remark']=$remark;
								$this->StockRemark->save($this->request->data['StockRemark']);
								// End Update stock remark 
							
								$conditions=array('Stock.item_id'=>$id);
								$fields=array('Stock.quantity');
								$allstockData=$this->Stock->getallStockData($conditions,$fields);
								
								// Update purchase sale table
								$itemData=$this->Item->findById($id);
								$this->PurchaseSale->deleteAll(array('PurchaseSale.item_id'=>$id,'PurchaseSale.location_id'=>$location_id));
								$psid=0;
								$item_id=$id;
								$category_id=$itemData['Item']['category_id'];
								$stock=$qty;
								$price=$stock*$itemData['Item']['price'];
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHASE,$category_id,$location_id,$qty,$stock,$price);
								// Update purchase sale table
								$totalBook="";
								if(!empty($allstockData))
								{
									foreach($allstockData as $row)
									{
									$stockArr[]=$row['Stock']['quantity'];
									}
								}
								 $totalBook=array_sum($stockArr);
								echo json_encode(array('status'=>'1000','message'=>'Stock update successfully','item_id'=>$id,'loc_id'=>$location_id,'qty'=>$qty,'total'=>$totalBook));
							}	
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Stock could not be update'));
						}
					}
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	
	/**
	@created by : Mohammad Masood
	@created on : 01 March 2017 
	**/
	public function admin_addStock()
	{
		$cond=array();
		$this->admin_check_login();
			
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			$this->loadModel('Stock');
			$this->loadModel('Item');
			$this->loadModel('Location');
			$this->loadModel('PurchaseSale');
			
			if($this->request->is('post')){
				
				
				if(!empty($this->request->data["Stock"])){
					
					$location_id = $this->request->data["Stock"]["location_id"];
					$save_i = 0;
					$error_i = 0;
					if(!empty($location_id)){
					
						foreach($this->request->data["StockDetail"] as $v){					
							
							if(!empty($v["item_id"]) and !empty($v["quantity"])){
								$stock = $this->Stock->find("first",array(
									"conditions"=>array(										
										"Stock.location_id"=>$location_id ,
										"Stock.item_id"=>$v["item_id"],
									),
									"recursive"=>-1,
								));
								if(!empty($stock)){
									$this->Stock->id = $stock["Stock"]["id"];
									$arr = array(
									"quantity"=>$v["quantity"]
									);
									if($this->Stock->save($arr)){
										$save_i++;
									}
									else{
										$error_i++;
									}
											
								}else{
									$this->Stock->create();
									$this->request->data['Stock']['item_id']=$v["item_id"];
									$this->request->data['Stock']['location_id']=$location_id;
									$this->request->data['Stock']['quantity']=$v["quantity"];
									
									if($this->Stock->save($this->request->data['Stock']))
									{
										$save_i++;
									}
									else{
										$error_i++;
									}
								}								
								
								// Update purchase sale table
								$this->PurchaseSale->deleteAll(array('PurchaseSale.item_id'=>$v["item_id"],'PurchaseSale.location_id'=>$location_id));
								$itemData=$this->Item->findById($v["item_id"]);
								$psid=0;
								$item_id=$v["item_id"];
								$category_id=$itemData['Item']['category_id'];
								$stock=$v["quantity"];
								$qty=$v["quantity"];
								$price=$stock*$itemData['Item']['price'];
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHASE,$category_id,$location_id,$qty,$stock,$price);
								// Update purchase sale table
							}						
						}
					}
					else{
						$this->Session->setFlash("Please select location", 'error');			
					}
					
					
				}
				
				$this->Session->setFlash('Items stock updated. <label class="label label-xs label-success">total updated records : '.$save_i.'</label> , <label class="label label-xs label-danger">total errors : '.$error_i.' </label>', 'success');				
					return $this->redirect(array('controller'=>'managements','action' => 'addStock','admin'=>true,'ext'=>URL_EXTENSION));
			}
			
			$locations = $this->Location->find("list",array(
				"conditions" =>array(
					"Location.id !="=>BOOL_FALSE,
					"Location.is_deleted"=>BOOL_FALSE,
					"Location.is_active"=>BOOL_TRUE,
					
				),
			));			
			$this->set(compact("locations"));
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }		
	
	public function admin_getStockData($code = NULL) {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');
		$this->loadModel('Stock');
		
        if ($this->request->is('ajax')) 
		{
			if(!empty($this->request->data["code"])){
				$code = strtoupper($this->request->data["code"]);
				$location_id = $this->request->data["location_id"];
						
				$item = $this->Item->find("first",array(
				"fields"=>array("Item.id","Item.name","Item.price"),
				"conditions" =>array(
					"Item.id !="=>BOOL_FALSE,
					"Item.code"=>$code,
					"Item.is_active"=>BOOL_TRUE,
					"Item.is_deleted"=>BOOL_FALSE,					
				),				
				'contain'=>array("Publisher"=>array("name")),
				"recursive"=> 2,
				));
			//	echo '<pre>';print_r($item);echo'</pre>';exit;		
				
				$itemDetails="";
				if(!empty($item))
				{
				$itemDetails='Item : '.$item['Item']['name'].', Publisher : '.$item['Publisher']['name'].', Price : '.$item['Item']['price']; 	
				}
				if(!empty($item)){
					
					$qty = 0;
					$stock = $this->Stock->find("first",array(
					"fields"=>array("Stock.quantity"),
					"conditions" =>array(
					"Stock.id !="=>BOOL_FALSE,
					"Stock.item_id"=>$item["Item"]["id"],
					"Stock.location_id"=>$location_id,
					"Stock.is_active"=>BOOL_TRUE,
					"Stock.is_deleted"=>BOOL_FALSE,					
					),				
					"recursive"=> -1,
					));
					if(!empty($stock)){
						$qty = $stock["Stock"]["quantity"];
					}
					
					echo json_encode(array("status"=>200,"item_id"=>$item["Item"]["id"],"quantity"=>$qty,'itemData'=>$itemDetails));
				}
				else{
					echo json_encode(array("status"=>404,"content"=>"Item not found"));
				}
				
			}
			exit;
		}	
	}
	
	
	
	/*Neha Umredkar
	30/08/2017
	User Profile*/
	
	function admin_addUserProfile(){
		$this->admin_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			$this->loadModel('UserProfile');
			$this->loadModel('Unit');
			$this->loadModel('State');

		   $unitLIst=$this->Unit->getUnitList();
		   $this->set(compact('unitLIst'));
		   
		   $state=$this->State->getStateList();
		   $this->set(compact('state'));
			
			$userdata=$this->UserProfile->find('first',array('conditions'=>array('UserProfile.id'=>$this->Session->read('Auth.User.user_profile_id')),'fields'=>array('UserProfile.id')));
			if(!empty($userdata))
			{
				$this->request->data['UserProfile']['id']=$userdata['UserProfile']['id'];
				$id=$userdata['UserProfile']['id'];
			}
					
			
			if ($this->request->is('post') or $this->request->is('put')) 
			{	
			//echo'<pre>';print_r($this->request->data);exit;
				
				if(!empty($this->request->data['UserProfile']['logo']['name']))
				{						
					$this->Img = $this->Components->load('Img');			
					$newName = strtotime("now");
					$rnd = rand(5, 15);
					$newName = $newName.$rnd;
					$ext = $this->Img->ext($this->request->data['UserProfile']['logo']['name']);			
					
					$filesize=$this->request->data['UserProfile']['logo']['size'];
					$max_size = 1024*1024;
					
					if($filesize > $max_size){
						$this->Session->setFlash("The file size must be less than 2 MB","error");
					}
					
					$origFile = $newName . '.' . $ext;
					$dst = $newName .  '.'.$ext;	
					$targetdir = WWW_ROOT . 'img/userImg';			
					echo $upload = $this->Img->upload($this->request->data['UserProfile']['logo']['tmp_name'], $targetdir, $origFile);		
			
					if($upload == 'Success') 
					{	
						$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 'img/userImg', $dst, 400, 400, 1, 0);
						$this->request->data['UserProfile']['logo'] = $dst;
					}
					else 
					{
						$this->Session->setFlash($upload, 'error');
						$this->request->data['UserProfile']['logo'] = '';
						
					}
				}
				else 
				{
					$this->request->data['UserProfile']['logo'] = '';
				}
				
				$this->UserProfile->create();		

				if ($this->UserProfile->save($this->request->data)) 
				{
					
					
					$this->Session->setFlash('User Profile has been saved', 'success');
					return $this->redirect($this->referer());
				} 
				else 
				{
					
					$this->Session->setFlash('User Profile could not be saved. Please, try again.', 'error');
				}
			}else{
				
			if(!empty($userdata))
			{
			
			$this->request->data=$this->UserProfile->findById($id);
			}
			}	
		}
		else
		{
			
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	/*
	Amit Sahu
	26.07.18
	User Authentication
	*/
	function admin_userAuthentication(){
		$this->admin_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
			$userList=$this->User->getUserList($user_profile_id);
			$this->set(compact('userList'));
		}
		else
		{
			
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	
	/*
	Amit Sahu
	get Navigation list by user
	26.07.18
	*/
	public function admin_getNavByUser() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('UserAuthentication');
		$this->loadModel('NavigationMaster');
		$this->loadModel('ActionMaster');
		$this->loadModel('ActionPermission');
		
        if ($this->request->is('ajax')) 
		{
			$user=$this->request->data['id'];
			$navs=$this->NavigationMaster->find('all',array('conditions'=>array('NavigationMaster.is_active'=>BOOL_TRUE,'NavigationMaster.is_deleted'=>BOOL_FALSE),'fields'=>array('NavigationMaster.name','NavigationMaster.id','NavigationMaster.nav_type'),'order'=>array('NavigationMaster.id','NavigationMaster.parent_id')));
		
			$data="";
			if(!empty($navs))
			{
				$i=0;
				foreach($navs as $row)
				{
					$id=$row['NavigationMaster']['id'];
					$icon='<i class="fa fa-square" aria-hidden="true" style="color:#bc72f8"></i>';
					if($row['NavigationMaster']['nav_type']==2)
					{
						$icon='<i class="fa fa-angle-double-right" aria-hidden="true" style="margin-left:20px"></i><i class="fa fa-angle-double-right" aria-hidden="true"></i>';
					}
					
					$status=array();
					$status=$this->UserAuthentication->find('first',array('conditions'=>array('UserAuthentication.is_active'=>BOOL_TRUE,'UserAuthentication.is_deleted'=>BOOL_FALSE,'UserAuthentication.nav_id'=>$id,'UserAuthentication.user_id'=>$user)));
					$permission=0;
					$auth_id="";
					if(!empty($status))
					{
						$permission=$status['UserAuthentication']['permission'];
						$auth_id=$status['UserAuthentication']['id'];
					}
					$checked="";
					if($permission==1)
					{
						$checked='checked';
					}
					$data.='
							<tr>	
							<td >'.$icon.' '.$row['NavigationMaster']['name'].'</td>
							<td>
							<div class="switch">
								<input id="cmn-toggle-'.$id.'" auth_id="'.$auth_id.'" class="cmn-toggle cmn-toggle-round-flat " '.$checked.' type="checkbox" value="'.$permission.'" >
								<label for="cmn-toggle-'.$id.'" no="'.$id.'" class="slide_btn_new"></label>
								</div>
							</td>
							</tr>
							';
				}
				
				//2nd table
				$action=$this->ActionMaster->find('all',array('conditions'=>array('ActionMaster.is_active'=>BOOL_TRUE,'ActionMaster.is_deleted'=>BOOL_FALSE),'fields'=>array('ActionMaster.action','ActionMaster.id','ActionMaster.constant'),'order'=>array('ActionMaster.id')));
	
				$action_data='';
				if(!empty($action))
				{
					foreach($action as $act)
					{
						$action_id=$act['ActionMaster']['id'];
						$icon='<i class="fa fa-square" aria-hidden="true" style="color:#bc72f8"></i>';
						
						$action_status=array();
						$action_status=$this->ActionPermission->find('first',array('conditions'=>array('ActionPermission.is_active'=>BOOL_TRUE,'ActionPermission.is_deleted'=>BOOL_FALSE,'ActionPermission.action_id'=>$action_id,'ActionPermission.user_id'=>$user)));
					
						$action_permission=0;
						$action_auth_id="";
						if(!empty($action_status))
						{
							$action_permission=$action_status['ActionPermission']['permission'];
							$action_auth_id=$action_status['ActionPermission']['id'];
						}
						$action_checked="";
						if($action_permission==1)
						{
							$action_checked='checked';
						}
						$action_data.='
								<tr>	
								<td >'.$icon.' '.$act['ActionMaster']['action'].'</td>
								<td>
								<div class="switch">
									<input id="1cmn-toggle-'.$action_id.'" auth_id="'.$action_auth_id.'" class="cmn-toggle cmn-toggle-round-flat " '.$action_checked.' type="checkbox" value="'.$action_permission.'">
									<label for="1cmn-toggle-'.$action_id.'" no="'.$action_id.'" class="slide_btn_action"></label>
									</div>
								</td>
								</tr>
								';
					}
				}
					
					echo json_encode(array("status"=>1000,"mydata"=>$data,'action_data'=>$action_data));
				}
				else{
					echo json_encode(array("status"=>1001,"content"=>"Item not found"));
				}
				
			}
	
	}
	/*
	Amit Sahu
	26.07.18
	Change User Authentication
	*/
	
	public function admin_changeUserAuthentication() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('UserAuthentication');
		
        if ($this->request->is('ajax')) 
		{
			//$auth_id=$this->request->data['auth_id'];
			$nav_id=$this->request->data['id'];
			$status=$this->request->data['status'];
			$user_id=$this->request->data['user_id'];
			
			$exist=$this->UserAuthentication->find('first',array('conditions'=>array('UserAuthentication.is_active'=>BOOL_TRUE,'UserAuthentication.is_deleted'=>BOOL_FALSE,'UserAuthentication.nav_id'=>$nav_id,'UserAuthentication.user_id'=>$user_id)));
			if(!empty($exist))
			{
				$this->request->data['UserAuthentication']['id']=$exist['UserAuthentication']['id'];
			}
			
			if($status==0)
			{
				$this->request->data['UserAuthentication']['permission']=1;
			}else{
				$this->request->data['UserAuthentication']['permission']=0;
			}
			
			$this->request->data['UserAuthentication']['nav_id']=$nav_id;
			
			$this->request->data['UserAuthentication']['user_id']=$user_id;
			if($this->UserAuthentication->save($this->request->data['UserAuthentication']))
			{
				$value=$this->request->data['UserAuthentication']['permission'];
				echo json_encode(array("status"=>1000,"message"=>'','value'=>$value));
			}
			else{
				echo json_encode(array("status"=>1001,"message"=>"Item not found"));
			}
			
		}
	
	}
	
	/*
	Rajeshwari  Lokhande
	27/7/18
	change authentication for action btn
	*/
	public function admin_changeUserAuthenticationForAction()
	{
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('ActionPermission');
		
        if ($this->request->is('ajax')) 
		{
			//$auth_id=$this->request->data['auth_id'];
			$action_id=$this->request->data['id'];
			$status=$this->request->data['status'];
			$user_id=$this->request->data['user_id'];
			
			$exist=$this->ActionPermission->find('first',array('conditions'=>array('ActionPermission.is_active'=>BOOL_TRUE,'ActionPermission.is_deleted'=>BOOL_FALSE,'ActionPermission.action_id'=>$action_id,'ActionPermission.user_id'=>$user_id)));
			if(!empty($exist['ActionPermission']['id']))
			{
				$this->request->data['ActionPermission']['id']=$exist['ActionPermission']['id'];
					if($status==0)
				{
					$this->request->data['ActionPermission']['permission']=1;
				}else{
					$this->request->data['ActionPermission']['permission']=0;
				}
				
				$this->request->data['ActionPermission']['action_id']=$action_id;
				
				$this->request->data['ActionPermission']['user_id']=$user_id;
				if($this->ActionPermission->save($this->request->data['ActionPermission']))
				{
					$value=$this->request->data['ActionPermission']['permission'];
					echo json_encode(array("status"=>1000,"message"=>'','value'=>$value));
				}
			}
			
			
			else{
				echo json_encode(array("status"=>1001,"message"=>"Item not found"));
			}
			
		}
	}
}