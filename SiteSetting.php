<?php

App::uses('AppModel', 'Model');

/**
 * SiteSetting Model
 */
class SiteSetting extends AppModel {

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array();

    // #function is used to get site setting value by passing key (like site_name, bar_tool_price etc)
    function getSettingValue($fieldKey) {
        $options = array(
            'fields' => 'value',
            'conditions' => array(
                'name' => $fieldKey
            )
        );
        $result = $this->find('first', $options);
        if ($result) {
            return $result ['SiteSetting'] ['value'];
        } else {
            return false;
        }
    }

    // function to find site setting data
    public function findSiteSettingData($type = 'first', $conditions = null, $fields = null, $contain = null, $order = null, $group = null, $recursive = null) {
        $siteSettingData = $this->find($type, array(
            'conditions' => $conditions,
            'fields' => $fields,
            'contain' => $contain,
            'order' => $order,
            'group' => $group,
            'recursive' => $recursive
        ));
        return $siteSettingData;
    }

}
