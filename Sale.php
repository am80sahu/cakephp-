<?php

App::uses('AppModel', 'Model');

class Sale extends AppModel {

  public $validate = array(

	 'sales_date' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter selling date',
			),
			'date' => array(
				'rule' => 'date',
				'allowEmpty' => false,
				'message' => 'Please enter vaild date'
			),
		
		),			
		'total_amount' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter total amount',
			),
			'price' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid amount',
			)
		),
		'total_payment' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter total payment',
			),
			'price' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid amount',
			)
		),
		'total_balance' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter total balance',
			),
			'price' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid amount',
			)
		),
		'final_total_amount' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter final total amount',
			),
			'price' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid amount',
			)
		),
		'notes' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please enter notes',
			),
		),	
		
 );
	 
    public $belongsTo = array(
	
	
		
		'Ledger' => array(
            'className' => 'Ledger',
            'foreignKey' => 'customer_id'
        ),
			'State' => array(
            'className' => 'State',
            'foreignKey' => 'state'
        ),
		
		
    );
	
	
	public $hasMany = array(
	
		'SalesDetail' => array(
            'className' => 'SalesDetail',
            'foreignKey' => 'sales_id'
        ),
		'SalesReturn' => array(
            'className' => 'SalesReturn',
            'foreignKey' => 'sales_id'
        ),
		'SaleCharge' => array(
            'className' => 'SaleCharge',
            'foreignKey' => 'sale_id'
        ),
		
		'PaymentTransaction' => array(
            'className' => 'PaymentTransaction',
            'foreignKey' => 'reference_id',
			'conditions'=>array(
				'PaymentTransaction.type'=>SALE_PAYMENT
			)
        ),
		
    );	
	
	/*
	Amit Sahu
	24.02.17
	get all sale data
	*/
	public function getSaleData($conditions,$fields) {	
        return $this->find('all', array('fields' => $fields,'conditions'=>$conditions));
    }
	/*
	Amit Sahu
	24.02.17
	get first sale data
	*/
	public function getSaleFirstData($conditions,$fields) {	
        return $this->find('first', array('fields' => $fields,'conditions'=>$conditions,'recursive'=>-1));
    }
	
	public function saleCount($conditions) {	
        return $this->find('count', array('conditions'=>$conditions));
    }
	
}