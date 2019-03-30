<?php

App::uses('AppModel', 'Model');

/**
 * Ledger Model
 *
 */
class Ledger extends AppModel {


public $validate = array(
        
			'name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Please enter ledger name',
				'allowEmpty' => false
            ),
			'isUnique' => array(
			'rule' => array('isUnique', array('name','user_profile_id','group_id','is_deleted'=>BOOL_FALSE), false),
			'message' => 'The author with this ledger exists.'
			)
			),	
			/*'group_id' => array(
			'notempty' => array(
			'rule' => array('notempty'),
			'message' => 'Please select group',
			)),*/
			
			
			
	);
	public $belongsTo = array(
	
		'Group' => array(
            'className' => 'Group',
            'foreignKey' => 'group_id'
        )
		);
		public $hasOne = array(	
		'PartyDetail' => array(
            'className' => 'PartyDetail',
            'foreignKey' => 'ledger_id'
        ),
	
		);
		
	/*
	Amit Sahu
	03.02.17
	get all ledger list
	*/
	public function getLedgerList($profile_id=null){	
        $fields = array('Ledger.id','Ledger.name');
		$conditions = array('Ledger.is_deleted !='=>BOOL_TRUE,'Ledger.is_active !='=>BOOL_FALSE,'OR'=>array('OR'=>array('Ledger.is_default'=>BOOL_TRUE),array('Ledger.is_default'=>BOOL_FALSE,'Ledger.user_profile_id'=>$profile_id)));
        return $this->find('list', array('fields' => $fields,'conditions'=>$conditions,'order'=>'name asc'));
    }
	/*
	Amit Sahu
	03.02.17
	get all ledger list
	*/
	public function getLedgerListByGroup($group_id=null,$profile_id=null){	
        $fields = array('Ledger.id','Ledger.name');
		$conditions = array('Ledger.is_deleted !='=>BOOL_TRUE,'Ledger.is_active !='=>BOOL_FALSE,'Ledger.group_id'=>$group_id,'Ledger.user_profile_id'=>$profile_id);
        return $this->find('list', array('fields' => $fields,'conditions'=>$conditions,'order'=>'name asc'));
    }

	
}
