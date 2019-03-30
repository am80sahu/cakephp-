<?php

App::uses('Controller', 'Controller');

class AppController extends Controller {
	
	public $helpers = array(
		'Form',
		'Html',
		'Js',
		'Time',
		'Encryption', //helper for encryption/decryption
		'Access',
		'Content',		
		'Common',		
		'Session',// Amit Sahu (12.09.17) 		
	);
	
	public $components = array(
		'Session',
		'Cookie',
		'Encryption',
		'Access',
		'Email',
		'P28n',
		'Common',
		'Cess',
		'DebugKit.Toolbar',
		'Auth' => array(
			'authenticate' => array(
				'Form' => array(
					'fields' => array('username' => 'email'),
					'scope' => array('User.is_active' => BOOL_TRUE, 'User.is_deleted' => BOOL_FALSE)
				)
			),
			'authorize' => 'Controller'
		),
		'RequestHandler',		
	);
	
	
	// callback function
    /*
     * (non-PHPdoc)
     * @see Controller::beforeFilter()
     */
    public function beforeFilter() {
		
		
        // checking if the request is from admin or user		
        if (!empty($this->params ['admin'])) {
		
			$this->Auth->loginAction = array('controller' => 'managements', 'action' => 'login', 'admin' => true);
			$this->Auth->loginRedirect = array('controller' => 'managements', 'action' => 'index', 'admin' => true);
			$this->Auth->logoutRedirect = array('controller' => 'managements', 'action' => 'login', 'ext' => URL_EXTENSION, 'admin' => true);
        } 

		else if (!empty($this->params ['shop'])) {
		
			$this->Auth->loginAction = array('controller' => 'shops', 'action' => 'login', 'shop' => true);
			$this->Auth->loginRedirect = array('controller' => 'shops', 'action' => 'index', 'shop' => true);
			$this->Auth->logoutRedirect = array('controller' => 'shops', 'action' => 'login', 'shop' => true);
        } 
		else if (!empty($this->params ['manager'])) {
		
			$this->Auth->loginAction = array('controller' => 'managers', 'action' => 'login', 'ext' => URL_EXTENSION, 'manager' => true);
			$this->Auth->loginRedirect = array('controller' => 'managers', 'action' => 'index', 'manager' => true);
			$this->Auth->logoutRedirect = array('controller' => 'managers', 'action' => 'login', 'manager' => true);
        } 
		
		else {
			$this->Auth->loginAction = array('controller' => 'users', 'action' => 'login', 'admin' => false);
			$this->Auth->loginRedirect = array('controller' => 'users', 'action' => 'dashboard', 'ext' => URL_EXTENSION, 'admin' => false);
			$this->Auth->logoutRedirect = array('controller' => 'pages', 'action' => 'home', 'admin' => false);
        }

        //set layout based on user session
        if ($this->Auth->user()) {
            /*$this->layout = 'front/inner';*/
			$this->layout = 'default';
        } else {
            $this->layout = 'default';
        }

        $this->set('loginInfo', $this->Auth->user());						
		
    }	
	
    public function isAuthorized($user = NULL) {
	
        // Any registered user can access public functions
        if (empty($this->request->params ['admin'])) {
            return true;
        }

        // Only admins can access admin functions
        if (isset($this->request->params ['admin'])) {
            return (bool) ($user ['Role'] ['name'] === 'Administrator' || $user ['Role'] ['name'] === 'Co-Administrator');
        }

        // Default deny
        return false;
    }

	/**
     * Function : check_login
     * @access private
     * Description : Function for check user login
     * Date : 11th may 2015
     */
    function admin_check_login() {
        $logininfo = $this->Session->read('Auth');
        $user_id = $this->Session->read('Auth.User.id');
        if ($user_id == "") {
            $this->Session->delete('Auth');
            $this->redirect('/admins/');
        }
    }
	/**
     * Function : check_login
     * @access private
     * Description : Function for check user login
     * Date : 01.02.17
	 * Amit Sahu
     */
    function manager_check_login() {
        $logininfo = $this->Session->read('Auth');
        $user_id = $this->Session->read('Auth.User.id');
        if ($user_id == "") {
            $this->Session->delete('Auth');
            $this->redirect('/manager/');
        }
    }
		/**
     * Function : check_login
     * @access private
     * Description : Function for check user login
     * Date : 01.02.17
	 * Amit Sahu
     */
    function godown_check_login() {
        $logininfo = $this->Session->read('Auth');
        $user_id = $this->Session->read('Auth.User.id');
        if ($user_id == "") {
            $this->Session->delete('Auth');
            $this->redirect('/godown/');
        }
    }
	/**
     * Function : check_login
     * @access private
     * Description : Function for check user login
     * Date : 01.02.17
	 * Amit Sahu
     */
    function shop_check_login() {
        $logininfo = $this->Session->read('Auth');
        $user_id = $this->Session->read('Auth.User.id');
        if ($user_id == "") {
            $this->Session->delete('Auth');
            $this->redirect('/shop/');
        }
    }
	/**
     * Function : check_login
     * @access private
     * Description : Function for check user login
     * Date : 29.05.17
	 * Amit Sahu
     */
    function emp_check_login() {
        $logininfo = $this->Session->read('Auth');
        $user_id = $this->Session->read('Auth.User.id');
        if ($user_id == "") {
            $this->Session->delete('Auth');
            $this->redirect('/emp/');
        }
    }

}
