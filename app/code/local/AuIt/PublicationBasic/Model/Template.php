<?php

class AuIt_PublicationBasic_Model_Template extends Mage_Core_Model_Abstract
{
    const CACHE_TAG     = 'auit_publicationbasic_template';
    protected $_cacheTag= 'auit_publicationbasic_template';
    protected $_depMode='';
    protected $_depLevel=0;
    protected function _construct()
    {
        $this->_init('auit_publicationbasic/template');
    }
    public function initialBasisData()
    {
    	if ( $this->getId() ) return;
    	$file   = Mage::getModuleDir('data', 'AuIt_Publication') . DS . 'initial'.DS.'template'.DS.'type_'.$this->getType().'.json';
    	if ( file_exists($file) )
    	{
    		
    		$this->setData('data',file_get_contents($file));
    	}
    }
    protected function _checkTemplateThumnails()
    {
    	 
    }
    protected function _getThumnailFile()
    {
    	
    }
    public function buildObjectList()
    {
    	$dep =array();
    	$dep['templates']=array();
    	$dep['styles']=array();
    	$dep['generators']=array();
    	$dep['images']=array();
    	$dep['vars']=array();
    	$dep['spreads']=array();
    	
    	$this->_depMode='var';
    	
    	$this->addDependence($dep);
    	$this->_depMode='';
    	
    	return array(
    		'identifier'=>$this->getIdentifier(),
    		'spreads'=>$dep['spreads'],
    		'vars'=>$dep['vars']
    	);
    	 
    }
    public function addDependence(&$dep,$level=0,$currentMainSpread=-1)
    {
    	$data=Zend_Json::decode($this->getData('data'));

    	 
    	if ( isset($dep['templates'][$this->getIdentifier()]) )
    		return;
    	$dep['templates'][$this->getIdentifier()]=$this;
    	
    	//Mage::log($data);
    	if ( isset($data['preview_style']) && $this->_depMode != 'var')
    	{
	    	$styleId = trim($data['preview_style']);
	    	if ( $styleId && !isset($dep['styles'][$styleId]))
	    	{
	    		$style = Mage::getModel('auit_publicationbasic/styles');
	    		$style->load(trim($styleId),'identifier');
	    		if ( $style->getId() )
	    		{
	    			$style->addDependence($dep);
	    			$dep['styles'][$styleId]=$style;
	    		}
	    	}
    	}
    	if ( isset($data['spreads']) )
    	foreach ( $data['spreads'] as $idx => $spread )
    	{
    		if ( $level == 0){
	    		$currentMainSpread= $idx+1;
	    		if ( isset($dep['spreads']) )
	    		{
	    			$name = 'Spread: '.$currentMainSpread;
	    			if ( isset($spread['pages']) )
	    			{
	    				foreach ( $spread['pages'] as $page )
	    				{
	    					$name .= ' ('.$page['name'].')';
	    					break;
	    				}
	    			}
	    			$dep['spreads'][]=array('name'=>$name,'idx'=>$currentMainSpread);
	    		}
    		}
    		foreach ( $spread['boxes'] as $box )
    		{
    			if ( isset($box['style_font_style']) && $box['style_font_style'] )
    			{
    				$tmp = explode(':',$box['style_font_style']);
    				$fn = trim($tmp[0]);
    				if ( $fn )
    					$dep['fonts'][$fn]=$fn;
    			}	
				switch ( $box['type'] )
				{
					case 'p_img':
						$tid = trim($box['p_opt']);
						$src = trim($box['src']);
						if ( $tid == 'media_static' )
						{
							$dep['images'][$src]=$src;
							if ( isset($dep['vars']) && isset($box['isparameterbox']) && $box['isparameterbox'])
							{
								$dep['vars'][]=array(
										'uid'=>$box['uid'],
										'name'=>$box['name'],
										'type'=>$box['type'],
										'src'=>$box['src'],
										'spread'=>$currentMainSpread,
										'tempname'=>trim($this->getName())
								);
								
							}
						}
						break;
					case 'p_free':
							if ( isset($dep['vars']) && isset($box['isparameterbox']) && $box['isparameterbox'])
							{
								$dep['vars'][]=array(
										'uid'=>$box['uid'],
										'name'=>$box['name'],
										'type'=>$box['type'],
										'def'=>$box['p_opt'],
										'spread'=>$currentMainSpread,
										'tempname'=>trim($this->getName())
								);
								
							}
						break;
					case 'p_bc':
						if ( isset($box['src']) && ($src = trim($box['src'])) )
						{
							$dep['images'][$src]=$src;
						}
						break;
					case 'p_templ':
						$tid = trim($box['p_opt']);
						if ( $tid && !isset($dep['templates'][$tid]) )
						{
							$model = Mage::getModel('auit_publicationbasic/template');
							$model->load(trim($box['p_opt']),'identifier');
							if ( $model->getId() )
								$model->addDependence($dep,$level+1,$currentMainSpread);
						}
					break;
					case 'p_gen':
						$tid = trim($box['p_opt']);
						if ( $tid && !isset($dep['generators'][$tid]) && $this->_depMode != 'var')
						{
							$model = Mage::getModel('auit_publicationbasic/generator');
							$model->load(trim($box['p_opt']),'identifier');
							if ( $model->getId() )
							{
								$model->addDependence($dep);
								$dep['generators'][$tid]=$model;
							}
						}
						break;
				}
    		}
    	}
    }
    public function exportTo(&$package,$rootDir,$templId)
    {
    	if ( isset($package['templates'][$this->getIdentifier()] ) )
    		return; 
    	//$dir = $rootDir.DS.$templId;
    	$this->_checkDir($rootDir);
    	$data = $this->getData();
    	$data['template_id']=null;
    	$Identifier = $this->getIdentifier();
    	file_put_contents($rootDir.DS.'template_'.$Identifier.'.ser', serialize($data));

    	$package['templates'][$this->getIdentifier()]=1;
    	
    	$dep =array(); 
    	$dep['templates']=array();
    	$dep['styles']=array();
    	$dep['generators']=array();
    	$dep['images']=array();
    	
    	$this->addDependence($dep);
    	foreach ( $dep as $type => $info )
    	{
    	//	Mage::log("t:$type : ".count($info));
    		switch ( $type )
    		{
    			case 'styles':
    				foreach ( $info as $key =>  $model  )
    				{
    		    		if ( $model->getId() )
    						$model->exportTo($package,$rootDir,$templId);
    				}
    			break;
    			case 'generators':
    		    	foreach ( $info as $key =>  $model  )
    				{
    		    		if ( $model->getId() )
    						$model->exportTo($package,$rootDir,$templId);
    				}
    			break;
    			case 'images':
    		    	foreach ( $info as $key =>  $model  )
    		    	{    		
    		    		if ( !isset($package['images'][$key]))
    		    		{
    		    			$this->exportImagesTo(trim($model),$rootDir);
    		    			$package['images'][$key]=1;
    		    		}		
    		    	}
    			break;
    			case 'fonts':
    		    	foreach ( $info as $key =>  $model  )
    		    	{    				
    		    		if ( !isset($package['fonts'][$key]))
    		    		{
    		    			$this->exportFontsTo(trim($model),$rootDir);
    						$package['fonts'][$key]=1;
    		    		}
    		    	}
    			break;
    			case 'templates':
    		    	foreach ( $info as $key =>  $model  )
    		    	{    				
    		    		if ( $model->getId() && !isset($package['templates'][$key]))
    		    		{
    		    			file_put_contents($rootDir.DS.'template_'.$key.'.ser', serialize($model->getData()));
    						$package['templates'][$key]=1;
    					}
    				}
    			break;
    		}
    	}
    	 
    	$dep = $data['dependence'];
    	$lines = explode("\n",$dep);
    	foreach ( $lines as $line )
    	{
    		$cmd = explode(':',$line);
    		switch ( trim($cmd[0]) )
    		{
    			case 'styles':
    				foreach ( explode(',',$cmd[1]) as $ident )
    				{
    					$style = Mage::getModel('auit_publicationbasic/styles');
    					$style->load(trim($ident),'identifier');
    					$style->exportTo($package,$rootDir,$templId);
    				}
    				break;
   				case 'generators':
    		    	foreach ( explode(',',$cmd[1]) as $ident )
    				{
    					$style = Mage::getModel('auit_publicationbasic/generator');
    					$style->load(trim($ident),'identifier');
    					$style->exportTo($package,$rootDir,$templId);
    				}
   					break;
   				case 'templates':
    		    	foreach ( explode(',',$cmd[1]) as $ident )
    				{
    					$ident=trim($ident); 
    					if ( !isset($package['templates'][$ident]) )
    					{
    						$model = Mage::getModel('auit_publicationbasic/template');
    						$model->load(trim($ident),'identifier');
    						if ( $model->getId() )
    							$model->exportTo($package,$rootDir,$templId);
    					}					
    				}
   					break;
   				case 'images':
    		    	foreach ( explode(',',$cmd[1]) as $ident )
    				{
    					$ident=trim($ident);
    					if ( !isset($package['images'][$ident]))
    					{
    						$this->exportImagesTo($ident,$rootDir);
    						$package['images'][$ident]=1;
    					}
    				}
   					break;
   				case 'fonts':
    		    	foreach ( explode(',',$cmd[1]) as $ident )
    				{
    				    $ident=trim($ident);
    					if ( !isset($package['fonts'][$ident]))
    					{
    						$this->exportFontsTo(trim($ident),$rootDir);
    						$package['fonts'][$ident]=1;
    					}
    				}
   					break;
    		}
    	}
    	 
    }
    
