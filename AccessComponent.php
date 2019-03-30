<?php

/**
 * @author vivekshukla
 *
 */
App::uses('Component', 'Controller');

class AccessComponent extends Component {

    var $components = array("Auth");

    /**
     * Function to check user permission against the action being performed
     * checkPermission method
     * @name checkPermission
     * @acces public 
     * @param  $permissionId(id of permission)
     * @return void
     * @created 18 Nov 2014
     * @modified 18 Nov 2014
     */
    function checkPermission($permissionId = array()) {
        if (is_array($permissionId)) {
            if ($this->Auth->user()) {
						
                if (trim($this->Auth->user('role_id') == ADMIN_ROLE_ID)) {
                    return true;
                } else {
                    $type = 'first';
                    $conditions = array(
                        'RolePermission.role_id' => trim($this->Auth->user('role_id')),
                        'RolePermission.permission_id' => !empty($permissionId) ? $permissionId : READ_PERMISSION_ID,
                        'RolePermission.is_active' => BOOL_TRUE,
                        'RolePermission.is_deleted' => BOOL_FALSE
                    );
                    $fields = array(
                        'RolePermission.id', 'RolePermission.is_permitted'
                    );
                    $contain = NULL;
                    $order = NULL;
                    $group = NULL;
                    $recursive = 0;
                    $this->RolePermission = ClassRegistry::init('RolePermission');					
                    $isPermitted = $this->RolePermission->getPermissionData($type, $conditions, $fields, $contain, $order, $group, $recursive);
					if(!empty($isPermitted['RolePermission']))
					{
						return (trim($isPermitted['RolePermission']['is_permitted']) == 1) ? true : false;
					}
                    else
					{
						return false;
					}
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
