<?php

/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');
App::uses('AuthComponent', 'Controller/Component');
/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */

class CommonsController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
 	
    public $helpers = array(
        'Date','Jqimageresize'//helper for accessing auth user
    );
    public $uses = array(
        'SiteContent'
    );
	
	public $components = array('Paginator','Files','Img','Unitchange');
	
	public function beforeFilter() 
	{
        parent::beforeFilter();
        $this->Auth->allow('sort_array_of_array','getStates','getDistricts','getCities','getTalukas','getVillages','generateRandomString','unique_mobile','unique_email','hide_mail','hide_phone','updateSession','getProductList','countSpProducts','ProductPrice','getSupplierList','getItemQty','addCategory','autoCompleteItems','getDiscountLevel','getItemDetail','getCreditorsDiscountLevel');
    }	
	
	/*
	@ Mohammad Masood 
	@ Get List of States of selected country
	@ 25-05-2016
	*/
	public function getStates() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('State');
			$states = array();
			if (isset($this->request['data']['id'])) 
			{
				$states = $this->State->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'State.country_id' => $this->request['data']['id'],
					'State.is_deleted' => BOOL_FALSE,
					'State.is_active' => BOOL_TRUE,					
					),
					'order'=>array('State.name'=>'ASC')
				));
				$str='<option value="">Select State</option>';
				foreach($states as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}			
		}			
	}
	
	/*
	@ Mohammad Masood 
	@ Get List of Districts on selecting state
	@ 25-05-2016
	*/
	public function getDistricts() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('City');
			$cities = array();
			if (isset($this->request['data']['id'])) 
			{
				$cities = $this->City->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'City.state_id' => $this->request['data']['id'],
					'City.is_district' => BOOL_TRUE,		
					'City.is_deleted' => BOOL_FALSE,
					'City.is_active' => BOOL_TRUE,					
					),
					'order'=>array('City.name'=>'ASC')
				));
				$str='<option value="">Select District</option>';
				foreach($cities as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}			
		}			
	}	
	
	/*
	@ Mohammad Masood 
	@ Get List of Cities on selecting state
	@ 25-05-2016
	*/
	public function getCities() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('City');
			$cities = array();
			if (isset($this->request['data']['id'])) 
			{
				$cities = $this->City->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'City.state_id' => $this->request['data']['id'],
					'City.is_deleted' => BOOL_FALSE,
					'City.is_active' => BOOL_TRUE,					
					),
					'order'=>array('City.name'=>'ASC')
				));
				
				$str='<option value="">Select District</option>';
				foreach($cities as $k=>$v){
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();				
			}			
		}			
	}	
	
	/*
	@ Mohammad Masood 
	@ Get List of Taluks on selecting Cities
	@ 25-05-2016
	*/
	public function getTalukas() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('Taluka');
			$talukas = array();
			if (isset($this->request['data']['id'])) 
			{
				$talukas = $this->Taluka->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'Taluka.city_id' => $this->request['data']['id'],
					'Taluka.is_deleted' => BOOL_FALSE,
					'Taluka.is_active' => BOOL_TRUE,					
					),
					'order'=>array('Taluka.name'=>'ASC')
				));
				$str='<option value="">Select Taluka</option>';
				foreach($talukas as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}
	
	/*
	@ Mohammad Masood 
	@ Get List of Villages on selecting Taluks
	@ 25-05-2016
	*/
	public function getVillages() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('Village');
			$villages = array();
			if (isset($this->request['data']['id'])) 
			{
				$villages = $this->Village->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'Village.taluka_id' => $this->request['data']['id'],
					'Village.is_deleted' => BOOL_FALSE,
					'Village.is_active' => BOOL_TRUE,					
					),
					'order'=>array('Village.name'=>'ASC')
				));
				$str='<option value="">Select Village/Town</option>';
				foreach($villages as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}
	
	/**
	@ Mohammad Masood
	@ Function to check unique email Id
	@ 06-06-2016
	**/
	public function unique_email()
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
	
	
	/**
	@ Mohammad Masood
	@ Function to check unique mobile number
	@ 06-06-2016
	**/
	public function unique_mobile()
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
	
	/*
	Kajal kurrewar
	12-09-2017
	Unique hsn for each Item
	*/
	public function unique_hsnItem()
	{		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');
		
        if ($this->request->is('ajax')) 
		{
			
			$count=$this->Item->find('count',array(
			'conditions'=>array(
				'Item.hsn'=>$this->request->data['Item']['hsn'],
				'Item.is_deleted'=>BOOL_FALSE
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
	
	
	/*
	@ Mohammad Masood 
	@ updateSessions 
	@ 25-05-2016
	*/
	public function updateSession() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('UserSession');
			$sessionUser=$this->Session->read('Auth.User.id');	
			if (!empty($sessionUser)) 
			{
			
				$this->UserSession->id=$this->Session->read('Auth.User.UserSession.id');
				if($this->UserSession->exists())
				{
					
					$array=array('current_time'=>date('H:i:s'));
					if($this->UserSession->save($array))
					{
						header('Content-Type: application/json');				
						echo json_encode(array('status'=>'1'));		
						exit;
					}
				}
				
				exit();
				
			}			
		}			
	}	
	
	
	
	public function getSupplierList() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('Supplier');
			
			$suppliers = $this->Supplier->find('list', array(
			'fields' => array('id','name'),
			'conditions' => array(
			'Supplier.id !=' => BOOL_FALSE,
			'Supplier.is_deleted' => BOOL_FALSE,
			'Supplier.is_active' => BOOL_TRUE,					
			),
			'order'=>array('Supplier.name'=>'ASC')
			));
			$str='<option value="">Select Supplier</option>';
			foreach($suppliers as $k=>$v)
			{
			$str.='<option value="'.$k.'">'.$v.'</option>';
			}
			header('Content-Type: application/json');
			echo json_encode(array('data'=>$str));
			exit();
			
			
			
		}	
		
	}
	
	/*
	@ Mohammad Masood 
	@ Get List of Taluks on selecting Cities
	@ 25-05-2016
	*/
	public function getProductList() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('Product');
			$talukas = array();
			if (isset($this->request['data']['supplier_id'])) 
			{
				$products = $this->Product->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'Product.supplier_id' => $this->request['data']['supplier_id'],
					'Product.quantity > ' => 0 ,
					'Product.is_deleted' => BOOL_FALSE,
					'Product.is_active' => BOOL_TRUE,					
					),
					'order'=>array('Product.name'=>'ASC')
				));
				$str='<option value="">Select Product</option>';
				foreach($products as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}
	
	/*
	@ Mohammad Masood 
	@ Get List of Taluks on selecting Cities
	@ 25-05-2016
	*/
	public function countSpProducts() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{					
			$this->loadModel('Product');
			$talukas = array();
			if (isset($this->request['data']['supplier_id'])) 
			{
				$count = $this->Product->find('count', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'Product.supplier_id' => $this->request['data']['supplier_id'],
					'Product. ' => BOOL_FALSE,
					'Product.is_active' => BOOL_TRUE,					
					),
					'order'=>array('Product.name'=>'ASC')
				));
				
				
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$count));
				exit();
			}
			
		}	
		
	}
	

	public function ProductPrice() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('Product');
			$talukas = array();
			if (isset($this->request['data']['id'])) 
			{
				$product = $this->Product->find('first', array(
					'fields' => array('purchase_price','sales_price'),
					'conditions' => array(
					'Product.id' => $this->request['data']['id'],
					'Product.is_deleted' => BOOL_FALSE,
					'Product.is_active' => BOOL_TRUE,					
					),					
				));
				
				$str = array('purchase_price'=>$product['Product']['purchase_price'], 'sales_price'=>$product['Product']['sales_price']);
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}
	
	public function getItemQty() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{					
			$this->loadModel('Stock');
			$this->loadModel('Item');
			$array = array();
			$unit=$this->request['data']['unit'];
			if (isset($this->request['data']['id'])) 
			{
				$count = $this->Stock->find('first', array(
					'fields' => array('Stock.quantity'),
					'conditions' => array(					
					'Stock.id !=' => BOOL_FALSE,
					'Stock.item_id' => $this->request['data']['id'],
				
					'Stock.is_deleted' => BOOL_FALSE,
					'Stock.is_active' => BOOL_TRUE,					
					),
					'recursive' => -1
				));
				$itemsData=$this->Item->findById($this->request['data']['id']);
			
					
				// Find Unit type
				$main_unit=BOOL_FALSE;				
				$alt_unit=BOOL_FALSE;				
				if($unit==$itemsData['Item']['unit'])
				{
					$main_unit=BOOL_TRUE;
				}elseif($unit==$itemsData['Item']['alt_unit'])
				{
					$alt_unit=BOOL_TRUE;
				}
				// End find Unit type	
				$limit="";
				if(!empty($itemsData))
				{
					$limit=$itemsData['Item']['min_stock_limit'];
				}
				if($itemsData['Item']['item_type']==SERVICES_TYPE)
					{
						$count['Stock']['quantity']=100000;
					}
							
				header('Content-Type: application/json');
				
				if(!empty($count)){
					$stockOld=$count['Stock']['quantity'];
									
					$value=$this->Unitchange->changeReverse( $this->request['data']['id'],$count['Stock']['quantity'],$itemsData['Item']['unit']);
				
					echo json_encode(array('data'=>$stockOld,'small_unit'=>$value['qty'],'limit'=>$limit,'main_unit'=>$main_unit,'alt_unit'=>$alt_unit));
				}
				else{
					
					echo json_encode(array('data'=>0,));
				}
				
				exit();
			}
			
		}	
		
	}
	/*
	Amit Sahu
	Add Category (Master)
	28.01.17
	*/
