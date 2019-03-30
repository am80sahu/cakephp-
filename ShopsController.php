<?php
/**
 * This file contain admin functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * 
 * @name       compounderionistsController.php
 * @class      compounderionistsController
 * @category   compounderionist Users
 * @package    compounderionist Users
 * @date       12- July 2016
 */ 

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::import('Controller', 'Commons');
class  ShopsController extends AppController {

    public $components = array('Paginator','Files','Img','Unitchange','Email');	
	public $uses = array('Upload');

	
    public function beforeFilter() 
	{
        parent::beforeFilter();
		$authAllowedActions = array( 
			'shop_login',
			'shop_findAccount',
			'shop_sendPasswordResetLink',
			'shop_generateRandomString',
			'shop_resetPassword',									
			'shop_changePassword',
			'shop_validateRole',												
			'shop_generateRandomString',
			"shop_forgotPassword",
			);
		
        $this->Auth->allow($authAllowedActions);
        if (!in_array($this->Auth->user('role_id'), array(SHOP_ROLE_ID))) 
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

    //function for admin login
	//Amit Sahu
	// 01.02.17
    public function shop_login() {
        $this->layout = 'shop/outer';

        if ($this->request->is('post')) 
		{
            $email = !empty($this->request->data ['User'] ['email']) ? trim($this->request->data ['User'] ['email']) : null;
            $password = !empty($this->request->data ['User'] ['password']) ? trim(AuthComponent::password($this->request->data['User']['password'])) : null;			
            $type = 'first';
			
            $conditions = array(
                'User.email' => $email,
                'User.password' => $password,
				'User.role_id' => array(SHOP_ROLE_ID),
                'User.is_active' => BOOL_TRUE,
                'User.is_deleted' => BOOL_FALSE
            );
            $fields = NULL;
            $contain = NULL;
            $order = NULL;
            $group = NULL;
            $recursive = 1;
            $this->loadModel('User');
			$this->loadModel('UserProfile');
			$this->loadModel('NavigationMaster');
			$this->loadModel('UserAuthentication');
			$this->loadModel('FinancialYear');
            $userData = $this->User->getUserData($type, $conditions, $fields, $contain, $order, $group, $recursive);
         
            if (!empty($userData)) {
                $userArray['User']['email'] = $email;
                $userArray['User']['password'] = $password;
                if ($this->Auth->login()) {
					
					//print_r($this->Auth->user());exit;
					$user_id=$this->Session->read('Auth.User.id');
					
					$permited=$this->UserAuthentication->find('list',array('conditions'=>array('UserAuthentication.is_active'=>BOOL_TRUE,'UserAuthentication.is_deleted'=>BOOL_FALSE,'UserAuthentication.user_id'=>$user_id,'UserAuthentication.permission'=>BOOL_TRUE),'fields'=>array('UserAuthentication.id','UserAuthentication.nav_id')));
					//
					$navData=$this->NavigationMaster->find('all',array('conditions'=>array('NavigationMaster.is_active'=>BOOL_TRUE,'NavigationMaster.is_deleted'=>BOOL_FALSE,'NavigationMaster.nav_type'=>1,'NavigationMaster.module'=>1,'NavigationMaster.id'=>$permited),
					'fields'=>array('NavigationMaster.name','NavigationMaster.controller','NavigationMaster.function','NavigationMaster.prefix'),'contain'=>array('SubNav'=>array('conditions'=>array('SubNav.id'=>$permited),'fields'=>array('name','controller','function','prefix')))
					
					));
					
			
					$this->Session->write('NavData', $navData);
					
					$financialYear=$this->FinancialYear->find('first',array('conditions'=>array('FinancialYear.id !='=>BOOL_FALSE,'FinancialYear.is_active'=>BOOL_TRUE,'FinancialYear.is_deleted'=>BOOL_FALSE),'fields'=>array('FinancialYear.name','FinancialYear.from_date','FinancialYear.to_date'),'recursive'=>-1));
					$this->Session->write('FinancialYear', $financialYear);
					
					$UserProfile=$this->UserProfile->find('first',array('conditions'=>array('UserProfile.id !='=>BOOL_FALSE,'UserProfile.is_active !='=>BOOL_FALSE,'UserProfile.is_deleted !='=>BOOL_TRUE,'UserProfile.id'=>$userData['User']['user_profile_id']),'recursive'=>2));
					$this->Session->write('UserProfile', $UserProfile);
					//$UserProfile=$this->Session->read('UserProfile');
					
                    $this->User->id = $this->Session->read('Auth.User.id');
                    //update below flag
                    $saveableArray = array(
                        'is_logged_in' => 1,
                        'last_login' => date('Y-m-d H:i:s'),
                        'ip_address' => trim($this->request->clientIp())
                    );
					 $this->User->save($saveableArray);
					
                    $this->redirect($this->Auth->redirectUrl());
                }
            } else {
                $this->Session->setFlash(__("Invalid email address or password"), 'error');
            }
        }
    }

    //function for admin logout
	//Amit Sahu
	// 01.02.17
    public function shop_logout() {
        if ($this->Auth->user('id')) {
            $this->loadModel('User');
            $this->User->id = $this->Auth->user('id');
            if ($this->User->saveField('is_logged_in', 0)) {
                $this->Session->destroy('Auth.User');
                $this->redirect($this->Auth->logout());
            }
        } else {
            $this->redirect($this->Auth->logout());
        }
    }
	

    //function for  dashboard
		//Amit Sahu
	// 01.02.17
    public function shop_index() {
		$this->shop_check_login();
		$this->loadModel('SalesDetail');		
		$this->loadModel('Publisher');		
		$this->loadModel('Item');		
		$this->loadModel('Stock');		
		$this->loadModel('Ledger');		
		$this->loadModel('Sale');		
		$this->loadModel('SalesReturn');		
		$this->loadModel('Voucher');		
				

		$this->loadModel('DefaultLedgerOpeing');	
		$this->loadModel('VoucherDetail');	
		
		//$this->loadModel('Item');		
		//$this->loadModel('PurchaseDetails');		
		//$this->loadModel('Stock');		
		//$this->loadModel('PurchaseSale');		
		
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
			$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
			// Top 10 selling product=========================================================
			
			$toptencond=array('SalesDetail.is_deleted'=>BOOL_FALSE,'SalesDetail.is_active'=>BOOL_TRUE,'Sale.is_deleted'=>BOOL_FALSE,'Sale.is_active'=>BOOL_TRUE,'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'));
			$toptenfileds=array('SUM(SalesDetail.quantity) as sale_qty');

			$toptenSalesProduct=$this->SalesDetail->find('all',array('conditions'=>$toptencond,'fields'=>$toptenfileds,'group'=>array('SalesDetail.item_id'),'contain'=>array('Sale'=>array('id'),'Item'=>array('name')),'recursive'=>2,'order'=>array('sale_qty'=>'DESC'),'limit'=>10));		
			$totalSalesProduct=$this->SalesDetail->find('first',array('conditions'=>$toptencond,'fields'=>$toptenfileds));
			$total_sale_qty=$totalSalesProduct[0]['sale_qty'];
			$this->set(compact('total_sale_qty'));
			$this->set(compact('toptenSalesProduct'));
		
		 // Today Sale===================================================================
			$conditions=array('Sale.is_active'=>BOOL_TRUE,'Sale.is_deleted'=>BOOL_FALSE,'DATE(Sale.sales_date)'=>date('Y-m-d'),'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'));
			$fields=array('Sale.total_amount','SUM(Sale.total_amount) AS totalSale');
			$saleTotal=$this->Sale->find('first',array('conditions'=>$conditions,'fields'=>$fields,'recursive'=>-1)) ;			
			$saleTotal['totalSale']=$saleTotal[0]['totalSale'];
			
