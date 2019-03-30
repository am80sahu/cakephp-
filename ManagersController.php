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
class ManagersController extends AppController 
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
		'manager_login',
		'manager_logout',
		);		
		
        $this->Auth->allow($authAllowedActions);        		
		if (!in_array($this->Auth->user('role_id'), array(MANAGER_ROLE_ID))) 
		{
            $this->Auth->logout();
        }

        //set layout based on user session
        if ($this->Auth->user()) {
            $this->layout = 'manager/inner';
        } else {
            $this->layout = 'manager/outer';
        }
    }
	
	/**************************login******************************/
	
	public function manager_index() 
	{
				
							
	}
	
    //function for admin login   
    public function manager_login() 
	{
	
		$this->layout = 'manager/outer';		
		if ($this->request->is('post'))
		 {	 	
			
				$email = !empty($this->request->data ['User'] ['email']) ? trim($this->request->data ['User'] ['email']) : null;
				$password = !empty($this->request->data ['User'] ['password']) ? trim(AuthComponent::password($this->request->data['User']['password'])) : null;
				
				$type = 'first';
				$conditions = array(
					'User.email' => $email,
					'User.password' => $password,
					'User.role_id' => array(MANAGER_ROLE_ID),
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
						'UserSession.role_id'=>MANAGER_ROLE_ID,
						'UserSession.current_time >='=>date('H:i:s',time() - 30),
						'DATE(UserSession.created)'=>date('Y-m-d'),
						'UserSession.logout_time'=>NULL,
						'UserSession.is_logged_out'=>BOOL_FALSE,

						),
					'recursive'=>-1
						
					));	
					
					if ($this->Auth->login()) {
						
						$UserProfile=$this->UserProfile->find('first',array('conditions'=>array('UserProfile.id !='=>BOOL_FALSE,'UserProfile.is_active !='=>BOOL_FALSE,'UserProfile.is_deleted !='=>BOOL_TRUE)));
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
    public function manager_logout() {
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
    public function manager_dashboard() {
		$this->layout = 'manager/inner';	
		$this->manager_check_login();
		
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
	public function manager_changePassword()
	{
		$this->manager_check_login();								
		
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
					'User.role_id'=>manager_ROLE_ID,
					'User.is_deleted'=>BOOL_FALSE,
					'User.is_active'=>BOOL_TRUE,
					'User.password'=>$old_password
					
					),
					'recursive'=>-1
					));
					
					if(!empty($userdata))
					{
						if(isset($this->request->data['User']['password']) and !empty($this->request->data['User']['confirm_password']))
						{	
							if($this->request->data['User']['password']==$this->request->data['User']['confirm_password'])		
							{		
								$this->request->data['User']['password']=$this->request->data['User']['password'];				
								
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
	
		
	public function manager_findAccount() {

	 $this->loadModel('User');
	 
		 if($this->request->is('post')) {	 	
		 
			 $userdata=$this->User->find('first',array(		
			 'conditions'=>array(
			 'email'=>$this->request->data['User']['email'],
			 'role_id'=>manager_ROLE_ID,
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
	
	public function manager_sendPasswordResetLink($email)
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		
		$this->loadModel('User');
		$email=$this->Encryption->decrypt($email);		
		$userdata = $this->User->findByEmailAndRoleId($email,manager_ROLE_ID);		
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
	public function manager_resetPassword($email,$token)
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
	public function manager_usersList() 
	{
		$cond=array();
		$this->manager_check_login();		
		
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
     * @name    : manager_viewUser
     * @acces   : public 
     * @param   : null
     * @return  : void
     * @created : 15 November 2016
     * @modified: 15 November 2016

     */
    public function manager_viewUser($id=NULL) 
	{
		$this->manager_check_login();				
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
	
	public function manager_addUser() 
	{
		$this->manager_check_login();				
		
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
				'Role.id NOT IN'=>array(BOOL_FALSE,CUSTOMER_ROLE_ID,CO_manager_ROLE_ID),
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
	
	public function manager_editUser($id = null) 
	{
		$this->manager_check_login();				
		
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
				'Role.id NOT IN'=>array(BOOL_FALSE,CUSTOMER_ROLE_ID,CO_manager_ROLE_ID),
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
	
	
	
	public function manager_resetUserSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->manager_check_login();		
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
			
	 public function manager_deleteUser($id = null) 
	 {
	 	
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{	
			$this->autoRender = FALSE;
			$this->layout = FALSE;						
			$this->manager_check_login();	
			
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
	
	public function manager_toggleUserStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->manager_check_login();
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
	
	
	/**
	@ Mohammad Masood
	@ Function to check unique email Id
	@ 06-06-2016
	**/
	public function manager_unique_email()
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
	public function manager_unique_email_emp()
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
	public function manager_unique_mobile()
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
	
	
	/*Neha Umredkar
	30/08/2017
	User Profile*/
	
	function manager_addUserProfile(){
		$this->manager_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			$this->loadModel('UserProfile');
			$this->loadModel('Unit');
			$this->loadModel('State');
			$this->loadModel('InvoiceFormat');

		   $unitLIst=$this->Unit->getUnitList();
		   $this->set(compact('unitLIst'));
		   
		   $state=$this->State->getStateList();
		   $this->set(compact('state'));
		   
		   $formatlist=$this->InvoiceFormat->invoiceFormatList();
		   $this->set(compact('formatlist'));
			
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
					
						$upid=$this->UserProfile->getInsertID();
						$roleArray=array(SHOP_ROLE_ID,ADMIN_ROLE_ID);
						
						foreach($roleArray as $row)
						{
							
							$this->User->create();
							$this->request->data['User']['registered_by']=$this->Auth->User('id');
							$this->request->data['User']['role_id']=$row;
							$this->request->data['User']['user_profile_id']=$upid;
							//$this->request->data['User']['user_id']=;
							if($row==SHOP_ROLE_ID)
							{
								
							$this->request->data['User']['email']=$this->request->data['UserProfile']['email'];
							}else{
								
								$emailArr=explode('@',$this->request->data['UserProfile']['email']);
								$this->request->data['User']['email']=$emailArr[0].'admin@'.$emailArr[1];
							}
							$this->request->data['User']['password']=12345678;
							$this->request->data['User']['name']=$this->request->data['UserProfile']['name'];
							$this->request->data['User']['gender']=1;
							$this->request->data['User']['address']=$this->request->data['UserProfile']['address'];
							$this->request->data['User']['mobile_no']=$this->request->data['UserProfile']['phone'];
							$this->request->data['User']['country']=1;
							$this->request->data['User']['state']=$this->request->data['UserProfile']['state'];				
							
							if($this->User->save($this->request->data['User']))
							{
								$id = $this->User->getInsertID();
								$user_id = 'CB'.sprintf('%04d', $id);
								$this->User->id=$id;
						
								$this->User->saveField('user_id',$user_id);
								if($row==SHOP_ROLE_ID)
								{
									
									
									$emailId=$this->request->data['UserProfile']['email'];
									$password = 12345678;
									$cust_name=$this->request->data['UserProfile']['name'];

									
									$emailData ['receiver_email'] = !empty($emailId) ? trim($emailId) : NULL;
									$emailData ['NAME'] = !empty($cust_name) ? trim($cust_name) : NULL;
									$emailData ['password'] = !empty($password) ? trim($password) : NULL;
									
									$emailResult = $this->Email->sendRegSuceesCustomer($emailData);
								}
								
							}	
						}
						// Email
		
						$emailId='amit.tantransh@gmail.com';
						$cust_name='Amit';
						$password='Paswword123!@#';
						
						$emailData ['receiver_email'] = !empty($emailId) ? trim($emailId) : NULL;
						$emailData ['NAME'] = !empty($cust_name) ? trim($cust_name) : NULL;
						$emailData ['password'] = !empty($password) ? trim($password) : NULL;
						
						$emailResult = $this->Email->sendRegSuceesCustomer($emailData);

						
						// End Email	
					$this->Session->setFlash('User Profile has been saved', 'success');
					return $this->redirect(array('controller'=>'managers', 'action'=>'userProfileList', 'manager'=>true));
				} 
				else 
				{
					
					$this->Session->setFlash('User Profile could not be saved. Please, try again.', 'error');
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
	User Profile List
	*/
	public function manager_userProfileList() 
	{
		$cond=array();
		$this->manager_check_login();		
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('UserProfile');	
							
			if(isset($this->request->data['UserProfile']))
			{					
				$this->Session->write('UserProfileSearch',$this->request->data['UserProfile']);
			}
			else
			{	
				$this->request->data['UserProfile']=$this->Session->read('UserProfileSearch');		
			}		
			if(isset($this->request->data['UserProfile']))				
			{			
				if(isset($this->request->data['UserProfile']['name']) and !empty($this->request->data['UserProfile']['name']))				
				{
					$cond['OR']['UserProfile.name LIKE']=$this->request->data['UserProfile']['name']."%";
					
				}				
			}		
						
				$conditions = array(
					'UserProfile.id !=' => BOOL_FALSE,
					'UserProfile.is_deleted' => BOOL_FALSE,
				);
				
				$conditions=array_merge($conditions,$cond);		
				
				$this->Paginator->settings = array(
					'UserProfile' => array(
						'conditions' => $conditions,
						'order' => array('UserProfile.id' => 'DESC'),
						'limit' => PAGINATION_LIMIT,
						'recursive' =>1
				));
				$usersProfiles = $this->Paginator->paginate('UserProfile');
				$this->set(compact('usersProfiles'));		
				
				
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	Edit User Profile
	21.09.17
	*/
	function manager_editUserProfile($id){
		$this->manager_check_login();				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			$this->loadModel('UserProfile');
			$this->loadModel('Unit');
			$this->loadModel('State');
			$this->loadModel('InvoiceFormat');

		   $unitLIst=$this->Unit->getUnitList();
		   $this->set(compact('unitLIst'));
		   
		   $state=$this->State->getStateList();
		   $this->set(compact('state'));
		   
		      $formatlist=$this->InvoiceFormat->invoiceFormatList();
		   $this->set(compact('formatlist'));
			
		   
		   $id=$this->Encryption->decrypt($id);
		   $this->request->data['UserProfile']['id']=$id;
			
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
				
			

				if ($this->UserProfile->save($this->request->data)) 
				{
					
					
					$this->Session->setFlash('User Profile has been saved', 'success');
					return $this->redirect(array('controller'=>'managers', 'action'=>'userProfileList', 'manager'=>true));
				} 
				else 
				{
					
					$this->Session->setFlash('User Profile could not be saved. Please, try again.', 'error');
				}
			}else{
				 
				 $this->request->data=$this->UserProfile->findById($id);
			}
		}
		else
		{
			
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	/*
	Delete User Userprofile or clientIp
	Amit Sahu
	21.09.17
	*/
	  public function manager_deleteUserProfile($id = null) 
	{
	
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->manager_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('UserProfile');
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{		
			$this->UserProfile->id = $id;
			if (!$this->UserProfile->exists()) 
			{
				throw new NotFoundException('Invalid Client');
			}
			if ($this->UserProfile->saveField('is_deleted',BOOL_TRUE)) 
			{
				$this->UserProfile->saveField('is_active',BOOL_FALSE);
				$this->Session->setFlash('Client deleted','success');
				return $this->redirect($this->referer());
			}
			
			$this->Session->setFlash('Client was not deleted', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
/*
Amit Sahu
Reset User profile search
21.09.17
*/	
	public function manager_resetUserProfileSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->manager_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('UserProfileSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	 Kajal kurrewar
	 Item unit list
	 09-08-2017
	*/
	public function manager_unitList() 
	{
		$cond=array();
		$this->manager_check_login();
		$this->loadModel('Unit');
		
		if(isset($this->request->data['Unit']))
		{					
		 $this->Session->write('UnitSearch',$this->request->data['Unit']);
		}
		else
		{	
		 $this->request->data['Unit']=$this->Session->read('UnitSearch');
		}		
		if(isset($this->request->data['Unit']))				
		{			
			if(isset($this->request->data['Unit']['name']) and !empty($this->request->data['Unit']['name']))				
			{
				$cond['Unit.name LIKE']="%".$this->request->data['Unit']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Unit.id !=' => BOOL_FALSE,
			'Unit.is_deleted' => BOOL_FALSE,
			'Unit.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Unit' => array(
				'conditions' => $conditions,
				'order' => array('Unit.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$units = $this->Paginator->paginate('Unit');
		$this->set(compact('units'));		
		
		
    }
	/*
	kajal kurrewar 
	add unit
	09-08-2017
	*/
	public function addUnit()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Unit');
		if ($this->request->is('ajax')) 
			{
				$this->Unit->create();
				if ($this->Unit->save($this->request->data)) 
				{
					$id =$this->Unit->getInsertID();
					$code=$this->request->data['Unit']['code'];
					$name=$this->request->data['Unit']['name'];
					echo json_encode(array('status'=>'1000','message'=>'Distributor added successfully', 'id'=>$id,'code'=>$code,'name'=>$name,));
				} 
				else 
				{
				    echo json_encode(array('status'=>'1001','message'=>'Distributor could not be added'));
				}
			}				
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	 }
	/*
	kajal kurrewar
	09-08-2017
	*/
	public function editUnit()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Unit');
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Unit']['id'];
				if(!empty($id))
					{
						if ($this->Unit->save($this->request->data)) 
						{
							$code=$this->request->data['Unit']['code'];
							$name=$this->request->data['Unit']['name'];
							
							echo json_encode(array('status'=>'1000','message'=>'Unit edit successfully','id'=>$id,'code'=>$code,'name'=>$name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Unit could not be added'));
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
	kajal kurrewar
	delete unit
	*/
	public function deleteUnit() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Unit');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Unit->id =$id;
				if (!$this->Unit->exists()) 
					{
						throw new NotFoundException('Invalid Unit');
					}
			    if ($this->Unit->saveField('is_deleted',BOOL_TRUE)) 
				   {
						$this->Unit->saveField('is_active',BOOL_FALSE);
					    echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Unit Deleted successfully'));
				   }
				   else
				   {
					    echo json_encode(array('status'=>'1001','message'=>'Unit could not be Deleted'));
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
	Reset Unit Search
	21.09.17
	*/
	public function manager_resetUnitSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('UnitSearch');
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
	02.02.17
	Legder List
	*/
	public function manager_ledgerList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Ledger');
		$this->loadModel('Group');
		
		if(isset($this->request->data['Ledger']))
		{					
		$this->Session->write('LedgerSearch',$this->request->data['Ledger']);
		}
		else
		{	
		$this->request->data['Ledger']=$this->Session->read('LedgerSearch');
		
		}		
		if(isset($this->request->data['Ledger']))				
		{			
			if(isset($this->request->data['Ledger']['name']) and !empty($this->request->data['Ledger']['name']))				
			{
				$cond['Ledger.name LIKE']="%".$this->request->data['Ledger']['name']."%";
			}	
			
		}		
		
						
		$conditions = array(
			'Ledger.id !=' => BOOL_FALSE,
			'Ledger.is_deleted' => BOOL_FALSE,
			'Ledger.is_active' => BOOL_TRUE,
			'Ledger.user_profile_id' => BOOL_FALSE,
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Ledger' => array(
				'conditions' => $conditions,
				'order' => array('Ledger.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$ledgers = $this->Paginator->paginate('Ledger');
		$this->set(compact('ledgers'));		
		
		
    }
	 /**
     Amit Sahu
	 12.10.17
	 Add Email template

     */
    public function manager_addEmailTemplate() {
        $this->loadModel('EmailTemplate');
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->EmailTemplate->set($this->request->data);
            if ($this->EmailTemplate->validates()) {
                if ($this->EmailTemplate->save($this->request->data['EmailTemplate'])) {
                    $this->Session->setFlash("Email template created successfuly", 'success');
					return $this->redirect(array('action' => 'listEmailTemplate'));
                } else {
                    $this->Session->setFlash("Email template could not be created", 'error');
                }
                // it validated logic
            } else {
                // didn't validate logic
                $errors = $this->EmailTemplate->validationErrors;
                $this->Session->setFlash($errors['content'][0], 'error');
            }
        }
    }

   /**
     Amit Sahu
	 12.10.17
	 Edit Email template

     */
    public function manager_editEmailTemplate($encodedEmailTemplateId) {
        $this->loadModel('EmailTemplate');
		//echo $encodedEmailTemplateId;
        if ($this->request->is('post') || $this->request->is('put')) {
			if (!empty($this->request->data)) {
                $this->EmailTemplate->set($this->request->data);
                if ($this->EmailTemplate->validates()) {
				   $this->EmailTemplate->id = $this->request->data['EmailTemplate']['id'];
					if ($this->EmailTemplate->save($this->request->data['EmailTemplate'])) {
                        $this->Session->setFlash("Email template updated successfuly", 'success');
						return $this->redirect(array('action' => 'listEmailTemplate'));
                    } else {
                        $this->Session->setFlash("Site content could not be created", 'error');
                    }
                    // it validated logic
                } else {
                    // didn't validate logic
                    $errors = $this->EmailTemplate->validationErrors;
                    $this->Session->setFlash($errors['content'][0], 'error');
                }
            }
        } else {
            if (!empty($encodedEmailTemplateId)) {
                $this->request->data =  $this->EmailTemplate->getTempateData($this->Encryption->decrypt($encodedEmailTemplateId));
            } else {
                $this->Session->setFlash("Invalid attempt to edit email template", 'error');
                $this->redirect($this->referer());
            }
        }
    }
	
	/**
     Amit Sahu
	 12.10.17
	 EmailTemplateList

     */
    public function manager_listEmailTemplate() {
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
            $conditions = array(
                'EmailTemplate.is_deleted' => BOOL_FALSE
            );

            $this->loadModel('EmailTemplate');
            $this->Paginator->settings = array(
                'EmailTemplate' => array(
                    'conditions' => $conditions,
                    'order' => array('EmailTemplate.type' => 'ASC'),
                    'limit' => PAGINATION_LIMIT,
                    'recursive' => 0
            ));
            $emailTemplate = $this->Paginator->paginate('EmailTemplate');
            $this->set(compact('emailTemplate'));
        } else {
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }

    /**
     Amit Sahu
	 12.10.17
	 Delete Email template

     */
    public function manager_deleteEmailTemplate($encodedEmailTemplateId) {
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
            if (!empty($encodedEmailTemplateId)) {
                $this->loadModel('EmailTemplate');
                $this->EmailTemplate->id = $this->Encryption->decrypt($encodedEmailTemplateId);
                if ($this->EmailTemplate->saveField('is_deleted', 1)) {
                    $this->Session->setFlash("Template deleted successfully", 'success');
                    $this->redirect($this->referer());
                } else {
                    $this->Session->setFlash("Request could not be processed, please try again", 'error');
                    $this->redirect($this->referer());
                }
            } else {
                $this->Session->setFlash("Unauthorized access", 'error');
                $this->redirect($this->referer());
            }
        } else {
            
        }
    }
	/*
	Amit Sahu
	11.10.18
	invoice Format List
	*/
	public function manager_invoiceFormatList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('InvoiceFormat');

		
						
		$conditions = array(
			'InvoiceFormat.id !=' => BOOL_FALSE,
			'InvoiceFormat.is_deleted' => BOOL_FALSE,
			'InvoiceFormat.is_active' => BOOL_TRUE,
			);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'InvoiceFormat' => array(
				'conditions' => $conditions,
				'order' => array('InvoiceFormat.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$formatlists = $this->Paginator->paginate('InvoiceFormat');
		$this->set(compact('formatlists'));		
		
		
    }
	
	/*
	Amit Sahu
	Add Invoice Format
	11.10.18
	*/
	
	public function manager_addInvoiceFormat()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('InvoiceFormat');
		if ($this->request->is('ajax')) 
			{
				$this->InvoiceFormat->create();
				$doc['image']=$this->request->data['InvoiceFormat']['image'];
				if(!empty($doc['image']))
				{						
					$this->Img = $this->Components->load('Img');			
					$newName = strtotime("now");
					$rnd = rand(5, 15);
					$newName = $newName.$rnd;
					$ext = $this->Img->ext($doc['image']['name']);			
					
					$origFile = $newName . '.' . $ext;
					$dst = $newName .  '.'.$ext;	
					$targetdir = WWW_ROOT . 'images/invoice_format';			
					$upload = $this->Img->upload($doc['image']['tmp_name'], $targetdir, $origFile);					
					if($upload == 'Success') 
					{	
						//$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 'images/society/', $dst, 400, 400, 1, 0);
						$this->request->data['InvoiceFormat']['image'] = $dst;
					}
					else 
					{
						$this->request->data['InvoiceFormat']['image'] = "";
					}
				}
				else 
				{
					$this->request->data['InvoiceFormat']['image'] ="";
					
				}
				
				if ($this->InvoiceFormat->save($this->request->data)) 
				{
					$id =$this->InvoiceFormat->getInsertID();
			
					$name=$this->request->data['InvoiceFormat']['name'];
					$image='<img src="'.$this->webroot.'images/invoice_format/'.$this->request->data['InvoiceFormat']['image'].'" style="width:70px;height:70px;">';
					echo json_encode(array('status'=>'1000','message'=>'Invoice Format added successfully', 'id'=>$id,'name'=>$name,'image'=>$image));
				} 
				else 
				{
				    echo json_encode(array('status'=>'1001','message'=>'Invoice Format could not be added'));
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
	11.10.18
	Delete Invoice Format
	*/
	public function manager_deleteInvoiceFormat() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('InvoiceFormat');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->InvoiceFormat->id =$id;
				if (!$this->InvoiceFormat->exists()) 
					{
						throw new NotFoundException('Invalid Invoice Format');
					}
			    if ($this->InvoiceFormat->saveField('is_deleted',BOOL_TRUE)) 
				   {
						$this->InvoiceFormat->saveField('is_active',BOOL_FALSE);
					    echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Invoice Format Deleted successfully'));
				   }
				   else
				   {
					    echo json_encode(array('status'=>'1001','message'=>'Invoice Format could not be Deleted'));
				   }
            }
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
}