<?php

App::uses('AppModel', 'Model');

class CreditNote extends AppModel {

  public $validate = array(
     
	 'customer_id' => array(
		'notempty' => array(
			'rule' => array('notempty'),
			'message' => 'Please enter custome ',
		),			
		),	
	 
		
 );
	 
    public $belongsTo = array(

		'Member' => array(
            'className' => 'Member',
            'foreignKey' => 'customer_id'
        ),	
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
	
		'CreditNoteDetail' => array(
            'className' => 'CreditNoteDetail',
            'foreignKey' => 'credit_note_id'
        ),
		
		'PaymentTransaction' => array(
            'className' => 'PaymentTransaction',
            'foreignKey' => 'reference_id',
			'conditions'=>array(
				'PaymentTransaction.type'=>PURCHASE_RETURN_PAYMENT
			)
        ),
		
    );	
}