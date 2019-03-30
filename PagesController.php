<?php
App::uses('AppController', 'Controller');
App::uses('AuthComponent', 'Controller/Component');

class PagesController extends AppController {

    public $helpers = array('Date','Jqimageresize');
    public $uses = array('SiteContent');	
	public $components = array('Paginator','Files','Img');
	
	public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('home', 'display');
    }
	public function initialize(){
    parent::initialize();
    // Set the layout
    $this->layout = 'frontend';
    }
	
/**
 * Displays a view
 *
 * @return void
 * @throws NotFoundException When the view file could not be found
 *	or MissingViewException in debug mode.
 */
	
	// This function is used to display cms pages.
    public function display($slug = NULL) {
        
		$this->layout = 'default';
		$conditions = array(
            'slug' => $slug,
            'is_active' => BOOL_TRUE,
            'is_deleted' => BOOL_FALSE
        );
        $fields = array(
            'name',
            'content'
        );
		
		$langCode = $this->Session->read('Config.language');
		
		$objContent = ClassRegistry::init('SiteContent');

		$contentID = $objContent->getContentDataByAlias($slug);
		
		if($langCode == "en" || $langCode == ""){

			$contentData = $contentID;
		}
		else
		{
			App::import('Model','ContentTranslation');
			$contenttranslation = new ContentTranslation();
			$contentTranslatedData = $contenttranslation->getTranslatedData($contentID['SiteContent']['id']);

			if(empty($contentTranslatedData))
			{
				
					
					$contentTranslatedData1 = $contenttranslation->getTranslatedData($contentID['SiteContent']['id']);

					if(!empty($contentTranslatedData1))
					{
						$contentData = $contentTranslatedData1;
					}else{
						$contentData = $contentID;
					}

				
			}else{

				$contentData = $contentTranslatedData;
			}

		}
		
        $this->set('pageData',$contentData);
		$this->set('langcode',$langCode);
    }

    public function home() {
	
	}

}

?>