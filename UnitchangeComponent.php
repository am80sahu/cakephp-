<?php

class UnitchangeComponent extends Component {
    function Query($table=NULL,$type=NULL,$array) 
	{					 
	 	$this->$table = ClassRegistry::init($table);	 		
	 	$result = $this->$table->find($type,$array);
//		echo $this->sql();
		return $result;
    }
	
	
    //    function to change
    function change($item_id=NULL, $value=NULL,$unit=NULL){
        // $model = ClassRegistry::init('Item');
		  $itemdata=$this->Query("Item","first",array('conditions'=>array('Item.id'=>$item_id),'fields'=>array('Item.unit','Item.alt_unit','Item.id'),'contain'=>array()));
				$changeValue['qty']=$value;
			  $changeValue['unit']=$unit;
		  if(!empty($itemdata))
		  {
			  if(!empty($itemdata['Item']['alt_unit']))
			  {
				  if($itemdata['Item']['alt_unit']==$unit)
				  {
					  $this->$unit = ClassRegistry::init('Unit');
						$unitdata=$this->$unit->findById($unit);
						  $changeValue['qty']=$value/$unitdata['Unit']['alt_clac'];
						  $changeValue['unit']=$itemdata['Item']['unit'];
					
				  }
				  /*if($itemdata['Item']['alt_unit']==GMS and $unit==GMS)
				  {
					  if($itemdata['Item']['unit']==KGS)
					  {
						  $changeValue['qty']=$value/1000;
						  $changeValue['unit']=KGS;
					  }
				  }
				   elseif($itemdata['Item']['alt_unit']==KGS and $unit==KGS)
				  {
					  
					  if($itemdata['Item']['unit']==QTL)
					  {
						  $changeValue['qty']=$value/100;
						  $changeValue['unit']=QTL;
					  }
				  }
				  elseif($itemdata['Item']['alt_unit']==QTL and $unit==QTL)
				  {
					  if($itemdata['Item']['unit']==TON)
					  {
						  $changeValue['qty']=$value/10;
						  $changeValue['unit']=TON;
					  }
				  }
				  elseif($itemdata['Item']['alt_unit']==INC and $unit==INC)
				  {
					  if($itemdata['Item']['unit']==FTS)
					  {
						  $changeValue['qty']=$value/12;
						  $changeValue['unit']=FTS;
					  }
				  }
				  elseif($itemdata['Item']['alt_unit']==PCS and $unit==PCS)
				  {
					  if($itemdata['Item']['unit']==DOZ)
					  {
						  $changeValue['qty']=$value/12;
						  $changeValue['unit']=DOZ;
					  }
				  }
				   elseif($itemdata['Item']['alt_unit']==CMS and $unit==CMS)
				  {
					  if($itemdata['Item']['unit']==MTR)
					  {
						  $changeValue['qty']=$value/1000;
						  $changeValue['unit']=MTR;
					  }
				  }*/
			  }else{
				  $changeValue['qty']=$value;
				   $changeValue['unit']=$unit;
			  }
		  }else{
			  $changeValue['qty']=$value;
			  $changeValue['unit']=$unit;
		  }
		  
		  
		 return $changeValue; 
    }
	 function changeReverse($item_id=NULL, $value=NULL,$unit=NULL){
        // $model = ClassRegistry::init('Item');
		  $itemdata=$this->Query("Item","first",array('conditions'=>array('Item.id'=>$item_id),'fields'=>array('Item.unit','Item.alt_unit','Item.id'),'contain'=>array()));
				$changeValue['qty']=$value;
			  $changeValue['unit']=$unit;
	
			  if(!empty($itemdata['Item']['alt_unit']))
			  {
				  if($itemdata['Item']['unit']==$unit)
				  {
					  $this->$unit = ClassRegistry::init('Unit');
						$unitdata=$this->$unit->findById($itemdata['Item']['alt_unit']);
						  $changeValue['qty']=$value*$unitdata['Unit']['alt_clac'];
						  $changeValue['unit']=$itemdata['Item']['alt_unit'];
					
				  }
			  }else{
				  $changeValue['qty']=$value;
				   $changeValue['unit']=$unit;
			  }
		 
		  
		  
		 return $changeValue; 
    }

  

}