    public function exportImagesTo($ident,$rootDir)
    {
    	if ( !$ident )
    		return;
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$file = $helper->convertIdToPath(''.$ident,false);
    	if ( !is_file($file) )
    	{
    		Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot found file :%s',$ident));
    	}
    	$this->_checkDir(dirname($rootDir.DS.'images'.DS.$ident) );
    	if ( !copy($file, $rootDir.DS.'images'.DS.$ident) )
    		Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot copy file :%s',$ident));
    }
    public function exportFontsTo($ident,$rootDir)
    {
    	$file = Mage::helper('auit_publicationbasic/font')->getFontDir($ident);
    	if ( !is_file($file) )
    	{
    		Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot found font :%s',''.$ident));
    	}
    	$this->_checkDir(dirname($rootDir.DS.'fonts'.DS.$ident));
    	if ( !copy($file, $rootDir.DS.'fonts'.DS.$ident) )
    		Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot copy font :%s',''.$ident));
    }
    protected function _checkDir($path)
    {
    	$io = new Varien_Io_File();
    	if (is_dir($path) || $io->mkdir($path)) {
    		if ( is_writable($path) )
    			return true;
    	}
    	Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot create new directory:%s',$path));
    }
    public function importData(&$messages,$templateDir,$templdata,$bvalidate)
    {
    	if ( isset($templdata['template_id']) )
    		unset($templdata['template_id']);
    	$this->addData($templdata);
    }
 }