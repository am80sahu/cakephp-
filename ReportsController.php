<?php
App::uses('AppController', 'Controller');
App::uses('AuthComponent', 'Controller/Component');

class ReportsController extends AppController {

    public $components = array('Paginator');

    public function beforeFilter() 
	{
        parent::beforeFilter();
        $authAllowedActions = array('saleBBList');
	 
    }
	
	/*
		amol kathaley - 09-09-2017
		Desc : For those record which has gstn id ..
	*/
	 public function saleBBList($todyreport=NULL) 
	 {
		    $this->loadModel('Sale');
		    $this->loadModel('SalesDetail');		
			
			$cond=array();
			$locname="";
		
			$this->layout = ('shop/inner');
			$locname=$this->Session->read('Auth.User.Location.name');
			//$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
		
			$from_date = "";
			$to_date = "";		
		
		
			
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailSearch');			
				
			}	
			if(isset($this->request->data['SalesDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
					//echo '22';
						$cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	 
		  
		  
			
			$conditions=array(			
			'Sale.customer_gstin !='=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')	
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('SalesDetail.id','SalesDetail.gst_slab', 'sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
			$contain=array(
			'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
			
			);
			
			$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			//echo '<pre>';print_r($salesdetaillist);echo '</pre>';exit;
			/*$log = $this->SalesDetail->getDataSource()->getLog(false, false);
			pr($log);*/
			$this->set(compact('salesdetaillist'));	
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		

		
    }
		
	
	/*
	amol kathaley
	09-09-17
	function for load the next data in todays sale detail
	*/
	public function loadMoreSale()
	{	
		$locname="";
		
		
		
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('SalesDetail');		
		$this->loadModel('Sale');
		
		
		if ($this->request->is('ajax')) 
			{
				
				$cond=array();
				$salesdetaillist=array();
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailSearch');			
				
			}	
			
			if(isset($this->request->data['SalesDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
					//echo '22';
						$cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}


			
		
		
			$conditions = array(
			'Sale.customer_gstin !='=>'',
			'SalesDetail.id >' => $this->request->data['id'],
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')
		     );
			$conditions=array_merge($conditions,$cond);
	
			$conditions=array_merge($conditions,$cond);
			$fields=array('SalesDetail.id','SalesDetail.gst_slab', 'sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
			$contain=array(
			'Sale'=>array('id','sales_date','inclusive','invoice_no','total_amount','State'=>array('name','state_no'))
			);
			
			$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			
			$data="";
		
			
			if(!empty($salesdetaillist))
			{
				
				
				foreach($salesdetaillist as $row)
				{
					
					$saledata="";
					$exe="";
					
					if($row['Sale']['inclusive'] == 0){ $ex='N';}else{ $ex='Y';}
					$loc="";
					if(!empty($row['Sale']['state'])){
						$loc=$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name'];
					}
					$data.='<tr>
							<td >'.$row['Sale']['customer_gstin'].'</td>
							<td>'.$row['Sale']['invoice_no'].'</td>							
							<td >'.$row['Sale']['sales_date'].';</td>
							<td >'.$row['Sale']['total_amount'].'; </td>
							<td >'.$loc.'</td>
							<td >'.$ex.'</td>
							<td >Regular</td>
							<td >--</td>
							<td  class="tamt">'.$row['SalesDetail']['gst_slab'].'</td>
							<td  class="damt">'.$row[0]['taxable'].'</td>
							<td>--</td>
							</tr>';
					$lastrowID=$row['SalesDetail']['id'];
					
					
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
		amol kathaley - 11-09-2017
		Desc : For those record which has NO gstn id ..
	*/
	 public function saleBCLList() 
	 {
		    $this->loadModel('Sale');
		    $this->loadModel('SalesDetail');		
			
			$cond=array();
			$locname="";
		
			$this->layout = ('shop/inner');
			$locname=$this->Session->read('Auth.User.Location.name');
			//$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
		
			$from_date = "";
			$to_date = "";		
		
		
			
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailB2CSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailB2CSearch');			
				
			}	
			if(isset($this->request->data['SalesDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
					//echo '22';
						$cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	 
		  
		  
			
			$conditions=array(			
			'Sale.customer_gstin'=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,
			'Sale.state !='=>$this->Session->read('UserProfile.State.id'),
			'Sale.total_amount >='=>250000,
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')	
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('SalesDetail.id','SalesDetail.gst_slab','sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
			$contain=array(
			'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
			);
			
			$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			//echo '<pre>';print_r($salesdetaillist);echo '</pre>';exit;
			/*$log = $this->SalesDetail->getDataSource()->getLog(false, false);
			pr($log);*/
			$this->set(compact('salesdetaillist'));	
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		

		
    }
		
	
	/*
	amol kathaley
	09-09-17
	function for load the next data in todays sale detail
	*/
	public function loadMoreSaleBCL()
	{	
		$locname="";
		
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('SalesDetail');		
		$this->loadModel('Sale');
		
		
		if ($this->request->is('ajax')) 
			{
				
				$cond=array();
				$salesdetaillist=array();
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailB2CSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailB2CSearch');			
				
			}	
			
			if(isset($this->request->data['SalesDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
					//echo '22';
						$cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	
			$conditions = array(
			'Sale.customer_gstin'=>'',
			'SalesDetail.id >' => $this->request->data['id'],
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,
			'Sale.state !='=>$this->Session->read('UserProfile.State.id'),
			'Sale.total_amount >='=>250000,
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')	
		     );
			$conditions=array_merge($conditions,$cond);
	
			$conditions=array_merge($conditions,$cond);
			$fields=array('SalesDetail.id','SalesDetail.gst_slab', 'sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
			$contain=array(
			'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
			);
			
			$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			
			$data="";
		
			
			if(!empty($salesdetaillist))
			{
				foreach($salesdetaillist as $row)
				{
					
					$saledata="";
					$exe="";
					
					if($row['Sale']['inclusive'] == 0){ $ex='N';}else{ $ex='Y';}
						$loc="";
					if(!empty($row['Sale']['state'])){
						$loc=$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name'];
					}
					$data.='<tr>
							<td>'.$row['SalesDetail']['invoice_no'].'</td>							
							<td >'.$row['Sale']['sales_date'].';</td>
							<td >'.$row['Sale']['total_amount'].'; </td>
							<td >'.$loc.'</td>
							<td  class="tamt">'.$row['SalesDetail']['gst_slab'].'</td>
							<td  class="damt">'.$row[0]['taxable'].'</td>
							<td>--</td>
							<td >--</td>
							</tr>';
					$lastrowID=$row['SalesDetail']['id'];
					
					
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
	Kajal kurrewar
	02.09.17
	Stock Item List
	*/
	
	public function stockItem()
	{
		$cond=array();
		$this->layout = ('shop/inner');
		$this->loadModel('Item');
		$this->loadModel('Stock');
		
		$this->loadModel('PurchaseSale');
		
		$stockitems="";
		
		$from_date='';
		$to_date='';
		
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
				$item=$this->Item->findById($this->request->data['Item']['item_id']);
				if(!empty($item))
				{
					$itemName=$item['Item']['name'];
				}
			}
			
			if(isset($this->request->data['Item']['from_date']) and !empty($this->request->data['Item']['from_date']))				
			{
				
				$from_date=$this->request->data['Item']['from_date'];
			}else{
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
			if(isset($this->request->data['Item']['to_date']) and !empty($this->request->data['Item']['to_date']))				
			{
				$to_date=$this->request->data['Item']['to_date'];
			}else{
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
			//End Search
			$conditions = array(
			'Stock.id !=' => BOOL_FALSE,
			'Stock.is_deleted' => BOOL_FALSE,
			'Stock.is_active' => BOOL_TRUE,
			'Item.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
			'Item.item_type !='=>SERVICES_TYPE
			);
		
		$conditions=array_merge($conditions,$cond);
		
		$contain=array('Item'=>array('name','id','hsn','price','sp','gst_slab','Unit'=>array('code')));
		
		$stockitems = $this->Stock->find('all',array('conditions'=>$conditions,'contain'=>$contain,'limit' =>PAGINATION_LIMIT_1,'recursive'=>2));
		
		
		if(!empty($stockitems))
		{
			$qtyOutArr=array();
			$qtyInArr=array();
			foreach($stockitems as $k=>$row)
			{
				
				//Opening Data
				$report['Opening']['qty']='';
				$conditions=array(
				'DATE(PurchaseSale.created) < '=>$from_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				);
				$fields=array('PurchaseSale.stock');
				
				$psData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('PurchaseSale.id'=>'DESC')));
				
				if(!empty($psData))
				{
					$report['Opening']['qty']=$psData['PurchaseSale']['stock'];				
				}
				//Inwards Data
				$report['Inawards']['qty']='';
				$qtyInArr=array();
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>array(PURCHASE,SALES_RETURN)

				);
				$fields=array('PurchaseSale.qty');
		        $psInData = $this->PurchaseSale->find('all',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
		       
				if(!empty($psInData))
				{
						foreach($psInData as $qtyIn)
						{
							$qtyInArr[]=$qtyIn['PurchaseSale']['qty'];				
						}
				}
				// Delete Purchase
				$psDeleteData=array();
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>array(PURCHSE_DELETE,CREDIT_NOTE_DELETE),
				);
				$fields=array('SUM(PurchaseSale.qty) as deletepoqty');
				$psDeleteData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
		     			   
				
				// End delete Purchase
				$report['Inawards']['qty']=array_sum($qtyInArr)-$psDeleteData[0]['deletepoqty'];
				
				//Outwards Data
				$qtyOutArr=array();
				$report['Outwards']['qty']='';
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>array(SALES,PURCHASE_RETURN),
				);
				
				$fields=array('PurchaseSale.qty');
				$psInData = $this->PurchaseSale->find('all',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
		       
				if(!empty($psInData))
				{
						foreach($psInData as $qtyOut)
						{
							$qtyOutArr[]=$qtyOut['PurchaseSale']['qty'];				
						}
				}
				// Delete Sale
				$saledeleteData=array();
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>array(SALE_DELETE,DEBIT_NOTE_DELETE),
				);
				
				$fields=array('SUM(PurchaseSale.qty) as deleteSale');
				$saledeleteData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
				// End Sale
				$report['Outwards']['qty']=array_sum($qtyOutArr)-$saledeleteData[0]['deleteSale'];
					//Closing Data
				$report['Closing']['qty']='';
				$conditions=array(
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				);
				$fields=array('PurchaseSale.stock');
				
				$clData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('PurchaseSale.id'=>'DESC')));
				//echo'<pre>';print_r($psData);exit;
				
		        
				if(!empty($clData))
				{
					$report['Closing']['qty']=$clData['PurchaseSale']['stock'];				
				}
				$stockitems[$k]=array_merge($stockitems[$k],$report);
			}
		}
		$this->set(compact('stockitems'));
				
    }
	/* 
     kajal kurrewar
	 11-09-2017
	 reset stock item
	*/
	public function resetStockItemSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('shop/inner');					
		
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
		kajal kurrewar
		11-09-17
		Load Stock Data on scroll
	*/
	public function loadMoreshopStockList()
	{	
	    
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Item');
		$this->loadModel('Stock');
		$this->loadModel('PurchaseSale');
		
		if ($this->request->is('ajax')) 
		{
		$cond=array();
		$stockitems="";
		$from_date='';
		$to_date='';
		
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
				$item=$this->Item->findById($this->request->data['Item']['item_id']);
				if(!empty($item))
				{
					$itemName=$item['Item']['name'];
				}
			}
			
			if(isset($this->request->data['Item']['from_date']) and !empty($this->request->data['Item']['from_date']))				
			{
				$from_date=$this->request->data['Item']['from_date'];
			}else{
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
			if(isset($this->request->data['Item']['to_date']) and !empty($this->request->data['Item']['to_date']))				
			{
				$to_date=$this->request->data['Item']['to_date'];
			}else{
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
		
			//End Search
			$conditions = array(
			'Stock.id !=' => BOOL_FALSE,
			'Stock.id >' => $this->request->data['id'],
			'Stock.is_deleted' => BOOL_FALSE,
			'Stock.is_active' => BOOL_TRUE,
			'Item.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
			'Item.item_type !='=>SERVICES_TYPE
			);
		
		$conditions=array_merge($conditions,$cond);
		$contain=array('Item'=>array('name','id','hsn','price','sp','gst_slab','Unit'=>array('code')));
		$stockitems = $this->Stock->find('all',array('conditions'=>$conditions,'contain'=>$contain,'limit' =>PAGINATION_LIMIT_1,'recursive'=>2));
		
		
		if(!empty($stockitems))
		{
			$qtyOutArr=array();
			$qtyInArr=array();
			foreach($stockitems as $k=>$row)
			{
				//Opening Data
				$report['Opening']['qty']='';
				$conditions=array(
				'DATE(PurchaseSale.created) < '=>$from_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				);
				$fields=array('PurchaseSale.stock');
				
				$psData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('PurchaseSale.id'=>'DESC')));
				
		        $this->set(compact('psData'));
				if(!empty($psData))
				{
					$report['Opening']['qty']=$psData['PurchaseSale']['stock'];				
				}
				//Inwards Data
				$report['Inawards']['qty']='';
				$qtyInArr=array();
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>array(PURCHASE,SALES_RETURN),

				);
				$fields=array('PurchaseSale.qty');
		        $psInData = $this->PurchaseSale->find('all',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
		       
				if(!empty($psInData))
				{
						foreach($psInData as $qtyIn)
						{
							$qtyInArr[]=$qtyIn['PurchaseSale']['qty'];				
						}
				}
				// Delete Purchase
				$psDeleteData=array();
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>array(PURCHSE_DELETE,CREDIT_NOTE_DELETE),
				);
				$fields=array('SUM(PurchaseSale.qty) as deletepoqty');
				$psDeleteData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
		     			   
				
				// End delete Purchase
				$report['Inawards']['qty']=array_sum($qtyInArr)-$psDeleteData[0]['deletepoqty'];
				
				//Outwards Data
				$report['Outwards']['qty']='';
				$qtyOutArr=array();
			
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>SALES,
				);
				$fields=array('PurchaseSale.qty');
				$psInData = $this->PurchaseSale->find('all',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
		       
				if(!empty($psInData))
				{
						foreach($psInData as $qtyOut)
						{
							$qtyOutArr[]=$qtyOut['PurchaseSale']['qty'];				
						}
				}
				//$report['Outwards']['qty']=array_sum($qtyOutArr);
				// Delete Sale
				$saledeleteData=array();
				$conditions=array(
				'DATE(PurchaseSale.created) >='=>$from_date,
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				'PurchaseSale.type'=>SALE_DELETE,
				);
				
				$fields=array('SUM(PurchaseSale.qty) as deleteSale');
				$saledeleteData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,$order=array('PurchaseSale.id'=>'DESC')));
				// End Sale
				$report['Outwards']['qty']=array_sum($qtyOutArr)-$saledeleteData[0]['deleteSale'];
				//Closing Data
				$report['Closing']['qty']='';
				$conditions=array(
				'DATE(PurchaseSale.created) <='=>$to_date,
				'PurchaseSale.item_id'=>$row['Stock']['item_id'],
				);
				$fields=array('PurchaseSale.stock');
				
				$clData = $this->PurchaseSale->find('first',array('conditions'=>$conditions,'fields'=>$fields,'order'=>array('PurchaseSale.id'=>'DESC')));
				//echo'<pre>';print_r($psData);exit;
				
		        
				if(!empty($clData))
				{
					$report['Closing']['qty']=$clData['PurchaseSale']['stock'];				
				}
				$stockitems[$k]=array_merge($stockitems[$k],$report);
			}
		}
				
		
			
		
			$data="";
			if(!empty($stockitems))
			{
				
				foreach($stockitems as $row)
				{
					$rowdata="";
				$rowdata1="";
				$rowdata2="";
				$rowdata3="";
				 if($row['Opening']['qty']!=0) { $rowdata = $row['Opening']['qty'].' '.$row['Item']['Unit']['code']; }
				 if($row['Inawards']['qty']!=0) { $rowdata1= $row['Inawards']['qty'].' '.$row['Item']['Unit']['code']; }
				 if($row['Outwards']['qty']!=0) { $rowdata2= $row['Outwards']['qty'].' '.$row['Item']['Unit']['code']; }
				 if($row['Closing']['qty']!=0) { $rowdata3= $row['Closing']['qty'].' '.$row['Item']['Unit']['code']; }
				 
				 
				 
					$data.='<tr>
					        <td>'.$row['Item']['hsn'].'</td>
							<td>'.$row['Item']['name'].'</td>
							<td>'.$row['Item']['price'].'</td>
							<td>'.$row['Item']['sp'].'</td>
							<td>'.$row['Item']['gst_slab'].'</td>
							<td class="OpITem">'.$rowdata.'</td>
							<td class="InItem">'.$rowdata1.'</td>
							<td class="OuItem">'.$rowdata2.'</td>
							<td class="ClItem">'.$rowdata3.'</td>
                            </tr>';
					$lastrowID=$row['Stock']['id'];	
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
	
	/*Amit sahu
	  12-09-2017
	  Reset B2B reposrt rearch
	  */
	public function resetB2BReportSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SalesDetailSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*Amit sahu
	  12-09-2017
	  Reset B2C reposrt rearch
	  */
	public function resetSalesDetailB2CSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SalesDetailB2CSearch');
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
		For hsn summary
		14-09-2017
		
	*/
	 public function hsnSummary() 
	{
		    $this->loadModel('Sale');
		    $this->loadModel('SalesDetail');		
		    $this->loadModel('Unit');		
		    $this->loadModel('Item');		
			
			$cond=array();
			$this->layout = ('shop/inner');
			$from_date = "";
			$to_date = "";		
		
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailhsnSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailhsnSearch');			
				
			}	
			if(isset($this->request->data['SalesDetail']))				
				{					
				    if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$fdate='';
							if(!empty($this->request->data['SalesDetail']['from_date']))
							{
								$fdate=date('Y-m-d',strtotime($this->request->data['SalesDetail']['from_date']));
							}
							$cond['Sale.sales_date >=']=$fdate;
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							
							if(!empty($this->request->data['SalesDetail']['to_date']))
							{
								$tdate=date('Y-m-d',strtotime($this->request->data['SalesDetail']['to_date']));
								$cond['Sale.sales_date <=']=$tdate;
							}
						}				
				}
				else{
					if(!isset($cond)){
					     $cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	 
		  
		  
			
			$conditions=array(			
			'Sale.id !='=>BOOL_FALSE,
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE	,
            'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')	
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('DISTINCT  SalesDetail.item_id','SalesDetail.id','SUM(`SalesDetail`.`quantity`) as `qty`','SUM(`SalesDetail`.`total_amount`) as `taxableamt`','SUM(CASE WHEN Sale.gst_type = '.IGST.' THEN SalesDetail.gst_amt ELSE 0 END) AS igst_amt','SUM(CASE WHEN Sale.gst_type = '.CGST_SGST.' THEN SalesDetail.gst_amt ELSE 0 END) AS csgst_amt');
			$group='SalesDetail.item_id';
			$contain=array(
			'Sale'=>array('gst_type','total_amount'),'Item'=>array('name','hsn','Unit'=>array('code','name')),
			
			);
			$salesdetail=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'conditions'=>$conditions,'group'=>$group,'fields'=>$fields,'contain'=>$contain,'recursive' => 2,'contain'=>$contain));
			//echo '<pre>';print_r($salesdetail);echo '</pre>';exit;
			/*echo "<pre>";
			print_r($salesdetail);
			exit;*/
			$this->set(compact('salesdetail'));	
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		
    }
	/*
	Reset Hsn summary search	
	Amit Sahu
	22.09.17
	*/
	public function resethsnsummarySearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SalesDetailhsnSearch');
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
	14-09-2017
	function for load the next data in hsn summary
	*/
/*	public function loadMoreHsnSummary()
	{	
		$locname="";
		
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('SalesDetail');		
		$this->loadModel('Sale');
		$this->loadModel('Item');
		$this->loadModel('Unit');
		
		
		if ($this->request->is('ajax')) 
			{
				
				$cond=array();
				$salesdetail=array();
			
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailhsnSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailhsnSearch');			
				
			}	
			if(isset($this->request->data['SalesDetail']))				
				{					
				    if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}
				else{
					if(!isset($cond)){
					     $cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	        $conditions=array(			
			'Sale.id !='=>BOOL_FALSE,
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE	,
            'SalesDetail.is_active'=>BOOL_TRUE,
            'SalesDetail.id >'=>$this->request->data['id'],
			'SalesDetail.is_deleted'=>BOOL_FALSE,			
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('DISTINCT  SalesDetail.item_id','SalesDetail.id','SUM(`SalesDetail`.`quantity`) as `qty`','SUM(`SalesDetail`.`total_amount`) as `taxableamt`','SUM(`SalesDetail`.`gst_amt`) as gstamt');
			$group='SalesDetail.item_id';
			$contain=array(
			'Sale'=>array('gst_type','total_amount'),'Item'=>array('name','hsn','Unit'=>array('code','name')),
			
			);
			$salesdetail=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'conditions'=>$conditions,'group'=>$group,'fields'=>$fields,'contain'=>$contain,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			
			$data="";
		
			
			if(!empty($salesdetail))
			{
				foreach($salesdetail as $row)
				{
					$unit="";
					$Idata="";
					$cSdata="";
					if(!empty($row['Item']['unit'])){
						$unit=$row['Item']['Unit']['code'].'-'.$row['Item']['Unit']['name'];
					}
					if($row['Sale']['gst_type']==BOOL_FALSE){$cSdata= $row[0]['gstamt']; }
					 if($row['Sale']['gst_type']==BOOL_TRUE)
					 { 
				     $Idata=$row[0]['gstamt'];
					 }
					 
					$data.='<tr>
							<td>'.$row['Item']['hsn'].'</td>							
							<td >'.$row['Item']['name'].'</td>
							<td >'.$unit.'</td>
							<td >'.$row[0]['qty'].'</td>
							<td  class="tamt">'.$row['Sale']['total_amount'].'</td>
							<td  class="damt">'.$row[0]['taxableamt'].'</td>
							<td >'.$Idata.'</td>
							<td >'.$cSdata.'</td>
							<td  >'.$cSdata.'</td>
							<td>--</td>
							
							</tr>';
					$lastrowID=$row['SalesDetail']['id'];
					
					
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
	
	*/
	
	
	
	/*
		Neha Umredkar - 14-09-2017
		Desc : For those record which has NO gstn id and amt less tan 25,0000
	*/
	 public function saleBCSList() 
	 {
		    $this->loadModel('Sale');
		    $this->loadModel('SalesDetail');		
			
			$cond=array();
			$locname="";
		
			$this->layout = ('shop/inner');
			$locname=$this->Session->read('Auth.User.Location.name');
			//$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
		
			$from_date = "";
			$to_date = "";		
		
		
			
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailB2CSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailB2CSearch');			
				
			}	
			if(isset($this->request->data['SalesDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
					//echo '22';
						$cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	 
		  
		  
			
			$conditions=array(			
			'Sale.customer_gstin'=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
			'OR'=>array(array(                   
                   'Sale.state !='=>$this->Session->read('UserProfile.State.id'),
                    'Sale.total_amount <'=>250000,	
					),
                array(
                    'Sale.state '=>$this->Session->read('UserProfile.State.id'),	
                    ))			
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('SalesDetail.id','SalesDetail.gst_slab','sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
			$contain=array(
			'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
			);
			
			$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
		//	echo '<pre>';print_r($salesdetaillist);echo '</pre>';exit;
			/*$log = $this->SalesDetail->getDataSource()->getLog(false, false);
			pr($log);*/
			$this->set(compact('salesdetaillist'));	
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		

		
    }
	
	
	
	
	/*
	Neha Umredkar
	14-09-17
	function for load the next data in todays sale BCS
	*/
	public function loadMoreSaleBCS()
	{	
		$locname="";
		
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('SalesDetail');		
		$this->loadModel('Sale');
		
		
		if ($this->request->is('ajax')) 
			{
				
				$cond=array();
				$salesdetaillist=array();
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailB2CSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailB2CSearch');			
				
			}	
			
			if(isset($this->request->data['SalesDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
					//echo '22';
						$cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	
			
			 $conditions = array(
			'Sale.customer_gstin'=>'',
			'SalesDetail.id >' => $this->request->data['id'],
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),
			'OR'=>array(array(                   
                   'Sale.state !='=>$this->Session->read('UserProfile.State.id'),
                    'Sale.total_amount <'=>250000,	
					),
                array(
                    'Sale.state '=>$this->Session->read('UserProfile.State.id'),	
                    ))			
			);
			$conditions=array_merge($conditions,$cond);
	
			$conditions=array_merge($conditions,$cond);
			$fields=array('SalesDetail.id','SalesDetail.gst_slab', 'sum(SalesDetail.total_amount) as taxable','SalesDetail.sales_id','SalesDetail.total_amount','Sale.customer_gstin');
		
			$contain=array(
			'Sale'=>array('id','sales_date','invoice_no','inclusive','total_amount','State'=>array('name','state_no'))
			);
			
			$salesdetaillist=$this->SalesDetail->find('all',array('order'=>array('SalesDetail.id asc'),'group'=>array('SalesDetail.gst_slab','SalesDetail.sales_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			
			$data="";
		
			
			if(!empty($salesdetaillist))
			{
				foreach($salesdetaillist as $row)
				{
					
					$saledata="";
					$exe="";
					
					if($row['Sale']['inclusive'] == 0){ $ex='N';}else{ $ex='Y';}
						$loc="";
					if(!empty($row['Sale']['state'])){
						$loc=$row['Sale']['State']['state_no'].'-'.$row['Sale']['State']['name'];
					}
					$data.='<tr>
							<td>'.$row['Sale']['invoice_no'].'</td>							
							<td >'.$row['Sale']['sales_date'].';</td>
							<td >'.$row['Sale']['total_amount'].'; </td>
							<td >'.$loc.'</td>
							<td  class="tamt">'.$row['SalesDetail']['gst_slab'].'</td>
							<td  class="damt">'.$row[0]['taxable'].'</td>
							<td>--</td>
							<td >--</td>
							</tr>';
					$lastrowID=$row['SalesDetail']['id'];
					
					
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
	Amit Sahu
	Nill rated Exampted
	18.09.17
	*/
	public function nillratedReport() 
	 {
		    $this->loadModel('Sale');
		    $this->loadModel('SalesDetail');		
			
			$cond=array();
			$locname="";
		
			$this->layout = ('shop/inner');
			$from_date = "";
			$to_date = "";		
		
		
			
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailB2CSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailB2CSearch');			
				
			}	
			if(isset($this->request->data['SalesDetail']))				
				{					
					if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
						{
							$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
						}
					if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
						{
							$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
						$cond['Sale.sales_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	 
		  
		  
			// Inter Register Value
			$conditions=array(			
			'Sale.customer_gstin !='=>'',
			'SalesDetail.is_active'=>BOOL_TRUE,
			'SalesDetail.is_deleted'=>BOOL_FALSE,	
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.is_active'=>BOOL_TRUE,	
			'Item.gst_slab'=>BOOL_FALSE,
			'Item.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id'),			
			'Sale.state !='=>$this->Session->read('UserProfile.UserProfile.state'),	
			);	
			
			$conditions=array_merge($conditions,$cond);
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
			);	
			
			$conditions=array_merge($conditions,$cond);
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
			);	
			
			$conditions=array_merge($conditions,$cond);
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
			);	
			
			$conditions=array_merge($conditions,$cond);
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
			
			
			$this->set(compact('nillrated'));	
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		

		
    }
	/*
	Amit Sahu
	CDNR
	18.09.17
	*/
	public function cdnrReport() 
	 {
		    
			$this->layout = ('shop/inner');
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
    }
	/*
	Amit Sahu
	CDNUR
	18.09.17
	*/
	public function cdnurReport() 
	 {
		    
			$this->layout = ('shop/inner');
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
    }
	/*
	Amit Sahu
	EXPORt
	18.09.17
	*/
	public function exportReport() 
	 {
		    
			$this->layout = ('shop/inner');
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
    }
	/*
	Amit Sahu
	Advance Received
	18.09.17
	*/
	public function advanceReceivedReport() 
	 {
		    
			$this->layout = ('shop/inner');
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
    }

	/*
	Amit Sahu
	Advance Adjusted
	18.09.17
	*/
	public function advanceAdjustedReport() 
	 {
		    
			$this->layout = ('shop/inner');
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
    }
	/*
	Amit Sahu
	documentIssueedReport
	18.09.17
	*/
	public function documentIssueedReport() 
	 {
		    
			$this->layout = ('shop/inner');
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
    }
	/*
	
	Sale Report
	Amit Sahu
	09.10.17
	*/	
	 public function saleReport($todyreport=NULL) 
	 {
		 $cond=array();
		 $this->layout = ('shop/inner');		
		
		$this->loadModel('SalesDetail');		
		$this->loadModel('Sale');
		$from_date = "";
		$to_date = "";
		
			if($todyreport=='todyreport')
			{
			$this->Session->delete('SalesDetailSearch');
			
			$cond['Sale.sales_date']= date('Y-m-d');
			$from_date =date('Y-m-d');
			$to_date =date('Y-m-d') ;
			
			}
			
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailSearch');			
				
			}
			
			if(isset($this->request->data['SalesDetail']))				
		{	
			//print_r($this->request->data['SalesDetail']);exit;
			if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
				{
					$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
					$from_date=$this->request->data['SalesDetail']['from_date'];
				}
				else
				{
					$cond['Sale.sales_date']= date('Y-m-d');
					$from_date = date('Y-m-d');
				}
			if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
				{
					$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
					$to_date=$this->request->data['SalesDetail']['to_date'];
					
				}
			else
				{
					$cond['Sale.sales_date']= date('Y-m-d');
					$to_date = date('Y-m-d');
				}
				
			
	  }
	  else
		{
		  
		  $cond['Sale.sales_date']= date('Y-m-d');
		  $from_date = date('Y-m-d');
		  $to_date = date('Y-m-d');
		}
			
			$conditions=array(
			'Sale.is_active'=>BOOL_TRUE,
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')
			);
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('Sale.id','Sale.invoice_no','Sale.state','Sale.customer_gstin','Sale.sales_date','Sale.total_amount','Sale.total_payment','Sale.total_balance','Sale.discount_amount','Sale.	gst_amt','Sale.gst_type');
			$contain=array(
			'Ledger'=>array('name'),
			'State'=>array('name')		
			);
			
			$salesdetaillist=$this->Sale->find('all',array('conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));			
			$this->set(compact('salesdetaillist'));			
		
		
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		
		
		$search='<tr class="border_none"><th colspan="10" class="text-center border_none">'.$date.'</th></tr>';
		$this->set(compact('search'));
		
    }
		
	  /*
	ResetTodaysSaledetailList
	Neha Bastawale
	22.04.17
	*/	
	
	public function resetsaleReportSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('SalesDetailSearch');
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
	18-05-17
	function for load the next data in todays sale detail
	*/
	public function loadMoreSaleReport()
	{	

		 $cond=array();
		$this->autoRender = FALSE;
		$this->layout = 'ajax';	
		
		$this->loadModel('SalesDetail');		
		$this->loadModel('Sale');
		$from_date = "";
		$to_date = "";
		
	
		
		if ($this->request->is('ajax')) 
			{
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('SalesDetailSearch',$this->request->data['SalesDetail']);			
				
			}
			else
			{
				$this->request->data['SalesDetail']=$this->Session->read('SalesDetailSearch');			
				
			}
			
			if(isset($this->request->data['SalesDetail']))				
		{	
			//print_r($this->request->data['SalesDetail']);exit;
			if(isset($this->request->data['SalesDetail']['from_date']) and !empty($this->request->data['SalesDetail']['from_date']))				
				{
					$cond['Sale.sales_date >=']=$this->request->data['SalesDetail']['from_date'];
					$from_date=$this->request->data['SalesDetail']['from_date'];
				}
				else
				{
					$cond['Sale.sales_date']= date('Y-m-d');
					$from_date = date('Y-m-d');
				}
			if(isset($this->request->data['SalesDetail']['to_date']) and !empty($this->request->data['SalesDetail']['to_date']))				
				{
					$cond['Sale.sales_date <=']=$this->request->data['SalesDetail']['to_date'];
					$to_date=$this->request->data['SalesDetail']['to_date'];
					
				}
			else
				{
					$cond['Sale.sales_date']= date('Y-m-d');
					$to_date = date('Y-m-d');
				}
				
			
	  }
	  else
		{
		  
		  $cond['Sale.sales_date']= date('Y-m-d');
		  $from_date = date('Y-m-d');
		  $to_date = date('Y-m-d');
		}
		
			$conditions = array(
			'Sale.id >' => $this->request->data['id'],
			'Sale.is_active'=>BOOL_TRUE,
			'Sale.is_deleted'=>BOOL_FALSE,
			'Sale.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')
		     );
			$conditions=array_merge($conditions,$cond);
	
			$fields=array('Sale.id','Sale.invoice_no','Sale.state','Sale.customer_gstin','Sale.sales_date','Sale.total_amount','Sale.total_payment','Sale.total_balance','Sale.discount_amount','Sale.	gst_amt','Sale.gst_type');
			$contain=array(
			'Ledger'=>array('name'),
			'State'=>array('name')		
			);
			$salesdetaillist=$this->Sale->find('all',array('conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
	
			$data="";
		
			
			if(!empty($salesdetaillist))
			{
		
				
				foreach($salesdetaillist as $row)
				{
					$data.='<tr>
					<td>'.$row['Sale']['invoice_no'].'</td>
					<td>'.date('d-m-Y',strtotime($row['Sale']['sales_date'])).'</td>
					
					<td >'.$row['Ledger']['name'].'</td>
					<td >'.$row['Sale']['customer_gstin'].'</td>
					<td >'.$row['State']['name'].'</td>
					<td class="tamt">'.$row['Sale']['total_amount'].'</td>
					<td class="paid">'.$row['Sale']['total_payment'].'</td>
					<td class="balance">'.$row['Sale']['total_balance'].'</td>
					<td class="discount">'.$row['Sale']['discount_amount'].'</td>
					<td class="gst">'.$row['Sale']['gst_amt'].'</td>		
					</tr>';
					
					$lastrowID=$row['SalesDetail']['id'];
				
					
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
	Amit Sahu
	25.05.17
	Purchase register 
	*/
	public function purchaseRegister() 
	{
		$cond=array();
		$this->layout = ('shop/inner');	


		$this->loadModel('Purchase');
		$this->loadModel('Ledger');

		$user_profile_id=$this->Session->read('Auth.User.user_profile_id');

			$distributor=$this->Ledger->getLedgerListByGroup(GROUP_SUNDRY_CREDITOR_ID,$user_profile_id);	
		$this->set(compact('distributor'));		
		$wise="";
		$distName="All";

		$from_date="";
		$to_date="";
		if(isset($this->request->data['PurcahseRegister']))
		{					
			$this->Session->write('PurcahseRegisterSearch',$this->request->data['PurcahseRegister']);
		}
		else
		{	
			$this->request->data['PurcahseRegister']=$this->Session->read('PurcahseRegisterSearch');		
		}		
		if(isset($this->request->data['PurcahseRegister']))				
		{			
			if(isset($this->request->data['PurcahseRegister']['from_date']) and !empty($this->request->data['PurcahseRegister']['from_date']))				
			{
				$cond['DATE(Purchase.recieved_date) >=']=$this->request->data['PurcahseRegister']['from_date'];
				$from_date=$this->request->data['PurcahseRegister']['from_date'];
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
			if(isset($this->request->data['PurcahseRegister']['to_date']) and !empty($this->request->data['PurcahseRegister']['to_date']))				
			{
				$cond['DATE(Purchase.recieved_date) <=']=$this->request->data['PurcahseRegister']['to_date'];
				$to_date=$this->request->data['PurcahseRegister']['to_date'];
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
		
			if(isset($this->request->data['PurcahseRegister']['distributor']) and !empty($this->request->data['PurcahseRegister']['distributor']))				
			{
				$cond['Purchase.distributor_id']=$this->request->data['PurcahseRegister']['distributor'];
			
				$distData=$this->Ledger->findById($this->request->data['PurcahseRegister']['distributor']);
				if(!empty($distData))
				{
				$distName=$distData['Ledger']['name'];
				}
			}
			
			if(isset($this->request->data['PurcahseRegister']['wise']) and !empty($this->request->data['PurcahseRegister']['wise']))				
			{
				$wise=$this->request->data['PurcahseRegister']['wise'];
			
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
		$conditions = array(
				'Purchase.id !=' => BOOL_FALSE,
				'Purchase.is_deleted' => BOOL_FALSE,
				'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')
			);
			$conditions=array_merge($conditions,$cond);		
		if(empty($wise)){			
			
			$contain=array(
			'Ledger'=>array('name')
			);
			$orders=$this->Purchase->find('all',array('conditions'=>$conditions,'contain'=>$contain,'limit'=>PAGINATION_LIMIT,'order'=>array('Purchase.id'),'recursive'=>2));
			$this->set(compact('orders'));	
		}else
		{
			$group="";
				$fields=array('Purchase.id','Purchase.created','Purchase.recieved_date','SUM(Purchase.total_amount) as total','SUM(Purchase.total_payment) as payment','SUM(Purchase.total_balance) as balance',);
			if($wise==1)
			{
				$group='Purchase.recieved_date';
			}
			elseif($wise==2)
			{
				$group=array('MONTH(Purchase.recieved_date)','YEAR(Purchase.recieved_date)');
			}
			elseif($wise==3)
			{
					$group=array('YEAR(Purchase.recieved_date)');
			}
				
			
				$dateWise=$this->Purchase->find('all',array('conditions'=>$conditions,'fields'=>$fields,'group'=>$group,'order'=>array('Purchase.id')));
				$this->set(compact('dateWise'));
						
				
			
		}
		$this->set(compact('wise'));
	
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		$this->set(compact('date'));	
		
		$search='<tr class="border_none"><th colspan="7" class="border_none text-center">Distributor : '.$distName.'</th></tr>';
		$this->set(compact('search'));	
    }

	
	
		


	/*
	Amit sahu
	25.05.17
	reset purchase register search
	*/
	public function resetPurchaseRegisterSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;
		

		if ($this->Access->checkPermission(array(READ_PERMISSION_ID)))
		{
			$this->Session->delete('PurcahseRegisterSearch');
			$this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
			$this->redirect($this->referer());
		}
    } 
	/*
	Load more purchase register
	Amit Sahu
	25.05.17
	*/
	public function loadMorePurchaseRegister()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		
		$this->loadModel('Purchase');
		$this->loadModel('Distributor');


		if ($this->request->is('ajax')) 
			{
					$cond=array();
				if(isset($this->request->data['PurcahseRegister']))
					{					
						$this->Session->write('PurcahseRegisterSearch',$this->request->data['PurcahseRegister']);
					}
					else
					{	
						$this->request->data['PurcahseRegister']=$this->Session->read('PurcahseRegisterSearch');		
				}		
				if(isset($this->request->data['PurcahseRegister']))				
				{			
					if(isset($this->request->data['PurcahseRegister']['from_date']) and !empty($this->request->data['PurcahseRegister']['from_date']))				
					{
						$cond['DATE(Purchase.recieved_date) >=']=$this->request->data['PurcahseRegister']['from_date'];
					}	
					if(isset($this->request->data['PurcahseRegister']['to_date']) and !empty($this->request->data['PurcahseRegister']['to_date']))				
					{
						$cond['DATE(Purchase.recieved_date) <=']=$this->request->data['PurcahseRegister']['to_date'];
					}
					if(isset($this->request->data['PurcahseRegister']['distributor']) and !empty($this->request->data['PurcahseRegister']['distributor']))				
					{
						$cond['Purchase.distributor_id']=$this->request->data['PurcahseRegister']['distributor'];
					
					}
					
				}
		
			$conditions = array(
			'Purchase.id !=' => BOOL_FALSE,
			'Purchase.id >' => $this->request->data['id'],
			'Purchase.is_deleted' => BOOL_FALSE,
			'Purchase.purchase_status' => ORDER_STATUS_PURCHASE,
			'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')
			
		);
		$conditions=array_merge($conditions,$cond);		
		
		
		$contain=array(
		'Ledger'=>array('name')
		);
		$orders=$this->Purchase->find('all',array('conditions'=>$conditions,'contain'=>$contain,'limit'=>PAGINATION_LIMIT,'order'=>array('Purchase.id'),'recursive'=>2));
		$this->set(compact('orders'));	
			

			$data="";
			if(!empty($orders))
			{
				
				$rowdata= "";
				
				$period="";
				foreach($orders as $row)
				{
					
				
				if(!empty($row['Purchase']['recieved_date']))
				{
				$due=date('d-m-Y', strtotime($row['Purchase']['due_date']));
				}
				$grdate="";
				$created="";
				if(!empty($row['Purchase']['recieved_date'])) { $grdate=date('d-m-Y',strtotime($row['Purchase']['recieved_date'])); }
				if(!empty($row['Purchase']['created'])){ $created= date('d-m-Y',strtotime($row['Purchase']['created'])); }
					$data='<tr>
			
					<td class=""> '.$row['Purchase']['bill_no'].'</td>
					<td class=""> '.$row['Purchase']['bill_date'].'</td>
					<td class=""> '.$row['Ledger']['name'].'</td>
				
					<td class="tamt"> '.$row['Purchase']['total_amount'].'</td>
					<td class="pamt"> '.$row['Purchase']['total_payment'].'</td>
					<td class="bamt"> '.$row['Purchase']['total_balance'].'</td>
					</tr>';
					$lastrowID=$row['Purchase']['id'];
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
	/**************************************************************GSTR 2******************************************************/
/*
		Amit Sahu
		For hsn summary
		13-11-2017
		
	*/
	 public function gstr2HsnSummary() 
	{
		    $this->loadModel('Purchase');
		    $this->loadModel('PurchaseDetail');		
		    $this->loadModel('Unit');		
		    $this->loadModel('Item');		
			
			$cond=array();
			$this->layout = ('shop/inner');
			$from_date = "";
			$to_date = "";		
		
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['PurchaseDetail']))
			{
				$this->Session->write('PurchaseDetailhsnSearch',$this->request->data['PurchaseDetail']);			
				
			}
			else
			{
				$this->request->data['PurchaseDetail']=$this->Session->read('PurchaseDetailhsnSearch');			
				
			}	
			if(isset($this->request->data['PurchaseDetail']))				
				{					
				    if(isset($this->request->data['PurchaseDetail']['from_date']) and !empty($this->request->data['PurchaseDetail']['from_date']))				
						{
							$fdate='';
							if(!empty($this->request->data['PurchaseDetail']['from_date']))
							{
								$fdate=date('Y-m-d',strtotime($this->request->data['PurchaseDetail']['from_date']));
							}
							$cond['Purchase.bill_date >=']=$fdate;
						}
					if(isset($this->request->data['PurchaseDetail']['to_date']) and !empty($this->request->data['PurchaseDetail']['to_date']))				
						{
							
							if(!empty($this->request->data['PurchaseDetail']['to_date']))
							{
								$tdate=date('Y-m-d',strtotime($this->request->data['PurchaseDetail']['to_date']));
								$cond['Purchase.bill_date <=']=$tdate;
							}
						}				
				}
				else{
					if(!isset($cond)){
					     $cond['Purchase.bill_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
	 
		  
		  
			
			$conditions=array(			
			'Purchase.id !='=>BOOL_FALSE,
			'Purchase.is_deleted'=>BOOL_FALSE,
			'Purchase.is_active'=>BOOL_TRUE	,
            'PurchaseDetail.is_active'=>BOOL_TRUE,
			'PurchaseDetail.is_deleted'=>BOOL_FALSE,
			'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')	
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('DISTINCT  PurchaseDetail.item_id','PurchaseDetail.id','SUM(`PurchaseDetail`.`quantity`) as `qty`','SUM(`PurchaseDetail`.`total_amount`) as `taxableamt`','SUM(CASE WHEN Purchase.gst_type = '.IGST.' THEN ((PurchaseDetail.total_amount/100)*PurchaseDetail.gst_slab) ELSE 0 END) AS igst_amt','SUM(CASE WHEN Purchase.gst_type = '.CGST_SGST.' THEN ((PurchaseDetail.total_amount/100)*PurchaseDetail.gst_slab)  ELSE 0 END) AS csgst_amt');
			$group='PurchaseDetail.item_id';
			$contain=array(
			'Purchase'=>array('gst_type','total_amount'),'Item'=>array('name','hsn','Unit'=>array('code','name')),
			
			);
			$salesdetail=$this->PurchaseDetail->find('all',array('order'=>array('PurchaseDetail.id asc'),'conditions'=>$conditions,'group'=>$group,'fields'=>$fields,'contain'=>$contain,'recursive' => 2,'contain'=>$contain));
			//echo '<pre>';print_r($salesdetail);echo '</pre>';exit;
			/*echo "<pre>";
			print_r($salesdetail);
			exit;*/
			$this->set(compact('salesdetail'));	
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		
    }
	/*
	Reset Hsn summary search	
	Amit Sahu
	22.09.17
	*/
	public function resetGstr2hsnsummarySearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('PurchaseDetailhsnSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }		
	
	/*---------------------------------------------------------------Purchase--------------------------------------------------------------*/
	 public function purchaseBBList() 
	 {
		    $this->loadModel('Purchase');
		    $this->loadModel('PurchaseDetail');		
			
			$cond=array();
			$locname="";
		
			$this->layout = ('shop/inner');
		
			//$cond['Sale.location_id']=$this->Session->read('Auth.User.location_id');
		
			$from_date = "";
			$to_date = "";		
		
		
			
			
        if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['PurchaseDetail']))
			{
				$this->Session->write('PurchaseDetailSearch',$this->request->data['PurchaseDetail']);			
				
			}
			else
			{
				$this->request->data['PurchaseDetail']=$this->Session->read('PurchaseDetailSearch');			
				
			}	
			if(isset($this->request->data['PurchaseDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['PurchaseDetail']['month']) and !empty($this->request->data['PurchaseDetail']['month']))				
						{
							
						$dataArr=explode('-',$this->request->data['PurchaseDetail']['month']);
			//echo print_r($dataArr);exit;
			$conditions=array(			

			'PurchaseDetail.is_active'=>BOOL_TRUE,
			'MONTH(Purchase.bill_date)'=>$dataArr[1],
			'YEAR(Purchase.bill_date)'=>$dataArr[0],
			'PurchaseDetail.is_deleted'=>BOOL_FALSE,
			'Purchase.is_deleted'=>BOOL_FALSE,
			'Purchase.is_active'=>BOOL_TRUE,	
			'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')	
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('PurchaseDetail.id','PurchaseDetail.gst_slab', 'sum(PurchaseDetail.total_amount) as taxable','PurchaseDetail.purchase_id','PurchaseDetail.total_amount','Purchase.gstin');
		
			$contain=array(
			'Purchase'=>array('id','bill_date','bill_no','rcm_applicable','total_amount','Distributor'=>array('state','State'=>array('name','state_no')))
			
			);
			
			$salesdetaillist=$this->PurchaseDetail->find('all',array('order'=>array('PurchaseDetail.id asc'),'group'=>array('PurchaseDetail.gst_slab','PurchaseDetail.purchase_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain));
			//echo '<pre>';print_r($salesdetaillist);echo '</pre>';exit;
			/*$log = $this->SalesDetail->getDataSource()->getLog(false, false);
			pr($log);*/
				}
				}
			$this->set(compact('salesdetaillist'));	
        } 
		else {

            $this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
			
        }
		$date='From date : '.date('d-m-Y',strtotime($from_date)).' to '.date('d-m-Y',strtotime($to_date));
		

		
    }
		
	public function resetPurchaseBBReportSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->shop_check_login();
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('PurchaseDetailSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }		
	/*
	amol kathaley
	09-09-17
	function for load the next data in todays sale detail
	*/
	/*public function loadMorePurchaseReport()
	{	
		$locname="";
		
		
		
		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('PurchaseDetail');		
		$this->loadModel('Purchase');
		
		
		if ($this->request->is('ajax')) 
			{
				
			$cond=array();
			$salesdetaillist=array();
			if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) {
			if(isset($this->request->data['SalesDetail']))
			{
				$this->Session->write('PurchaseDetailSearch',$this->request->data['PurchaseDetail']);			
				
			}
			else
			{
				$this->request->data['PurchaseDetail']=$this->Session->read('PurchaseDetailSearch');			
				
			}	
			if(isset($this->request->data['PurchaseDetail']))				
				{					
				//echo '11';
					if(isset($this->request->data['PurchaseDetail']['from_date']) and !empty($this->request->data['PurchaseDetail']['from_date']))				
						{
							$cond['Purchase.bill_date >=']=$this->request->data['PurchaseDetail']['from_date'];
						}
					if(isset($this->request->data['PurchaseDetail']['to_date']) and !empty($this->request->data['PurchaseDetail']['to_date']))				
						{
							$cond['Purchase.bill_date <=']=$this->request->data['PurchaseDetail']['to_date'];
						}				
				}else{
					if(!isset($cond)){
					//echo '22';
						$cond['Purchase.bill_date']= date('Y-m-d');
						$from_date = date('Y-m-d');
						$to_date = date('Y-m-d');					
					}
				}
			}

			$conditions=array(			
			'PurchaseDetail.id >' => $this->request->data['id'],
			'PurchaseDetail.is_active'=>BOOL_TRUE,
			'PurchaseDetail.is_deleted'=>BOOL_FALSE,
			'Purchase.is_deleted'=>BOOL_FALSE,
			'Purchase.is_active'=>BOOL_TRUE,	
			'Purchase.user_profile_id'=>$this->Session->read('Auth.User.user_profile_id')	
			);			
			
			$conditions=array_merge($conditions,$cond);
			$fields=array('PurchaseDetail.id','PurchaseDetail.gst_slab', 'sum(PurchaseDetail.total_amount) as taxable','PurchaseDetail.purchase_id','PurchaseDetail.total_amount','Purchase.gstin');
		
			$contain=array(
			'Purchase'=>array('id','bill_date','bill_no','rcm_applicable','total_amount','Distributor'=>array('state','State'=>array('name','state_no')))
			
			);
			
			$salesdetaillist=$this->PurchaseDetail->find('all',array('order'=>array('PurchaseDetail.id asc'),'group'=>array('PurchaseDetail.gst_slab','PurchaseDetail.purchase_id'),'fields'=>$fields,'conditions'=>$conditions,'recursive' => 2,'contain'=>$contain,'limit' => PAGINATION_LIMIT_1));
			$data="";
		
			
			if(!empty($salesdetaillist))
			{
				
				
				foreach($salesdetaillist as $row)
				{
					
					$saledata="";
					$exe="";
					
					if($row['Purchase']['rcm_applicable'] == 0){ $ex='N';}else{ $ex='Y';}
					$loc="";
					if(!empty($row['Purchase']['Distributor']['state'])){
						$loc=$row['Purchase']['Distributor']['State']['state_no'].'-'.$row['Purchase']['Distributor']['State']['name'];
					}
					$data.='<tr>
							<td >'.$row['Purchase']['gstin'].'</td>
							<td>'.$row['Purchase']['bill_no'].'</td>							
							<td >'.$row['Purchase']['bill_date'].'</td>
							<td >'.$row['Purchase']['total_amount'].' </td>
							<td >'.$loc.'</td>
							<td >'.$ex.'</td>
							<td >Regular</td>
							<td >--</td>
							<td  class="tamt">'.$row['PurchaseDetail']['gst_slab'].'</td>
							<td  class="damt">'.$row[0]['taxable'].'</td>
							<td>--</td>
							</tr>';
					$lastrowID=$row['PurchaseDetail']['id'];
					
					
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
	
}
