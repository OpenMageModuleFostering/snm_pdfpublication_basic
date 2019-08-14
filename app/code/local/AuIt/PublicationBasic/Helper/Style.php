<?php
class AuIt_PublicationBasic_Helper_Style extends Mage_Core_Helper_Abstract
{
    public function getStyleItem($items)
    {
    	$blockInfo=array('x'=>10,'y'=>10,'w'=>60,'h'=>40,'box_class'=>'');
    	$items = $this->setArrayDefault($items,$blockInfo);
    	return Mage::getModel('auit_publicationbasic/style',$items);
    }
	public function setArrayDefault($x,$defaults)
	{
    	foreach ( $defaults as $key => $v)
    	{
    		if ( !isset($x[$key])|| trim($x[$key]) === '' )
    		{
    			$x[$key]=$v;
    		}
    	}
    	if ( isset($x['box_class']) ){
    		$x['class']=$x['box_class'];
    		unset($x['box_class']);
    	}
		return $x;
	}
	public function getClassNames($styleId)
	{
		$style = Mage::getModel('auit_publicationbasic/styles')->load($styleId);
		return $style->getClassNames();
	}
	public function getCss($styleId,$field=null)
	{
		$style = Mage::getModel('auit_publicationbasic/styles')->load($styleId);
		return $style->getCss();
	}
	public function getCssStyles()
	{
		static $_options;
		if (!$_options) {
			$collection = Mage::getResourceModel('auit_publicationbasic/styles_collection')->
			addFieldToFilter('status',1);
			foreach ( $collection as $opt )
			{
				$_options[$opt->getIdentifier()] = $opt->getName();
			}
		}
		return $_options;
	}
	public function getCssFromObj($objectData)
	{
		static $_options;
		if (!$_options) {
			$_options=array();
			if ( isset($objectData['preview_style']) && $objectData['preview_style'] )
			{
				$_options[$objectData['preview_style']]=$this->getCss($objectData['preview_style'],'identifier');
			}
		}
		return $_options;
	}
	public function getCssClasses($objectData)
	{
		//(auto) 6pt 72pt
		static $_options;
		if (!$_options) {
			$_options=array();
			$_options[]=array('value'=>'','label'=>'');
			if ( isset($objectData['preview_style']) && $objectData['preview_style'] )
			{
				
				foreach ( $this->getClassNames($objectData['preview_style']) as $v =>$l)
					$_options[]=array('value'=>$v,'label'=>$l);
			}
		}
		return $_options;
	}
	public function getComputedStyle($styleId,$class,$att,$default)
	{
		$style = Mage::getModel('auit_publicationbasic/styles')->load($styleId);
		return $style->getComputedStyle($class,$att,$default);
	}
}
