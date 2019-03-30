<?php

App::uses('AppModel', 'Model');

/**
 * Group Model
 *
 */
class Group extends AppModel {
    
	public $validate = array(
			'name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Please enter group name',
				'allowEmpty' => false
            ),
			'isUnique' => array(
			'rule' => array('isUnique', array('name','is_deleted'=>BOOL_FALSE), false),
			'message' => 'The group alreay exists.'
			)
			)	
	);
		public $belongsTo = array(
	
		'ParentGroup' => array(
            'className' => 'Group',
            'foreignKey' => 'parent_id'
        )
		);
	public $hasMany = array(	
		'Ledger' => array(
            'className' => 'Ledger',
            'foreignKey' => 'group_id'
        ),

    );		
	
	/*
	Amit Sahu
	24.02.17
	get all Group list
	*/
	public function getGroupList($profile_id=NULL) {	
        $fields = array('Group.id','Group.name');
		$conditions = array('Group.is_deleted !='=>BOOL_TRUE,'Group.is_active !='=>BOOL_FALSE,'OR'=>array('OR'=>array('Group.is_default'=>BOOL_TRUE),array('Group.is_default'=>BOOL_FALSE,'Group.user_profile_id'=>$profile_id)));
        return $this->find('list', array('fields' => $fields,'conditions'=>$conditions,'order'=>'name asc'));
    }
	/*
	Amit Sahu
	24.02.17
	get all Group list
	*/
	public function getAllGroupData($conditions,$fields) {	
        return $this->find('all', array('fields' => $fields,'conditions'=>$conditions,'order'=>'name asc'));
    }
}