public function addCategory()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Category');
		//$this->admin_check_login();				
		
		if ($this->request->is('ajax')) 
			{
				$this->Category->create();			
				if ($this->Category->save($this->request->data)) 
				{
					
						$id=$this->Category->getInsertID();
						$name=$this->request->data['Category']['name'];
						$parent_id=$this->request->data['Category']['parent_id'];
						
						$parentName="";
						if(!empty($parent_id))
						{
							$catdata=$this->Category->findById($parent_id);
							$parentName=$catdata['Category']['name'];
						}
						$rc_discount=$this->request->data['Category']['rc_discount'];
					echo json_encode(array('status'=>'1000','message'=>'Category added successfully', 'id'=>$id,'name'=>$name,'parent_id'=>$parent_id,'parent'=>$parentName,'rc_discount'=>$rc_discount));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Category could not be added'));
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
	Edit Category (Master)
	28.01.17
	*/
public function editCategory()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Category');
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Category']['id'];
				if(!empty($id))
					{
						if ($this->Category->save($this->request->data)) 
						{
								$name=$this->request->data['Category']['name'];
								$parent_id=$this->request->data['Category']['parent_id'];
								
								$parentName="";
								if(!empty($parent_id))
								{
								$catdata=$this->Category->findById($parent_id);
								$parentName=$catdata['Category']['name'];
								}
								$rc_discount=$this->request->data['Category']['rc_discount'];
							
							echo json_encode(array('status'=>'1000','message'=>'Category edit successfully','id'=>$id,'name'=>$name,'parent_id'=>$parent_id,'parent'=>$parentName,'rc_discount'=>$rc_discount));
								
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Category could not be added'));
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
	28.01.17
	Delete Category
	*/
	
	public function deleteCategory() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Category');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Category->id =$id;
				if (!$this->Category->exists()) 
				{
					throw new NotFoundException('Invalid Category');
				}
				
						$child=$this->Category->countCategoryParentIdWise($id);
						if($child==BOOL_FALSE)
						{
							   if ($this->Category->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Category->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Category Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Category could not be Deleted'));
							   }
						}else{
							echo json_encode(array('status'=>'1001','message'=>'Please check child category '));
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
	Add Publisher (Master)
	28.01.17
	*/
public function addPublisher()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Publisher');
		$this->loadModel('Category');
		
		if ($this->request->is('ajax')) 
			{
				$city=trim($this->request->data['Publisher']['city']);		
				if(empty($city)){
					unset($this->request->data['Publisher']['city']);
				}
				$this->Publisher->create();	
		
				if ($this->Publisher->save($this->request->data)) 
				{

						$id=$this->Publisher->getInsertID();
						$name=$this->request->data['Publisher']['name'];
						$address=$this->request->data['Publisher']['address'];
						$mobile=$this->request->data['Publisher']['mobile'];
						$email=$this->request->data['Publisher']['email'];
						$crOpBlnc=$this->request->data['Publisher']['crop_blnc'];
						$debOpBlnc=$this->request->data['Publisher']['debop_blnc'];
						$deposit=$this->request->data['Publisher']['deposit'];
                         
						
					echo json_encode(array('status'=>'1000','message'=>'Publisher added successfully', 'id'=>$id,'name'=>$name,'address'=>$address,'mobile'=>$mobile,'email'=>$email,'crop_blnc'=>$crOpBlnc,'debop_blnc'=>$debOpBlnc,'deposit'=>$deposit));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Publisher could not be added'));
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
	Edit Publisher (Master)
	28.01.17
	*/
	public function editPublisher()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Publisher');
		$this->loadModel('Category');
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Publisher']['id'];
				if(!empty($id))
					{
						if ($this->Publisher->save($this->request->data)) 
						{
								$name=$this->request->data['Publisher']['name'];
								$address=$this->request->data['Publisher']['address'];
								$mobile=$this->request->data['Publisher']['mobile'];
								$email=$this->request->data['Publisher']['email'];
								$crOpBlnc=$this->request->data['Publisher']['crop_blnc'];
								$debOpBlnc=$this->request->data['Publisher']['debop_blnc'];
								$deposit=$this->request->data['Publisher']['deposit'];
								
								
							echo json_encode(array('status'=>'1000','message'=>'Publisher edit successfully', 'id'=>$id,'name'=>$name,'address'=>$address,'mobile'=>$mobile,'email'=>$email,'crop_blnc'=>$crOpBlnc,'debop_blnc'=>$debOpBlnc,'deposit'=>$deposit));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Publisher could not be added'));
						}
					}
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
			
	}
	
	public function setPublishersDiscount()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Publisher');
		$this->loadModel('PublishersDiscount');
		
		if ($this->request->is('ajax')) 
			{
				$discount_id ='';
				$publisher_id = $this->request->data['Publisher']['publisher_id'];				
				if(!empty($publisher_id))
				{
					
					foreach($this->request->data['PublishersDiscountBooks'] as $k=>$v){
						
						$discount_id ='';
						$discount_id = $v['discount_id'];
						$arr = array(
								"publisher_id"=>$publisher_id,
								"category_id"=>BOOKS_CATEGORY,
								"discount_level"=>$v['discount_level'],
								"discount_percent"=>$v['discount_percent'],
						 );
						if(!empty($discount_id)){
							$this->PublishersDiscount->id = $discount_id;
						}
						else{
							$this->PublishersDiscount->create();
						}

						$this->PublishersDiscount->save($arr);
					}
					foreach($this->request->data['PublishersDiscountMags'] as $k=>$v){
						
						$discount_id ='';
						$discount_id = $v['discount_id'];
						$arr1 = array(
								"publisher_id"=>$publisher_id,
								"category_id"=>MAGZINE_CATEGORY,
								"discount_level"=>$v['discount_level'],
								"discount_percent"=>$v['discount_percent'],
						 );
						if(!empty($discount_id)){
							$this->PublishersDiscount->id = $discount_id;
						}
						else{
							$this->PublishersDiscount->create();
						}

						$this->PublishersDiscount->save($arr1);
					}

					echo json_encode(array('status'=>'1000','message'=>'Publisher\'s Discount added successfully'));
				}
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Discount could not be added'));
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
	28.01.17
	Delete Category
	*/
	
	public function deletePublisher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Publisher');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Publisher->id =$id;
				if (!$this->Publisher->exists()) 
				{
					throw new NotFoundException('Invalid Publisher');
				}
					   if ($this->Publisher->saveField('is_deleted',BOOL_TRUE)) 
					   {
							$this->Publisher->saveField('is_active',BOOL_FALSE);
						echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Publisher Deleted successfully'));
					   }else
					   {
						   echo json_encode(array('status'=>'1001','message'=>'Publisher could not be Deleted'));
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
	Add Author (Master)
	28.01.17
	*/
public function addAuthor()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Author');
					
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Author->create();			
				if ($this->Author->save($this->request->data)) 
				{
					
						$id=$this->Author->getInsertID();
						$name=$this->request->data['Author']['name'];
						
						
					echo json_encode(array('status'=>'1000','message'=>'Author added successfully', 'id'=>$id,'name'=>$name));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Author could not be added'));
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
	Edit Author (Master)
	28.01.17
	*/
public function editAuthor()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Author');
					
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Author']['id'];
				if(!empty($id))
					{
						if ($this->Author->save($this->request->data)) 
						{
								$name=$this->request->data['Author']['name'];
								
							echo json_encode(array('status'=>'1000','message'=>'Author edit successfully', 'id'=>$id,'name'=>$name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Author could not be added'));
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
	28.01.17
	Delete Category
	*/
	public function deleteAuthor() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Author');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Author->id =$id;
				if (!$this->Author->exists()) 
				{
					throw new NotFoundException('Invalid Author');
				}
				
						
							   if ($this->Author->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Author->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Author Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Author could not be Deleted'));
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
	Add Distributor (Master)
	28.01.17
	*/
	//neha Umredkar 30/08/2017
public function addDistributor()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Distributor');
		$this->loadModel('State');
					
		
		if ($this->request->is('ajax')) 
			{
			
				$this->Distributor->create();
				$this->request->data['Distributor']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
				if ($this->Distributor->save($this->request->data)) 				{
					
						$id =$this->Distributor->getInsertID();
						$name=$this->request->data['Distributor']['name'];
						$gstn=$this->request->data['Distributor']['gstin'];
						$address=$this->request->data['Distributor']['address'];
						$email=$this->request->data['Distributor']['email'];
						$mobile=$this->request->data['Distributor']['mobile'];	
						$state=$this->request->data['Distributor']['state'];	
						$stateData=$this->State->findById($state);
						$state_name="";
						if(!empty($stateData))
						{
							$state_name=$stateData['State']['name'];
						}

													
					echo json_encode(array('status'=>'1000','message'=>'Distributor added successfully', 'id'=>$id,'name'=>$name,'gstn'=>$gstn,'address'=>$address,'email'=>$email,'mobile'=>$mobile,'state'=>$state,'state_name'=>$state_name));
				} 
				else 
				{
					//$errors = $this->Distributor->validationErrors;
					//print_r($errors);
					//$exiterr=$errors['payhead_id'][0];
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
	Amit Sahu
	Edit Distributor (Master)
	29.01.17
	*/
public function editDistributor()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('State');
		$this->loadModel('Distributor');
					
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Distributor']['id'];
				if(!empty($id))
					{
						if ($this->Distributor->save($this->request->data)) 
						{
							$name=$this->request->data['Distributor']['name'];
							$gstn=$this->request->data['Distributor']['gstin'];
							$address=$this->request->data['Distributor']['address'];
							$email=$this->request->data['Distributor']['email'];
							$mobile=$this->request->data['Distributor']['mobile'];	
								$state=$this->request->data['Distributor']['state'];	
						$stateData=$this->State->findById($state);
						$state_name="";
						if(!empty($stateData))
						{
							$state_name=$stateData['State']['name'];
						}
							echo json_encode(array('status'=>'1000','message'=>'Distributor edit successfully','id'=>$id,'name'=>$name,'address'=>$address,'email'=>$email,'mobile'=>$mobile,'gstn'=>$gstn,'state'=>$state,'state_name'=>$state_name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Distributor could not be added'));
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
	29.01.17
	Delete Distributor
	*/
	
	public function deleteDistributor() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Distributor');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Distributor->id =$id;
				if (!$this->Distributor->exists()) 
				{
					throw new NotFoundException('Invalid Distributor');
				}
				
						
							   if ($this->Distributor->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Distributor->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Distributor Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Distributor could not be Deleted'));
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
	Add Item
	28.01.17
	*/
     public function addItem()
		{		
			$this->autoRender = FALSE;
			$this->layout = 'ajax';
			$this->loadModel('Item');
			$this->loadModel('Unit');
			$this->loadModel('UserProfile');
			$this->loadModel('GstMaster');
		    
				
			if ($this->request->is('ajax')) 
				{
					
		            $UserProfile=$this->Session->read('UserProfile');
		            if(!empty($UserProfile) and $UserProfile['UserProfile']['unit_type']==BOOL_TRUE)
					{
					   $this->request->data['Item']['unit']=$UserProfile['UserProfile']['unit_id'];
					}
					$this->request->data['Item']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
					
					$slab_id=$this->request->data['Item']['gst_slab_id'];
					$gst_percentage=0;
					if(!empty($this->request->data['Item']['gst_slab_id']))
					{
						$gst_percentage_data=$this->GstMaster->findById($slab_id,array('GstMaster.gst_percentage'));
						$gst_percentage=$gst_percentage_data['GstMaster']['gst_percentage'];
					}
					$this->request->data['Item']['gst_slab']=$gst_percentage;
					
					$this->Item->create();			
					if ($this->Item->save($this->request->data)) 
					{
						$id=$this->Item->getInsertID();
						$name=$this->request->data['Item']['name'];
						$hsn=$this->request->data['Item']['hsn'];
						$gst_slab=$this->request->data['Item']['gst_slab'];
						$sp=$this->request->data['Item']['sp'];
						$unitname="";
						$unitdata="";
						$altunitname="";
						$altunitdata="";
						
						$unitId=$this->request->data['Item']['unit'];
						if(!empty($unitId))
						{
							$unitdata=$this->Unit->findById($unitId);
							$unitname=$unitdata['Unit']['name'];
						}
						if(!empty($this->request->data['Item']['alt_unit']))
						{
						$altunitId=$this->request->data['Item']['alt_unit'];
						if(!empty($altunitId))
						{
							$altunitdata=$this->Unit->findById($altunitId);
							$altunitname=$altunitdata['Unit']['name'];
						}
						}
						$alertQty='';
						$price=number_format($this->request->data['Item']['price'],2);
						
						echo json_encode(array('status'=>'1000','message'=>'Item added successfully','id'=>$id,'name'=>$name,'price'=>$price,'hsn'=>$hsn,'gst_slab'=>$gst_percentage,'unit_id'=>$unitId,'unitname'=>$unitname,'altUnit'=>$altunitdata,'altunitName'=>$altunitname,'sp'=>$sp,'slab_id'=>$slab_id));
					} 
					else 
					{
						//$errors = $this->Item->validationErrors;
					   // $exithsn=$errors['hsn'][0];
						echo json_encode(array('status'=>'1001','message'=>'Item could not be added'));
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
	Edit item (Master)
	30.01.17
	*/
public function editItem()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Item');
		$this->loadModel('Unit');
		$this->loadModel('GstMaster');
		
		if ($this->request->is('ajax')) 
			{
				
				$id=$this->request->data['Item']['id'];
				if(!empty($id))
					{
						$slab_id=$this->request->data['Item']['gst_slab_id'];
						$gst_percentage=0;
						if(!empty($this->request->data['Item']['gst_slab_id']))
						{
							$gst_percentage_data=$this->GstMaster->findById($slab_id,array('GstMaster.gst_percentage'));
							$gst_percentage=$gst_percentage_data['GstMaster']['gst_percentage'];
						}
						$this->request->data['Item']['gst_slab']=$gst_percentage;
					   if ($this->Item->save($this->request->data)) 
						{
						
						$name=$this->request->data['Item']['name'];
						$price=number_format($this->request->data['Item']['price'],2);
						
						$hsn=$this->request->data['Item']['hsn'];
						$gst_slab=$this->request->data['Item']['gst_slab'];
						$sp=$this->request->data['Item']['sp'];
						 
						
							
						    $unitname="";
							if(!empty($unitId))
							{
								$unitdata=$this->Unit->findById($unitId);
								$unitname=$unitdata['Unit']['name'];
							}
							
					    echo json_encode(array('status'=>'1000','message'=>'Item edit successfully', 'id'=>$id,'name'=>$name,'price'=>$price,'hsn'=>$hsn,'gst_slab'=>$gst_percentage,'unitname'=>$unitname,'sp'=>$sp,'slab_id'=>$slab_id));
					
						} 
						else 
						{
							
							echo json_encode(array('status'=>'1001','message'=>'Item could not be added'));
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
	Delete Item
	*/
	
	public function deleteItem() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Item');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Item->id =$id;
				if (!$this->Item->exists()) 
				{
					throw new NotFoundException('Invalid Item');
				}
				
						
							   if ($this->Item->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Item->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Item Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Item could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Get GST parcentage by id
	Amit Sahu
	27.08.18	
	*/
	public function getGstParcentage() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('GstMaster');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];			
				
				$gstData=$this->GstMaster->findById($id,array('GstMaster.gst_percentage'));
			   if (!empty($gstData)) 
			   {
				   $value=$gstData['GstMaster']['gst_percentage'];
				
				echo json_encode(array('status'=>'1000','value'=>$value));
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
	Add Ledger
	28.01.17
	*/
public function addLedger()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		$this->loadModel('Group');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Ledger->create();			
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
						$gst_rate=$this->request->data['Ledger']['gst_rate'];
						$levy_tax=$this->request->data['Ledger']['levy_tax'];
						$reverse_charge=$this->request->data['Ledger']['reverse_charge'];
						$eligible_credit=$this->request->data['Ledger']['eligible_credit'];
						
						
					echo json_encode(array('status'=>'1000','message'=>'Ledger added successfully', 'id'=>$id,'ledger_name'=>$name,'group_name'=>$group_name,'levy_tax'=>$levy_tax,'reverse_charge'=>$reverse_charge,'eligible_credit'=>$eligible_credit,'gst_rate'=>$gst_rate));
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
	Edit Ledger (Master)
	30.01.17
	*/
public function editLedger()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		$this->loadModel('Group');
		
					
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Ledger']['id'];
				if(!empty($id))
					{
						if ($this->Ledger->save($this->request->data)) 
						{
							
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
						$gst_rate=$this->request->data['Ledger']['gst_rate'];
						$levy_tax=$this->request->data['Ledger']['levy_tax'];
						$reverse_charge=$this->request->data['Ledger']['reverse_charge'];
						$eligible_credit=$this->request->data['Ledger']['eligible_credit'];		
							    
					echo json_encode(array('status'=>'1000','message'=>'Ledger update successfully', 'id'=>$id,'ledger_name'=>$name,'group_name'=>$group_name,'levy_tax'=>$levy_tax,'reverse_charge'=>$reverse_charge,'eligible_credit'=>$eligible_credit,'gst_rate'=>$gst_rate));
							
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Ledger could not be added'));
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
	@Created By : Mohammad Masood
	@Created On : 15 Feb 2016
	**/
	public function autoCompleteItems() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');		
		$this->loadModel('Stock');		
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
			$query=trim($_REQUEST['query']);
			if(isset($query) and !empty($query))
			{
				$or_cond = array();
				$or_cond['OR']['Item.name LIKE'] = $_REQUEST['query'].'%';

				
				if(is_numeric($_REQUEST['query'])){
					$or_cond['OR']['Item.price'] = $_REQUEST['query'];
				}
				$items=$this->Item->find('all',array(
					"fields"=>array("Item.id","Item.code","Item.name","Item.price","Item.min_stock_limit"),
					'conditions'=>array(
					"OR"=>$or_cond,
					'Item.id !='=>BOOL_FALSE,
					'Item.is_deleted'=>BOOL_FALSE,
					'Item.is_active'=>BOOL_TRUE,									
					),
				
					"recursive"=>-1
					));									
					
					foreach($items as $row)
					{	
						
						//Get Stock Amit
						$stock=0;
						$conditions=array('Stock.id !='=>BOOL_FALSE,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE,'Stock.item_id'=>$row['Item']['id'],'Stock.location_id'=>$this->Session->read('Auth.User.location_id'));
						$fields=array('Stock.quantity');
						$stockData=$this->Stock->getallStock($conditions,$fields);
						if(!empty($stockData))
						{
							$stock=$stockData['Stock']['quantity'];
						}
						//End Get stock Amit
						$value_suffix=$row['Item']['id'].'<<>>'.$row['Item']['price'].'<<>>'.$row['Item']['code'].'<<>>'.$stock.'<<>>'.$row['Item']['min_stock_limit'];
									
						$value=ucfirst($row['Item']['name'])."<<>><<>>Rs.".$row['Item']['price'];
						$arr[$value_suffix]=$value;
					}
					
				if(!empty($arr))	
				{
					asort($arr);				
				}	
				$str='';
				
				
				if(!empty($arr))
				{
					foreach($arr as $k=>$v)
					{
						$suggestions[]=array('data'=>$k,'value'=>$v);
					}					
					echo json_encode(array('suggestions'=>$suggestions));
				}
				else
				{
					echo json_encode(array('suggestions'=>array(array('data'=>'','value'=>''))));
				}
			}
			
			exit;
			
		}	
	}
	/**
	@Created By : Amit Sahu
	@Created On : 30.03.17
	**/
	public function autoCompleteILimitedtems() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');		
		$this->loadModel('Stock');		
			
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
			$no=trim($this->request->data['no']);
			$query=trim($this->request->data['value']);
			$qrArr=explode(',',$query);
			$query=$qrArr[0];
			$pid="";
			if(count($qrArr)==2)
			{
			$pid=$qrArr[1];
			}
			
			$no=trim($this->request->data['no']);
			
			if(isset($query) and !empty($query))
			{
				$or_cond = array();
				
				if(!empty($pid))
				{
					
					$or_cond['AND']['Item.price'] = $query;
				}else{
				$or_cond['OR']['Item.name LIKE'] ='%'.$query.'%';
				$or_cond['OR']['Item.price LIKE'] = $query.'%';
				$or_cond['OR']['Item.code LIKE'] = $query.'%';
				}
			
				$items=$this->Item->find('all',array(
					"fields"=>array("Item.id","Item.code","Item.name","Item.price","Item.min_stock_limit","Item.hsn"),
					'conditions'=>array(
					"OR"=>$or_cond,
					'Item.id !='=>BOOL_FALSE,
					'Item.is_deleted'=>BOOL_FALSE,
					'Item.is_active'=>BOOL_TRUE,
					'Item.user_profile_id' => $this->Session->read('Auth.User.user_profile_id')					
					),
					
					"recursive"=>-1,
					'limit'=>15,
					'order'=>array('Item.id'=>'ASC','Item.code'=>'ASC')
					));									
				
				$table="";
			
				if(!empty($items))
				{
					foreach($items as $row)
					{
						$id="'".$row['Item']['id']."'";
					
						$table.='<tr onClick="onselectItem('.$id.','.$no.')" id-no="'.$row['Item']['id'].','.$no.'"><td><input type="text" class="hidden_input_row">'.$row['Item']['hsn'].'</td><td>'.$row['Item']['name'].'</td><td>'.$row['Item']['price'].'</td></tr>';
						$lastId=$row['Item']['id'];
					}
				
					echo json_encode(array('table'=>$table,'status'=>1000,'lastId'=>$lastId));
				}
				else
				{
					echo json_encode(array('suggestions'=>array(array('data'=>'','value'=>''))));
				}
			}
			
		}	
	}
	
function subval_sort($a,$subkey) {
    foreach($a as $k=>$v) {
        $b[$k] = strtolower($v[$subkey]);
    }
    asort($b);
    foreach($b as $key=>$val) {
        $c[$key] = $a[$key];
    }
    return $c;
}
	/*
	Amit 
	30.03.17
	LoadMore Item
	*/
	public function loadMoreItem()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Item');
		
		
		if ($this->request->is('ajax')) 
			{
				
			$last=trim($this->request->data['id']);
			$query=trim($this->request->data['value']);
			$qrArr=explode(',',$query);
			$query=$qrArr[0];
			$pid="";
			if(count($qrArr)==2)
			{
			$pid=$qrArr[1];
			}
			$no=trim($this->request->data['no']);
			if(isset($query) and !empty($query))
			{
				$or_cond = array();
				if(!empty($pid))
				{
					$or_cond['AND']['Item.publisher_id'] = trim($pid);
					$or_cond['AND']['Item.price'] = $query;
				}else{
				$or_cond['OR']['Item.name LIKE'] =$query.'%';
				$or_cond['OR']['Item.price LIKE'] = $query.'%';
				$or_cond['OR']['Item.code LIKE'] = $query.'%';
				}
				
				$items=$this->Item->find('all',array(
					"fields"=>array("Item.id","Item.code","Item.name","Item.price","Item.min_stock_limit"),
					'conditions'=>array(
					"OR"=>$or_cond,
					'Item.id !='=>BOOL_FALSE,
					'Item.id >'=>$last,
					'Item.is_deleted'=>BOOL_FALSE,
					'Item.is_active'=>BOOL_TRUE,									
					),
					"contain"=>array(
						"Publisher"=>array(
							"fields"=>array("Publisher.id","Publisher.name"),
						),
						
					),
					"recursive"=>-1,
					'limit'=>15,
					'order'=>array('Item.id'=>'ASC','Item.code'=>'ASC')
					));									
					
			
				$table="";
				if(!empty($items))
				{
					foreach($items as $row)
					{
						$id="'".$row['Item']['id']."'";
						
						$table.='<tr onClick="onselectItem('.$id.','.$no.')" id-no="'.$row['Item']['id'].','.$no.'"><td><input type="text" class="hidden_input_row">'.$row['Item']['code'].'</td><td>'.$row['Item']['name'].'</td><td>'.$row['Item']['price'].'</td></tr>';
						$lastId=$row['Item']['id'];
					}
				
					echo json_encode(array('table'=>$table,'status'=>1000,'lastId'=>$lastId));
				}
				else
				{
					echo json_encode(array('table'=>'','status'=>1001,'lastId'=>''));
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
	@Created By : Mohammad Masood
	@Created On : 15 Feb 2016
	**/
	public function getDiscountLevel($id = NULL) {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('DiscountLevel');
		$this->loadModel('Item');
		$this->loadModel('PublishersDiscount');
		$this->loadModel('CategoryDiscount');
		$this->loadModel('Member');
		$this->loadModel('ItemsDiscount');
		
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{
			if(!empty($this->request->data["discount_level"])){
			
				$discount_percent = 0;				
				$item_id = $this->request->data["item_id"];
				$discount_level = $this->request->data["discount_level"];
				$is_member = isset($this->request->data["is_member"])?$this->request->data["is_member"]:false;
				$is_coupon = isset($this->request->data["is_coupon"])?$this->request->data["is_coupon"]:false;
				
				$member_id = isset($this->request->data["member_id"])?$this->request->data["member_id"]:false;
				
				if(!empty($item_id)){
					$id = $this->request->data["discount_level"];
					$this->DiscountLevel->id = $id;
					if(!$this->DiscountLevel->exists()){
						throw new NotFoundException("Invalid Selection");
					}
					
					$item_detail = $this->Item->find("first",array(
					"conditions" =>array(
						"Item.id !="=>BOOL_FALSE,
						"Item.id"=>$item_id,
						"Item.is_active"=>BOOL_TRUE,
						"Item.is_deleted"=>BOOL_FALSE,
					),
					"recursive"=> 2,
					));
					
					if(!empty($item_detail)){
						
						if(isset($item_detail["Category"]["PrCategory"]["id"])){
							$cat_id = $item_detail["Category"]["PrCategory"]["id"]; 
							
							$cat_detail = $item_detail["Category"]["PrCategory"];
						}
						else{
							$cat_id = $item_detail["Category"]["id"]; 
						
							$cat_detail = $item_detail["Category"];
						}
						
						if(!empty($item_id))
						{
							$id_detail = $this->ItemsDiscount->find("first",array(
							"conditions" =>array(
							"ItemsDiscount.id !="=>BOOL_FALSE,
							"ItemsDiscount.item_id"=>$item_id,							
							"ItemsDiscount.discount_level"=>$discount_level,
							"ItemsDiscount.is_active"=>BOOL_TRUE,
							"ItemsDiscount.is_deleted"=>BOOL_FALSE,
							),
							"recursive"=> 2,
							));
						}
						if(!empty($id_detail) and !is_null($id_detail["ItemsDiscount"]["discount_percent"])){
							$discount_percent = $id_detail["ItemsDiscount"]["discount_percent"];
						 	}else{
							
						$publisher_id = !empty($item_detail["Publisher"]["id"])?$item_detail["Publisher"]["id"]:'';
						
						if(!empty($publisher_id)){
							
							$pd_detail = $this->PublishersDiscount->find("first",array(
							"conditions" =>array(
							"PublishersDiscount.id !="=>BOOL_FALSE,
							"PublishersDiscount.publisher_id"=>$publisher_id,							
							"PublishersDiscount.discount_level"=>$discount_level,
							"PublishersDiscount.category_id !="=>BOOL_FALSE,
							"PublishersDiscount.category_id"=>$cat_id,
							"PublishersDiscount.is_active"=>BOOL_TRUE,
							"PublishersDiscount.is_deleted"=>BOOL_FALSE,
							),
							"recursive"=> 2,
							));
							if(!empty($pd_detail) and !is_null($pd_detail["PublishersDiscount"]["discount_percent"])){
							$discount_percent = $pd_detail["PublishersDiscount"]["discount_percent"];
						 	}
							else{
								
								$cwd_detail = $this->CategoryDiscount->find("first",array(
								"conditions" =>array(
								"CategoryDiscount.id !="=>BOOL_FALSE,
								"CategoryDiscount.discount_level"=>$discount_level,
								"CategoryDiscount.category_id !="=>BOOL_FALSE,
								"CategoryDiscount.category_id"=>$cat_id,
								"CategoryDiscount.discount_percent !="=>"",
								"CategoryDiscount.is_active"=>BOOL_TRUE,
								"CategoryDiscount.is_deleted"=>BOOL_FALSE,
								),
								"recursive"=> 2,
								));
								
								if(!empty($cwd_detail) and !is_null($cwd_detail["CategoryDiscount"]["discount_percent"])){
									$discount_percent = $cwd_detail["CategoryDiscount"]["discount_percent"];	 		}								
								else{
								
									$detail = $this->DiscountLevel->find("first",array(
									"conditions" =>array(
									"DiscountLevel.id !="=>BOOL_FALSE,
									"DiscountLevel.id"=>$id,
									"DiscountLevel.is_active"=>BOOL_TRUE,
									"DiscountLevel.is_deleted"=>BOOL_FALSE,
									),
									"recursive"=> -1,
									));
									
									$discount_percent = $detail["DiscountLevel"]["discount"];
									if(!empty($detail["DiscountLevel"]['is_offer'])){
									$from_time = strtotime($detail["DiscountLevel"]['offer_from']);
									$to_time = strtotime($detail["DiscountLevel"]['offer_upto']);
									$ctime = strtotime(date("Y-m-d"));
									
									if($ctime <=$to_time and $ctime>=$from_time){
									$discount_percent = $detail["DiscountLevel"]["discount"];
									}
									else{
									$discount_percent = 0;
									}							
									}	
									
									$discount_percent = $detail["DiscountLevel"]["discount"];
								}
							}	
									
							}	

						}
					
					$is_member = $is_member=="true"?true:false;
					
					if($is_member == true){
						if(!empty($member_id)){
							$today = date("Y-m-d");
							$md = $this->Member->find("first",array(
										"conditions"=>array(
										"Member.id"=>$member_id,
										"Member.id !="=>BOOL_FALSE,
										"Member.is_deleted"=>BOOL_FALSE,
										"DATE(Member.valid_upto) >="=>$today,
										)
										));
							if(!empty($md)){
								$discount_percent = $discount_percent + $cat_detail['rc_discount'];		
							}
						}
						
					}
					$is_coupon = $is_coupon=="true"?true:false;
				if($is_coupon==true){
					
							
								$discount_percent = $discount_percent + $cat_detail['rc_discount'];		
							
					}					
					echo json_encode(array("status"=>200,"content"=>$discount_percent));
				}

				}
			}
			exit;
		}
	}

	public function getItemDetail($code = NULL) {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');
		$this->loadModel('Stock');
		$this->loadModel('MinStockLevel');
		
        if ($this->request->is('ajax')) 
		{
			if(!empty($this->request->data["code"])){
				$code = strtoupper($this->request->data["code"]);
								
				$item = $this->Item->find("first",array(
				"conditions" =>array(
					"Item.id !="=>BOOL_FALSE,
					"Item.code"=>$code,
					"Item.is_active"=>BOOL_TRUE,
					"Item.is_deleted"=>BOOL_FALSE,
				),
				'contain'=>array("Category"),
				"recursive"=> -1,
				));
								
						
				if(!empty($item)){
					$levelData=$this->MinStockLevel->find('first',array('conditions'=>array('MinStockLevel.item_id'=>$item['Item']['id'],'MinStockLevel.location_id'=>$this->Session->read('Auth.User.location_id'))));
					if(!empty($levelData))
					{
					$item['Item']['min_stock_limit']=$levelData['MinStockLevel']['quantity'];
					}else{
					$item['Item']['min_stock_limit']=0;
					}
				
					//Get Stock Amit
					$stock=0;
					$conditions=array('Stock.id !='=>BOOL_FALSE,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE,'Stock.item_id'=>$item['Item']['id'],'Stock.location_id'=>$this->Session->read('Auth.User.location_id'));
					$fields=array('Stock.quantity');
					$stockData=$this->Stock->getallStock($conditions,$fields);
					if(!empty($stockData))
					{
						$stock=$stockData['Stock']['quantity'];
					}
					//End Get stock Amit
					echo json_encode(array("status"=>200,"content"=>$item["Item"],'stock'=>$stock));
				}
				else{
					echo json_encode(array("status"=>404,"content"=>"Item not found"));
				}
				
			}
			exit;
		}	
	}
	/*
	Amit Sahu
	01.04.17
	for ajax get item details and stock 
	*/
	public function getItemDetailByID($code = NULL) {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');
		$this->loadModel('Stock');
		$this->loadModel('Unit');
		
        if ($this->request->is('ajax')) 
		{
			if(!empty($this->request->data["id"])){
				$id = strtoupper($this->request->data["id"]);
								
				$item = $this->Item->find("first",array(
				"conditions" =>array(
					"Item.id"=>$id,
					"Item.is_active"=>BOOL_TRUE,
					"Item.is_deleted"=>BOOL_FALSE,
				),
				'contain'=>array("Category",'Unit'=>array('code','id'),'AltUnit'=>array('code','id'),'CessMaster'=>array('code','name'),'GstMaster'=>array('cgst','sgst','igst')),
				"recursive"=> -1,
				
				));	
				$options="";
				if(!empty($item)){
					$cgst=$item['GstMaster']['cgst'];
					$sgst=$item['GstMaster']['sgst'];
					$igst=$item['GstMaster']['igst'];
					// Get Unit for dropdown
					
							$main_unit=$item['Unit']['code'];	
							$options.='<option value="'.$item['Item']['unit'].'">'.$item['Unit']['code'].'</option>';
							$options.='<option value="'.$item['Item']['alt_unit'].'">'.$item['AltUnit']['code'].'</option>';
					
					// End get Unit for dropdown
					//print_r($unitArr);
					//Get Stock Amit
					$stock=0;
					$conditions=array('Stock.id !='=>BOOL_FALSE,'Stock.is_deleted'=>BOOL_FALSE,'Stock.is_active'=>BOOL_TRUE,'Stock.item_id'=>$item['Item']['id']);
					$fields=array('Stock.quantity');
					$stockData=$this->Stock->getallStock($conditions,$fields);
					$cess_amount=0;
					if(!empty($stockData))
					{
						$cess_amount=$this->Cess->getCessAmount($id,1,$item["Item"]['price']);
						$stock=$stockData['Stock']['quantity'];
					}
					
					$cess_code=$item['CessMaster']['code'];
					$cess_name=$item['CessMaster']['name'];
					//End Get stock Amit
					echo json_encode(array("status"=>200,"content"=>$item["Item"],'stock'=>$stock,'options'=>$options,'main_unit'=>$main_unit,'cess_amount'=>$cess_amount,'cess_code'=>$cess_code,'cess_name'=>$cess_name,'cgst'=>$cgst,'sgst'=>$sgst,'igst'=>$igst));
				}
				else{
					echo json_encode(array("status"=>404,"content"=>"Item not found"));
				}
				
			}
			exit;
		}	
	}
	
	/*
	Amit Sahu
	14.02.17
	Update Sale Purchase 
	*/
	public function updatePurchaseSale($psid,$item_id,$type,$category_id,$location_id,$qty,$stock,$price)
	{
		$this->autoRender = FALSE;
        $this->layout = false;
		$this->loadModel('PurchaseSale');
		
		$this->PurchaseSale->create();
		$this->request->data['PurchaseSale']['ps_id']=$psid;
		$this->request->data['PurchaseSale']['item_id']=$item_id;
		$this->request->data['PurchaseSale']['type']=$type;
		$this->request->data['PurchaseSale']['category_id']=$category_id;
		$this->request->data['PurchaseSale']['location_id']=$location_id;
		$this->request->data['PurchaseSale']['qty']=$qty;
		$this->request->data['PurchaseSale']['stock']=$stock;
		$this->request->data['PurchaseSale']['stock_price']=$price;
		$this->PurchaseSale->save($this->request->data['PurchaseSale']);
		
		
	}
	/*
	Amit Sahu
	Add Ocation
	28.01.17
	*/
public function addOcation()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ocation');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Ocation->create();			
				if ($this->Ocation->save($this->request->data)) 
				{
					
						$id=$this->Ocation->getInsertID();
						$name=$this->request->data['Ocation']['name'];
					
						
					echo json_encode(array('status'=>'1000','message'=>'Ocation added successfully', 'id'=>$id,'name'=>$name));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Ocation could not be added'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
		


	/*
	Get pin code by address
	Amit sahu 
	14.03.17
	*/
		public function getPincode($code = NULL) {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		
        if ($this->request->is('ajax')) 
		{
				
					$query = $this->request->data['address'];
					$url = "http://www.getpincode.info/api/pincode?q=" . urlencode ($query);
					$apidata = file_get_contents ($url);
					$json = json_decode ($apidata, true);
					# if no error found, then print pincode
					if (array_key_exists ('error', $json))
					echo 'ERROR';
					else
					$pincode=$json ['pincode'];
				
					echo json_encode(array("status"=>1000,"pincode"=>$pincode));
		}
					
	}
	
		public function memberTransaction() 
	{
		
		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==GODOWN_ROLE_ID){
			$this->layout = ('godown/inner');
		}elseif($roleId==SHOP_ROLE_ID)
		{
			$this->layout = ('shop/inner');
		}else{
		$this->layout = ('office/inner');
		}
		$this->loadModel('Sale');
		$this->loadModel('Member');
		$this->loadModel('Location');
		
		
		$cond=array();
		$memberInfo="";
		$sales=array();
		if(isset($this->request->data['Transaction']))
		{					
			$this->Session->write('MemberTransactionSearch',$this->request->data['Transaction']);
		}
		else
		{	
			$this->request->data['Transaction']=$this->Session->read('MemberTransactionSearch');		
		}	
		if(isset($this->request->data['Transaction']))				
		{
				if(isset($this->request->data['Transaction']['card_no']) and !empty($this->request->data['Transaction']['card_no']))				
			{
				$cond['Sale.card_no']=trim($this->request->data['Transaction']['card_no']);
				$cardNo=$this->request->data['Transaction']['card_no'];
			}	
			
			$memberData=$this->Member->memberFindByCardNo($cardNo);
			if(!empty($memberData))
			{
				$memberInfo='<tr><td class="txt_borderless">Card No : '.$memberData['Member']['card_no'].'</td><td class="txt_borderless">Name: '.$memberData['Member']['name'].' '.$memberData['Member']['middle_name'].' '.$memberData['Member']['last_name'].'</td><td class="txt_borderless">Validity : '.date('d-m-Y',strtotime($memberData['Member']['valid_from'])).' to '.date('d-m-Y',strtotime($memberData['Member']['valid_upto'])).'</td><td class="txt_borderless">Location : '.$memberData['Location']['name'].'</td><td class="txt_borderless"></td></tr>';
			
			
			$conditions = array(
				'Sale.id !=' => BOOL_FALSE,
				'Sale.is_deleted' => BOOL_FALSE,
				'Sale.is_active' => BOOL_TRUE,
			);
			$conditions=array_merge($conditions,$cond);		
			
				$fields=array('Sale.id','Sale.sales_date','Sale.total_amount','Sale.total_payment','Sale.total_balance','Sale.discount_amount','Sale.final_total_amount','Location.name');
					$sales=$this->Sale->getSaleData($conditions,$fields);	
			}else{
				$this->Session->setFlash('Invalid Card No.','error');
			}				
		}
	$this->set(compact('sales'));
	$this->set(compact('memberInfo'));

    }
	/*
	Amit Sahu
	reset member Transaction search
	30.01.17
	*/
	public function resetMemberTransactionSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->office_check_login();	
		
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('MemberTransactionSearch');
			$this->redirect($this->referer()); 
				
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect( array('controller'=>'boffices','action'=>'profitLoss','office'=>true));
			$this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Shau
	Memebr onselect
	*/
	public function getDataByMemberId() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		

		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];			
				$this->loadModel('Ledger');
				$this->Ledger->id =$id;
				$this->Session->write('selected_member',$id);
				if (!$this->Ledger->exists()) 
				{
					throw new NotFoundException('Invalid Member');

				}
				else
				{				
					$data=$this->Ledger->find('first',array('conditions'=>array('Ledger.id'=>$id,'Ledger.is_deleted'=>BOOL_FALSE,'Ledger.is_active'=>BOOL_TRUE),'fields'=>array('Ledger.name','Ledger.id'),'contain'=>array('PartyDetail'=>array('state','city','address','mobile','pin_code','State'=>array('state_no'))),'recursive'=>2));
				
					echo json_encode(array('status'=>'1000','mydata'=>$data,));				

				}
				
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/**
	@Created By : Amit Sahu
	@Created On : 15 March 2016
	**/
	public function autoCompleteMembers() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Member');				
		$this->loadModel('Location');				
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
			$query=trim($_REQUEST['query']);
			if(isset($query) and !empty($query))
			{	
				$members=$this->Member->find('all',array(
					"fields"=>array("Member.id","Member.card_no","Member.name","Member.middle_name","Member.last_name"),
					'conditions'=>array(
					"OR"=>array(
						'Member.name LIKE' =>$_REQUEST['query'].'%',
						'Member.middle_name LIKE' =>$_REQUEST['query'].'%',
						'Member.last_name LIKE' =>$_REQUEST['query'].'%',
						
						),
					'Member.id !='=>BOOL_FALSE,
					'Member.is_deleted'=>BOOL_FALSE,
					'Member.is_active'=>BOOL_TRUE,									
					),
					"contain"=>array(
						"Location"=>array(
							"fields"=>array("Location.id","Location.name"),
						),
					),
					"recursive"=>-1
					));									
					
					foreach($members as $row)
					{	
					
						$value_suffix=$row['Member']['id'].'<<>>'.$row['Member']['card_no'];						
					
						$value=ucfirst($row['Member']['name'])." ".ucfirst($row['Member']['middle_name'])." ".$row['Member']['last_name'];
						$arr[$value_suffix]=$value;
					}
					
				if(!empty($arr))	
				{
					asort($arr);				
				}	
				$str='';
				
				
				if(!empty($arr))
				{
					foreach($arr as $k=>$v)
					{
						$suggestions[]=array('data'=>$k,'value'=>$v);
					}					
					echo json_encode(array('suggestions'=>$suggestions));
				}
				else
				{
					echo json_encode(array('suggestions'=>array(array('data'=>'','value'=>''))));
				}
			}
			
			exit;
			
		}	
	}
	/*
	Amit Sahu
	Get Data by model or id
	17.03.17
	*/
	public function getDataByModelOrId() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		

		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$model= $this->request->data['model'];
				$this->loadModel($model);
				$this->$model->id =$id;
				$this->Session->write('selected_member',$id);
				if (!$this->$model->exists()) 
				{
					throw new NotFoundException('Invalid '.$model);

				}
				else
				{				
					$data=$this->$model->findById($id);
				
					echo json_encode(array('status'=>'1000','mydata'=>$data,));				

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
	Get Data by Distrubutor id
	17.03.17
	*/
	public function getDataDistributorId() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		

		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				
				$this->loadModel('Ledger');
				$this->Ledger->id =$id;
				$this->Session->write('selected_member',$id);
				if (!$this->Ledger->exists()) 
				{
					throw new NotFoundException('Invalid Ledger');

				}
				else
				{				
					$data=$this->Ledger->find('first',array('conditions'=>array('Ledger.id'=>$id),'fields'=>array('id'),'contain'=>array('PartyDetail'=>array('id','gstin','State'=>array('state_no'))),'recursive'=>2));
					
					echo json_encode(array('status'=>'1000','mydata'=>$data,));				

				}
				
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	get item sale order details
	Amit Sahu
	05.06.17
	*/
	public function getSaleOrderDetails() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		

		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->loadModel('SalesOrder');
				$this->SalesOrder->id =$id;
				if (!$this->SalesOrder->exists()) 
				{
					throw new NotFoundException('Invalid Sale Order');

				}else{				
					$data=$this->SalesOrder->find('first',array('conditions'=>array('SalesOrder.id'=>$id,'SalesOrder.is_active'=>BOOL_TRUE,'SalesOrder.is_deleted'=>BOOL_FALSE,'SalesOrder.id !='=>BOOL_FALSE),'recursive'=>2));
				
					echo json_encode(array('status'=>'1000','mydata'=>$data));				

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
	21.03.17
	sale  report
	*/
	public function saleReport() 
	{
		
		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==GODOWN_ROLE_ID){
			$this->layout = ('godown/inner');
		}elseif($roleId==SHOP_ROLE_ID)
		{
			$this->layout = ('shop/inner');
		}else{
		$this->layout = ('office/inner');
		}
		$cond=array();
		$locName="All";
		$from_date="";
		$to_date="";
		$sales=array();	
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('Sale');	
			$this->loadModel('Ledger');	
			$this->loadModel('Location');	
			
			
			$ledgers=$this->Ledger->getLedgerList();	
			$this->set(compact('ledgers'));
			
			$shops=$this->Location->getOnlyShopList();
			$this->set(compact('shops'));
							
			if(isset($this->request->data['SaleReport']))
			{					
				$this->Session->write('SaleReportSearch',$this->request->data['SaleReport']);
			}
			else
			{	
				$this->request->data['SaleReport']=$this->Session->read('SaleReportSearch');		
			}		
			if(isset($this->request->data['SaleReport']))				
			{			
				if(isset($this->request->data['SaleReport']['from_date']) and !empty($this->request->data['SaleReport']['from_date']))				
				{
					$cond['Sale.sales_date >=']=$this->request->data['SaleReport']['from_date'];
					$from_date=$this->request->data['SaleReport']['from_date'];
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
				
				if(isset($this->request->data['SaleReport']['to_date']) and !empty($this->request->data['SaleReport']['to_date']))				
				{
					$cond['Sale.sales_date <=']=$this->request->data['SaleReport']['to_date'];
					$to_date=$this->request->data['SaleReport']['to_date'];
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
				if(isset($this->request->data['SaleReport']['ledger']) and !empty($this->request->data['SaleReport']['ledger']))				
				{
					$cond['Sale.ledger_id']=$this->request->data['SaleReport']['ledger'];
				}
				if($this->Session->read('Auth.User.location_id') ==BOOL_FALSE)
				{
					if(isset($this->request->data['SaleReport']['location']) and !empty($this->request->data['SaleReport']['location']))				
					{
						$cond['Sale.location_id']=$this->request->data['SaleReport']['location'];
						 $locData=$this->Location->findById($this->request->data['SaleReport']['location']);
						if(!empty($locData))
						{
						$locName=$locData['Location']['name'];
						}
					}
				}else{
						$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
						$locName=$this->Session->read('Auth.User.location_id');
					}
				
				if(isset($this->request->data['SaleReport']['date_type']) and !empty($this->request->data['SaleReport']['date_type']))				
				{
						if($this->request->data['SaleReport']['date_type']==DATE_WISE)
						{
							$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
							$group='Sale.sales_date';
						}
						elseif($this->request->data['SaleReport']['date_type']==MONTH_WISE)
						{
							$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
							$group=array('MONTH(Sale.sales_date)','YEAR(Sale.sales_date)');
						}
						elseif($this->request->data['SaleReport']['date_type']==YEAR_WISE)
						{
								$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
							$group=array('YEAR(Sale.sales_date)');
						}
				}else{
					$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
					$group='Sale.sales_date';
				}

			$conditions = array(
				'Sale.id !=' => BOOL_FALSE,
				
				'Sale.is_deleted' => BOOL_FALSE,

			);

			$conditions=array_merge($conditions,$cond);
			
			$contain=array();
			$sales=$this->Sale->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>$group,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			}
			 else
			{
				//From Date
				$year=date("Y");
				$month=date("m");
			
				if($month <=3)
				{
				$yearOpen=$year-1;
				$from_date=$yearOpen.'-04-01';
				}else{
				$from_date=$year.'-04-01';
				}
				
				//To Date
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
			$this->set(compact('sales'));
			
				
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		
		$this->set(compact('date'));
		
		$search='<tr class="border_none"><th colspan="8" class="border_none text-center">Location : '.$locName.'</th></tr>';
		$this->set(compact('search'));
    }
	
	public function resetSaleReportSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->office_check_login();	
		
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SaleReportSearch');
			$this->redirect($this->referer()); 
				
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect( array('controller'=>'boffices','action'=>'profitLoss','office'=>true));
			$this->redirect($this->referer());
		}		
		
    }
	/*
	Kajal kurrewar
	22-05-17
	function for load the next data in sales report  summary
	*/
	/*public function loadMoreSaleReport()
	{	

		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==GODOWN_ROLE_ID){
			$this->layout = ('godown/inner');
		}elseif($roleId==SHOP_ROLE_ID)
		{
			$this->layout = ('shop/inner');
		}else{
		$this->layout = ('office/inner');
		}
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Sale');	
			$this->loadModel('Ledger');	
			$this->loadModel('Location');	
	
		
		if ($this->request->is('ajax')) 
			{
				
			$cond=array();
			$sales=array();	
		if(isset($this->request->data['SaleReport']))
			{					
				$this->Session->write('SaleReportSearch',$this->request->data['SaleReport']);
			}
			else
			{	
				$this->request->data['SaleReport']=$this->Session->read('SaleReportSearch');		
			}		
			if(isset($this->request->data['SaleReport']))				
			{			
				if(isset($this->request->data['SaleReport']['from_date']) and !empty($this->request->data['SaleReport']['from_date']))				
				{
					$cond['Sale.sales_date >=']=$this->request->data['SaleReport']['from_date'];
				}
				if(isset($this->request->data['SaleReport']['to_date']) and !empty($this->request->data['SaleReport']['to_date']))				
				{
					$cond['Sale.sales_date <=']=$this->request->data['SaleReport']['to_date'];
				}
				if(isset($this->request->data['SaleReport']['ledger']) and !empty($this->request->data['SaleReport']['ledger']))				
				{
					$cond['Sale.ledger_id']=$this->request->data['SaleReport']['ledger'];
				}
				if($this->Session->read('Auth.User.location_id') ==BOOL_FALSE)
				{
					if(isset($this->request->data['SaleReport']['location']) and !empty($this->request->data['SaleReport']['location']))				
					{
						$cond['Sale.location_id']=$this->request->data['SaleReport']['location'];
					}
				}else{
						$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
					}
				
					if($this->request->data['SaleReport']['date_type']==DATE_WISE)
						{
							$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
							$group='Sale.sales_date';
						}
						elseif($this->request->data['SaleReport']['date_type']==MONTH_WISE)
						{
							$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
							$group=array('MONTH(Sale.sales_date)','YEAR(Sale.sales_date)');
						}
						elseif($this->request->data['SaleReport']['date_type']==YEAR_WISE)
						{
								$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
							$group=array('YEAR(Sale.sales_date)');
						}
				else{
					$fields=array('Sale.id','Sale.sales_date','SUM(Sale.total_amount) as total','SUM(Sale.total_payment) as payment','SUM(Sale.total_balance) as balance',);
					$group='Sale.sales_date';
				}

			
			}
		
			$conditions = array(
				'Sale.id !=' => BOOL_FALSE,
				'Sale.id >' => $this->request->data['id'],				
				'Sale.is_deleted' => BOOL_FALSE,

			);

			$conditions=array_merge($conditions,$cond);
			
			$contain=array();
			$sales=$this->Sale->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>$group,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			$data="";
			
			if(!empty($sales))
			{
				
				
				foreach($sales as $row)
				{
					
					if($this->request->data['SaleReport']['date_type']==DATE_WISE)
				{	
				   $datad = date('d-m-Y',strtotime($row['Sale']['sales_date']));
				}elseif($this->request->data['SaleReport']['date_type']==MONTH_WISE)
				{
					$datad = date('M-Y',strtotime($row['Sale']['sales_date']));
				}
				elseif($this->request->data['SaleReport']['date_type']==YEAR_WISE)
				{
					$datad = date('Y',strtotime($row['Sale']['sales_date']));
				} 
				else
				{
					$datad = date('d-m-Y',strtotime($row['Sale']['sales_date']));
				}
					
					$data.='<tr class=""><td>'.$datad.'</td><td class="tamt">'.$row[0]['total'].'</td><td class="damt">'.$row[0]['payment'].'</td><td class="blnc">'.$row[0]['balance'].'</td></tr>';
					$lastrowID=$row['Sale']['id'];
					
					
				}
				
				echo json_encode(array('status'=>'1000','tablegg'=>$data,'lastrowID'=>$lastrowID));	
			}else{
			echo json_encode(array('status'=>'1001'));	
			}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
	}*/
	/*
	Amit Sahu
	21.03.17
	sale  report category wise
	*/
	public function saleCatWiseReport() 
	{
		
		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==GODOWN_ROLE_ID){
			$this->layout = ('godown/inner');
		}elseif($roleId==SHOP_ROLE_ID)
		{
			$this->layout = ('shop/inner');
		}else{
		$this->layout = ('office/inner');
		}
		$cond=array();
		$from_date="";
		$to_date="";
		$locName="All";
		$catName="All";
		$sales=array();	
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			$this->loadModel('Sale');	
			$this->loadModel('Ledger');	
			$this->loadModel('Location');	
			$this->loadModel('SalesDetail');	
			$this->loadModel('Category');	
			
			
			$categoryList=$this->Category->getCategoryList();	
			$this->set(compact('categoryList'));
			
			$shops=$this->Location->getOnlyShopList();
			$this->set(compact('shops'));
							
			if(isset($this->request->data['SaleCatReport']))
			{					
				$this->Session->write('SaleCatReportSearch',$this->request->data['SaleCatReport']);
			}
			else
			{	
				$this->request->data['SaleCatReport']=$this->Session->read('SaleCatReportSearch');		
			}		
			if(isset($this->request->data['SaleCatReport']))				
			{			
				if(isset($this->request->data['SaleCatReport']['from_date']) and !empty($this->request->data['SaleCatReport']['from_date']))				
				{
					$cond['Sale.sales_date >=']=$this->request->data['SaleCatReport']['from_date'];
					$from_date=$this->request->data['SaleCatReport']['from_date'];
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
				if(isset($this->request->data['SaleCatReport']['to_date']) and !empty($this->request->data['SaleCatReport']['to_date']))				
				{
					$cond['Sale.sales_date <=']=$this->request->data['SaleCatReport']['to_date'];
					$to_date=$this->request->data['SaleCatReport']['to_date'];
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
				if(isset($this->request->data['SaleCatReport']['category']) and !empty($this->request->data['SaleCatReport']['category']))				
				{
					
					$catId=$this->request->data['SaleCatReport']['category'];
					$conditions=array('Category.id !='=>BOOL_FALSE,'Category.is_deleted'=>BOOL_FALSE,'Category.is_active'=>BOOL_TRUE,'Category.parent_id'=>$catId);
					$fields=array('Category.id');
					$catData=$this->Category->getCategoryAllData($conditions,$fields);
					$catIdArr[]=$catId;
					if(!empty($catData))
					{
						foreach($catData as $row)
						{
							$catIdArr[]=$row['Category']['id'];
						}
						
					}
					$cond['Item.category_id']=$catIdArr;
					 $catcData=$this->Category->findById($catIdArr);
						if(!empty($catcData))
						{
						$catName=$catcData['Category']['name'];
						}
					
				}
			
				if($this->Session->read('Auth.User.location_id') ==BOOL_FALSE)
				{
					if(isset($this->request->data['SaleCatReport']['location']) and !empty($this->request->data['SaleCatReport']['location']))				
					{
						$cond['Sale.location_id']=$this->request->data['SaleCatReport']['location'];
						 $locData=$this->Location->findById($this->request->data['SaleCatReport']['location']);
						if(!empty($locData))
						{
						$locName=$locData['Location']['name'];
						}
					}
				}else{
						$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
						$locName=$this->Session->read('Auth.User.location_id');
					}
				
				if(isset($this->request->data['SaleCatReport']['date_type']) and !empty($this->request->data['SaleCatReport']['date_type']))				
				{
						if($this->request->data['SaleCatReport']['date_type']==DATE_WISE)
						{
							$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
							$group='Sale.sales_date';
						}
						elseif($this->request->data['SaleCatReport']['date_type']==MONTH_WISE)
						{
							$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
							$group=array('MONTH(Sale.sales_date)','YEAR(Sale.sales_date)');
						}
						elseif($this->request->data['SaleCatReport']['date_type']==YEAR_WISE)
						{
								$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
							$group=array('YEAR(Sale.sales_date)');
						}
				}else{
					$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
					$group='Sale.sales_date';
				}

			$conditions = array(
				'SalesDetail.id !=' => BOOL_FALSE,
				
				'SalesDetail.is_deleted' => BOOL_FALSE,

			);

			$conditions=array_merge($conditions,$cond);
			
			$contain=array('Sale','Item'=>array('category_id','Category'=>array('parent_id')));
			$sales=$this->SalesDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>$group,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			}
			else{
				//From Date
				$year=date("Y");
				$month=date("m");
				
				if($month <=3)
				{
				$yearOpen=$year-1;
				$from_date=$yearOpen.'-04-01';
				}else{
				$from_date=$year.'-04-01';
				}
				
				//To Date
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
			$this->set(compact('sales'));
		
				
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		
		$this->set(compact('date'));
		
		$search='<tr class="border_none"><th colspan="3" class="border_none text-center">Location : '.$locName.'</th><th colspan="2" class="border_none text-center">Category : '.$catName.'</th></tr>';
		$this->set(compact('search'));	
    }
	public function resetSaleReportCatWiseSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SaleCatReportSearch');
			$this->redirect($this->referer()); 
				
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect( array('controller'=>'boffices','action'=>'profitLoss','office'=>true));
			$this->redirect($this->referer());
		}		
		
    }
	/*
	Kajal kurrewar
	22-05-17
	function for load the next data in sale cat wise
	*/
	public function loadMoreSalecatWise()
	{	

		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==GODOWN_ROLE_ID){
			$this->layout = ('godown/inner');
		}elseif($roleId==SHOP_ROLE_ID)
		{
			$this->layout = ('shop/inner');
		}else{
		$this->layout = ('office/inner');
		}
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Sale');	
			$this->loadModel('Sale');	
			$this->loadModel('Ledger');	
			$this->loadModel('Location');	
			$this->loadModel('SalesDetail');	
			$this->loadModel('Category');	
	
		
		if ($this->request->is('ajax')) 
			{
				
				$cond=array();
				$sales=array();	
		
		if(isset($this->request->data['SaleCatReport']))
			{					
				$this->Session->write('SaleCatReportSearch',$this->request->data['SaleCatReport']);
			}
			else
			{	
				$this->request->data['SaleCatReport']=$this->Session->read('SaleCatReportSearch');		
			}		
			if(isset($this->request->data['SaleCatReport']))				
			{			
				if(isset($this->request->data['SaleCatReport']['from_date']) and !empty($this->request->data['SaleCatReport']['from_date']))				
				{
					$cond['Sale.sales_date >=']=$this->request->data['SaleCatReport']['from_date'];
				}
				if(isset($this->request->data['SaleCatReport']['to_date']) and !empty($this->request->data['SaleCatReport']['to_date']))				
				{
					$cond['Sale.sales_date <=']=$this->request->data['SaleCatReport']['to_date'];
				}
				if(isset($this->request->data['SaleCatReport']['category']) and !empty($this->request->data['SaleCatReport']['category']))				
				{
					
					$catId=$this->request->data['SaleCatReport']['category'];
					$conditions=array('Category.id !='=>BOOL_FALSE,'Category.is_deleted'=>BOOL_FALSE,'Category.is_active'=>BOOL_TRUE,'Category.parent_id'=>$catId);
					$fields=array('Category.id');
					$catData=$this->Category->getCategoryAllData($conditions,$fields);
					$catIdArr[]=$catId;
					if(!empty($catData))
					{
						foreach($catData as $row)
						{
							$catIdArr[]=$row['Category']['id'];
						}
						
					}
					$cond['Item.category_id']=$catIdArr;
				}
				
				if($this->Session->read('Auth.User.location_id') ==BOOL_FALSE)
				{
					if(isset($this->request->data['SaleCatReport']['location']) and !empty($this->request->data['SaleCatReport']['location']))				
					{
						$cond['Sale.location_id']=$this->request->data['SaleCatReport']['location'];
					}
				}else{
						$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
					}
				
				if(isset($this->request->data['SaleCatReport']['date_type']) and !empty($this->request->data['SaleCatReport']['date_type']))				
				{
						if($this->request->data['SaleCatReport']['date_type']==DATE_WISE)
						{
							$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
							$group='Sale.sales_date';
						}
						elseif($this->request->data['SaleCatReport']['date_type']==MONTH_WISE)
						{
							$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
							$group=array('MONTH(Sale.sales_date)','YEAR(Sale.sales_date)');
						}
						elseif($this->request->data['SaleCatReport']['date_type']==YEAR_WISE)
						{
								$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
							$group=array('YEAR(Sale.sales_date)');
						}
				}else{
					$fields=array('SalesDetail.id','Sale.sales_date','SUM(SalesDetail.total_amount) as total','SUM(SalesDetail.discount) as discount',);
					$group='Sale.sales_date';
				}
				$conditions = array(
				'SalesDetail.id !=' => BOOL_FALSE,
				'SalesDetail.id >' => $this->request->data['id'],				
				'SalesDetail.is_deleted' => BOOL_FALSE,

			);

			$conditions=array_merge($conditions,$cond);
			
			$contain=array('Sale','Item'=>array('category_id','Category'=>array('parent_id')));
			$sales=$this->SalesDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>$group,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));

			}
			$this->set(compact('sales'));
			
			$data="";
			
			if(!empty($sales))
			{
				
				
				foreach($sales as $row)
				{
					
					if($this->request->data['SaleCatReport']['date_type']==DATE_WISE)
				{	
				$datad = date('d-m-Y',strtotime($row['Sale']['sales_date']));
				}elseif($this->request->data['SaleCatReport']['date_type']==MONTH_WISE)
				{
					$datad = date('M-Y',strtotime($row['Sale']['sales_date']));
				}
				elseif($this->request->data['SaleCatReport']['date_type']==YEAR_WISE)
				{
					$datad = date('Y',strtotime($row['Sale']['sales_date']));
				} else{
					$datad = date('d-m-Y',strtotime($row['Sale']['sales_date']));
				}
					$result = $row[0]['total']-$row[0]['discount'];
					$data.='<tr class=""><td>'.$datad.'</td><td class="tamt">'.$row[0]['total'].'</td><td class="damt">'.$row[0]['discount'].'</td><td class="disc">'.$result.'</td></tr>';
					$lastrowID = $row['SalesDetail']['id'];
					
					
				}
			
				echo json_encode(array('status'=>'1000','tablegg'=>$data,'lastrowID'=>$lastrowID));	
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
	Rahul Katole
	Add Creditor (Master)
	24.03.17
	*/
	
	public function addCreditor()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Creditor');
		$this->loadModel('DiscountLevel');
			
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Creditor->create();	
           		   
				if ($this->Creditor->save($this->request->data)) 
				{
					
						$id=$this->Creditor->getInsertID();
						$name=$this->request->data['Creditor']['name'];
						$address=$this->request->data['Creditor']['address'];
						$mobile=$this->request->data['Creditor']['mobile'];
						$email=$this->request->data['Creditor']['email'];
						$creOpnBlnc=$this->request->data['Creditor']['opening_balance'];
						$disId=$this->request->data['Creditor']['discount_level'];
						$debOpBlnc=$this->request->data['Creditor']['debitop_balance'];
						$deposit=$this->request->data['Creditor']['deposit'];
						$dl="";
						if(!empty($disId))
						{
						$dlData=$this->DiscountLevel->findById($disId);
							if(!empty($dlData))
							{
								$dl=$dlData['DiscountLevel']['name'];
							}
						}
					
						
					echo json_encode(array('status'=>'1000','message'=>'Creditor added successfully', 'id'=>$id,'name'=>$name,'address'=>$address,'mobile'=>$mobile,'email'=>$email,'dl'=>$dl,'opening_balance'=>$creOpnBlnc,'debitop_balance'=>$debOpBlnc,'deposit'=>$deposit));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Creditor could not be added'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	
		/*
	Rahul Katole
	Edite Creditor (Master)
	24.03.17
	*/
	public function editCreditor()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Creditor');
		$this->loadModel('DiscountLevel');
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Creditor']['id'];
				if(!empty($id))
					{
						if ($this->Creditor->save($this->request->data)) 
						{
								$name=$this->request->data['Creditor']['name'];
								$address=$this->request->data['Creditor']['address'];
								$mobile=$this->request->data['Creditor']['mobile'];
								$email=$this->request->data['Creditor']['email'];
								$creOpnBlnc=$this->request->data['Creditor']['opening_balance'];
								$disId=$this->request->data['Creditor']['discount_level'];
								$debOpBlnc=$this->request->data['Creditor']['debitop_balance'];
								$deposit=$this->request->data['Creditor']['deposit'];
								
								$dl="";
								if(!empty($disId))
								{
								$dlData=$this->DiscountLevel->findById($disId);
									if(!empty($dlData))
									{
										$dl=$dlData['DiscountLevel']['name'];
									}
								}
							echo json_encode(array('status'=>'1000','message'=>'Creditor edit successfully','id'=>$id,'name'=>$name,'address'=>$address,'mobile'=>$mobile,'email'=>$email,'dl'=>$dl,'opening_balance'=>$creOpnBlnc,'debitop_balance'=>$debOpBlnc,'deposit'=>$deposit));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Creditor could not be updated'));
						}
					}
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
			
	}
	
	public function setCreditorDiscount()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Creditor');
		$this->loadModel('PublishersDiscount');
		
		if ($this->request->is('ajax')) 
			{
				$discount_id ='';
				$publisher_id = $this->request->data['Creditor']['publisher_id'];				
				if(!empty($publisher_id))
				{
					foreach($this->request->data['PublishersDiscount'] as $k=>$v){
						
						$discount_id ='';
						$discount_id = $v['discount_id'];
						$arr = array(
								"publisher_id"=>$publisher_id,
								"discount_level"=>$v['discount_level'],
								"discount_percent"=>$v['discount_percent'],
						 );
						if(!empty($discount_id)){
							$this->PublishersDiscount->id = $discount_id;
						}
						else{
							$this->PublishersDiscount->create();
						}

						$this->PublishersDiscount->save($arr);
					}

					echo json_encode(array('status'=>'1000','message'=>'Creditor\'s Discount added successfully'));
				}
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Discount could not be added'));
				}
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}			
	}
	
	/*
	Rahul Katole
	24.03.17
	Delete Creditor
	*/
	
	public function deleteCreditor() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Creditor');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Creditor->id =$id;
				if (!$this->Creditor->exists()) 
				{
					throw new NotFoundException('Invalid Creditor');
				}
					   if ($this->Creditor->saveField('is_deleted',BOOL_TRUE)) 
					   {
							$this->Creditor->saveField('is_active',BOOL_FALSE);
						echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Creditor Deleted successfully'));
					   }else
					   {
						   echo json_encode(array('status'=>'1001','message'=>'Creditor could not be Deleted'));
					   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	
	/*
	Rahul Katole
	Add Designation (Master)
	24.03.17
	*/
	
	public function addDesignation()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Designation');
			
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Designation->create();			
				if ($this->Designation->save($this->request->data)) 
				{
					
						$id=$this->Designation->getInsertID();
						$name=$this->request->data['Designation']['name'];
						
					
						
					echo json_encode(array('status'=>'1000','message'=>'Designation added successfully', 'id'=>$id,'name'=>$name));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Designation could not be added or Designation name should be unique'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	
		/*
	Rahul Katole
	Edite Designation (Master)
	24.03.17
	*/
	public function editDesignation()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Designation');
	
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Designation']['id'];
				if(!empty($id))
					{
						if ($this->Designation->save($this->request->data)) 
						{
								$name=$this->request->data['Designation']['name'];
								
								
							echo json_encode(array('status'=>'1000','message'=>'Designation edit successfully', 'id'=>$id,'name'=>$name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Designation could not be added or Designation name should be unique'));
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
	Rahul Katole
	24.03.17
	Delete Designation
	*/
	
	public function deleteDesignation() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Designation');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Designation->id =$id;
				if (!$this->Designation->exists()) 
				{
					throw new NotFoundException('Invalid Designation');
				}
					   if ($this->Designation->saveField('is_deleted',BOOL_TRUE)) 
					   {
							$this->Designation->saveField('is_active',BOOL_FALSE);
							
						echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Designation Deleted successfully'));
					   }else
					   {
						   echo json_encode(array('status'=>'1001','message'=>'Designation could not be Deleted'));
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
	Get Data by model or field
	17.03.17
	*/
	public function getDataByModelFieldOrfiledEnqValue() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		

		if ($this->request->is('ajax')) 
			{
			
				$model= $this->request->data['model'];
				$filed= $this->request->data['filed'];
				$value= trim($this->request->data['value']);
				$this->loadModel($model);
			
					if(!empty($value)){
						$data=$this->$model->find('first',array('conditions'=>array($model.'.'.$filed=>$value,$model.'.is_deleted'=>BOOL_FALSE),'recursive'=>-1));
						if(!empty($data))
						{
							$card_valid = "";
							if($model=="Member"){
								$valid_upto = strtotime($data["Member"]["valid_upto"]);
								$today = strtotime(date("Y-m-d"));
								if($valid_upto>=$today){
									$card_valid = "valid";
								}
								else{
									$card_valid = "expired";
								}
								
							}
							echo json_encode(array('status'=>'1000','mydata'=>$data,"card_valid"=>$card_valid));
						}
						else{
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
	11.04.17
	Set category wise discount
	*/
	public function setCategoryDiscount()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Category');
		$this->loadModel('CategoryDiscount');
		
		if ($this->request->is('ajax')) 
			{
				$discount_id ='';
				$cat_id = $this->request->data['Category']['category_id'];				
				if(!empty($cat_id))
				{
					foreach($this->request->data['CategoryDiscount'] as $k=>$v){
						
						$discount_id ='';
						$discount_id = $v['discount_id'];
						$arr = array(
								"category_id"=>$cat_id,
								"discount_level"=>$v['discount_level'],
								"discount_percent"=>$v['discount_percent'],
						 );
						if(!empty($discount_id)){
							$this->CategoryDiscount->id = $discount_id;
						}
						else{
							$this->CategoryDiscount->create();
						}

						$this->CategoryDiscount->save($arr);
					}

					echo json_encode(array('status'=>'1000','message'=>'Category Discount added successfully'));
				}
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Category could not be added'));
				}
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}			
	}
	public function addTransport()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Transport');
		
		
		if ($this->request->is('ajax')) 
			{				
				$this->Transport->create();			
				if ($this->Transport->save($this->request->data)) 
				{
					$id=$this->Transport->getInsertID();
					$name=$this->request->data['Transport']['name'];
					$contact=$this->request->data['Transport']['contact'];
					$address=$this->request->data['Transport']['address'];
					
					echo json_encode(array('status'=>'1000','message'=>'Transport added successfully', 'id'=>$id,'contact'=>$contact,'address'=>$address,'name'=>$name));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Transport could not be added'));
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
	item Is_unique 
	17.04.17
	*/
	public function unique_item()
	{		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');
		
        if ($this->request->is('ajax')) 
		{
			
			$count=$this->Item->find('count',array(
			'conditions'=>array(
				'Item.name'=>$this->request->data['Item']['name'],
				'Item.is_deleted'=>BOOL_FALSE
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
	Publisher
	17.04.17
	*/

	public function autoCompleteIpublisher() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Publisher');		

        if ($this->request->is('ajax')) 
		{		
			
			$query=trim($this->request->data['value']);
			if(isset($query) and !empty($query))
			{
				$or_cond = array();
				$or_cond['OR']['Publisher.id LIKE'] =$query.'%';
				$or_cond['OR']['Publisher.name LIKE'] = $query.'%';
				
				$public=$this->Publisher->find('all',array(
					"fields"=>array("Publisher.id","Publisher.name"),
					'conditions'=>array(
					"OR"=>$or_cond,
					'Publisher.id !='=>BOOL_FALSE,
					'Publisher.is_deleted'=>BOOL_FALSE,
					'Publisher.is_active'=>BOOL_TRUE,									
					),
					
					"fields"=>array("Publisher.id","Publisher.name"),						
					"recursive"=>-1,					
					'order'=>array('Publisher.id'=>'ASC','Publisher.name'=>'ASC')
					));									
					
				
				if(!empty($public))
				{	
					$table="";
					$i=0;
					foreach($public as $row)
					{
						$i++;
						
						$table.='<tr><td>'.$i.'<td>'.$row['Publisher']['id'].'</td><td>'.$row['Publisher']['name'].'</td></tr>';
						
					}
				
					echo json_encode(array('table'=>$table,'status'=>1000));
				}
				else
				{
					echo json_encode(array('suggestions'=>array(array('data'=>'','value'=>''))));
				}
			}
			
		}	
	}
	/*
	Amit Sahu
	28.02.17
	track internal order
	*/
	public function trackInternalOrder() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('InternalPorder');
		$this->loadModel('InternalOrderDetails');
	

		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				
				if(!empty($id))
				{
					$track='';
					$orderData=$this->InternalPorder->findById($id);
					
					if(!empty($orderData))
					{
						$track.='<div class="track_box"><div style="float: left;"><div class="row">';
							$track.='<div class="track_data"><img src="'.$this->webroot.'images/track/create.png"><h3><span class="trac_item_date">'.date('d-m-Y',strtotime($orderData['InternalPorder']['created'])).'</span><br>Created</h3></div>';
						
						if($orderData['InternalPorder']['order_status']!=INTERNAL_ORDER_CREATE)
						{
							$track.='<div class="track_data track_line">-------------------</div><div class="track_data"><img src="'.$this->webroot.'images/track/send.png"><h3><span class="trac_item_date">'.date('d-m-Y',strtotime($orderData['InternalPorder']['send_date'])).'</span><br>Send</h3></div>';
						}
						$track.='</div></div><div style="float:left">';
						$conditions=array('InternalOrderDetails.int_porder_id'=>$id);
						$ordDetails=$this->InternalOrderDetails->find('all',array('conditions'=>$conditions));
					
						if(!empty($ordDetails))
						{
						$i=0;	
						foreach($ordDetails as $row)
						{
						$i++;
								$track.='<div class="row">';
								$rem="";
								if(!empty($row['InternalOrderDetails']['ref_id']))
								{
									$rem='Rem-';
								}
								if($i==BOOL_TRUE)
								{
									$track.='<div class="track_data track_line">-------------------</div><div class="track_data "><img src="'.$this->webroot.'images/track/books.png"><h3><span class="trac_item_code">'.$rem.''.$row['Item']['code'].'</span><span class="trac_item_qty"> ( '.$row['InternalOrderDetails']['qty'].' Nos. )</span><br>Item</h3></div>';
								}else{
										$track.='<div class="track_data track_line " style="margin-left:-30px"><img src="'.$this->webroot.'images/track/down.png"></div><div class="track_data " style="margin-left:80px"><img src="'.$this->webroot.'images/track/books.png"><h3><span class="trac_item_code">'.$rem.''.$row['Item']['code'].'</span><span class="trac_item_qty"> ( '.$row['InternalOrderDetails']['qty'].' Nos. )</span><br>Item</h3></div>';
								}
						
							
						
								if(!empty($row['InternalOrderDetails']['po_req_date']))
								{
									
									$track.='<div class="track_data track_line">-------------------</div><div class="track_data"><img src="'.$this->webroot.'images/track/po_req.png"><h3><span class="trac_item_date">'.date('d-m-Y',strtotime($row['InternalOrderDetails']['po_req_date'])).'</span><span class="trac_item_qty"> ( '.$row['InternalOrderDetails']['qty'].' Nos. )</span><br>P.O. Req.</h3></div>';
								}
								
								if(!empty($row['InternalOrderDetails']['po_id']))
								{
									
									$track.='<div class="track_data track_line">-------------------</div><div class="track_data"><img src="'.$this->webroot.'images/track/po.png"><h3><span class="trac_item_date">'.date('d-m-Y',strtotime($row['Porder']['created'])).'</span><br>P.O. Dispatched.</h3></div>';
								}
								
								
								if(!empty($row['Porder']['good_rec_date']))
								{
									
									$track.='<div class="track_data track_line">-------------------</div><div class="track_data"><img src="'.$this->webroot.'images/track/purchase.png"><h3><span class="trac_item_date">'.date('d-m-Y',strtotime($row['Porder']['good_rec_date'])).'</span><br>P.O. Received.</h3></div>';
								}
								if($row['InternalOrderDetails']['status']==INTERNAL_ORDER_DISPATCH or $row['InternalOrderDetails']['status']==INTERNAL_ORDER_RECEIVED)
								{
									
									$track.='<div class="track_data track_line">-------------------</div><div class="track_data"><img src="'.$this->webroot.'images/track/dispatch.png"><h3><span class="trac_item_date">'.date('d-m-Y',strtotime($row['InternalOrderDetails']['dis_date'])).'</span><span class="trac_item_qty"> ( '.$row['InternalOrderDetails']['dis_qty'].' Nos. )</span><br>Dispatched</h3></div>';
								}
								if($row['InternalOrderDetails']['status']==INTERNAL_ORDER_RECEIVED)
								{
									
									$track.='<div class="track_data track_line">-------------------</div><div class="track_data"><img src="'.$this->webroot.'images/track/received.png"><h3><span class="trac_item_date">'.date('d-m-Y',strtotime($row['InternalOrderDetails']['rec_date'])).'</span><span class="trac_item_qty"> ( '.$row['InternalOrderDetails']['received_qty'].' Nos. )</span><br>Received</h3></div>';
								}
								if($row['InternalOrderDetails']['status']==INTERNAL_ORDER_PENDING)
								{
									
									$track.='<div class="track_data track_line">-------------------</div><div class="track_data"><img src="'.$this->webroot.'images/track/pening.jpg"><h3><span class="trac_item_date"></span><br>Pending</h3></div>';
								}
								
								$track.='</div>';
								
						}
						}
						$track.='</div></div>';
						
					}else{
						$track.="Enter a valid order no.";
					}
					$track.='</div>';
				 echo json_encode(array('status'=>'1000','track'=>$track));	
				}
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	

	
	/*
	Masood Sir
	26.04.17
	Check internet connected
	*/
	public function is_connected()
	{
		$connected = @fsockopen("www.google.com", 80); 
											//website, port  (try 80 or 443)
		if ($connected){
			$is_conn = true; //action when connected
			fclose($connected);
		}else{
			$is_conn = false; //action in connection failure
		}
		return $is_conn;	
	}
	/*
	Amit Sahu
	26.04.17
	get stock history
	*/
	public function getstckHistory()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('InternalOrderDetails');
		$this->loadModel('PurchaseDetail');
		
		if ($this->request->is('ajax')) 
			{
					$item_id=$this->request->data['item_id'];
					$loc_id=$this->request->data['loc_id'];
					$loc_type=$this->request->data['loc_type'];
					$table="";
					if($loc_type==LOCATION_SHOP)
					{
						$conditions=array('InternalOrderDetails.item_id'=>$item_id,'InternalPorder.location_id'=>$loc_id);
						$data=$this->InternalOrderDetails->find('all',array('conditions'=>$conditions,'fields'=>array('InternalOrderDetails.received_qty','InternalOrderDetails.int_porder_id','InternalOrderDetails.rec_date'),'limit'=>5,'order'=>array('InternalOrderDetails.rec_date'=>'DESC')));
						if(!empty($data))
						{
							foreach($data as $row)
							{
							$table.='<tr><td>'.date('d-m-Y', strtotime($row['InternalOrderDetails']['rec_date'])).'</td><td>'.$row['InternalOrderDetails']['int_porder_id'].'</td><td>'.$row['InternalOrderDetails']['received_qty'].'</td></tr>';
							}
						}else{
							$table.='<tr><td colspan="3" class="text-center">No record</td></tr>';
						}
					}
					elseif($loc_type==LOCATION_GODOWN)
					{
						$contain=array('Porder'=>array('good_rec_date'));
						$conditions=array('PurchaseDetail.item_id'=>$item_id);
						$data=$this->PurchaseDetail->find('all',array('conditions'=>$conditions,'recursive'=>2,'contain'=>$contain,'limit'=>5,'order'=>array('Porder.good_rec_date'=>'DESC')));
						if(!empty($data))
						{
							foreach($data as $row)
							{
							$table.='<tr><td>'.date('d-m-Y', strtotime($row['Porder']['good_rec_date'])).'</td><td>'.$row['PurchaseDetail']['order_id'].'</td><td>'.$row['PurchaseDetail']['quantity'].'</td></tr>';
							}
						}else{
							$table.='<tr><td colspan="3" class="text-center">No record</td></tr>';
						}
					}
					
							echo json_encode(array('status'=>'1000','table'=>$table));
						
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
			
	}
	
	/*
	Neha Bastawale
	27.04.17
	Minstocklevel Quantity
	*/
	
	public function addMinstocklevelQuantity() 

    {
		$this->layout = 'ajax';
		$this->autoRender = FALSE;
		$this->loadModel('MinStockLevel');
		$this->loadModel('Location');
		$this->loadModel('Item');
				
		if ($this->request->is('ajax')) 
			{
				   
                    $item_id=$this->request->data['MinStockLevel']['item_id'];
				
					$loc_id=$this->Session->read('Auth.User.location_id');	
					
					$conditions=array('MinStockLevel.item_id'=>$item_id,'MinStockLevel.location_id'=>$loc_id);
				    $data=$this->MinStockLevel->find('first',array('conditions'=>$conditions));
					
					if(!empty($data))
					{
				    $this->request->data['MinStockLevel']['id']=$data['MinStockLevel']['id'];	
					}
					else{
						$this->MinStockLevel->create();
						$this->request->data['MinStockLevel']['location_id']=$loc_id;
					}
				  
				    if($this->MinStockLevel->save($this->request->data['MinStockLevel'])) 

				    {
					$quantity=$this->request->data['MinStockLevel']['quantity'];
							
					echo json_encode(array('status'=>'1000','message'=>'MinStockLevel update successfully','quantity'=>$quantity));
				   } 
				  else 
				  {
					echo json_encode(array('status'=>'1001','message'=>'MinStockLevel could not be updated'));
				  }
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}	
	}
	public function setMinstocklevelQuantity()
	{
		$this->layout = 'ajax';
		$this->autoRender = FALSE;
		$this->loadModel('MinStockLevel');
		$this->loadModel('Location');
		$this->loadModel('Item');
		
		if($this->request->is('ajax'))
		{
		        $item_id ='';
				$item_id = $this->request->data['id'];
                $loc_id=$this->Session->read('Auth.User.location_id');
              	             	   
				if(!empty($item_id))
				{
					
						$conditions=array('MinStockLevel.item_id'=>$item_id,'MinStockLevel.location_id'=>$loc_id);
						 
						$data=$this->MinStockLevel->find('first',array('conditions'=>$conditions,'fields'=>array('MinStockLevel.quantity')));
						 
						$quantity=$data['MinStockLevel']['quantity'];
						
						echo json_encode(array('status'=>'1000','message'=>'MinStockLevel updated successfully','quantity'=>$quantity));
				}
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'MinStockLevel could not be updated'));
				}
				
		}
	}
public function dailyAttendance($id = null) 
	{
		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==ADMIN_ROLE_ID){
			$this->layout = ('admin/inner');
		}else{
		$this->layout = ('office/inner');
		}
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID,UPDATE_PERMISSION_ID))) 
		{		
		
			$this->loadModel('Location');
			$this->loadModel('Employee');
			$this->loadModel('EmployeesAttendance');			
			$p_arr = array();
			$total_present = 0;
			$total_absent = 0;
			if ($this->request->is('post') || $this->request->is('put')) 
			{
				
				if(isset($this->request->data["EmployeesAttendance"]["location_id"])){
					
					if(isset($this->request->data["EmployeesAttendance"]["location_id"])){																	
					
						$location_id = $this->request->data["EmployeesAttendance"]["location_id"];
						
						$employees = $this->Employee->find("all",array(
							"conditions"=>array(
							"Employee.id !="=>BOOL_FALSE,
							"Employee.location_id"=>$location_id,
							"Employee.is_active"=>BOOL_TRUE,
							"Employee.is_deleted"=>BOOL_FALSE,
							),
						));
						$this->set(compact("employees"));					
						
						if(isset($this->request->data["EmployeesAttendance"]["Submit"])){
							
							if(!empty($this->request->data["EmployeesAttendance"]["attendance_date"])){
							$this->request->data["EmployeesAttendance"]["attendance_date"] = !empty($this->request->data["EmployeesAttendance"]["attendance_date"])?date("Y-m-d",strtotime($this->request->data["EmployeesAttendance"]["attendance_date"])):"";
							
							$isdone = $this->EmployeesAttendance->find("count",array(
							"conditions"=>array(
							"EmployeesAttendance.id !="=>BOOL_FALSE,
							"EmployeesAttendance.location_id"=>$location_id,
							"EmployeesAttendance.attendance_date"=>$this->request->data["EmployeesAttendance"]["attendance_date"],
							),
							"recursive"=>-1,
							));
							
								if(!empty($isdone) and $isdone > 0)
								{
									$this->Session->setFlash("Attendance entered already", 'error');
								}
								else{
								
									if(!empty($this->request->data["EmployeesAttendance"]["is_holiday"]))
									{
										$emps = $this->Employee->find("all",array(
									"conditions"=>array(
									"Employee.id !="=>BOOL_FALSE,
									"Employee.location_id"=>$this->request->data["EmployeesAttendance"]["location_id"],
									"Employee.is_active"=>BOOL_TRUE,
									"Employee.is_deleted"=>BOOL_FALSE,
									),
									"recursive"=>-1,
									));
									
									foreach($emps as $row)	{
									
										$emp_arr = array(
										"location_id"=>$location_id,
										"employee_id"=>$row["Employee"]["id"],
										"attendance_date"=>$this->request->data["EmployeesAttendance"]["attendance_date"],
										"status"=>ATTD_STATUS_HOLIDAY,
										"occasion" =>$this->request->data["EmployeesAttendance"]["occasion"]
										);
										
										$this->EmployeesAttendance->create();
										if($this->EmployeesAttendance->save($emp_arr)){
										
										}
									}	
										
										$this->Session->setFlash('Marked as Holiday. ', 'success');
										$this->redirect($this->referer());	
									}
									else{
									foreach($this->request->data["EmployeesAttendance"]["employee_id"] as $k=>$v){
										if(!empty($v)){
											$p_arr[] = $v;
										}
									}
									
									if(!empty($p_arr)){
										foreach($p_arr as $v){
										$emp_arr = array(
										"location_id"=>$location_id,
										"employee_id"=>$v,
										"attendance_date"=>$this->request->data["EmployeesAttendance"]["attendance_date"],
										"status"=>ATTD_STATUS_PRESENT,
										);
											$this->EmployeesAttendance->create();
											if($this->EmployeesAttendance->save($emp_arr)){
											$total_present++;
											}
										}
									}
									
									$emps = $this->Employee->find("all",array(
									"conditions"=>array(
									"Employee.id !="=>BOOL_FALSE,
									"Employee.id !="=>$p_arr,
									"Employee.location_id"=>$this->request->data["EmployeesAttendance"]["location_id"],
									"Employee.is_active"=>BOOL_TRUE,
									"Employee.is_deleted"=>BOOL_FALSE,
									),
									"recursive"=>-1,
									));
									
									foreach($emps as $row)	{
									
										$emp_arr = array(
										"location_id"=>$location_id,
										"employee_id"=>$row["Employee"]["id"],
										"attendance_date"=>$this->request->data["EmployeesAttendance"]["attendance_date"],	
										"status"=>ATTD_STATUS_ABSENT,
										);
									
										$this->EmployeesAttendance->create();
										if($this->EmployeesAttendance->save($emp_arr)){
											$total_absent++;
										}
									}
									
									$this->Session->setFlash('Attendance done. <label class="label label-xs label-success">total present : '.$total_present.'</label> , <label class="label label-xs label-danger">total absent : '.$total_absent.' </label>', 'success');
									$this->redirect($this->referer());
									}
								}
							
							
							
							}
							else{
								$this->Session->setFlash("Please select date", 'error');
							}
						}
					}					
				}
				
				
				
				/*echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";				
				exit;*/
				
				$this->request->data["EmployeesAttendance"]["attendance_date"] = !empty($this->request->data["EmployeesAttendance"]["attendance_date"])?date("d-m-Y",strtotime($this->request->data["EmployeesAttendance"]["attendance_date"])):"";
			}
			
			$locations=$this->Location->getShopList();
			$this->set(compact('locations'));
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
		
    }
	
	
	
	
	public function assignTask() 
	{
		
		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==ADMIN_ROLE_ID){
			$this->layout = ('admin/inner');
		}else{
		$this->layout = ('office/inner');
		}
			
		$employees = array();
		$total_tasks = 0;
			
			$this->loadModel('TaskAssignment');			
			$this->loadModel('Location');
			$this->loadModel('Employee');			
			if ($this->request->is('post')) 
			{
				$location_id = $this->request->data["TaskAssignment"]["location_id"];
				
				$employee_id = $this->request->data["TaskAssignment"]["employee_id"];
				
				$task_type = $this->request->data["TaskAssignment"]["task_type"];
				
				
					if(!empty($employee_id)){

							
							if(!empty($this->request->data["TaskDetail"])){
								foreach($this->request->data["TaskDetail"] as $k){
									
									$from_date = !empty($k["from_date"])?date("Y-m-d",strtotime($k["from_date"])):"";
									$to_date = !empty($k["to_date"])?date("Y-m-d",strtotime($k["to_date"])):"";
									$td_arr = array(
									"location_id" =>$location_id,
									"employee_id" =>$employee_id,
									"task_type" => $task_type,
									"from_date" =>$from_date,
									"to_date" =>$to_date,
									"detail" =>$k["detail"],
									"task_type" =>$k["type"],
									"created_by" => $this->Auth->User('id'),
									);
									
									$this->TaskAssignment->create();
							
									if($this->TaskAssignment->save($td_arr)){
										$total_tasks ++;
									}	
									
								}
								
								$this->Session->setFlash('Tasks assigned successfully.', 'success');
								$this->redirect($this->referer());
								
							}
						
						
					}
					else{
						$this->Session->setFlash("Please select employee", 'error');
					}
					
					$this->Employee->virtualFields = array('name' => 'CONCAT(Employee.name, " ", Employee.middle_name, " ",Employee.last_name )');
					$employees=$this->Employee->find('list',array(
						'conditions'=>array(
							'Employee.id !='=>BOOL_FALSE,
							'Employee.location_id'=>$location_id,
							
							'Employee.is_deleted'=>BOOL_FALSE,
							'Employee.is_active'=>BOOL_TRUE,
						 )
					));
					$this->set(compact('employees'));
				
					$this->Session->setFlash("Please select location", 'error');
			
			
			}	
			
			$locations=$this->Location->getShopList();
			
			$this->set(compact('locations'));
			
			$this->set(compact('employees'));
			
    }
	
	public function getEmployeesList() 
	{
		$this->autoRender = false;
		$this->layout = "ajax";		
		$this->loadModel("Employee");
		$str = "";
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			if($this->request->is("ajax")){
				
				if(isset($this->request->data["location_id"])){
					$location_id = $this->request->data["location_id"];
					
					$this->Employee->virtualFields = array('name' => 'CONCAT(Employee.name, " ", Employee.middle_name, " ",Employee.last_name )');
					$employees=$this->Employee->find('list',array(
						'conditions'=>array(
							'Employee.id !='=>BOOL_FALSE,
							'Employee.location_id'=>$this->request->data["location_id"],
							'Employee.is_deleted'=>BOOL_FALSE,
							'Employee.is_active'=>BOOL_TRUE,
						 )
					));
					if(!empty($employees)){
						
						$str='<option value="">Select Employee</option>';
						foreach($employees as $k=>$v)
						{
							$str.='<option value="'.$k.'">'.$v.'</option>';
						}
					}
				}				
				
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
								
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
	16.06.17
	get user list by role ajax
	*/
	/*public function getUserListByRole() 
	{
		$this->autoRender = false;
		$this->layout = "ajax";		
		//$this->admin_check_login();
		$this->loadModel("User");
		$str = "";
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			if($this->request->is("ajax")){
				
				if(isset($this->request->data["role"])){
					$role = $this->request->data["role"];
					
					//$this->User->virtualFields = array('full_name' => 'CONCAT(User.name, " ", User.middle_name, " ",User.last_name )');
					$users=$this->User->find('list',array(
						'conditions'=>array(
							'User.id !='=>BOOL_FALSE,
							'User.role_id'=>$role,
							//'Employee.location_id !='=>BOOL_FALSE,
							'User.is_deleted'=>BOOL_FALSE,
							'User.is_active'=>BOOL_TRUE,
						 ),
						 'fields'=>array('User.id','User.name')
					));
					
					if(!empty($users)){
						
						$str='<option value="">Select User</option>';
						foreach($users as $k=>$v)
						{
							$str.='<option value="'.$k.'">'.$v.'</option>';
						}
					}
				}				
				
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
								
			}
			
        } 
		else 
		{
            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
        }
    }
	/*
	
	add item discount in master
	Amit Sahu
	15.05.17
	*/
 public function addItemDiscount()
	{
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Item');
		$this->loadModel('ItemsDiscount');
		
		if ($this->request->is('ajax')) 
			{
				
				$item_id ='';
				$item_id = $this->request->data['Item']['item_id'];				
				if(!empty($item_id))
				{
					
					foreach($this->request->data['ItemDiscount'] as $k=>$v){
						
						$discount_id ='';
						$discount_id = $v['discount_id'];
						$arr = array(
								"item_id"=>$item_id,								
								"discount_level"=>$v['discount_level'],
								"discount_percent"=>$v['discount_percent'],
						 );
						if(!empty($item_id)){
							$this->ItemsDiscount->id = $discount_id;
						}
						else{
							$this->ItemsDiscount->create();
						}

						$this->ItemsDiscount->save($arr);
					}
					

					echo json_encode(array('status'=>'1000','message'=>'Item\'s discount added successfully'));
				}

				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Discount could not be added'));
				}
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}			
	}
	/*
	view Voucher 
	22.05.17
	Amit Sahu
	*/
		public function viewVoucher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Voucher');
		$this->loadModel('VoucherDetail');
		$this->loadModel('Location');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Voucher->id =$id;
				
					$voucherData=$this->Voucher->findById($id);
					$credit=$voucherData['CrLedger']['name'];
					if($voucherData['Voucher']['cr_is_bank']==BOOL_TRUE)
					{
					$credit=$voucherData['BankAccount']['bank_name'];
					}
					
					$conditions=array('VoucherDetail.vid'=>$voucherData['Voucher']['id'],'VoucherDetail.is_deleted'=>BOOL_FALSE);
					$fields=array('VoucherDetail.amount','VoucherDetail.id','VoucherDetail.naration');
					$location=$voucherData['Location']['name'];
					$id=$voucherData['Voucher']['id'];
					$date=$voucherData['Voucher']['date'];
					$vDetails=$this->VoucherDetail->getVoucherData($conditions,$fields);
					if($voucherData['Voucher']['dr_is_bank']==BOOL_TRUE)
					{
					$ledname=$voucherData['DrBankAccount']['bank_name'];
					}else{
					$ledname=$voucherData['Ledger']['name'];
					}
					$table="";
					$totalAmount=0;
					if(!empty($vDetails))
					{
						foreach($vDetails as $row)
						{
							$totalAmount=$totalAmount+$row['VoucherDetail']['amount'];
							$table.='<tr><td class="border_none"><span class="full-width"><b>Dr. '.ucfirst($ledname).'</b></span><br><br></td><td>'.$row['VoucherDetail']['amount'].'</td></tr><tr><td class="border_none"><span class="full-width"><b>Cr. '.ucfirst($credit).'</b></span>(<i> '.ucfirst($row['VoucherDetail']['naration']).'</i>)</td><td class="text-right">'.$row['VoucherDetail']['amount'].'</td></tr>';
						}
					}
					$table.='<tr><td class="border_none"><span class="full-width text-right"><b>Total</b></span></td><td>'.number_format($totalAmount,2).'</td></tr><tr ><td class="text-right" colspan="3"><div class="row voffset4"></div>Signature______________</td></tr>';
					echo json_encode(array('status'=>'1000','table'=>$table,'location'=>$location,'id'=>$id,'date'=>$date,'amount'=>$totalAmount));
							
		
			}
			else
			{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			}
    }
	/*
	Amit Sahu
	print creadit invoice
	01.06.17
	*/
	
	public function printCreditInvoice($id=NULL){
		
		
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
				'recursive'=>2,
			));
			$saleItem=$sale['SalesDetail'];
			$limitData = array();
					$k=1;
					$j=1;
					foreach($saleItem as $row){
								$limitData[$j]['SalesDetail'][] = $row;
								if($k == 20){
									$j++;
									$k=0;
								}
								$k++;
								if(!empty($limitData[$j]))
								{
						$limitData[$j]=array_merge($sale,$limitData[$j]);
								}
						}
				
			$this->set(compact('limitData'));
		
			
		}
		else{
				$this->Session->setFlash("Unauthorized access", 'error');
				$this->redirect($this->referer());
		}
	}
	
	
	/*
	Kajal kurrewar
	
	5-6-2017
	
	For notify the incomplete member registration
	
	*/
	
		public function incompleteMemberRegistration() 
	{
		$cond=array();
		
		if($this->Session->read('Auth.User.location_id')==BOOL_FALSE)
		{
			$this->layout = ('office/inner');	
	
		}else
		{
		$this->layout = ('shop/inner');
		$cond['Member.location_id']=$this->Session->read('Auth.User.location_id');
		}
		$this->loadModel('Member');	
		$this->loadModel('Location');	
		
		$shops=$this->Location->getOnlyShopList();
			$this->set(compact('shops'));
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{
			
							
			if(isset($this->request->data['MemberSearch']))
			{					
				$this->Session->write('MemberSearch',$this->request->data['MemberSearch']);
			}
			else
			{	
				$this->request->data['MemberSearch']=$this->Session->read('MemberSearch');		
			}		
			if(isset($this->request->data['MemberSearch']))				
			{			
				if(isset($this->request->data['MemberSearch']['name']) and !empty($this->request->data['MemberSearch']['name']))				
				{
					$cond['OR']['Member.id']=$this->request->data['MemberSearch']['name'];
					$cond['OR']['Member.card_no LIKE']=$this->request->data['MemberSearch']['name']."%";
					$cond['OR']['Member.name LIKE']=$this->request->data['MemberSearch']['name']."%";
					$cond['OR']['Member.email LIKE']=$this->request->data['MemberSearch']['name']."%";
					$cond['OR']['Member.contact_no LIKE']=$this->request->data['MemberSearch']['name']."%";
				}
				if(isset($this->request->data['MemberSearch']['date']) and !empty($this->request->data['MemberSearch']['date']))				
				{
					$cond['DATE(Member.created)']=$this->request->data['MemberSearch']['date'];
				
				}
				if(isset($this->request->data['MemberSearch']['location']) and !empty($this->request->data['MemberSearch']['location']))				
				{
					$cond['Member.location_id']=$this->request->data['MemberSearch']['location'];
				
				}
			}
			
			
			$conditions = array('Member.name ='=>'','Member.present_address ='=>'','Member.land_mark ='=>'','Member.pin_code ='=>'','Member.taluka ='=>'','Member.district ='=>'','Member.email ='=>'','Member.state ='=>'',);

			$conditions=array_merge($conditions,$cond);
			$members=$this->Member->find('all',array('conditions'=>$conditions,'recursive'=>2,'limit'=>PAGINATION_LIMIT,'order'=>array('Member.id'=>'DESC')));
			$this->set(compact('members'));
			
		}	
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	public function resetincompleteMemberRegistrationSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;							
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('MemberSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}

    }
	/*
	Ledger Summary Report
	Kajal kurrewar
	16-6-17
	*/
	public function ledgerSummary() 
	{
		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==GODOWN_ROLE_ID){
			$this->layout = ('godown/inner');
		}elseif($roleId==SHOP_ROLE_ID)
		{
			$this->layout = ('shop/inner');
		}else{
		$this->layout = ('office/inner');
		}
		$this->loadModel('Ledger');
		$this->loadModel('Voucher');
		$this->loadModel('Location');
		$this->loadModel('Distributor');
		$this->loadModel('Publisher');
		$this->loadModel('Creditor');
		$this->loadModel('Porder');
		$this->loadModel('Purchase');
		$this->loadModel('Sale');
		$this->loadModel('BankAccount');
		$this->loadModel('PaymentTransaction');
		$this->loadModel('SalesReturn');
		$this->loadModel('CreditSaleReturn');
		$this->loadModel('PurchaseReturn');
		
		
		$ledgers=$this->Ledger->getLedgerList();
		$this->set(compact('ledgers'));	
		
		$crblnc=0;
		$dbblnc=0;
		
		$locationArr=$this->Location->getShopList();
		foreach($locationArr as $lk=>$lv)
		{
			$location[$lk]=$lv;
		}
		$location['bo']='Back Office';
		$this->set(compact('location'));	
		
		$creditors=$this->Creditor->getCreditorList();
		$this->set(compact('creditors'));	
		
		$distributor=$this->Distributor->getDistributorList();
		$this->set(compact('distributor'));	
		
		$publisher_list=$this->Publisher->getPublisherList();
		$this->set(compact('publisher_list'));

        $bankAccounts_list=$this->BankAccount->getBankAccountList();
		$this->set(compact('bankAccounts_list'));	
		
		$cond=array();
		$cond1=array();
		$cond2=array();
		$cond3=array();
		$search_ledger="";
		$search_bankledger="";
		$search_pub="";
		$search_cred="";
		$search_dis="";
		$from_date="";
		$to_date="";
		$ledName="";
		
		
		if($this->Session->read('Auth.User.location_id')==BOOL_FALSE)
		{
		$locationId="";	
		}
		else
		{
		$locationId=$this->Session->read('Auth.User.location_id');
		}
		if(isset($this->request->data['Ledger']))
		{					
			$this->Session->write('LedgerSummarySearch',$this->request->data['Ledger']);
		}
		else
		{	
			$this->request->data['Ledger']=$this->Session->read('LedgerSummarySearch');		
		}	
		if(isset($this->request->data['Ledger']))				
		{
			if($this->request->data['Ledger']['ledger_Type'] == 1)
			{
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))				
				{
					$cond['YEAR(Voucher.date) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))				
				{
					$cond['YEAR(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				
				if(isset($this->request->data['Ledger']['location']) and !empty($this->request->data['Ledger']['location']))				
				{
					
					$locationId=$this->request->data['Ledger']['location'];
					
				}
				if(isset($this->request->data['Ledger']['ledger']) and !empty($this->request->data['Ledger']['ledger']))				
				{
					$cond['OR']['Voucher.ledger_id']=array($this->request->data['Ledger']['ledger']);
					$cond['OR']['Voucher.cr_ledger_id']=array($this->request->data['Ledger']['ledger']);
					$search_ledger=$this->request->data['Ledger']['ledger'];
					$ledgerdata=$this->Ledger->findById($this->request->data['Ledger']['ledger']);
					if(!empty($ledgerdata))
					{
						$ledName=$ledgerdata['Ledger']['name'];
					}
				}
				$start    = new DateTime($from_date);
				$start->modify('first day of this year');
				$end      = new DateTime($to_date);
				$end->modify('first day of next year');
				$interval = DateInterval::createFromDateString('1 year');
				$period   = new DatePeriod($start, $interval, $end);
                $rowdata="";
					
					$dararr="";
				foreach ($period as $dt)
				{
					
					$dararr[]= $dt->format("Y") . "<br>\n";
				    $date=$dararr;
				}
			
				if(!empty($date)){
				foreach ($date as $k=>$v)
				{
					$cd="";
					$debt="";
					$year['year']=$v;
					$id['ledger']=$search_ledger;
					
					$conditions = array(
					'Voucher.id !=' => BOOL_FALSE,
					'Voucher.ledger_id' =>$search_ledger,
					'Voucher.is_deleted'=> BOOL_FALSE,
					'Voucher.is_active'=> BOOL_TRUE,
					'YEAR(Voucher.date)'=>$v,
					
					);
					$conditions=array_merge($conditions,$cond);		
					
					$fields=array('Voucher.id','Voucher.date','Voucher.ledger_id',);
					
					$debitData=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(Voucher.total) as debit')));
					$this->set(compact('debitData'));
					
					$debt['debit'] = $debitData[0]['debit'];
					
					$conditions = array(
					'Voucher.id !=' => BOOL_FALSE,
					'Voucher.cr_ledger_id' =>$search_ledger,
					'Voucher.is_deleted'=> BOOL_FALSE,
					'Voucher.is_active' => BOOL_TRUE,
					'YEAR(Voucher.date)'=>$v,
					
					);
					$conditions=array_merge($conditions,$cond);		
					
					$fields=array('Voucher.id','Voucher.date','Voucher.cr_ledger_id',);
				
					$creditData=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(Voucher.total) as credit')));
					$this->set(compact('creditData'));
					
					$cd['credit'] = $creditData[0]['credit'];
					
					$date[$k]= array_merge($year,$cd ,$debt ,$id);
					
				}
				
				}
		    }
		
		else if($this->request->data['Ledger']['ledger_Type'] == 2)
			{
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
				{
					$cond1['YEAR(Purchase.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond2['YEAR(PurchaseReturn.created) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
				{
					$cond1['YEAR(Purchase.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond2['YEAR(PurchaseReturn.created) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['distributor']) and !empty($this->request->data['Ledger']['distributor']))
				{
					$cond1['Porder.supplier_id']=$this->request->data['Ledger']['distributor'];
					$cond2['PurchaseReturn.supplier_id']=$this->request->data['Ledger']['distributor'];
					$search_dis=$this->request->data['Ledger']['distributor'];
					$distdata=$this->Distributor->findById($this->request->data['Ledger']['distributor']);
					if(!empty($distdata))
					{
						$ledName=$distdata['Distributor']['name'];
					}
				}
		
			    $start = new DateTime($from_date);
				$start->modify('first day of this year');
				$end = new DateTime($to_date);
				$end->modify('first day of next year');
				$interval = DateInterval::createFromDateString('1 year');
				$period   = new DatePeriod($start, $interval, $end);
                $rowdata="";
					
					$dararr="";
				foreach ($period as $dt)
				{
					
					$dararr[]= $dt->format("Y") . "<br>\n";
				    $distrdate=$dararr;
				}
				
				if(!empty($distrdate)){
				foreach ($distrdate as $k=>$v)
				{
					$cd="";
					$debt="";
					$debt1="";
					$year['year']=$v;
					$id['ledger']=$search_dis;
					
					$conditions = array(
				'Purchase.id !=' =>BOOL_FALSE,
				'Porder.supplier_id'=>$search_dis,
				'Purchase.is_deleted' =>BOOL_FALSE,
				'Purchase.is_active' =>BOOL_TRUE,
				'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
				'Porder.type' => DISTRIBUTOR,
				'YEAR(Purchase.created)'=>$v,
				);
				$conditions1=array_merge($conditions,$cond1);
				$fields1=array('Purchase.id','Purchase.created','Purchase.total_amount');
				$debitData=$this->Purchase->find('first', array(
							"conditions"=>$conditions1,
							'fields'=>array('SUM(Purchase.total_amount) as debit'),
							"contain"=>array(
								"Porder",
								"Porder.Distributor",
								"PaymentTransaction",
							),
							'order'=>array('Purchase.id'=>'ASC')
						));
				$this->set(compact('purchases'));
				$debt['debit'] = $debitData[0]['debit'];
				
				$conditions = array(
				'PurchaseReturn.id !=' =>BOOL_FALSE,
				'PurchaseReturn.supplier_id'=>$search_dis,
				'PurchaseReturn.is_deleted' =>BOOL_FALSE,
				'PurchaseReturn.is_active' =>BOOL_TRUE,
				'PurchaseReturn.status' =>RETURN_RECIEVED,
				'PurchaseReturn.supplier_type' =>DISTRIBUTOR,
				'YEAR(PurchaseReturn.created)'=>$v,
				);
				$conditions1=array_merge($conditions,$cond2);
				$fields1=array('PurchaseReturn.id','PurchaseReturn.created','PurchaseReturn.sanction_amount');
				$debitReturn=$this->PurchaseReturn->find('first', array(
							"conditions"=>$conditions1,
							'fields'=>array('SUM(PurchaseReturn.sanction_amount) as debit'),
							'order'=>array('PurchaseReturn.id'=>'ASC')
						));
				$this->set(compact('debitReturn'));
				$debt1['debit'] = $debitReturn[0]['debit'];
				
				foreach (array_keys($debt + $debt1) as $key) 
				{
					$debit[$key] = $debt[$key] + $debt1[$key];
				}
				
				$conditions = array(
				'Purchase.id !=' =>BOOL_FALSE,
				'Porder.supplier_id'=>$search_dis,
				'Purchase.is_deleted' =>BOOL_FALSE,
				'Purchase.is_active' =>BOOL_TRUE,
				'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
				'Porder.type' => DISTRIBUTOR,
				'YEAR(Purchase.created)'=>$v,
				);
				$conditions1=array_merge($conditions,$cond1);
				$fields1=array('Purchase.id','Purchase.created','Purchase.total_payment');
				$creditData=$this->Purchase->find('first', array(
							"conditions"=>$conditions1,
							'fields'=>array('SUM(Purchase.total_payment) as credit'),
							"contain"=>array(
								"Porder",
								"Porder.Distributor",
								"PaymentTransaction",
							),
							'order'=>array('Purchase.id'=>'ASC')
						));
				$this->set(compact('purchases'));
				
				$cd['credit'] = $creditData[0]['credit'];
				
				$distrdate[$k]=array_merge($year,$cd ,$debit,$id);
					
				}

				}
			        
		}
		else if($this->request->data['Ledger']['ledger_Type'] == 3)
		{
			
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
				{
					$cond1['YEAR(Purchase.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond2['YEAR(PurchaseReturn.created) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
				{
					$cond1['YEAR(Purchase.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond2['YEAR(PurchaseReturn.created) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['pub_id']) and !empty($this->request->data['Ledger']['pub_id']))
				{
					$cond1['Porder.supplier_id']=$this->request->data['Ledger']['pub_id'];
					$cond2['PurchaseReturn.supplier_id']=$this->request->data['Ledger']['pub_id'];
					$search_pub=$this->request->data['Ledger']['pub_id'];
					$pubdata=$this->Publisher->findById($this->request->data['Ledger']['pub_id']);
					if(!empty($pubdata))
					{
						$ledName=$pubdata['Publisher']['name'];
					}
				}
			
			
				$start= new DateTime($from_date);
				$start->modify('first day of this year');
				$end      = new DateTime($to_date);
				$end->modify('first day of next year');
				$interval = DateInterval::createFromDateString('1 year');
				$period   = new DatePeriod($start, $interval, $end);
                $rowdata="";
					
					$dararr="";
				foreach ($period as $dt)
				{
					
					$dararr[]= $dt->format("Y") . "<br>\n";
				    $publishersdate=$dararr;
				}
			
				if(!empty($publishersdate)){
				foreach ($publishersdate as $k=>$v)
				{
					$cd="";
					$debt="";
					$debt1="";
					$year['year']=$v;
					$id['ledger']=$search_pub;
					
				$conditions = array(
				'Purchase.id !=' =>BOOL_FALSE,
				'Purchase.is_deleted' =>BOOL_FALSE,
				'Porder.supplier_id'=>$search_pub,
				'Purchase.is_active' =>BOOL_TRUE,
				'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
				'Porder.type' => PUBLISHER,
				'YEAR(Purchase.created)'=>$v,
				);
				$conditions=array_merge($conditions,$cond1);
				$fields1=array('Purchase.id','Purchase.created','Purchase.total_amount');
				$debitData=$this->Purchase->find('first', array(
						"conditions"=>$conditions,
						'fields'=>array('SUM(Purchase.total_amount) as debit'),
						"contain"=>array(
							"Porder",
							"Porder.Publisher",
							"PaymentTransaction",
						),
						"recursive"=>-1
						));
				$this->set(compact('purchases'));
				$debt['debit'] = $debitData[0]['debit'];	
				
				$conditions = array(
				'PurchaseReturn.id !=' =>BOOL_FALSE,
				'PurchaseReturn.supplier_id'=>$search_pub,
				'PurchaseReturn.is_deleted' =>BOOL_FALSE,
				'PurchaseReturn.is_active' =>BOOL_TRUE,
				'PurchaseReturn.status' =>RETURN_RECIEVED,
				'PurchaseReturn.supplier_type' =>PUBLISHER,
				'YEAR(PurchaseReturn.created)'=>$v,
				);
				$conditions1=array_merge($conditions,$cond2);
				$fields1=array('PurchaseReturn.id','PurchaseReturn.created','PurchaseReturn.sanction_amount');
				$debitReturn=$this->PurchaseReturn->find('first', array(
							"conditions"=>$conditions1,
							'fields'=>array('SUM(PurchaseReturn.sanction_amount) as debit'),
							'order'=>array('PurchaseReturn.id'=>'ASC')
						));
				$this->set(compact('debitReturn'));
				$debt1['debit'] = $debitReturn[0]['debit'];
				
				foreach (array_keys($debt + $debt1) as $key) 
				{
					$debit[$key] = $debt[$key] + $debt1[$key];
				}
				
					$conditions = array(
				'Purchase.id !=' =>BOOL_FALSE,
				'Purchase.is_deleted' =>BOOL_FALSE,
				'Porder.supplier_id'=>$search_pub,
				'Purchase.is_active' =>BOOL_TRUE,
				'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
				'Porder.type' => PUBLISHER,
				'YEAR(Purchase.created)'=>$v,
				);
				$conditions=array_merge($conditions,$cond1);
				$fields1=array('Purchase.id','Purchase.created','Purchase.total_payment');
				$creditData=$this->Purchase->find('first', array(
						"conditions"=>$conditions,
						'fields'=>array('SUM(Purchase.total_payment) as credit'),
						"contain"=>array(
							"Porder",
							"Porder.Publisher",
							"PaymentTransaction",
						),
						"recursive"=>-1
						));
				    $this->set(compact('purchases'));
					$cd['credit'] = $creditData[0]['credit'];
					
					$publishersdate[$k]=array_merge($year,$cd ,$debit,$id);
					
				}
			  
				}
			         
		}
		else if($this->request->data['Ledger']['ledger_Type'] == 4)
		{		
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
				{
					$cond['DATE(Sale.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond1['DATE(SalesReturn.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond2['DATE(CreditSaleReturn.date) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
				{
					$cond['DATE(Sale.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond1['DATE(SalesReturn.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond2['DATE(CreditSaleReturn.date) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['creditors']) and !empty($this->request->data['Ledger']['creditors']))
				{
					$cond['Sale.ledger_id']=$this->request->data['Ledger']['creditors'];
					$cond1['Sale.ledger_id']=$this->request->data['Ledger']['creditors'];
					$cond2['CreditSaleReturn.ledger_id']=$this->request->data['Ledger']['creditors'];
					$search_cred=$this->request->data['Ledger']['creditors'];
					$debdata=$this->Creditor->findById($this->request->data['Ledger']['creditors']);
					if(!empty($debdata))
					{
						$ledName=$debdata['Creditor']['name'];
					}
				}
				
			$start= new DateTime($from_date);
				$start->modify('first day of this year');
				$end      = new DateTime($to_date);
				$end->modify('first day of next year');
				$interval = DateInterval::createFromDateString('1 year');
				$period   = new DatePeriod($start, $interval, $end);
                $rowdata="";
					
					$dararr="";
				foreach ($period as $dt)
				{
					
					$dararr[]= $dt->format("Y") . "<br>\n";
				    $creditordate=$dararr;
				}
				
				if(!empty($creditordate)){
				foreach ($creditordate as $k=>$v)
				{
					$cd="";
					$credit="";
					$debt="";
					$debt1="";
					
					$year['year']=$v;
					$id['ledger']=$search_cred;
					
					$conditions = array(
					'Sale.id !=' =>BOOL_FALSE,
					'Sale.ledger_id'=>$search_cred,
					'Sale.is_deleted' =>BOOL_FALSE,
					'Sale.is_active' =>BOOL_TRUE,
					'YEAR(Sale.created)'=>$v,
					
					);
					$conditions=array_merge($conditions,$cond);		
					
					$fields=array('Sale.id','Sale.created',);
					
					$debitData=$this->Sale->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(Sale.total_amount) as debit'),"contain"=>array(
							"Member",
							"Creditor",
							"PaymentTransaction",
							)));
					$this->set(compact('debitData'));
					
					$debt1['debit'] = $debitData[0]['debit'];
					
					
					$conditions1 = array(
					'SalesReturn.id !=' =>BOOL_FALSE,
					'SalesReturn.is_deleted' =>BOOL_FALSE,
					'SalesReturn.is_active' =>BOOL_TRUE,
					'Sale.ledger_id'=>$search_cred,
					'YEAR(SalesReturn.created)'=>$v,);
					
					
					$conditions=array_merge($conditions1,$cond);		
					
					$fields=array('SalesReturn.id','SalesReturn.created','SalesReturn.total_amount');
					
					$debitData=$this->SalesReturn->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(SalesReturn.total_amount) as debit')));
					
					$this->set(compact('debitData'));
					$debt['debit'] = $debitData[0]['debit'];
					
					foreach (array_keys($debt1 + $debt) as $key) {
						$debit[$key] = $debt1[$key] + $debt[$key];
					}
					
					
					$conditions =array(
						'PaymentTransaction.id !='=>BOOL_FALSE,
						'PaymentTransaction.is_deleted'=>BOOL_FALSE,
						'PaymentTransaction.is_active'=>BOOL_TRUE,
						'PaymentTransaction.type'=>SALE_PAYMENT,
						'Sale.ledger_id'=>$search_cred,
						'YEAR(PaymentTransaction.created)'=>$v,
						);
					$conditions=array_merge($conditions,$cond);		
					
					$fields=array('PaymentTransaction.reference_id','PaymentTransaction.created','PaymentTransaction.type','PaymentTransaction.payment');
					
					$creditData=$this->PaymentTransaction->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(PaymentTransaction.payment) as credit'),"contain"=>array(
							"Sale",
							)));
					$this->set(compact('creditData'));
	
					$cd['credit'] = $creditData[0]['credit'];
					
					$conditions2 = array(
					'CreditSaleReturn.id !=' =>BOOL_FALSE,
					'CreditSaleReturn.is_deleted' =>BOOL_FALSE,
					'CreditSaleReturn.is_active' =>BOOL_TRUE,
					'CreditSaleReturn.ledger_id'=>$search_cred,
					'YEAR(CreditSaleReturn.date)'=>$v,);
					
					
					$conditions=array_merge($conditions2,$cond2);		
					
					$fields=array('CreditSaleReturn.id','CreditSaleReturn.date','CreditSaleReturn.total_amount');
				
					$debitData=$this->CreditSaleReturn->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(CreditSaleReturn.total_amount) as credit')));
					
					$this->set(compact('debitData'));
					$credit['credit'] = $debitData[0]['credit'];
					
					foreach (array_keys($cd + $credit) as $key) {
						$credit[$key] = $cd[$key] + $credit[$key];
					}
					
					$creditordate[$k]=array_merge($year,$credit ,$debit, $id);
					
				}
				
				}
			         
		}
		else if($this->request->data['Ledger']['ledger_Type'] == 5) /*  For Bank Ledger*/
		{		
					if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))				
				{
					$cond['YEAR(Voucher.date) >=']=$this->request->data['Ledger']['from_date'];
					$cond1['YEAR(PaymentTransaction.created) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}	
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))				
				{
					$cond['YEAR(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
					$cond1['YEAR(PaymentTransaction.created) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['Bank']) and !empty($this->request->data['Ledger']['Bank']))				
				{
					$cond['OR']['Voucher.ledger_id']=array($this->request->data['Ledger']['Bank']);
					$cond['OR']['Voucher.cr_ledger_id']=array($this->request->data['Ledger']['Bank']);
					$cond1['OR']['PaymentTransaction.dr_bank']=array($this->request->data['Ledger']['Bank']);
					$search_ledger=$this->request->data['Ledger']['Bank'];
					$bankdata=$this->BankAccount->findById($this->request->data['Ledger']['Bank']);
					if(!empty($bankdata))
					{
						$ledName=$bankdata['BankAccount']['bank_name'];
					}
						
				}
            
				
				$start= new DateTime($from_date);
				$start->modify('first day of this year');
				$end= new DateTime($to_date);
				$end->modify('first day of next year');
				$interval = DateInterval::createFromDateString('1 year');
				$period   = new DatePeriod($start, $interval, $end);
                $rowdata="";
					
				$dararr="";
				foreach ($period as $dt)
				{
					
					$dararr[]= $dt->format("Y") . "<br>\n";
				    $bankdate=$dararr;
				}
				
				if(!empty($bankdate)){
				foreach ($bankdate as $k=>$v)
				{
					$credit="";
					$debit="";
					$cd="";
					$cd1="";
					$debt1="";
					$debt="";
					$year['year']=$v;
					$id['ledger']=$search_ledger;

					$conditions = array(
					'Voucher.id !=' => BOOL_FALSE,
					'Voucher.ledger_id' =>$search_ledger,
					'Voucher.is_deleted'=> BOOL_FALSE,
					'Voucher.is_active' => BOOL_TRUE,
					'YEAR(Voucher.date)'=>$v,
					
					);
					$conditions=array_merge($conditions,$cond);		
					
					$fields=array('Voucher.id','Voucher.date','Voucher.ledger_id',);
				
					$debitData1=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(Voucher.total) as debit')));
					$this->set(compact('debitData1'));
					
					
					$debt1['debit'] = $debitData1[0]['debit'];
				
					$conditions = array(
					'Voucher.id !=' => BOOL_FALSE,
					'Voucher.cr_ledger_id' =>$search_ledger,
					'Voucher.is_deleted'=> BOOL_FALSE,
					'Voucher.is_active' => BOOL_TRUE,
					'YEAR(Voucher.date)'=>$v,
					
					);
					$conditions=array_merge($conditions,$cond);		
					
					$fields=array('Voucher.id','Voucher.date','Voucher.cr_ledger_id',);
					
					$creditData1=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(Voucher.total) as credit')));
					$this->set(compact('creditData1'));
					
					$cd['credit'] = $creditData1[0]['credit'];
					
					
					$conditions1 = array(
					'PaymentTransaction.id !=' =>BOOL_FALSE,
					'PaymentTransaction.dr_bank'=>$search_ledger,
					'PaymentTransaction.is_deleted '=>BOOL_FALSE,
					'PaymentTransaction.is_active'=>BOOL_TRUE,
					'PaymentTransaction.type'=>PURCHASE_PAYMENT,
					'YEAR(PaymentTransaction.created)'=>$v,);
					
					
					$conditions=array_merge($conditions1,$cond1);		
					
					$fields=array('PaymentTransaction.id','PaymentTransaction.created','PaymentTransaction.payment');
					
					$debitData=$this->PaymentTransaction->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(PaymentTransaction.payment) as debit')));
					
					$this->set(compact('debitData'));
					
					$debt['debit'] = $debitData[0]['debit'];
					
					foreach (array_keys($debt1 + $debt) as $key) {
						$debit[$key] = $debt1[$key] + $debt[$key];
					}

					$conditions1 =array(
					'PaymentTransaction.id !=' =>BOOL_FALSE,
					'PaymentTransaction.dr_bank'=>$search_ledger,
					'PaymentTransaction.is_deleted '=>BOOL_FALSE,
					'PaymentTransaction.is_active'=>BOOL_TRUE,
					'PaymentTransaction.type'=>SALE_PAYMENT,
					'YEAR(PaymentTransaction.created)'=>$v,
						);
					$conditions=array_merge($conditions1,$cond1);		
					
					$fields=array('PaymentTransaction.reference_id','PaymentTransaction.created','PaymentTransaction.type','PaymentTransaction.payment');
					
					$creditData=$this->PaymentTransaction->find('first',array('conditions'=>$conditions,'fields'=>array('SUM(PaymentTransaction.payment) as credit')));
					$this->set(compact('creditData'));
					
					$cd1['credit'] = $creditData[0]['credit'];
					foreach (array_keys($cd + $cd1) as $key) {
						$credit[$key] = $cd[$key] + $cd1[$key];
					}
					
					$bankdate[$k]=array_merge($year,$credit ,$debit ,$id);
					
				 }
				
				}
										
		}
                
	}
	else{
				//From Date
				$year=date("Y");
				$month=date("m");
				
				if($month <=3)
				{
				$yearOpen=$year-1;
				$from_date=$yearOpen.'-04-01';
				}else{
				$from_date=$year.'-04-01';
				}
				
				//To Date
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
	
	$this->set(compact('search_ledger'));
	$this->set(compact('date'));
	$this->set(compact('distrdate'));
	
	$this->set(compact('publishersdate'));
    $this->set(compact('creditordate'));
	$this->set(compact('bankdate'));
	$datecurr='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		
		$this->set(compact('datecurr'));
		
	
	     $search='<tr class="border_none "><th class="border_none text-ceter"  colspan="7"><h5 class="text-center" style="padding:0;margin:0;"><b>'.$ledName.'</b></h5><span style="float:left;width:100%;text-align:center">Ledger Account</span></th></tr>';
		$this->set(compact('search'));
	
	}
	/*
	Kajal kurrewar
	reset ledger Summary search
	16-6-17
	*/
	public function resetLedgerSummarySearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->office_check_login();	
		
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('LedgerSummarySearch');
			$this->redirect($this->referer()); 
				
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect( array('controller'=>'boffices','action'=>'profitLoss','office'=>true));
			$this->redirect($this->referer());
		}		
		
    }
	/*
	Ledger Summary Report for month 
	Kajal kurrewar
	28-6-17
	*/
	
	public function ledgerSummaryMonthWise($id=NULL) 
	{
		$year = $this->Encryption->decrypt($id);
		
		$roleId=$this->Session->read('Auth.User.role_id');
		if($roleId==GODOWN_ROLE_ID)
		{
			$this->layout = ('godown/inner');
		}elseif($roleId==SHOP_ROLE_ID)
		{
			$this->layout = ('shop/inner');
		}
		else{
		$this->layout = ('office/inner');
		}
		$this->loadModel('Ledger');
		$this->loadModel('Voucher');
		$this->loadModel('Location');
		$this->loadModel('Distributor');
		$this->loadModel('Publisher');
		$this->loadModel('Creditor');
		$this->loadModel('Porder');
		$this->loadModel('Purchase');
		$this->loadModel('Sale');
		$this->loadModel('BankAccount');
		$this->loadModel('PaymentTransaction');
		$this->loadModel('SalesReturn');
		$this->loadModel('CreditSaleReturn');
		$this->loadModel('PurchaseReturn');
		
		
		$ledgers=$this->Ledger->getLedgerList();
		$this->set(compact('ledgers'));	
		
		$creditors=$this->Creditor->getCreditorList();
		$this->set(compact('creditors'));	
		
		$distributor=$this->Distributor->getDistributorList();
		$this->set(compact('distributor'));	
		
		$publisher_list=$this->Publisher->getPublisherList();
		$this->set(compact('publisher_list'));

        $bankAccounts_list=$this->BankAccount->getBankAccountList();
		$this->set(compact('bankAccounts_list'));	
		
		
		$cond=array();
		$cond1=array();
		$cond2=array();
		$cond3=array();
		$search_ledger="";
		$search_bankledger="";
		$search_pub="";
		$search_cred="";
		$search_dis="";
		$from_date="";
		$to_date="";
		$ledName="";
		
		
		if($this->Session->read('Auth.User.location_id')==BOOL_FALSE)
		{
		$locationId="";	
		}
		else
		{
		$locationId=$this->Session->read('Auth.User.location_id');
		}
		if(isset($this->request->data['Ledger']))
		{					
			$this->Session->write('LedgerSummarySearch',$this->request->data['Ledger']);
		}
		else
		{	
			$this->request->data['Ledger']=$this->Session->read('LedgerSummarySearch');		
		}	
		if(isset($this->request->data['Ledger']))				
		{
			if($this->request->data['Ledger']['ledger_Type'] == 1)
			{
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))				
				{
					$cond['YEAR(Voucher.date) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
					
				}

								
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))				
				{
					$cond['YEAR(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				
				if(isset($this->request->data['Ledger']['location']) and !empty($this->request->data['Ledger']['location']))				
				{
					
					$locationId=$this->request->data['Ledger']['location'];
					
				}
				if(isset($this->request->data['Ledger']['ledger']) and !empty($this->request->data['Ledger']['ledger']))				
				{
					$cond['OR']['Voucher.ledger_id']=array($this->request->data['Ledger']['ledger']);
					$cond['OR']['Voucher.cr_ledger_id']=array($this->request->data['Ledger']['ledger']);
					$search_ledger=$this->request->data['Ledger']['ledger'];
					$ledgerdata=$this->Ledger->findById($this->request->data['Ledger']['ledger']);
					if(!empty($ledgerdata))
					{
						$ledName=$ledgerdata['Ledger']['name'];
					}
				}
				 
				 
					  $year=preg_replace('/\s+/', '',$year) ;
				      $year=strip_tags($year);
    
					  $month=array("01","02","03","04","05","06","07","08","09","10","11","12");
					
					 
					  if(!empty($month))
					  {
					   foreach($month as $k=>$dt)
					  {
						 $conditions = array(
						'Voucher.id !=' => BOOL_FALSE,
						'Voucher.ledger_id' =>$search_ledger,
						'Voucher.is_deleted'=> BOOL_FALSE,
						'Voucher.is_active'=> BOOL_TRUE,
						'MONTH(Voucher.date)'=> $dt,
						'YEAR(Voucher.date)'=> $year,
						);
						
						$debitData=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('Voucher.date','SUM(Voucher.total) as debit')));
						$name['month_name']=$dt;
						$debit['debit_amount']=$debitData[0]['debit'];
						
						
						
						$conditions = array(
						'Voucher.id !=' => BOOL_FALSE,
						'Voucher.cr_ledger_id' =>$search_ledger,
						'Voucher.is_deleted'=> BOOL_FALSE,
						'Voucher.is_active'=> BOOL_TRUE,
						'MONTH(Voucher.date)'=> $dt,
						'YEAR(Voucher.date)'=> $year,

						);
						$creditdata=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('Voucher.date','SUM(Voucher.total) as credit')));
						$yname['year_name']=$year; 
						$name['month_name']=$dt;
						$credit['credit_amount']=$creditdata[0]['credit'];
						$month[$k]=array_merge($yname,$name,$debit,$credit);
					  }
					  }
					 
		    }
		
		else if($this->request->data['Ledger']['ledger_Type'] == 2)
			{
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
				{
					$cond1['YEAR(Purchase.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond2['YEAR(PurchaseReturn.created) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
				{
					$cond1['YEAR(Purchase.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond2['YEAR(PurchaseReturn.created) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['distributor']) and !empty($this->request->data['Ledger']['distributor']))
				{
					$cond1['Porder.supplier_id']=$this->request->data['Ledger']['distributor'];
					$cond2['PurchaseReturn.supplier_id']=$this->request->data['Ledger']['distributor'];
					$search_dis=$this->request->data['Ledger']['distributor'];
					$distdata=$this->Distributor->findById($this->request->data['Ledger']['distributor']);
					if(!empty($distdata))
					{
						$ledName=$distdata['Distributor']['name'];
					}
				}
			  
				 $year=preg_replace('/\s+/', '',$year) ;
				 $year=strip_tags($year);
				 
			     $dist_month=array("01","02","03","04","05","06","07","08","09","10","11","12");
		
				 
				  foreach($dist_month as $k=>$dt)
					  {
						
						$conditions = array(
						'Purchase.id !=' =>BOOL_FALSE,
						'Porder.supplier_id'=>$search_dis,
						'Purchase.is_deleted' =>BOOL_FALSE,
						'Purchase.is_active' =>BOOL_TRUE,
						'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
						'Porder.type' => DISTRIBUTOR,
						'MONTH(Purchase.created)'=> $dt,
						'YEAR(Purchase.created)'=> $year,
			
					);
				
				        $distDebit=$this->Purchase->find('first', array(
							"conditions"=>$conditions,
							'fields'=>array('SUM(Purchase.total_amount) as debit'),
							"contain"=>array(
								"Porder",
								"Porder.Distributor",
								"PaymentTransaction",
							)
						));
						$name['month_name']=$dt;
						$debt['debit_amount']=$distDebit[0]['debit'];
				
				$conditions = array(
				'PurchaseReturn.id !=' =>BOOL_FALSE,
				'PurchaseReturn.supplier_id'=>$search_dis,
				'PurchaseReturn.is_deleted' =>BOOL_FALSE,
				'PurchaseReturn.is_active' =>BOOL_TRUE,
				'PurchaseReturn.status' =>RETURN_RECIEVED,
				'PurchaseReturn.supplier_type' =>DISTRIBUTOR,
				'MONTH(PurchaseReturn.created)'=> $dt,
				'YEAR(PurchaseReturn.created)'=> $year,
				);
				$conditions1=array_merge($conditions,$cond2);
				$fields1=array('PurchaseReturn.id','PurchaseReturn.created','PurchaseReturn.sanction_amount');
				$debitReturn=$this->PurchaseReturn->find('first', array(
							"conditions"=>$conditions1,
							'fields'=>array('SUM(PurchaseReturn.sanction_amount) as debit'),
							'order'=>array('PurchaseReturn.id'=>'ASC')
						));
				$this->set(compact('debitReturn'));
				$debt1['debit_amount'] = $debitReturn[0]['debit'];
				
				foreach (array_keys($debt + $debt1) as $key) 
				{
					$debit[$key] = $debt[$key] + $debt1[$key];
				}
				
				
				$conditions = array(
				'Purchase.id !=' =>BOOL_FALSE,
				'Porder.supplier_id'=>$search_dis,
				'Purchase.is_deleted' =>BOOL_FALSE,
				'Purchase.is_active' =>BOOL_TRUE,
				'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
				'Porder.type' => DISTRIBUTOR,
				'MONTH(Purchase.created)'=> $dt,
				'YEAR(Purchase.created)'=> $year,
				);
			
				
				$distCredit=$this->Purchase->find('first', array(
							"conditions"=>$conditions,
							'fields'=>array('Purchase.created','SUM(Purchase.total_payment) as credit'),
							"contain"=>array(
								"Porder",
								"Porder.Distributor",
								"PaymentTransaction",
							)
						));
						$yname['year_name']=$year; 
						$name['month_name']=$dt;
						$credit['credit_amount']=$distCredit[0]['credit'];
						$dist_month[$k]=array_merge($yname,$name,$debit,$credit);
			
			 }
		  
		}
		else if($this->request->data['Ledger']['ledger_Type'] == 3)
		{
			
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
				{
					$cond1['YEAR(Purchase.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond2['YEAR(PurchaseReturn.created) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
				{
					$cond1['YEAR(Purchase.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond2['YEAR(PurchaseReturn.created) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['pub_id']) and !empty($this->request->data['Ledger']['pub_id']))
				{
					$cond1['Porder.supplier_id']=$this->request->data['Ledger']['pub_id'];
					$cond2['PurchaseReturn.supplier_id']=$this->request->data['Ledger']['pub_id'];
					$search_pub=$this->request->data['Ledger']['pub_id'];
					$pubdata=$this->Publisher->findById($this->request->data['Ledger']['pub_id']);
					if(!empty($pubdata))
					{
						$ledName=$pubdata['Publisher']['name'];
					}
				}
			
				 $year=preg_replace('/\s+/', '',$year) ;
				 $year=strip_tags($year);
				
				 
			     $pub_month=array("01","02","03","04","05","06","07","08","09","10","11","12");
				 
				  foreach($pub_month as $k=>$dt)
					  {
				 
						$conditions = array(
						'Purchase.id !=' =>BOOL_FALSE,
						'Purchase.is_deleted' =>BOOL_FALSE,
						'Porder.supplier_id'=>$search_pub,
						'Purchase.is_active' =>BOOL_TRUE,
						'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
						'Porder.type' => PUBLISHER,
						'MONTH(Purchase.created)'=> $dt,
				        'YEAR(Purchase.created)'=> $year,
						);
					
						$purdebit=$this->Purchase->find('first', array(
								"conditions"=>$conditions,
								'fields'=>array('Purchase.created','SUM(Purchase.total_amount) as debit'),
								"contain"=>array(
									"Porder",
									"Porder.Publisher",
									"PaymentTransaction",
								),
								"recursive"=>-1
								));
					    $name['month_name']=$dt;
						$debt['debit_amount']=$purdebit[0]['debit'];
						
						$conditions = array(
				'PurchaseReturn.id !=' =>BOOL_FALSE,
				'PurchaseReturn.supplier_id'=>$search_pub,
				'PurchaseReturn.is_deleted' =>BOOL_FALSE,
				'PurchaseReturn.is_active' =>BOOL_TRUE,
				'PurchaseReturn.status' =>RETURN_RECIEVED,
				'PurchaseReturn.supplier_type' =>PUBLISHER,
				'MONTH(PurchaseReturn.created)'=> $dt,
				'YEAR(PurchaseReturn.created)'=> $year,
				);
				$conditions1=array_merge($conditions,$cond2);
				$fields1=array('PurchaseReturn.id','PurchaseReturn.created','PurchaseReturn.sanction_amount');
				$debitReturn=$this->PurchaseReturn->find('first', array(
							"conditions"=>$conditions1,
							'fields'=>array('SUM(PurchaseReturn.sanction_amount) as debit'),
							'order'=>array('PurchaseReturn.id'=>'ASC')
						));
				$this->set(compact('debitReturn'));
				$debt1['debit_amount'] = $debitReturn[0]['debit'];
				
				foreach (array_keys($debt + $debt1) as $key) 
				{
					$debit[$key] = $debt[$key] + $debt1[$key];
				}
				
								$conditions = array(
							'Purchase.id !=' =>BOOL_FALSE,
							'Purchase.is_deleted' =>BOOL_FALSE,
							'Porder.supplier_id'=>$search_pub,
							'Purchase.is_active' =>BOOL_TRUE,
							'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
							'Porder.type' => PUBLISHER,
							'MONTH(Purchase.created)'=> $dt,
							'YEAR(Purchase.created)'=> $year,
							);
							$purcredit=$this->Purchase->find('first', array(
									"conditions"=>$conditions,
									'fields'=>array('Purchase.created','SUM(Purchase.total_payment) as credit'),
									"contain"=>array(
										"Porder",
										"Porder.Publisher",
										"PaymentTransaction",
									),
									"recursive"=>-1
									));
									
						$yname['year_name']=$year; 
						$name['month_name']=$dt;
						$credit['credit_amount']=$purcredit[0]['credit'];
						$pub_month[$k]=array_merge($yname,$name,$debit,$credit);
					  }
	
					
		}
		else if($this->request->data['Ledger']['ledger_Type'] == 4)
		{		
				if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
				{
					$cond['DATE(Sale.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond1['DATE(SalesReturn.created) >=']=$this->request->data['Ledger']['from_date'];
					$cond2['DATE(CreditSaleReturn.date) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
				{
					$cond['DATE(Sale.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond1['DATE(SalesReturn.created) <=']=$this->request->data['Ledger']['to_date'];
					$cond2['DATE(CreditSaleReturn.date) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['creditors']) and !empty($this->request->data['Ledger']['creditors']))
				{
					$cond['Sale.ledger_id']=$this->request->data['Ledger']['creditors'];
					$cond1['Sale.ledger_id']=$this->request->data['Ledger']['creditors'];
					$cond2['CreditSaleReturn.ledger_id']=$this->request->data['Ledger']['creditors'];
					$search_cred=$this->request->data['Ledger']['creditors'];
					$debdata=$this->Creditor->findById($this->request->data['Ledger']['creditors']);
					if(!empty($debdata))
					{
						$ledName=$debdata['Creditor']['name'];
					}
				}
				
					 $year=preg_replace('/\s+/', '',$year) ;
				     $year=strip_tags($year);
					
					$cred_month=array("01","02","03","04","05","06","07","08","09","10","11","12");
				 
				  foreach($cred_month as $k=>$dt)
					  {
				
					$conditions = array(
					'Sale.id !=' =>BOOL_FALSE,
					'Sale.ledger_id'=>$search_cred,
					'Sale.is_deleted' =>BOOL_FALSE,
					'Sale.is_active' =>BOOL_TRUE,
					'MONTH(Sale.created)'=> $dt,
					'YEAR(Sale.created)'=> $year,
					);
					
					$saledebit=$this->Sale->find('first',array('conditions'=>$conditions,'fields'=>array('Sale.created','SUM(Sale.total_amount) as debit'),"contain"=>array(
							"Member",
							"Creditor",
							"PaymentTransaction",
							)));
				
					$debit['debit_amount']=$saledebit[0]['debit'];
					
					$conditions = array(
					'SalesReturn.id !=' =>BOOL_FALSE,
					'SalesReturn.is_deleted' =>BOOL_FALSE,
					'SalesReturn.is_active' =>BOOL_TRUE,
					'Sale.ledger_id'=>$search_cred,
					'MONTH(SalesReturn.created)'=> $dt,
					'YEAR(SalesReturn.created)'=> $year,
					);
					
					$returnDebit=$this->SalesReturn->find('first',array('conditions'=>$conditions,'fields'=>array('SalesReturn.created','SUM(SalesReturn.total_amount) as debit')));
					 
					$debit1['debit_amount']=$returnDebit[0]['debit'];
					foreach (array_keys($debit + $debit1 ) as $key) {
						$alldebit[$key] = $debit[$key] + $debit1[$key] ;
					}
					
					$conditions = array(
					'CreditSaleReturn.id !=' =>BOOL_FALSE,
					'CreditSaleReturn.is_deleted' =>BOOL_FALSE,
					'CreditSaleReturn.is_active' =>BOOL_TRUE,
					'CreditSaleReturn.ledger_id'=>$search_cred,
					'MONTH(CreditSaleReturn.date)'=> $dt,
					'YEAR(CreditSaleReturn.date)'=> $year,
					);
					
					$conditions=array_merge($conditions,$cond2);		
					
					$creditreturnCredit=$this->CreditSaleReturn->find('first',array('conditions'=>$conditions,'fields'=>array('CreditSaleReturn.date','SUM(CreditSaleReturn.total_amount) as credit')));
					
					$credit1['credit_amount']=$creditreturnCredit[0]['credit'];
					
					$conditions =array(
						'PaymentTransaction.id !='=>BOOL_FALSE,
						'PaymentTransaction.is_deleted'=>BOOL_FALSE,
						'PaymentTransaction.is_active'=>BOOL_TRUE,
						'PaymentTransaction.type'=>SALE_PAYMENT,
						'Sale.ledger_id'=>$search_cred,
						'MONTH(PaymentTransaction.created)'=> $dt,
					    'YEAR(PaymentTransaction.created)'=> $year,
						
						);
						
					$salecredit=$this->PaymentTransaction->find('first',array('conditions'=>$conditions,'fields'=>array('PaymentTransaction.created','SUM(PaymentTransaction.payment) as credit'),"contain"=>array(
							"Sale",
							)));
					$credit['credit_amount']=$salecredit[0]['credit'];
					
					foreach (array_keys($credit + $credit1 ) as $key) {
						$allcredit[$key] = $credit[$key] + $credit1[$key] ;
					}
					    $yname['year_name']=$year; 
						$name['month_name']=$dt;
						
						$cred_month[$k]=array_merge($yname,$name,$alldebit,$allcredit);
					  }
					
		}
		else if($this->request->data['Ledger']['ledger_Type'] == 5) /*  For Bank Ledger*/
		{		
					if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))				
				{
					$cond['YEAR(Voucher.date) >=']=$this->request->data['Ledger']['from_date'];
					$cond1['YEAR(PaymentTransaction.created) >=']=$this->request->data['Ledger']['from_date'];
					$from_date=$this->request->data['Ledger']['from_date'];
				}	
				if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))				
				{
					$cond['YEAR(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
					$cond1['YEAR(PaymentTransaction.created) <=']=$this->request->data['Ledger']['to_date'];
					$to_date=$this->request->data['Ledger']['to_date'];
				}
				if(isset($this->request->data['Ledger']['Bank']) and !empty($this->request->data['Ledger']['Bank']))				
				{
					$cond['OR']['Voucher.ledger_id']=array($this->request->data['Ledger']['Bank']);
					$cond['OR']['Voucher.cr_ledger_id']=array($this->request->data['Ledger']['Bank']);
					$cond1['OR']['PaymentTransaction.dr_bank']=array($this->request->data['Ledger']['Bank']);
					$search_ledger=$this->request->data['Ledger']['Bank'];
					$bankdata=$this->BankAccount->findById($this->request->data['Ledger']['Bank']);
					if(!empty($bankdata))
					{
						$ledName=$bankdata['BankAccount']['bank_name'];
					}
						
				}
            
					$year=preg_replace('/\s+/', '',$year) ;
				    $year=strip_tags($year);
					 
					$bank_month=array("01","02","03","04","05","06","07","08","09","10","11","12");
				 
				    foreach($bank_month as $k=>$dt)
					  { 
					  
					$conditions = array(
					'Voucher.id !=' => BOOL_FALSE,
					'Voucher.ledger_id' =>$search_ledger,
					'Voucher.is_deleted'=> BOOL_FALSE,
					'Voucher.is_active' => BOOL_TRUE,
					'MONTH(Voucher.date)'=> $dt,
					'YEAR(Voucher.date)'=> $year,
					
					);
					
					$bankvdebit=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('Voucher.date','SUM(Voucher.total) as debit')));
					$debit['debit_amount']=$bankvdebit[0]['debit'];
					
					$conditions= array(
					'PaymentTransaction.id !=' =>BOOL_FALSE,
					'PaymentTransaction.dr_bank'=>$search_ledger,
					'PaymentTransaction.is_deleted '=>BOOL_FALSE,
					'PaymentTransaction.is_active'=>BOOL_TRUE,
					'PaymentTransaction.type'=>PURCHASE_PAYMENT,
					'MONTH(PaymentTransaction.created)'=> $dt,
					'YEAR(PaymentTransaction.created)'=> $year,
					);
					
					
					$paymentbankdebit=$this->PaymentTransaction->find('first',array('conditions'=>$conditions,'fields'=>array('PaymentTransaction.created','SUM(PaymentTransaction.payment) as debit')));
				     $debit1['debit_amount']=$paymentbankdebit[0]['debit'];
						
				    foreach (array_keys($debit + $debit1) as $key) {
						$alldebit[$key] = $debit[$key] + $debit1[$key];
					}
				
					$conditions = array(
					'Voucher.id !=' => BOOL_FALSE,
					'Voucher.cr_ledger_id' =>$search_ledger,
					'Voucher.is_deleted'=> BOOL_FALSE,
					'Voucher.is_active' => BOOL_TRUE,
					'MONTH(Voucher.date)'=> $dt,
					'YEAR(Voucher.date)'=> $year,
					
					);
					
					$bankcreditv=$this->Voucher->find('first',array('conditions'=>$conditions,'fields'=>array('Voucher.date','SUM(Voucher.total) as credit')));
					$credit['credit_amount']=$bankcreditv[0]['credit'];
					
					$conditions = array(
					'PaymentTransaction.id !=' =>BOOL_FALSE,
					'PaymentTransaction.dr_bank'=>$search_ledger,
					'PaymentTransaction.is_deleted '=>BOOL_FALSE,
					'PaymentTransaction.is_active'=>BOOL_TRUE,
					'PaymentTransaction.type'=>SALE_PAYMENT,
					'MONTH(PaymentTransaction.created)'=> $dt,
					'YEAR(PaymentTransaction.created)'=> $year,
						);
					
					
				
					$bankcredip=$this->PaymentTransaction->find('first',array('conditions'=>$conditions,'fields'=>array('PaymentTransaction.created','SUM(PaymentTransaction.payment) as credit')));
					$credit1['credit_amount']=$bankcredip[0]['credit'];
					
					
					 foreach (array_keys($credit + $credit1) as $key) {
						$allcredit[$key] = $credit[$key] + $credit1[$key];
					}
					$yname['year_name']=$year; 
					$name['month_name']=$dt;
					$bank_month[$k]=array_merge($yname,$name,$alldebit,$allcredit);
					  }
		}
		
	}
	$ledgerType=$this->request->data['Ledger']['ledger_Type'];
	$this->set(compact('ledgerType'));
	$this->set(compact('search_ledger'));
	$this->set(compact('month'));
	$this->set(compact('dist_month'));
	$this->set(compact('pub_month'));
	$this->set(compact('cred_month'));
	$this->set(compact('bank_month'));
	

	$datecurr='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		
		$this->set(compact('datecurr'));
		
	
	     $search='<tr class="border_none "><th class="border_none text-ceter"  colspan="7"><h5 class="text-center" style="padding:0;margin:0;"><b>'.$ledName.'</b></h5><span style="float:left;width:100%;text-align:center">Ledger Account</span></th></tr>';
		$this->set(compact('search'));
	
	}
	
	/*
	Amit Sahu
	28.07.17
	get list by ledger type
	*/
	public function getLedgerByTypeType()
	{	
	
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		$this->loadModel('Distributor');
		$this->loadModel('Publisher');
		$this->loadModel('Creditor');
		$this->loadModel('BankAccount');		
		
		if ($this->request->is('ajax')) 
			{
				$data=array();
				$type=$this->request->data['id'];
				if($type==LEDGER_TYPE_LEDGER)
				{
					$data=$this->Ledger->getLedgerList();
				}
				elseif($type==LEDGER_TYPE_BANK)
				{
					$data=$this->BankAccount->getBankAccountList($this->Session->read('Auth.User.user_profile_id'));
				}
				elseif($type==LEDGER_TYPE_DISTRIBUTOR)
				{
					$data=$this->Distributor->getDistributorList();
				}
				elseif($type==LEDGER_TYPE_PUBLISHER)
				{
					$data=$this->Publisher->getPublisherList();
				}
				elseif($type==LEDGER_TYPE_DEBTOR)
				{
					$data=$this->Creditor->getCreditorList();
				}
				
				
				$options='';	
				if(!empty($data))
				{
					foreach($data as $k=>$row){
						
						$options.='<option value='.$k.' >'.$row.'</option>';
					}
				}
				
				echo json_encode(array('status'=>'1000','options'=>$options));
				
			}else{
				
			}
	}

	public function getCreditorsDiscountLevel($id = NULL) {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('DiscountLevel');
		$this->loadModel('Item');
		$this->loadModel('PublishersDiscount');
		$this->loadModel('CategoryDiscount');
		$this->loadModel('Member');
		$this->loadModel('ItemsDiscount');
		$this->loadModel('Creditor');
		
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{
			
			$id=$this->request->data['id'];
			$cdata=$this->Creditor->findById($id);
			$dl_id=DISCOUNT_LEVEL_RETAILER;
			if(!empty($cdata))
			{
				if(!empty($cdata['Creditor']['discount_level']))
				{
				$dl_id=$cdata['Creditor']['discount_level'];
				}
				
			}
		echo json_encode(array("status"=>200,"dl_id"=>$dl_id));


		}
	}
			

/*
	Amit Sahu
	11.08.17
	get free gift received Customer
	*/
	public function getFreeGiftReceivedmember()
	{	
	
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Sale');
		$this->loadModel('SalesDetail');
		
		
		if ($this->request->is('ajax')) 
			{
				$card_no=$this->request->data['card_no'];
				$conditions=array('Sale.is_active'=>BOOL_TRUE,'Sale.card_no'=>$card_no,'Sale.is_deleted'=>BOOL_FALSE,'SalesDetail.item_id'=>39318,'SalesDetail.discount_percent'=>100);
				
				$recdeivedCount=$this->SalesDetail->find('count',array('conditions'=>$conditions));
				
				echo json_encode(array('status'=>'1000','recdeivedCount'=>$recdeivedCount));
				
			}else{
				
			}
	}

	/*
	Amit Sahu
	17.08.17
	View payment Transction
	*/
	/*
	view Voucher 
	22.05.17
	Amit Sahu
	*/
		public function viewPtVoucher() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('PaymentTransaction');
		
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$table="";
					$paymentDetails=$this->PaymentTransaction->find('first',array('conditions'=>array('PaymentTransaction.id'=>$id,'PaymentTransaction.is_deleted'=>BOOL_FALSE)));
					$paym = array(PAYMENT_TYPE_CASH=>'Cash',PAYMENT_TYPE_CHEQUE=>'Cheque',PAYMENT_TYPE_ONLINE=>'Online',PAYMENT_TYPE_VOUCHER=>'Gift Voucher');
							
							$table.="<tr><td>".$paym[$paymentDetails['PaymentTransaction']['payment_method']]."</td><td>".$paymentDetails['PaymentTransaction']['payment']."</td><td>".$paymentDetails['PaymentTransaction']['cheque_no']."</td><td>".$paymentDetails['PaymentTransaction']['bank_name']."</td></tr>
							";
					
					
					echo json_encode(array('status'=>'1000','table'=>$table));
							
		
			}
			else
			{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			}
    }
	/*Kajal kurrewar
    29-08-2017
    get state by state TIN no
    */
    public function getstate() {
   
        $this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax'))
        {       
            $this->loadModel('State');
            $this->loadModel('City');
            $states = array();
           
            if (isset($this->request['data']['id']))
            {
                $states = $this->State->find('first', array(
                    'fields' => array('id','name'),
                    'conditions' => array(
                    'State.state_no' => $this->request['data']['id'],
                    'State.is_deleted' => BOOL_FALSE,
                    'State.is_active' => BOOL_TRUE,                   
                    ),
                    'order'=>array('State.name'=>'ASC')
                ));
				$stateId=$states['State']['id'];
				 $cities = $this->City->find('list', array(
                    'fields' => array('id','name'),
                    'conditions' => array(
                    'City.state_id' => $stateId,
                    'City.is_deleted' => BOOL_FALSE,
                    'City.is_active' => BOOL_TRUE,                   
                    ),
                    'order'=>array('City.name'=>'ASC')
                ));
				$city='<option value="">Select District</option>';
				
				foreach($cities as $k=>$v)
				{
					$city.='<option value="'.$k.'">'.$v.'</option>';
				}
				
                $str=$states['State']['id'];
                header('Content-Type: application/json');
                echo json_encode(array("status"=>200,'stateData'=>$str,'data'=>$city));
                exit();
            }           
        }           
    }
	/*Kajal kurrewar
    29-08-2017
    get existing members detail
    */
   public function getMembersDetail() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		

		if ($this->request->is('ajax')) 
			{
			    $this->loadModel('Ledger');
			    
				
					if(isset($this->request['data']['id']))
					{
				
						$memberDetail = $this->Ledger->find('first', array(
							'fields' => array('Ledger.id','Ledger.name'),
							'contain'=>array('PartyDetail'=>array('mobile','state','gstin','address','pin_code','email','city','State'=>array('state_no'))),
							'conditions' => array(
							'Ledger.id !=' => BOOL_FALSE,
							'PartyDetail.mobile' => $this->request['data']['id'],
							'Ledger.is_deleted' => BOOL_FALSE,
							'Ledger.is_active' => BOOL_TRUE, 
							'Ledger.group_id' => GROUP_SUNDRY_DEBTOR_ID, 
							'Ledger.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),							
							),
							'order'=>array('Ledger.id'=>'ASC')
						));
					 if(!empty($memberDetail))
					 {	
                          $name="";
						 $gstno="";
						 $email="";
						 $address="";
						 $pin="";
						 $state_no="";
						 $cityId="";
						 $stateId="";
		
					       $id=$memberDetail['Ledger']['id'];
					      $name=$memberDetail['Ledger']['name'];
						  $gstno=$memberDetail['PartyDetail']['gstin'];
						  $email=$memberDetail['PartyDetail']['email'];
						  $address=$memberDetail['PartyDetail']['address'];
						  $pin=$memberDetail['PartyDetail']['pin_code'];
						  
						  $cityId=$memberDetail['PartyDetail']['city'];
                          $stateId=$memberDetail['PartyDetail']['state'];
                          $state_no=$memberDetail['PartyDetail']['State']['state_no'];
						  

						  echo json_encode(array('status'=>'1000','id'=>$id,'city'=>$cityId,"state"=>$stateId,"name"=>$name,"gst"=>$gstno,"email"=>$email,"address"=>$address,"pin"=>$pin,'state_no'=>$state_no));

					 
						

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
	kajal kurrewar 
	get selected vendor detail in add po
	07-08-2017
	*/
	 public function setVendorName() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');	
        $this->loadModel('Distributor');		

		if ($this->request->is('ajax')) 
			{
			        if(isset($this->request['data']['id']))
					{
				
						$vendorsNmae = $this->Distributor->find('first', array(
							'fields' => array('id','name','gstin','address','mobile','email','city','state'),
							'conditions' => array(
							'Distributor.id !=' => BOOL_FALSE,
							'Distributor.id' => $this->request['data']['id'],
							'Distributor.is_deleted' => BOOL_FALSE,
							'Distributor.is_active' => BOOL_TRUE,                   
							),
							'order'=>array('Distributor.id'=>'ASC')
						));
						
						$name=$vendorsNmae['Distributor']['name'];
						$gstn=$vendorsNmae['Distributor']['gstin'];
						$address=$vendorsNmae['Distributor']['address'];
						$mobile=$vendorsNmae['Distributor']['mobile'];
						$email=$vendorsNmae['Distributor']['email'];
						$city=$vendorsNmae['Distributor']['city'];
						$state=$vendorsNmae['Distributor']['state'];
						
						 echo json_encode(array('status'=>'1000','name'=>$name,'gstn'=>$gstn,'address'=>$address,'mobile'=>$mobile,'email'=>$email,'city'=>$city,'state'=>$state));
					}
            }
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
		/*
	Kajal kurrewar
	13-08-2017
	get Item Data 
	*/
	public function getItemSetData() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');		
		$this->loadModel('Unit');
			
        if ($this->request->is('ajax')) 
		{
			
			if(!empty($this->request->data["id"])){
				$id = strtoupper($this->request->data["id"]);
							
				$item = $this->Item->find("first",array(
				"conditions" =>array(
					"Item.id"=>$id,
					"Item.is_active"=>BOOL_TRUE,
					"Item.is_deleted"=>BOOL_FALSE,
				),
				'contain'=>array("Unit"),
			
				));
				
				if(!empty($item)){
					$name=$item['Item']['name'];
					$price=$item['Item']['price'];
					$gst=$item['Item']['gst_slab'];
				    $gstSlab= sprintf('%g',$gst);
					$hsn=$item['Item']['hsn'];
					$unit=$item['Item']['unit'];
					$sp=$item['Item']['sp'];
					$nill_rated=$item['Item']['nill_rated'];
					$item_type=$item['Item']['item_type'];
					$cess_type=$item['Item']['cess_type'];
					$slab_id=$item['Item']['gst_slab_id'];
					
					$cess_per=$item['Item']['cess_per'];
					$cess_amt=$item['Item']['cess_amt'];
					$Units = explode(",",$unit);
                   
 					 $uname=array();
							if(!empty($Units))
							{
								foreach($Units as $k=>$v)
									{
									$unitdata=$this->Unit->findById($v);
									
								    $uname[]=$unitdata['Unit']['id'];
									
									}
								
							}
						
					echo json_encode(array("status"=>1000,"id"=>$id,"name"=>$name,"price"=>$price,"gstSlab"=>$gstSlab,"hsn"=>$hsn,"unit"=>$uname,'sp'=>$sp,'nill_rated'=>$nill_rated,'item_type'=>$item_type,'cess_type'=>$cess_type,'cess_per'=>$cess_per,'cess_amt'=>$cess_amt,'slab_id'=>$slab_id));
				}
				
			}
			
		}	
	}
	/*
	kajal kurrewar 
	get alernate Unit List
	14-09-2017
	*/
	 public function getalternateUnits() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');	
        $this->loadModel('Unit');		

		if ($this->request->is('ajax')) 
			{
			   if(isset($this->request['data']['id']))
					{
						 $unit = $this->Unit->find('first', array(
						    'conditions' => array('Unit.id' =>$this->request['data']['id'],
							'Unit.is_deleted' => BOOL_FALSE,
							'Unit.is_active' => BOOL_TRUE),
							));
						
				        $unitList = $this->Unit->find('first', array(
							'fields' => array('id','name'),
							'conditions' => array(
							'Unit.id !=' => BOOL_FALSE,
							'Unit.sequence <' => $unit['Unit']['sequence'],
							'Unit.group' => $unit['Unit']['group'],
							'Unit.is_deleted' => BOOL_FALSE,
							'Unit.is_active' => BOOL_TRUE,                   
							),
							'order'=>array('Unit.id'=>'DESC')
						));
						if(!empty($unitList))
						{
						$str='<option value="">Select Unit</option>';
						
							$str.='<option value="'.$unitList['Unit']['id'].'">'.$unitList['Unit']['name'].'</option>';
						}
						header('Content-Type: application/json');
						echo json_encode(array('data'=>$str));
						exit();	
				    }
            }
			
    }
	public function convertItemUnit()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Item');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$item_id=$this->request->data['item_id'];
				$qty=$this->request->data['qty'];
				$unit=$this->request->data['unit'];
				$itemData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$item_id),'fields'=>array('Item.id','Item.unit','Item.alt_unit')));
				
				if($itemData['Item']['unit']==$unit)
				{
					$convqty=$qty;
				}elseif($itemData['Item']['alt_unit']==$unit){
					$value=$this->Unitchange->change($item_id,$qty, $unit);
					$convqty=$value['qty'];
				}
				echo json_encode(array('status'=>'1000','message'=>'Group added successfully', 'convqty'=>$convqty));
				 
				
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	
	public function getLedgerGstRate()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$id=$this->request->data['id'];

				$ledgerData=$this->Ledger->find('first',array('conditions'=>array('Ledger.id'=>$id),'fields'=>array('Ledger.id','Ledger.gst_rate')));
				
				if(!empty($ledgerData))
				{
					$gst_rate=$ledgerData['Ledger']['gst_rate'];
					echo json_encode(array('status'=>'1000', 'gst_rate'=>$gst_rate));
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
	Ledger
	Amit Sahu
	14.03.17
	*/
	public function ledger($year=NULL,$month=NULL)
    {
        $year = $this->Encryption->decrypt($year);
        $month = $this->Encryption->decrypt($month);
   
        $roleId=$this->Session->read('Auth.User.role_id');
        if($roleId==GODOWN_ROLE_ID){
            $this->layout = ('godown/inner');
        }elseif($roleId==SHOP_ROLE_ID)
        {
            $this->layout = ('shop/inner');
        }else{
        $this->layout = ('office/inner');
        }
        $this->loadModel('Ledger');
        $this->loadModel('Voucher');
        $this->loadModel('Distributor');
        $this->loadModel('Purchase');
        $this->loadModel('Sale');
        $this->loadModel('BankAccount');
        $this->loadModel('PaymentTransaction');
        $from_date="";
        $to_date="";
       
        if(!empty($year) and !empty($month))
        {
            $firstdate=$year."-".$month."-01";
            $lastdate="";
           
            $date = new DateTime($year."-".$month);
            $date->modify('last day of this month');
            $lastdate=$date->format('Y-m-d');
       
       
            $session['from_date']=$firstdate;
            $session['to_date']=$lastdate;
            $from_date=$firstdate;
            $to_date=$lastdate;
            $this->Session->write('LedgerSummarySearch',array_merge($this->Session->read('LedgerSummarySearch'),$session));
        }
       
        $ledgers=$this->Ledger->getLedgerList();
        $this->set(compact('ledgers'));   
       
        $crblnc=0;
        $dbblnc=0;
        $creditAmt=0;
        $debAmt=0;
		
        $distributor=$this->Distributor->getDistributorList($this->Session->read('Auth.User.user_profile_id'));
        $this->set(compact('distributor'));  
        $bankAccounts_list=$this->BankAccount->getBankAccountList($this->Session->read('Auth.User.user_profile_id'));
        $this->set(compact('bankAccounts_list'));   
       
        $cond=array();
        $cond1=array();
        $cond2=array();
        $cond3=array();
        $cond4=array();
        $cond5=array();
   
        $search_ledger="";
        $search_bankledger="";
        $search_pub="";
        $search_cred="";
        $search_dis="";
        $ledName="";
        $distName="All";
        $pubName="All";
        $debName="All";
        $bankName="All";
       
        if($this->Session->read('Auth.User.location_id')==BOOL_FALSE)
        {
        $locationId="";   
        }else{
        $locationId=$this->Session->read('Auth.User.location_id');
        }
        if(isset($this->request->data['Ledger']))
        {                   
            $this->Session->write('LedgerSummarySearch',$this->request->data['Ledger']);
        }
        else
        {   
            $this->request->data['Ledger']=$this->Session->read('LedgerSummarySearch');       
        }   
        if(isset($this->request->data['Ledger']))               
        {
            if($this->request->data['Ledger']['ledger_Type'] == 1)
            {
                if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))               
                {
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
                if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))               
                {
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
                    //$cond['OR']['Voucher.ledger_id']=array($this->request->data['Ledger']['ledger']);
                    //$cond['OR']['Voucher.cr_ledger_id']=array($this->request->data['Ledger']['ledger']);
                    $search_ledger=$this->request->data['Ledger']['ledger'];
                    $ledgerdata=$this->Ledger->findById($this->request->data['Ledger']['ledger']);
                    if(!empty($ledgerdata))
                    {
                        $ledName=$ledgerdata['Ledger']['name'];
                    }
                }
               
                               
                $conditions = array(
                    'Voucher.id !=' => BOOL_FALSE,
                    'Voucher.is_deleted' => BOOL_FALSE,
                    'Voucher.is_active' => BOOL_TRUE,
					'Voucher.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
					 'OR'=>array(array(
                   
                    'Voucher.dr_type' =>LEDGER_TYPE_LEDGER,
                    'Voucher.ledger_id' =>$search_ledger),
                array(
                    'Voucher.cr_type' =>LEDGER_TYPE_LEDGER,
                    'Voucher.cr_ledger_id' =>$search_ledger
                   
                    ))
                );
                $conditions=array_merge($conditions,$cond);       
               
                $fields=array('Voucher.id','Voucher.ledger_id','Voucher.cr_ledger_id','Voucher.type','Voucher.cr_type','Voucher.date','Voucher.total','CrLedger.name','Ledger.name');
               
                $vouchers=$this->Voucher->find('all',array('conditions'=>$conditions,'fields'=>$fields,'limit'=>PAGINATION_LIMIT_1));   
               
                       
                       
                       
                        
            }
        /*------------------------------------------------------------------------Get Distributor Ledger---------------------------------------------------------------*/
        else if($this->request->data['Ledger']['ledger_Type'] == 2)
            {
                if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
                {
                    $cond1['DATE(Purchase.created) >=']=$this->request->data['Ledger']['from_date'];
                
                    $cond4['DATE(PaymentTransaction.created) >=']=$this->request->data['Ledger']['from_date'];
                    $cond3['Voucher.date >=']=$this->request->data['Ledger']['from_date'];
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
                if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
                {
                    $cond1['DATE(Purchase.created) <=']=$this->request->data['Ledger']['to_date'];
                    $cond2['DATE(PurchaseReturn.created) <=']=$this->request->data['Ledger']['to_date'];
                    $cond4['DATE(PaymentTransaction.created) <=']=$this->request->data['Ledger']['to_date'];
                    $cond3['Voucher.date <=']=$this->request->data['Ledger']['to_date'];
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
                if(isset($this->request->data['Ledger']['distributor']) and !empty($this->request->data['Ledger']['distributor']))
                {
                    $cond1['Purchase.distributor_id']=$this->request->data['Ledger']['distributor'];
                    $search_dis=$this->request->data['Ledger']['distributor'];
                    $search_ledger=$this->request->data['Ledger']['distributor'];
                    $distdata=$this->Distributor->findById($this->request->data['Ledger']['distributor']);
                    if(!empty($distdata))
                    {
                        $ledName=$distdata['Distributor']['name'];
                    }
                }
                //Get Purchase                           
                $conditions = array(
                'Purchase.id !=' =>BOOL_FALSE,
                'Purchase.is_deleted' =>BOOL_FALSE,
                'Purchase.is_active' =>BOOL_TRUE,
				'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
                );
                $conditions1=array_merge($conditions,$cond1);
                $fields1=array('Purchase.id','Purchase.created','Purchase.total_amount');
                $purchases=$this->Purchase->find('all', array(
                    "conditions"=>$conditions1,
                    "contain"=>array(
                    "PaymentTransaction",
                    ),
                    'order'=>array('Purchase.id'=>'ASC'),
					'limit'=>PAGINATION_LIMIT_1
				
                ));
                $this->set(compact('purchases'));
                //Get Payment Transaction
                $conditions = array(
                'PaymentTransaction.id !=' =>BOOL_FALSE,
                'PaymentTransaction.is_deleted' =>BOOL_FALSE,
                'PaymentTransaction.is_active' =>BOOL_TRUE,
                'PaymentTransaction.type' =>PURCHASE_PAYMENT,
                'DATE(PaymentTransaction.created) >=' =>$from_date,
                'DATE(PaymentTransaction.created) <=' =>$to_date,               
                'Purchase.distributor_id' =>$search_dis,
                'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
                );
               
                $disPaymentTrans=$this->PaymentTransaction->find('all',array('conditions'=>$conditions,'contain'=>array('Purchase'),'recursive'=>2,'limit'=>PAGINATION_LIMIT_1));
				/*echo "<pre>";
				print_r($disPaymentTrans);
				echo "</pre>";*/
                $this->set(compact('disPaymentTrans'));
               

                     
        }
       
        /*------------------------------------------------------------------------End Get Distributor Ledger---------------------------------------------------------------*/
     
        else if($this->request->data['Ledger']['ledger_Type'] == 5) /*  For Bank Ledger*/
        {       
                    if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))               
                {
                    $cond['DATE(Voucher.created) >=']=$this->request->data['Ledger']['from_date'];
                    $cond1['DATE(PaymentTransaction.created) >=']=$this->request->data['Ledger']['from_date'];
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
                if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))               
                {
                    $cond['DATE(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
                    $cond1['OR']['DATE(PaymentTransaction.created) <=']=$this->request->data['Ledger']['to_date'];
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
                if(isset($this->request->data['Ledger']['Bank']) and !empty($this->request->data['Ledger']['Bank']))               
                {
                    //$cond['OR']['Voucher.ledger_id']=array($this->request->data['Ledger']['Bank']);
                  // $cond['OR']['Voucher.cr_ledger_id']=array($this->request->data['Ledger']['Bank']);
                    $cond1['OR']['PaymentTransaction.dr_bank']=array($this->request->data['Ledger']['Bank']);
                    $search_ledger=$this->request->data['Ledger']['Bank'];
                    $bankdata=$this->BankAccount->findById($this->request->data['Ledger']['Bank']);
                    if(!empty($bankdata))
                    {
                        $ledName=$bankdata['BankAccount']['bank_name'];
                    }
                       
                }
                   
            $conditions = array(
                'Voucher.id !=' => BOOL_FALSE,
                'Voucher.is_deleted' => BOOL_FALSE,
                'Voucher.is_active' => BOOL_TRUE,
				'Voucher.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
                'OR'=>array(array(
                    'Voucher.dr_type' =>LEDGER_TYPE_BANK,
                    'Voucher.ledger_id' =>$search_ledger),
                array(
                    'Voucher.cr_type' =>LEDGER_TYPE_BANK,
                    'Voucher.cr_ledger_id' =>$search_ledger
                   
                    ))
               
            );
           
                $conditions=array_merge($conditions,$cond);
                $contain=array('BankAccount'=>array('bank_name'),'DrBankAccount'=>array('bank_name'));           
           
                $fields=array('Voucher.id','Voucher.ledger_id','Voucher.cr_ledger_id','Voucher.type','Voucher.cr_type','Voucher.dr_type','Voucher.date','Voucher.total','CrLedger.name','Ledger.name');
                $bankvouchers=$this->Voucher->find('all', array(
                "conditions"=>$conditions,
                'limit'=>PAGINATION_LIMIT_1,
                'contain'=>$contain
                ));
          
						                        
        }
               
    }
    else{
                //From Date
                $year=date("Y");
                $month=date("m");
               
                if($month <=3)
                {
                $yearOpen=$year-1;
                $from_date=$yearOpen.'-04-01';
                }else{
                $from_date=$year.'-04-01';
                }
               
                //To Date
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
		$crblnc=0;
		$dbblnc=0;
    $this->set(compact('crblnc'));
   
    $this->set(compact('dbblnc'));
   
    $this->set(compact('creditAmt'));
    $this->set(compact('debAmt'));
    $this->set(compact('vouchers'));
	$ledgerType=$this->request->data['Ledger']['ledger_Type'];
	$this->set(compact('ledgerType'));
    $this->set(compact('bankvouchers'));
    $this->set(compact('paymentvoucher'));
    $this->set(compact('SalesReturn'));
    $this->set(compact('from_date'));
    $this->set(compact('to_date'));
   
    $this->set(compact('search_ledger'));
   
   
    $date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
       
        $this->set(compact('date'));
       
        $search='<tr class="border_none "><th class="border_none text-ceter"  colspan="7"><h5 class="text-center" style="padding:0;margin:0;"><b>'.$ledName.'</b></h5><span style="float:left;width:100%;text-align:center">Ledger Account</span></th></tr>';
        $this->set(compact('search'));
       
    }
	/*
	Load more date ledger
	Amit Sahu , kajal kurrewar
	26.05.17 , 27-05-17
	*/
	
	public function loadMoreLedger()
	{	

		
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		$this->loadModel('Voucher');

		$this->loadModel('Distributor');



		$this->loadModel('Purchase');
		$this->loadModel('Sale');



		$this->loadModel('PaymentTransaction');
		
		if ($this->request->is('ajax')) 
			{
				
		$cond=array();
		$cond1 = array();
		$cond2 = array();
		$cond3 = array();
		$lastVoucherId=0;
		$last_purchse_id=0;
		$last_payment_id=0;
		$lastDisVoucherId=0;
		$lasPurchaseRetID=0;
		$lastSaleID=0;
		$lastprId=0;
		$creditSaleReturnId=0;
		$voucherBankLast=0;
		$paymentTLast=0;
		$last_purchase_id=0;
		$salePayLastId=0;
		$lastBankVoucherId=0;
		$lastpaymentBAnkID=0;
		$search_ledger="";
		
		$lastrowID="";
	
		
		$data="";
		if($this->Session->read('Auth.User.location_id')==BOOL_FALSE)
		{
		$locationId="";	
		}else{
		$locationId=$this->Session->read('Auth.User.location_id');
		}
		if(isset($this->request->data['Ledger']))
		{					
			$this->Session->write('LedgerSummarySearch',$this->request->data['Ledger']);
		}
		else
		{	
			$this->request->data['Ledger']=$this->Session->read('LedgerSummarySearch');		
		}	
				if(isset($this->request->data['Ledger']))				
				{
					if($this->request->data['Ledger']['ledger_Type'] == 1)
					{
						if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))				
						{
							$cond['DATE(Voucher.date) >=']=$this->request->data['Ledger']['from_date'];
						}	
						if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))				
						{
							$cond['DATE(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
						}
					
						if(isset($this->request->data['Ledger']['ledger']) and !empty($this->request->data['Ledger']['ledger']))				
						{
							
							$search_ledger=$this->request->data['Ledger']['ledger'];
						}
						
						
						

						$last_voucher_id=$this->request->data['voucher_id'];	
						if($last_voucher_id !=0)
						{							
						  $conditions = array(
						'Voucher.id !=' => BOOL_FALSE,
						'Voucher.id >' => $last_voucher_id,
						'Voucher.is_deleted' => BOOL_FALSE,
						'Voucher.is_active' => BOOL_TRUE,
						'Voucher.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
						 'OR'=>array(array(

						'Voucher.dr_type' =>LEDGER_TYPE_LEDGER,
						'Voucher.ledger_id' =>$search_ledger),
						array(
						'Voucher.cr_type' =>LEDGER_TYPE_LEDGER,
						'Voucher.cr_ledger_id' =>$search_ledger

						))
						);
						$conditions=array_merge($conditions,$cond);       

						$fields=array('Voucher.id','Voucher.ledger_id','Voucher.cr_ledger_id','Voucher.type','Voucher.cr_type','Voucher.date','Voucher.total','CrLedger.name','Ledger.name');

						$vouchers=$this->Voucher->find('all',array('conditions'=>$conditions,'fields'=>$fields,'limit'=>PAGINATION_LIMIT_1)); 
						
						if(!empty($vouchers))
						{
							
							foreach($vouchers as $row)
							{
								$debit="";
								$credit="";
								
								
								
								if($search_ledger==$row['Voucher']['ledger_id'])
									{
										
										$credit=$row['Voucher']['total'];
									}			
									else if($search_ledger==$row['Voucher']['cr_ledger_id'])
									{
									
										$debit=$row['Voucher']['total'];
									}
									$type=array(
									PAYMENT=>'Payment',
									CONTRA=>'Contra',
									RECEIPT=>'Receipt',
									GENERAL=>'Journal',
									);	
									 if($row['Voucher']['cr_type']==BOOL_TRUE){
										$payment="Bank";
									} else{
										$payment= "Cash";
									}
								$data.='<tr class="tdDate" onclick="viewVoucher('.$row['Voucher']['id'].')">
									<td class="closing_bg">'.date('d-m-Y' ,strtotime($row['Voucher']['date'])).'</td>									
									<td class="closing_bg">'.$row['VoucherDetail'][0]['naration'].'</td>
									<td class="closing_bg">'.$type[$row['Voucher']['type']].'</td>
									<td class="closing_bg">'.$payment.'</td>
									<td class="closing_bg">'.$row['Voucher']['id'].'</td>
									<td class="closing_bg debitAmt">'.$debit.'</td>
									<td class="closing_bg creditAmt">'.$credit.'</td>	
									
								</tr>';
								$lastVoucherId=$row['Voucher']['id'];
							}
						
					}
				}				
				}
				else if($this->request->data['Ledger']['ledger_Type'] == 2)
            {
                if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))
                {
                    $cond1['DATE(Purchase.created) >=']=$this->request->data['Ledger']['from_date'];
                    $cond2['DATE(PurchaseReturn.created) >=']=$this->request->data['Ledger']['from_date'];
                    $cond4['DATE(PaymentTransaction.created) >=']=$this->request->data['Ledger']['from_date'];
                    $cond3['Voucher.date >=']=$this->request->data['Ledger']['from_date'];
                    $from_date=$this->request->data['Ledger']['from_date'];
                }
                else{
                $year=date("Y");
                $month=date("m");
                if($month <=3)
                {
                $yearOpen=$year-1;
                $from_date=$yearOpen.'-04-01';
                }
				else{
                $from_date=$year.'-04-01';
                }
                }
                if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))
                {
                    $cond1['DATE(Purchase.created) <=']=$this->request->data['Ledger']['to_date'];
                    $cond2['DATE(PurchaseReturn.created) <=']=$this->request->data['Ledger']['to_date'];
                    $cond4['DATE(PaymentTransaction.created) <=']=$this->request->data['Ledger']['to_date'];
                    $cond3['Voucher.date <=']=$this->request->data['Ledger']['to_date'];
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
                if(isset($this->request->data['Ledger']['distributor']) and !empty($this->request->data['Ledger']['distributor']))
                {
                    $cond1['Purchase.distributor_id']=$this->request->data['Ledger']['distributor'];
                    
                    $search_dis=$this->request->data['Ledger']['distributor'];
                    $search_ledger=$this->request->data['Ledger']['distributor'];
                    $distdata=$this->Distributor->findById($this->request->data['Ledger']['distributor']);
                    if(!empty($distdata))
                    {
                        $ledName=$distdata['Distributor']['name'];
                    }
                }
            //Get Purchase  
				$last_purchase_id=$this->request->data['purchase_id'];	
				
				if($last_purchase_id !=0)
				{					
                $conditions = array(
                'Purchase.id !=' =>BOOL_FALSE,
                'Purchase.id > ' =>$last_purchase_id,
                'Purchase.is_deleted' =>BOOL_FALSE,
                'Purchase.is_active' =>BOOL_TRUE,
                'Purchase.purchase_status' =>ORDER_STATUS_PURCHASE,
            'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
                );
                $conditions1=array_merge($conditions,$cond1);
				
                $fields1=array('Purchase.id','Purchase.created','Purchase.total_amount');
                $purchases=$this->Purchase->find('all', array(
                    "conditions"=>$conditions1,
                    "contain"=>array(
                    
                    "Distributor",
                    "PaymentTransaction",
                    ),
                    'order'=>array('Purchase.id'=>'ASC'),
					'limit'=>PAGINATION_LIMIT_1
					
                ));
						
					$data='';
					
					if(!empty($purchases))
						{
							
							foreach($purchases as $row)
							{
								$dr_t_amt=0;
									
								$data.='<tr class="tdDate" onclick="viewPurchase('.$row['Purchase']['id'].')">
									<td class="closing_bg">'.date('d-m-Y' ,strtotime($row['Purchase']['created'])).'</td>									
									<td class="closing_bg">Purchase</td>
									<td class="closing_bg">Purchase</td>
									<td class="closing_bg">N/A</td>
									<td class="closing_bg">#'.$row["Purchase"]["id"].'</td>
									<td class="closing_bg"></td>
									<td class="closing_bg creditAmt">'.$dr_t_amt.'</td>
										
								</tr>';
								
							$last_purchse_id=$row['Purchase']['id'];
							}
							
						}
				}
						//Get Payment Transaction
						$last_payment=$this->request->data['lastPaymentId'];
						if($last_payment !=0)
						{
                $conditions = array(
                'PaymentTransaction.id !=' =>BOOL_FALSE,
                'PaymentTransaction.id >' =>$last_payment,
                'PaymentTransaction.is_deleted' =>BOOL_FALSE,
                'PaymentTransaction.is_active' =>BOOL_TRUE,
                'PaymentTransaction.type' =>PURCHASE_PAYMENT,
                'DATE(PaymentTransaction.created) >=' =>$from_date,
                'DATE(PaymentTransaction.created) <=' =>$to_date,               
                'Purchase.distributor_id' =>$search_dis,
				'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
                );
               
               // $disPaymentTrans=$this->PaymentTransaction->find('all',array('conditions'=>$conditions,'recursive'=>-1,'limit'=>PAGINATION_LIMIT_1,
                $disPaymentTrans=$this->PaymentTransaction->find('all',array('conditions'=>$conditions,'contain'=>array('Purchase'),'recursive'=>2,'limit'=>2,
                
                ));
				
				if(!empty($disPaymentTrans))
				{
					foreach($disPaymentTrans as $row){
									
									
									$pay_meth='';
									$rowdata1='';
									$cr_t_amt=0;
									$cr_t_amt = $row['PaymentTransaction']["payment"];
									
								
									
									$pay_meth = array(PAYMENT_TYPE_CASH=>"Cash",PAYMENT_TYPE_CHEQUE=>"Cheque",PAYMENT_TYPE_ONLINE=>"Card/Net Banking",PAYMENT_TYPE_VOUCHER=>"Gift Voucher");
									$rowdata1 = $pay_meth[$row['PaymentTransaction']["payment_method"]];
										
									$data.='<tr class="tdDate" onclick="viewPtVoucher('.$row['PaymentTransaction']['id'].')">
									<td class="closing_bg">'.date('d-m-Y' ,strtotime($row['PaymentTransaction']['created'])).'</td>									
									<td class="closing_bg">Payment</td>
									<td class="closing_bg">Payment</td>
									<td class="closing_bg">'.$rowdata1.'</td>
									<td class="closing_bg">#'.$row['PaymentTransaction']['id'].'</td>
									<td class="closing_bg debitAmt" >'.$cr_t_amt.'</td>		
									<td class="closing_bg"></td>
																
								   </tr>';
								   $last_payment_id=$row['Purchase']['id'];
							}
						}
					}
				//Voucher data Amit
				$lastDisVid=$this->request->data['last_dist_voucher_id'];
				if($lastDisVid !=0)
				{
                $conditions3 = array(
                'Voucher.id !=' =>BOOL_FALSE,
                'Voucher.id >' =>$lastDisVid,
                'Voucher.is_deleted' =>BOOL_FALSE,
                'Voucher.is_active' =>BOOL_TRUE,
           'Voucher.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
                'OR'=>array(array(
                   
                    'Voucher.dr_type' =>LEDGER_TYPE_DISTRIBUTOR,
                    'Voucher.ledger_id' =>$search_dis),
                array(
                    'Voucher.cr_type' =>LEDGER_TYPE_DISTRIBUTOR,
                    'Voucher.cr_ledger_id' =>$search_dis
                   
                    ))              
                       
                );
                $conditions3=array_merge($conditions3,$cond3);
                $fields3=array('Voucher.id','Voucher.type','Voucher.date','Voucher.total','Voucher.ledger_id','BankAccount.bank_name','Voucher.cr_ledger_id','DrBankAccount.bank_name','Voucher.cr_type','Voucher.dr_type');
                $distVoucher=$this->Voucher->find('all', array(
                        "conditions"=>$conditions3,
                        'fields'=>$fields3,
						'limit'=>PAGINATION_LIMIT_1,
						
                        'order'=>array('Voucher.id'=>'ASC')
                        ));
               if(!empty($distVoucher))
						{
							
							foreach($distVoucher as $row)
							{
								$debit="";
								$credit="";
								$ledger="";
								
								
								if($search_ledger==$row['Voucher']['ledger_id'])
									{
									
										$credit=$row['Voucher']['total'];
									}			
									else if($search_ledger==$row['Voucher']['cr_ledger_id'])
									{
										
										$debit=$row['Voucher']['total'];
									}
									$type=array(
									PAYMENT=>'Payment',
									CONTRA=>'Contra',
									RECEIPT=>'Receipt',
									GENERAL=>'Journal',
									);	
									 if($row['Voucher']['cr_type']==BOOL_TRUE){
										$payment="Bank";
									} else{
										$payment= "Cash";
									}
									if(!empty($row['VoucherDetail'][0]['naration']))
									{
										$naration=$row['VoucherDetail'][0]['naration'];
									}
									else{
										$naration='payment';
									}
								$data.='<tr class="tdDate" onclick="viewVoucher('.$row['Voucher']['id'].')">
									<td class="closing_bg">'.date('d-m-Y' ,strtotime($row['Voucher']['date'])).'</td>									
									<td class="closing_bg">'.ucfirst($naration).'</td>
									<td class="closing_bg">'.$type[$row['Voucher']['type']].'</td>
									<td class="closing_bg">'.$payment.'</td>
									<td class="closing_bg">'.$row['Voucher']['id'].'</td>
									<td class="closing_bg debitAmt">'.$debit.'</td>
									<td class="closing_bg creditAmt">'.$credit.'</td>	
									
								</tr>';
								$lastDisVoucherId=$row['Voucher']['id'];
							}
						}
					}
				//End voucher data Amit	
				
				}
			
                 	else if($this->request->data['Ledger']['ledger_Type'] == 5) /*  For Bank Ledger*/
		{		
					if(isset($this->request->data['Ledger']['from_date']) and !empty($this->request->data['Ledger']['from_date']))               
                {
                    $cond['DATE(Voucher.created) >=']=$this->request->data['Ledger']['from_date'];
                    $cond1['DATE(PaymentTransaction.created) >=']=$this->request->data['Ledger']['from_date'];
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
                if(isset($this->request->data['Ledger']['to_date']) and !empty($this->request->data['Ledger']['to_date']))               
                {
                    $cond['DATE(Voucher.date) <=']=$this->request->data['Ledger']['to_date'];
                    $cond1['OR']['DATE(PaymentTransaction.created) <=']=$this->request->data['Ledger']['to_date'];
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
                if(isset($this->request->data['Ledger']['Bank']) and !empty($this->request->data['Ledger']['Bank']))               
                {
                   
                    $cond1['OR']['PaymentTransaction.dr_bank']=array($this->request->data['Ledger']['Bank']);
                    $search_ledger=$this->request->data['Ledger']['Bank'];
                    
                }
				$voucherBankLast=$this->request->data['last_bankVou_id'];
				if($voucherBankLast !=0)
				{
					$conditions = array(
					'Voucher.id !=' => BOOL_FALSE,
					'Voucher.is_deleted' => BOOL_FALSE,
					'Voucher.is_active' => BOOL_TRUE,
					'Voucher.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
						'OR'=>array(array(                   
                    'Voucher.dr_type' =>LEDGER_TYPE_BANK,
                    'Voucher.ledger_id' =>$search_ledger),
						array(
                    'Voucher.cr_type' =>LEDGER_TYPE_BANK,
                    'Voucher.cr_ledger_id' =>$search_ledger                   
                    ))               
					);
           
					$conditions=array_merge($conditions,$cond);
					$contain=array('BankAccount'=>array('bank_name'),'DrBankAccount'=>array('bank_name'));           

					$fields=array('Voucher.id','Voucher.ledger_id','Voucher.cr_ledger_id','Voucher.type','Voucher.cr_type','Voucher.dr_type','Voucher.date','Voucher.total','CrLedger.name','Ledger.name');
					$bankvouchers=$this->Voucher->find('all', array(
					"conditions"=>$conditions,
					'limit'=>PAGINATION_LIMIT_1,
					'contain'=>$contain
					));
					
						 if(!empty($bankvouchers))
						{
							foreach($bankvouchers as $row)
							{
								$debit="";
								$credit="";
								$ledger="";
								if($search_ledger==$row['Voucher']['ledger_id'] and $row['Voucher']['cr_ledger_id'] !=CASH_HANUMAN_LEDGER and $row['Voucher']['cr_ledger_id'] !=PAYMENT 
		and $row['Voucher']['cr_ledger_id'] !=CONTRA and  $row['Voucher']['cr_ledger_id'] !=RECEIPT and $row['Voucher']['cr_ledger_id'] !=GENERAL 
		)
									{
										$credit=$row['Voucher']['total'];
									}			
									else if($search_ledger==$row['Voucher']['cr_ledger_id'] ||  $row['Voucher']['cr_ledger_id']==CASH_HANUMAN_LEDGER
		|| $row['Voucher']['cr_ledger_id']==PAYMENT || $row['Voucher']['cr_ledger_id']==CONTRA || $row['Voucher']['cr_ledger_id']==RECEIPT || $row['Voucher']['cr_ledger_id']==GENERAL
		)
									{
										$debit=$row['Voucher']['total'];
									}
									$type=array(
									PAYMENT=>'Payment',
									CONTRA=>'Contra',
									RECEIPT=>'Receipt',
									GENERAL=>'Journal',
									);	
									 if($row['Voucher']['cr_type']==BOOL_TRUE){
										$payment="Bank";
									} else{
										$payment= "Cash";
									}
									if(!empty($row['VoucherDetail'][0]['naration']))
									{
										$naration=$row['VoucherDetail'][0]['naration'];
									}
									else{
										$naration='payment';
									}
								$data.='<tr class="tdDate" onclick="viewVoucher('.$row['Voucher']['id'].')">
									<td class="closing_bg">'.date('d-m-Y' ,strtotime($row['Voucher']['date'])).'</td>									
									<td class="closing_bg">'.ucfirst($naration).'</td>
									<td class="closing_bg">'.$type[$row['Voucher']['type']].'</td>
									<td class="closing_bg">'.$payment.'</td>
									<td class="closing_bg">'.$row['Voucher']['id'].'</td>
									<td class="closing_bg debitAmt">'.$debit.'</td>
									<td class="closing_bg creditAmt">'.$credit.'</td>										
								</tr>';
								$lastBankVoucherId=$row['Voucher']['id'];
							}
						}		
				}
				$paymentTLast=$this->request->data['lasPatID'];
				if($paymentTLast !=0)
				{
				 $conditions1= array(
				'PaymentTransaction.id !=' => BOOL_FALSE,
				'PaymentTransaction.id >' => $paymentTLast,
				'PaymentTransaction.is_deleted' => BOOL_FALSE,
				'PaymentTransaction.is_active' => BOOL_TRUE,
				'PaymentTransaction.type'=>array(PURCHASE_PAYMENT,SALE_PAYMENT),
			 );
			    $conditions1=array_merge($conditions1,$cond1);
			    $fields=array('PaymentTransaction.id','PaymentTransaction.dr_bank','PaymentTransaction.bank_name','PaymentTransaction.type','PaymentTransaction.created','PaymentTransaction.payment_method');
				$paymentvoucher=$this->PaymentTransaction->find('all', array('conditions'=>$conditions1,'limit'=>PAGINATION_LIMIT_1));
			
			$data='';			
					if(!empty($paymentvoucher)) 
						{
							
						foreach($paymentvoucher as $row)
								{
									$rowdata="";
									$rowdata1="";
									$rowdata2="";
									$rowdata3="";
									$rowdata4="";
									$rowdata5="";
									$rowdata6="";
									$rowdata7="";
									
				  
									if(!empty($row['PaymentTransaction']['created'])){$rowdata =date('d-m-Y' ,strtotime($row['PaymentTransaction']['created']));}
									
									if($row['PaymentTransaction']['type']==PURCHASE_PAYMENT){$rowdata1= "Payment"; }			
									else if($row['PaymentTransaction']['type']==SALE_PAYMENT){$rowdata1= "Sale";}
									
									if($row['PaymentTransaction']['type']==PURCHASE_PAYMENT){$rowdata2 ="Payment";}
									else if($row['PaymentTransaction']['type']==SALE_PAYMENT){$rowdata2 ="Sale";	}
									$pay_meth = array(PAYMENT_TYPE_CASH=>"Cash",PAYMENT_TYPE_CHEQUE=>"Cheque",PAYMENT_TYPE_ONLINE=>"Card/Net Banking",PAYMENT_TYPE_VOUCHER=>"Gift Voucher");
									$rowdata4 =  $pay_meth[$row['PaymentTransaction']['payment_method']];
									if($row['PaymentTransaction']['type']==PURCHASE_PAYMENT){$dr_t_amt= $row['PaymentTransaction']['payment'];}
									if($row['PaymentTransaction']['type']==SALE_PAYMENT){$cr_t_amt= $row['PaymentTransaction']['payment'];}
													
									$data.='<tr>
										<td class="closing_bg">'.$rowdata.'</td>									
										<td class="closing_bg">'.$rowdata1.'</td>
										<td class="closing_bg">'.$rowdata2.'</td>
										<td class="closing_bg">'.$rowdata3.'</td>
										<td class="closing_bg">'.$rowdata4.'</td>
										<td class="closing_bg">'.$rowdata5.'</td>
										<td class="closing_bg">'.$row['PaymentTransaction']['id'].'</td>
										<td class="closing_bg debitAmt">'.$dr_t_amt.'</td>
										<td class="closing_bg creditAmt">'.$cr_t_amt.'</td>
											
									</tr>';
									
								$lastpaymentBAnkID=$row['PaymentTransaction']['id'];
							}
					
						}
					}
		
				}				
			}
				if(!empty($data))
				{
				echo json_encode(array('status'=>'1000','tablegg'=>$data,'lastrowID'=>$lastrowID,'lastVoucherId'=>$lastVoucherId,'last_purchase_id'=>$last_purchse_id,'last_payment_id'=>$last_payment_id,'lastDisVoucherId'=>$lastDisVoucherId,'lasPurchaseRetID'=>$lasPurchaseRetID,'lastSaleID'=>$lastSaleID,'salePayLastId'=>$salePayLastId,'creditSaleReturnId'=>$creditSaleReturnId,'lastBankVoucherId'=>$lastBankVoucherId,'lastpaymentBAnkID'=>$lastpaymentBAnkID));	
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
	reset ledger search
	30.01.17
	*/
	public function resetLedgerSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();	
		
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('LedgerSummarySearch');
			$this->redirect($this->referer()); 
				
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect( array('controller'=>'boffices','action'=>'profitLoss','office'=>true));
			$this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	06.11.17
	Get Customer For Dropdown
	*/
	public function getCustomerListDrop()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$name=$this->request->data['name'];
				$conditions=array('Ledger.is_deleted'=>BOOL_FALSE,'Ledger.is_active'=>BOOL_TRUE,'Ledger.name LIKE'=>'%'.$name.'%','Ledger.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),'Ledger.group_id'=>GROUP_SUNDRY_DEBTOR_ID);
				$meberData=$this->Ledger->find('all',array('conditions'=>$conditions,'fields'=>array('Ledger.name','Ledger.id'),'contain'=>array('PartyDetail'=>array('address'))));
				
				if(!empty($meberData))
				{
					$options='';
					foreach($meberData as $row)
					{
						$options.='<tr onClick="onselectMember('.$row['Ledger']['id'].')" m-id="'.$row['Ledger']['id'].'"><td><input type="text" class="hidden_input_row1">'.$row['Ledger']['name'].'</td><td>'.$row['PartyDetail']['address'].'</td></tr>';	
					}
					echo json_encode(array('status'=>'1000','options'=>$options));
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
	/**
	@Created By : Amit Sahu
	@Created On : 26.12.17
	@ Auto completet item for credit note
	**/
	public function autoCompleteILimitedtemsCrNote() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');		
		$this->loadModel('SalesDetail');		
			
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
			$no=trim($this->request->data['no']);
			$cust_id=$this->request->data['cust_id'];
			$query=trim($this->request->data['value']);
			$qrArr=explode(',',$query);
			$query=$qrArr[0];
			$pid="";
			if(count($qrArr)==2)
			{
			$pid=$qrArr[1];
			}
			
			$no=trim($this->request->data['no']);
			
			if(isset($query) and !empty($query))
			{
				$or_cond = array();
				
				if(!empty($pid))
				{
					
					$or_cond['AND']['Item.price'] = $query;
				}else{
				$or_cond['OR']['Item.name LIKE'] ='%'.$query.'%';
				$or_cond['OR']['Item.price LIKE'] = $query.'%';
				$or_cond['OR']['Item.code LIKE'] = $query.'%';
				}
				$beforeSixMonth=date('Y-m-d', strtotime('-6 months')) ;
				$items=$this->SalesDetail->find('all',array(
					"fields"=>array("Item.id","Item.code","Item.name","Item.price","Item.min_stock_limit","Item.hsn"),
					'conditions'=>array(
					"OR"=>$or_cond,
					'SalesDetail.id !='=>BOOL_FALSE,
					'Sale.customer_id'=>$cust_id,
					'SalesDetail.is_deleted'=>BOOL_FALSE,
					'SalesDetail.is_active'=>BOOL_TRUE,
					'Sale.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),					
					'Sale.sales_date >' =>$beforeSixMonth,					
					),
					
					"recursive"=>2,
					'limit'=>100,
					'group'=>array('Item.name'),
					'order'=>array('Item.name'=>'ASC')
					));									
				
				$table="";
			
				if(!empty($items))
				{
					foreach($items as $row)
					{
						$id="'".$row['Item']['id']."'";
					
						$table.='<tr onClick="onselectItem('.$id.','.$no.')" id-no="'.$row['Item']['id'].','.$no.'"><td><input type="text" class="hidden_input_row">'.$row['Item']['hsn'].'</td><td>'.$row['Item']['name'].'</td><td>'.$row['Item']['price'].'</td></tr>';
						//$lastId=$row['Item']['id'];
					}
				
					echo json_encode(array('table'=>$table,'status'=>1000));
				}
				else
				{
					echo json_encode(array('suggestions'=>array(array('data'=>'','value'=>''))));
				}
			}
			
		}	
	}
	/*
	Amit Sahu
	26.12.17
	Credit Note item onselect
	*/
	public function creditNoteItemOnSelect() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';

		$this->loadModel('SalesDetail');		
		$this->loadModel('Item');		
			
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
		
			
				$item_id=$this->request->data['id'];
				$cust_id=$this->request->data['cust_id'];
				$itemData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$item_id),'fields'=>array('Item.name','Item.unit','Item.alt_unit','Unit.code','AltUnit.code'),'recursive'=>2));
				
				$unit_opt="";
				if(!empty($itemData)){
					// Get Unit for dropdown
					
							$main_unit=$itemData['Unit']['code'];	
							$unit_opt.='<option value="'.$itemData['Item']['unit'].'">'.$itemData['Unit']['code'].'</option>';
							if(!empty($itemData['Item']['alt_unit']))
							{
							$unit_opt.='<option value="'.$itemData['Item']['alt_unit'].'">'.$itemData['AltUnit']['code'].'</option>';
							}
				}	
							
				$item_name=$itemData['Item']['name'];
				$beforeSixMonth=date('Y-m-d', strtotime('-6 months')) ;
				$conditions=array('SalesDetail.item_id'=>$item_id,'SalesDetail.is_deleted'=>BOOL_FALSE,'SalesDetail.is_active'=>BOOL_TRUE,'Sale.sales_date >' =>$beforeSixMonth,'Sale.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),'Sale.customer_id'=>$cust_id);
				$fields=array('SalesDetail.sales_id','Sale.invoice_no');
				$salesIds=$this->SalesDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>array('SalesDetail.sales_id'),'order'=>array('SalesDetail.sales_id'=>'DESC')));
				if(!empty($salesIds))
				{
					$options="";
					foreach($salesIds as $row)
					{
						$options.='<option value="'.$row['SalesDetail']['sales_id'].'">'.$row['Sale']['invoice_no'].'</option>';
					}
					
				
				$fields=array('SalesDetail.sales_id','Sale.id','SalesDetail.quantity','SalesDetail.unit','SalesDetail.hsn','SalesDetail.gst_slab','SalesDetail.sp','SalesDetail.gst_slab','SalesDetail.total_amount','Item.price','SalesDetail.cgst_per','SalesDetail.sgst_per','SalesDetail.igst_per','SalesDetail.cgst_amt','SalesDetail.sgst_amt','SalesDetail.igst_amt');
				$sales_data=$this->SalesDetail->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('SalesDetail.sales_id'=>'DESC')));
				
					echo json_encode(array('status'=>1000,'options'=>$options,'item_id'=>$item_id,'item_name'=>$item_name,'sales_data'=>$sales_data,'unit_opt'=>$unit_opt));
				}
				else
				{
					echo json_encode(array('data'=>'','options'=>'','item_id'=>$item_id,'item_name'=>$item_name,'unit_opt'=>$unit_opt));
				}
		}	
			
		
	}
	/*
	Amit Sahu
	26.12.17
	GEt Sale details by sales id or item id
	*/
	public function getItemSaleDetailsBYItemAndSaleId() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';

		$this->loadModel('SalesDetail');		
	
			
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
		
			
				$item_id=$this->request->data['id'];
		
				$sale_id=$this->request->data['sale_id'];
		
			
				$conditions=array('SalesDetail.item_id'=>$item_id,'SalesDetail.is_deleted'=>BOOL_FALSE,'SalesDetail.is_active'=>BOOL_TRUE,'Sale.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),'SalesDetail.sales_id'=>$sale_id);
				$fields=array('SalesDetail.sales_id','Sale.id','SalesDetail.quantity','SalesDetail.unit','SalesDetail.hsn','SalesDetail.gst_slab','SalesDetail.sp','SalesDetail.gst_slab','SalesDetail.total_amount','Item.price','SalesDetail.cgst_per','SalesDetail.sgst_per','SalesDetail.igst_per','SalesDetail.cgst_amt','SalesDetail.sgst_amt','SalesDetail.igst_amt');
				$sales_data=$this->SalesDetail->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('SalesDetail.sales_id'=>'DESC')));
				//print_r($sales_data);
				if(!empty($sales_data))
				{					
				
				
					echo json_encode(array('status'=>1000,'sales_data'=>$sales_data));
				}
				else
				{
					echo json_encode(array('data'=>''));
				}
		}	
			
		
	}
		/**
	@Created By : Amit Sahu
	@Created On : 26.12.17
	@ Auto completet item for Debit note
	**/
	public function autoCompleteILimitedtemsDrNote() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel('Item');		
		$this->loadModel('PurchaseDetail');		
			
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
			$no=trim($this->request->data['no']);
			$cust_id=$this->request->data['cust_id'];
			$query=trim($this->request->data['value']);
			$qrArr=explode(',',$query);
			$query=$qrArr[0];
			$pid="";
			if(count($qrArr)==2)
			{
			$pid=$qrArr[1];
			}
			
			$no=trim($this->request->data['no']);
			
			if(isset($query) and !empty($query))
			{
				$or_cond = array();
				
				if(!empty($pid))
				{
					
					$or_cond['AND']['Item.price'] = $query;
				}else{
				$or_cond['OR']['Item.name LIKE'] ='%'.$query.'%';
				$or_cond['OR']['Item.price LIKE'] = $query.'%';
				$or_cond['OR']['Item.code LIKE'] = $query.'%';
				}
				$beforeSixMonth=date('Y-m-d', strtotime('-6 months')) ;
				$items=$this->PurchaseDetail->find('all',array(
					"fields"=>array("Item.id","Item.code","Item.name","Item.price","Item.min_stock_limit","Item.hsn"),
					'conditions'=>array(
					"OR"=>$or_cond,
					'PurchaseDetail.id !='=>BOOL_FALSE,
					'Purchase.distributor_id'=>$cust_id,
					'PurchaseDetail.is_deleted'=>BOOL_FALSE,
					'PurchaseDetail.is_active'=>BOOL_TRUE,
					'Purchase.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),					
					'Purchase.bill_date >' =>$beforeSixMonth,					
					),
					
					"recursive"=>2,
					'limit'=>100,
					'group'=>array('Item.name'),
					'order'=>array('Item.name'=>'ASC')
					));									
				
				$table="";
				
				if(!empty($items))
				{
					foreach($items as $row)
					{
						$id="'".$row['Item']['id']."'";
					
						$table.='<tr onClick="onselectItem('.$id.','.$no.')" id-no="'.$row['Item']['id'].','.$no.'"><td><input type="text" class="hidden_input_row">'.$row['Item']['hsn'].'</td><td>'.$row['Item']['name'].'</td><td>'.$row['Item']['price'].'</td></tr>';
						//$lastId=$row['Item']['id'];
					}
				
					echo json_encode(array('table'=>$table,'status'=>1000));
				}
				else
				{
					echo json_encode(array('suggestions'=>array(array('data'=>'','value'=>''))));
				}
			}
			
		}	
	}
	
	/*
	Amit Sahu
	26.12.17
	Credit Note item onselect
	*/
	public function debitNoteItemOnSelect() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';

		$this->loadModel('PurchaseDetail');		
		$this->loadModel('Item');		
		$this->loadModel('Stock');		
			
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
		
			
				$item_id=$this->request->data['id'];
				$cust_id=$this->request->data['cust_id'];
				$itemData=$this->Item->find('first',array('conditions'=>array('Item.id'=>$item_id),'fields'=>array('Item.name','Item.unit','Item.alt_unit'),'contain'=>array('Unit'=>array('code','id'),'AltUnit'=>array('code','id')),'recursive'=>2));
				
				$unit_opt="";
				if(!empty($itemData)){
					
					// Get Unit for dropdown
					
							$main_unit=$itemData['Unit']['code'];	
							$unit_opt.='<option value="'.$itemData['Item']['unit'].'">'.$itemData['Unit']['code'].'</option>';
							if(!empty($itemData['Item']['alt_unit']))
							{
							$unit_opt.='<option value="'.$itemData['Item']['alt_unit'].'">'.$itemData['AltUnit']['code'].'</option>';
							}
				}	
							
				$item_name=$itemData['Item']['name'];
				$beforeSixMonth=date('Y-m-d', strtotime('-6 months')) ;
				$conditions=array('PurchaseDetail.item_id'=>$item_id,'PurchaseDetail.is_deleted'=>BOOL_FALSE,'PurchaseDetail.is_active'=>BOOL_TRUE,'Purchase.bill_date >' =>$beforeSixMonth,'Purchase.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),'Purchase.distributor_id'=>$cust_id);
				$fields=array('PurchaseDetail.purchase_id','Purchase.bill_no');
				$purchasesIds=$this->PurchaseDetail->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>array('PurchaseDetail.purchase_id'),'order'=>array('PurchaseDetail.purchase_id'=>'DESC')));
				if(!empty($purchasesIds))
				{
					$options="";
					foreach($purchasesIds as $row)
					{
						$options.='<option value="'.$row['PurchaseDetail']['purchase_id'].'">'.$row['Purchase']['bill_no'].'</option>';
					}
					
				
				$fields=array('PurchaseDetail.purchase_id','Purchase.id','PurchaseDetail.quantity','PurchaseDetail.unit','PurchaseDetail.hsn','PurchaseDetail.gst_slab','PurchaseDetail.pp','PurchaseDetail.gst_slab','PurchaseDetail.total_amount','Item.price','PurchaseDetail.cgst_per','PurchaseDetail.sgst_per','PurchaseDetail.igst_per','PurchaseDetail.cgst_amt','PurchaseDetail.sgst_amt','PurchaseDetail.igst_amt');
				$sales_data=$this->PurchaseDetail->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('PurchaseDetail.purchase_id'=>'DESC')));
				
				$stockData=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$item_id,'Item.user_profile_id'=> $this->Session->read('Auth.User.user_profile_id'))));	
					if(!empty($stockData))
					{
						$current_stock=$stockData['Stock']['quantity'];
					}else{
						$current_stock=0;
					}
					if($sales_data['PurchaseDetail']['quantity']>$current_stock)
					{
						$sales_data['PurchaseDetail']['quantity']=$current_stock;
					}	
				
					echo json_encode(array('status'=>1000,'options'=>$options,'item_id'=>$item_id,'item_name'=>$item_name,'sales_data'=>$sales_data,'unit_opt'=>$unit_opt));
				}
				else
				{
					echo json_encode(array('data'=>'','options'=>'','item_id'=>$item_id,'item_name'=>$item_name,'unit_opt'=>$unit_opt));
				}
		}	
			
		
	}	
	/*
	Amit Sahu
	26.12.17
	GEt Sale details by purchase id or item id
	*/
	public function getItemPurchaseDetailsBYItemAndPurchaseId() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';

		$this->loadModel('PurchaseDetail');		
		$this->loadModel('Stock');		
	
			
		$suggestions=array();
		$arr=array();
        if ($this->request->is('ajax')) 
		{		
		
			
				$item_id=$this->request->data['id'];
		
				$sale_id=$this->request->data['sale_id'];
		
			
				$conditions=array('PurchaseDetail.item_id'=>$item_id,'PurchaseDetail.is_deleted'=>BOOL_FALSE,'PurchaseDetail.is_active'=>BOOL_TRUE,'Purchase.user_profile_id' => $this->Session->read('Auth.User.user_profile_id'),'PurchaseDetail.purchase_id'=>$sale_id);
				$fields=array('PurchaseDetail.purchase_id','Purchase.id','PurchaseDetail.quantity','PurchaseDetail.unit','PurchaseDetail.hsn','PurchaseDetail.gst_slab','PurchaseDetail.pp','PurchaseDetail.gst_slab','PurchaseDetail.total_amount','Item.price','PurchaseDetail.cgst_per','PurchaseDetail.sgst_per','PurchaseDetail.igst_per','PurchaseDetail.cgst_amt','PurchaseDetail.sgst_amt','PurchaseDetail.igst_amt');
				$sales_data=$this->PurchaseDetail->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('PurchaseDetail.purchase_id'=>'DESC')));
				//print_r($sales_data);
				
				if(!empty($sales_data))
				{
					$stockData=$this->Stock->find('first',array('conditions'=>array('Stock.item_id'=>$item_id,'Item.user_profile_id'=> $this->Session->read('Auth.User.user_profile_id'))));	
					if(!empty($stockData))
					{
						$current_stock=$stockData['Stock']['quantity'];
					}else{
						$current_stock=0;
					}
					if($sales_data['PurchaseDetail']['quantity']>$current_stock)
					{
						$sales_data['PurchaseDetail']['quantity']=$current_stock;
					}						
				
					echo json_encode(array('status'=>1000,'sales_data'=>$sales_data));
				}
				else
				{
					echo json_encode(array('data'=>''));
				}
		}	
			
		
	}
	/*
	Amit Sahu
	26.12.17
	get cess amount by item or amount  
	*/
	public function getCessAmount() {
		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';


	
        if ($this->request->is('ajax')) 
		{		
		
			$item_id=$this->request->data['item_id'];
			$amount=$this->request->data['amount'];
			$qty=$this->request->data['qty'];
				
			$cess_amount=$this->Cess->getCessAmount($item_id,$qty,$amount);
					echo json_encode(array('status'=>1000,'cess_amount'=>$cess_amount));
				
		}	
			
		
	}
	
	/*
	Amit 
	30.03.17
	LoadMore Ledger for dropdown
	*/
	public function loadMoreLedgerDropdown()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		
		
		if ($this->request->is('ajax')) 
			{

			$query=trim($this->request->data['value']);
			$no=$this->request->data['no'];
		
				$or_cond = array();
				$and_cond = array();
			if(!empty($this->request->data['get_type']))
			{
					$get_type=$this->request->data['get_type'];	
				if($get_type==1)
				{
					$and_cond['OR']['Ledger.group_id'] =GROUP_BANK_ACCOUNT_ID;
					$and_cond['OR']['Ledger.id'] =CASH_LEDGER;
				}
			}
			if(isset($query) and !empty($query))
			{
			
				
				//$or_cond['OR']['Ledger.name LIKE'] =$query.'%';
				//$or_cond['OR']['Ledger.code LIKE'] = $query.'%';
				
				
				$ledgers=$this->Ledger->find('all',array(
					"fields"=>array("Ledger.id","Ledger.code","Ledger.name"),
					'conditions'=>array(
					//"OR"=>$or_cond,
					"AND"=>$and_cond,
					'Ledger.id !='=>BOOL_FALSE,
					'Ledger.name LIKE'=> $query.'%',
					'Ledger.is_deleted'=>BOOL_FALSE,
					'Ledger.is_active'=>BOOL_TRUE,
					'OR'=>array(
					'Ledger.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
					'Ledger.is_default'=>BOOL_TRUE,
					)
					),
					
					"recursive"=>-1,
					'limit'=>15,
					'order'=>array('Ledger.id'=>'ASC')
					));									
					
			
				$table="";
				if(!empty($ledgers))
				{
					foreach($ledgers as $row)
					{
						$id="'".$row['Ledger']['id']."'";
						
						$table.='<tr onClick="onselectLedger('.$id.','.$no.')" id-no="'.$row['Ledger']['id'].','.$no.'"><td><input type="text" class="hidden_input_row">'.$row['Ledger']['code'].' '.$row['Ledger']['name'].'</td></tr>';
						$lastId=$row['Ledger']['id'];
					}
				
					echo json_encode(array('table'=>$table,'status'=>1000,'lastId'=>$lastId));
				}
				else
				{
					echo json_encode(array('table'=>'','status'=>1001,'lastId'=>''));
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
	Get Ledger Data By id For auto select
	Amit Sahu
	21.07.18
	*/
	
	public function getLedgerDetailByID()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ledger');
		
		
		if ($this->request->is('ajax')) 
			{

				$id=$this->request->data['id'];
				$ledgerData=$this->Ledger->findById($id,array('Ledger.name','Ledger.group_id'));
				$group_id="";
				if(!empty($ledgerData))
				{
					$ledger_name=$ledgerData['Ledger']['name'];
					$group_id=$ledgerData['Ledger']['group_id'];
			
					echo json_encode(array('status'=>1000,'ledger_name'=>$ledger_name,'id'=>$id,'group_id'=>$group_id));
				}
				else
				{
					echo json_encode(array('table'=>'','status'=>1001));
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
	Enque by modal or id
	*/
	public function unique_modalOrId($model=null,$fields=null)
	{		
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
		$this->loadModel($model);
		//echo $model;exit;
        if ($this->request->is('ajax')) 
		{
			$user_profile_id=$this->Session->read('Auth.User.user_profile_id');		
			$count=$this->$model->find('count',array(
			'conditions'=>array(
				$model.'.'.$fields=>$this->request->data[$model][$fields],
				$model.'.user_profile_id'=>$user_profile_id,
				$model.'.is_deleted'=>BOOL_FALSE
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
}