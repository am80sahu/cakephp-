<?php
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
 // set allowed extensions for site
	Router::parseExtensions('html', 'rss');
	
	
 //default
	Router::connect('/', array('controller' => 'shops', 'action' => 'login','shop'=>true));
		
	Router::connect('/shops/login', array('controller' => 'shops', 'action' => 'login','shop'=>true));
	
	Router::connect('/manager', array('controller' => 'managers', 'action' => 'login','manager'=>true));


// setting routes for admin
	Router::connect('/admin', array('controller' => 'managements','action' => 'login','admin' => true,'ext' => URL_EXTENSION));
	Router::connect('/admins', array('controller' => 'managements','action' => 'login','admin' => true,'ext' => URL_EXTENSION));

	
Router::connect('/admin/managements/forgotPassword/',array('controller' => 'managements','action' => 'forgotPassword','admin' => true,'ext' => URL_EXTENSION));
	Router::connect('/admin/managements/forgotPassword/',array('controller' => 'managements','action' => 'forgotPassword','admin' => true,'ext' => URL_EXTENSION));
		

	//route to switch locale
Router::connect('/lang/*', array('controller' => 'P28n', 'action' => 'changenew'));


//forgiving routes that allow users to change the lang of any page
Router::connect('/en?/*', array(
    'controller' => "p28n",
    'action' => "shuntRequest",
    'lang' => 'eng'
));

Router::connect('/hi/*', array(
    'controller' => "p28n",
    'action' => "shuntRequest",
    'lang' => 'hin'
));


/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
