<?php

App::uses('AppModel', 'Model');

class Stock extends AppModel {

  public $validate = array(
     		
	 'location_id' => array(
		'notempty' => array(
			'rule' => array('notempty'),
			'message' => 'Please select location',
		),			
		),
		'item_id' => array(
		'notempty' => array(
			'rule' => array('notempty'),
			'message' => 'Please select item',
		),			
		),	 		
		'quantity' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter quantity',
			),
			'decimal' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid quantity',
			)
		),

 );
	 
    public $belongsTo = array(
	
		
		'Item' => array(
            'className' => 'Item',
            'foreignKey' => 'item_id'
        ),
		
    );
	
	
	/*
	Amit Sahu
	08.02.17
	get stocks item and location wise
	*/
	public function getallStock($conditions,$fields) {	
        return $this->find('first', array('fields' => $fields,'conditions'=>$conditions));
    }
		/*
	Amit Sahu
	08.02.17
	get stocks item and location wise
	*/
	public function getallStockData($conditions,$fields) {	
        return $this->find('all', array('fields' => $fields,'conditions'=>$conditions));
    }
}