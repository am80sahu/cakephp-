<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::import('Controller', 'Commons');
class  AccountsController extends AppController {

    public $components = array('Paginator','Files','Img','Unitchange','Email','NumberToText');	
	public $uses = array('Upload');

	
    public function beforeFilter() 
	{
        parent::beforeFilter();
		$authAllowedActions = array( 
			'shop_login',
		//	'tdsDetailList',
		/*	'shop_findAccount',
			'shop_sendPasswordResetLink',
			'shop_generateRandomString',
			'shop_resetPassword',									
			'shop_changePassword',
			'shop_validateRole',												
			'shop_generateRandomString',
			"shop_forgotPassword",*/
			);
		
        $this->Auth->allow($authAllowedActions);
        if (!in_array($this->Auth->user('role_id'), array(ACCOUNTANT_ROLE_ID,SHOP_ROLE_ID))) 
		{
            $this->Auth->logout();
        }

        //set layout based on user session
        if ($this->Auth->user())
		{
            $this->layout = 'shop/inner';
        }
		else
		{
            $this->layout = 'shop/outer';
        }
    }
	
	/*
	Rajeshwari Lokhande
	25/7/18
	tds detail list
	*/
	public function tdsDetailList()
	{
		$cond=array();
		$this->loadModel('TdsDetail');
		$this->loadModel('TdsNature');
		 $this->layout = 'shop/inner';
		//$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
		
		$tdsNatureList=$this->TdsNature->getTdsNatureList();
		$this->set(compact('tdsNatureList'));
		
	
		if(isset($this->request->data['TdsDetail']))
		{					
		$this->Session->write('TdsSearch',$this->request->data['TdsDetail']);
		}
		else
		{	
		$this->request->data['TdsDetail']=$this->Session->read('TdsSearch');
		
		}		
		if(isset($this->request->data['TdsDetail']))				
		{			
			if(isset($this->request->data['TdsDetail']['name']) and !empty($this->request->data['TdsDetail']['name']))				
			{
				$cond['TdsDetail.name LIKE']="%".$this->request->data['TdsDetail']['name']."%";
			}
			if(isset($this->request->data['TdsDetail']['tds_nature_id']) and !empty($this->request->data['TdsDetail']['tds_nature_id']))				
			{
				$cond['TdsDetail.tds_nature_id']=$this->request->data['TdsDetail']['tds_nature_id'];
			}			
		}		
		
	//	$profile_id=$this->Session->read('Auth.User.user_profile_id');						
		$conditions = array(
			'TdsDetail.id !=' => BOOL_FALSE,
			'TdsDetail.is_deleted' => BOOL_FALSE,
			'TdsDetail.is_active' => BOOL_TRUE,
		/*	'OR'=>array(
					'OR'=>array('Group.is_default'=>BOOL_TRUE),
							 array('Group.is_default'=>BOOL_FALSE,'Group.user_profile_id'=>$profile_id)
					)*/
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'TdsDetail' => array(
				'conditions' => $conditions,
				'order' => array('TdsDetail.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'contain'=>array('TdsNature'=>array('id','name')),
				'recursive' => 2
		));
		$tds_detail = $this->Paginator->paginate('TdsDetail');
		$this->set(compact('tds_detail'));		
	}
	
	/*
	Rajeshwari Lokhande
	25/7/18
	add tds details
	*/
	public function addTdsDetail()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('TdsDetail');
		$this->loadModel('TdsNature');
		
		
		if ($this->request->is('ajax')) 
		{
				$this->TdsDetail->create();		
				$this->request->data['TdsDetail']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');						
				if ($this->TdsDetail->save($this->request->data)) 
				{
						$code='';
						$tds_surcharge='';
						$name='';
						$nature_id='';
						$tds='';
						$tds_ecess='';
						$tds_shecess='';
						$nature_name="";
						
						$id=$this->TdsDetail->getInsertID();
						$code=$this->request->data['TdsDetail']['code'];
						$name=$this->request->data['TdsDetail']['name'];
						$nature_id=$this->request->data['TdsDetail']['tds_nature_id'];
						$tds=$this->request->data['TdsDetail']['tds'];
						$tds_ecess=$this->request->data['TdsDetail']['tds_ecess'];
						$tds_shecess=$this->request->data['TdsDetail']['tds_shecess'];
						$tds_surcharge=$this->request->data['TdsDetail']['tds_surcharge'];
					
						if(!empty($nature_id))
						{
							$natureData=$this->TdsNature->findById($nature_id);
							$nature_name=$natureData['TdsNature']['name'];
						}
						
					echo json_encode(array('status'=>'1000','message'=>'TDS Details added successfully', 'id'=>$id,'code'=>$code,'name'=>$name,'nature_name'=>$nature_name,'nature_id'=>$nature_id,'tds'=>$tds,'tds_ecess'=>$tds_ecess,'tds_shecess'=>$tds_shecess,'tds_surcharge'=>$tds_surcharge));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Tds could not be added'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	
	/*
	Rajeshwari Lokhande
	25/7/18
	edit tds
	*/
	public function editTds()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('TdsDetail');
		$this->loadModel('TdsNature');
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['TdsDetail']['id'];
				if(!empty($id))
					{
						if ($this->TdsDetail->save($this->request->data)) 
						{
							$nature_name="";
							$code="";
							$nature_id="";
							$name="";
							$tds="";
							$tds_ecess="";
							$tds_shecess="";
							$tds_surcharge="";
							
							$code=$this->request->data['TdsDetail']['code'];						
							$nature_id=$this->request->data['TdsDetail']['tds_nature_id'];						
							$name=$this->request->data['TdsDetail']['name'];						
							$tds=$this->request->data['TdsDetail']['tds'];
							$tds_ecess=$this->request->data['TdsDetail']['tds_ecess'];
							$tds_shecess=$this->request->data['TdsDetail']['tds_shecess'];
							$tds_surcharge=$this->request->data['TdsDetail']['tds_surcharge'];
							
							if(!empty($nature_id))
							{
								$tdsNatureData=$this->TdsNature->findById($nature_id);
								$nature_name=$tdsNatureData['TdsNature']['name'];
							}
												
						
							echo json_encode(array('status'=>'1000','message'=>'Tds details updated successfully', 'id'=>$id,'name'=>$name,'code'=>$code,'nature_id'=>$nature_id,'nature_name'=>$nature_name,'tds'=>$tds,'tds_ecess'=>$tds_ecess,'tds_shecess'=>$tds_shecess,'tds_surcharge'=>$tds_surcharge));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Tds could not be update'));
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
	Rajeshwari Lokhande
	25/7/18
	delete tds
	*/
	public function deleteTds()
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('TdsDetail');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->TdsDetail->id =$id;
				if (!$this->TdsDetail->exists()) 
				{
					throw new NotFoundException('Invalid Group');
				}
									
							   if ($this->TdsDetail->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->TdsDetail->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'TDS deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'TDS could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	
	/*
	Rajeshwari Lokhande
	25/7/18
	reset tds search
	*/
	public function resetTds()
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('TdsSearch');
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
	public function ledgerList() 
	{
		$cond=array();
	//	$this->shop_check_login();
		$this->loadModel('Ledger');
		$this->loadModel('Group');
		$this->loadModel('State');
		
		$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
		$groups=$this->Group->getGroupList($user_profile_id);
		$this->set(compact('groups'));
		
		$states=$this->State->getStateList($user_profile_id);
		$this->set(compact('states'));
		
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
			'Ledger.is_active' => BOOL_TRUE
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
	
	/*
	Amit Sahu
	Add Ledger
	28.01.17
	*/
	public function addLedger()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		$this->loadModel('Group');
		$this->loadModel('PartyDetail');
		$this->loadModel('BankAccount');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Ledger->create();		
				$this->request->data['Ledger']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
				if ($this->Ledger->save($this->request->data)) 
				{
					
						$id=$this->Ledger->getInsertID();
						$name=$this->request->data['Ledger']['name'];
						$group_id=$this->request->data['Ledger']['group_id'];
						$group_name="";
						if(!empty($group_id))
						{
							$groupData=$this->Group->findById($group_id);
							if(!empty($groupData))
							{
								$group_name=$groupData['Group']['name'];
							}
						}
						if($group_id==GROUP_SUNDRY_CREDITOR_ID or $group_id==GROUP_SUNDRY_DEBTOR_ID)
							{
								$this->request->data['PartyDetail']['ledger_id']=$id;
								$this->request->data['PartyDetail']['address']=$this->request->data['Ledger']['address'];
								$this->request->data['PartyDetail']['mobile']=$this->request->data['Ledger']['mobile'];
								$this->request->data['PartyDetail']['email']=$this->request->data['Ledger']['email'];
								$this->request->data['PartyDetail']['contact_person']=$this->request->data['Ledger']['contact_person'];
								$this->request->data['PartyDetail']['state']=$this->request->data['Ledger']['state'];
								$this->request->data['PartyDetail']['city']=$this->request->data['Ledger']['city'];
								$this->request->data['PartyDetail']['owner']=$this->request->data['Ledger']['owner'];
								$this->request->data['PartyDetail']['gstin']=$this->request->data['Ledger']['gstin'];
								$this->PartyDetail->save($this->request->data['PartyDetail']);
							}
							else if($group_id==GROUP_BANK_ACCOUNT_ID)
							{
							
								$this->request->data['BankAccount']['ledger_id']=$id;
								$this->request->data['BankAccount']['name']=$this->request->data['Ledger']['name'];
								$this->request->data['BankAccount']['ac_no']=$this->request->data['Ledger']['ac_no'];
								$this->request->data['BankAccount']['branch']=$this->request->data['Ledger']['branch'];
								$this->request->data['BankAccount']['ifsc']=$this->request->data['Ledger']['ifsc'];
								$this->BankAccount->save($this->request->data['BankAccount']);
							}
							
								$name=$this->request->data['Ledger']['name'];		
									
						
							
						/*$gst_rate=$this->request->data['Ledger']['gst_rate'];
						$levy_tax=$this->request->data['Ledger']['levy_tax'];
						$reverse_charge=$this->request->data['Ledger']['reverse_charge'];
						$eligible_credit=$this->request->data['Ledger']['eligible_credit'];*/
						
						
					echo json_encode(array('status'=>'1000','message'=>'Ledger added successfully', 'id'=>$id,'ledger_name'=>$name,'group_name'=>$group_name));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Ledger could not be added'));
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
	6/1/18
	set edit ledger data
	*/ 
	public function setEditData(){

		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		$this->loadModel('BankAccount');
		$this->loadModel('PartyDetail');
		
        if ($this->request->is('ajax')) 
		{
			$edit_ledger_id=$this->request->data["id"];
	//		echo "<pre>";print_r($edit_ledger_id);
			if(!empty($edit_ledger_id))
			{
					
				$ledger_data = $this->Ledger->find("all",array(
				"conditions" =>array(
					"Ledger.id"=>$edit_ledger_id,
					"Ledger.is_active"=>BOOL_TRUE,
					"Ledger.is_deleted"=>BOOL_FALSE,
				),
			//	'contain'=>array("LegBankTable","LegVendorDetail"),
				"recursive"=>-1,
				));
				$leg_id="";
				$group_id="";
				$name="";
				$opening_bal="";
				$debitop_bal="";
				foreach($ledger_data as $row)
				{
					$leg_id=$row['Ledger']['id'];
					$group_id=$row['Ledger']['group_id'];
					$name=$row['Ledger']['name'];
					$opening_bal=$row['Ledger']['opening_bal'];
					$debitop_bal=$row['Ledger']['debitop_bal'];
				}
				if($group_id==GROUP_SUNDRY_DEBTOR_ID or $group_id==GROUP_SUNDRY_CREDITOR_ID)
				{
					$ledger_detail = $this->PartyDetail->find("first",array(
						"conditions" =>array(
							"PartyDetail.ledger_id"=>$edit_ledger_id,
							"PartyDetail.is_active"=>BOOL_TRUE,
							"PartyDetail.is_deleted"=>BOOL_FALSE,
						),
						"recursive"=>-1,
						));

						$leg_id="";
						$address="";
						$mobile="";
						$email="";
						$contact_person="";
						$city="";
						$owner="";
					if(!empty($ledger_detail))
					{
						$leg_id=$ledger_detail['PartyDetail']['ledger_id'];
						$address=$ledger_detail['PartyDetail']['address'];
						$mobile=$ledger_detail['PartyDetail']['mobile'];
						$email=$ledger_detail['PartyDetail']['email'];
						$contact_person=$ledger_detail['PartyDetail']['contact_person'];
						$city=$ledger_detail['PartyDetail']['city'];
						$owner=$ledger_detail['PartyDetail']['owner'];
						$state=$ledger_detail['PartyDetail']['state'];
						$gstin=$ledger_detail['PartyDetail']['gstin'];
					}

					echo json_encode(array("status"=>201,"id"=>$leg_id,'group_id'=>$group_id,'vendor_name'=>$name,'opening_bal'=>$opening_bal,'debitop_bal'=>$debitop_bal,'address'=>$address,'mobile'=>$mobile,'email'=>$email,'contact_person'=>$contact_person,'city'=>$city,'owner'=>$owner,'state'=>$state,'gstin'=>$gstin));
				}
				else if($group_id==GROUP_BANK_ACCOUNT_ID)
				{
					$bank_detail = $this->BankAccount->find("first",array(
						"conditions" =>array(
							"BankAccount.ledger_id"=>$edit_ledger_id,
							"BankAccount.is_active"=>BOOL_TRUE,
							"BankAccount.is_deleted"=>BOOL_FALSE,
						),
						"recursive"=>-1,
					));
				
					$leg_id="";
					$ac_no="";
					$branch="";
					$ifsc="";
					if(!empty($bank_detail))
					{
					//	echo "<pre>";print_r($bank_detail);
						$leg_id=$bank_detail['BankAccount']['ledger_id'];
						$ac_no=$bank_detail['BankAccount']['ac_no'];
						$branch=$bank_detail['BankAccount']['branch'];
						$ifsc=$bank_detail['BankAccount']['ifsc'];
					}
			
					echo json_encode(array("status"=>202,"id"=>$leg_id,'group_id'=>$group_id,'bank_name'=>$name,'opening_bal'=>$opening_bal,'debitop_bal'=>$debitop_bal,'ac_no'=>$ac_no,'branch'=>$branch,'ifsc'=>$ifsc));
				}
				else
				{
					echo json_encode(array("status"=>200,"id"=>$leg_id,'group_id'=>$group_id,"name"=>$name,"opening_bal"=>$opening_bal,"debitop_bal"=>$debitop_bal));
				}
			}
			else
			{
				echo json_encode(array("status"=>404,"content"=>"leadger data not found"));
			}
					
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
		
	}//end of setEditData()
	
	/*
	Amit Sahu
	edit ledger
	*/
	public function editLedger()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		$this->loadModel('Group');
		$this->loadModel('PartyDetail');
		$this->loadModel('BankAccount');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
							
				if ($this->Ledger->save($this->request->data)) 
				{
					
						$id=$this->request->data['Ledger']['id'];
						$name=$this->request->data['Ledger']['name'];
						$group_id=$this->request->data['Ledger']['group_id'];
						$group_name="";
						if(!empty($group_id))
						{
							$groupData=$this->Group->findById($group_id);
							if(!empty($groupData))
							{
								$group_name=$groupData['Group']['name'];
							}
						}
						$partData=$this->PartyDetail->find('first',array('conditions'=>array('PartyDetail.ledger_id'=>$id),'fields'=>array('PartyDetail.id')));
						if(!empty($partData))
						{
							$this->PartyDetail->delete($partData['PartyDetail']['id']);
						
						}
						$bankData=$this->BankAccount->find('first',array('conditions'=>array('BankAccount.ledger_id'=>$id),'fields'=>array('BankAccount.id')));
						if(!empty($bankData))
						{
							$this->BankAccount->delete($bankData['BankAccount']['id']);
							
						}
						if($group_id==GROUP_SUNDRY_CREDITOR_ID or $group_id==GROUP_SUNDRY_DEBTOR_ID)
							{
								
								
								$this->request->data['PartyDetail']['ledger_id']=$id;
								$this->request->data['PartyDetail']['address']=$this->request->data['Ledger']['address'];
								$this->request->data['PartyDetail']['mobile']=$this->request->data['Ledger']['mobile'];
								$this->request->data['PartyDetail']['email']=$this->request->data['Ledger']['email'];
								$this->request->data['PartyDetail']['contact_person']=$this->request->data['Ledger']['contact_person'];
								$this->request->data['PartyDetail']['state']=$this->request->data['Ledger']['state'];
								$this->request->data['PartyDetail']['city']=$this->request->data['Ledger']['city'];
								$this->request->data['PartyDetail']['owner']=$this->request->data['Ledger']['owner'];
								$this->request->data['PartyDetail']['gstin']=$this->request->data['Ledger']['gstin'];
								$this->PartyDetail->save($this->request->data['PartyDetail']);
							}
							else if($group_id==GROUP_BANK_ACCOUNT_ID)
							{
								
									$this->request->data['BankAccount']['id']='';
								$this->request->data['BankAccount']['ledger_id']=$id;
								$this->request->data['BankAccount']['name']=$this->request->data['Ledger']['name'];
								$this->request->data['BankAccount']['ac_no']=$this->request->data['Ledger']['ac_no'];
								$this->request->data['BankAccount']['branch']=$this->request->data['Ledger']['branch'];
								$this->request->data['BankAccount']['ifsc']=$this->request->data['Ledger']['ifsc'];
								$this->BankAccount->save($this->request->data['BankAccount']);
							}
							
								$name=$this->request->data['Ledger']['name'];		
									
						
							
						/*$gst_rate=$this->request->data['Ledger']['gst_rate'];
						$levy_tax=$this->request->data['Ledger']['levy_tax'];
						$reverse_charge=$this->request->data['Ledger']['reverse_charge'];
						$eligible_credit=$this->request->data['Ledger']['eligible_credit'];*/
						
						
					echo json_encode(array('status'=>'1000','message'=>'Ledger added successfully', 'id'=>$id,'ledger_name'=>$name,'group_name'=>$group_name));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Ledger could not be added'));
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
	01.02.17
	Delete Ledger
	*/
	
	public function deleteLedger() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Ledger');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Ledger->id =$id;
				if (!$this->Ledger->exists()) 
				{
					throw new NotFoundException('Invalid Ledger');
				}
				
						
							   if ($this->Ledger->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Ledger->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Ledger Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Ledger could not be Deleted'));
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
	reset ledger search
	01.02.17
	*/
	public function resetLedgerSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('LedgerSearch');
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
	23.02.17
	Group List
	*/
	public function groupList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Group');
		
		$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
		$groupList=$this->Group->getGroupList($user_profile_id);
		$this->set(compact('groupList'));
		
	
		if(isset($this->request->data['Group']))
		{					
		$this->Session->write('GroupSearch',$this->request->data['Group']);
		}
		else
		{	
		$this->request->data['Group']=$this->Session->read('GroupSearch');
		
		}		
		if(isset($this->request->data['Group']))				
		{			
			if(isset($this->request->data['Group']['name']) and !empty($this->request->data['Group']['name']))				
			{
				$cond['Group.name LIKE']="%".$this->request->data['Group']['name']."%";
			}
			if(isset($this->request->data['Group']['parent_id']) and !empty($this->request->data['Group']['parent_id']))				
			{
				$cond['Group.parent_id']=$this->request->data['Group']['parent_id'];
			}			
		}		
		
		$profile_id=$this->Session->read('Auth.User.user_profile_id');						
		$conditions = array(
			'Group.id !=' => BOOL_FALSE,
			'Group.is_deleted' => BOOL_FALSE,
			'Group.is_active' => BOOL_TRUE,
			'OR'=>array(
					'OR'=>array('Group.is_default'=>BOOL_TRUE),
							 array('Group.is_default'=>BOOL_FALSE,'Group.user_profile_id'=>$profile_id)
					)
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Group' => array(
				'conditions' => $conditions,
				'order' => array('Group.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$groups = $this->Paginator->paginate('Group');
		$this->set(compact('groups'));		
		
    }
	/*
	Amit Sahu
	23.2.17
	reset group search
	*/
	public function resetGroupSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('GroupSearch');
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
	Add Group
	24.07.18
	*/
	public function addGroup()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Group');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Group->create();		
				$this->request->data['Group']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');				
				if ($this->Group->save($this->request->data)) 
				{
					
						$id=$this->Group->getInsertID();
						$name=$this->request->data['Group']['name'];
						$parent_id=$this->request->data['Group']['parent_id'];
						$parent_name="";
						if(!empty($parent_id))
						{
							$parentData=$this->Group->findById($parent_id);
							$parent_name=$parentData['Group']['name'];
						}
						
					echo json_encode(array('status'=>'1000','message'=>'Group added successfully', 'id'=>$id,'name'=>$name,'parent_name'=>$parent_name,'parent_id'=>$parent_id));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Group could not be added'));
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
	Edit Group  (Master)
	23.02.17
	*/
	public function editGroup()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Group');
		
					
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Group']['id'];
				if(!empty($id))
					{
						if ($this->Group->save($this->request->data)) 
						{
						$name=$this->request->data['Group']['name'];						
						$parent_id=$this->request->data['Group']['parent_id'];
						$parent_name="";
						if(!empty($parent_id))
						{
							$parentData=$this->Group->findById($parent_id);
							$parent_name=$parentData['Group']['name'];
						}
											
						
					echo json_encode(array('status'=>'1000','message'=>'Group edit successfully', 'id'=>$id,'name'=>$name,'parent_id'=>$parent_id,'parent_name'=>$parent_name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Group could not be added'));
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
	Delete Group
	24.02.17
	*/
	public function deleteGroup() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Group');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Group->id =$id;
				if (!$this->Group->exists()) 
				{
					throw new NotFoundException('Invalid Group');
				}
									
							   if ($this->Group->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Group->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Group deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Group could not be Deleted'));
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
	Group Is unique
	24.07.18*/
	
	public function groupUnique()
	{
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Group');
	
        if ($this->request->is('ajax')) 
		{
			$profile_id=$this->Session->read('Auth.User.user_profile_id');	
			$count=$this->Group->find('count',array(
			'conditions'=>array(
				'Group.name'=>$this->request->data['Group']['name'],
				'Group.is_deleted'=>BOOL_FALSE,
				'OR'=>array(
					'OR'=>array('Group.is_default'=>BOOL_TRUE),
							 array('Group.is_default'=>BOOL_FALSE,'Group.user_profile_id'=>$profile_id)
					)
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
	01.08.18
	Voucher List
	*/
	public function paymentVoucherList() 
	{
		
		$cond=array();
		$cond1=array();
		//$this->shop_check_login();
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');
		$this->loadModel('Group');



		
			//$cond['DATE(Voucher.created)']=date('Y-m-d');
		
		$ledgers=$this->Ledger->getLedgerList();	
		$this->set(compact('ledgers'));
		
		

		
		
		if(isset($this->request->data['Voucher']))
		{	
			
			$this->Session->write('VoucherSearch',$this->request->data['Voucher']);
			
		}
		else
		{
			$this->request->data['Voucher']=$this->Session->read('VoucherSearch');
	
					
			
		}

		
		if(isset($this->request->data['Voucher']))				
		{			
			if(isset($this->request->data['Voucher']['from_date']) and !empty($this->request->data['Voucher']['from_date']))				
			{
				$cond['DATE(Voucher.date) >=']=date('Y-m-d', strtotime($this->request->data['Voucher']['from_date']));
			}
			if(isset($this->request->data['Voucher']['to_date']) and !empty($this->request->data['Voucher']['to_date']))				
			{
				$cond['DATE(Voucher.date) <=']=date('Y-m-d', strtotime($this->request->data['Voucher']['to_date']));
			}
			
					
		}		
		
		
		$conditions = array(
			'Voucher.id !=' => BOOL_FALSE,
			'Voucher.is_deleted' => BOOL_FALSE,
			'Voucher.type' =>PAYMENT,
			'Voucher.user_profile_id' =>$this->Session->read('Auth.User.user_profile_id'),
		
		
		);
		$conditions=array_merge($conditions,$cond,$cond1);		
	
		$this->Paginator->settings = array(
			'Voucher' => array(
				'conditions' => $conditions,
				'order' => array('Voucher.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive'=>2,
				//'contain'=>array('VoucherDetail'=>array('Ledger'=>array('name'))),
				
		));
		$vouchers = $this->Paginator->paginate('Voucher');
		$this->set(compact('vouchers'));	
		/*echo "<pre>";
		print_r($vouchers);exit;*/
	}
/*
	Amit Sahu
	17.02.17
	Reset Voucher Search
	*/
	public function resetVoucherSerachSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		//$this->shop_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('VoucherSearch');
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
	Get ledger group id by ledger id
	16.01.18
	*/
	public function getGroupTypeByLedgerId()
	{ 
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
			$this->loadModel('Ledger');
		
			if ($this->request->is('ajax')) 
			{	
					
					$id=$this->request->data['id'];
					//print($id);exit;
					$ledgerData=$this->Ledger->findById($id,array('Ledger.group_id'));
					if(!empty($ledgerData))
					{	
						$group_id=$ledgerData['Ledger']['group_id'];
					 echo json_encode(array('status'=>'1000','group_id'=>$group_id));
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
	Add Voucher
	01.08.17
	*/
public function addPaymentVoucher()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');


		
		
		if ($this->request->is('ajax')) 
			{
				
				$this->Voucher->create();
				$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
				$date=$this->request->data['Voucher']['date'];	
				$cheque_date=$this->request->data['Voucher']['cheque_date'];	
				if(!empty($cheque_date))
				{
				$this->request->data['Voucher']['cheque_date']=date('Y-m-d',strtotime($cheque_date));
				}
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				$financeData=$this->Voucher->getLastVoucherNo($fin_from_date,$fin_to_date ,PAYMENT,$user_profile_id);
				$new_no=1;

				if(!empty($financeData))
				{
					$new_no=$financeData['Voucher']['no']+1;
				}
				$voucherDetails=$this->request->data['VoucherDetails'];
				
				
				$this->request->data['Voucher']['no']=$new_no;	
				$this->request->data['Voucher']['type']=PAYMENT;	
				$this->request->data['Voucher']['dr_cr']=LEDGER_IS_CREDIT;	
				
				
				$this->request->data['Voucher']['user_profile_id']=$user_profile_id;
				$this->request->data['Voucher']['date']=date('Y-m-d',strtotime($date));
				$naration=$this->request->data['Voucher']['naration'];
			
					if ($this->Voucher->save($this->request->data['Voucher'])) 
					{
							$amount=$this->request->data['Voucher']['amount'];
							$ledger_name="";
							$ledgersData=$this->Ledger->findById($this->request->data['Voucher']['ledger_id']);
							if(!empty($ledgersData))
							{
								$ledger_name=$ledgersData['Ledger']['name'];
							
							}		
							
							$id=$this->Voucher->getInsertID();
							// Default Row Entry
								$this->VoucherDetail->create();
								$this->request->data['VoucherDetail']['vid']=$id;
								$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
								$this->request->data['VoucherDetail']['dr_cr']=LEDGER_IS_CREDIT;
								$this->request->data['VoucherDetail']['ledger_id']=$this->request->data['Voucher']['ledger_id'];
								$this->request->data['VoucherDetail']['amount']=$this->request->data['Voucher']['amount'];							
								$this->VoucherDetail->save($this->request->data['VoucherDetail']);
							// Default Row Entry
							
							
							if(!empty($voucherDetails))
							{
								foreach($voucherDetails as $row1)
								{
																
									$this->VoucherDetail->create();
									$this->request->data['VoucherDetail']['vid']=$id;
									$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
									$this->request->data['VoucherDetail']['dr_cr']=$row1['dr_cr'];;
									$this->request->data['VoucherDetail']['ledger_id']=$row1['ledger_id'];
									$this->request->data['VoucherDetail']['amount']=$row1['amount'];								
									
									$this->VoucherDetail->save($this->request->data['VoucherDetail']);
									
								}
							}	
							
							
						echo json_encode(array('status'=>'1000','message'=>'Voucher added successfully', 'id'=>$id,'date'=>$date,'ledger_name'=>$ledger_name,'amount'=>$amount,'naration'=>$naration));
				} else 
							{
								$errors = $this->Voucher->validationErrors;
								echo json_encode(array('status'=>'1001','message'=>'Voucher could not be added','errors'=>$errors));
					
				}
				
			}					
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/*
	view Payment Voucher 
	02.08.18
	Amit Sahu
	*/
	public function viewPaymentVoucher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				
					$voucherData=$this->Voucher->findById($id);
					$table="";
					$crbank_details="";
					if($voucherData['Ledger']['group_id']==GROUP_BANK_ACCOUNT_ID)
					{
						$cdate='';
						if(!empty($voucherData['Voucher']['cheque_date']))
						{
							$cdate=date('d-m-Y',strtotime($voucherData['Voucher']['cheque_date']));
						}
						$crbank_details='<br><p><b>Cheque No : </b>'.$voucherData['Voucher']['cheque_no'].',<b>Cheque Date : </b>'.$cdate.'</p>';
					}
					$totalAmount=$voucherData['Voucher']['amount'];
					$table.='<tr><td class="border_none"><span class="full-width"><b>Cr. '.ucfirst($voucherData['Ledger']['name']).'</b>'.$crbank_details.'</span><br></td><td style="text-align: right;"></td><td style="text-align: right;">'.$voucherData['Voucher']['amount'].'</td></tr>	';
					
					
					
			
					$date=date('d-m-Y', strtotime($voucherData['Voucher']['date']));
					
					$conditions=array('VoucherDetail.vid'=>$id,'VoucherDetail.is_deleted'=>BOOL_FALSE,'VoucherDetail.ledger_id !='=>$voucherData['Voucher']['ledger_id']);
					$fields=array('VoucherDetail.amount','VoucherDetail.id','VoucherDetail.ledger_id','VoucherDetail.amount','VoucherDetail.dr_cr');
					$contain=array('Ledger'=>array('name'));
						
					$drVDetails=$this->VoucherDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'recursive'=>2));
					
					$totalAmount=$voucherData['Voucher']['amount'];
					
					if(!empty($drVDetails))
					{
						foreach($drVDetails as $row)
						{
							$ledger_id=$row['VoucherDetail']['ledger_id'];
							$ledger="";
							$ledgersData=$this->Ledger->findById($ledger_id,array('Ledger.name','Ledger.group_id'));
							if(!empty($ledgersData))
							{
								$ledger=$ledgersData['Ledger']['name'];
							}
							if($row['VoucherDetail']['dr_cr']==LEDGER_IS_DEBIT)
							{
								$type='Dr.';
							}else{
								$type='Cr.';
							}
							$debitAmt='';
							$creditAmt='';
							if($row['VoucherDetail']['dr_cr']==LEDGER_IS_DEBIT)
							{
								$debitAmt=$row['VoucherDetail']['amount'];
							
							}else{
								$totalAmount=$totalAmount+$row['VoucherDetail']['amount'];
								$creditAmt=$row['VoucherDetail']['amount'];
							
							}
							
							
							$table.='<tr><td class="border_none"><span class="full-width"><b>'.$type.' '.ucfirst($ledger).'</b></span><br><br></td><td>'.$debitAmt.'</td><td>'.$creditAmt.'</td></tr>	';
						}
					}
					
					
					//(<i> '.ucfirst($row['VoucherDetail']['naration']).'</i>)
					$amtnum=$this->NumberToText->convert_number_to_words($totalAmount);
					$table.='<tr><td class="border_none"><span class="full-width ">'.$voucherData['Voucher']['naration'].'</span><br><span class="full-width text-right"><b>Total</b><br> ('.$amtnum.' only.) </span></td><td><b>'.number_format($totalAmount,2).'</b></td><td><b>'.number_format($totalAmount,2).'</b></td></tr><tr ><td class="text-right" colspan="3"><div class="row voffset4"></div>Signature______________</td></tr>';
					  echo json_encode(array('status'=>'1000','table'=>$table,'id'=>$id,'date'=>$date));
							
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/*
	Amit Sahu
	Delete Voucher
	24.02.17
	*/
	public function deleteVoucher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Voucher');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Voucher->id =$id;
				if (!$this->Voucher->exists()) 
				{
					throw new NotFoundException('Invalid Voucher');
				}
									
							   if ($this->Voucher->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Voucher->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Voucher deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Voucher could not be Deleted'));
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
	01.08.18
	Receipt Voucher List
	*/
	public function receiptVoucherList() 
	{
		
		$cond=array();
		$cond1=array();
		//$this->shop_check_login();
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');
		$this->loadModel('Group');



		
			//$cond['DATE(Voucher.created)']=date('Y-m-d');
		
		$ledgers=$this->Ledger->getLedgerList();	
		$this->set(compact('ledgers'));
		
		

		
		
		if(isset($this->request->data['Voucher']))
		{	
			
			$this->Session->write('VoucherSearch',$this->request->data['Voucher']);
			
		}
		else
		{
			$this->request->data['Voucher']=$this->Session->read('VoucherSearch');
	
					
			
		}

		
		if(isset($this->request->data['Voucher']))				
		{			
			if(isset($this->request->data['Voucher']['from_date']) and !empty($this->request->data['Voucher']['from_date']))				
			{
				$cond['DATE(Voucher.date) >=']=date('Y-m-d', strtotime($this->request->data['Voucher']['from_date']));
			}
			if(isset($this->request->data['Voucher']['to_date']) and !empty($this->request->data['Voucher']['to_date']))				
			{
				$cond['DATE(Voucher.date) <=']=date('Y-m-d', strtotime($this->request->data['Voucher']['to_date']));
			}
			
					
		}		
		
		
		$conditions = array(
			'Voucher.id !=' => BOOL_FALSE,
			'Voucher.is_deleted' => BOOL_FALSE,
			'Voucher.type' =>RECEIPT,
			'Voucher.user_profile_id' =>$this->Session->read('Auth.User.user_profile_id'),
		
		
		);
		$conditions=array_merge($conditions,$cond,$cond1);		
	
		$this->Paginator->settings = array(
			'Voucher' => array(
				'conditions' => $conditions,
				'order' => array('Voucher.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive'=>2,
				//'contain'=>array('VoucherDetail'=>array('Ledger'=>array('name'))),
				
		));
		$vouchers = $this->Paginator->paginate('Voucher');
		$this->set(compact('vouchers'));	
		/*echo "<pre>";
		print_r($vouchers);exit;*/
	}
	/*
	Amit Sahu
	Add Receipt Voucher
	01.08.17
	*/
public function addReceiptVoucher()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');


		
		
		if ($this->request->is('ajax')) 
			{
				
				$this->Voucher->create();
				$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
				$date=$this->request->data['Voucher']['date'];	
				$cheque_date=$this->request->data['Voucher']['cheque_date'];	
				if(!empty($cheque_date))
				{
				$this->request->data['Voucher']['cheque_date']=date('Y-m-d',strtotime($cheque_date));
				}
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				$financeData=$this->Voucher->getLastVoucherNo($fin_from_date,$fin_to_date ,RECEIPT,$user_profile_id);
				$new_no=1;

				if(!empty($financeData))
				{
					$new_no=$financeData['Voucher']['no']+1;
				}
				$voucherDetails=$this->request->data['VoucherDetails'];
				
				
				$this->request->data['Voucher']['no']=$new_no;	
				$this->request->data['Voucher']['type']=RECEIPT;	
				$this->request->data['Voucher']['dr_cr']=LEDGER_IS_DEBIT;	
				
				
				$this->request->data['Voucher']['user_profile_id']=$user_profile_id;
				$this->request->data['Voucher']['date']=date('Y-m-d',strtotime($date));
				$naration=$this->request->data['Voucher']['naration'];
			
					if ($this->Voucher->save($this->request->data['Voucher'])) 
					{
							$amount=$this->request->data['Voucher']['amount'];
							$ledger_name="";
							$ledgersData=$this->Ledger->findById($this->request->data['Voucher']['ledger_id']);
							if(!empty($ledgersData))
							{
								$ledger_name=$ledgersData['Ledger']['name'];
							
							}		
							
							$id=$this->Voucher->getInsertID();
							// Default Row Entry
								$this->VoucherDetail->create();
								$this->request->data['VoucherDetail']['vid']=$id;
								$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
								$this->request->data['VoucherDetail']['dr_cr']=LEDGER_IS_DEBIT;
								$this->request->data['VoucherDetail']['ledger_id']=$this->request->data['Voucher']['ledger_id'];
								$this->request->data['VoucherDetail']['amount']=$this->request->data['Voucher']['amount'];							
								$this->VoucherDetail->save($this->request->data['VoucherDetail']);
							// Default Row Entry
							
							
							if(!empty($voucherDetails))
							{
								foreach($voucherDetails as $row1)
								{
																
									$this->VoucherDetail->create();
									$this->request->data['VoucherDetail']['vid']=$id;
									$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
									$this->request->data['VoucherDetail']['dr_cr']=$row1['dr_cr'];
									$this->request->data['VoucherDetail']['ledger_id']=$row1['ledger_id'];
									$this->request->data['VoucherDetail']['amount']=$row1['amount'];								
									
									$this->VoucherDetail->save($this->request->data['VoucherDetail']);
									
								}
							}	
							
							
						echo json_encode(array('status'=>'1000','message'=>'Voucher added successfully', 'id'=>$id,'date'=>$date,'ledger_name'=>$ledger_name,'amount'=>$amount,'naration'=>$naration));
				} else 
							{
								$errors = $this->Voucher->validationErrors;
								echo json_encode(array('status'=>'1001','message'=>'Voucher could not be added','errors'=>$errors));
					
				}
				
			}					
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	view Receipt Voucher 
	02.08.18
	Amit Sahu
	*/
	public function viewReceiptVoucher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				
					$voucherData=$this->Voucher->findById($id);
					$table="";
					$crbank_details="";
					if($voucherData['Ledger']['group_id']==GROUP_BANK_ACCOUNT_ID)
					{
						$cdate='';
						if(!empty($voucherData['Voucher']['cheque_date']))
						{
							$cdate=date('d-m-Y',strtotime($voucherData['Voucher']['cheque_date']));
						}
						$crbank_details='<br><p><b>Cheque No : </b>'.$voucherData['Voucher']['cheque_no'].',<b>Cheque Date : </b>'.$cdate.'</p>';
					}
					$totalAmount=$voucherData['Voucher']['amount'];
					$table.='<tr><td class="border_none"><span class="full-width"><b>Dr. '.ucfirst($voucherData['Ledger']['name']).'</b>'.$crbank_details.'</span><br></td><td style="text-align: right;"></td><td style="text-align: right;">'.$voucherData['Voucher']['amount'].'</td></tr>	';
					
					
					
			
					$date=date('d-m-Y', strtotime($voucherData['Voucher']['date']));
					
					$conditions=array('VoucherDetail.vid'=>$id,'VoucherDetail.is_deleted'=>BOOL_FALSE,'VoucherDetail.ledger_id !='=>$voucherData['Voucher']['ledger_id']);
					$fields=array('VoucherDetail.amount','VoucherDetail.id','VoucherDetail.ledger_id','VoucherDetail.amount','VoucherDetail.dr_cr');
					$contain=array('Ledger'=>array('name'));
						
					$drVDetails=$this->VoucherDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'recursive'=>2));
					
				
					if(!empty($drVDetails))
					{
						foreach($drVDetails as $row)
						{
							$ledger_id=$row['VoucherDetail']['ledger_id'];
							$ledger="";
							$ledgersData=$this->Ledger->findById($ledger_id,array('Ledger.name','Ledger.group_id'));
							if(!empty($ledgersData))
							{
								$ledger=$ledgersData['Ledger']['name'];
							}
							if($row['VoucherDetail']['dr_cr']==LEDGER_IS_DEBIT)
							{
								$type='Dr.';
							}else{
								$type='Cr.';
							}
							$debitAmt='';
							$creditAmt='';
							if($row['VoucherDetail']['dr_cr']==LEDGER_IS_DEBIT)
							{
								$debitAmt=$row['VoucherDetail']['amount'];
								$totalAmount=$totalAmount+$row['VoucherDetail']['amount'];
							}else{
								
								$creditAmt=$row['VoucherDetail']['amount'];
							
							}
							
							
							$table.='<tr><td class="border_none"><span class="full-width"><b>'.$type.' '.ucfirst($ledger).'</b></span><br><br></td><td>'.$creditAmt.'</td><td class="text-right">'.$debitAmt.'</td></tr>	';
						}
					}
					
					
					
					
					//(<i> '.ucfirst($row['VoucherDetail']['naration']).'</i>)
					$amtnum=$this->NumberToText->convert_number_to_words($totalAmount);
					$table.='<tr><td class="border_none"><span class="full-width ">'.$voucherData['Voucher']['naration'].'</span><br><span class="full-width text-right"><b>Total</b><br> ('.$amtnum.' only.) </span></td><td><b>'.number_format($totalAmount,2).'</b></td><td><b>'.number_format($totalAmount,2).'</b></td></tr><tr ><td class="text-right" colspan="3"><div class="row voffset4"></div>Signature______________</td></tr>';
					  echo json_encode(array('status'=>'1000','table'=>$table,'id'=>$id,'date'=>$date));
							
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/*
	Amit Sahu
	01.08.18
	Contra Voucher List
	*/
	public function contraVoucherList() 
	{
		
		$cond=array();
		
		$this->loadModel('Voucher');
		$this->loadModel('Ledger');

		
		if(isset($this->request->data['Voucher']))
		{	
			
			$this->Session->write('VoucherSearch',$this->request->data['Voucher']);
			
		}
		else
		{
			$this->request->data['Voucher']=$this->Session->read('VoucherSearch');
	
		}

		
		if(isset($this->request->data['Voucher']))				
		{			
			if(isset($this->request->data['Voucher']['from_date']) and !empty($this->request->data['Voucher']['from_date']))				
			{
				$cond['DATE(Voucher.date) >=']=date('Y-m-d', strtotime($this->request->data['Voucher']['from_date']));
			}
			if(isset($this->request->data['Voucher']['to_date']) and !empty($this->request->data['Voucher']['to_date']))				
			{
				$cond['DATE(Voucher.date) <=']=date('Y-m-d', strtotime($this->request->data['Voucher']['to_date']));
			}
			
					
		}		
		
		
		$conditions = array(
			'Voucher.id !=' => BOOL_FALSE,
			'Voucher.is_deleted' => BOOL_FALSE,
			'Voucher.type' =>CONTRA,
			'Voucher.user_profile_id' =>$this->Session->read('Auth.User.user_profile_id'),
		
		);
		$conditions=array_merge($conditions,$cond);		
	
		$this->Paginator->settings = array(
			'Voucher' => array(
				'conditions' => $conditions,
				'order' => array('Voucher.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive'=>2,
				//'contain'=>array('VoucherDetail'=>array('Ledger'=>array('name'))),
				
		));
		$vouchers = $this->Paginator->paginate('Voucher');
		$this->set(compact('vouchers'));	
		
	}
	
	/*
	Amit Sahu
	Add Contra Voucher
	01.08.17
	*/
public function addContraVoucher()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');


		
		
		if ($this->request->is('ajax')) 
			{
				
				$this->Voucher->create();
				$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
				$date=$this->request->data['Voucher']['date'];	
				$cheque_date=$this->request->data['Voucher']['cheque_date'];	
				if(!empty($cheque_date))
				{
				$this->request->data['Voucher']['cheque_date']=date('Y-m-d',strtotime($cheque_date));
				}
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				$financeData=$this->Voucher->getLastVoucherNo($fin_from_date,$fin_to_date ,CONTRA,$user_profile_id);
				$new_no=1;

				if(!empty($financeData))
				{
					$new_no=$financeData['Voucher']['no']+1;
				}
				$voucherDetails=$this->request->data['VoucherDetails'];
				
				
				$this->request->data['Voucher']['no']=$new_no;	
				$this->request->data['Voucher']['type']=CONTRA;	
				//$this->request->data['Voucher']['dr_cr']=LEDGER_IS_DEBIT;	
				
				
				$this->request->data['Voucher']['user_profile_id']=$user_profile_id;
				$this->request->data['Voucher']['date']=date('Y-m-d',strtotime($date));
				$naration=$this->request->data['Voucher']['naration'];
			
					if ($this->Voucher->save($this->request->data['Voucher'])) 
					{
							$amount=$this->request->data['Voucher']['amount'];
							$ledger_name="";
							$ledgersData=$this->Ledger->findById($this->request->data['Voucher']['ledger_id']);
							if(!empty($ledgersData))
							{
								$ledger_name=$ledgersData['Ledger']['name'];
							
							}		
							
							$id=$this->Voucher->getInsertID();
							// Default Row Entry
								$this->VoucherDetail->create();
								$this->request->data['VoucherDetail']['vid']=$id;
								$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
								$this->request->data['VoucherDetail']['dr_cr']=$this->request->data['Voucher']['dr_cr'];
								$this->request->data['VoucherDetail']['ledger_id']=$this->request->data['Voucher']['ledger_id'];
								$this->request->data['VoucherDetail']['amount']=$this->request->data['Voucher']['amount'];							
								$this->VoucherDetail->save($this->request->data['VoucherDetail']);
							// Default Row Entry
							
							
							if(!empty($voucherDetails))
							{
								foreach($voucherDetails as $row1)
								{
																
									$this->VoucherDetail->create();
									$this->request->data['VoucherDetail']['vid']=$id;
									$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
									$this->request->data['VoucherDetail']['dr_cr']=$row1['dr_cr'];
									$this->request->data['VoucherDetail']['ledger_id']=$row1['ledger_id'];
									$this->request->data['VoucherDetail']['amount']=$row1['amount'];								
									
									$this->VoucherDetail->save($this->request->data['VoucherDetail']);
									
								}
							}	
							
							
						echo json_encode(array('status'=>'1000','message'=>'Voucher added successfully', 'id'=>$id,'date'=>$date,'ledger_name'=>$ledger_name,'amount'=>$amount,'naration'=>$naration));
				} else 
							{
								$errors = $this->Voucher->validationErrors;
								echo json_encode(array('status'=>'1001','message'=>'Voucher could not be added','errors'=>$errors));
					
				}
				
			}					
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	
	/*
	view Contra Voucher 
	02.08.18
	Amit Sahu
	*/
	public function viewContraVoucher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				
					$voucherData=$this->Voucher->findById($id);
					$table="";
					$crbank_details="";
					if($voucherData['Ledger']['group_id']==GROUP_BANK_ACCOUNT_ID)
					{
						$cdate='';
						if(!empty($voucherData['Voucher']['cheque_date']))
						{
							$cdate=date('d-m-Y',strtotime($voucherData['Voucher']['cheque_date']));
						}
						$crbank_details='<br><p><b>Cheque No : </b>'.$voucherData['Voucher']['cheque_no'].',<b>Cheque Date : </b>'.$cdate.'</p>';
					}
					if($voucherData['Voucher']['dr_cr']==LEDGER_IS_DEBIT)
					{
						$type='Dr.';
					}else{
						$type='Cr.';
					}
					$totalAmount=$voucherData['Voucher']['amount'];
					$table.='<tr><td class="border_none"><span class="full-width"><b>'.$type.' '.ucfirst($voucherData['Ledger']['name']).'</b>'.$crbank_details.'</span><br></td><td style="text-align: right;"></td><td style="text-align: right;">'.$voucherData['Voucher']['amount'].'</td></tr>	';
					
					
					$date=date('d-m-Y', strtotime($voucherData['Voucher']['date']));
					
					$conditions=array('VoucherDetail.vid'=>$id,'VoucherDetail.is_deleted'=>BOOL_FALSE,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'VoucherDetail.ledger_id !='=>$voucherData['Voucher']['ledger_id']);
					$fields=array('VoucherDetail.amount','VoucherDetail.id','VoucherDetail.ledger_id','VoucherDetail.amount');
					$contain=array('Ledger'=>array('name'));
						
					$drVDetails=$this->VoucherDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'recursive'=>2));
					
					$totalAmount=0;
					if(!empty($drVDetails))
					{
						foreach($drVDetails as $row)
						{
							$ledger_id=$row['VoucherDetail']['ledger_id'];
							$ledger="";
							$ledgersData=$this->Ledger->findById($ledger_id,array('Ledger.name','Ledger.group_id'));
							if(!empty($ledgersData))
							{
								$ledger=$ledgersData['Ledger']['name'];
							}
							
							$totalAmount=$totalAmount+$row['VoucherDetail']['amount'];
							$table.='<tr><td class="border_none"><span class="full-width"><b>Cr. '.ucfirst($ledger).'</b></span><br><br></td><td>'.$row['VoucherDetail']['amount'].'</td><td></td></tr>	';
						}
					}
					
					
					$conditions1=array('VoucherDetail.vid'=>$id,'VoucherDetail.is_deleted'=>BOOL_FALSE,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'VoucherDetail.ledger_id !='=>$voucherData['Voucher']['ledger_id']);
		
						
					$crVDetails=$this->VoucherDetail->find('all',array('conditions'=>$conditions1,'fields'=>$fields,'contain'=>$contain,'recursive'=>2));
					
					$totalAmount=0;
					if(!empty($crVDetails))
					{
						foreach($crVDetails as $row1)
						{
							$ledger_id=$row1['VoucherDetail']['ledger_id'];
							$ledger="";
							$ledgersData=$this->Ledger->findById($ledger_id,array('Ledger.name','Ledger.group_id'));
							if(!empty($ledgersData))
							{
								$ledger=$ledgersData['Ledger']['name'];
							}
							
							$totalAmount=$totalAmount+$row1['VoucherDetail']['amount'];
							$table.='<tr><td class="border_none"><span class="full-width"><b>Dr. '.ucfirst($ledger).'</b></span><br><br></td><td>'.$row1['VoucherDetail']['amount'].'</td><td></td></tr>	';
						}
					}
					
					
					
					
					//(<i> '.ucfirst($row['VoucherDetail']['naration']).'</i>)
					$amtnum=$this->NumberToText->convert_number_to_words($voucherData['Voucher']['amount']);
					$table.='<tr><td class="border_none"><span class="full-width ">'.$voucherData['Voucher']['naration'].'</span><br><span class="full-width text-right"><b>Total</b><br> ('.$amtnum.' only.) </span></td><td><b>'.number_format($voucherData['Voucher']['amount'],2).'</b></td><td><b>'.number_format($voucherData['Voucher']['amount'],2).'</b></td></tr><tr ><td class="text-right" colspan="3"><div class="row voffset4"></div>Signature______________</td></tr>';
					  echo json_encode(array('status'=>'1000','table'=>$table,'id'=>$id,'date'=>$date));
							
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/*
	Amit Sahu
	01.08.18
	Contra Voucher List
	*/
	public function journalVoucherList() 
	{
		
		$cond=array();
		
		$this->loadModel('Voucher');
		$this->loadModel('Ledger');

		
		if(isset($this->request->data['Voucher']))
		{	
			
			$this->Session->write('VoucherSearch',$this->request->data['Voucher']);
			
		}
		else
		{
			$this->request->data['Voucher']=$this->Session->read('VoucherSearch');
	
		}

		
		if(isset($this->request->data['Voucher']))				
		{			
			if(isset($this->request->data['Voucher']['from_date']) and !empty($this->request->data['Voucher']['from_date']))				
			{
				$cond['DATE(Voucher.date) >=']=date('Y-m-d', strtotime($this->request->data['Voucher']['from_date']));
			}
			if(isset($this->request->data['Voucher']['to_date']) and !empty($this->request->data['Voucher']['to_date']))				
			{
				$cond['DATE(Voucher.date) <=']=date('Y-m-d', strtotime($this->request->data['Voucher']['to_date']));
			}
			
					
		}		
		
		
		$conditions = array(
			'Voucher.id !=' => BOOL_FALSE,
			'Voucher.is_deleted' => BOOL_FALSE,
			'Voucher.type' =>GENERAL,
			'Voucher.user_profile_id' =>$this->Session->read('Auth.User.user_profile_id'),
		
		);
		$conditions=array_merge($conditions,$cond);		
	
		$this->Paginator->settings = array(
			'Voucher' => array(
				'conditions' => $conditions,
				'order' => array('Voucher.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive'=>2,
				//'contain'=>array('VoucherDetail'=>array('Ledger'=>array('name'))),
				
		));
		$vouchers = $this->Paginator->paginate('Voucher');
		$this->set(compact('vouchers'));	
		
	}
	
	/*
	Amit Sahu
	Add Contra Voucher
	01.08.17
	*/
public function addJournalVoucher()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');


		
		
		if ($this->request->is('ajax')) 
			{
				
				$this->Voucher->create();
				$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
				$date=$this->request->data['Voucher']['date'];	
				$cheque_date=$this->request->data['Voucher']['cheque_date'];	
				if(!empty($cheque_date))
				{
				$this->request->data['Voucher']['cheque_date']=date('Y-m-d',strtotime($cheque_date));
				}
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				$financeData=$this->Voucher->getLastVoucherNo($fin_from_date,$fin_to_date ,GENERAL,$user_profile_id);
				$new_no=1;

				if(!empty($financeData))
				{
					$new_no=$financeData['Voucher']['no']+1;
				}
				$voucherDetails=$this->request->data['VoucherDetails'];
				
				
				$this->request->data['Voucher']['no']=$new_no;	
				$this->request->data['Voucher']['type']=GENERAL;	
				//$this->request->data['Voucher']['dr_cr']=LEDGER_IS_DEBIT;	
				
				
				$this->request->data['Voucher']['user_profile_id']=$user_profile_id;
				$this->request->data['Voucher']['date']=date('Y-m-d',strtotime($date));
				$naration=$this->request->data['Voucher']['naration'];
			
					if ($this->Voucher->save($this->request->data['Voucher'])) 
					{
							$amount=$this->request->data['Voucher']['amount'];
							$ledger_name="";
							$ledgersData=$this->Ledger->findById($this->request->data['Voucher']['ledger_id']);
							if(!empty($ledgersData))
							{
								$ledger_name=$ledgersData['Ledger']['name'];
							
							}		
							
							$id=$this->Voucher->getInsertID();
							// Default Row Entry
								$this->VoucherDetail->create();
								$this->request->data['VoucherDetail']['vid']=$id;
								$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
								$this->request->data['VoucherDetail']['dr_cr']=$this->request->data['Voucher']['dr_cr'];
								$this->request->data['VoucherDetail']['ledger_id']=$this->request->data['Voucher']['ledger_id'];
								$this->request->data['VoucherDetail']['amount']=$this->request->data['Voucher']['amount'];							
								$this->VoucherDetail->save($this->request->data['VoucherDetail']);
							// Default Row Entry
							
							
							if(!empty($voucherDetails))
							{
								foreach($voucherDetails as $row1)
								{
																
									$this->VoucherDetail->create();
									$this->request->data['VoucherDetail']['vid']=$id;
									$this->request->data['VoucherDetail']['reporting_type']=REPORTING_VOUCHER;
									$this->request->data['VoucherDetail']['dr_cr']=$row1['dr_cr'];
									$this->request->data['VoucherDetail']['ledger_id']=$row1['ledger_id'];
									$this->request->data['VoucherDetail']['amount']=$row1['amount'];								
									
									$this->VoucherDetail->save($this->request->data['VoucherDetail']);
									
								}
							}	
							
							
						echo json_encode(array('status'=>'1000','message'=>'Voucher added successfully', 'id'=>$id,'date'=>$date,'ledger_name'=>$ledger_name,'amount'=>$amount,'naration'=>$naration));
				} else 
							{
								$errors = $this->Voucher->validationErrors;
								echo json_encode(array('status'=>'1001','message'=>'Voucher could not be added','errors'=>$errors));
					
				}
				
			}					
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	
	/*
	view Contra Voucher 
	02.08.18
	Amit Sahu
	*/
	public function viewJournalVoucher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Ledger');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				
					$voucherData=$this->Voucher->findById($id);
					$table="";
					$crbank_details="";
					if($voucherData['Ledger']['group_id']==GROUP_BANK_ACCOUNT_ID)
					{
						$cdate='';
						if(!empty($voucherData['Voucher']['cheque_date']))
						{
							$cdate=date('d-m-Y',strtotime($voucherData['Voucher']['cheque_date']));
						}
						$crbank_details='<br><p><b>Cheque No : </b>'.$voucherData['Voucher']['cheque_no'].',<b>Cheque Date : </b>'.$cdate.'</p>';
					}
					if($voucherData['Voucher']['dr_cr']==LEDGER_IS_DEBIT)
					{
						$type='Dr.';
					}else{
						$type='Cr.';
					}
					$totalAmount=$voucherData['Voucher']['amount'];
					$table.='<tr><td class="border_none"><span class="full-width"><b>'.$type.' '.ucfirst($voucherData['Ledger']['name']).'</b>'.$crbank_details.'</span><br></td><td style="text-align: right;"></td><td style="text-align: right;">'.$voucherData['Voucher']['amount'].'</td></tr>	';
					
					
					$date=date('d-m-Y', strtotime($voucherData['Voucher']['date']));
					
					$conditions=array('VoucherDetail.vid'=>$id,'VoucherDetail.is_deleted'=>BOOL_FALSE,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'VoucherDetail.ledger_id !='=>$voucherData['Voucher']['ledger_id']);
					$fields=array('VoucherDetail.amount','VoucherDetail.id','VoucherDetail.ledger_id','VoucherDetail.amount');
					$contain=array('Ledger'=>array('name'));
						
					$drVDetails=$this->VoucherDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'recursive'=>2));
					
					$totalAmount=0;
					if(!empty($drVDetails))
					{
						foreach($drVDetails as $row)
						{
							$ledger_id=$row['VoucherDetail']['ledger_id'];
							$ledger="";
							$ledgersData=$this->Ledger->findById($ledger_id,array('Ledger.name','Ledger.group_id'));
							if(!empty($ledgersData))
							{
								$ledger=$ledgersData['Ledger']['name'];
							}
							
							$totalAmount=$totalAmount+$row['VoucherDetail']['amount'];
							$table.='<tr><td class="border_none"><span class="full-width"><b>Cr. '.ucfirst($ledger).'</b></span><br><br></td><td>'.$row['VoucherDetail']['amount'].'</td><td></td></tr>	';
						}
					}
					
					
					$conditions1=array('VoucherDetail.vid'=>$id,'VoucherDetail.is_deleted'=>BOOL_FALSE,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'VoucherDetail.ledger_id !='=>$voucherData['Voucher']['ledger_id']);
		
						
					$crVDetails=$this->VoucherDetail->find('all',array('conditions'=>$conditions1,'fields'=>$fields,'contain'=>$contain,'recursive'=>2));
					
					$totalAmount=0;
					if(!empty($crVDetails))
					{
						foreach($crVDetails as $row1)
						{
							$ledger_id=$row1['VoucherDetail']['ledger_id'];
							$ledger="";
							$ledgersData=$this->Ledger->findById($ledger_id,array('Ledger.name','Ledger.group_id'));
							if(!empty($ledgersData))
							{
								$ledger=$ledgersData['Ledger']['name'];
							}
							
							$totalAmount=$totalAmount+$row1['VoucherDetail']['amount'];
							$table.='<tr><td class="border_none"><span class="full-width"><b>Dr. '.ucfirst($ledger).'</b></span><br><br></td><td>'.$row1['VoucherDetail']['amount'].'</td><td></td></tr>	';
						}
					}
					
					
					
					
					//(<i> '.ucfirst($row['VoucherDetail']['naration']).'</i>)
					$amtnum=$this->NumberToText->convert_number_to_words($voucherData['Voucher']['amount']);
					$table.='<tr><td class="border_none"><span class="full-width ">'.$voucherData['Voucher']['naration'].'</span><br><span class="full-width text-right"><b>Total</b><br> ('.$amtnum.' only.) </span></td><td><b>'.number_format($voucherData['Voucher']['amount'],2).'</b></td><td><b>'.number_format($voucherData['Voucher']['amount'],2).'</b></td></tr><tr ><td class="text-right" colspan="3"><div class="row voffset4"></div>Signature______________</td></tr>';
					  echo json_encode(array('status'=>'1000','table'=>$table,'id'=>$id,'date'=>$date));
							
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Ledger Report
	Amit Shau
	03.08.18
	*/
	public function ledgerReport()
    {
		 $this->loadModel('Voucher');
		 $this->loadModel('VoucherDetail');
		 $this->loadModel('Ledger');
		 $this->loadModel('DefaultLedgerOpeing');
		 $vouchers=array();
		 $search='';
		 $from_date='';
		 $to_date='';
		 $ledName='';
		 $total_debit=0;
		 $total_credit=0;
		 $opening=0;
		 $actual_opeing=0;
		 $opening_type="";
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
          
                if(isset($this->request->data['Ledger']['from_date1']) and !empty($this->request->data['Ledger']['from_date1']))               
                {
					$this->request->data['Ledger']['from_date']=date('Y-m-d',strtotime($this->request->data['Ledger']['from_date1']));
                    $cond['DATE(Voucher.date) >=']=$this->request->data['Ledger']['from_date'];
                    $from_date=$this->request->data['Ledger']['from_date'];
                }
                else{
                $year=date("Y");
                $month=date("m");
                if($month <=3)
                {
                $yearOpen=$year-1;
                $from_date=$yearOpen.'-04-01';
                }else{
                $from_date=$year.'-04-01';
                }
                }               
                if(isset($this->request->data['Ledger']['to_date1']) and !empty($this->request->data['Ledger']['to_date1']))               
                {
					$this->request->data['Ledger']['to_date']=date('Y-m-d',strtotime($this->request->data['Ledger']['to_date1']));
                    $cond['DATE(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
                 
                    $to_date=$this->request->data['Ledger']['to_date'];
                }
                else{
                $year=date("Y");
                $month=date("m");
                if($month <=3)
                {               
                $to_date=$year.'-03-31';
                }else{
                $yearClose=$year+1;
                $to_date=$yearClose.'-03-31';
                }
                }
                if(isset($this->request->data['Ledger']['ledger']) and !empty($this->request->data['Ledger']['ledger']))               
                {
					
                    $search_ledger=$this->request->data['Ledger']['ledger'];
                    $ledgerdata=$this->Ledger->findById($this->request->data['Ledger']['ledger']);
					  $cond['VoucherDetail.ledger_id']=$this->request->data['Ledger']['ledger'];
                    if(!empty($ledgerdata))
                    {
                        $ledName=$ledgerdata['Ledger']['name'];
                    }
                
				
				
				// Opeing balance calculation
				
				$opeingData=$this->DefaultLedgerOpeing->find('first',array('conditions'=>array('DefaultLedgerOpeing.is_deleted'=>BOOL_FALSE,'DefaultLedgerOpeing.ledger_id'=>$search_ledger,'DefaultLedgerOpeing.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')),'fields'=>array('DefaultLedgerOpeing.amount','DefaultLedgerOpeing.dr_cr')));
				if(!empty($opeingData))
				{
					$amount=$opeingData['DefaultLedgerOpeing']['amount'];
					if($opeingData['DefaultLedgerOpeing']['dr_cr']==LEDGER_IS_DEBIT)
					{
						$total_debit=$amount;
					}
					if($opeingData['DefaultLedgerOpeing']['dr_cr']==LEDGER_IS_CREDIT)
					{
						$total_credit=$amount;
					}
				}
				
				$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
				$debitData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'VoucherDetail.ledger_id'=>$search_ledger,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'Voucher.date <'=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_debit')));
				if(!empty($debitData)){
					$total_debit=$total_debit+$debitData[0]['total_debit'];
				}
				$creditData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'VoucherDetail.ledger_id'=>$search_ledger,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'Voucher.date <'=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_credit')));
				if(!empty($debitData)){
					$total_credit=$total_credit+$creditData[0]['total_credit'];
				}
				if($total_debit>$total_credit)
				{
					$opening=$total_debit-$total_credit;
					$opening_type="Dr.";
				}
				elseif($total_credit>$total_debit)
				{
					$opening=$total_credit-$total_debit;
					$opening_type="Cr.";
					
				}
				$actual_opeing=$total_debit-$total_credit;
			
			
				// End opeing balance calculation
				
				
				
				
				
				$conditions = array(
                    'VoucherDetail.id !=' => BOOL_FALSE,
                    'VoucherDetail.is_deleted' => BOOL_FALSE,
                    'Voucher.is_deleted' => BOOL_FALSE,
                    'Voucher.is_active' => BOOL_TRUE,
					'Voucher.user_profile_id'=>$user_profile_id
                );
                $conditions=array_merge($conditions,$cond);       
               
				$fields=array('VoucherDetail.id','VoucherDetail.ledger_id','Voucher.type','VoucherDetail.dr_cr','VoucherDetail.amount');
				/* $contain=array('Voucher'=>array('date','no','amount','id','naration','Ledger'=>array('name'),'VoucherDetail'=>array('amount','dr_cr','Ledger'=>array('name')))); */
				
				$contain=array('Voucher'=>array('date','no','amount','id','naration','Ledger'=>array('name'),'VoucherDetail'=>array('Ledger'=>array('name'),'conditions'=>array('VoucherDetail.ledger_id !='=>$search_ledger),'fields'=>array('amount','dr_cr')))); 
				
            
				 $vouchers=$this->VoucherDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'limit'=>PAGINATION_LIMIT_1,'order'=>array('VoucherDetail.id'=>'ASC')));  
		}	 
		}
		/*echo "<pre>";
		print_r($vouchers);
		exit;*/
		$search='<tr><th colspan="7" class="text-center">Account Ledger</th></tr><tr><th colspan="7" class="text-center">From : '.$from_date.' To : '.$to_date.'</th></tr><tr><th colspan="3" class="text-left" style="border-right:1px solid #9c9c9b"><b>Ledger Name : '.$ledName.'</b></th><th colspan="4" class="text-right"><b>Opening Balance : '.$opening.' '.$opening_type.'</b></th></tr>';
		$this->set(compact('vouchers'));		 
		$this->set(compact('opening'));		 
		$this->set(compact('search'));		 
		$this->set(compact('actual_opeing'));		 
	
	}
	
	
	
	public function loadMoreLedger()
	{	

		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		 $this->loadModel('Voucher');
		 $this->loadModel('VoucherDetail');
		 $this->loadModel('Ledger');
		
		
		if ($this->request->is('ajax')) 
			{
				
				$cond=array();
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
          
                if(isset($this->request->data['Ledger']['from_date1']) and !empty($this->request->data['Ledger']['from_date1']))               
                {
					$this->request->data['Ledger']['from_date']=date('Y-m-d',strtotime($this->request->data['Ledger']['from_date1']));
                    $cond['DATE(Voucher.date) >=']=$this->request->data['Ledger']['from_date'];
                    $from_date=$this->request->data['Ledger']['from_date'];
                }
                else{
                $year=date("Y");
                $month=date("m");
                if($month <=3)
                {
                $yearOpen=$year-1;
                $from_date=$yearOpen.'-04-01';
                }else{
                $from_date=$year.'-04-01';
                }
                }               
                if(isset($this->request->data['Ledger']['to_date1']) and !empty($this->request->data['Ledger']['to_date1']))               
                {
					$this->request->data['Ledger']['to_date']=date('Y-m-d',strtotime($this->request->data['Ledger']['to_date1']));
                    $cond['DATE(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
                 
                    $to_date=$this->request->data['Ledger']['to_date'];
                }
                else{
                $year=date("Y");
                $month=date("m");
                if($month <=3)
                {               
                $to_date=$year.'-03-31';
                }else{
                $yearClose=$year+1;
                $to_date=$yearClose.'-03-31';
                }
                }
                if(isset($this->request->data['Ledger']['ledger']) and !empty($this->request->data['Ledger']['ledger']))               
                {
					
                    $search_ledger=$this->request->data['Ledger']['ledger'];
                    $ledgerdata=$this->Ledger->findById($this->request->data['Ledger']['ledger']);
					  $cond['VoucherDetail.ledger_id']=$this->request->data['Ledger']['ledger'];
                    if(!empty($ledgerdata))
                    {
                        $ledName=$ledgerdata['Ledger']['name'];
                    }
                
				$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
				$id=$this->request->data['id'];
				$conditions = array(
                    'VoucherDetail.id !=' => BOOL_FALSE,
                    'VoucherDetail.is_deleted' => BOOL_FALSE,
                    'VoucherDetail.id >' => $id	,
					    'Voucher.is_deleted' => BOOL_FALSE,
                    'Voucher.is_active' => BOOL_TRUE,
					'Voucher.user_profile_id'=>$user_profile_id
                );
                $conditions=array_merge($conditions,$cond);       
               
				
				$fields=array('VoucherDetail.id','VoucherDetail.ledger_id','Voucher.type','VoucherDetail.dr_cr','VoucherDetail.amount');
								
				$contain=array('Voucher'=>array('date','no','amount','id','naration','Ledger'=>array('name'),'VoucherDetail'=>array('Ledger'=>array('name'),'conditions'=>array('VoucherDetail.ledger_id !='=>$search_ledger),'fields'=>array('amount','dr_cr')))); 
				
            
				 $vouchers=$this->VoucherDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'limit'=>PAGINATION_LIMIT_1,'order'=>array('VoucherDetail.id'=>'ASC')));  
				 
				 $data="";
				 $lastrowID=0;
			 if(!empty($vouchers))
					{
						foreach($vouchers as $row)
						{
							$lastrowID=$row['VoucherDetail']['id'];
								$voucherType=array(
								GENERAL=>'Journal',
								PAYMENT=>'Payment',
								CONTRA=>'Contra',
								RECEIPT=>'Receipt',
								PURCHASE_VOUCHER=>'Purchase',
								SALE_VOUCHER=>'Sale',
								DEBIT_NOTE_VOUCHER=>'Debit Note',
								CREDIT_NOTE_VOUCHER=>'Credit Note',
								);
								$debitAmt="";
								$creditAmt="";
								if($row['VoucherDetail']['dr_cr']==LEDGER_IS_DEBIT)
								{
									$debitAmt=number_format($row['VoucherDetail']['amount'],2);
								}
								elseif($row['VoucherDetail']['dr_cr']==LEDGER_IS_CREDIT)
								{
									$creditAmt=number_format($row['VoucherDetail']['amount'],2);
								}
							$data.='<tr>
								<td>'.date('d-m-Y',strtotime($row['Voucher']['date'])).'</td>
								<td>'.$row['Voucher']['no'].'</td>
								<td>'.$voucherType[$row['Voucher']['type']].'</td>
								<td>';
								$details=$row['Voucher']['VoucherDetail'];
									if(!empty($details))
									{
										foreach($details as $del)
										{
											if($del['dr_cr']==LEDGER_IS_DEBIT)
													{
														$type= "Dr.";
													}else{
														$type= "Cr.";
													}
											$data.='<div class="row">
															<div class="col-sm-8"><b>'.ucwords($del['Ledger']['name']).'</b></div>
															<div class="col-sm-4">'.number_format($del['amount'],2).' '.$type.'
															</div>
														</div>';
										}
									}

								$data.='<br>'.ucfirst($row['Voucher']['naration']).'</td>
								<td class="debitAmt">'.$debitAmt.'</td>
								<td class="creditAmt">'.$creditAmt.'</td>
								<td  class="inline_bal"> </td>
							</tr>';
						
							
						}
					}
			
				echo json_encode(array('status'=>'1000','tablegg'=>$data,'lastrowID'=>$lastrowID));	
			}else{
			echo json_encode(array('status'=>'1001'));	
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
	04.08.18
	Reset ledger search 
	*/
	public function resetLedgerReportSearch() 
	{
		$this->autoRender = FALSE;
				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('LedgerSearch');
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
	Get Ledger opeing For update
	24.09.18*/
	public function getLedgerOpeing()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('DefaultLedgerOpeing');
	


		
		
		if ($this->request->is('ajax')) 
			{
				$ledgerid=$this->request->data['id'];
				$opeingData=$this->DefaultLedgerOpeing->find('first',array('conditions'=>array('DefaultLedgerOpeing.is_deleted'=>BOOL_FALSE,'DefaultLedgerOpeing.ledger_id'=>$ledgerid,'DefaultLedgerOpeing.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')),'fields'=>array('DefaultLedgerOpeing.amount','DefaultLedgerOpeing.dr_cr','DefaultLedgerOpeing.id')));
				if(!empty($opeingData))
				{
					$amount=$opeingData['DefaultLedgerOpeing']['amount'];
					$dr_cr=$opeingData['DefaultLedgerOpeing']['dr_cr'];
					$id=$opeingData['DefaultLedgerOpeing']['id'];
				}else{
					$amount=0;
					$dr_cr="";
					$id="";
				}
				echo json_encode(array('status'=>'1000','amount'=>$amount,'dr_cr'=>$dr_cr,'id'=>$id));		
			}					
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
	/*
	Amit Sahu
	Update Ledger opeing
	24.09.18*/
	public function updateLedgerOpeing()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('DefaultLedgerOpeing');
		if ($this->request->is('ajax')) 
			{
				$this->request->data['DefaultLedgerOpeing']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
				if($this->DefaultLedgerOpeing->save($this->request->data['DefaultLedgerOpeing']))
				{
					echo json_encode(array('status'=>'1000','message'=>'Ledger opening balance updated successfully .'));	
				}else{
					echo json_encode(array('status'=>'1001','message'=>'Ledger opening balance could not be updated'));	
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
	18.01.19
	Trail Balance
	*/
	public function trailBalance()
    {
		 $this->loadModel('Voucher');
		 $this->loadModel('VoucherDetail');
		 $this->loadModel('Group');
			// Current Finacial year
			$year=date("Y");
			$month=date("m");
			if($month <=3)
			{               
			$to_date=$year.'-03-31';
			}else{
			$yearClose=$year+1;
			$to_date=$yearClose.'-03-31';
			}

			if($month <=3)
			{
			$yearOpen=$year-1;
			$from_date=$yearOpen.'-04-01';
			}else{
			$from_date=$year.'-04-01';
			}
			// End current Finacial year
		 if(isset($this->request->data['TrailBalance']))
			{
				$this->Session->write('TrailBalanceSearch',$this->request->data['TrailBalance']);			
				
			}
			else
			{
				$this->request->data['TrailBalance']=$this->Session->read('TrailBalanceSearch');			
				
			}	
		   if(isset($this->request->data['TrailBalance']))               
        {
          
                if(isset($this->request->data['TrailBalance']['from_date1']) and !empty($this->request->data['TrailBalance']['from_date1']))               
                {
					
                    $from_date=date('Y-m-d',strtotime($this->request->data['TrailBalance']['from_date1']));
                }
                      
                if(isset($this->request->data['TrailBalance']['to_date1']) and !empty($this->request->data['TrailBalance']['to_date1']))               
                {
					
                 
                    $to_date=date('Y-m-d',strtotime($this->request->data['TrailBalance']['to_date1']));
                }
             
              
			
		}
		
			
		
			$search='<tr></tr><tr><th colspan="3" class="text-center">From : '.$from_date.' To : '.$to_date.'</th></tr>';
			$this->set(compact('search'));
			
			$group_data=$this->Group->find('all',array('conditions'=>array('Group.is_deleted'=>BOOL_FALSE,'Group.is_active'=>BOOL_TRUE,'Group.parent_id'=>NULL),'fields'=>array('id','name'),'recursive'=>-1));
			if(!empty($group_data))
			{
				foreach($group_data as $k=>$row)
				{
					$childgroup_list=$this->Group->find('list',array('conditions'=>array('Group.is_deleted'=>BOOL_FALSE,'Group.is_active'=>BOOL_TRUE,'Group.parent_id'=>$row['Group']['id']),'fields'=>array('id')));
					$selpgroup=array($row['Group']['id']=>$row['Group']['id']);
					$childgroup_list=array_merge($childgroup_list,$selpgroup);
			
					$conditions=array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.date >='=>$from_date,'Voucher.date <='=>$to_date,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'Ledger.group_id'=>$childgroup_list);
					
					$voucher_data_dr=$this->VoucherDetail->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(VoucherDetail.amount) as total_amount'),'contain'=>array('Ledger'=>array('id'),'Voucher'=>array('id')),'recursive'=>2));
					
					$conditions1=array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.date >='=>$from_date,'Voucher.date <='=>$to_date,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'Ledger.group_id'=>$childgroup_list);
			
					$voucher_data_cr=$this->VoucherDetail->find('first',array('conditions'=>$conditions1,'fields'=>array('SUM(VoucherDetail.amount) as total_amount'),'contain'=>array('Ledger'=>array('id'),'Voucher'=>array('id')),'recursive'=>2));
					$group_data[$k]['taotal_cr_amt']=$voucher_data_cr[0]['total_amount'];
					$group_data[$k]['taotal_dr_amt']=$voucher_data_dr[0]['total_amount'];
				}
			}
			
			$this->set(compact('group_data'));
			
			/*echo "<pre>";
			print_r($group_data);exit;*/
		
	}
	
	/*
	Amit Sahu
	04.08.18
	Reset ledger search 
	*/
	public function resetTrailBalanceSearch() 
	{
		$this->autoRender = FALSE;
				
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('TrailBalanceSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

    }
	public function viewSubgroupTrial()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('VoucherDetail');
		 $this->loadModel('Group');
		
		
		if ($this->request->is('ajax')) 
			{
				$year=date("Y");
			$month=date("m");
			if($month <=3)
			{               
			$to_date=$year.'-03-31';
			}else{
			$yearClose=$year+1;
			$to_date=$yearClose.'-03-31';
			}

			if($month <=3)
			{
			$yearOpen=$year-1;
			$from_date=$yearOpen.'-04-01';
			}else{
			$from_date=$year.'-04-01';
			}
			// End current Finacial year
				if(isset($this->request->data['TrailBalance']))
				{
				$this->Session->write('TrailBalanceSearch',$this->request->data['TrailBalance']);			

				}
				else
				{
				$this->request->data['TrailBalance']=$this->Session->read('TrailBalanceSearch');			

				}	
				if(isset($this->request->data['TrailBalance']))               
				{

					if(isset($this->request->data['TrailBalance']['from_date1']) and !empty($this->request->data['TrailBalance']['from_date1']))               
					{
						
						$from_date=date('Y-m-d',strtotime($this->request->data['TrailBalance']['from_date1']));
					}
						  
					if(isset($this->request->data['TrailBalance']['to_date1']) and !empty($this->request->data['TrailBalance']['to_date1']))               
					{
						
					 
						$to_date=date('Y-m-d',strtotime($this->request->data['TrailBalance']['to_date1']));
					}

				}
				
				$group_data=$this->Group->find('all',array('conditions'=>array('Group.is_deleted'=>BOOL_FALSE,'Group.is_active'=>BOOL_TRUE,'Group.parent_id !='=>NULL),'fields'=>array('id','name'),'contain'=>array('ParentGroup'=>array('id')),'recursive'=>-1));
				if(!empty($group_data))
				{
					foreach($group_data as $k=>$row)
					{
						$childgroup_list=$this->Group->find('list',array('conditions'=>array('Group.is_deleted'=>BOOL_FALSE,'Group.is_active'=>BOOL_TRUE,'Group.parent_id'=>$row['Group']['id']),'fields'=>array('id')));
						$selpgroup=array($row['Group']['id']=>$row['Group']['id']);
						$childgroup_list=array_merge($childgroup_list,$selpgroup);
				
						$conditions=array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.date >='=>$from_date,'Voucher.date <='=>$to_date,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'Ledger.group_id'=>$childgroup_list);
						
						$voucher_data_dr=$this->VoucherDetail->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(VoucherDetail.amount) as total_amount'),'contain'=>array('Ledger'=>array('id'),'Voucher'=>array('id')),'recursive'=>2));
						
						$conditions1=array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.date >='=>$from_date,'Voucher.date <='=>$to_date,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'Ledger.group_id'=>$childgroup_list);
				
						$voucher_data_cr=$this->VoucherDetail->find('first',array('conditions'=>$conditions1,'fields'=>array('SUM(VoucherDetail.amount) as total_amount'),'contain'=>array('Ledger'=>array('id'),'Voucher'=>array('id')),'recursive'=>2));
						$group_data[$k]['taotal_cr_amt']=$voucher_data_cr[0]['total_amount'];
						$group_data[$k]['taotal_dr_amt']=$voucher_data_dr[0]['total_amount'];
					}
				}
			
				echo json_encode(array('status'=>'1000','groupdata'=>$group_data));		
			}					
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}
}