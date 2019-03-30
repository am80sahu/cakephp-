<?php

App::uses('AppModel', 'Model');

class DebitNote extends AppModel {

  public $validate = array(
		'distributor_id' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Please select distributor',
			),			
		),
		'total_amount' => array(
			
			'price' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid amount',
				'allowEmpty' => true,
			)
		),
		'total_payment' => array(			
			'price' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid amount',
				'allowEmpty' => true,
			)
		),
		'total_balance' => array(
			
			'price' => array(
				'rule' => array('decimal'),
				'message' => 'Please enter valid amount',
				'allowEmpty' => true,
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
            'foreignKey' => 'distributor_id'
        ),
	
		
    );
	
	public $hasMany = array(
	
		'DebitNoteDetail' => array(
            'className' => 'DebitNoteDetail',
            'foreignKey' => 'debit_note_id'
        ),
		
		'PaymentTransaction' => array(
            'className' => 'PaymentTransaction',
            'foreignKey' => 'reference_id',
			'conditions'=>array(
				'PaymentTransaction.type'=>SALE_RETURN_PAYMENT,
			)
        ),		
    );	
	/*
	Amit Sahu
	24.02.17
	get all purchase data
	
	public function getAllPurchaseData($conditions,$fields) {	
        return $this->find('all', array('fields' => $fields,'conditions'=>$conditions));
    }*/
}