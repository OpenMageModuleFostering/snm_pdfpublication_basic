<?php
class AuIt_PublicationBasic_Admin_FilemanagerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
    }
    public function loadAction()
    {
    	try {
    		$cmd = $this->getRequest()->getParam('operation');
    		switch ( $cmd )
    		{
    			case 'get_children':
    				$this->_get_children();
    				break;
    			case 'create_node':
    				$this->_create_node();
    				break;
    			case 'rename_node':
   					$this->_rename_node();
				break;
    			case 'remove_node':
   					$this->_remove_node();
				break;
    			case 'get_files':
   					$this->_get_files();
				break;
				case 'search':
					$this->_search();
					break;

    			case 'upload_default':
    				$this->_upload_default();
    				break;
    		}
    	}
    	catch ( Exception $e )
    	{
    		Mage::log("AuIt_PublicationBasic_Admin_FilemanagerController::Exception - ".$e->getMessage());
    		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    		$this->getResponse()->setBody(Zend_Json::encode(array('status'=>0,'error'=>$e->getMessage())));
    		return;
    	}
    }
    static function _toByteString($size)
    {
    	$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    	for ($i=0; $size >= 1024 && $i < 9; $i++) {
    		$size /= 1024;
    	}
    	return round($size, 2) . $sizes[$i];
    }
    protected function getDocumentFileNodeData($filename)
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$title = basename($filename);
    	$info = pathinfo($filename);
    	$name = basename($filename,'.'.$info['extension']);
    	$qtip = ''.Mage::helper('core')->formatDate(new Zend_Date(filemtime($filename)), Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, true);
    	$qtip .= ' ['.self::_toByteString(filesize($filename)).']';
    	$html = '<li class="ui-state-default snm-file-box clearfix" id="'.$helper->convertPathToId($filename).'" data-src="'.$helper->convertPathToArea($filename).'">';
    	//$html.= '<a id="'.$helper->convertPathToId($filename).'_A"></a>';

    	$html.= '<img src="'.Mage::helper('auit_publicationbasic/dirdirective')->getThumbUrl($filename).'"/>';
    	$html.= '<p class="title">'.$title.'</p>';
    	$html.= '<p class="info">'.$qtip.'</p>';
    	$html.= '</li>';
    	return $html;
    }

    protected  function _get_files()
    {
    	
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$jsonArray = array();
    	$storeId=0;
    	$this->getRequest()->setParam('storeID',$storeId);
    	$this->getRequest()->setParam('store',$storeId);
    	$source = AuIt_PublicationBasic_Helper_Filemanager::ROOT;
    	$path = $helper->getCurrentPath();
    	
    	$filter = '';
    	$collection = $helper->getStorage()->getFilesCollection($path,'picture');
    	
    	$jsonArray[] = '<ul>';
    	foreach ($collection as $item) {
   			$child = $this->getDocumentFileNodeData($item->getFilename());
    		$jsonArray[] = $child;
    	}
    	$jsonArray[] = '</ul>';
    	$this->getResponse()->setBody(implode("\n", $jsonArray));
    	
    }
    protected  function _get_children()
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$jsonArray = array();
    	$storeId=0;
    	$this->getRequest()->setParam('storeID',$storeId);
    	$this->getRequest()->setParam('store',$storeId);
    	$source = AuIt_PublicationBasic_Helper_Filemanager::ROOT;
//    	$helper->checkFolder(Mage::getBaseDir('media').DS.'snm-portal'.DS.'print-factory'.DS.'samples');
    	$path = $helper->getCurrentPath();
    	if ( $this->getRequest()->getParam('id') == 1 )
    	{
    		$child=array();
//    		$child['attr']=array('id'=>$helper->convertPathToId($path),'rel'=>'drive');
    		$child['attr']=array('id'=>'ROOT','rel'=>'drive');
    		$child['state']='closed';
    		$child['data']='MEDIA';
    		$child['children']=array();
    		$jsonArray[] = $child;
    		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    		$this->getResponse()->setBody(Zend_Json::encode($jsonArray));
			return;
    	}

    	$filter = '';

    	$collection = $helper->getStorage()->getDirsCollection($path,'document');
    	foreach ($collection as $item) {
    		if ( is_dir($item->getFilename()) && (!$filter || $filter == basename($item->getFilename())))
    		{
    			$child=array();


    			$child['attr']=array('id'=>$helper->convertPathToId($item->getFilename()),'rel'=>'folder');
    			$child['state']='closed';
    			$child['data']=array('title'=>$item->getBasename(),'icon'=>'folder');
    			$child['children']=array();
    			$jsonArray[] = $child;
    		}
    	}
		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    	$this->getResponse()->setBody(Zend_Json::encode($jsonArray));
    }
    protected  function _create_node()
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$path = $helper->getCurrentPath();
		$name = $this->getRequest()->getParam('title');
		$status=0;$id=0;
		if ( $name && ($this->getRequest()->getParam('type') == 'folder') )
		{
			if ( $helper->checkFolder($path.DS.$name) )
			{
				$id = $helper->convertPathToId($path.DS.$name);
				$status=1;
			}
		}
		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
		$this->getResponse()->setBody(Zend_Json::encode(array('status'=>$status,'id'=>$id)));
    }
    protected  function _rename_node()
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$path = $helper->getCurrentPath();
		$newName = $this->getRequest()->getParam('title');
    	$status=0;
    	$id=0;
		if ( is_dir($path) ) {
    		$newPath = $helper->getStorage()->renameDirectory($newName,$path);
    		$id = $helper->convertPathToId($newPath);
    		$status=1;
		}
		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
		$this->getResponse()->setBody(Zend_Json::encode(array('status'=>$status,'id'=>$id)));
    }
    protected  function _remove_node()
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$path = $helper->getCurrentPath();
		if ( is_dir($path) )
			$helper->getStorage()->deleteDirectory($helper->getCurrentPath());
		$status=1;$id=0;
		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
		$this->getResponse()->setBody(Zend_Json::encode(array('status'=>$status,'id'=>$id)));
    }
    protected  function _upload_default()
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$path = $helper->getCurrentPath();
		$status=0;$id=0;
    	if ( is_dir($path) )
		{
			$type = null;//trim($request->getParam('type'));
			$result = $helper->getStorage()->uploadFile($path,$type);
			$status=1;$id=0;
		}
		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
		$this->getResponse()->setBody(Zend_Json::encode(array('status'=>$status,'id'=>$id)));
    }

    protected  function _search()
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$search_str = $this->getRequest()->getParam('search_str');
    	$path = $helper->convertIdToPath($search_str,false);
    	$result=array();
    	if ( is_file($path) )
    	{
    		$treepath = $helper->convertPathToArea($path);
    		$treepath = explode(DS,$treepath);
    		$full = array_shift($treepath);
    		$result[]='#'.$full;
    		foreach ( $treepath as $subpath )
    		{
    			$full .= DS.$subpath;
    			$result[]='#'.$helper->urlEncode($full);
    		}
    	}
		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
		$this->getResponse()->setBody(Zend_Json::encode($result));
    }
}
