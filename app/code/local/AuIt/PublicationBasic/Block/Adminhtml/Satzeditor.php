<?php
class AuIt_PublicationBasic_Block_Adminhtml_Satzeditor extends Mage_Adminhtml_Block_Template
{
	protected $_modeloptions;
	protected $_obj;
	
	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
	protected function _beforeToHtml()
	{
		return parent::_beforeToHtml();
	}
	/*
	public  function isTemplate($type=-1)
	{
		$objectInfo = $this->getObjectInfo();
		$isTemplate = ($objectInfo['model']=='template');
		$t = $objectInfo['type'];
		return ($isTemplate && $type==-1) || ($isTemplate && $type==$t); 
	}
	public  function getTemplateType()
	{
		$objectInfo = $this->getObjectInfo();
		$isTemplate = ($objectInfo['model']=='template');
		$t = $objectInfo['type'];
		return $isTemplate?$t:0;
	}
	*/
	/*
	public  function getObjectInfo()
	{
		$objectData=array();
		if ( $obj =Mage::registry('auit_publicationbasic_project') ){
			$objectData['model']='project';
			$objectData['id']=$obj->getId();
			$objectData['type']=$obj->getType();
		}
		if ( $obj =Mage::registry('auit_publicationbasic_template') ){
			$objectData['model']='template';
			$objectData['id']=$obj->getId();
			$objectData['type']=$obj->getType();
		}
		return $objectData;
	}
	*/
	public  function getObj()
	{
		if ( !$this->_obj )
		{
			$obj = Mage::helper('auit_publicationbasic')->cleanLayoutData($this->getElement()->getValue());
//			$obj['object']=$this->getObjectInfo();
			$this->_obj = $obj;
			
		}
		return 	$this->_obj;
	}
	public  function getJsonObj()
	{
		
		return Mage::helper('core')->jsonEncode($this->getObj());
	}
	public  function getJsonModel()
	{
		return '';//Mage::helper('core')->jsonEncode($this->getObj());
	}
	public  function getObjData($key)
	{
		$d = $this->getObj();
		return $d[$key];
	}
/*	
	public  function getModelOptions()
	{
		if ( !$this->_modeloptions )
		{
			$obj = $this->getObj();
			$genParams=array();
			foreach ( Mage::getResourceModel('auit_publicationbasic/generator_collection')->load() as $opt )
			{
				$genParams[$opt->getIdentifier()] = $opt->getParameter();
			}
		//	Mage::log($genParams);	
			$options=array(
					'gen_params'=>$genParams,
					'attributes'=>Mage::helper('auit_publicationbasic')->getAttributes($this->isTemplate(),$this->getTemplateType()),
					'searchurl'=>Mage::getModel('adminhtml/url')->getUrl('auit_publicationbasic/admin_preview/search'),
					'styleurl'=>Mage::getModel('adminhtml/url')->getUrl('auit_publicationbasic/admin_styles/edit'),
					'dataurl'=>$this->getUrl('auit_publicationbasic/content/data'),
					'templurl'=>$this->getUrl('auit_publicationbasic/content/templ'),
					'generatorurl'=>$this->getUrl('auit_publicationbasic/content/generator'),
					'blockurl'=>$this->getUrl('auit_publicationbasic/content/block'),
					'mediaUrl'=>Mage::getBaseUrl('media'),
					'contenturl'=>str_replace('/media/', '/auit_publicationbasic/content/', Mage::getBaseUrl('media')),
					'imgurl'=>$this->getUrl('auit_publicationbasic/content/image'),
					'cssurl'=>$this->getUrl('auit_publicationbasic/content/css'),
					//		'css'=>Mage::helper('auit_publicationbasic/style')->getCssFromObj($obj),
					'css_class'=>Mage::helper('auit_publicationbasic/style')->getCssClasses($obj),
					'msg'=>array(
							'b1'=>$this->__('Are you sure you want to delete current box?'),
							'p1'=>$this->__('Are you sure you want to delete current page?'),
					),
					'clipgroups'=>Mage::helper('auit_publicationbasic/svg')->getClipGroups()
			);
			$this->_modeloptions=$options;
		}		
		return $this->_modeloptions;
	}
*/	
}
