<?php
App::uses('AppController', 'Controller');
class UtilitiesController extends AppController {

	public $components = array('Paginator');
    public function beforeFilter()
	{
        parent::beforeFilter();
        $authAllowedActions = array('admin_login', 'admin_forgotPassword', 'admin_resetPassword','admin_countriesList','getStates','getDistricts','getCities','getTalukas','getVillages');
		
        $this->Auth->allow($authAllowedActions);
        if (!in_array($this->Auth->user('role_id'), array(ADMIN_ROLE_ID,CO_ADMIN_ROLE_ID))) 
		{
            $this->Auth->logout();
        }

        //set layout based on user session
        if ($this->Auth->user()) 
		{
            $this->layout = 'admin/inner';
        }else{
            $this->layout = 'admin/outer';
        }
    }
	
    public function admin_statesList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('State');
		
		
		if(isset($this->request->data['State']))
		{					
		$this->Session->write('StateSearch',$this->request->data['State']);
		}
		else
		{	
		$this->request->data['State']=$this->Session->read('StateSearch');
		
		}		
		if(isset($this->request->data['State']))				
		{			
			if(isset($this->request->data['State']['name']) and !empty($this->request->data['State']['name']))				
			{
				$cond['State.name LIKE']="%".$this->request->data['State']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'State.id !=' => BOOL_FALSE,
			'State.is_deleted' => BOOL_FALSE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'State' => array(
				'conditions' => $conditions,
				'order' => array('State.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$states = $this->Paginator->paginate('State');
		$this->set(compact('states'));		
		
		
    }
	
	
	public function admin_resetStateSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('StateSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
    public function admin_addState() {
		$this->admin_check_login();
		$this->loadModel('Country');
		$this->loadModel('State');
        
		if ($this->request->is('post')) 
		{	
			
			$this->State->create();			
			if ($this->State->save($this->request->data)) 
			{
				$this->Session->setFlash('The state has been saved', 'success');
				return $this->redirect(array('controller'=>'utilities','action' => 'statesList','admin'=>true,'ext'=>URL_EXTENSION));
			} 
			else 
			{
				$this->Session->setFlash('The state could not be saved. Please, try again.', 'error');
			}
		
        }		
		
		$countries=$this->Country->find('list',array('conditions'=>array('Country.id !='=>BOOL_FALSE,'Country.is_deleted'=>BOOL_FALSE),'order'=>array('Country.name'=>'ASC')));
		$this->set(compact('countries'));
    }


    public function admin_editState($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);
		$this->loadModel('Country');
		$this->loadModel('State');
		
		$countries=$this->Country->find('list',array('conditions'=>array('Country.id !='=>BOOL_FALSE,'Country.is_deleted'=>BOOL_FALSE),'order'=>array('Country.name'=>'ASC')));
		$this->set(compact('countries'));
		
		if (!$this->State->exists($id)) 
		{
            throw new NotFoundException('Invalid State');
        }
        
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			$this->State->id=$id;
			if ($this->State->save($this->request->data)) 
			{
				$this->Session->setFlash('The state has been updated', 'success');
				return $this->redirect(array('controller'=>'utilities','action' => 'stateList','admin'=>true,'ext'=>URL_EXTENSION));
			} 
			else 
			{
				$this->Session->setFlash('The state could not be updated. Please, try again.', 'error');
			}
		} 
		else 
		{
			$this->request->data = $this->State->find('first', array('conditions' => array('State.id' => $id)));
		}
    }

    public function admin_deleteState($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('State');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{
			$this->State->id = $id;
			if (!$this->State->exists()) 
			{
				throw new NotFoundException('Invalid State');
			}
		   if ($this->State->saveField('is_deleted',BOOL_TRUE)) 
		   {
				$this->State->saveField('is_active',BOOL_FALSE);
				$this->Session->setFlash('State deleted','success');	
				 return $this->redirect($this->referer());
		   }
		
	        $this->Session->setFlash('State was not deleted', 'error');
    	     return $this->redirect($this->referer());
		} 
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	

	
	public function admin_villagesList() 
	{	
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Village');
				
		
		if(isset($this->request->data['Village']))
		{					
			$this->Session->write('VillageSearch',$this->request->data['Village']);
		}
		else
		{	
			$this->request->data['Village']=$this->Session->read('VillageSearch');		
		}		
		if(isset($this->request->data['Village']))				
		{			
			if(isset($this->request->data['Village']['name']) and !empty($this->request->data['Village']['name']))				
			{
				$cond['Village.name LIKE']="%".$this->request->data['Village']['name']."%";
			}				
		}			
				
		$conditions = array(
			'Village.id !=' => BOOL_FALSE,
			'Village.is_deleted' => BOOL_FALSE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Village' => array(
				'conditions' => $conditions,
				'order' => array('Village.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' =>2
		));
		$villages = $this->Paginator->paginate('Village');
		$this->set(compact('villages'));		
    }
	
	
	public function admin_resetVillageSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('VillageSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
    public function admin_addVillage() {
		$this->admin_check_login();				
		$this->loadModel('State');
		$this->loadModel('City');
		$this->loadModel('Taluka');
		$this->loadModel('Village');
        
		$cities=array();
		$talukas=array();		
		$this->set('cities',$cities);
		$this->set('talukas',$talukas);
		
		if ($this->request->is('post')) 
		{	
				
			$cities=$this->City->find('list',array('conditions'=>array('City.id !='=>BOOL_FALSE,'City.is_deleted'=>BOOL_FALSE,'City.is_district'=>BOOL_TRUE,'City.state_id'=>$this->request->data['Taluka']['City']['state_id']),'order'=>array('name'=>'ASC')));
			$this->set(compact('cities'));
			
			$talukas=$this->Taluka->find('list',array('conditions'=>array('Taluka.id !='=>BOOL_FALSE,'Taluka.is_deleted'=>BOOL_FALSE,'Taluka.city_id'=>$this->request->data['Taluka']['city_id'],)));
			$this->set(compact('talukas'));
					
				$this->Village->create();			
				if ($this->Village->save($this->request->data)) 
				{
					$this->Session->setFlash('The village has been saved', 'success');
					return $this->redirect(array('controller'=>'utilities','action' => 'villagesList','admin'=>true,'ext'=>URL_EXTENSION));
				} 
				else 
				{
					$this->Session->setFlash('The village could not be saved. Please, try again.', 'error');
				}
			
        }		
		
		$states=$this->State->find('list',array('conditions'=>array('State.id !='=>BOOL_FALSE,'State.is_deleted'=>BOOL_FALSE,'State.country_id'=>INDIA_COUNTRY_ID),'order'=>array('name'=>'ASC')));
		$this->set(compact('states'));		
    }


    public function admin_editVillage($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);		
		$this->loadModel('State');		
		$this->loadModel('City');		
		$this->loadModel('Taluka');		
		$this->loadModel('Village');
		
		$cities=array();
		$talukas=array();		
		$this->set('cities',$cities);
		$this->set('talukas',$talukas);
					
		if (!$this->Village->exists($id)) 
		{
            throw new NotFoundException('Invalid Village');
        }
        
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			$this->Village->id=$id;
			if ($this->Village->save($this->request->data)) 
			{
				$this->request->data = $this->Village->find('first', array('conditions' => array('Village.id' => $id),
				'recursive'=>2
				));
				
				$this->Session->setFlash('The village has been updated', 'success');
				return $this->redirect(array('controller'=>'utilities','action' => 'villagesList','admin'=>true,'ext'=>URL_EXTENSION));
			} 
			else 
			{
				$this->Session->setFlash('The village could not be updated. Please, try again.', 'error');
			}
		} 
		else 
		{
			$this->request->data = $this->Village->find('first', array('conditions' => array('Village.id' => $id),
			'recursive'=>2
			));		
			
		}
		
		$talukas=$this->Taluka->find('list',array('conditions'=>array('Taluka.id !='=>BOOL_FALSE,'Taluka.is_deleted'=>BOOL_FALSE,'Taluka.city_id'=>$this->request->data['Taluka']['city_id'])));
		$this->set(compact('talukas'));
		
		
		
		$cities=$this->City->find('list',array('conditions'=>array('City.id !='=>BOOL_FALSE,'City.is_deleted'=>BOOL_FALSE,'City.is_district'=>BOOL_TRUE,'City.state_id'=>$this->request->data['Taluka']['City']['state_id']),'order'=>array('name'=>'ASC')));	
		$this->set(compact('cities'));
		
		$states=$this->State->find('list',array('conditions'=>array('State.id !='=>BOOL_FALSE,'State.is_deleted'=>BOOL_FALSE,'State.country_id'=>INDIA_COUNTRY_ID),'order'=>array('name'=>'ASC')));
		$this->set(compact('states'));
		
		$this->request->data['Village']['state']=$this->request->data['Taluka']['City']['state_id'];
		
    }

    public function admin_deleteVillage($id = null) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('Village');
				
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{	
			$this->Village->id = $id;
			if (!$this->Village->exists()) 
			{
			throw new NotFoundException('Invalid Village');
			}
			if ($this->Village->saveField('is_deleted',BOOL_TRUE)) 
			{
			$this->Village->saveField('is_active',BOOL_FALSE);
			$this->Session->setFlash('Village deleted','success');
			return $this->redirect($this->referer());
			}
			
			$this->Session->setFlash('Village was not deleted', 'error');
			return $this->redirect($this->referer());
		}
		else
		{			
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}	
    }


	
	
	/*
	@ Mohammad Masood 
	@ Get List of States of selected country
	@ 25-05-2016
	*/
	public function getStates() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('State');
			$states = array();
			if (isset($this->request['data']['id'])) 
			{
				$states = $this->State->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'State.country_id' => $this->request['data']['id'],
					'State.is_deleted' => BOOL_FALSE,
					'State.is_active' => BOOL_TRUE,					
					),
					'order'=>array('State.name'=>'ASC')
				));
				$str='<option value="">Select State</option>';
				foreach($states as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}
	/*
	@ Mohammad Masood 
	@ Get List of Districts on selecting state
	@ 25-05-2016
	*/
	public function getDistricts() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('City');
			$cities = array();
			if (isset($this->request['data']['id'])) 
			{
				$cities = $this->City->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'City.state_id' => $this->request['data']['id'],
					'City.is_district' => BOOL_TRUE,		
					'City.is_deleted' => BOOL_FALSE,
					'City.is_active' => BOOL_TRUE,					
					),
					'order'=>array('City.name'=>'ASC')
				));
				$str='<option value="">Select District</option>';
				foreach($cities as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}	
	
	/*
	@ Mohammad Masood 
	@ Get List of Cities on selecting state
	@ 25-05-2016
	*/
	public function getCities() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('City');
			$cities = array();
			if (isset($this->request['data']['id'])) 
			{
				$cities = $this->City->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'City.state_id' => $this->request['data']['id'],
					'City.is_deleted' => BOOL_FALSE,
					'City.is_active' => BOOL_TRUE,					
					),
					'order'=>array('City.name'=>'ASC')
				));
				
				$str='<option value="">Select City</option>';
				foreach($cities as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
				
			}
			
		}	
		
	}	
	
	/*
	@ Mohammad Masood 
	@ Get List of Taluks on selecting Cities
	@ 25-05-2016
	*/
	public function getTalukas() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('Taluka');
			$talukas = array();
			if (isset($this->request['data']['id'])) 
			{
				$talukas = $this->Taluka->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'Taluka.city_id' => $this->request['data']['id'],
					'Taluka.is_deleted' => BOOL_FALSE,
					'Taluka.is_active' => BOOL_TRUE,					
					),
					'order'=>array('Taluka.name'=>'ASC')
				));
			
				$str='<option value="">Select Taluka</option>';
				foreach($talukas as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}
	
	/*
	@ Mohammad Masood 
	@ Get List of Villages on selecting Taluks
	@ 25-05-2016
	*/
	public function getVillages() {
	
		$this->autoRender = FALSE;
        $this->layout = 'ajax';
        if ($this->request->is('ajax')) 
		{		
			$this->loadModel('Village');
			$villages = array();
			if (isset($this->request['data']['id'])) 
			{
				$villages = $this->Village->find('list', array(
					'fields' => array('id','name'),
					'conditions' => array(
					'Village.taluka_id' => $this->request['data']['id'],
					'Village.is_deleted' => BOOL_FALSE,
					'Village.is_active' => BOOL_TRUE,					
					),
					'order'=>array('Village.name'=>'ASC')
				));
				$str='<option value="">Select Village/Town</option>';
				foreach($villages as $k=>$v)
				{
					$str.='<option value="'.$k.'">'.$v.'</option>';
				}
				header('Content-Type: application/json');
				echo json_encode(array('data'=>$str));
				exit();
			}
			
		}	
		
	}
	
	public function admin_resetNewsSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('NewsSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_addNews() 
	{
		$this->admin_check_login();				
		$this->loadModel('News');		
        
		if ($this->request->is('post')) 
		{	
			if(!empty($this->request->data['News']['image']['name']))
			{			
				$this->Img = $this->Components->load('Img');			
				$ext = $this->Img->ext($this->request->data['News']['image']['name']);		
				$newName = strtotime("now").$rnd = rand(5, 15);				
				$origFile = $newName . '.' . $ext;				
				$dst = $newName .  '.jpg';	
				$targetdir = WWW_ROOT . 'images/news';			
				
				$allowed_exts=array('jpg','jpeg','png','gif','bmp');				
				
				if(in_array($ext,$allowed_exts))
				{
				
					$upload = $this->Img->upload($this->request->data['News']['image']['tmp_name'], $targetdir, $origFile);				
					
					if($upload == 'Success') 
					{	
						$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 	'images/news/thumbs/', $dst, 400, 400, 1, 0);
						$this->request->data['News']['image'] = $dst;
					}
					else 
					{
						$this->request->data['News']['image'] = '';
					}	
				}
				else{					
					$this->request->data['News']['image'] = '';	
					$this->Session->setFlash("You are trying to upload invalid file type . Only images with jpg,jpeg,gif,bmp extensions  are allowed","error");
					return $this->redirect($this->referer());
				}				
				
			}
			else 
			{
				$this->request->data['News']['image'] = '';
			}
			
            $this->News->create();			
            if ($this->News->save($this->request->data)) 
			{
                $this->Session->setFlash('The news has been saved', 'success');
                return $this->redirect(array('controller'=>'utilities','action' => 'newsList','admin'=>true,'ext'=>URL_EXTENSION));
            } 
			else 
			{
                $this->Session->setFlash('The news could not be saved. Please, try again.', 'error');
            }
        }				
    }
	
	public function admin_editNews($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);				
		$this->loadModel('News');		
		
		if (!$this->News->exists($id)) 
		{
            throw new NotFoundException('Invalid News');
        }
        
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			if(!empty($this->request->data['News']['image']['name']))
			{			
				$this->Img = $this->Components->load('Img');			
				$ext = $this->Img->ext($this->request->data['News']['image']['name']);		
				$newName = strtotime("now").$rnd = rand(5, 15);				
				$origFile = $newName . '.' . $ext;				
				$dst = $newName .  '.jpg';	
				$targetdir = WWW_ROOT . 'images/news';			
				
				$allowed_exts=array('jpg','jpeg','png','gif','bmp');				
				
				if(in_array($ext,$allowed_exts))
				{
				
					$upload = $this->Img->upload($this->request->data['News']['image']['tmp_name'], $targetdir, $origFile);				
					
					if($upload == 'Success') 
					{	
						$this->Img->resampleGD($targetdir . DS . $origFile, WWW_ROOT . 	'images/news/thumbs/', $dst, 400, 400, 1, 0);
						$this->request->data['News']['image'] = $dst;
					}
					else 
					{
						$this->request->data['News']['image'] = '';
					}	
				}
				else{					
						$this->request->data['News']['image'] = '';	
						$this->Session->setFlash("You are trying to upload invalid file type . Only images with jpg,jpeg,gif,bmp extensions  are allowed","error");
						return $this->redirect($this->referer());
				}				
				
			}
			else 
			{
				unset($this->request->data['News']['image']);
			}
			$this->News->id=$id;
			if ($this->News->save($this->request->data)) 
			{
				$this->Session->setFlash('News has been updated', 'success');
				return $this->redirect(array('controller'=>'utilities','action' => 'newsList','admin'=>true,'ext'=>URL_EXTENSION));
			} 
			else 
			{
				$this->Session->setFlash('News could not be updated. Please, try again.', 'error');
			}
		} 
		else 
		{
			$this->request->data = $this->News->find('first', array('conditions' => array('News.id' => $id)));
		}
    }
	
	
	 public function admin_deleteNews($id = null) 
	 {
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('News');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{		
			$this->News->id = $id;
			if (!$this->News->exists()) 
			{
			throw new NotFoundException('Invalid News');
			}
			if ($this->News->saveField('is_deleted',BOOL_TRUE)) 
			{
			$this->News->saveField('is_active',BOOL_FALSE);
			$this->Session->setFlash('News deleted','success');
			return $this->redirect($this->referer());
			}
			
			$this->Session->setFlash('Error! News was not deleted', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}	
    }
	
	public function admin_toggleNewsStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('News');		
        $this->News->id = $id;
		
        if (!$this->News->exists()) 
		{
            throw new NotFoundException('Invalid News');
        }
		
		if($action=='activate')
		{
			$value=BOOL_TRUE;
			$msg='Activated';
		}
		if($action=='deactivate')
		{
			$value=BOOL_FALSE;
			$msg='DeActivated';
		}
       if ($this->News->saveField('is_active',$value)) 
	   {	   		
            $this->Session->setFlash('News '.$msg.'','success');
            return $this->redirect($this->referer());
       }
		
        $this->Session->setFlash('News was not '.$msg.'', 'error');
        return $this->redirect($this->referer());

	}
	////////////////News Management Ends//////////////
	
	/////////////////Faqs Management //////////////////
	public function admin_faqsList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Faq');
		
		if(isset($this->request->data['Faq']))
		{					
			$this->Session->write('FaqSearch',$this->request->data['Faq']);
		}
		else
		{	
			$this->request->data['Faq']=$this->Session->read('FaqSearch');		
		}		
		if(isset($this->request->data['Faq']))				
		{			
			if(isset($this->request->data['Faq']['question']) and !empty($this->request->data['Faq']['question']))				
			{
				$cond['Faq.question LIKE']="%".$this->request->data['Faq']['question']."%";
			}				
		}		
		
		$conditions = array(
			'Faq.id !=' => BOOL_FALSE,
			'Faq.is_deleted' => BOOL_FALSE
		);
		$conditions=array_merge($conditions,$cond);		
		
		$this->Paginator->settings = array(
			'Faq' => array(
				'conditions' => $conditions,
				'order' => array('Faq.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' =>-1
		));
		$faqs_list = $this->Paginator->paginate('Faq');
		$this->set(compact('faqs_list'));	
    }	
	
	public function admin_resetFaqSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('FaqSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	public function admin_addFaq() 
	{
		$this->admin_check_login();				
		$this->loadModel('Faq');		
        
		if ($this->request->is('post')) 
		{				
            $this->Faq->create();			
            if ($this->Faq->save($this->request->data)) 
			{
                $this->Session->setFlash('The FAQ has been saved', 'success');
                return $this->redirect(array('controller'=>'utilities','action' => 'faqsList','admin'=>true,'ext'=>URL_EXTENSION));
            } 
			else 
			{
                $this->Session->setFlash('The FAQ could not be saved. Please, try again.', 'error');
            }
        }				
    }
	
	public function admin_editFaq($id = null) 
	{
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);				
		$this->loadModel('Faq');		
		
		if (!$this->Faq->exists($id)) 
		{
            throw new NotFoundException('Invalid FAQ');
        }
        
		if ($this->request->is('post') || $this->request->is('put')) 
		{			
			$this->Faq->id=$id;
			if ($this->Faq->save($this->request->data)) 
			{
				$this->Session->setFlash('FAQ has been updated', 'success');
				return $this->redirect(array('controller'=>'utilities','action' => 'faqsList','admin'=>true,'ext'=>URL_EXTENSION));
			} 
			else 
			{
				$this->Session->setFlash('FAQ could not be updated. Please, try again.', 'error');
			}
		} 
		else 
		{
			$this->request->data = $this->Faq->find('first', array('conditions' => array('Faq.id' => $id)));
		}
    }
	
	
	 public function admin_deleteFaq($id = null) 
	 {
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('Faq');
		
		if ($this->Access->checkPermission(array(DELETE_PERMISSION_ID))) 
		{		
			$this->Faq->id = $id;
			if (!$this->Faq->exists()) 
			{
			throw new NotFoundException('Invalid FAQ');
			}
			if ($this->Faq->saveField('is_deleted',BOOL_TRUE)) 
			{
			$this->Faq->saveField('is_active',BOOL_FALSE);
			$this->Session->setFlash('FAQ deleted','success');
			return $this->redirect($this->referer());
			}
			
			$this->Session->setFlash('Error! FAQ was not deleted', 'error');
			return $this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}	
    }
	
	public function admin_toggleFaqStatus($id=NULL,$action=NULL) 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;		
		$this->admin_check_login();
		$id = $this->Encryption->decrypt($id);        
		$this->loadModel('Faq');		
        $this->Faq->id = $id;
		
        if (!$this->Faq->exists()) 
		{
            throw new NotFoundException('Invalid FAQ');
        }
		
		if($action=='activate')
		{
			$value=BOOL_TRUE;
			$msg='Activated';
		}
		if($action=='deactivate')
		{
			$value=BOOL_FALSE;
			$msg='DeActivated';
		}
       if ($this->Faq->saveField('is_active',$value)) 
	   {	   		
            $this->Session->setFlash('FAQ '.$msg.'','success');
            return $this->redirect($this->referer());
       }
		
        $this->Session->setFlash('FAQ was not '.$msg.'', 'error');
        return $this->redirect($this->referer());

	}
	////////////////Faqs Management Ends//////////////
	/*
	Amit Sahu
	28.01.17
	Category List
	*/
	public function admin_categoryList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Category');
		$this->loadModel('DiscountLevel');
		
		$catList=$this->Category->getCategoryList();
		$this->set(compact('catList'));
		
		$discountLevels=$this->DiscountLevel->getDiscountLevelList();
		$this->set(compact('discountLevels'));
		
		if(isset($this->request->data['Category']))
		{					
		$this->Session->write('CategorySearch',$this->request->data['Category']);
		}
		else
		{	
		$this->request->data['Category']=$this->Session->read('CategorySearch');
		
		}		
		if(isset($this->request->data['Category']))				
		{			
			if(isset($this->request->data['Category']['name']) and !empty($this->request->data['Category']['name']))				
			{
				$cond['Category.name LIKE']="%".$this->request->data['Category']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Category.id !=' => BOOL_FALSE,
			'Category.is_deleted' => BOOL_FALSE,
			'Category.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Category' => array(
				'conditions' => $conditions,
				'order' => array('Category.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$categories = $this->Paginator->paginate('Category');
		$this->set(compact('categories'));		
		
		
    }
	/*
	Amit Sahu
	28.01.17
	Reset Category Search
	*/
	
	public function admin_resetCategorySearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('CategorySearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	28.01.17
	Publisher List
	*/
	public function admin_publisherList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Publisher');
		$this->loadModel('Category');
		$this->loadModel('City');		
		$this->loadModel('DiscountLevel');
		
		$cityList=$this->City->getCityList();
		$this->set(compact('cityList'));
		
		$catList=$this->Category->getCategoryList();
		$this->set(compact('catList'));
		
		$discountLevels=$this->DiscountLevel->getDiscountLevelList();
		$this->set(compact('discountLevels'));
		
		if(isset($this->request->data['Publisher']))
		{					
		$this->Session->write('PublisherSearch',$this->request->data['Publisher']);
		}
		else
		{	
		$this->request->data['Publisher']=$this->Session->read('PublisherSearch');
		
		}		
		if(isset($this->request->data['Publisher']))				
		{			
			if(isset($this->request->data['Publisher']['name']) and !empty($this->request->data['Publisher']['name']))				
			{
				$cond['Publisher.name LIKE']="%".$this->request->data['Publisher']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Publisher.id !=' => BOOL_FALSE,
			'Publisher.is_deleted' => BOOL_FALSE,
			'Publisher.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Publisher' => array(
				'conditions' => $conditions,
				'order' => array('Publisher.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$publishers = $this->Paginator->paginate('Publisher');
		$this->set(compact('publishers'));		
		
		
    }
	/*
	Amit Sahu
	28.01.17
	Reset Publisher Search
	*/
	
	public function admin_resetPublisherSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('PublisherSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	28.01.17
	Publisher List
	*/
	public function admin_authorList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Author');
		
		
		if(isset($this->request->data['Author']))
		{					
		$this->Session->write('AuthorSearch',$this->request->data['Author']);
		}
		else
		{	
		$this->request->data['Author']=$this->Session->read('AuthorSearch');
		
		}		
		if(isset($this->request->data['Author']))				
		{			
			if(isset($this->request->data['Author']['name']) and !empty($this->request->data['Author']['name']))				
			{
				$cond['Author.name LIKE']="%".$this->request->data['Author']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Author.id !=' => BOOL_FALSE,
			'Author.is_deleted' => BOOL_FALSE,
			'Author.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Author' => array(
				'conditions' => $conditions,
				'order' => array('Author.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$authors = $this->Paginator->paginate('Author');
		$this->set(compact('authors'));		
		
		
    }
	/*
	Amit Sahu
	28.01.17
	Reset Author Search
	*/
	
	public function admin_resetAuthorSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('AuthorSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
		/*
	Amit Sahu
	30.01.17
	Distributor List
	*/
	public function admin_distributorList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Distributor');
		$this->loadModel('City');
		$this->loadModel('State');
		
			$cityList=$this->City->getCityList();
		$this->set(compact('cityList'));
		
			$stateList=$this->State->getStateList();
		$this->set(compact('stateList'));
		
		if(isset($this->request->data['Distributor']))
		{					
		$this->Session->write('DistributorSearch',$this->request->data['Distributor']);
		}
		else
		{	
		$this->request->data['Distributor']=$this->Session->read('DistributorSearch');
		
		}		
		if(isset($this->request->data['Distributor']))				
		{			
			if(isset($this->request->data['Distributor']['name']) and !empty($this->request->data['Distributor']['name']))				
			{
				$cond['Distributor.name LIKE']="%".$this->request->data['Distributor']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Distributor.id !=' => BOOL_FALSE,
			'Distributor.is_deleted' => BOOL_FALSE,
			'Distributor.is_active' => BOOL_TRUE,
			'Distributor.user_profile_id' => $this->Session->read('Auth.User.user_profile_id')
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Distributor' => array(
				'conditions' => $conditions,
				'order' => array('Distributor.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$distributors = $this->Paginator->paginate('Distributor');
		$this->set(compact('distributors'));		
		
		
    }
	/*
	Amit Sahu
	29.01.17
	Reset Distributor Search
	*/
	
	public function admin_resetDistributorSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('DistributorSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
		/*
	Amit Sahu
	01.02.17
	Shop List
	*/
	public function admin_locationList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Location');
		
		
		if(isset($this->request->data['Location']))
		{					
		$this->Session->write('LocationSearch',$this->request->data['Location']);
		}
		else
		{	
		$this->request->data['Location']=$this->Session->read('LocationSearch');
		
		}		
		if(isset($this->request->data['Location']))				
		{			
			if(isset($this->request->data['Location']['name']) and !empty($this->request->data['Location']['name']))				
			{
				$cond['Location.name LIKE']="%".$this->request->data['Location']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Location.id !=' => BOOL_FALSE,
			'Location.is_deleted' => BOOL_FALSE,
			'Location.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Location' => array(
				'conditions' => $conditions,
				'order' => array('Location.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$locations = $this->Paginator->paginate('Location');
		$this->set(compact('locations'));		
		
		
    }
	/*
	Amit Sahu
	Add Shop (Master)
	01.02.17
	*/
public function addLocation()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Location');
		$this->admin_check_login();			
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->Location->create();			
				if ($this->Location->save($this->request->data)) 
				{					
						$id=$this->Location->getInsertID();
						if($this->request->data['Location']['location_type']=='1'){
								$location_type="Shop";	
						}
						else if($this->request->data['Location']['location_type']=='2'){
							$location_type="Godown";	
						}						
						$name=$this->request->data['Location']['name'];
						$code=$this->request->data['Location']['code'];
						$address=$this->request->data['Location']['address'];
						$contact=$this->request->data['Location']['contact'];
						
						
					echo json_encode(array('status'=>'1000','message'=>'Location added successfully', 'id'=>$id,'location_type'=>$location_type,'name'=>$name,'address'=>$address,'contact'=>$contact,'code'=>$code));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Location could not be added'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
		/*
	Amit Sahu
	Edit Shop (Master)
	01.02.17
	*/
public function editLocation()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Location');
		
		$this->admin_check_login();				
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Location']['id'];
				if(!empty($id))
					{
						if ($this->Location->save($this->request->data)) 
						{
							if($this->request->data['Location']['location_type']=='1'){
								$location_type="Shop";	
							}
							else if($this->request->data['Location']['location_type']=='2'){
								$location_type="Godown";	
							}
							
							$name=$this->request->data['Location']['name'];
							$address=$this->request->data['Location']['address'];
							$contact=$this->request->data['Location']['contact'];
							$code=$this->request->data['Location']['code'];
								
							echo json_encode(array('status'=>'1000','message'=>'Location edit successfully','id'=>$id,'location_type'=>$location_type,'name'=>$name,'address'=>$address,'contact'=>$contact,'code'=>$code));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Location could not be edit'));
						}
					}
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	/*
	Amit Sahu
	01.02.17
	Delete Location
	*/
	
	public function deleteLocation() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Location');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Location->id =$id;
				if (!$this->Location->exists()) 
				{
					throw new NotFoundException('Invalid Location');
				}
				
						
							   if ($this->Location->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Location->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Location Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Location could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	reset shop search
	01.02.17
	*/
	public function admin_resetLocationSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('LocationSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	02.02.17
	Legder List
	*/
	public function admin_ledgerList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Ledger');
		$this->loadModel('Group');
		
		if(isset($this->request->data['Ledger']))
		{					
		$this->Session->write('LedgerSearch',$this->request->data['Ledger']);
		}
		else
		{	
		$this->request->data['Ledger']=$this->Session->read('LedgerSearch');
		
		}		
		if(isset($this->request->data['Ledger']))				
		{			
			if(isset($this->request->data['Ledger']['name']) and !empty($this->request->data['Ledger']['name']))				
			{
				$cond['Ledger.name LIKE']="%".$this->request->data['Ledger']['name']."%";
			}	
			
		}		
		
						
		$conditions = array(
			'Ledger.id !=' => BOOL_FALSE,
			'Ledger.is_deleted' => BOOL_FALSE,
			'Ledger.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Ledger' => array(
				'conditions' => $conditions,
				'order' => array('Ledger.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$ledgers = $this->Paginator->paginate('Ledger');
		$this->set(compact('ledgers'));		
		
		
    }
/*
	Amit Sahu
	01.02.17
	Delete Ledger
	*/
	
	public function deleteLedger() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Ledger');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Ledger->id =$id;
				if (!$this->Ledger->exists()) 
				{
					throw new NotFoundException('Invalid Ledger');
				}
				
						
							   if ($this->Ledger->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Ledger->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Ledger Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Ledger could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	reset ledger search
	01.02.17
	*/
	public function admin_resetLedgerSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('LedgerSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	02.02.17
	Bank Account List
	*/
	public function admin_bankAccountList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('BankAccount');
		
		
		if(isset($this->request->data['BankAccount']))
		{					
		$this->Session->write('BankAccountSearch',$this->request->data['BankAccount']);
		}
		else
		{	
		$this->request->data['BankAccount']=$this->Session->read('BankAccountSearch');
		
		}		
		if(isset($this->request->data['BankAccount']))				
		{			
			if(isset($this->request->data['BankAccount']['name']) and !empty($this->request->data['BankAccount']['name']))				
			{
				$cond['BankAccount.name LIKE']="%".$this->request->data['BankAccount']['name']."%";
			}	
			
		}		
		
						
		$conditions = array(
			'BankAccount.id !=' => BOOL_FALSE,
			'BankAccount.is_deleted' => BOOL_FALSE,
			'BankAccount.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'BankAccount' => array(
				'conditions' => $conditions,
				'order' => array('BankAccount.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$accounts = $this->Paginator->paginate('BankAccount');
		$this->set(compact('accounts'));		
		
		
    }
	/*
	Amit Sahu
	Add Bank Account
	14.02.17
	*/
public function admin_addBankAccount()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('BankAccount');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->BankAccount->create();			
				if ($this->BankAccount->save($this->request->data)) 
				{
					
						$id=$this->BankAccount->getInsertID();
						$name=$this->request->data['BankAccount']['name'];
						$bank_name=$this->request->data['BankAccount']['bank_name'];
						$ac_no=$this->request->data['BankAccount']['ac_no'];
						$branch=$this->request->data['BankAccount']['branch'];
						$ifsc=$this->request->data['BankAccount']['ifsc'];
						$micr=$this->request->data['BankAccount']['micr'];
						$cropnBlnc=$this->request->data['BankAccount']['opening_balance'];
						$debopnBlnc=$this->request->data['BankAccount']['debitop_blnc'];
						
					echo json_encode(array('status'=>'1000','message'=>'Bank Account added successfully', 'id'=>$id,'name'=>$name,'bank_name'=>$bank_name,'ac_no'=>$ac_no,'branch'=>$branch,'ifsc'=>$ifsc,'micr'=>$micr,'opening_balance'=>$cropnBlnc,'debitop_blnc'=>$debopnBlnc));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'Bank Account could not be added'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	/*
	Amit Sahu
	Edit Bank Account  (Master)
	14.02.17
	*/
		public function Admin_editBankAccount()
			{		
				$this->autoRender = FALSE;
				$this->layout = 'ajax';
				$this->loadModel('BankAccount');
				
							
				
				if ($this->request->is('ajax')) 
					{
						$id=$this->request->data['BankAccount']['id'];
						if(!empty($id))
							{
								if ($this->BankAccount->save($this->request->data)) 
								{
								$name=$this->request->data['BankAccount']['name'];
								$bank_name=$this->request->data['BankAccount']['bank_name'];
								$ac_no=$this->request->data['BankAccount']['ac_no'];
								$branch=$this->request->data['BankAccount']['branch'];
								$ifsc=$this->request->data['BankAccount']['ifsc'];
								$micr=$this->request->data['BankAccount']['micr'];
								$cropnBlnc=$this->request->data['BankAccount']['opening_balance'];
								$debopnBlnc=$this->request->data['BankAccount']['debitop_blnc'];
								
							echo json_encode(array('status'=>'1000','message'=>'Bank Account edit successfully', 'id'=>$id,'name'=>$name,'bank_name'=>$bank_name,'ac_no'=>$ac_no,'branch'=>$branch,'ifsc'=>$ifsc,'micr'=>$micr,'opening_balance'=>$cropnBlnc,'debitop_blnc'=>$debopnBlnc));
								} 
								else 
								{
									echo json_encode(array('status'=>'1001','message'=>'Bank account could not be added'));
								}
							}
					}
				
				else
				{
					$this->Session->setFlash("Unauthorized access", 'error');
					$this->redirect($this->referer());
				}
					
			}
	/*
	Amit Sahu
	Delete bank accounts
	14.02.17
	*/
	public function admin_deleteBankAccount() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('BankAccount');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->BankAccount->id =$id;
				if (!$this->BankAccount->exists()) 
				{
					throw new NotFoundException('Invalid Bank Account');
				}
				
						
							   if ($this->BankAccount->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->BankAccount->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Bank Account Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Bank Account could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	reset bank account search
	14.02.17
	*/
	public function admin_resetbankAccountSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('BankAccountSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	02.02.17
	Discount Level List
	*/
	public function admin_discountLevelList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('DiscountLevel');
		
		
		if(isset($this->request->data['DiscountLevel']))
		{					
		$this->Session->write('DiscountLevelSearch',$this->request->data['DiscountLevel']);
		}
		else
		{	
		$this->request->data['DiscountLevel']=$this->Session->read('DiscountLevelSearch');
		
		}		
		if(isset($this->request->data['DiscountLevel']))				
		{			
			if(isset($this->request->data['DiscountLevel']['name']) and !empty($this->request->data['DiscountLevel']['name']))				
			{
				$cond['DiscountLevel.name LIKE']="%".$this->request->data['DiscountLevel']['name']."%";
			}	
			
		}		
		
						
		$conditions = array(
			'DiscountLevel.id !=' => BOOL_FALSE,
			'DiscountLevel.is_deleted' => BOOL_FALSE,
			'DiscountLevel.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'DiscountLevel' => array(
				'conditions' => $conditions,
				'order' => array('DiscountLevel.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$discounts = $this->Paginator->paginate('DiscountLevel');
		$this->set(compact('discounts'));		
		
		
    }
	
	/*
	Amit Sahu
	Add Discount Level
	14.02.17
	*/
public function admin_addDiscountLevel()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('DiscountLevel');
				
		if ($this->request->is('ajax'))
		{	
			$this->request->data['DiscountLevel']['offer_from'] = !empty($this->request->data['DiscountLevel']['offer_from'])?date("Y-m-d H:i:s",strtotime($this->request->data['DiscountLevel']['offer_from'])):"";
				$this->request->data['DiscountLevel']['offer_upto'] = !empty($this->request->data['DiscountLevel']['offer_upto'])?date("Y-m-d H:i:s",strtotime($this->request->data['DiscountLevel']['offer_upto'])):"";

			$this->DiscountLevel->create();
			if ($this->DiscountLevel->save($this->request->data))
			{	
				$this->request->data['DiscountLevel']['offer_from'] = !empty($this->request->data['DiscountLevel']['offer_from'])?date("d-m-Y",strtotime($this->request->data['DiscountLevel']['offer_from'])):"";
			$this->request->data['DiscountLevel']['offer_upto'] = !empty($this->request->data['DiscountLevel']['offer_upto'])?date("d-m-Y",strtotime($this->request->data['DiscountLevel']['offer_upto'])):"";

				$id=$this->DiscountLevel->getInsertID();
				$name=$this->request->data['DiscountLevel']['name'];
				$discount=$this->request->data['DiscountLevel']['discount'];
				$is_offer=$this->request->data['DiscountLevel']['is_offer'];
				$offer_from=$this->request->data['DiscountLevel']['offer_from'];
				$offer_upto=$this->request->data['DiscountLevel']['offer_upto'];
				
				echo json_encode(array('status'=>'1000','message'=>'Discount Level added successfully', 'id'=>$id,'name'=>$name,'discount'=>$discount,'is_offer'=>$is_offer,'offer_from'=>$offer_from,'offer_upto'=>$offer_upto));
			} 
			else 
			{
				echo json_encode(array('status'=>'1001','message'=>'Discount Level could not be added'));
			}
			
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	/*
	Amit Sahu
	Edit Bank Account  (Master)
	14.02.17
	*/
public function admin_editDiscountLevel()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('DiscountLevel');
		
		if ($this->request->is('ajax')){
				$id=$this->request->data['DiscountLevel']['id'];
				$this->request->data['DiscountLevel']['offer_from'] = !empty($this->request->data['DiscountLevel']['offer_from'])?date("Y-m-d H:i:s",strtotime($this->request->data['DiscountLevel']['offer_from'])):"";
				$this->request->data['DiscountLevel']['offer_upto'] = !empty($this->request->data['DiscountLevel']['offer_upto'])?date("Y-m-d H:i:s",strtotime($this->request->data['DiscountLevel']['offer_upto'])):"";
				
				if(!empty($id))
					{
						if ($this->DiscountLevel->save($this->request->data))
						{
						
						$this->request->data['DiscountLevel']['offer_from'] = !empty($this->request->data['DiscountLevel']['offer_from'])?date("d-m-Y",strtotime($this->request->data['DiscountLevel']['offer_from'])):"";
			$this->request->data['DiscountLevel']['offer_upto'] = !empty($this->request->data['DiscountLevel']['offer_upto'])?date("d-m-Y",strtotime($this->request->data['DiscountLevel']['offer_upto'])):"";
				
						$name=$this->request->data['DiscountLevel']['name'];
						$discount=$this->request->data['DiscountLevel']['discount'];
						$is_offer=$this->request->data['DiscountLevel']['is_offer'];
						$offer_from=$this->request->data['DiscountLevel']['offer_from'];
						$offer_upto=$this->request->data['DiscountLevel']['offer_upto'];
						
						echo json_encode(array('status'=>'1000','message'=>'Discount level edit successfully', 'id'=>$id,'name'=>$name,'discount'=>$discount,'is_offer'=>$is_offer,'offer_from'=>$offer_from,'offer_upto'=>$offer_upto));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Discount level could not be added'));
						}
					}
					
					
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	/*
	Amit Sahu
	Delete discoount level
	14.02.17
	*/
	public function admin_deleteDiscountLevel() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('DiscountLevel');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->DiscountLevel->id =$id;
				if (!$this->DiscountLevel->exists()) 
				{
					throw new NotFoundException('Invalid Discount Level');
				}
				
						
							   if ($this->DiscountLevel->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->DiscountLevel->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Discount Level Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Discount Level could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	reset discount level search
	14.02.17
	*/
	public function admin_resetdiscountlevelSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('DiscountLevelSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	20.02.17
	Ocation List
	*/
	public function admin_ocationList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Ocation');
		
		if(isset($this->request->data['Ocation']))
		{					
		$this->Session->write('OcationSearch',$this->request->data['Ocation']);
		}
		else
		{	
		$this->request->data['Ocation']=$this->Session->read('OcationSearch');
		
		}		
		if(isset($this->request->data['Ocation']))				
		{			
			if(isset($this->request->data['Ocation']['name']) and !empty($this->request->data['Ocation']['name']))				
			{
				$cond['Ocation.name LIKE']="%".$this->request->data['Ocation']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Ocation.id !=' => BOOL_FALSE,
			'Ocation.is_deleted' => BOOL_FALSE,
			'Ocation.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Ocation' => array(
				'conditions' => $conditions,
				'order' => array('Ocation.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$ocations = $this->Paginator->paginate('Ocation');
		$this->set(compact('ocations'));		
		
		
    }
	/*
	Amit Sahu
	Edit Ocation  (Master)
	20.02.17
	*/
public function admin_editOcation()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Ocation');
		
					
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Ocation']['id'];
				if(!empty($id))
					{
						if ($this->Ocation->save($this->request->data)) 
						{
						$name=$this->request->data['Ocation']['name'];						
						
					echo json_encode(array('status'=>'1000','message'=>'Ocation edit successfully', 'id'=>$id,'name'=>$name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Ocation could not be added'));
						}
					}
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	/*
	Amit Sahu
	Delete Ocation
	20.02.17
	*/
	public function admin_deleteOcation() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Ocation');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Ocation->id =$id;
				if (!$this->Ocation->exists()) 
				{
					throw new NotFoundException('Invalid Ocation');
				}
				
						
							   if ($this->Ocation->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Ocation->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Ocation Deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Ocation could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	/*
	Amit Sahu
	reset ocation search
	20.02.17
	*/
	public function admin_resetOcationSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('OcationSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	
	/*
	Amit Sahu
	23.02.17
	Group List
	*/
	public function admin_groupList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Group');
		
		if(isset($this->request->data['Group']))
		{					
		$this->Session->write('GroupSearch',$this->request->data['Group']);
		}
		else
		{	
		$this->request->data['Group']=$this->Session->read('GroupSearch');
		
		}		
		if(isset($this->request->data['Group']))				
		{			
			if(isset($this->request->data['Group']['name']) and !empty($this->request->data['Group']['name']))				
			{
				$cond['Group.name LIKE']="%".$this->request->data['Group']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Group.id !=' => BOOL_FALSE,
			'Group.is_deleted' => BOOL_FALSE,
			'Group.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Group' => array(
				'conditions' => $conditions,
				'order' => array('Group.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$groups = $this->Paginator->paginate('Group');
		$this->set(compact('groups'));		
		
		
    }
	public function admin_resetGroupSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('GroupSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	Edit Group  (Master)
	23.02.17
	*/
public function admin_editGroup()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Group');
		
					
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Group']['id'];
				if(!empty($id))
					{
						if ($this->Group->save($this->request->data)) 
						{
						$name=$this->request->data['Group']['name'];						
						
					echo json_encode(array('status'=>'1000','message'=>'Group edit successfully', 'id'=>$id,'name'=>$name));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Group could not be added'));
						}
					}
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	/*
	Amit Sahu
	Delete Group
	24.02.17
	*/
	public function admin_deleteGroup() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Group');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Group->id =$id;
				if (!$this->Group->exists()) 
				{
					throw new NotFoundException('Invalid Group');
				}
									
							   if ($this->Group->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Group->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Group deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Group could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	/*
	Rahul Katole
	Creditor List
	24.03.17
	*/
	
	public function admin_creditor()
	{
		  $cond=array();
		$this->admin_check_login();
		$this->loadModel('Creditor');
		$this->loadModel('Category');
		$this->loadModel('City');		
		$this->loadModel('DiscountLevel');
		
		$cityList=$this->City->getCityList();
		$this->set(compact('cityList'));
		
		$catList=$this->Category->getCategoryList();
		$this->set(compact('catList'));
		
		$discountLevels=$this->DiscountLevel->getDiscountLevelList();
		$this->set(compact('discountLevels'));
		
		if(isset($this->request->data['Creditor']))
		{					
		$this->Session->write('PublisherSearch',$this->request->data['Creditor']);
		}
		else
		{	
		$this->request->data['Creditor']=$this->Session->read('PublisherSearch');
		
		}		
		if(isset($this->request->data['Creditor']))				
		{			
			if(isset($this->request->data['Creditor']['name']) and !empty($this->request->data['Creditor']['name']))				
			{
				$cond['Creditor.name LIKE']="%".$this->request->data['Creditor']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Creditor.id !=' => BOOL_FALSE,
			'Creditor.is_deleted' => BOOL_FALSE,
			'Creditor.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Creditor' => array(
				'conditions' => $conditions,
				'order' => array('Creditor.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$creditors = $this->Paginator->paginate('Creditor');
		$this->set(compact('creditors'));
		
	}
	/*
	Rahul Katole
	Creditor List
	24.03.17
	*/
	public function admin_creditorList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Creditor');
		$this->loadModel('Category');
		$this->loadModel('City');		
		$this->loadModel('DiscountLevel');
		
		$cityList=$this->City->getCityList();
		$this->set(compact('cityList'));
		
		$catList=$this->Category->getCategoryList();
		$this->set(compact('catList'));
		
		$discountLevels=$this->DiscountLevel->getDiscountLevelList();
		$this->set(compact('discountLevels'));
		
		if(isset($this->request->data['Creditor']))
		{					
		$this->Session->write('PublisherSearch',$this->request->data['Creditor']);
		}
		else
		{	
		$this->request->data['Creditor']=$this->Session->read('PublisherSearch');
		
		}		
		if(isset($this->request->data['Creditor']))				
		{			
			if(isset($this->request->data['Creditor']['name']) and !empty($this->request->data['Creditor']['name']))				
			{
				$cond['Creditor.name LIKE']="%".$this->request->data['Creditor']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Creditor.id !=' => BOOL_FALSE,
			'Creditor.is_deleted' => BOOL_FALSE,
			'Creditor.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Creditor' => array(
				'conditions' => $conditions,
				'order' => array('Creditor.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$creditors = $this->Paginator->paginate('Creditor');
		$this->set(compact('creditors'));		
		
		
    }
	/*
	Rahul Katole
	Creditor Search
	24.03.17
	*/
	
	public function admin_resetcreditorSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('PublisherSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	
	/*
	Rahul Katole
	Designation List
	24.03.17
	*/
	public function admin_designationList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Designation');
		
		
		if(isset($this->request->data['Designation']))
		{					
		$this->Session->write('PublisherSearch',$this->request->data['Designation']);
		}
		else
		{	
		$this->request->data['Designation']=$this->Session->read('PublisherSearch');
		
		}		
		if(isset($this->request->data['Designation']))				
		{			
			if(isset($this->request->data['Designation']['name']) and !empty($this->request->data['Designation']['name']))				
			{
				$cond['Designation.name LIKE']="%".$this->request->data['Designation']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'Designation.id !=' => BOOL_FALSE,
			'Designation.is_deleted' => BOOL_FALSE,
			'Designation.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Designation' => array(
				'conditions' => $conditions,
				'order' => array('Designation.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$designations = $this->Paginator->paginate('Designation');
		$this->set(compact('designations'));		
		
		
    }
	
	public function admin_resetdesignationSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('PublisherSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	12.04.17
	transport list
	*/
	  public function admin_transportList() 
	{	
		
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('Transport');
		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{	
			if(isset($this->request->data['Transport']))
			{					
				$this->Session->write('TransportSearch',$this->request->data['Transport']);
			}
			else
			{	
				$this->request->data['Transport']=$this->Session->read('TransportSearch');		
			}
			if(isset($this->request->data['Transport']))				
			{			
				if(isset($this->request->data['Transport']['name']) and !empty($this->request->data['Transport']['name']))				
				{
					$cond['Transport.name LIKE']="%".$this->request->data['Transport']['name']."%";
				}				
			}
			
			$conditions = array(
			'Transport.id !=' => BOOL_FALSE,
			'Transport.is_deleted' => BOOL_FALSE,
			'Transport.is_active' => BOOL_TRUE
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'Transport' => array(
				'conditions' => $conditions,
				'order' => array('Transport.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => -1
		));
		$transports = $this->Paginator->paginate('Transport');
		$this->set(compact('transports'));		
		
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}	
					
		
		
    }
	/*
	Neha Bastawale
	13.04.17
	edit transport list
	*/
	
	public function admin_editTransportList()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('Transport');
		
					
		
		if ($this->request->is('ajax')) 
			{
				$id=$this->request->data['Transport']['id'];
				if(!empty($id))
					{
						if ($this->Transport->save($this->request->data)) 
						{
						$name=$this->request->data['Transport']['name'];
						$address=$this->request->data['Transport']['address'];
						$contact=$this->request->data['Transport']['contact'];
                      
					echo json_encode(array('status'=>'1000','message'=>'Transport edit successfully', 'id'=>$id,'name'=>$name,'address'=>$address,'contact'=>$contact));
						} 
						else 
						{
							echo json_encode(array('status'=>'1001','message'=>'Transport could not be added'));
						}
					}
			}
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	
	   public function admin_deleteTransport() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		
		$this->loadModel('Transport');
		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->Transport->id =$id;
				if (!$this->Transport->exists()) 
				{
					throw new NotFoundException('Invalid Transport');
				}
									
							   if ($this->Transport->saveField('is_deleted',BOOL_TRUE)) 
							   {
									$this->Transport->saveField('is_active',BOOL_FALSE);
								echo json_encode(array('status'=>'1000','id'=>$id,'message'=>'Transport deleted successfully'));
							   }else
							   {
								   echo json_encode(array('status'=>'1001','message'=>'Transport could not be Deleted'));
							   }
						
		
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
	public function admin_resetTransportSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('TransportSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	21.07.18
	Gst Master List
	*/
	public function admin_gstMasterList() 
	{
		$cond=array();
		$this->admin_check_login();
		$this->loadModel('GstMaster');
		
		
		if(isset($this->request->data['GstMaster']))
		{					
		$this->Session->write('GstMasterSearch',$this->request->data['GstMaster']);
		}
		else
		{	
		$this->request->data['GstMaster']=$this->Session->read('GstMasterSearch');
		
		}		
		if(isset($this->request->data['GstMaster']))				
		{			
			if(isset($this->request->data['GstMaster']['name']) and !empty($this->request->data['GstMaster']['name']))				
			{
				$cond['GstMaster.name LIKE']="%".$this->request->data['GstMaster']['name']."%";
			}				
		}		
		
						
		$conditions = array(
			'GstMaster.id !=' => BOOL_FALSE,
			'GstMaster.is_deleted' => BOOL_FALSE,
			'GstMaster.user_profile_id' =>$this->Session->read('Auth.User.user_profile_id'),
		);
		
		$conditions=array_merge($conditions,$cond);
		
		$this->Paginator->settings = array(
			'GstMaster' => array(
				'conditions' => $conditions,
				'order' => array('GstMaster.id' => 'DESC'),
				'limit' => PAGINATION_LIMIT,
				'recursive' => 0
		));
		$gstslabs = $this->Paginator->paginate('GstMaster');
		$this->set(compact('gstslabs'));		
		
		
    }
	
	
	public function admin_resetGstMasterSearch() 
	{
		$this->autoRender = FALSE;
		$this->layout = FALSE;						
		$this->admin_check_login();		
		if ($this->Access->checkPermission(array(READ_PERMISSION_ID))) 
		{			
			$this->Session->delete('GstMasterSearch');
			$this->redirect($this->referer());	
			
		}
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}		
		
    }
	/*
	Amit Sahu
	Add gst slab
	23.07.18
	*/
	public function admin_addGstSlab()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('GstMaster');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				$this->GstMaster->create();	
				$this->request->data['GstMaster']['user_profile_id']=$this->Session->read('Auth.User.user_profile_id');
				if ($this->GstMaster->save($this->request->data['GstMaster'])) 
				{
					
						$id=$this->GstMaster->getInsertID();
						$name=$this->request->data['GstMaster']['name'];
						$gst_percentage=$this->request->data['GstMaster']['gst_percentage'];
						$sgst=$this->request->data['GstMaster']['sgst'];
						$cgst=$this->request->data['GstMaster']['cgst'];
						$igst=$this->request->data['GstMaster']['igst'];
					
						
					echo json_encode(array('status'=>'1000','message'=>'GST slab added successfully', 'id'=>$id,'name'=>$name,'gst_percentage'=>$gst_percentage,'sgst'=>$sgst,'cgst'=>$cgst,'igst'=>$igst));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'GST slab could not be added'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
	/*
	Amit Sahu
	Set Data for edit
	23.07.18*/
	public function admin_setDataForEditGstMaster() 
	{
		$this->autoRender = FALSE;
		$this->layout = ('ajax');		

		if ($this->request->is('ajax')) 
			{
				$id= $this->request->data['id'];
				$this->loadModel('GstMaster');
				$this->loadModel('Ledger');
				$this->GstMaster->id =$id;
				if (!$this->GstMaster->exists()) 
				{
					throw new NotFoundException('Invalid GST Slab');

				}else{				
					$data=$this->GstMaster->findById($id);
					if(!empty($data))
					{
						$name=$data['GstMaster']['name'];
						$gst_percentage=$data['GstMaster']['gst_percentage'];
						$sgst=$data['GstMaster']['sgst'];
						$cgst=$data['GstMaster']['cgst'];
						$igst=$data['GstMaster']['igst'];
						
						$sale_sgst_po_lg=$data['GstMaster']['sale_sgst_po_lg'];
						$sale_cgst_po_lg=$data['GstMaster']['sale_cgst_po_lg'];
						$sale_igst_po_lg=$data['GstMaster']['sale_igst_po_lg'];
						
						$purchase_sgst_po_lg=$data['GstMaster']['purchase_sgst_po_lg'];
						$purchase_cgst_po_lg=$data['GstMaster']['purchase_cgst_po_lg'];
						$purchase_igst_po_lg=$data['GstMaster']['purchase_igst_po_lg'];
						
						$rcm_crd_sgst_lg=$data['GstMaster']['rcm_crd_sgst_lg'];
						$rcm_crd_cgst_lg=$data['GstMaster']['rcm_crd_cgst_lg'];
						$rcm_crd_igst_lg=$data['GstMaster']['rcm_crd_igst_lg'];
						
						$rcm_dbt_sgst_lg=$data['GstMaster']['rcm_dbt_sgst_lg'];
						$rcm_dbt_cgst_lg=$data['GstMaster']['rcm_dbt_cgst_lg'];
						$rcm_dbt_igst_lg=$data['GstMaster']['rcm_dbt_igst_lg'];
						
						$sale_sgst_po_lg_name='';
						$sale_cgst_po_lg_name='';
						$sale_igst_po_lg_name='';
						$data1=$this->Ledger->findById($sale_sgst_po_lg,array('Ledger.name'));
						if(!empty($data1))
						{
							$sale_sgst_po_lg_name=$data1['Ledger']['name'];
						}
						$data2=$this->Ledger->findById($sale_cgst_po_lg,array('Ledger.name'));
						if(!empty($data2))
						{
							$sale_cgst_po_lg_name=$data2['Ledger']['name'];
						}
						$data3=$this->Ledger->findById($sale_igst_po_lg,array('Ledger.name'));
						if(!empty($data3))
						{
							$sale_igst_po_lg_name=$data3['Ledger']['name'];
						}
						
						$purchase_sgst_po_lg_name='';
						$purchase_cgst_po_lg_name='';
						$purchase_igst_po_lg_name='';
						
						$data4=$this->Ledger->findById($purchase_sgst_po_lg,array('Ledger.name'));
						if(!empty($data4))
						{
							$purchase_sgst_po_lg_name=$data4['Ledger']['name'];
						}
						$data5=$this->Ledger->findById($purchase_cgst_po_lg,array('Ledger.name'));
						if(!empty($data5))
						{
							$purchase_cgst_po_lg_name=$data5['Ledger']['name'];
						}
						$data6=$this->Ledger->findById($purchase_igst_po_lg,array('Ledger.name'));
						if(!empty($data6))
						{
							$purchase_igst_po_lg_name=$data6['Ledger']['name'];
						}
						
						$rcm_crd_sgst_lg_name='';
						$rcm_crd_cgst_lg_name='';
						$rcm_crd_igst_lg_name='';
						
						$data7=$this->Ledger->findById($rcm_crd_sgst_lg,array('Ledger.name'));
						if(!empty($data7))
						{
							$rcm_crd_sgst_lg_name=$data7['Ledger']['name'];
						}
						$data8=$this->Ledger->findById($rcm_crd_cgst_lg,array('Ledger.name'));
						if(!empty($data8))
						{
							$rcm_crd_cgst_lg_name=$data8['Ledger']['name'];
						}
						$data9=$this->Ledger->findById($rcm_crd_igst_lg,array('Ledger.name'));
						if(!empty($data9))
						{
							$rcm_crd_igst_lg_name=$data9['Ledger']['name'];
						}
						
						$rcm_dbt_sgst_lg_name='';
						$rcm_dbt_cgst_lg_name='';
						$rcm_dbt_igst_lg_name='';
						
						$data10=$this->Ledger->findById($rcm_dbt_sgst_lg,array('Ledger.name'));
						if(!empty($data10))
						{
							$rcm_dbt_sgst_lg_name=$data10['Ledger']['name'];
						}
						$data11=$this->Ledger->findById($rcm_dbt_cgst_lg,array('Ledger.name'));
						if(!empty($data11))
						{
							$rcm_dbt_cgst_lg_name=$data11['Ledger']['name'];
						}
						$data12=$this->Ledger->findById($rcm_dbt_igst_lg,array('Ledger.name'));
						if(!empty($data12))
						{
							$rcm_dbt_igst_lg_name=$data12['Ledger']['name'];
						}
						
						$data=array(
						'name'=>$name,
						'gst_percentage'=>$gst_percentage,
						'sgst'=>$sgst,
						'cgst'=>$cgst,
						'igst'=>$igst,
						
						'sale_sgst_po_lg'=>$sale_sgst_po_lg,
						'sale_cgst_po_lg'=>$sale_cgst_po_lg,
						'sale_igst_po_lg'=>$sale_igst_po_lg,
						
						'purchase_sgst_po_lg'=>$purchase_sgst_po_lg,
						'purchase_cgst_po_lg'=>$purchase_cgst_po_lg,
						'purchase_igst_po_lg'=>$purchase_igst_po_lg,
						
						'rcm_crd_sgst_lg'=>$rcm_crd_sgst_lg,
						'rcm_crd_cgst_lg'=>$rcm_crd_cgst_lg,
						'rcm_crd_igst_lg'=>$rcm_crd_igst_lg,
						
						'rcm_dbt_sgst_lg'=>$rcm_dbt_sgst_lg,
						'rcm_dbt_cgst_lg'=>$rcm_dbt_cgst_lg,
						'rcm_dbt_igst_lg'=>$rcm_dbt_igst_lg,
						
						'sale_sgst_po_lg_name'=>$sale_sgst_po_lg_name,
						'sale_cgst_po_lg_name'=>$sale_cgst_po_lg_name,
						'sale_igst_po_lg_name'=>$sale_igst_po_lg_name,
						
						'purchase_sgst_po_lg_name'=>$purchase_sgst_po_lg_name,
						'purchase_cgst_po_lg_name'=>$purchase_cgst_po_lg_name,
						'purchase_igst_po_lg_name'=>$purchase_igst_po_lg_name,
						
						'rcm_crd_sgst_lg_name'=>$rcm_crd_sgst_lg_name,
						'rcm_crd_cgst_lg_name'=>$rcm_crd_cgst_lg_name,
						'rcm_crd_igst_lg_name'=>$rcm_crd_igst_lg_name,
						
						'rcm_dbt_sgst_lg_name'=>$rcm_dbt_sgst_lg_name,
						'rcm_dbt_cgst_lg_name'=>$rcm_dbt_cgst_lg_name,
						'rcm_dbt_igst_lg_name'=>$rcm_dbt_igst_lg_name,
						
					);
						

						
						echo json_encode(array('status'=>'1000','mydata'=>$data,));		
						
					}else{
							echo json_encode(array('status'=>'1001'));		
					}
				
						

				}
				
			}
			else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
    }
	
		/*
	Amit Sahu
	Add gst slab
	23.07.18
	*/
	public function admin_editGstSlab()
	{		
		$this->autoRender = FALSE;
		$this->layout = 'ajax';
		$this->loadModel('GstMaster');
		
		
		if ($this->request->is('ajax')) 
			{
				
				
				
				if ($this->GstMaster->save($this->request->data['GstMaster'])) 
				{
					
						$id=$this->request->data['GstMaster']['id'];
						$name=$this->request->data['GstMaster']['name'];
						$gst_percentage=$this->request->data['GstMaster']['gst_percentage'];
						$sgst=$this->request->data['GstMaster']['sgst'];
						$cgst=$this->request->data['GstMaster']['cgst'];
						$igst=$this->request->data['GstMaster']['igst'];
					
						
					echo json_encode(array('status'=>'1000','message'=>'GST slab updated successfully', 'id'=>$id,'name'=>$name,'gst_percentage'=>$gst_percentage,'sgst'=>$sgst,'cgst'=>$cgst,'igst'=>$igst));
				} 
				else 
				{
					echo json_encode(array('status'=>'1001','message'=>'GST slab could not be updated'));
				}
			}				
		
		else
		{
			$this->Session->setFlash("Unauthorized access", 'error');
            $this->redirect($this->referer());
		}
			
    }
}