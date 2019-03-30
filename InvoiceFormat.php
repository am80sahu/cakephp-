<?php

App::uses('AppModel', 'Model');

/**
 * InvoiceFormat Model
 *
 */
class InvoiceFormat extends AppModel {


public $validate = array(
        
			'name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Please enter name',
				'allowEmpty' => false
            ),
			'isUnique' => array(
			'rule' => array('isUnique', array('name','is_deleted'=>BOOL_FALSE), false),
			'message' => 'The name already exist.'
			)
			),			
		
	);
	
	public function invoiceFormatList() {	
        $fields = array('InvoiceFormat.id','InvoiceFormat.name');
		$conditions = array('InvoiceFormat.is_deleted !='=>BOOL_TRUE,'InvoiceFormat.is_active !='=>BOOL_FALSE);
        return $this->find('list', array('fields' => $fields,'conditions'=>$conditions));
    }
	
}
