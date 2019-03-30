<?php

App::uses('AppModel', 'Model');

/**
 * City Model
 *
 */
class City extends AppModel {


public $validate = array(
        
			'name' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Please enter district name',
				'allowEmpty' => false
            ),
			'isUnique' => array(
			'rule' => array('isUnique', array('name', 'state_id','is_deleted'=>BOOL_FALSE), false),
			'message' => 'The district with this state already exists.'
			)
			),			
			'state_id' => array(
            'nonEmpty' => array(
                'rule' => array('notEmpty'),
                'message' => 'Please select state',
				'allowEmpty' => false
            ))
			
	);
	
	public $belongsTo = array(
        'State' => array(
            'className' => 'State',
            'foreignKey' => 'state_id'
        )
    );	
	
	public function getCityList() {	
        $fields = array('City.id','City.name');
		$conditions = array('City.is_deleted !='=>BOOL_TRUE,'City.is_active !='=>BOOL_FALSE);
        return $this->find('list', array('fields' => $fields,'conditions'=>$conditions,'order'=>'name asc'));
    }
}
