<?php 
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */


/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class P28nController extends AppController {
    var $name = 'P28n';
    var $uses = null;
    var $components = array('P28n');

	public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('changenew', 'shuntRequest');
    }
	
    function changenew($lang = null) {
		    
            $this->P28n->change($lang);

            $this->redirect($this->referer(null, true));
    }

    function shuntRequest() {
		
            $this->P28n->change($this->params['lang']);

            $args = func_get_args();
            if(isset($_SERVER['HTTP_REFERER'])){
				$this->redirect($_SERVER['HTTP_REFERER']);
			}else{
				$this->redirect('/');
			}
    }
}

?>