			$conditions1=array('Sale.is_active'=>BOOL_TRUE,'Sale.is_deleted'=>BOOL_FALSE,'DATE(Sale.sales_date)'=>date('Y-m-d'),'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'));
			$fields1=array('SUM(Sale.total_payment) AS totalCashSale');
			$saleCashTotal=$this->Sale->find('first',array('conditions'=>$conditions1,'fields'=>$fields1,'recursive'=>-1)) ;
			$saleTotal['totalCashSale']=$saleCashTotal[0]['totalCashSale'];
			
			$conditions2=array('Sale.is_active'=>BOOL_TRUE,'Sale.is_deleted'=>BOOL_FALSE,'DATE(Sale.sales_date)'=>date('Y-m-d'),'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'));
			$fields2=array('SUM(Sale.total_balance) AS totalCreditSale');
			$saleCreditTotal=$this->Sale->find('first',array('conditions'=>$conditions2,'fields'=>$fields2,'recursive'=>-1)) ;
			$saleTotal['totalCreditSale']=$saleCreditTotal[0]['totalCreditSale'];
			
			$this->set(compact('saleTotal'));
			// Cash in hand=============================================================
			$from_date=date('Y-m-d');
			$total_debit=0;
			$total_credit=0;
			$opeingData=$this->DefaultLedgerOpeing->find('first',array('conditions'=>array('DefaultLedgerOpeing.is_deleted'=>BOOL_FALSE,'DefaultLedgerOpeing.ledger_id'=>CASH_LEDGER,'DefaultLedgerOpeing.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')),'fields'=>array('DefaultLedgerOpeing.amount','DefaultLedgerOpeing.dr_cr')));
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
				$debitData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'VoucherDetail.ledger_id'=>CASH_LEDGER,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'Voucher.date <='=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_debit')));
				if(!empty($debitData)){
					$total_debit=$total_debit+$debitData[0]['total_debit'];
				}
				$creditData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'VoucherDetail.ledger_id'=>CASH_LEDGER,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'Voucher.date <='=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_credit')));
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
				$this->set(compact('actual_opeing'));
			// Outstanding=====================================================================
				// Total Receiable
				$recDebitData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'Ledger.group_id'=>GROUP_SUNDRY_DEBTOR_ID,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'Voucher.date <='=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_debit')));
				
				
				$recCreditData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'Ledger.group_id'=>GROUP_SUNDRY_DEBTOR_ID,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'Voucher.date <='=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_credit')));
				
				$total_receiable=$recDebitData[0]['total_debit']-$recCreditData[0]['total_credit'];
				
				// Total Payable
				$payCreditData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'Ledger.group_id'=>GROUP_SUNDRY_CREDITOR_ID,'VoucherDetail.dr_cr'=>LEDGER_IS_CREDIT,'Voucher.date <='=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_credit')));
				
				
				$payDebitData=$this->VoucherDetail->find('first',array('conditions'=>array('VoucherDetail.is_deleted'=>BOOL_FALSE,'Voucher.is_active'=>BOOL_TRUE,'Voucher.is_deleted'=>BOOL_FALSE,'Ledger.group_id'=>GROUP_SUNDRY_CREDITOR_ID,'VoucherDetail.dr_cr'=>LEDGER_IS_DEBIT,'Voucher.date <='=>$from_date,'Voucher.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(VoucherDetail.amount) as total_debit')));
				
				$total_payable=$payCreditData[0]['total_credit']-$payDebitData[0]['total_debit'];
				$outStanding['total_payable']=$total_payable;
				$outStanding['total_receiable']=$total_receiable;
				$this->set(compact('outStanding'));
				//Seven days sale===================================================================
				$countDate=array();
				$start   = new DateTime();
				$end     = new DateTime();

				$start   = $start->modify( '-7 days' ); 
				
				$interval = new DateInterval('P1D');
				$daterange = new DatePeriod($start, $interval ,$end);
				$sevenDate=array();
				foreach($daterange as $date){
				$sevenDate[ ]=$date->format('Y-m-d') ;
				}
				
				for($i=0; $i<count($sevenDate); $i++)
				{
					$dat=$sevenDate[$i];
					
				$saleAmt[0]['totalTodaySale']=0;
				$returnData[0]['returnamt']=0;
				 $saleAmt=$this->Sale->find('first',array('conditions' => array('DATE(Sale.sales_date)'=>$dat,'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')),'fields'=>array('SUM(Sale.total_amount) AS totalTodaySale')));
				 //$returnData=$this->SalesReturn->find('first',array('conditions'=>array('DATE(Sale.sales_date)'=>$dat),'fields'=>array('SUM(SalesReturn.total_amount) as returnamt')));
				
				$countDate[]=!empty($saleAmt[0]['totalTodaySale'])?$saleAmt[0]['totalTodaySale']:0;
				 
				 //$countDate[$dat][0]['totalSale']=$saleAmt[0]['totalTodaySale']-$returnData[0]['returnamt'];
				}
				$countDate=implode(',',$countDate);			
				$this->set(compact('countDate'));
				//Customer Visit==================================================================
				$otherDayCust=$this->Ledger->find('count',array('conditions'=>array('Ledger.user_profile_id'=>$user_profile_id,'Ledger.is_deleted'=>BOOL_FALSE,'Ledger.is_active'=>BOOL_TRUE,'Ledger.group_id'=>GROUP_SUNDRY_DEBTOR_ID,'DATE(Ledger.created) !='=>date('Y-m-d'))));
				$toDayCust=$this->Ledger->find('count',array('conditions'=>array('Ledger.user_profile_id'=>$user_profile_id,'Ledger.is_deleted'=>BOOL_FALSE,'Ledger.is_active'=>BOOL_TRUE,'Ledger.group_id'=>GROUP_SUNDRY_DEBTOR_ID,'DATE(Ledger.created)'=>date('Y-m-d'))));
				
				$customer['today']=$toDayCust;
				$customer['other_today']=$otherDayCust;
				$this->set(compact('customer'));
				// Total Stock================================================================
				$stockData=$this->Stock->find('first',array('conditions'=>array('Stock.is_deleted'=>BOOL_FALSE,'Item.is_deleted'=>BOOL_FALSE,'Item.is_active'=>BOOL_TRUE,'Item.user_profile_id'=>$user_profile_id),'fields'=>array('SUM(Item.sp*Stock.quantity) as total_stock')));
				$stockamt=$stockData[0]['total_stock'];
				$this->set(compact('stockamt'));
				
        } else {
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	
	
	/**
	Reset Password
	**/
	public function shop_changePassword()
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
					'User.role_id'=>SHOP_ROLE_ID,
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
								return $this->redirect(array('controller'=>'shops','action' => 'changePassword','shop'=>true,'ext'=>URL_EXTENSION));
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
	
	
	
	/*********************************************************************/	
	/*
	@ Amit Sahu
	@ generateRandomString($length)
	@ retun string with specified length
	@ For generating a random password
	@ 01.02.17
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
	public function shop_resetPassword($email,$token)
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
						return $this->redirect(array('controller'=>'Compounders','action' => 'login','compounder'=>true,'ext'=>URL_EXTENSION));
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
	
	public function shop_addSale()
    {
		/*echo '<pre>';
		echo $this->Session->read('UserProfile.UserProfile.dealer_type');
        exit;*/
		$cond=array();
        $this->shop_check_login();

        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {
            $this->loadModel('Sale');
            $this->loadModel('SalesDetail');
            $this->loadModel('Item');
            $this->loadModel('Stock');
            $this->loadModel('PaymentTransaction');

			$this->loadModel('AdvanceDetail');
       
            $this->loadModel('Member');
            $this->loadModel('State');
            $this->loadModel('City');
            $this->loadModel('Ledger');
            $this->loadModel('SaleCharge');
            $this->loadModel('AdvanceAdjustment');
            $this->loadModel('CessMaster');
            $this->loadModel('GstMaster');
            $this->loadModel('Voucher');
            $this->loadModel('Ledger');
            $this->loadModel('PartyDetail');
          
           	$banks=$distList=$this->Ledger->getLedgerListByGroup(GROUP_BANK_ACCOUNT_ID,$this->Session->read('Auth.User.user_profile_id'));	
			$this->set(compact('banks'));
			
			$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
			
			$cessData=$this->CessMaster->find('all',array('conditions'=>array('CessMaster.is_deleted'=>BOOL_FALSE,'CessMaster.is_active'=>BOOL_TRUE),'fields'=>array('CessMaster.name','CessMaster.code')));
			$this->set(compact('cessData'));	          
			
            if($this->request->is('post')){
				
				 if(!empty($this->request->data["Sale"]))
                    {
				
					
					
					$membeId=$this->request->data["Sale"]["customer_id"];
					if(empty($membeId))
				     {
					    $this->Ledger->create();   
					 }else{
						 $this->request->data["Ledger"]["id"]=$membeId;
						  $customer_id =$membeId;
					 }
                    	//ADD Memer  & Details
						$this->request->data["Ledger"]["user_profile_id"]=$this->Session->read('Auth.User.user_profile_id');
						$this->request->data["Ledger"]["name"]=$this->request->data["Sale"]["customer_name"];
						$this->request->data["Ledger"]["group_id"]=GROUP_SUNDRY_DEBTOR_ID;
						  if($this->Ledger->save($this->request->data["Ledger"]))
						   {
							 
							   if(empty($membeId))
								 {
									 $customer_id = $this->Ledger->getInsertID(); 
									 $this->request->data["PartyDetail"]["ledger_id"]=$customer_id;
									 $this->request->data["PartyDetail"]["mobile"]=$this->request->data["Sale"]["contact_no"];
									$this->request->data["PartyDetail"]["gstin"]=$this->request->data["Sale"]["customer_gstin"];
									if(!empty($this->request->data["Sale"]["state"]))
									{
									$this->request->data["PartyDetail"]["state"]=$this->request->data["Sale"]["state"];
									}
									if(!empty($this->request->data["Sale"]["city"]))
									{
									 $this->request->data["PartyDetail"]["city"]=$this->request->data["Sale"]["city"];
									}
								   
									if(!empty($this->request->data["Sale"]["email"]))
									{
									$this->request->data["PartyDetail"]["email"]=$this->request->data["Sale"]["email"];
									}
									if(!empty($this->request->data["Sale"]["address_1"]))
									{
									$this->request->data["PartyDetail"]["address"]=$this->request->data["Sale"]["address_1"];
									}
									if(!empty($this->request->data["Sale"]["pin_code"]))
									{
									$this->request->data["PartyDetail"]["pin_code"]=$this->request->data["Sale"]["pin_code"];
									}
									$this->PartyDetail->save($this->request->data["PartyDetail"]);
								 }else{
									 
									  $customer_id =$membeId;
								 }
								 
									
						   }
						$this->request->data['Sale']['customer_id']= $customer_id;
						
						
						 
				   
					}
				
                $errqty=0;
                
              
           foreach($this->request->data['SalesDetail'] as $k)
                    {
                        
                        if($k['quantity']==0)
                        {
                            $errqty=1;
                        }
                        
                    }                        
                if($errqty==1)
                {
                    $this->Session->setFlash('Invalid quentity please try again  ', 'error');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addSale','shop'=>true,'ext'=>URL_EXTENSION));
                    exit;
                }
            
            
            /*    if(!empty($this->request->data["Sale"]))
                    {
					$mbNo=$this->request->data["Sale"]["contact_no"];
					
					
					$membeId=$this->request->data["Sale"]["customer_id"];
					if(empty($membeId))
				     {
					    $this->Member->create();   
					 }else{
						 $this->request->data["Member"]["id"]=$membeId;
						  $customer_id =$membeId;
					 }
                    	//ADD Memer  & Details
						$this->request->data["Member"]["user_profile_id"]=$this->Session->read('Auth.User.user_profile_id');
						$this->request->data["Member"]["customer_name"]=$this->request->data["Sale"]["customer_name"];
						$this->request->data["Member"]["contact_no"]=$this->request->data["Sale"]["contact_no"];
						$this->request->data["Member"]["customer_gstin"]=$this->request->data["Sale"]["customer_gstin"];
						if(!empty($this->request->data["Sale"]["state"]))
						{
						$this->request->data["Member"]["state"]=$this->request->data["Sale"]["state"];
						}
						if(!empty($this->request->data["Sale"]["city"]))
						{
						 $this->request->data["Member"]["city"]=$this->request->data["Sale"]["city"];
						}
					   
						if(!empty($this->request->data["Sale"]["email"]))
						{
						$this->request->data["Member"]["email"]=$this->request->data["Sale"]["email"];
						}
						if(!empty($this->request->data["Sale"]["address_1"]))
						{
						$this->request->data["Member"]["address_1"]=$this->request->data["Sale"]["address_1"];
						}
						if(!empty($this->request->data["Sale"]["pin_code"]))
						{
						$this->request->data["Member"]["pin_code"]=$this->request->data["Sale"]["pin_code"];
						}
						
						   if($this->Member->save($this->request->data["Member"]))
						   {
							 
							   if(empty($membeId))
								 {
									  $customer_id = $this->Member->getInsertID(); 
								 }else{
									 
									  $customer_id =$membeId;
								 }
						   }
						$this->request->data['Sale']['customer_id']= $customer_id;
				   
					}*/
					
                //$location_id = $this->Session->read('Auth.User.location_id');
                //$this->request->data['Sale']['location_id']= $location_id;
                
                $this->request->data['Sale']['sales_date']=$this->request->data['Sale']['sales_date'];
                $this->Sale->create();   
				
                $this->request->data['Sale']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id'); 
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				
				
				$saleOlddata=$this->Sale->find('first',array('conditions'=>array('Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),'Sale.sales_date >='=>$fin_from_date,'Sale.sales_date <='=>$fin_to_date),'fields'=>array('Sale.invoice_no','Sale.sale_no'),'order'=>array('Sale.invoice_no'=>'DESC')));
				// Create invoice
				$invoice=1;
				$sale_no=1;
				if(!empty($saleOlddata))
				{
					$invoice=$saleOlddata['Sale']['invoice_no']+1;
					$sale_no=$saleOlddata['Sale']['sale_no']+1;
				}
				
				$this->request->data['Sale']['invoice_no']=$invoice;
				$this->request->data['Sale']['sale_no']=$invoice;
				// End create invoice			
				$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
				$lastNoPtData=$this->Voucher->find('first',array('conditions'=>array('Voucher.user_profile_id'=>$user_profile_id,'Voucher.date >='=>$fin_from_date,'Voucher.date <='=>$fin_to_date,'Voucher.type'=>RECEIPT),'order'=>array('Voucher.no'=>'DESC'),'fields'=>array('Voucher.no')));
					
				$new_pt_no=1;
				if(!empty($lastNoPtData))
				{
					$last_pt_no=$lastNoPtData['Voucher']['no'];
					$new_pt_no=$last_pt_no+1;
				}
				$this->request->data["Sale"]['round_up_amt']=$this->request->data["Sale"]['total_amount']-$this->request->data["Sale"]['total_before_round'];	
					
                if($this->Sale->save($this->request->data["Sale"])){
					
					if(isset($this->request->data['Sale']['inclusive']))
					{
					$inclusive=$this->request->data['Sale']['inclusive'];
					}else{
						$inclusive=0;
					}
				
                    $sales_id = $this->Sale->getInsertID();
                   
                    if(!empty($this->request->data["Sale"]["mode_cr_dr_card"])){
                        $card_amt = $this->request->data["Sale"]["mode_cr_dr_card"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>SALE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_ONLINE,
                        "reference_id"=>$sales_id,
                        "person_name"=>$this->request->data["Sale"]['customer_name'],
                        "payment"=>$card_amt,
                        "bank_name"=>$this->request->data["Sale"]["card_bank_name"],
						"dr_bank"=>$this->request->data["Sale"]["dr_bank"],
						"card_no"=>$this->request->data["Sale"]["bcard_no"],
                        "user_profile_id"=>$user_profile_id,
                        "trans_no"=>$new_pt_no,

                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    if(!empty($this->request->data["Sale"]["mode_cheque"])){
                        $cheque_amt = $this->request->data["Sale"]["mode_cheque"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>SALE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CHEQUE,
                        "reference_id"=>$sales_id,
                        "person_name"=>$this->request->data["Sale"]['customer_name'],
                        "payment"=>$cheque_amt,
                        "bank_name"=>$this->request->data["Sale"]["cheque_bank_name"],
                        "cheque_date"=>$this->request->data["Sale"]["cheque_date"],
						"user_profile_id"=>$user_profile_id,
						 "cheque_no"=>$this->request->data["Sale"]["cheque_no"],
						 "dr_bank"=>$this->request->data["Sale"]["cheque_dr_bank"],
						"trans_no"=>$new_pt_no,
                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    if(!empty($this->request->data["Sale"]["mode_cash"])){
                        $cash_amt = $this->request->data["Sale"]["mode_cash"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>SALE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CASH,
                        "reference_id"=>$sales_id,
                        "person_name"=>$this->request->data["Sale"]['customer_name'],
                        "payment"=>$cash_amt,     
						"user_profile_id"=>$user_profile_id,	
						"trans_no"=>$new_pt_no,	
                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    // Advance adjust entry
				/*	if($this->request->data['Sale']['advance_adj']==BOOL_TRUE)
					{
						$advDetails=$this->request->data['AdvanceAdjustment'];
				
						if(!empty($advDetails))
						{
							foreach($advDetails as $adv)
							{
								if(!empty($adv['amount']))
								{
									$this->AdvanceAdjustment->create();
									$this->request->data['AdvanceAdjustment']['sale_id']=$sales_id;
									$this->request->data['AdvanceAdjustment']['amount']=$adv['amount'];
									$this->request->data['AdvanceAdjustment']['gst_rate']=$adv['gst_rate'];
									$this->request->data['AdvanceAdjustment']['advance_id']=$adv['advance_id'];
									$this->AdvanceAdjustment->save($this->request->data['AdvanceAdjustment']);
									$advcon=array('AdvanceDetail.is_deleted'=>BOOL_FALSE,'AdvanceDetail.is_active'=>BOOL_TRUE,'AdvanceDetail.advance_id'=>$adv['advance_id'],'AdvanceDetail.gst_rate'=>$adv['gst_rate']);
									$advFields=array('AdvanceDetail.id','AdvanceDetail.balance_amt','AdvanceDetail.adjusted_amt');
									$advData=$this->AdvanceDetail->find('all',array('conditions'=>$advcon,'fields'=>$advFields));
									if(!empty($advData))
									{
										
										$adv_amt=$adv['amount'];
										foreach($advData as $advAmt)
										{
											if($adv_amt!=0)
											{
												$this->request->data['AdvanceDetail']['id']=$advAmt['AdvanceDetail']['id'];
												if($adv_amt>$advAmt['AdvanceDetail']['balance_amt'])
												{
													$this->request->data['AdvanceDetail']['balance_amt']=0;
													$this->request->data['AdvanceDetail']['adjusted_amt']=$advAmt['AdvanceDetail']['adjusted_amt']+$advAmt['AdvanceDetail']['balance_amt'];
													$adv_amt=$adv_amt-$advAmt['AdvanceDetail']['balance_amt'];
												}else{
													$this->request->data['AdvanceDetail']['adjusted_amt']=$advAmt['AdvanceDetail']['adjusted_amt']+$adv_amt;
													$this->request->data['AdvanceDetail']['balance_amt']=$advAmt['AdvanceDetail']['balance_amt']-$adv_amt;
													$adv_amt=0;
												}
												
												$this->AdvanceDetail->save($this->request->data['AdvanceDetail']);
											}
										}
									}
								}
							}
						}
					}
                    
                    */
                    
                    $distcountArr=array();
                    foreach($this->request->data['SalesDetail'] as $k)
                    {
                        $dp = !empty($k['discount'])?$k['discount']:0;
                        $dpi = (($k['price'] / 100) * $dp);
                        $discount = $k['quantity'] * $dpi;
    
                        
						$gsamt=($k['total_amount']/100)*$k['gst_rate'];
						$value=$this->Unitchange->change($k['item_id'],$k['quantity'], $k['unit']);
						$nill="";
						$itemNillData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$k['item_id']),'fields'=>array('Item.nill_rated'),'contain'=>array('GstMaster.sgst','GstMaster.cgst','GstMaster.igst')));
						if($k['gst_rate']==BOOL_FALSE)
						{
						
							$nill=$itemNillData['Item']['nill_rated'];
						}
						
						$cgs_per=$itemNillData['GstMaster']['cgst'];
						$sgst_per=$itemNillData['GstMaster']['sgst'];
						$igst_per=$itemNillData['GstMaster']['igst'];
						if($k['gst_rate']==BOOL_FALSE)
						{
							
							$nill=$itemNillData['Item']['nill_rated'];
						}
                       
						$cgst_amt=$k['cgst_amt'];
						$sgst_amt=$k['sgst_amt'];
						$igst_amt=$k['igst_amt'];
						$gst_type=$this->request->data['Sale']['gst_type'];
					
						if($gst_type==IGST)
						{
					
							$cgst_amt=0;
							$sgst_amt=0;
						}else{
							$igst_amt=0;
						}
                        $this->SalesDetail->create();
                        $sDtail=array(
                        'sales_id'=>$sales_id,
                        'item_id'=>$k['item_id'],
                        'quantity'=>$value['qty'],
                        'price'=>$k['price'],                        
                        'total_amount'=>$k['total_amount'],
                        'gst_amt'=>$gsamt,
						'cgst_amt'=>$cgst_amt,
                        'sgst_amt'=>$sgst_amt,
                        'igst_amt'=>$igst_amt,
                        'cgst_per'=>$cgs_per,
                        'sgst_per'=>$sgst_per,
                        'igst_per'=>$igst_per,						
                        'hsn'=>$k['hsn'],
                        'gst_slab'=>$k['gst_rate'],
                        'sp'=>$k['sp'],
                        'discount_per'=>$k['discount_per'],
						'unit'=>$value['unit'],
						'nill_rated'=>$nill,
                        'created_by'=>$this->Session->read('Auth.User.id'),
						'cess_type'=>$k['cess_type'],
						'cess_amount'=>$k['cess_amt'],
						'cess_name'=>$k['cess_name'],
                        
                        );

                        if($this->SalesDetail->save($sDtail)){
							 $item_id=$k["item_id"];
                            $itemdata=$this->Item->findById($item_id);//Get category Id
							if($itemdata['Item']['item_type']!=SERVICES_TYPE)
							{
                            $sales_detail_id = $this->SalesDetail->getInsertID();
                            $stock=$this->Stock->find('first',array(
                            'conditions'=>array(
                                    'Stock.item_id'=>$k['item_id'],
                                    'Stock.is_deleted'=>BOOL_FALSE,
                                    'Stock.is_active'=>BOOL_TRUE,
                                ),
                            'recursive'    => -1
                            ));
                            $this->Stock->id = $stock["Stock"]["id"];
                            $this->Stock->saveField('quantity',$stock['Stock']['quantity'] - $value['qty']);
							
                            $psid = $sales_detail_id;
                           
                            $category_id=$itemdata["Item"]["category_id"];
                            $location_id=$this->Auth->user("location_id");
                            $qty=$value['qty'];
                            $conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
                            $fields=array('Stock.quantity');
                            $stockData=$this->Stock->getallStock($conditions,$fields);
                            $stock=$stockData['Stock']['quantity'];
                            $price=$itemdata['Item']['price'] * $stock;
                            
                            $updateSelePurchaseController = new CommonsController;
                            $updateSelePurchaseController->updatePurchaseSale($psid,$item_id,SALES,$category_id,$location_id,$qty,$stock,$price);
							}
                        }
                    }
                    
					// Save Sale other Charges
						/*if(!empty($this->request->data['Sale']['other_charge_inc_amount']) or !empty($this->request->data['Sale']['charge_inc_amount']))
						{
							$ledgerIDArr="";
							if(!empty($this->request->data['Sale']['charge_inc_type']))
							{
								$ledgerIDArr=implode(',',$this->request->data['Sale']['charge_inc_type']);
							}
						$this->SaleCharge->create();	
						$this->request->data['SaleCharge']['gst_type']=CONSIDER_GST;	
						$this->request->data['SaleCharge']['sale_id']=$sales_id;	
						$this->request->data['SaleCharge']['ledger_id']=$ledgerIDArr;	
						$this->request->data['SaleCharge']['ledger_amt']=$this->request->data['Sale']['charge_inc_amount'];	
						$this->request->data['SaleCharge']['other_exp']=$this->request->data['Sale']['other_charge_inc'];	
						$this->request->data['SaleCharge']['othe_amount']=$this->request->data['Sale']['other_charge_inc_amount'];
						$this->SaleCharge->save($this->request->data['SaleCharge']);
						}
						if(!empty($this->request->data['Sale']['charge_exc_amount']) or !empty($this->request->data['Sale']['other_charge_exc_amount']))
						{
							$ledgerIDArr1="";
							if(!empty($this->request->data['Sale']['charge_exc_type']))
							{
								$ledgerIDArr1=implode(',',$this->request->data['Sale']['charge_exc_type']);
							}
						$this->request->data['SaleCharge1']['gst_type']=NONCONSIDER_GST;	
						$this->request->data['SaleCharge1']['sale_id']=$sales_id;		
						$this->request->data['SaleCharge1']['ledger_id']=$ledgerIDArr1;	
						$this->request->data['SaleCharge1']['ledger_amt']=$this->request->data['Sale']['charge_exc_amount'];	
						$this->request->data['SaleCharge1']['other_exp']=$this->request->data['Sale']['other_charge_exc'];	
						$this->request->data['SaleCharge1']['othe_amount']=$this->request->data['Sale']['other_charge_exc_amount'];
						$this->SaleCharge->create();
						$this->SaleCharge->save($this->request->data['SaleCharge1']);
						}*/							
						// End Save Sale other Charges	
					$invoice_type=$this->Session->read('UserProfile.UserProfile.invoice_type');
					if($invoice_type==1)
					{
                    $this->Session->setFlash('The Sale has been saved <a href="'.Router::fullbaseUrl().Router::url(array('controller'=>'shops','action'=>'printInvoice','shop'=>true,'ext'=>URL_EXTENSION,$this->Encryption->encrypt($sales_id))).'" class="btn btn-warning"  >Print Invoice</a>', 'success');                
                    return $this->redirect(array('controller'=>'shops','action' => 'printInvoice','shop'=>true,'ext'=>URL_EXTENSION,$this->Encryption->encrypt($sales_id)));
					}elseif($invoice_type==2)
					{
						$this->Session->setFlash('The Sale has been saved <a href="'.Router::fullbaseUrl().Router::url(array('controller'=>'shops','action'=>'printInvoice2','shop'=>true,'ext'=>URL_EXTENSION,$this->Encryption->encrypt($sales_id))).'" class="btn btn-warning"  >Print Invoice</a>', 'success');                
						return $this->redirect(array('controller'=>'shops','action' => 'printInvoice2','shop'=>true,'ext'=>URL_EXTENSION,$this->Encryption->encrypt($sales_id)));
					}
                
                }
                else 
                {
                    $this->Session->setFlash('The Sales could not be saved. Please, try again.', 'error');
                }
            }
            
            
						
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
	

	public function shop_printInvoice($id=NULL){
		
		$this->shop_check_login();
		$this->layout="shop/invoice";
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		$this->loadModel('GstMaster');
		
		$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
		
		
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
				'recursive'=>3,
				'contain'=>array('SalesDetail'=>array('Item'=>array('AltUnit'=>array('code')),'Unit'=>array('code')),'Ledger'=>array('name','PartyDetail'=>array('mobile','gstin','address','pin_code','State'=>array('name'))),'SaleCharge'),
			));
			
			$this->set(compact('sale'));
			/*echo "<pre>";
			print_r($sale);
			exit;*/
			
		
			
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
	}
	
	public function shop_printInvoice2($id=NULL){
		
		$this->shop_check_login();
		$this->layout="shop/invoice";
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		$this->loadModel('GstMaster');
		
		$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
		
		
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
				'recursive'=>3,
				'contain'=>array('SalesDetail'=>array('Item'=>array('AltUnit'=>array('code')),'Unit'=>array('code')),'Ledger'=>array('name','PartyDetail'=>array('mobile','gstin','address','pin_code','State'=>array('name'))),'SaleCharge'),
			));
			
			$this->set(compact('sale'));
			/*echo "<pre>";
			print_r($sale);
			exit;*/
			
		
			
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
	}
    /*
	kajal kurrewar
	Sales list
	*/
    public function shop_salesList() 
	{
		$cond=array();
		$this->shop_check_login();		
		$this->loadModel('Sale');
		$this->loadModel('Member');
		$this->loadModel('SalesDetail');
		$this->loadModel('Item');
		$this->loadModel('UserProfile');
		$this->loadModel('GstMaster');
		$UserProfile=$this->Session->read('UserProfile');
		
		$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
			
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
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
					$cond['OR']['Sale.invoice_no']=$this->request->data['Sale']['name'];
					$cond['OR']['Ledger.name LIKE']=$this->request->data['Sale']['name']."%";
					
				}
			}			
		   
			$conditions = array(
				'Sale.id !=' => BOOL_FALSE,
				'Sale.is_deleted' => BOOL_FALSE,
				'Sale.is_active' => BOOL_TRUE,
				'Sale.user_profile_id' => $this->Session->read('Auth.User.user_profile_id')
            );
           $conditions=array_merge($conditions,$cond);
			$this->Paginator->settings = array(
				    'Sale' => array(
					'conditions' => $conditions,
					'order' => array('Sale.id' => 'DESC'),
					'contain'=>array('SalesDetail'=>array('Item'=>array('AltUnit'=>array('code')),'Unit'=>array('code')),'Ledger'=>array('name','PartyDetail'=>array('mobile','gstin','address','pin_code','State'=>array('name'))),'SaleCharge'),
					'limit' => PAGINATION_LIMIT_1,
					'recursive' => 2
			));
			$sales = $this->Paginator->paginate('Sale');
			
			$this->set(compact('sales'));
			
			$this->set(compact('UserProfile'));	
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	public function shop_resetSaleSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();		
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
	/*
	kajal kurrewar
	31-08-2017
	edit sale 
	*/
	 public function shop_editSale($id = null) 
	{
		$this->shop_check_login();
		
				
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,UPDATE_PERMISSION_ID))) 
		{
			
			$id = $this->Encryption->decrypt($id);
			$this->loadModel('Sale');		
			$this->loadModel('SalesDetail');
			$this->loadModel('Item');
			$this->loadModel('Member');
			
		$this->Sale->id=$id;
			if (!$this->Sale->exists($id)) 
			{
				throw new NotFoundException('Invalid Sale');
			}
			
			if ($this->request->is('post') || $this->request->is('put')) 
			{	
				$this->request->data['Sale']['sales_date']=date('Y-m-d',strtotime($this->request->data['Sale']['sales_date']));
				$this->request->data['Sale']['modified_by']=$this->Auth->User('id');
				$this->request->data["Member"]["customer_name"]=$this->request->data["Sale"]["customer_name"];
				$this->request->data["Member"]["state"]=$this->request->data["Sale"]["state"];
				$this->request->data["Member"]["city"]=$this->request->data["Sale"]["city"];
				$this->request->data["Member"]["customer_gstin"]=$this->request->data["Sale"]["customer_gstin"];
				$this->request->data["Member"]["contact_no"]=$this->request->data["Sale"]["contact_no"];
				$this->request->data["Member"]["email"]=$this->request->data["Sale"]["email"];
				$this->request->data["Member"]["address_1"]=$this->request->data["Sale"]["address_1"];
			
				$this->request->data["Member"]["city"]=$this->request->data["Sale"]["city"];
				
				$this->request->data["Member"]["pin_code"]=$this->request->data["Sale"]["pin_code"];				
				
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
						'SalesDetail'=>array('Item',
							'conditions'=>array(
								'SalesDetail.is_deleted'=>BOOL_FALSE,
								)
							),
						'Member'=>array('conditions'=>array(
								'Member.is_deleted'=>BOOL_FALSE,
								)
								),	
						
							
						),
					));
				
			}
          //echo'<pre>';  print_r($this->request->data);exit;
		}	
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
		
		
    }	
	/*
	Kajal Kurrewar
	02.09.17
	Delete sale list
	*/
	
	public function shop_deleteSaleList() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		$this->loadModel('Stock');
		$this->loadModel('Item');
		$this->loadModel('AdvanceAdjustment');
		$this->loadModel('AdvanceDetail');
		$this->loadModel('Voucher');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Sale->id =$id;
				if (!$this->Sale->exists()) 
				{
					throw new NotFoundException('Invalid Sale');
				}
				
							   if ($this->Sale->saveField('is_deleted',BOOL_TRUE)) 
							   {
								   
								   $this->Voucher->updateAll(array('Voucher.is_deleted' =>BOOL_TRUE,'Voucher.is_active'=>BOOL_FALSE),array('Voucher.referance_id' =>$id,'Voucher.reporting_type'=>REPORTING_SALE));
								
								$sddata=$this->SalesDetail->find('all',array('conditions'=>array('SalesDetail.sales_id'=>$id,'SalesDetail.is_deleted'=>BOOL_FALSE)));
								   if(!empty($sddata))
								   {
									   foreach($sddata as $row)
									   {
										   $qty=$row['SalesDetail']['quantity'];
											$stockData1=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$row['SalesDetail']['item_id']),'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE));
											
											$this->request->data['Stock']['id']=$stockData1['Stock']['id'];
											$this->request->data['Stock']['quantity']=$stockData1['Stock']['quantity']+$qty;
											$this->Stock->save($this->request->data['Stock']);
											
											$psid = $id;
											$item_id=$row['SalesDetail']['item_id'];
											$itemdata=$this->Item->findById($item_id);//Get category Id
											$category_id=$itemdata["Item"]["category_id"];
											$location_id=$this->Auth->user("location_id");
											$qty=$qty;
											$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
											$fields=array('Stock.quantity');
											$stockData=$this->Stock->getallStock($conditions,$fields);
											$stock=$stockData['Stock']['quantity'];
											$price=$itemdata['Item']['price'] * $stock;
											
											$updateSelePurchaseController = new CommonsController;
											$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,SALE_DELETE,$category_id,$location_id,$qty,$stock,$price);
										
									   }
								   }
								   
								 // Remove advance adjustment
								   $conditions=array('AdvanceAdjustment.is_deleted'=>BOOL_FALSE,'AdvanceAdjustment.is_active'=>BOOL_TRUE,'AdvanceAdjustment.sale_id'=>$id);
								   $fields=array('AdvanceAdjustment.id','AdvanceAdjustment.advance_id','AdvanceAdjustment.amount','AdvanceAdjustment.gst_rate');
								   $adjustData=$this->AdvanceAdjustment->find('all',array('conditions'=>$conditions,'fields'=>$fields));
								   if(!empty($adjustData))
								   {
									   foreach($adjustData as $adv)
									   {
										   $this->request->data['AdvanceAdjustment']['id']=$adv['AdvanceAdjustment']['id'];
										   $this->request->data['AdvanceAdjustment']['is_deleted']=BOOL_TRUE;
										   $this->request->data['AdvanceAdjustment']['is_active']=BOOL_FALSE;
										   $this->AdvanceAdjustment->save($this->request->data['AdvanceAdjustment']);
										
										   $advData=$this->AdvanceDetail->find('all',array('conditions'=>array('AdvanceDetail.advance_id'=>$adv['AdvanceAdjustment']['advance_id'],'AdvanceDetail.gst_rate'=>$adv['AdvanceAdjustment']['gst_rate'],'AdvanceDetail.is_active'=>BOOL_TRUE,'AdvanceDetail.is_deleted'=>BOOL_FALSE),'fields'=>array('AdvanceDetail.id','AdvanceDetail.amount','AdvanceDetail.balance_amt','AdvanceDetail.adjusted_amt')));
										  
										   if(!empty($advData))
										   {
											   $adv_amt=$adv['AdvanceAdjustment']['amount'];
												foreach($advData as $advAmt)
												{
													if($adv_amt!=0)
													{
														$this->request->data['AdvanceDetail']['id']=$advAmt['AdvanceDetail']['id'];
														if($adv_amt>$advAmt['AdvanceDetail']['adjusted_amt'])
														{
															$this->request->data['AdvanceDetail']['balance_amt']=$advAmt['AdvanceDetail']['adjusted_amt']+$advAmt['AdvanceDetail']['balance_amt'];
															$this->request->data['AdvanceDetail']['adjusted_amt']=0;
															$adv_amt=$adv_amt-$advAmt['AdvanceDetail']['adjusted_amt'];
														}else{
															$this->request->data['AdvanceDetail']['adjusted_amt']=$advAmt['AdvanceDetail']['adjusted_amt']-$adv_amt;
															$this->request->data['AdvanceDetail']['balance_amt']=$advAmt['AdvanceDetail']['balance_amt']+$adv_amt;
															$adv_amt=0;
														}
														
														$this->AdvanceDetail->save($this->request->data['AdvanceDetail']);
													}
												}											
										   }
										   
										   
									   }
								   }
								   
								
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Sale List Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Sale List could not be Deleted'));
							   }
						
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
	
	
	
		
	/**
	Kajal kurrewar
	Add PARTY
	**/
	public function shop_addMember()
	{
		$cond=array();
		$this->shop_check_login();
			
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			$this->loadModel('Member');
			if($this->request->is('post')){
				
				$this->Member->create();
				if($this->Member->save($this->request->data["Member"])){
						
					$this->Session->setFlash('New Party added', 'success');				
					return $this->redirect($this->referer());
				
				}
				else 
				{
					$this->Session->setFlash('Error in adding new member. Please, try again.', 'error');
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
	Add Porder
	01.09.17
	*/
	public function shop_addPo()
    {
        $cond=array();
        $this->shop_check_login();

        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {
            $this->loadModel('Item');
            $this->loadModel('Stock');
			$this->loadModel('State');
           
            $this->loadModel('City');
            $this->loadModel('Purchase');
            $this->loadModel('PurchaseDetails');
            $this->loadModel('PaymentTransaction');
            $this->loadModel('Unit');
			$this->loadModel('UserProfile');
			$this->loadModel('Ledger');
			$this->loadModel('PurchaseCharge');
			$this->loadModel('BankAccount');
			$this->loadModel('CessMaster');
			$this->loadModel('GstMaster');
			$this->loadModel('Voucher');
			$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
			
			$gstList=$this->GstMaster->getGstSlabListForCalculation($user_profile_id);
			$this->set(compact('gstList'));
	
			
						
			$cessData=$this->CessMaster->find('all',array('conditions'=>array('CessMaster.is_deleted'=>BOOL_FALSE,'CessMaster.is_active'=>BOOL_TRUE),'fields'=>array('CessMaster.name','CessMaster.code')));
			$this->set(compact('cessData'));	
			
			$cess_list=$this->CessMaster->getCessMasterList();		
			$this->set(compact('cess_list'));	
		
		
			 $banks=$this->Ledger->getLedgerListByGroup(GROUP_BANK_ACCOUNT_ID,$user_profile_id);	
			$this->set(compact('banks'));
			
			// Get Other Exp ledger
			
		$UserProfile=$this->Session->read('UserProfile');
		$this->set(compact('UserProfile'));	
          $unitsList=$this->Unit->find('list',array(
						'conditions'=>array(
						'Unit.id !='=>BOOL_FALSE,
						'Unit.is_deleted'=>BOOL_FALSE,
						'Unit.is_active'=>BOOL_TRUE,
						)
						));			
				$this->set(compact('unitsList'));
			
			$distList=$this->Ledger->getLedgerListByGroup(GROUP_SUNDRY_CREDITOR_ID,$user_profile_id);	
			$this->set(compact('distList'));
			$stateList=$this->State->getStateList();
		$this->set(compact('stateList'));
		
            if($this->request->is('post'))
			{
				
				 $errqty=0;
                
                foreach($this->request->data['SalesDetail'] as $k)
                    {
                        
                        if($k['quantity']==0 or $k['item_id']=='')
                        {
                            $errqty=1;
                        }
                        
                    }                        
                if($errqty==1)
                {
                    $this->Session->setFlash('Invalid quentity or item please try again  ', 'error');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addPo','shop'=>true,'ext'=>URL_EXTENSION));
                    exit;
                }
            
            
                $this->request->data['Purchase']['user_profile_id']=$user_profile_id;
                $this->Purchase->create();                
              if(!empty($this->request->data["Purchase"]['distributor_id']))
			  {
				  $sateno="";
				$disData=$this->Ledger->find('first',array('conditions'=>array('Ledger.id'=>$this->request->data["Purchase"]['distributor_id']),'fields'=>array('Ledger.id'),'contain'=>array('PartyDetail'=>array('State'=>array('state_no'))),'recursive'=>2));
				if(!empty($disData))
				{
					$sateno=$disData['PartyDetail']['State']['state_no'];
				}
				if($sateno!=$this->Session->read('UserProfile.State.state_no'))
				{
					$this->request->data["Purchase"]['gst_type']=IGST;
					$this->request->data["Purchase"]['cgst_amt']=0;
					$this->request->data["Purchase"]['sgst_amt']=0;
				}else{
					$this->request->data["Purchase"]['igst_amt']=0;
				}
				
				$this->request->data["Purchase"]["round_up_amt"]=$this->request->data["Purchase"]["total_amount"]-$this->request->data["Purchase"]["total_before_round"];
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				
				$lastNoData=$this->Purchase->find('first',array('conditions'=>array('Purchase.user_profile_id'=>$user_profile_id,'DATE(Purchase.created) >='=>$fin_from_date,'DATE(Purchase.created) <='=>$fin_to_date),'order'=>array('Purchase.purchase_no'=>'DESC'),'fields'=>array('Purchase.purchase_no')));
				
				
				$new_no=1;
				if(!empty($lastNoData))
				{
					$last_no=$lastNoData['Purchase']['purchase_no'];
					$new_no=$last_no+1;
				}
				$this->request->data["Purchase"]['purchase_no']=$new_no;
				
				$lastNoPtData=$this->Voucher->find('first',array('conditions'=>array('Voucher.user_profile_id'=>$user_profile_id,'Voucher.date >='=>$fin_from_date,'Voucher.date <='=>$fin_to_date,'Voucher.type'=>PAYMENT),'order'=>array('Voucher.no'=>'DESC'),'fields'=>array('Voucher.no')));
				
				$new_pt_no=1;
				if(!empty($lastNoPtData))
				{
					$last_pt_no=$lastNoPtData['Voucher']['no'];
					$new_pt_no=$last_pt_no+1;
				}
				
                if($this->Purchase->save($this->request->data["Purchase"])){
                
					
				
                    $purchase_id = $this->Purchase->getInsertID();
                
                                           
                    if(!empty($this->request->data["Purchase"]["mode_cr_dr_card"])){
						
                        $card_amt = $this->request->data["Purchase"]["mode_cr_dr_card"];
                        
                        $this->PaymentTransaction->create();   
									
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_ONLINE,
                        "reference_id"=>$purchase_id,
                        "party_id"=>$this->request->data["Purchase"]['distributor_id'],
                        "payment"=>$card_amt,
                        "bank_name"=>$this->request->data["Purchase"]["card_bank_name"],
						"trans_no"=>$new_pt_no,
						"dr_bank"=>$this->request->data["Purchase"]["dr_bank"],
						"user_profile_id"=>$user_profile_id,
                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    if(!empty($this->request->data["Purchase"]["mode_cheque"])){
						
                        $cheque_amt = $this->request->data["Purchase"]["mode_cheque"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CHEQUE,
                        "reference_id"=>$purchase_id,
                        "party_id"=>$this->request->data["Purchase"]['distributor_id'],
                        "payment"=>$cheque_amt,
                        "bank_name"=>$this->request->data["Purchase"]["cheque_bank_name"],
                        "cheque_date"=>$this->request->data["Purchase"]["cheque_date"],
                        "cheque_no"=>$this->request->data["Purchase"]["cheque_no"],
                        "dr_bank"=>$this->request->data["Purchase"]["cheque_dr_bank"],
                        "trans_no"=>$new_pt_no,
						"user_profile_id"=>$user_profile_id,
                       
                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    if(!empty($this->request->data["Purchase"]["mode_cash"])){
						
                        $cash_amt = $this->request->data["Purchase"]["mode_cash"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CASH,
                        "reference_id"=>$purchase_id,
						"party_id"=>$this->request->data["Purchase"]['distributor_id'],
                        "payment"=>$cash_amt, 
						"trans_no"=>$new_pt_no,	
						"user_profile_id"=>$user_profile_id,						
                        ));
						$new_pt_no=$new_pt_no+1;
                    }
					$discountArr=array();
                    foreach($this->request->data['SalesDetail'] as $k)
                    {
                     
					   $value=$this->Unitchange->change($k['item_id'],$k['quantity'], $k['unit']);
						$nill="";
						$itemNillData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$k['item_id']),'fields'=>array('Item.nill_rated'),'contain'=>array('GstMaster.sgst','GstMaster.cgst','GstMaster.igst')));
						$cgs_per=$itemNillData['GstMaster']['cgst'];
						$sgst_per=$itemNillData['GstMaster']['sgst'];
						$igst_per=$itemNillData['GstMaster']['igst'];
						if($k['gst_rate']==BOOL_FALSE)
						{
							
							$nill=$itemNillData['Item']['nill_rated'];
						}
                        if(!empty($k['item_id']))
						{
							$cgst_amt=$k['cgst_amt'];
							$sgst_amt=$k['sgst_amt'];
							$igst_amt=$k['igst_amt'];
							if($sateno!=$this->Session->read('UserProfile.State.state_no'))
							{
						
								$cgst_amt=0;
								$sgst_amt=0;
							}else{
								$igst_amt=0;
							}
                        $this->PurchaseDetails->create();
                        $sDtail=array(
                        'purchase_id'=>$purchase_id,
                        'item_id'=>$k['item_id'],
                        'quantity'=>$value['qty'],                  
                        'total_amount'=>$k['total_amount'],
                        'hsn'=>$k['hsn'],
                        'gst_slab'=>$k['gst_rate'],
                        'cgst_amt'=>$cgst_amt,
                        'sgst_amt'=>$sgst_amt,
                        'igst_amt'=>$igst_amt,
                        'cgst_per'=>$cgs_per,
                        'sgst_per'=>$sgst_per,
                        'igst_per'=>$igst_per,
                        'unit'=>$value['unit'],
                        'gross_total'=>$k['sp']*$value['qty'],
                        'pp'=>$k['sp'],
                        'discount'=>$k['discount'],
                        'nill_rated'=>$nill,
                        'created_by'=>$this->Session->read('Auth.User.id'),
						'cess_type'=>$k['cess_type'],
						'cess_amount'=>$k['cess_amt'],
						'cess_name'=>$k['cess_name'],
                        
                        );
						// Srore All Discount
						 $discountArr[]=$k['discount'];
						 //end Srore All Discount
                        if($this->PurchaseDetails->save($sDtail)){
                            $purchase_detail_id = $this->PurchaseDetails->getInsertID();
                            $stock=$this->Stock->find('first',array(
                            'conditions'=>array(
                                    'Stock.item_id'=>$k['item_id'],
                                    'Stock.is_deleted'=>BOOL_FALSE,
                                    'Stock.is_active'=>BOOL_TRUE,
                                ),
                            'recursive'    => -1
                            ));
                           
							if(!empty($stock))
							{
								 $this->Stock->id = $stock["Stock"]["id"];
                            $this->Stock->saveField('quantity',($stock['Stock']['quantity'] + $value['qty']));
							}else{
								$this->Stock->create();
								$this->request->data['Stock']['item_id']=$k['item_id'];
								$this->request->data['Stock']['quantity']=$value['qty'];
								$this->Stock->save($this->request->data['Stock']);
							}
                            
                            $psid = $purchase_detail_id;
                            $item_id=$k["item_id"];
                            $itemdata=$this->Item->findById($item_id);//Get category Id
                            $category_id=$itemdata["Item"]["category_id"];
                            $qty=$value['qty'];
                            $conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
                            $fields=array('Stock.quantity');
                            $stockData=$this->Stock->getallStock($conditions,$fields);
                            $stock=$stockData['Stock']['quantity'];
                            $price=$itemdata['Item']['price'] * $stock;
                            $location_id="";
                            $updateSelePurchaseController = new CommonsController;
                            $updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHASE,$category_id,$location_id,$qty,$stock,$price);
                        }
						}
                    }
						//Update Product Discount Amount
						$itemDis=array_sum($discountArr);                   
						$this->request->data['PurchaseUpdate']['id']=$purchase_id;
						$this->request->data['PurchaseUpdate']['product_discount']=$itemDis;
						$this->Purchase->save($this->request->data['PurchaseUpdate']);
						//End update Product Discount Amount
							
						// Save Purchase other Charges
						/*if(!empty($this->request->data['Purchase']['other_charge_inc_amount']) or !empty($this->request->data['Purchase']['charge_inc_amount']))
						{
							$ledgerIDArr="";
							if(!empty($this->request->data['Purchase']['charge_inc_type']))
							{
								$ledgerIDArr=implode(',',$this->request->data['Purchase']['charge_inc_type']);
							}
						$this->PurchaseCharge->create();	
						$this->request->data['PurchaseCharge']['gst_type']=CONSIDER_GST;	
						$this->request->data['PurchaseCharge']['purchase_id']=$purchase_id;	
						$this->request->data['PurchaseCharge']['ledger_id']=$ledgerIDArr;	
						$this->request->data['PurchaseCharge']['ledger_amt']=$this->request->data['Purchase']['charge_inc_amount'];	
						$this->request->data['PurchaseCharge']['other_exp']=$this->request->data['Purchase']['other_charge_inc'];	
						$this->request->data['PurchaseCharge']['othe_amount']=$this->request->data['Purchase']['other_charge_inc_amount'];
						$this->PurchaseCharge->save($this->request->data['PurchaseCharge']);
						}
						if(!empty($this->request->data['Purchase']['charge_exc_amount']) or !empty($this->request->data['Purchase']['other_charge_exc_amount']))
						{
							$ledgerIDArr1="";
							if(!empty($this->request->data['Purchase']['charge_exc_type']))
							{
								$ledgerIDArr1=implode(',',$this->request->data['Purchase']['charge_exc_type']);
							}
						$this->request->data['PurchaseCharge1']['gst_type']=NONCONSIDER_GST;	
						$this->request->data['PurchaseCharge1']['purchase_id']=$purchase_id;		
						$this->request->data['PurchaseCharge1']['ledger_id']=$ledgerIDArr1;	
						$this->request->data['PurchaseCharge1']['ledger_amt']=$this->request->data['Purchase']['charge_exc_amount'];	
						$this->request->data['PurchaseCharge1']['other_exp']=$this->request->data['Purchase']['other_charge_exc'];	
						$this->request->data['PurchaseCharge1']['othe_amount']=$this->request->data['Purchase']['other_charge_exc_amount'];
						$this->PurchaseCharge->create();
						$this->PurchaseCharge->save($this->request->data['PurchaseCharge1']);
						}
						*/						
						// End Save Purchase other Charges	
					
                    $this->Session->setFlash(' Purchase has been saved ', 'success');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addPo','shop'=>true));
                
                }
			  
                else 
                {
                    $this->Session->setFlash(' Purchase could not be saved. Please, try again.', 'error');
                }
			  }else{
				  $this->Session->setFlash('Please select vendor.', 'error');
			  }
            }
			else
			{
				
			}
           
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
	/*
	Amit Sahu
	Edit Porder
	01.09.17
	*/
	public function shop_editPo($id=NULL)
    {
        $cond=array();
        $this->shop_check_login();
		$id = $this->Encryption->decrypt($id);
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {
             $this->loadModel('Item');
            $this->loadModel('Stock');
			$this->loadModel('State');
   
            $this->loadModel('City');
            $this->loadModel('Purchase');
            $this->loadModel('PurchaseDetails');
            $this->loadModel('PaymentTransaction');
            $this->loadModel('Unit');
			$this->loadModel('UserProfile');
			$this->loadModel('Ledger');
			$this->loadModel('PurchaseCharge');
			$this->loadModel('BankAccount');
			$this->loadModel('CessMaster');
			$this->loadModel('GstMaster');
			
			$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
	
			
						
			$cessData=$this->CessMaster->find('all',array('conditions'=>array('CessMaster.is_deleted'=>BOOL_FALSE,'CessMaster.is_active'=>BOOL_TRUE),'fields'=>array('CessMaster.name','CessMaster.code')));
			$this->set(compact('cessData'));	
			
			$cess_list=$this->CessMaster->getCessMasterList();		
			$this->set(compact('cess_list'));	
		
		
			 $banks=$this->Ledger->getLedgerListByGroup(GROUP_BANK_ACCOUNT_ID,$this->Session->read('Auth.User.user_profile_id'));	
			$this->set(compact('banks'));
			
			// Get Other Exp ledger
			
		$UserProfile=$this->Session->read('UserProfile');
		$this->set(compact('UserProfile'));	
          $unitsList=$this->Unit->find('list',array(
						'conditions'=>array(
						'Unit.id !='=>BOOL_FALSE,
						'Unit.is_deleted'=>BOOL_FALSE,
						'Unit.is_active'=>BOOL_TRUE,
						)
						));			
				$this->set(compact('unitsList'));
			
			$distList=$this->Ledger->getLedgerListByGroup(GROUP_SUNDRY_CREDITOR_ID,$this->Session->read('Auth.User.user_profile_id'));	
			$this->set(compact('distList'));
			$stateList=$this->State->getStateList();
		$this->set(compact('stateList'));
		
            if($this->request->is('post') or $this->request->is('put'))
			{
				
				 $errqty=0;
                
                foreach($this->request->data['SalesDetail'] as $k)
                    {
                        
                        if($k['quantity']==0 or $k['item_id']=='')
                        {
                            $errqty=1;
                        }
                        
                    }                        
                if($errqty==1)
                {
                    $this->Session->setFlash('Invalid quentity or item please try again  ', 'error');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addPo','shop'=>true,'ext'=>URL_EXTENSION));
                    exit;
                }
            
            
                $this->request->data['Purchase']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
                $this->Purchase->create();                
              if(!empty($this->request->data["Purchase"]['distributor_id']))
			  {
				  $sateno="";
			/*	$disData=$this->Distributor->find('first',array('conditions'=>array('Distributor.id'=>$this->request->data["Purchase"]['distributor_id']),'fields'=>array('State.state_no')));*/
			$disData=$this->Ledger->find('first',array('conditions'=>array('Ledger.id'=>$this->request->data["Purchase"]['distributor_id']),'fields'=>array('Ledger.id'),'contain'=>array('PartyDetail'=>array('State'=>array('state_no'))),'recursive'=>2));
				if(!empty($disData))
				{
					$sateno=$disData['PartyDetail']['State']['state_no'];
				}
				if($sateno!=$this->Session->read('UserProfile.State.state_no'))
				{
					$this->request->data["Purchase"]['gst_type']=IGST;
				}
				$this->request->data['Purchase']['id']=$id;
                if($this->Purchase->save($this->request->data["Purchase"])){
                
					
					$purchase_id=$id;
					// Delete payment Transction
             /*      $delpayment=$this->PaymentTransaction->find('all',array('conditions'=>array('PaymentTransaction.type'=>PURCHASE_PAYMENT,'PaymentTransaction.reference_id'=>$purchase_id),'fields'=>array('PaymentTransaction.id')));
				   if(!empty($delpayment))
				   {
					   foreach($delpayment as $pdl)
					   {
						   $this->PaymentTransaction->delete($pdl['PaymentTransaction']['id']);
					   }
				   }*/
					// Delete saleDetails 
					$delpd=$this->PurchaseDetails->find('all',array('conditions'=>array('PurchaseDetails.purchase_id'=>$purchase_id),'fields'=>array('PurchaseDetails.id')));
				   if(!empty($delpd))
				   {
					   foreach($delpd as $pddl)
					   {
						   $psid =$pddl['PurchaseDetails']['id'];
						   $pdData=$this->PurchaseDetails->findById($psid);
						   
						   
						   
						    $stock=$this->Stock->find('first',array(
                            'conditions'=>array(
                                    'Stock.item_id'=>$pdData['PurchaseDetails']['item_id'],
                                    'Stock.is_deleted'=>BOOL_FALSE,
                                    'Stock.is_active'=>BOOL_TRUE,
                                ),
                            'recursive'    => -1
                            ));
                           
							if(!empty($stock))
							{
								$this->Stock->id = $stock["Stock"]["id"];
								$this->Stock->saveField('quantity',($stock['Stock']['quantity'] - $pdData['PurchaseDetails']['quantity']));
							}
						   
						   //echo "<pre>";print_r($pdData);exit;
								
							$item_id=$pdData['PurchaseDetails']['item_id'];
							$itemdata=$this->Item->findById($item_id);//Get category Id
							$category_id=$itemdata["Item"]["category_id"];
							$location_id=$this->Auth->user("location_id");
							$qty=$pdData['PurchaseDetails']['quantity'];
							$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
							$fields=array('Stock.quantity');
							$stockData=$this->Stock->getallStock($conditions,$fields);
							$stock=$stockData['Stock']['quantity'];
							$price=$itemdata['Item']['price'] * $stock;
							
							$updateSelePurchaseController = new CommonsController;
							$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHSE_DELETE,$category_id,$location_id,$qty,$stock,$price);
								
							$this->PurchaseDetails->delete($pddl['PurchaseDetails']['id']);
					   }
				   }
				   // Delete saleDetails 
				/*	$delPc=$this->PurchaseCharge->find('all',array('conditions'=>array('PurchaseCharge.purchase_id'=>$purchase_id),'fields'=>array('PurchaseCharge.id')));
				   if(!empty($delPc))
				   {
					   foreach($delPc as $pcdl)
					   {
						   $this->PurchaseCharge->delete($pcdl['PurchaseCharge']['id']);
					   }
				   }*/
                                           
              /*      if(!empty($this->request->data["Purchase"]["mode_cr_dr_card"])){
                        $card_amt = $this->request->data["Purchase"]["mode_cr_dr_card"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_ONLINE,
                        "reference_id"=>$purchase_id,
                        //"person_name"=>$this->request->data["Purchase"]['customer_name'],
                        "payment"=>$card_amt,
                        "bank_name"=>$this->request->data["Purchase"]["card_bank_name"],

                        ));
                    }
                    if(!empty($this->request->data["Purchase"]["mode_cheque"])){
                        $cheque_amt = $this->request->data["Purchase"]["mode_cheque"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CHEQUE,
                        "reference_id"=>$purchase_id,
                       // "person_name"=>$this->request->data["Purchase"]['customer_name'],
                        "payment"=>$cheque_amt,
                        "bank_name"=>$this->request->data["Purchase"]["cheque_bank_name"],
                        "cheque_date"=>$this->request->data["Purchase"]["cheque_date"],
                       "dr_bank"=>$this->request->data["Purchase"]["cheque_dr_bank"],
                        ));
                    }
                    if(!empty($this->request->data["Purchase"]["mode_cash"])){
                        $cash_amt = $this->request->data["Purchase"]["mode_cash"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CASH,
                        "reference_id"=>$purchase_id,
                        "payment"=>$cash_amt,                        
                        ));
                    }*/
					$discountArr=array();
					
                    foreach($this->request->data['SalesDetail'] as $k)
                    {
                     
					   $value=$this->Unitchange->change($k['item_id'],$k['quantity'], $k['unit']);
						$nill="";
						$cgs_per="";
						$sgst_per="";
						$igst_per="";
						$itemNillData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$k['item_id']),'fields'=>array('Item.nill_rated'),'contain'=>array('GstMaster.sgst','GstMaster.cgst','GstMaster.igst')));
							$cgs_per=$itemNillData['GstMaster']['cgst'];
							$sgst_per=$itemNillData['GstMaster']['sgst'];
							$igst_per=$itemNillData['GstMaster']['igst'];
						if($k['gst_rate']==BOOL_FALSE)
						{
								$nill=$itemNillData['Item']['nill_rated'];
						}
                        if(!empty($k['item_id']))
						{
							$cgst_amt=$k['cgst_amt'];
							$sgst_amt=$k['sgst_amt'];
							$igst_amt=$k['igst_amt'];
							if($sateno!=$this->Session->read('UserProfile.State.state_no'))
							{
						
								$cgst_amt=0;
								$sgst_amt=0;
							}else{
								$igst_amt=0;
							}
                        $this->PurchaseDetails->create();
                        $sDtail=array(
                        'purchase_id'=>$purchase_id,
                        'item_id'=>$k['item_id'],
                        'quantity'=>$value['qty'],                  
                        'total_amount'=>$k['total_amount'],
                        'hsn'=>$k['hsn'],
                        'gst_slab'=>$k['gst_rate'],
                        'cgst_amt'=>$cgst_amt,
                        'sgst_amt'=>$sgst_amt,
                        'igst_amt'=>$igst_amt,
                        'cgst_per'=>$cgs_per,
                        'sgst_per'=>$sgst_per,
                        'igst_per'=>$igst_per,
                        'unit'=>$value['unit'],
                        'gross_total'=>$k['sp']*$value['qty'],
                        'pp'=>$k['sp'],
                        'discount'=>$k['discount'],
                        'nill_rated'=>$nill,
                        'created_by'=>$this->Session->read('Auth.User.id'),
						'cess_type'=>$k['cess_type'],
						'cess_amount'=>$k['cess_amt'],
						'cess_name'=>$k['cess_name'],
                        
                        );
						// Srore All Discount
						 $discountArr[]=$k['discount'];
						 //end Srore All Discount
                        if($this->PurchaseDetails->save($sDtail)){
                            $purchase_detail_id = $this->PurchaseDetails->getInsertID();
                            $stock=$this->Stock->find('first',array(
                            'conditions'=>array(
                                    'Stock.item_id'=>$k['item_id'],
                                    'Stock.is_deleted'=>BOOL_FALSE,
                                    'Stock.is_active'=>BOOL_TRUE,
                                ),
                            'recursive'    => -1
                            ));
                           
							if(!empty($stock))
							{
								 $this->Stock->id = $stock["Stock"]["id"];
                            $this->Stock->saveField('quantity',($stock['Stock']['quantity']+$value['qty']));
							}else{
								$this->Stock->create();
								$this->request->data['Stock']['item_id']=$k['item_id'];
								$this->request->data['Stock']['quantity']=$value['qty'];
								$this->Stock->save($this->request->data['Stock']);
							}
                            
                            $psid = $purchase_detail_id;
                            $item_id=$k["item_id"];
                            $itemdata=$this->Item->findById($item_id);//Get category Id
                            $category_id=$itemdata["Item"]["category_id"];
                            $qty=$value['qty'];
                            $conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
                            $fields=array('Stock.quantity');
                            $stockData=$this->Stock->getallStock($conditions,$fields);
                            $stock=$stockData['Stock']['quantity'];
                            $price=$itemdata['Item']['price'] * $stock;
                            $location_id="";
                            $updateSelePurchaseController = new CommonsController;
                            $updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHASE,$category_id,$location_id,$qty,$stock,$price);
                        }
						}
                    }
						//Update Product Discount Amount
						$itemDis=array_sum($discountArr);                   
						$this->request->data['PurchaseUpdate']['id']=$purchase_id;
						$this->request->data['PurchaseUpdate']['product_discount']=$itemDis;
						$this->Purchase->save($this->request->data['PurchaseUpdate']);
						//End update Product Discount Amount
							
						// Save Purchase other Charges
					/*	if(!empty($this->request->data['Purchase']['other_charge_inc_amount']) or !empty($this->request->data['Purchase']['charge_inc_amount']))
						{
							$ledgerIDArr="";
							if(!empty($this->request->data['Purchase']['charge_inc_type']))
							{
								$ledgerIDArr=implode(',',$this->request->data['Purchase']['charge_inc_type']);
							}
						$this->PurchaseCharge->create();	
						$this->request->data['PurchaseCharge']['gst_type']=CONSIDER_GST;	
						$this->request->data['PurchaseCharge']['purchase_id']=$purchase_id;	
						$this->request->data['PurchaseCharge']['ledger_id']=$ledgerIDArr;	
						$this->request->data['PurchaseCharge']['ledger_amt']=$this->request->data['Purchase']['charge_inc_amount'];	
						$this->request->data['PurchaseCharge']['other_exp']=$this->request->data['Purchase']['other_charge_inc'];	
						$this->request->data['PurchaseCharge']['othe_amount']=$this->request->data['Purchase']['other_charge_inc_amount'];
						$this->PurchaseCharge->save($this->request->data['PurchaseCharge']);
						}
						if(!empty($this->request->data['Purchase']['charge_exc_amount']) or !empty($this->request->data['Purchase']['other_charge_exc_amount']))
						{
							$ledgerIDArr1="";
							if(!empty($this->request->data['Purchase']['charge_exc_type']))
							{
								$ledgerIDArr1=implode(',',$this->request->data['Purchase']['charge_exc_type']);
							}
						$this->request->data['PurchaseCharge1']['gst_type']=NONCONSIDER_GST;	
						$this->request->data['PurchaseCharge1']['purchase_id']=$purchase_id;		
						$this->request->data['PurchaseCharge1']['ledger_id']=$ledgerIDArr1;	
						$this->request->data['PurchaseCharge1']['ledger_amt']=$this->request->data['Purchase']['charge_exc_amount'];	
						$this->request->data['PurchaseCharge1']['other_exp']=$this->request->data['Purchase']['other_charge_exc'];	
						$this->request->data['PurchaseCharge1']['othe_amount']=$this->request->data['Purchase']['other_charge_exc_amount'];
						$this->PurchaseCharge->create();
						$this->PurchaseCharge->save($this->request->data['PurchaseCharge1']);
						}					*/		
						// End Save Purchase other Charges	
					
                    $this->Session->setFlash(' Purchase has been saved ', 'success');                
                    return $this->redirect(array('controller'=>'shops','action' => 'purchaseList','shop'=>true));
                
                }
			  
                else 
                {
                    $this->Session->setFlash(' Purchase could not be saved. Please, try again.', 'error');
                }
			  }else{
				  $this->Session->setFlash('Please select vendor.', 'error');
			  }
            }
			else
			{
				
				$this->request->data=$this->Purchase->find('first',array(
														'conditions'=>array('Purchase.id'=>$id),
														'contain'=>array('PurchaseDetail'=>array('Item'=>array('GstMaster'=>array('gst_percentage','sgst',	'cgst','igst')),'CessMaster'=>array('code')),'PurchaseCharge','PaymentTransaction'
																),
														'recursive'=>2
													));
				//echo "<pre>";print_r($this->request->data);exit;
			}
           
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
	/*
	kajal kurrewar 
	Purchase list
	*/
	public function shop_purchaseList() 
	{
		$cond=array();
		$this->shop_check_login();
		
		$this->loadModel('Purchase');

		$this->loadModel('PurchaseDetail');
		$this->loadModel('BankAccount');
		
		$this->loadModel('UserProfile');
		$this->loadModel('GstMaster');
		
		$UserProfile=$this->Session->read('UserProfile');
		
		$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
			
	/*	$banks=$this->BankAccount->getBankAccountList();	
		$this->set(compact('banks'));*/
		
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
					//$cond['OR']['Purchase.bill_no LIKE']=$this->request->data['Purchase']['name']."%";
					
					$cond['OR']['Ledger.name LIKE']=$this->request->data['Purchase']['name']."%";
					
					//$cond['OR']['DATE(Purchase.bill_date)']=date("Y-m-d",strtotime($this->request->data['Purchase']['name']));
				}
			}
			
			

			$conditions = array(
				'Purchase.id !=' => BOOL_FALSE,
				'Purchase.is_deleted' => BOOL_FALSE,
				'Purchase.is_active' => BOOL_TRUE,
				'Purchase.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),

			);

			$conditions=array_merge($conditions,$cond);

			$this->Paginator->settings = array(
				'Purchase' => array(
					'conditions' => $conditions,
					'order' => array('Purchase.id' => 'DESC'),					
					'limit' => PAGINATION_LIMIT_1,
					'recursive' =>2
			));
			
			$purchases = $this->Paginator->paginate('Purchase');
			$this->set(compact('purchases'));
			
		$this->set(compact('UserProfile'));
		
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }

	/*
	Kajal Kurrewar
	07.02.17
	Delete Purchase list
	*/
	
	public function shop_deletePurchaseList() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Purchase');
		$this->loadModel('Stock');
		$this->loadModel('PurchaseDetail');
		$this->loadModel('Item');
		$this->loadModel('Voucher');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Purchase->id =$id;
				if (!$this->Purchase->exists()) 
				{
					throw new NotFoundException('Invalid Purchase');
				}
				  if ($this->Purchase->saveField('is_deleted',BOOL_TRUE)) 
				   {
					    $this->Voucher->updateAll(array('Voucher.is_deleted' =>BOOL_TRUE,'Voucher.is_active'=>BOOL_FALSE),array('Voucher.referance_id' =>$id,'Voucher.reporting_type'=>REPORTING_PURCHASE));
						
					   $pddata=$this->PurchaseDetail->find('all',array('conditions'=>array('PurchaseDetail.purchase_id'=>$id,'PurchaseDetail.is_deleted'=>BOOL_FALSE)));
					   if(!empty($pddata))
					   {
						   foreach($pddata as $row)
						   {
							   $qty=$row['PurchaseDetail']['quantity'];
								$stockData1=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$row['PurchaseDetail']['item_id']),'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE));
								
								$this->request->data['Stock']['id']=$stockData1['Stock']['id'];
								$this->request->data['Stock']['quantity']=$stockData1['Stock']['quantity']-$qty;
								$this->Stock->save($this->request->data['Stock']);
								
								$psid = $id;
								$item_id=$row['PurchaseDetail']['item_id'];
								$itemdata=$this->Item->findById($item_id);//Get category Id
								$category_id=$itemdata["Item"]["category_id"];
								$location_id=$this->Auth->user("location_id");
								$qty=$qty;
								$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
								$fields=array('Stock.quantity');
								$stockData=$this->Stock->getallStock($conditions,$fields);
								$stock=$stockData['Stock']['quantity'];
								$price=$itemdata['Item']['price'] * $stock;
								
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHSE_DELETE,$category_id,$location_id,$qty,$stock,$price);
							
						   }
					   }
					
					echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Purchase  Deleted successfully'));
				   }else
				   {
					   echo json_encode(array('status'=>'1001','message'=>'Purchase  could not be Deleted'));
				   }
			
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/*kajal kurrewar
	  06-08-2017
	  Reset purchase list
	*/
	
	public function shop_resetPurchaseSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();
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
	  /*
		Kajal kurrewar
		Edit Vendor in add po
		07-08-2017
	  */
	public function shop_editVendor()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Distributor');
		$this->admin_check_login();				
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Distributor']['id'];
				if(!empty($id))
					{
						if ($this->Distributor->save($this->request->data['Distributor'])) 
						{
							$venId=$this->request->data['Distributor']['id'];						
							$name=$this->request->data['Distributor']['name'];						
							echo json_encode(array('status'=>'1000','message'=>'Vendor updated successfully','vendid'=>$venId,'name'=>$name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Vendor could not be updated'));
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
	09.09.17
	Get customer prevoius balance
	*/
	
	public function shop_getPreviousBalance() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Sale');
		$this->loadModel('Member');
		if ($this->request->is('ajax')) 
			{
				$mobile= $this->request->data['id'];
				$memberData=$this->Member->find('first',array('conditions'=>array('Member.contact_no'=>$mobile,'Member.is_deleted'=>BOOL_FALSE,'Member.is_active'=>BOOL_TRUE,'Member.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'))));
				if(!empty($memberData))
				{
					$conditions=array('Sale.customer_id'=>$memberData['Member']['id'],'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),'Sale.is_deleted'=>BOOL_FALSE,'Sale.is_active'=>BOOL_TRUE,'Sale.total_balance >'=>0);
					$fields=array('SUM(Sale.total_balance) as toatl_balance');
					$saleData=$this->Sale->find('first',array('conditions'=>$conditions,'fields'=>$fields));
					
					
					if($saleData[0]['toatl_balance'] >0)
					{
						echo json_encode(array('status'=>'1000','message'=>'Your previous balance is <b>'.$saleData[0]['toatl_balance'].'</b> &nbsp<a class="btn btn-success btn-xs" href="#" onclick="viewPrevoiusBalance('.$memberData['Member']['id'].')" style="margin-right:5px;">View Details</a>'));
					}else{
						echo json_encode(array('status'=>'1001'));
					}					
					
				}else{
					echo json_encode(array('status'=>'1001'));
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
	09.09.17
	Get customer prevoius balance Details
	*/
	
	public function shop_getPreviousBalanceDetails() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Sale');
		$this->loadModel('Member');
		if ($this->request->is('ajax')) 
			{
				$customer_id= $this->request->data['id'];
				$conditions=array('Sale.customer_id'=>$customer_id,'Sale.is_deleted'=>BOOL_FALSE,'Sale.is_active'=>BOOL_TRUE,'Sale.total_balance >'=>0,'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'));
					$fields=array('Sale.total_balance','Sale.id','Sale.total_amount','Sale.total_payment','Sale.sales_date','Sale.invoice_no');
					$saleData=$this->Sale->find('all',array('conditions'=>$conditions,'fields'=>$fields));
				if(!empty($saleData))
				{
					$table="";
					$totalArr=array();
					$payArr=array();
					$balArr=array();
					$i=0;
					foreach($saleData as $row)
					{
						$i++;
						$table.='<tr>
											<td><input type="checkbox" id="checkBoxPreAmtPaid_'.$row['Sale']['id'].'" name="selectArr['.$i.']" class="pre_amt_select"></td>
											<td><input type="hidden" value="'.$row['Sale']['id'].'" name="idArr['.$i.']">'.$row['Sale']['invoice_no'].'</td>
											<td>'.date('d-m-Y',strtotime($row['Sale']['sales_date'])).'</td>
											<td>'.$row['Sale']['total_amount'].'</td>
											<td>'.$row['Sale']['total_payment'].'</td>
											<td id="preBalanceAmt_'.$row['Sale']['id'].'">'.$row['Sale']['total_balance'].'</td>
											<td>
											<input type="hidden" class="form-control" name="balanceArr['.$i.']" value="'.$row['Sale']['total_balance'].'">
											<input type="hidden" class="form-control" name="paidArr['.$i.']" value="'.$row['Sale']['total_payment'].'">
											<input type="text" class="form-control" name="amountArr['.$i.']" id="prePayAmt_'.$row['Sale']['id'].'"></td>
									  </tr>';
									  $totalArr[]=$row['Sale']['total_amount'];
									  $payArr[]=$row['Sale']['total_payment'];
									  $balArr[]=$row['Sale']['total_balance'];
					}
						$table.='<tr>
											<td colspan="3" class="total_bg">Total</td>
											<td class="total_bg">'.array_sum($totalArr).'</td>
											<td class="total_bg">'.array_sum($payArr).'</td>
											<td class="total_bg">'.array_sum($balArr).'</td>
											<td class="total_bg" id="prePaynowtToatl"></td>
									  </tr>';						
						echo json_encode(array('status'=>'1001','table'=>$table));
									
					
				}else{
					echo json_encode(array('status'=>'1001'));
				}
			
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	public function shop_paidPreviousBalance() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Sale');
		$this->loadModel('PaymentTransaction');
		$this->loadModel('Voucher');
		if ($this->request->is('ajax')) 
			{
				
				$selectedArr=array();
				if(isset($this->request->data['selectArr']))
				{
				$selectedArr=$this->request->data['selectArr'];
				}
				$idArr=$this->request->data['idArr'];
				$balanceArr=$this->request->data['balanceArr'];
				$amountArr=$this->request->data['amountArr'];
				$paidArr=$this->request->data['paidArr'];
				$sucIdArr=array();
				$amt=0;
				if(!empty($selectedArr))
				{
					foreach($selectedArr as $k=>$v)
					{
						// get clear balance sale id
						if($balanceArr[$k]==$amountArr[$k])
						{
							$sucIdArr[]=$idArr[$k];
						}
						// end get clear balance sale id
						$this->request->data['Sale']['id']=$idArr[$k];
						$this->request->data['Sale']['total_balance']=$balanceArr[$k]-$amountArr[$k];
						$this->request->data['Sale']['total_payment']=$paidArr[$k]+$amountArr[$k];
						$this->Sale->save($this->request->data['Sale']);	
						//Update payment transction
						$this->PaymentTransaction->create();
						$this->request->data['PaymentTransaction']['payment']=$amountArr[$k];
						$this->request->data['PaymentTransaction']['reference_id']=$idArr[$k];
						$this->request->data['PaymentTransaction']['type']=SALE_PAYMENT;	
						if($this->request->data['PreviousPayment']['pre_pay_type']==PAYMENT_TYPE_CASH)					
						{					
							$this->request->data['PaymentTransaction']['payment_method']=PAYMENT_TYPE_CASH;							
							//$amt=$this->request->data['PreviousPayment']['pre_mode_cash'];							
						}
						elseif($this->request->data['PreviousPayment']['pre_pay_type']==PAYMENT_TYPE_CHEQUE)						
						{
							$this->request->data['PaymentTransaction']['payment_method']=PAYMENT_TYPE_CHEQUE;							
							$this->request->data['PaymentTransaction']['cheque_no']=$this->request->data['PreviousPayment']['pre_cheque_no'];							
							$this->request->data['PaymentTransaction']['cheque_date']=$this->request->data['PreviousPayment']['pre_cheque_date'];							
							$this->request->data['PaymentTransaction']['bank_name']=$this->request->data['PreviousPayment']['pre_cheque_bank_name'];	
							$amt=$this->request->data['PreviousPayment']['pre_mode_cheque'];
							//Create advance voucher
							//$this->request->data['Voucher']['cheque_no']=$this->request->data['PreviousPayment']['pre_cheque_no'];		
							//$this->request->data['Voucher']['cheque_date']=$this->request->data['PreviousPayment']['pre_cheque_date'];		
						}
						elseif($this->request->data['PreviousPayment']['pre_pay_type']==PAYMENT_TYPE_ONLINE)						
						{
							$this->request->data['PaymentTransaction']['payment_method']=PAYMENT_TYPE_ONLINE;							
							$this->request->data['PaymentTransaction']['card_no']=$this->request->data['PreviousPayment']['pre_bcard_no'];							
							$this->request->data['PaymentTransaction']['dr_bank']=$this->request->data['PreviousPayment']['pre_dr_bank'];
							$this->request->data['PaymentTransaction']['bank_name']=$this->request->data['PreviousPayment']['pre_card_bank_name'];	
															
						}
						$this->PaymentTransaction->save($this->request->data['PaymentTransaction']);	
					}
					/*if($this->request->data['PreviousPayment']['total_hidden']<$amt)
					{
						$this->Voucher->create();
						$adv=$amt-$this->request->data['PreviousPayment']['total_hidden'];
						$this->request->data['Voucher']['type']=ADVANCE;
						$this->request->data['Voucher']['date']=date('Y-m-d');
						$this->request->data['Voucher']['ledger_id']=$this->request->data['PreviousPayment']['cust_id'];
						$this->request->data['Voucher']['dr_type']=BOOL_FALSE;
						$this->request->data['Voucher']['total']=$adv;
						$this->Voucher->save($this->request->data['Voucher']);
					}*/
					echo json_encode(array('status'=>'1000','message'=>'Amount paid successfully','amount'=>array_sum($amountArr)));
				}else{
				echo json_encode(array('status'=>'1001','message'=>'Amount could not be paid successfully'));
				}
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }

	
	/*
	Neha Umredkar
	14/09/2017
	Add Balance Payment
	*/
	public function shop_addPayment() 
	{
		$cond=array();
			$this->shop_check_login();
		
		$this->layout = 'ajax';
		$this->autoRender = false;
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID))) 
		{
			$this->loadModel('Purchase');	
			$this->loadModel('PaymentTransaction');

			if($this->request->is('ajax')){
				//echo'<pre>';print_r($this->request->data);exit;
				if(!empty($this->request->data)){
											
					$purchase_id = $this->request->data["PaymentTransaction"]['reference_id'];
					$purchase_detail = $this->Purchase->find("first",array(
						'conditions'=>array(
							'Purchase.id !='=>BOOL_FALSE,
							'Purchase.id'=>$purchase_id,
							'Purchase.is_deleted'=>BOOL_FALSE,
						)
					));
					if(!empty($purchase_detail)){
						
						if($this->request->data["PaymentTransaction"]['payment'] > 0){
							$this->request->data["PaymentTransaction"]["type"]=PURCHASE_PAYMENT;
							if($this->PaymentTransaction->save($this->request->data['PaymentTransaction'])){

							$this->Purchase->id = $purchase_id;
							$payment= $purchase_detail['Purchase']['total_payment']+$this->request->data["PaymentTransaction"]['payment'];
							$this->request->data["Purchase"]['total_payment'] = $payment;
							 $balance= $purchase_detail['Purchase']['total_balance']-$this->request->data["PaymentTransaction"]['payment'];
							 $this->request->data["Purchase"]['total_balance'] =$balance;
							$this->request->data["Purchase"]['payment_status'] = BOOL_TRUE;
							$this->Purchase->save($this->request->data["Purchase"]);
							
													
							
							echo json_encode(array("status"=>200,"reference_id"=>$purchase_id,"payment"=>$payment,'balance'=>$balance,'msg'=>"Payment entred successfully"));
							}
							else{
								echo json_encode(array("status"=>201,"msg"=>"Payment entry failed."));
							}
						}
						else{
							echo json_encode(array("status"=>201,"msg"=>"Can not pay 0 amount."));
						}		
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
	
	/*
	Neha Umredkar
	14/09/2017
	Amount Balance details
	*/
		public function shop_balanceDetail() 
	{
		$this->layout = 'ajax';
		$this->autoRender = false;
		
		$cond=array();
		$this->shop_check_login();
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('Purchase');
			
			if($this->request->is('ajax')){
				
				if(!empty($this->request->data)){				
					
					$id = $this->request->data["purchase_id"];
					$this->Purchase->id = $id;
					if(!$this->Purchase->exists()){
						throw new NotFoundException("Invalid Purchase Id");
					}
					
					$purchase_detail = $this->Purchase->find("first",array(
						'conditions'=>array(
							'Purchase.id !='=>BOOL_FALSE,
							'Purchase.id'=>$id,
							'Purchase.is_deleted'=>BOOL_FALSE,
							
						)
					));
				//	echo'<pre>';print_r($purchase_detail);exit;
					if(!empty($purchase_detail)){
						$data = array("status"=>200,"content"=>$purchase_detail);
						echo json_encode($data);
					}
					else{
						$data = array("status"=>404,"content"=>"content not found");	
						echo json_encode($data);
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
	
		/*
	Amit Sahu
	30.01.17
	Item List
	*/
	public function shop_itemList() 
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
        $this->loadModel('CessMaster');
        $this->loadModel('GstMaster');
		
		$UserProfile=$this->Session->read('UserProfile');
		
		
	    $unitsList=$this->Unit->find('list',array(
				'conditions'=>array(
				'Unit.id !='=>BOOL_FALSE,
				'Unit.is_deleted'=>BOOL_FALSE,
				'Unit.is_active'=>BOOL_TRUE,
				)
				));			
		$this->set(compact('unitsList'));	
		
		$cess_list=$this->CessMaster->getCessMasterList();		
		$this->set(compact('cess_list'));	
		
		$gst_slab_list=$this->GstMaster->getGstSlabList($this->Session->read('Auth.User.user_profile_id'));		
		$this->set(compact('gst_slab_list'));	
		
		
		
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
	public function shop_resetItemSearch() 
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
	Amiy Sahu
	11.10.17
	consumption
	*/
	public function shop_consumption()
    {
       
        $this->shop_check_login();

        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {

            $this->loadModel('Item');
            $this->loadModel('Stock');
            $this->loadModel('SaleCharge');
            if($this->request->is('post')){
				
                $errqty=0;
                /*echo "<pre>";
				print_r($this->request->data);
                echo "</pre>";exit;*/
           foreach($this->request->data['SalesDetail'] as $k)
					{

					if($k['quantity']==0)
					{
					$errqty=1;
					}

					}                        
					if($errqty==1)
					{
						//echo "123";exit;
					$this->Session->setFlash('Invalid quentity please try again  ', 'error');                
					return $this->redirect(array('controller'=>'shops','action' => 'consumption','shop'=>true,'ext'=>URL_EXTENSION));
					exit;
					}
					$currentnewStock=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$this->request->data['Sale']['pro_item_id'],'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE),'fields'=>array('Stock.item_id','Stock.quantity','Stock.id')));
					if(!empty($currentnewStock))
						{
							$this->request->data['Stock1']['id']=$currentnewStock['Stock']['id'];
							$this->request->data['Stock1']['quantity']=$currentnewStock['Stock']['quantity']+$this->request->data['Sale']['pro_item_quantity'];
							
						}else{
							$this->Stock->create();
							$this->request->data['Stock1']['item_id']=$this->request->data['Sale']['pro_item_id'];
							$this->request->data['Stock1']['quantity']=$this->request->data['Sale']['pro_item_quantity'];
							
						}
					if($this->Stock->save($this->request->data['Stock1']))
					{
								
								$psid=$this->Stock->getInsertID();
								 $item_id=$this->request->data['Sale']['pro_item_id'];
								 $itemdata=$this->Item->findById($item_id);
								
								$category_id=$itemdata["Item"]["category_id"];
								$location_id=$this->Auth->user("location_id");
								$qty=$this->request->data['Sale']['pro_item_quantity'];
								$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
								$fields=array('Stock.quantity');
								$stockData=$this->Stock->getallStock($conditions,$fields);
								$stock=$stockData['Stock']['quantity'];
								$price=$itemdata['Item']['price'] * $stock;
								
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHASE,$category_id,$location_id,$qty,$stock,$price);
                   
                    foreach($this->request->data['SalesDetail'] as $k)
                    {
						$value=$this->Unitchange->change($k['item_id'],$k['quantity'], $k['unit']);
						$currentStock=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$k['item_id'],'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE),'fields'=>array('Stock.item_id','Stock.quantity','Stock.id')));
						if(!empty($currentStock))
						{
							$this->request->data['Stock']['id']=$currentStock['Stock']['id'];
							$this->request->data['Stock']['quantity']=$currentStock['Stock']['quantity']-$value['qty'];
							if($this->Stock->save($this->request->data['Stock']))
							{
								 $psid=$this->Stock->getInsertID();
								 $item_id=$k["item_id"];
								 $itemdata=$this->Item->findById($item_id);
								
								$category_id=$itemdata["Item"]["category_id"];
								$location_id=$this->Auth->user("location_id");
								$qty=$value['qty'];
								$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
								$fields=array('Stock.quantity');
								$stockData=$this->Stock->getallStock($conditions,$fields);
								$stock=$stockData['Stock']['quantity'];
								$price=$itemdata['Item']['price'] * $stock;
								
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,SALES,$category_id,$location_id,$qty,$stock,$price);
							}
						}
						
                    }
                    
				
                    $this->Session->setFlash('Production has been saved', 'success');                
                    return $this->redirect(array('controller'=>'shops','action' => 'consumption','shop'=>true,'ext'=>URL_EXTENSION));
					}
					 else 
                {
                    $this->Session->setFlash('The Consumption could not be saved. Please, try again.', 'error');
                }
                } else
			{
			   //echo "hii";
			}
               
            
        }
        else
        {
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	/*
	Gst GSTR One 
	16.02.17
	Amit Sahu
	*/
		public function shop_gstGstrOne() 
	{
		$this->autoRender = FALSE;
		
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {

            $this->loadModel('SalesDetail');
            $this->loadModel('CreditNoteDetail');
            $this->loadModel('AdvanceDetail');
            $this->loadModel('AdvanceAdjustment');
            if($this->request->is('post')){
				// App::import('Vendor', 'excel/PHPExcel.php');
				  App::import('Vendor', 'excel', array(
            'file' => 'excel' . DS . 'PHPExcel.php'
        ));

				// Create new PHPExcel object
				$objPHPExcel = new PHPExcel();

				// Set document properties
				// Comment by amit
				/*$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
				->setLastModifiedBy("Maarten Balliauw")
				->setTitle("Office 2007 XLSX Test Document")
				->setSubject("Office 2007 XLSX Test Document")
				->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
				->setKeywords("office 2007 openxml php")
				->setCategory("Test result file");
				*/

				// Add some data

				//First sheet
				$sheet = $objPHPExcel->getActiveSheet();

				//Start adding next sheets
				
				$i=0;
				while ($i < 11) {

				// Add new sheet
				
				$objWorkSheet = $objPHPExcel->createSheet($i); //Setting indexindex when creating

				$i++;
				//Write cells
				//*****************************Get Data************************************************************//
				//B2B List
				if($i==1)
				{
					
				$conditions=array(			
				'Sale.customer_gstin !='=>'',
				'SalesDetail.is_active'=>BOOL_TRUE,
				'SalesDetail.is_deleted'=>BOOL_FALSE,
				'Sale.is_deleted'=>BOOL_FALSE,
				'Sale.is_active'=>BOOL_TRUE,	
				'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']
				);			
	
				$fields=array('SalesDetail.id','SalesDetail.gst_slab', 'sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
			
				$contain=array(
				'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
				
				);
				
				$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain));
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'GSTIN/UNI')
				->setCellValue('B1', 'Invoice Id')
				->setCellValue('C1', 'Invoice Date')
				->setCellValue('D1', 'Invoice Value')
				->setCellValue('E1', 'Place of Supply')
				->setCellValue('F1', 'Reverse Charge')
				->setCellValue('G1', 'Invoice Type')
				->setCellValue('H1', 'E-Commerce')
				->setCellValue('I1', 'GST Rate')
				->setCellValue('J1', 'Taxable Value')
				->setCellValue('K1', 'Cess Amt');
				
				if(!empty($salesdetaillist))
				{
					$no=1;
					foreach($salesdetaillist as $row)
					{
						$no++;
						if($row['Sale']['inclusive'] == 0){ $inc= 'N';}else{ $inc= 'Y';}
						$objWorkSheet->setCellValue('A'.$no,$row['Sale']['customer_gstin'])
						->setCellValue('B'.$no,$row['Sale']['invoice_no'])
						->setCellValue('C'.$no, $row['Sale']['sales_date'])
						->setCellValue('D'.$no, $row['Sale']['total_amount'])
						->setCellValue('E'.$no,!empty($row['Sale']['state'])?$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name']:'')
						->setCellValue('F'.$no,$inc)
						->setCellValue('G'.$no, 'Regular')
						->setCellValue('H'.$no, '--')
						->setCellValue('I'.$no, $row['SalesDetail']['gst_slab'])
						->setCellValue('J'.$no, $row[0]['taxable'])
						->setCellValue('K'.$no, '--');
						
					}
				}
				

				// Rename sheet
				$objWorkSheet->setTitle("B2B");
				}
				//B2CL List
				if($i==2)
				{
			$conditions=array(			
			'Sale.customer_gstin'=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,
			'Sale.state !='=>$this->Session->read('UserProfile.State.id'),
			'Sale.total_amount >='=>250000,
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
			'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
			'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']			
			);				
	
			$fields=array('SalesDetail.id','SalesDetail.gst_slab','sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
			$contain=array(
			'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
			);
			
			$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain));
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'Invoice Id')
				->setCellValue('B1', 'Invoice Date')
				->setCellValue('C1', 'Invoice Value')
				->setCellValue('D1', 'Place of Supply')
				->setCellValue('E1', 'GST Rate')
				->setCellValue('F1', 'Taxable Value')
				->setCellValue('G1', 'Cess Amt')
				->setCellValue('H1', 'E-Commerce');
			
				
				if(!empty($salesdetaillist))
				{
					$no=1;
					foreach($salesdetaillist as $row)
					{
						$no++;
						if($row['Sale']['State']['state_no'] != 27  && $row['Sale']['total_amount'] > 250000)
						{		
						$objWorkSheet->setCellValue('A'.$no,$row['Sale']['invoice_no'])
						->setCellValue('B'.$no, $row['Sale']['sales_date'])
						->setCellValue('C'.$no, $row['Sale']['total_amount'])
						->setCellValue('D'.$no,!empty($row['Sale']['state'])?$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name']:'')
						->setCellValue('E'.$no, $row['SalesDetail']['gst_slab'])
						->setCellValue('F'.$no, $row[0]['taxable'])
						->setCellValue('G'.$no, '--')
						->setCellValue('H'.$no, '--');
						}
						elseif($row['Sale']['State']['state_no'] == 27)
						{		
						$objWorkSheet->setCellValue('A'.$no,$row['Sale']['invoice_no'])
						->setCellValue('B'.$no, $row['Sale']['sales_date'])
						->setCellValue('C'.$no, $row['Sale']['total_amount'])
						->setCellValue('D'.$no,!empty($row['Sale']['state'])?$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name']:'')
						->setCellValue('E'.$no, $row['SalesDetail']['gst_slab'])
						->setCellValue('F'.$no, $row[0]['taxable'])
						->setCellValue('G'.$no, '--')
						->setCellValue('H'.$no, '--');
						}
					}
				}
				

				// Rename sheet
				$objWorkSheet->setTitle("B2CL");
				}
				
				
				///////////B2SC//////////////////////**********************
				if($i==3)
				{
				$conditions=array(			
				'Sale.customer_gstin'=>'',
				'SalesDetail.is_active'=>BOOL_TRUE,
				'SalesDetail.is_deleted'=>BOOL_FALSE,	
				'Sale.is_deleted'=>BOOL_FALSE,
				'Sale.is_active'=>BOOL_TRUE,	
				'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month'],
				'OR'=>array(array(                   
					   'Sale.state !='=>$this->Session->read('UserProfile.State.id'),
						'Sale.total_amount <'=>250000,	
						),
					array(
						'Sale.state '=>$this->Session->read('UserProfile.State.id'),	
						))			
				);			
	
				$fields=array('SalesDetail.id','SalesDetail.gst_slab','sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
				$contain=array(
				'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
				);
				
				$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain));
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'Invoice Id')
				->setCellValue('B1', 'Invoice Date')
				->setCellValue('C1', 'Invoice Value')
				->setCellValue('D1', 'Place of Supply')
				->setCellValue('E1', 'GST Rate')
				->setCellValue('F1', 'Taxable Value')
				->setCellValue('G1', 'Cess Amt')
				->setCellValue('H1', 'E-Commerce');
				
				if(!empty($salesdetaillist))
				{
					$no=1;
					foreach($salesdetaillist as $row)
					{
						$no++;
						if($row['Sale']['State']['state_no'] != 27  && $row['Sale']['total_amount'] < 250000)
						{		
						$objWorkSheet->setCellValue('A'.$no,$row['Sale']['invoice_no'])
						->setCellValue('B'.$no, $row['Sale']['sales_date'])
						->setCellValue('C'.$no, $row['Sale']['total_amount'])
						->setCellValue('D'.$no,!empty($row['Sale']['state'])?$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name']:'')
						->setCellValue('E'.$no, $row['SalesDetail']['gst_slab'])
						->setCellValue('F'.$no, $row[0]['taxable'])
						->setCellValue('G'.$no, '--')
						->setCellValue('H'.$no, '--');
						}
						elseif($row['Sale']['State']['state_no'] == 27)
						{		
						$objWorkSheet->setCellValue('A'.$no,$row['Sale']['invoice_no'])
						->setCellValue('B'.$no, $row['Sale']['sales_date'])
						->setCellValue('C'.$no, $row['Sale']['total_amount'])
						->setCellValue('D'.$no,!empty($row['Sale']['state'])?$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name']:'')
						->setCellValue('E'.$no, $row['SalesDetail']['gst_slab'])
						->setCellValue('F'.$no, $row[0]['taxable'])
						->setCellValue('G'.$no, '--')
						->setCellValue('H'.$no, '--');
						}
						
					}
				}
				

				// Rename sheet
				$objWorkSheet->setTitle("B2CS");
				}
				///////////B2SC//////////////////////**********************
				if($i==4)
				{
				
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'GSTIN / UIN of Recipient')
				->setCellValue('B1', 'Invoice / Advance Receipt Number')
				->setCellValue('C1', 'Invoice / Advance Receipt date')
				->setCellValue('D1', 'Note / Refund Voucher Number')
				->setCellValue('E1', 'Note / Refund Voucher date')
				->setCellValue('F1', 'Document Type')
				->setCellValue('G1', 'Reason For Issuing document')
				->setCellValue('H1', 'Place Of Supply')
				->setCellValue('I1', 'Note / Refund Voucher Value')
				->setCellValue('J1', 'Rate')
				->setCellValue('K1', 'Taxable Value')
				->setCellValue('L1', 'Cess Amount')
				->setCellValue('M1', 'Pre GST');
				
				$conditions=array(			
				'CreditNote.customer_gstin !='=>'',
				'CreditNoteDetail.is_active'=>BOOL_TRUE,
				'CreditNoteDetail.is_deleted'=>BOOL_FALSE,	
				'CreditNote.is_deleted'=>BOOL_FALSE,
				'CreditNote.is_active'=>BOOL_TRUE,	
				'CreditNote.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(CreditNote.date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(CreditNote.date)'=>$this->request->data['Gstr1']['month'],
						
				);
				$fields=array('CreditNoteDetail.total_amount','CreditNoteDetail.inv_no','CreditNoteDetail.gst_slab','SUM(CreditNoteDetail.total_amount) as tax_value');
				$contain=array('CreditNote'=>array('date','id','total_amount','customer_gstin','State'=>array('name','state_no')),'Sale'=>array('id','sales_date','invoice_no'));
				$sdnrList=$this->CreditNoteDetail->find('all',array('order'=>array('CreditNoteDetail.id asc'),'group'=>array('CreditNoteDetail.gst_slab','CreditNoteDetail.credit_note_id','CreditNoteDetail.inv_no'),'fields'=>$fields,'conditions'=>$conditions,'contain'=>$contain,'recursive' => 2));
				
				if(!empty($sdnrList))
				{
					$no=1;
					foreach($sdnrList as $row)
					{
						$no++;
								
						$objWorkSheet->setCellValue('A'.$no,$row['CreditNote']['customer_gstin'])
						->setCellValue('B'.$no, $row['Sale']['invoice_no'])
						->setCellValue('C'.$no, date('d-m-Y', strtotime($row['Sale']['sales_date'])))
						->setCellValue('D'.$no,$row['CreditNote']['id'])
						->setCellValue('E'.$no, date('d-m-Y', strtotime($row['CreditNote']['date'])))
						->setCellValue('F'.$no,'--')
						->setCellValue('G'.$no,'--')
						->setCellValue('H'.$no,!empty($row['CreditNote']['state'])?$row['CreditNote']['State']['state_no'].'-'.$row['CreditNote']['State']['name']:'')
						->setCellValue('I'.$no,$row['CreditNote']['total_amount'])
						->setCellValue('J'.$no,$row['CreditNoteDetail']['gst_slab'])
						->setCellValue('K'.$no, $row[0]['tax_value'])
						->setCellValue('L'.$no, '--')
						->setCellValue('M'.$no, '--');
						
						
					}
				}
					
				// Rename sheet
				$objWorkSheet->setTitle("CDNR");
				}
				if($i==5)
				{				
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'UR Type')
				->setCellValue('B1', 'Note / Refund Voucher Number')
				->setCellValue('C1', 'Note / Refund Voucher date')
				->setCellValue('D1', 'Note / Document Type')
				->setCellValue('E1', 'Invoice / Advance Receipt Number')
				->setCellValue('F1', 'Invoice / Advance Receipt date')
				->setCellValue('G1', 'Reason For Issuing document')
				->setCellValue('H1', 'Place Of Supply')
				->setCellValue('I1', 'Note / Refund Voucher Value')
				->setCellValue('J1', 'Rate')
				->setCellValue('K1', 'Taxable Value')
				->setCellValue('L1', 'Cess Amount')
				->setCellValue('M1', 'Pre GST');
				$conditions=array(			
				'CreditNote.customer_gstin'=>'',
				'CreditNoteDetail.is_active'=>BOOL_TRUE,
				'CreditNoteDetail.is_deleted'=>BOOL_FALSE,	
				'CreditNote.is_deleted'=>BOOL_FALSE,
				'CreditNote.is_active'=>BOOL_TRUE,	
				'CreditNote.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(CreditNote.date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(CreditNote.date)'=>$this->request->data['Gstr1']['month'],
						
				);
				$fields=array('CreditNoteDetail.total_amount','CreditNoteDetail.inv_no','CreditNoteDetail.gst_slab','SUM(CreditNoteDetail.total_amount) as tax_value');
				$contain=array('CreditNote'=>array('date','id','total_amount','customer_gstin','State'=>array('name','state_no')),'Sale'=>array('id','sales_date','invoice_no'));
				$sdnurList=$this->CreditNoteDetail->find('all',array('order'=>array('CreditNoteDetail.id asc'),'group'=>array('CreditNoteDetail.gst_slab','CreditNoteDetail.credit_note_id','CreditNoteDetail.inv_no'),'fields'=>$fields,'conditions'=>$conditions,'contain'=>$contain,'recursive' => 2));
				
				if(!empty($sdnurList))
				{
					$no=1;
					foreach($sdnurList as $row)
					{
						$no++;
								
						$objWorkSheet->setCellValue('A'.$no,$row['CreditNote']['customer_gstin'])
						->setCellValue('B'.$no, $row['Sale']['invoice_no'])
						->setCellValue('C'.$no, date('d-m-Y', strtotime($row['Sale']['sales_date'])))
						->setCellValue('D'.$no,$row['CreditNote']['id'])
						->setCellValue('E'.$no, date('d-m-Y', strtotime($row['CreditNote']['date'])))
						->setCellValue('F'.$no,'--')
						->setCellValue('G'.$no,'--')
						->setCellValue('H'.$no,!empty($row['CreditNote']['state'])?$row['CreditNote']['State']['state_no'].'-'.$row['CreditNote']['State']['name']:'')
						->setCellValue('I'.$no,$row['CreditNote']['total_amount'])
						->setCellValue('J'.$no,$row['CreditNoteDetail']['gst_slab'])
						->setCellValue('K'.$no, $row[0]['tax_value'])
						->setCellValue('L'.$no, '--')
						->setCellValue('M'.$no, '--');
						
						
					}
				}
				// Rename sheet
				$objWorkSheet->setTitle("CDNUR");
				}
				if($i==6)
				{				
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'Export Type')
				->setCellValue('B1', 'Invoice Number')
				->setCellValue('C1', 'Invoice Date')
				->setCellValue('D1', 'Invoice Value')
				->setCellValue('E1', 'Port Code')
				->setCellValue('F1', 'Shiping Bill Number')
				->setCellValue('G1', 'Shiping Bill Date')
				->setCellValue('H1', 'Rate')
				->setCellValue('I1', 'Taxable Value');
			
				
				// Rename sheet
				$objWorkSheet->setTitle("EXPORT");
				}
				if($i==7)
				{				
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'Place Of Supply')
				->setCellValue('B1', 'Rate')
				->setCellValue('C1', 'Advance Received')
				->setCellValue('D1', 'Cess Amount');		
				
				$conditions=array(			
				'AdvanceDetail.is_active'=>BOOL_TRUE,
				'AdvanceDetail.is_deleted'=>BOOL_FALSE,	
				'Advance.is_deleted'=>BOOL_FALSE,
				'Advance.is_active'=>BOOL_TRUE,	
				'Advance.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Advance.date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Advance.date)'=>$this->request->data['Gstr1']['month'],
						
				);
				$fields=array('SUM(AdvanceDetail.amount) as adv_amt','AdvanceDetail.gst_rate');
				$contain=array('Advance'=>array('member_id','member_state_id','State'=>array('name','state_no')));
				$advanceData=$this->AdvanceDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'recursive'=>2,'group'=>array('Advance.member_state_id','AdvanceDetail.gst_rate')));
				if(!empty($advanceData))
				{
					$no=1;
					foreach($advanceData as $row)
					{
						$no++;
						$adjCond=array(
						'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
						'Sale.state'=>$row['Advance']['member_state_id'],
						'YEAR(Advance.date)'=>$this->request->data['Gstr1']['year'],	
						'MONTH(Advance.date)'=>$this->request->data['Gstr1']['month'],
						'AdvanceAdjustment.gst_rate'=>$row['AdvanceDetail']['gst_rate'],
						);
						$fields=('SUM(AdvanceAdjustment.amount) as currnt_adj_amt');
						$advAdjData=$this->AdvanceAdjustment->find('first',array('conditions'=>$adjCond,'fields'=>$fields));	
						$adj_amt=0;
						if(!empty($advAdjData))
						{
							$adj_amt=$advAdjData[0]['currnt_adj_amt'];
						}							
						
						$objWorkSheet->setCellValue('A'.$no,!empty($row['Advance']['member_state_id'])?$row['Advance']['State']['state_no'].'-'.$row['Advance']['State']['name']:'')
						->setCellValue('B'.$no, $row['AdvanceDetail']['gst_rate'])
						->setCellValue('C'.$no,$row[0]['adv_amt']-$adj_amt)						
						->setCellValue('D'.$no, '--');
						
					}
				}
				// Rename sheet
				$objWorkSheet->setTitle("Advance Received ");
				}
				if($i==8)
				{				
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'Place Of Supply')
				->setCellValue('B1', 'Rate')
				->setCellValue('C1', 'Gross Advance Adjusted')
				->setCellValue('D1', 'Cess Amount');		
				$conditions=array(			
				'AdvanceAdjustment.is_active'=>BOOL_TRUE,
				'AdvanceAdjustment.is_deleted'=>BOOL_FALSE,	
				'Sale.is_deleted'=>BOOL_FALSE,
				'Sale.is_active'=>BOOL_TRUE,	
				'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month'],
						
				);
				$fields=array('SUM(AdvanceAdjustment.amount) as adj_amt','AdvanceAdjustment.gst_rate');
				$contain=array('Sale'=>array('customer_id','state','State'=>array('name','state_no')));
				$advanceAdjData=$this->AdvanceAdjustment->find('all',array('conditions'=>$conditions,'fields'=>$fields,'contain'=>$contain,'recursive'=>2,'group'=>array('Sale.state','AdvanceAdjustment.gst_rate')));
				
				if(!empty($advanceAdjData))
				{
					$no=1;
					foreach($advanceAdjData as $row)
					{
						$no++;
						$adjCond=array(
						'Advance.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
						'Advance.member_state_id'=>$row['Sale']['state'],
						'YEAR(Advance.date)'=>$this->request->data['Gstr1']['year'],	
						'MONTH(Advance.date)'=>$this->request->data['Gstr1']['month'],
						'AdvanceDetail.gst_rate'=>$row['AdvanceAdjustment']['gst_rate'],
						);
						$fields=('SUM(AdvanceDetail.amount) as currnt_adj_amt');
						$advAdjData=$this->AdvanceDetail->find('first',array('conditions'=>$adjCond,'fields'=>$fields));	
						
						$adv_amt=0;
						if(!empty($advAdjData))
						{
							//$adv_amt=$advAdjData[0]['currnt_adj_amt'];
							if($row[0]['adj_amt']>$advAdjData[0]['currnt_adj_amt'])
							{
								$adv_amt=$row[0]['adj_amt']-$advAdjData[0]['currnt_adj_amt'];
							}else{
								$adv_amt=0;
							}
						}							
						if(!empty($adv_amt))
						{
						$objWorkSheet->setCellValue('A'.$no,!empty($row['Sale']['state'])?$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name']:'')
						->setCellValue('B'.$no, $row['AdvanceAdjustment']['gst_rate'])
						->setCellValue('C'.$no,$adv_amt)						
						->setCellValue('D'.$no, '--');
						}
						
					}
				}
				// Rename sheet
				$objWorkSheet->setTitle("Advance Adjusted ");
				}
				///////////////////////////HSN NUMMARY/////////////////********************
				if($i==9)
				{				
				
				//setCellValue('A1', 'Hello'.$i)
				$conditions=array(			
				'Sale.id !='=>BOOL_FALSE,
				'Sale.is_deleted'=>BOOL_FALSE,
				'Sale.is_active'=>BOOL_TRUE	,
				'SalesDetail.is_active'=>BOOL_TRUE,
				'SalesDetail.is_deleted'=>BOOL_FALSE,
				'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']				
				);	
				$fields=array('DISTINCT  SalesDetail.item_id','SalesDetail.id','SUM(`SalesDetail`.`quantity`) as `qty`','SUM(`SalesDetail`.`total_amount`) as `taxableamt`','SUM(CASE WHEN Sale.gst_type = '.IGST.' THEN SalesDetail.gst_amt ELSE 0 END) AS igst_amt','SUM(CASE WHEN Sale.gst_type = '.CGST_SGST.' THEN SalesDetail.gst_amt ELSE 0 END) AS csgst_amt');
				$group='SalesDetail.item_id';
				$contain=array(
				'Sale'=>array('gst_type','total_amount'),'Item'=>array('name','hsn','Unit'=>array('code','name')),
				
				);
				$salesdetail=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'conditions'=>$conditions,'group'=>$group,'fields'=>$fields,'contain'=>$contain,'recursive' => 2,'contain'=>$contain));
			
				$objWorkSheet->setCellValue('A1', 'HSN')
				->setCellValue('B1', 'Description')
				->setCellValue('C1', 'UQC')
				->setCellValue('D1', 'Total Quantity')		
				->setCellValue('E1', 'Total Value')		
				->setCellValue('F1', 'Taxable Value')		
				->setCellValue('G1', 'IGST Amt')		
				->setCellValue('H1', 'CGST Amt')		
				->setCellValue('I1', 'SGST/UT Amt')		
				->setCellValue('J1', 'Cess Amt');		
				
				if(!empty($salesdetail))
				{
					$no=1;
					foreach($salesdetail as $row)
					{
						$no++;
				
						$objWorkSheet->setCellValue('A'.$no,$row['Item']['hsn'])
						->setCellValue('B'.$no,$row['Item']['name'])
						->setCellValue('C'.$no, !empty($row['Item']['unit'])?$row['Item']['Unit']['code'].'-'.$row['Item']['Unit']['name']:'')
						->setCellValue('D'.$no, $row[0]['qty'])
						->setCellValue('E'.$no,$row[0]['taxableamt']+$row[0]['igst_amt']+$row[0]['csgst_amt'])
						->setCellValue('F'.$no,$row[0]['taxableamt'])
						->setCellValue('G'.$no, $row[0]['igst_amt'])
						->setCellValue('H'.$no, $row[0]['csgst_amt']/2)
						->setCellValue('I'.$no, $row[0]['csgst_amt']/2)
						->setCellValue('J'.$no, '--');
						
					}
				}
				
				// Rename sheet
				$objWorkSheet->setTitle("HSN SUMMARY ");
				}
				///////////////////////////nill rated/////////////////********************
				if($i==10)
				{				
					$conditions=array(			
			'Sale.customer_gstin !='=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Item.gst_slab'=>BOOL_FALSE,
			'Item.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),			
			'Sale.state !='=>$this->Session->read('UserProfile.UserProfile.state'),
			'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']	
			);	
			
	
			$fields=array('SUM(CASE WHEN SalesDetail.nill_rated = '.NILL_RATED.' THEN SalesDetail.total_amount ELSE 0 END) AS inter_nill_amt_reg','SUM(CASE WHEN SalesDetail.nill_rated = '.EXAMPTED.' THEN SalesDetail.total_amount ELSE 0 END) AS inter_exam_amt_reg');		
					
			$regInter=$this->SalesDetail->find('first',array('fields'=>$fields,'conditions'=>$conditions,'recursive' => 2));
			
			
			// end Inter Register Value
			// Intra Register Value
			$conditions=array(			
			'Sale.customer_gstin !='=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Item.gst_slab'=>BOOL_FALSE,	
			'Sale.state '=>$this->Session->read('UserProfile.UserProfile.state'),	
			'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']
			);	
			
		
			$fields=array('SUM(CASE WHEN SalesDetail.nill_rated = '.NILL_RATED.' THEN SalesDetail.total_amount ELSE 0 END) AS intra_nill_amt_reg','SUM(CASE WHEN SalesDetail.nill_rated = '.EXAMPTED.' THEN SalesDetail.total_amount ELSE 0 END) AS intra_exam_amt_reg');		
			$contain=array();			
			$regIntra=$this->SalesDetail->find('first',array('fields'=>$fields,'conditions'=>$conditions,'recursive' => 2));
			// end Intra Register Value
			// Inter Unregister Value
			$conditions=array(			
			'Sale.customer_gstin '=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Item.gst_slab'=>BOOL_FALSE,	
			'Sale.state !='=>$this->Session->read('UserProfile.UserProfile.state'),
'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']			
			);	
			
	
			$fields=array('SUM(CASE WHEN SalesDetail.nill_rated = '.NILL_RATED.' THEN SalesDetail.total_amount ELSE 0 END) AS inter_nill_amt_unreg','SUM(CASE WHEN SalesDetail.nill_rated = '.EXAMPTED.' THEN SalesDetail.total_amount ELSE 0 END) AS inter_exam_amt_unreg');		
			$contain=array();			
			$unregInter=$this->SalesDetail->find('first',array('fields'=>$fields,'conditions'=>$conditions,'recursive' => 2));
			// end Inter Unregister Value
			// Intra Unregister Value
			$conditions=array(			
			'Sale.customer_gstin '=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Item.gst_slab'=>BOOL_FALSE,	
			'Sale.state '=>$this->Session->read('UserProfile.UserProfile.state'),	
			'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']
			);	
			
			
			$fields=array('SUM(CASE WHEN SalesDetail.nill_rated = '.NILL_RATED.' THEN SalesDetail.total_amount ELSE 0 END) AS intra_nill_amt_unreg','SUM(CASE WHEN SalesDetail.nill_rated = '.EXAMPTED.' THEN SalesDetail.total_amount ELSE 0 END) AS intra_exam_amt_unreg');		
			$contain=array();			
			$unregIntra=$this->SalesDetail->find('first',array('fields'=>$fields,'conditions'=>$conditions,'recursive' => 2));
			// end Intra Unregister Value
			
			// Arry merge
			$nillrated['inter_reg']['name']='Inter-State supplies to registered person';
			$nillrated['inter_reg']['nill']=$regInter[0]['inter_nill_amt_reg'];
			$nillrated['inter_reg']['exam']=$regInter[0]['inter_exam_amt_reg'];
			
			$nillrated['intra_reg']['name']='Intra-State supplies to registered person';
			$nillrated['intra_reg']['nill']=$regIntra[0]['intra_nill_amt_reg'];
			$nillrated['intra_reg']['exam']=$regIntra[0]['intra_exam_amt_reg'];
			
			$nillrated['inter_unreg']['name']='Inter-State supplies to unregistered person';
			$nillrated['inter_unreg']['nill']=$unregInter[0]['inter_nill_amt_unreg'];
			$nillrated['inter_unreg']['exam']=$unregInter[0]['inter_exam_amt_unreg'];
			
			$nillrated['intra_unreg']['name']='Intra-State supplies to unregistered person';
			$nillrated['intra_unreg']['nill']=$unregIntra[0]['intra_nill_amt_unreg'];
			$nillrated['intra_unreg']['exam']=$unregIntra[0]['intra_exam_amt_unreg'];
			
				$objWorkSheet->setCellValue('A1', 'Description')
				->setCellValue('B1', 'Nil Rated Supplies')
				->setCellValue('C1', 'Exempted ( other than nil rated/non GST Supply )')
				->setCellValue('D1', 'Non Gst supplies');		
				
				
			
				
				$objWorkSheet->setCellValue('A2',$nillrated['inter_reg']['name'])
				->setCellValue('B2',!empty($nillrated['inter_reg']['nill'])?$nillrated['inter_reg']['nill']:'0.00')
				->setCellValue('C2', !empty($nillrated['inter_reg']['exam'])?$nillrated['inter_reg']['exam']:'0.00')
				->setCellValue('D2','0.00');
				
				$objWorkSheet->setCellValue('A3',$nillrated['intra_reg']['name'])
				->setCellValue('B3',!empty($nillrated['intra_reg']['nill'])?$nillrated['intra_reg']['nill']:'0.00')
				->setCellValue('C3', !empty($nillrated['intra_reg']['exam'])?$nillrated['intra_reg']['exam']:'0.00')
				->setCellValue('D3','0.00');
				
				$objWorkSheet->setCellValue('A4',$nillrated['inter_unreg']['name'])
				->setCellValue('B4', !empty($nillrated['inter_unreg']['nill'])?$nillrated['inter_unreg']['nill']:'0.00')
				->setCellValue('C4', !empty($nillrated['inter_unreg']['exam'])?$nillrated['inter_unreg']['exam']:'0.00')
				->setCellValue('D4','0.00');
				
				$objWorkSheet->setCellValue('A5',$nillrated['intra_unreg']['name'])
				->setCellValue('B5', !empty($nillrated['intra_unreg']['nill'])?$nillrated['intra_unreg']['nill']:'0.00')
				->setCellValue('C5', !empty($nillrated['intra_unreg']['exam'])?$nillrated['intra_unreg']['exam']:'0.00')
				->setCellValue('D5','0.00');
						
				
				// Rename sheet
				$objWorkSheet->setTitle(" Report_Nil rated");
				}
				if($i==11)
				{				
				
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'Nature Of Document')
				->setCellValue('B1', 'Sr. No. From')
				->setCellValue('C1', 'Sr. No. To')
				->setCellValue('D1', 'Total Number')		
				->setCellValue('E1', 'Cancelled');		
				
				// Rename sheet
				$objWorkSheet->setTitle("Document");
				}
				
				}
				
				// Redirect output to a client’s web browser (Excel5)
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="01simple.xls"');
				header('Cache-Control: max-age=0');
				// If you're serving to IE 9, then the following may be needed
				header('Cache-Control: max-age=1');

				// If you're serving to IE over SSL, then the following may be needed
				header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
				header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				header ('Pragma: public'); // HTTP/1.0

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				    return $this->redirect($this->referer());
				//exit;	
		
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
	21.11.17
	Update Invoice list
	*/
	
	public function shop_updateInvoiceNo() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		$this->loadModel('Stock');
		$this->loadModel('Item');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$new_inv= $this->request->data['new_inv'];
				$invData=$this->Sale->find('all',array('conditions'=>array('Sale.invoice_no'=>$new_inv,'Sale.is_deleted'=>BOOL_FALSE,'Sale.is_active'=>BOOL_TRUE,'Sale.user_profile_id '=>$this->Session->read('UserProfile.UserProfile.id'),'Sale.id !='=>$id)));
				if(empty($invData))
				{
					$this->Sale->id =$id;
					if (!$this->Sale->exists()) 
					{
						throw new NotFoundException('Invalid Sale');
					}
					
						
							   if ($this->Sale->saveField('invoice_no',$new_inv)) 
							   {
								
								
								 
								echo json_encode(array('status'=>'1000','id'=>$id,'new_inv'=>$new_inv,'message'=>'Sale Invoice Updated successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Sale Invoice could not be Updated'));
							   }
				}else{
					echo json_encode(array('status'=>'1002','message'=>'Sale Invoice could not be Updated'));
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
	Add Credit Note 
	22.12.17
	*/
	public function shop_addCreditNote()
    {
		
		$cond=array();
        $this->shop_check_login();

        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {
            $this->loadModel('CreditNote');
            $this->loadModel('CreditNoteDetail');
            $this->loadModel('Item');
            $this->loadModel('Stock');
            $this->loadModel('PaymentTransaction');
			$this->loadModel('BankAccount');
       
            $this->loadModel('Member');
            $this->loadModel('State');
            $this->loadModel('City');
            $this->loadModel('Ledger');
            $this->loadModel('GstMaster');
            $this->loadModel('Voucher');

			$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
			$banks=$distList=$this->Ledger->getLedgerListByGroup(GROUP_BANK_ACCOUNT_ID,$this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('banks'));	

			$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
			
         
			
            if($this->request->is('post')){
				
                $errqty=0;
                
                
           foreach($this->request->data['SalesDetail'] as $k)
                    {
                        
                        if($k['quantity']==0)
                        {
                            $errqty=1;
                        }
                        
                    }                        
                if($errqty==1)
                {
                    $this->Session->setFlash('Invalid quentity please try again  ', 'error');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addCreditNote','shop'=>true,'ext'=>URL_EXTENSION));
                    exit;
                }
            
            
              
                
                $this->request->data['CreditNote']['date']=$this->request->data['Sale']['sales_date'];
				$this->request->data['CreditNote']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
				$this->request->data['CreditNote']['customer_id']=$this->request->data['Sale']['customer_id'];
				$this->request->data['CreditNote']['state']=$this->request->data['Sale']['state'];
				$this->request->data['CreditNote']['customer_gstin']=$this->request->data['Sale']['customer_gstin'];
				$this->request->data['CreditNote']['total_amount']=$this->request->data['Sale']['total_amount'];
				$this->request->data['CreditNote']['total_payment']=$this->request->data['Sale']['total_payment'];
				$this->request->data['CreditNote']['total_balance']=$this->request->data['Sale']['total_balance'];
				$this->request->data['CreditNote']['discount_amount']=$this->request->data['Sale']['discount_amount'];
				$this->request->data['CreditNote']['gst_amt']=$this->request->data['Sale']['gst_amt'];
				$this->request->data['CreditNote']['gst_type']=$this->request->data['Sale']['gst_type'];
				$this->request->data['CreditNote']['naration']=$this->request->data['Sale']['naration'];
				$this->request->data['CreditNote']['net_total']=$this->request->data['Sale']['net_total'];
				$this->request->data['CreditNote']['cgst_amt']=$this->request->data['Sale']['cgst_amt'];
				$this->request->data['CreditNote']['sgst_amt']=$this->request->data['Sale']['sgst_amt'];
				$this->request->data['CreditNote']['igst_amt']=$this->request->data['Sale']['igst_amt'];
				//$this->request->data['CreditNote']['final_total_amount']=$this->request->data['Sale']['final_total_amount'];
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				
				$saleOlddata=$this->CreditNote->find('first',array('conditions'=>array('CreditNote.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),'CreditNote.date >='=>$fin_from_date,'CreditNote.date <='=>$fin_to_date),'fields'=>array('CreditNote.credit_note_no'),'order'=>array('CreditNote.credit_note_no'=>'DESC')));
				// Create invoice
				
				$credit_note_no=1;
				if(!empty($saleOlddata))
				{
					
					$credit_note_no=$saleOlddata['CreditNote']['credit_note_no']+1;
				}
				$this->request->data['CreditNote']['credit_note_no']=$credit_note_no;
                $this->CreditNote->create();   
				
				$this->request->data["CreditNote"]['round_up_amt']=$this->request->data["Sale"]['total_amount']-$this->request->data["Sale"]['total_before_round'];
				$lastNoPtData=$this->Voucher->find('first',array('conditions'=>array('Voucher.user_profile_id'=>$user_profile_id,'Voucher.date >='=>$fin_from_date,'Voucher.date <='=>$fin_to_date,'Voucher.type'=>PAYMENT),'order'=>array('Voucher.no'=>'DESC'),'fields'=>array('Voucher.no')));
				
				$new_pt_no=1;
				if(!empty($lastNoPtData))
				{
					$last_pt_no=$lastNoPtData['Voucher']['no'];
					$new_pt_no=$last_pt_no+1;
				}
				
                if($this->CreditNote->save($this->request->data["CreditNote"])){
					
					/*if(isset($this->request->data['Sale']['inclusive']))
					{
					$inclusive=$this->request->data['Sale']['inclusive'];
					}else{
						$inclusive=0;
					}
					*/
                    $crn_id = $this->CreditNote->getInsertID();
                   
                    if(!empty($this->request->data["Sale"]["mode_cheque"])){
                        $cheque_amt = $this->request->data["Sale"]["mode_cheque"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>SALE_RETURN_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CHEQUE,
                        "reference_id"=>$crn_id,
                        "person_name"=>$this->request->data["Sale"]['customer_name'],
                        "payment"=>$cheque_amt,
                        "bank_name"=>$this->request->data["Sale"]["cheque_bank_name"],
                        "cheque_date"=>$this->request->data["Sale"]["cheque_date"],
						 "dr_bank"=>$this->request->data["Purchase"]["cheque_dr_bank"],
						   "cheque_no"=>$this->request->data["Purchase"]["cheque_no"],
						'user_profile_id'=>$user_profile_id,
                          "trans_no"=>$new_pt_no,

                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    if(!empty($this->request->data["Sale"]["mode_cash"])){
                        $cash_amt = $this->request->data["Sale"]["mode_cash"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>SALE_RETURN_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CASH,
                        "reference_id"=>$crn_id,
                        "person_name"=>$this->request->data["Sale"]['customer_name'],
                        "payment"=>$cash_amt,                        
                         "trans_no"=>$new_pt_no,
						'user_profile_id'=>$user_profile_id,	
                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    
                    
                    
                    
                    $distcountArr=array();
					//print_r($this->request->data['SalesDetail']);exit;
                    foreach($this->request->data['SalesDetail'] as $k)
                    {
                        $dp = !empty($k['discount'])?$k['discount']:0;
                        $dpi = (($k['price'] / 100) * $dp);
                        $discount = $k['quantity'] * $dpi;
    
                        
                      
					
						$gsamt=($k['total_amount']/100)*$k['gst_rate'];
						$value=$this->Unitchange->change($k['item_id'],$k['quantity'], $k['unit']);
						$nill="";
						$itemNillData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$k['item_id']),'fields'=>array('Item.nill_rated'),'contain'=>array('GstMaster.sgst','GstMaster.cgst','GstMaster.igst')));
						if($k['gst_rate']==BOOL_FALSE)
						{
							
							$nill=$itemNillData['Item']['nill_rated'];
						}
						$cgs_per=$itemNillData['GstMaster']['cgst'];
						$sgst_per=$itemNillData['GstMaster']['sgst'];
						$igst_per=$itemNillData['GstMaster']['igst'];
						
						$cgst_amt=$k['cgst_amt'];
						$sgst_amt=$k['sgst_amt'];
						$igst_amt=$k['igst_amt'];
						
						$gst_type=$this->request->data['Sale']['gst_type'];
					
						if($gst_type==IGST)
						{
					
							$cgst_amt=0;
							$sgst_amt=0;
						}else{
							$igst_amt=0;
						}
						
                        $this->CreditNoteDetail->create();
                        $sDtail=array(
                        'credit_note_id'=>$crn_id,
                        'item_id'=>$k['item_id'],
                        'quantity'=>$value['qty'],
                        'price'=>$k['price'],                        
                        'total_amount'=>$k['total_amount'],
                        'gst_amt'=>$gsamt,   
						'cgst_amt'=>$cgst_amt,
                        'sgst_amt'=>$sgst_amt,
                        'igst_amt'=>$igst_amt,
                        'cgst_per'=>$cgs_per,
                        'sgst_per'=>$sgst_per,
                        'igst_per'=>$igst_per,								
                        'hsn'=>$k['hsn'],
                        'gst_slab'=>$k['gst_rate'],
                        'inv_no'=>$k['inv_no'],
                        'sp'=>$k['sp'],
						 'discount_per'=>$k['discount_per'],
						'unit'=>$value['unit'],
						'nill_rated'=>$nill,
                        'created_by'=>$this->Session->read('Auth.User.id'),
                        
                        );

                        if($this->CreditNoteDetail->save($sDtail)){
							 $item_id=$k["item_id"];
                            $itemdata=$this->Item->findById($item_id);//Get category Id
							if($itemdata['Item']['item_type']!=SERVICES_TYPE)
							{
                            $sales_detail_id = $this->CreditNoteDetail->getInsertID();
                            $stock=$this->Stock->find('first',array(
                            'conditions'=>array(
                                    'Stock.item_id'=>$k['item_id'],
                                    'Stock.is_deleted'=>BOOL_FALSE,
                                    'Stock.is_active'=>BOOL_TRUE,
                                ),
                            'recursive'    => -1
                            ));
                            $this->Stock->id = $stock["Stock"]["id"];
                            $this->Stock->saveField('quantity',$stock['Stock']['quantity'] + $value['qty']);
							
                            $psid = $sales_detail_id;
                           
                            $category_id=$itemdata["Item"]["category_id"];
                            $location_id=$this->Auth->user("location_id");
                            $qty=$value['qty'];
                            $conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
                            $fields=array('Stock.quantity');
                            $stockData=$this->Stock->getallStock($conditions,$fields);
                            $stock=$stockData['Stock']['quantity'];
                            $price=$itemdata['Item']['price'] * $stock;
                            
                            $updateSelePurchaseController = new CommonsController;
                            $updateSelePurchaseController->updatePurchaseSale($psid,$item_id,SALES_RETURN,$category_id,$location_id,$qty,$stock,$price);
							}
                        }
                    }
                   
					
                    $this->Session->setFlash('The credit note has been saved ', 'success');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addCreditNote','shop'=>true,'ext'=>URL_EXTENSION,$this->Encryption->encrypt($crn_id)));
                
                }
                else 
                {
                    $this->Session->setFlash('The Sales could not be saved. Please, try again.', 'error');
                }
            }
            
            
						
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
	/*
	Amit Sahu
	22.12.17
	Credit note list
	*/
	   public function shop_creditNoteList() 
	{
		$cond=array();
		$this->shop_check_login();		
		$this->loadModel('CreditNote');
		$this->loadModel('Member');
		$this->loadModel('CreditNoteDetail');
		$this->loadModel('Item');
		$this->loadModel('UserProfile');
		$UserProfile=$this->Session->read('UserProfile');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID, UPDATE_PERMISSION_ID))) 
		{		
			if(isset($this->request->data['CreditNote']))
			{					
				$this->Session->write('CreditNoteSearch',$this->request->data['CreditNote']);
			}
			else
			{	
				$this->request->data['CreditNote']=$this->Session->read('CreditNoteSearch');		
			}	
				if(isset($this->request->data['CreditNote']))
			{
				if(isset($this->request->data['CreditNote']['name']) and !empty($this->request->data['CreditNote']['name']))
				{
				
					$cond['Member.customer_name LIKE']=$this->request->data['CreditNote']['name']."%";
					
				}
			}			
		   
			$conditions = array(
				'CreditNote.id !=' => BOOL_FALSE,
				'CreditNote.is_deleted' => BOOL_FALSE,
				'CreditNote.is_active' => BOOL_TRUE,
				'CreditNote.user_profile_id' => $this->Session->read('Auth.User.user_profile_id')
            );
           $conditions=array_merge($conditions,$cond);
			$this->Paginator->settings = array(
				    'CreditNote' => array(
					'conditions' => $conditions,
					'order' => array('CreditNote.id' => 'DESC'),
					'contain'=>array('CreditNoteDetail'=>array('Item'=>array('AltUnit'=>array('code')),'Unit'=>array('code')),'Ledger'=>array('PartyDetail'=>array('mobile','address','gstin','State'=>array('name')))),
					'limit' => PAGINATION_LIMIT_1,
					'recursive' => 2
			));
			$sales = $this->Paginator->paginate('CreditNote');
			
			$this->set(compact('sales'));
			
			$this->set(compact('UserProfile'));	
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Kajal Kurrewar
	07.02.17
	Delete Purchase list
	*/
	
	public function shop_deleteCreditNote() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('CreditNote');
		$this->loadModel('Stock');
		$this->loadModel('CreditNoteDetail');
		$this->loadModel('Item');
		$this->loadModel('Voucher');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->CreditNote->id =$id;
				if (!$this->CreditNote->exists()) 
				{
					throw new NotFoundException('Invalid Credit Note');
				}
				  if ($this->CreditNote->saveField('is_deleted',BOOL_TRUE)) 
				   {
					   $this->Voucher->updateAll(array('Voucher.is_deleted' =>BOOL_TRUE,'Voucher.is_active'=>BOOL_FALSE),array('Voucher.referance_id' =>$id,'Voucher.reporting_type'=>REPORTING_CREDIT_NOTE));
					   
					   $pddata=$this->CreditNoteDetail->find('all',array('conditions'=>array('CreditNoteDetail.credit_note_id'=>$id,'CreditNoteDetail.is_deleted'=>BOOL_FALSE)));
					   if(!empty($pddata))
					   {
						   foreach($pddata as $row)
						   {
							   $qty=$row['CreditNoteDetail']['quantity'];
								$stockData1=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$row['CreditNoteDetail']['item_id']),'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE));
								
								$this->request->data['Stock']['id']=$stockData1['Stock']['id'];
								$this->request->data['Stock']['quantity']=$stockData1['Stock']['quantity']-$qty;
								$this->Stock->save($this->request->data['Stock']);
								
								$psid = $id;
								$item_id=$row['CreditNoteDetail']['item_id'];
								$itemdata=$this->Item->findById($item_id);//Get category Id
								$category_id=$itemdata["Item"]["category_id"];
								$location_id=$this->Auth->user("location_id");
								$qty=$qty;
								$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
								$fields=array('Stock.quantity');
								$stockData=$this->Stock->getallStock($conditions,$fields);
								$stock=$stockData['Stock']['quantity'];
								$price=$itemdata['Item']['price'] * $stock;
								
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,CREDIT_NOTE_DELETE,$category_id,$location_id,$qty,$stock,$price);
							
						   }
					   }
					
					echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Purchase List Deleted successfully'));
				   }else
				   {
					   echo json_encode(array('status'=>'1001','message'=>'Purchase List could not be Deleted'));
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
	Reset Credit note Search
	22.12.17
	*/
	public function shop_resetCreditNoteSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('CreditNoteSearch');
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
	Add Debit Note
	01.09.17
	*/
	public function shop_addDebitNote()
    {
        $cond=array();
        $this->shop_check_login();

        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {
            $this->loadModel('Item');
            $this->loadModel('Stock');
           
            $this->loadModel('DebitNote');
            $this->loadModel('DebitNoteDetail');
            $this->loadModel('PaymentTransaction');
            $this->loadModel('Ledger');
            $this->loadModel('GstMaster');
            $this->loadModel('Voucher');
         

			$this->loadModel('UserProfile');
			
			$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
			
			$gstList=$this->GstMaster->getGstSlabListForCalculation($user_profile_id);
			$this->set(compact('gstList'));	
					
			$banks=$this->Ledger->getLedgerListByGroup(GROUP_BANK_ACCOUNT_ID,$user_profile_id);	
			$this->set(compact('banks'));
			
			$UserProfile=$this->Session->read('UserProfile');
			$this->set(compact('UserProfile'));	    
			
			$distList=$this->Ledger->getLedgerListByGroup(GROUP_SUNDRY_CREDITOR_ID,$user_profile_id);	
			$this->set(compact('distList'));
			
	
		
            if($this->request->is('post'))
			{
				
				 $errqty=0;
                
                foreach($this->request->data['SalesDetail'] as $k)
				{					
					if($k['quantity']==0 or $k['item_id']=='')
					{
						$errqty=1;
					}					
				}                        
                if($errqty==1)
                {
                    $this->Session->setFlash('Invalid quentity or item please try again  ', 'error');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addCreditNote','shop'=>true,'ext'=>URL_EXTENSION));
                    exit;
                }
            
            
                $this->request->data['Purchase']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
                $this->DebitNote->create();                
              if(!empty($this->request->data["Purchase"]['distributor_id']))
			  {
				 $sateno="";
				$disData=$this->Ledger->find('first',array('conditions'=>array('Ledger.id'=>$this->request->data["Purchase"]['distributor_id']),'fields'=>array('Ledger.id'),'contain'=>array('PartyDetail'=>array('State'=>array('state_no'))),'recursive'=>2));
				if(!empty($disData))
				{
					$sateno=$disData['PartyDetail']['State']['state_no'];
				}
				if($sateno!=$this->Session->read('UserProfile.State.state_no'))
				{
					$this->request->data["Purchase"]['gst_type']=IGST;
						$this->request->data["Purchase"]['cgst_amt']=0;
					$this->request->data["Purchase"]['sgst_amt']=0;
				}else{
					$this->request->data["Purchase"]['igst_amt']=0;
				}
				
				$this->request->data["Purchase"]["round_up_amt"]=$this->request->data["Purchase"]["total_amount"]-$this->request->data["Purchase"]["total_before_round"];
				
				$fin_from_date=$this->Session->read('FinancialYear.FinancialYear.from_date');
				$fin_to_date=$this->Session->read('FinancialYear.FinancialYear.to_date');
				
				$lastNoData=$this->DebitNote->find('first',array('conditions'=>array('DebitNote.user_profile_id'=>$user_profile_id,'DATE(DebitNote.created) >='=>$fin_from_date,'DATE(DebitNote.created) <='=>$fin_to_date),'order'=>array('DebitNote.debit_note_no'=>'DESC'),'fields'=>array('DebitNote.debit_note_no')));
			
				$new_no=1;
				if(!empty($lastNoData))
				{
					$last_no=$lastNoData['DebitNote']['debit_note_no'];
					$new_no=$last_no+1;
				}
				$this->request->data["Purchase"]['debit_note_no']=$new_no;
				
				$lastNoPtData=$this->Voucher->find('first',array('conditions'=>array('Voucher.user_profile_id'=>$user_profile_id,'Voucher.date >='=>$fin_from_date,'Voucher.date <='=>$fin_to_date,'Voucher.type'=>RECEIPT),'order'=>array('Voucher.no'=>'DESC'),'fields'=>array('Voucher.no')));
					
				$new_pt_no=1;
				if(!empty($lastNoPtData))
				{
					$last_pt_no=$lastNoPtData['Voucher']['no'];
					$new_pt_no=$last_pt_no+1;
				}
				
                if($this->DebitNote->save($this->request->data["Purchase"])){
                
					
				
                    $debit_note_id = $this->DebitNote->getInsertID();
					
                                           
                    if(!empty($this->request->data["Purchase"]["mode_cr_dr_card"])){
                        $card_amt = $this->request->data["Purchase"]["mode_cr_dr_card"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_RETURN_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_ONLINE,
                        "reference_id"=>$debit_note_id,
                        "person_name"=>$this->request->data["Purchase"]['customer_name'],
                        "payment"=>$card_amt,
                        "bank_name"=>$this->request->data["Purchase"]["card_bank_name"],
						 "user_profile_id"=>$user_profile_id,
						"trans_no"=>$new_pt_no,

                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    if(!empty($this->request->data["Purchase"]["mode_cheque"])){
                        $cheque_amt = $this->request->data["Purchase"]["mode_cheque"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_RETURN_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CHEQUE,
                        "reference_id"=>$debit_note_id,
                       // "person_name"=>$this->request->data["Purchase"]['customer_name'],
                        "payment"=>$cheque_amt,
                        "bank_name"=>$this->request->data["Purchase"]["cheque_bank_name"],
                        "cheque_date"=>$this->request->data["Purchase"]["cheque_date"],
						 "dr_bank"=>$this->request->data["Purchase"]["cheque_dr_bank"],
						  "cheque_no"=>$this->request->data["Purchase"]["cheque_no"],
						  "user_profile_id"=>$user_profile_id,
                       
                         "trans_no"=>$new_pt_no,

                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    if(!empty($this->request->data["Purchase"]["mode_cash"])){
                        $cash_amt = $this->request->data["Purchase"]["mode_cash"];
                        
                        $this->PaymentTransaction->create();                        
                        $this->PaymentTransaction->save(array(
                        "type"=>PURCHASE_RETURN_PAYMENT,
                        "payment_method"=>PAYMENT_TYPE_CASH,
                        "reference_id"=>$debit_note_id,
                        "payment"=>$cash_amt,    
						"user_profile_id"=>$user_profile_id,						
                         "trans_no"=>$new_pt_no,

                        ));
						$new_pt_no=$new_pt_no+1;
                    }
                    
					$discountArr=array();
                    foreach($this->request->data['SalesDetail'] as $k)
                    {
                     
					   $value=$this->Unitchange->change($k['item_id'],$k['quantity'], $k['unit']);
						$nill="";
						$itemNillData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$k['item_id']),'fields'=>array('Item.nill_rated'),'contain'=>array('GstMaster.sgst','GstMaster.cgst','GstMaster.igst')));
						$cgs_per=$itemNillData['GstMaster']['cgst'];
						$sgst_per=$itemNillData['GstMaster']['sgst'];
						$igst_per=$itemNillData['GstMaster']['igst'];
						if($k['gst_rate']==BOOL_FALSE)
						{
							
							$nill=$itemNillData['Item']['nill_rated'];
						}
						$cgst_amt=$k['cgst_amt'];
							$sgst_amt=$k['sgst_amt'];
							$igst_amt=$k['igst_amt'];
							if($sateno!=$this->Session->read('UserProfile.State.state_no'))
							{
						
								$cgst_amt=0;
								$sgst_amt=0;
							}else{
								$igst_amt=0;
							}
                        if(!empty($k['item_id']))
						{
                        $this->DebitNoteDetail->create();
                        $sDtail=array(
                        'debit_note_id'=>$debit_note_id,
                        'purchase_id'=>$k['inv_no'],
                        'item_id'=>$k['item_id'],
                        'quantity'=>$value['qty'],                  
                        'total_amount'=>$k['total_amount'],
                        'hsn'=>$k['hsn'],
                        'gst_slab'=>$k['gst_rate'], 'cgst_amt'=>$cgst_amt,
                        'sgst_amt'=>$sgst_amt,
                        'igst_amt'=>$igst_amt,
                        'cgst_per'=>$cgs_per,
                        'sgst_per'=>$sgst_per,
                        'igst_per'=>$igst_per,
                        'unit'=>$value['unit'],
                        'gross_total'=>$k['sp']*$value['qty'],
                        'pp'=>$k['sp'],
                        'discount'=>$k['discount'],
                        'nill_rated'=>$nill,
                        'created_by'=>$this->Session->read('Auth.User.id'),
                        
                        );
						// Srore All Discount
						 $discountArr[]=$k['discount'];
						 //end Srore All Discount
                        if($this->DebitNoteDetail->save($sDtail)){
                            $debit_note_detail_id = $this->DebitNoteDetail->getInsertID();
                            $stock=$this->Stock->find('first',array(
                            'conditions'=>array(
                                    'Stock.item_id'=>$k['item_id'],
                                    'Stock.is_deleted'=>BOOL_FALSE,
                                    'Stock.is_active'=>BOOL_TRUE,
                                ),
                            'recursive'    => -1
                            ));
                           
							if(!empty($stock))
							{
								 $this->Stock->id = $stock["Stock"]["id"];
                            $this->Stock->saveField('quantity',($stock['Stock']['quantity'] - $value['qty']));
							}
                            
                            $psid = $debit_note_detail_id;
                            $item_id=$k["item_id"];
                            $itemdata=$this->Item->findById($item_id);//Get category Id
                            $category_id=$itemdata["Item"]["category_id"];
                            $qty=$value['qty'];
                            $conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
                            $fields=array('Stock.quantity');
                            $stockData=$this->Stock->getallStock($conditions,$fields);
                            $stock=$stockData['Stock']['quantity'];
                            $price=$itemdata['Item']['price'] * $stock;
                            $location_id="";
                            $updateSelePurchaseController = new CommonsController;
                            $updateSelePurchaseController->updatePurchaseSale($psid,$item_id,PURCHASE_RETURN,$category_id,$location_id,$qty,$stock,$price);
                        }
						}
                    }
						//Update Product Discount Amount
						$itemDis=array_sum($discountArr);                   
						$this->request->data['PurchaseUpdate']['id']=$debit_note_id;
						$this->request->data['PurchaseUpdate']['product_discount']=$itemDis;
						$this->DebitNote->save($this->request->data['PurchaseUpdate']);
						//End update Product Discount Amount
							
					
					
                    $this->Session->setFlash(' Debit Note has been saved ', 'success');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addDebitNote','shop'=>true));
                
                }
			  
                else 
                {
                    $this->Session->setFlash(' Debit note could not be saved. Please, try again.', 'error');
                }
			  }else{
				  $this->Session->setFlash('Please select vendor.', 'error');
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
	22.12.17
	Debit note list
	*/
	public function shop_debitNoteList() 
	{
		$cond=array();
		$this->shop_check_login();
		
		$this->loadModel('DebitNote');

		$this->loadModel('DebitNoteDetail');
		$this->loadModel('GstMaster');

		
		
		$UserProfile=$this->Session->read('UserProfile');
		$this->set(compact('UserProfile'));
	
		$gstList=$this->GstMaster->getGstSlabListForCalculation($this->Session->read('Auth.User.user_profile_id'));
			$this->set(compact('gstList'));
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID)))
		{
			
			if(isset($this->request->data['DebitNote']))
			{
				$this->Session->write('DebitNoteSearch',$this->request->data['DebitNote']);
			}
			else
			{
				$this->request->data['DebitNote']=$this->Session->read('DebitNoteSearch');
			}
			if(isset($this->request->data['DebitNote']))
			{
				if(isset($this->request->data['DebitNote']['name']) and !empty($this->request->data['DebitNote']['name']))
				{
					//$cond['OR']['Purchase.bill_no LIKE']=$this->request->data['Purchase']['name']."%";
					
					$cond['Ledger.name LIKE']=$this->request->data['DebitNote']['name']."%";
					
					//$cond['OR']['DATE(Purchase.bill_date)']=date("Y-m-d",strtotime($this->request->data['Purchase']['name']));
				}
			}

			$conditions = array(
				'DebitNote.id !=' => BOOL_FALSE,
				'DebitNote.is_deleted' => BOOL_FALSE,
				'DebitNote.is_active' => BOOL_TRUE,
				'DebitNote.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),

			);

			$conditions=array_merge($conditions,$cond);

			$this->Paginator->settings = array(
				'DebitNote' => array(
					'conditions' => $conditions,
					'order' => array('DebitNote.id' => 'DESC'),					
					'limit' => PAGINATION_LIMIT_1,
					'recursive' =>2
			));
			
			$debitNotes = $this->Paginator->paginate('DebitNote');
			$this->set(compact('debitNotes'));
			
	
		
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	12.12.17
	Delete Debit note
	*/
	
	public function shop_deleteDebitNote() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('DebitNote');
		$this->loadModel('Stock');
		$this->loadModel('DebitNoteDetail');
		$this->loadModel('Item');
		$this->loadModel('Voucher');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->DebitNote->id =$id;
				if (!$this->DebitNote->exists()) 
				{
					throw new NotFoundException('Invalid DebitNote');
				}
				  if ($this->DebitNote->saveField('is_deleted',BOOL_TRUE)) 
				   {
					   $this->Voucher->updateAll(array('Voucher.is_deleted' =>BOOL_TRUE,'Voucher.is_active'=>BOOL_FALSE),array('Voucher.referance_id' =>$id,'Voucher.reporting_type'=>REPORTING_DEBIT_NOTE));
					   
					   $pddata=$this->DebitNoteDetail->find('all',array('conditions'=>array('DebitNoteDetail.debit_note_id'=>$id,'DebitNoteDetail.is_deleted'=>BOOL_FALSE)));
					   if(!empty($pddata))
					   {
						   foreach($pddata as $row)
						   {
							   $qty=$row['DebitNoteDetail']['quantity'];
								$stockData1=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$row['DebitNoteDetail']['item_id']),'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE));
								
								$this->request->data['Stock']['id']=$stockData1['Stock']['id'];
								$this->request->data['Stock']['quantity']=$stockData1['Stock']['quantity']+$qty;
								$this->Stock->save($this->request->data['Stock']);
								
								$psid = $id;
								$item_id=$row['DebitNoteDetail']['item_id'];
								$itemdata=$this->Item->findById($item_id);//Get category Id
								$category_id=$itemdata["Item"]["category_id"];
								$location_id=$this->Auth->user("location_id");
								$qty=$qty;
								$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
								$fields=array('Stock.quantity');
								$stockData=$this->Stock->getallStock($conditions,$fields);
								$stock=$stockData['Stock']['quantity'];
								$price=$itemdata['Item']['price'] * $stock;
								
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,DEBIT_NOTE_DELETE,$category_id,$location_id,$qty,$stock,$price);
							
						   }
					   }
					
					echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'DebitNote  Deleted successfully'));
				   }else
				   {
					   echo json_encode(array('status'=>'1001','message'=>'DebitNote could not be Deleted'));
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
	Reset Debit note Search
	22.12.17
	*/
	public function shop_resetDebitNoteSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('DebitNoteSearch');
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
	23.12.17
	Advance List
	*/
	public function shop_advanceReceiveList() 
	{
		$cond=array();
		$this->shop_check_login();
		
		$this->loadModel('Advance');
		$this->loadModel('Member');
		$this->loadModel('BankAccount');

		
		
		/*$UserProfile=$this->Session->read('UserProfile');
		$this->set(compact('UserProfile'));*/
		
		$banks=$this->BankAccount->getBankAccountList($this->Session->read('Auth.User.user_profile_id'));	
		$this->set(compact('banks'));
		
	
		$fields=array('Member.id','Member.customer_name');
		$conditions=array('Member.id !='=>BOOL_FALSE,'Member.is_deleted'=>BOOL_FALSE,'Member.is_active'=>BOOL_TRUE,'Member.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'));
    
		$member_list=$this->Member->find('list',array('conditions'=>$conditions,'fields'=>$fields));
		$this->set(compact('member_list'));
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID)))
		{
			
			if(isset($this->request->data['Advance']))
			{
				$this->Session->write('AdvanceSearch',$this->request->data['Advance']);
			}
			else
			{
				$this->request->data['Advance']=$this->Session->read('AdvanceSearch');
			}
			if(isset($this->request->data['Advance']))
			{
				if(isset($this->request->data['Advance']['name']) and !empty($this->request->data['Advance']['name']))
				{
					//$cond['OR']['Purchase.bill_no LIKE']=$this->request->data['Purchase']['name']."%";
					
					$cond['Member.customer_name LIKE']=$this->request->data['Advance']['name']."%";
					
					//$cond['OR']['DATE(Purchase.bill_date)']=date("Y-m-d",strtotime($this->request->data['Purchase']['name']));
				}
				if(isset($this->request->data['Advance']['date']) and !empty($this->request->data['Advance']['date']))
				{
					$cond['Advance.date']=date('Y-m-d' , strtotime($this->request->data['Advance']['date']));
				}
			}

			$conditions = array(
				'Advance.id !=' => BOOL_FALSE,
				'Advance.is_deleted' => BOOL_FALSE,
				'Advance.is_active' => BOOL_TRUE,
				'Advance.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),

			);

			$conditions=array_merge($conditions,$cond);

			$this->Paginator->settings = array(
				'Advance' => array(
					'conditions' => $conditions,
					'order' => array('Advance.id' => 'DESC'),					
					'limit' => PAGINATION_LIMIT_1,
					'recursive' =>2
			));
			
			$advances = $this->Paginator->paginate('Advance');
			$this->set(compact('advances'));
			
	
		
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	Reset Advance Search
	22.12.17
	
	*/	
	public function shop_resetAdvanceSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						

		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('AdvanceSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Receive advance
	Amit Sahu
	22.12.17
	*/
	public function shop_receivedAdvance() {
		$this->autoRender = FALSE;
			$this->layout = 'ajax';
			$this->loadModel('Advance');
			$this->loadModel('PaymentTransaction');
			$this->loadModel('Member');
			$this->loadModel('AdvanceDetail');
		
		    
				
			if ($this->request->is('ajax')) 
				{
					
		          
					$this->request->data['Advance']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
					$date=$this->request->data['Advance']['date'];
					$this->request->data['Advance']['date']=date('Y-m-d',strtotime($this->request->data['Advance']['date']));
					$memData=$this->Member->findById($this->request->data['Advance']['member_id']);
					if(!empty($memData))
					{
					$this->request->data['Advance']['member_state_id']=$memData['Member']['state'];
					}
					if(!empty($this->request->data['AdvanceDetails']))
					{
						$this->Advance->create();			
						if ($this->Advance->save($this->request->data['Advance'])) 
						{
							$id=$this->Advance->getInsertID();
							
							$advDetails=$this->request->data['AdvanceDetails'];
							
							if(!empty($advDetails))
							{
								foreach($advDetails as $row)
								{
									$this->AdvanceDetail->create();
									$this->request->data["AdvanceDetail"]["advance_id"]=$id;
									$this->request->data["AdvanceDetail"]["item_id"]=$row['item_id'];
									$this->request->data["AdvanceDetail"]["hsn"]=$row['hsn'];
									$this->request->data["AdvanceDetail"]["gst_rate"]=$row['gst_rate'];
									$this->request->data["AdvanceDetail"]["amount"]=$row['amount'];
									$this->request->data["AdvanceDetail"]["balance_amt"]=$row['amount'];
									$this->AdvanceDetail->save($this->request->data["AdvanceDetail"]);
								}
							}					
							
							$this->PaymentTransaction->create();
							$this->request->data["PaymentTransaction"]["type"]=ADVANCE_PAYMENT;
							$this->request->data["PaymentTransaction"]["reference_id"]=$id;
							$this->request->data["PaymentTransaction"]["customer_id"]=$this->request->data['Advance']['member_id'];
							$this->request->data["PaymentTransaction"]["payment"]=$this->request->data['Advance']['amount'];
							$this->request->data["PaymentTransaction"]["payment_method"]=$this->request->data['Advance']['payment_method'];
							$this->request->data["PaymentTransaction"]["dr_bank"]=$this->request->data['Advance']['dr_bank'];
							$this->request->data["PaymentTransaction"]["cheque_no"]=$this->request->data['Advance']['cheque_no'];
							$this->request->data["PaymentTransaction"]["cheque_date"]=!empty($this->request->data['Advance']['cheque_date'])? date('Y-m-d',strtotime($this->request->data['Advance']['cheque_date'])):'';
							$this->request->data["PaymentTransaction"]["card_no"]=$this->request->data['Advance']['card_no'];
							$this->PaymentTransaction->save($this->request->data["PaymentTransaction"]);
							
							
							$memData=$this->Member->findById($this->request->data['Advance']['member_id']);
							$mem_name="";
							if(!empty($memData))
							{
								$mem_name=$memData['Member']['customer_name'];
							}
							$amount=number_format($this->request->data['Advance']['amount'],2);
							echo json_encode(array('status'=>'1000','message'=>'Advance received successfully','id'=>$id,'mem_name'=>$mem_name,'amount'=>$amount,'date'=>$date));
						} 
						else 
						{
							$errors = $this->Advance->validationErrors;
							$mem_req=$errors['member_id'][0];
							echo json_encode(array('status'=>'1001','message'=>'Advance could not be received','mem_req'=>$mem_req));
						}
					}
					else
					{
						echo json_encode(array('status'=>'1001','message'=>'Please select at least one  item for received advance'));
					}
				}				
			
			else
			{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
			}
    }
	/*
	Edit  advance
	Amit Sahu
	22.12.17
	*/
	public function shop_editAdvance() {
		$this->autoRender = FALSE;
			$this->layout = 'ajax';
			$this->loadModel('Advance');
			$this->loadModel('PaymentTransaction');
			$this->loadModel('Member');
			$this->loadModel('AdvanceDetail');
		
			if ($this->request->is('ajax')) 
				{
					
					$date=date('d-m-Y',strtotime($this->request->data['Advance']['date']));
					$this->request->data['Advance']['date']=date('Y-m-d',strtotime($this->request->data['Advance']['date']));
					//print_r($this->request->data);
					if(!empty($this->request->data['AdvanceDetails']))
					{
						if ($this->Advance->save($this->request->data['Advance'])) 
						{
											
							$id=$this->request->data['Advance']['id'];
							
							$advOldData=$this->AdvanceDetail->find('all',array('conditions'=>array('AdvanceDetail.advance_id'=>$id),'fields'=>array('AdvanceDetail.id')));
					
							if(!empty($advOldData))
							{
								foreach($advOldData as $old)
								{
									$this->AdvanceDetail->delete($old['AdvanceDetail']['id']);
								}
							}
							$advDetails=$this->request->data['AdvanceDetails'];
							
							if(!empty($advDetails))
							{
								foreach($advDetails as $row)
								{
									$this->AdvanceDetail->create();
									$this->request->data["AdvanceDetail"]["advance_id"]=$id;
									$this->request->data["AdvanceDetail"]["item_id"]=$row['item_id'];
									$this->request->data["AdvanceDetail"]["hsn"]=$row['hsn'];
									$this->request->data["AdvanceDetail"]["gst_rate"]=$row['gst_rate'];
									$this->request->data["AdvanceDetail"]["amount"]=$row['amount'];
									$this->request->data["AdvanceDetail"]["balance_amt"]=$row['amount'];
									$this->AdvanceDetail->save($this->request->data["AdvanceDetail"]);
								}
							}
							$this->PaymentTransaction->create();
							$this->request->data["PaymentTransaction"]["id"]=$this->request->data['Advance']['payment_tr_id'];
							$this->request->data["PaymentTransaction"]["reference_id"]=$id;
							$this->request->data["PaymentTransaction"]["type"]=ADVANCE_PAYMENT;
							$this->request->data["PaymentTransaction"]["customer_id"]=$this->request->data['Advance']['member_id'];
							$this->request->data["PaymentTransaction"]["payment"]=$this->request->data['Advance']['amount'];
							$this->request->data["PaymentTransaction"]["payment_method"]=$this->request->data['Advance']['payment_method'];
							$this->request->data["PaymentTransaction"]["dr_bank"]=$this->request->data['Advance']['dr_bank'];
							$this->request->data["PaymentTransaction"]["cheque_no"]=$this->request->data['Advance']['cheque_no'];
							$this->request->data["PaymentTransaction"]["cheque_date"]=!empty($this->request->data['Advance']['cheque_date'])? date('Y-m-d',strtotime($this->request->data['Advance']['cheque_date'])):'';
							$this->request->data["PaymentTransaction"]["card_no"]=$this->request->data['Advance']['card_no'];
							$this->PaymentTransaction->save($this->request->data["PaymentTransaction"]);
						
							
							$memData=$this->Member->findById($this->request->data['Advance']['member_id']);
							$mem_name="";
							if(!empty($memData))
							{
								$mem_name=$memData['Member']['customer_name'];
							}
							$amount=number_format($this->request->data['Advance']['amount'],2);
							echo json_encode(array('status'=>'1000','message'=>'Advance received edit successfully','id'=>$id,'mem_name'=>$mem_name,'amount'=>$amount,'date'=>$date));
						} 
						else 
						{
							$errors = $this->Advance->validationErrors;
							$mem_req=$errors['member_id'][0];
							echo json_encode(array('status'=>'1001','message'=>'Advance could not be received','mem_req'=>$mem_req));
						}
					}else
					{
						echo json_encode(array('status'=>'1001','message'=>'Please select at least one  item for received advance'));
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
	Delete Advance
	24.02.17
	*/
	public function shop_deleteAdvance() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Advance');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Advance->id =$id;
				if (!$this->Advance->exists()) 
				{
					throw new NotFoundException('Invalid Advance');
				}
									
							   if ($this->Advance->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Advance->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Advance deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Advance could not be Deleted'));
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
	Set Edit Advance Data
	27.12.17
	*/
	public function shop_setEditAdvanceData() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Advance');
		$this->loadModel('Item');
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['id'];
			
				$advanceData=$this->Advance->findById($id);
									
				   if (!empty($advanceData)) 
				   {
						$cust_id=$advanceData['Advance']['member_id'];
						$adv_date=date('d-m-Y', strtotime($advanceData['Advance']['date']));
						$amount=$advanceData['Advance']['amount'];
						
						$pay_meth="";
						$card_no="";
						$dr_bank="";
						$cheque_no="";
						$cheque_date="";
						$pt_tr_id="";
						if(!empty($advanceData['PaymentTransaction']))
						{
						$pt_tr_id=$advanceData['PaymentTransaction'][0]['id'];
						$pay_meth=$advanceData['PaymentTransaction'][0]['payment_method'];
						$card_no=$advanceData['PaymentTransaction'][0]['card_no'];
						$dr_bank=$advanceData['PaymentTransaction'][0]['dr_bank'];
						$cheque_no=$advanceData['PaymentTransaction'][0]['cheque_no'];
						$cheque_date=$advanceData['PaymentTransaction'][0]['cheque_date'];
						}
						
						$detailsData=$advanceData['AdvanceDetail'];
						$table="";
						if(!empty($detailsData))
						{
							$i=0;
							foreach($detailsData as $row)
							{
								
								$itemData=$this->Item->findById($row['item_id']);
								$i++;
								$pd_row="'pdRow_".$i."'";
								$table.='<tr id="pdRow_'.$i.'" class="main_item_row">
								<td>'.$i.'</td>
								<td><input id="ItemIdTmp2_'.$i.'" name="data[AdvanceDetails]['.$i.'][item_id_tmp2]" class="item_id_tmp2" type="hidden"><input id="ItemId_'.$i.'" name="data[AdvanceDetails]['.$i.'][item_id]" class="item_id" value="'.$row['item_id'].'" type="hidden"><input id="ItemNameTmp_'.$i.'" name="data[AdvanceDetails]['.$i.'][item_name_tmp]" value="'.$itemData['Item']['name'].'" class="form-control sale_text_box item_name_tmp txt_borderless valid" autocomplete="off" data-original-title="" title="" aria-invalid="false" type="text"><div class="my_autoslect test_select" id="filterBox_'.$i.'" onscroll="appendData('.$i.')" style="display: none;"><table class="table table-bordered table-sm m-b-0 report_table_font"><thead></thead><tbody id="itemTable"></tbody></table><input id="lastID'.$i.'" value="50" type="hidden"></div></td>
								<td><input id="productHsn_'.$i.'" name="data[AdvanceDetails]['.$i.'][hsn]" class="form-control sale_text_box  txt_borderless" value="'.$row['hsn'].'" readonly="" type="text"></td>
								<td><input id="productGst_'.$i.'" value="'.$row['gst_rate'].'" name="data[AdvanceDetails]['.$i.'][gst_rate]" class="form-control sale_text_box  txt_borderless" readonly="" type="text"></td>
								<td><input id="totalAmount_'.$i.'" value="'.$row['amount'].'" name="data[AdvanceDetails]['.$i.'][amount]" class="form-control sale_text_box adv_amount txt_borderless" type="text"></td>
								<td align="center"><a href="javascript:void();" class="remove_row btn waves-effect btn-danger btn-xs" onclick="removeRow('.$pd_row.')"><i class="fa fa-trash"></i></a></td>
								</tr>';
							}
						}	
					echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Advance deleted successfully','cust_id'=>$cust_id,'adv_date'=>$adv_date,'amount'=>$amount,'pay_meth'=>$pay_meth,'card_no'=>$card_no,'dr_bank'=>$dr_bank,'cheque_no'=>$cheque_no,'cheque_date'=>$cheque_date,'table'=>$table));
				   }else
				   {
					   echo json_encode(array('status'=>'1001','message'=>'Invalid Data'));
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
	Get advance receive no by party and gts rate  
	27.12.17
	*/
	public function shop_getAddvanceRecNoByCustAndGStRate() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('AdvanceDetail');
		if ($this->request->is('ajax')) 
			{
				$gst_rate=$this->request->data['gst_rate'];
				$cust_id=$this->request->data['cust_id'];
			
				$conditions=array('Advance.is_deleted'=>BOOL_FALSE,'Advance.is_active'=>BOOL_TRUE,'AdvanceDetail.is_deleted'=>BOOL_FALSE,'AdvanceDetail.is_active'=>BOOL_TRUE,'Advance.member_id'=>$cust_id,'AdvanceDetail.gst_rate'=>$gst_rate,'AdvanceDetail.balance_amt >'=>0);
				$fields=array('AdvanceDetail.advance_id','SUM(AdvanceDetail.amount) as total_advance','SUM(AdvanceDetail.adjusted_amt) as total_adjusted','SUM(AdvanceDetail.balance_amt) as total_balance');
				$detsilsData=$this->AdvanceDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>array('AdvanceDetail.advance_id')));
				
				//print_r($detsilsData);exit;
					$options="";				
				   if (!empty($detsilsData)) 
				   {
					   foreach($detsilsData as $row)
					   {
							$options.='<option value="'.$row['AdvanceDetail']['advance_id'].'">'.$row['AdvanceDetail']['advance_id'].'</option>';	
					   }
					   $adv_amt=$detsilsData[0][0]['total_advance'];
					   $total_adjusted=$detsilsData[0][0]['total_adjusted'];
					   $total_balance=$detsilsData[0][0]['total_balance'];
					 
						
					echo json_encode(array('status'=>'1000','options'=>$options,'adv_amt'=>$adv_amt,'total_adjusted'=>$total_adjusted,'total_balance'=>$total_balance));
				   }else
				   {
					   echo json_encode(array('status'=>'1001','options'=>'options'));
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
	Get advance by receive no gst rate  
	27.12.17
	*/
	public function shop_getAddvanceByRecNoGStRate() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('AdvanceDetail');
		if ($this->request->is('ajax')) 
			{
				$gst_rate=$this->request->data['gst_rate'];
				$rec_no=$this->request->data['rec_no'];
			
				$conditions=array('Advance.is_deleted'=>BOOL_FALSE,'Advance.is_active'=>BOOL_TRUE,'AdvanceDetail.is_deleted'=>BOOL_FALSE,'AdvanceDetail.is_active'=>BOOL_TRUE,'AdvanceDetail.advance_id'=>$rec_no,'AdvanceDetail.gst_rate'=>$gst_rate);
				$fields=array('AdvanceDetail.advance_id','SUM(AdvanceDetail.amount) as total_advance','SUM(AdvanceDetail.adjusted_amt) as total_adjusted','SUM(AdvanceDetail.balance_amt) as total_balance');
				$detsilsData=$this->AdvanceDetail->find('first',array('conditions'=>$conditions,'fields'=>$fields,'group'=>array('AdvanceDetail.gst_rate')));
				
				//print_r($detsilsData);exit;
			
				   if (!empty($detsilsData)) 
				   {
					   
					   $adv_amt=$detsilsData[0]['total_advance'];
					   $total_adjusted=$detsilsData[0]['total_adjusted'];
					   $total_balance=$detsilsData[0]['total_balance'];
					 
						
					echo json_encode(array('status'=>'1000','adv_amt'=>$adv_amt,'total_adjusted'=>$total_adjusted,'total_balance'=>$total_balance));
				   }else
				   {
					   echo json_encode(array('status'=>'1001'));
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
	GSTR 3B
	16.01.18
	*/
		public function shop_gstGstrOneThreeB() 
	{
		$this->autoRender = FALSE;
		
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {

            $this->loadModel('SalesDetail');
            $this->loadModel('PurchaseDetail');

            if($this->request->is('post')){
				// App::import('Vendor', 'excel/PHPExcel.php');
				  App::import('Vendor', 'excel', array(
            'file' => 'excel' . DS . 'PHPExcel.php'
        ));

				// Create new PHPExcel object
				$objPHPExcel = new PHPExcel();

				

				// Add some data

				//First sheet
				$sheet = $objPHPExcel->getActiveSheet();

				//Start adding next sheets
				
			

				// Add new sheet
			
				$objWorkSheet = $objPHPExcel->createSheet(0); //Setting index when creating

				
				//Write cells
				//*****************************Get Data************************************************************//
				//B2B List
				
					
				$conditions=array(	
				'SalesDetail.is_active'=>BOOL_TRUE,
				'SalesDetail.is_deleted'=>BOOL_FALSE,
				'Sale.is_deleted'=>BOOL_FALSE,
				'Sale.is_active'=>BOOL_TRUE,	
				'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']
				);			
	
				$fields=array('SUM(SalesDetail.total_amount) as taxable','Sale.id');
			
				$salesdetaillist=$this->SalesDetail->find('first',array('order'=>array('SalesDetail.id asc'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2));
				
				$conditions1=array(	
				'SalesDetail.is_active'=>BOOL_TRUE,
				'SalesDetail.is_deleted'=>BOOL_FALSE,
				'Sale.is_deleted'=>BOOL_FALSE,
				'Sale.is_active'=>BOOL_TRUE,	
				'Sale.gst_type'=>BOOL_TRUE,	
				'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']
				);			
	
				$fields1=array('SUM(SalesDetail.gst_amt) as igst');
			
				$salesigst=$this->SalesDetail->find('first',array('order'=>array('SalesDetail.id asc'),'fields'=>$fields1,'conditions'=>$conditions1,'recursive' => 2));
				
				$conditions2=array(	
				'SalesDetail.is_active'=>BOOL_TRUE,
				'SalesDetail.is_deleted'=>BOOL_FALSE,
				'Sale.is_deleted'=>BOOL_FALSE,
				'Sale.is_active'=>BOOL_TRUE,	
				'Sale.gst_type'=>BOOL_FALSE,	
				'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Sale.sales_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Sale.sales_date)'=>$this->request->data['Gstr1']['month']
				);			
	
				$fields2=array('SUM(SalesDetail.gst_amt) as csgst');
			
				$salescsgst=$this->SalesDetail->find('first',array('order'=>array('SalesDetail.id asc'),'fields'=>$fields2,'conditions'=>$conditions2,'recursive' => 2));
				$scgst=0;
				if($salescsgst[0]['csgst']!=BOOL_FALSE)
				{
					$scgst=$salescsgst[0]['csgst']/2;
				}
				//setCellValue('A1', 'Hello'.$i)
				$objWorkSheet->setCellValue('A1', 'Nature of Supplies')
				->setCellValue('B1', 'Total Taxable value')
				->setCellValue('C1', 'Integrated Tax')
				->setCellValue('D1', 'Central Tax')
				->setCellValue('E1', 'State/UT Tax')
				->setCellValue('F1', 'Cess');
				$objWorkSheet->setCellValue('A2', '(a) Outward Taxable  supplies  (other than zero rated, nil rated and exempted)')
				->setCellValue('B2', $salesdetaillist[0]['taxable'])
				->setCellValue('C2', $salesigst[0]['igst'])
				->setCellValue('D2', $scgst)
				->setCellValue('E2', $scgst)

				->setCellValue('F2', '0');
				$objWorkSheet->setCellValue('A3', '')
				->setCellValue('B3', '')
				->setCellValue('C3', '')
				->setCellValue('D3', '')
				->setCellValue('E3', '');
			
				// Purchase
				$conditions2=array(	
				'PurchaseDetail.is_active'=>BOOL_TRUE,
				'PurchaseDetail.is_deleted'=>BOOL_FALSE,
				'Purchase.is_deleted'=>BOOL_FALSE,
				'Purchase.is_active'=>BOOL_TRUE,	
				'Purchase.gst_type'=>BOOL_TRUE,	
				'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Purchase.bill_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Purchase.bill_date)'=>$this->request->data['Gstr1']['month']
				);			
	
				$fields2=array('SUM((PurchaseDetail.total_amount/100)*PurchaseDetail.gst_slab) as igst');
			
				$purchaseigst=$this->PurchaseDetail->find('first',array('fields'=>$fields2,'conditions'=>$conditions2,'recursive' => 2));
				

				// Purchase
				$conditions3=array(	
				'PurchaseDetail.is_active'=>BOOL_TRUE,
				'PurchaseDetail.is_deleted'=>BOOL_FALSE,
				'Purchase.is_deleted'=>BOOL_FALSE,
				'Purchase.is_active'=>BOOL_TRUE,	
				'Purchase.gst_type'=>BOOL_FALSE,	
				'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
				'YEAR(Purchase.bill_date)'=>$this->request->data['Gstr1']['year'],	
				'MONTH(Purchase.bill_date)'=>$this->request->data['Gstr1']['month']
				);			
	
				$fields3=array('SUM((PurchaseDetail.total_amount/100)*PurchaseDetail.gst_slab) as csgst');
			
				$purchasecs=$this->PurchaseDetail->find('first',array('fields'=>$fields3,'conditions'=>$conditions3,'recursive' => 2));
				$pcsgst=0;
				if($purchasecs[0]['csgst']!=0)
				{
					$pcsgst=$purchasecs[0]['csgst']/2;
				}
				$objWorkSheet->setCellValue('A4', 'Details')
				->setCellValue('B4', 'Integrated Tax')
				->setCellValue('C4', 'Central Tax')
				->setCellValue('D4', 'State/UT Tax')
				->setCellValue('E4', 'Cess');
				
				$objWorkSheet->setCellValue('A5', '(e) Non-GST Outward supplies')
				->setCellValue('B5', $purchaseigst[0]['igst'])
				->setCellValue('C5', $pcsgst)
				->setCellValue('D5', $pcsgst)
				->setCellValue('E5', '0');

				
				
				// Rename sheet
				$objWorkSheet->setTitle("3B");
				
				
				
				
				
				// Redirect output to a client’s web browser (Excel5)
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="01simple.xls"');
				header('Cache-Control: max-age=0');
				// If you're serving to IE 9, then the following may be needed
				header('Cache-Control: max-age=1');

				// If you're serving to IE over SSL, then the following may be needed
				header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
				header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
				header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
				header ('Pragma: public'); // HTTP/1.0

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
				$objWriter->save('php://output');
				    return $this->redirect($this->referer());
				//exit;	
		
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
	Add Opeing stock
	22.09.18
	*/
	public function shop_addOpeningStock()
    {
       
        $this->shop_check_login();

        if ($this->Access->checkPermission(array(READ_PERMISSION_ID,CREATE_PERMISSION_ID)))
        {

            $this->loadModel('Item');
            $this->loadModel('Stock');
            $this->loadModel('SaleCharge');
            if($this->request->is('post')){
				
                $errqty=0;
                /*echo "<pre>";
				print_r($this->request->data);
                echo "</pre>";exit;*/
           foreach($this->request->data['SalesDetail'] as $k)
					{

					if($k['quantity']==0)
					{
					$errqty=1;
					}

					}                        
					if($errqty==1)
					{
						//echo "123";exit;
					$this->Session->setFlash('Invalid quentity please try again  ', 'error');                
					return $this->redirect(array('controller'=>'shops','action' => 'consumption','shop'=>true,'ext'=>URL_EXTENSION));
					exit;
					}
				
                   
                    foreach($this->request->data['SalesDetail'] as $k)
                    {
						$value=$this->Unitchange->change($k['item_id'],$k['quantity'], $k['unit']);
						$currentStock=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$k['item_id'],'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE),'fields'=>array('Stock.item_id','Stock.quantity','Stock.id')));
							if(!empty($currentStock))
							{
								$psid=$currentStock['Stock']['id'];
								$this->request->data['Stock']['id']=$currentStock['Stock']['id'];
								$this->request->data['Stock']['quantity']=$value['qty'];
							}else{
								$this->Stock->create();
								$this->request->data['Stock']['id']='';
								$this->request->data['Stock']['item_id']=$k['item_id'];
								$this->request->data['Stock']['quantity']=$value['qty'];
							}
							if($this->Stock->save($this->request->data['Stock']))
							{
								if(empty($this->request->data['Stock']['id']))
								{
									 $psid=$this->Stock->getInsertID();
								}
								
								 $item_id=$k["item_id"];
								 $itemdata=$this->Item->findById($item_id);
								
								$category_id=$itemdata["Item"]["category_id"];
								$location_id=$this->Auth->user("location_id");
								$qty=$value['qty'];
								$conditions=array('Stock.item_id'=>$item_id,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE);
								
								$stock=$value['qty'];
								$price=$itemdata['Item']['price'] * $stock;
								
								$updateSelePurchaseController = new CommonsController;
								$updateSelePurchaseController->updatePurchaseSale($psid,$item_id,ADD_OPEING_STOCK,$category_id,$location_id,$qty,$stock,$price);
							
						}
						
                    }
                    
				
                    $this->Session->setFlash('Stock has been saved', 'success');                
                    return $this->redirect(array('controller'=>'shops','action' => 'addOpeningStock','shop'=>true,'ext'=>URL_EXTENSION));
					}
					
                 
            
        }
        else
        {
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	
	public function shop_uploadItem() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('AdvanceDetail');
		$this->loadModel('Unit');
		$this->loadModel('GstMaster');
		$this->loadModel('Item');
		if ($this->request->is('ajax')) 
			{
				//print_r($this->request->data['ItemUpload']);
				
							if (isset($this->request->data['ItemUpload']["file"]["type"])) {
							$validextensions = array("ods",'xlsx');
							$temporary = explode(".", $this->request->data['ItemUpload']["file"]["name"]);
							$file_extension = end($temporary);
							if (($this->request->data['ItemUpload']["file"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" || $this->request->data['ItemUpload']["file"]["type"] == "image/jpeg"
									) && ($this->request->data['ItemUpload']["file"]["size"] < 900000)//Approx. 100kb files can be uploaded.
									&& in_array($file_extension, $validextensions)) {
								if ($this->request->data['ItemUpload']["file"]["error"] > 0) {
									echo json_encode(array('status' => 1001, 'message' => 'Invalid file.', 'imgpath' => ""));
									/* echo "Return Code: " . $_FILES["file"]["error"] . "<br/><br/>"; */
								} else {
									$sourcePath = $this->request->data['ItemUpload']['file']['tmp_name']; // Storing source path of the file in a variable
									//$date = new DateTime();
									//$datetimestr = $date->getTimestamp();
									//$imgName = $datetimestr . $this->request->data['ItemUpload']['file']['name'];
									$imgName = $this->request->data['ItemUpload']['file']['name'];
									
									$targetPath = "images/excel/" . $imgName; // Target path where file is to be stored
									move_uploaded_file($sourcePath, $targetPath); // Moving Uploaded file
									
									// Get Excel Data
									App::import('Vendor', 'PHPExcel/Classes/PHPExcel');
									if (!class_exists('PHPExcel')) {
										throw new CakeException('Vendor class PHPExcel not found!');
									}
									
									$filename = "images/excel/ItemUpload.xlsx";
									$type = PHPExcel_IOFactory::identify($filename);
									$objReader = PHPExcel_IOFactory::createReader($type);
									$objPHPExcel = $objReader->load($filename);

									foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
										$worksheets[$worksheet->getTitle()] = $worksheet->toArray();
									}
									//print_r($worksheets);
									$sheetone=$worksheets['Sheet1'];
									unset($sheetone[0]);
									//print_r($sheetone);
									//exit;
									
									// Besic data
									$user_profile_id=$this->Session->read('Auth.User.user_profile_id');
									
									$unitData=$this->Unit->getUnitList();
									$unitData=array_flip($unitData);
									
									$gstslabData=$this->GstMaster->find('list',array('conditions'=>array('GstMaster.is_deleted'=>BOOL_FALSE,'GstMaster.is_active'=>BOOL_TRUE,'GstMaster.user_profile_id'=>$user_profile_id),'fields'=>array('id','gst_percentage')));
									$gstslabData=array_flip($gstslabData);
									//print_r($unitData);
									//exit;
									if(!empty($sheetone))
									{
										$success=0;
										foreach($sheetone as $k=>$row)
										{
											$line=$k+1;
											$itemName=$row[0];
											$itemUnit=$row[1];
											$itemMrp=$row[2];
											$itemSp=$row[3];
											$hsn=$row[4];
											$itemGstSlab=$row[5];
											$unitid="";
											if(!empty($unitData[$itemUnit]))
											{
												$unitid=$unitData[$itemUnit];
											}
											$gstslabid="";
											$itemGstSlab=number_format($itemGstSlab,2);
											if(!empty($gstslabData[$itemGstSlab]))
											{
												
												$gstslabid=$gstslabData[$itemGstSlab];
											}
											if(!empty($itemName) or !empty($unitid) or !empty($gstslabid))
											{
												$this->Item->create();
												$this->request->data['Item']['user_profile_id']=$user_profile_id;
												$this->request->data['Item']['name']=$itemName;
												$this->request->data['Item']['unit']=$unitid;
												$this->request->data['Item']['item_type']=BOOL_FALSE;
												$this->request->data['Item']['price']=!empty($itemMrp)?$itemMrp:0;
												$this->request->data['Item']['sp']=!empty($itemSp)?$itemSp:0;
												$this->request->data['Item']['hsn']=!empty($hsn)?$hsn:'';
												$this->request->data['Item']['gst_slab']=$itemGstSlab;
												$this->request->data['Item']['gst_slab_id']=$gstslabid;
												if($this->Item->save($this->request->data['Item'])){
													$success++;
												}else{
													$errors=$this->Item->validationErrors;
													$nameerrors=$errors['name'][0];
													if(!empty($nameerrors))
													{
															echo json_encode(array('status' => 1004, 'message' =>'Item alredy exist , row number '.$line.' . Remove this item and try again'));
															return;
													}
													
												}
											
											}else{
												
												echo json_encode(array('status' => 1003, 'message' =>'Something wrong in row number '.$line.' !!'));
												return;
												
											}
											
										}
									}
									echo json_encode(array('status' => 1000, 'message' => $success.' items Created Successfully...!!'));
								}
							} else {
								echo json_encode(array('status' => 1001, 'message' => 'Invalid file Size or Type.', 'imgpath' => ""));
							}
						} else {
							echo json_encode(array('status' => 1002, 'message' => 'Invalid file Size or Type.', 'imgpath' => ""));
						}
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	
	
	

}