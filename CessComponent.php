<?php

class CessComponent extends Component {
    function Query($table=NULL,$type=NULL,$array) 
	{					 
	 	$this->$table = ClassRegistry::init($table);	 		
	 	$result = $this->$table->find($type,$array);
//		echo $this->sql();
		return $result;
    }
	
	
    //    function to change
    function getCessAmount($item_id=NULL, $qty=NULL,$amount=NULL){
        // $model = ClassRegistry::init('Item');
		  $itemdata=$this->Query("Item","first",array('conditions'=>array('Item.id'=>$item_id),'fields'=>array('Item.cess_type','Item.cess_amt','Item.id','Item.cess_per','Item.unit'),'contain'=>array()));
			$cess_amount=0;
		  if(!empty($itemdata))
		  {
			 $type=$itemdata['Item']['cess_type'];
			 //Percentage
			 if($type==1)
			 {
				 $percentage=$itemdata['Item']['cess_per'];
				 $cess_amount=($amount/100)*$percentage;
			 }
			 //Percentage + Amount per thousand
			else  if($type==2)
			 {
				 $percentage=$itemdata['Item']['cess_per'];
				 $cess_amount=($amount/100)*$percentage;
				 $th=$amount/1000;
				 $th=(int)$th;
				 if($th>0)
				 {
					 $amount_pr_th=$itemdata['Item']['cess_amt'];
					 $cess_amount=$cess_amount+($amount_pr_th*$th);
				 }
			 }
			 //Percentage or Amount per thousand, whichever is higher
			else if($type==3)
			 {
				 $percentage=$itemdata['Item']['cess_per'];
				 $cess_amount=($amount/100)*$percentage;
				 $th=$amount/1000;
				 $th=(int)$th;
				 $cess_amount_pr_th=0;
				 if($th>0)
				 {
					 $amount_pr_th=$itemdata['Item']['cess_amt'];
					 $cess_amount_pr_th=$amount_pr_th*$th;
				 }
				 if($cess_amount>$cess_amount_pr_th)
				 {
					 $cess_amount=$cess_amount;
				 }else{
					  $cess_amount=$cess_amount_pr_th;
				 }
			 }
			 //Amount per thousand
			else if($type==4)
			 {
			
				 $th=$amount/1000;
				 $th=(int)$th;
				 $cess_amount_pr_th=0;
				 if($th>0)
				 {
					 $amount_pr_th=$itemdata['Item']['cess_amt'];
					 $cess_amount=$amount_pr_th*$th;
				 }
				 
			 }
			 //Amount per tonne
			else if($type==5)
			 {
		
				if($itemdata['Item']['unit']==6)
				{
				
					$amount_pr_ton=$itemdata['Item']['cess_amt'];
					$cess_amount=$amount_pr_ton*$qty;
				}
				 
			 }
		  }
		  
		 
		 return $cess_amount; 
    }
	

  

}

