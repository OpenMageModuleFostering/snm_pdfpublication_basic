<?php
class AuIt_PublicationBasic_Helper_Pdf extends Mage_Core_Helper_Abstract
{
	public function getDataSheetUrl($product,$params=array())	
	{
		if ($product && $product instanceof Mage_Catalog_Model_Product) {
			$params['skub64']=urlencode(base64_encode($product->getSku()));
			return Mage::getUrl('auit_publicationbasic/content/datasheet',$params);
		}
		return false;
	}
	public function getDataSheetTemplateId($product)	
	{
		
		$tid = Mage::getStoreConfig('auit_publicationbasic/product_pdf/template_default');
		$map = (array)Mage::helper('auit_publicationbasic/arrayconfig')->getArrayStoreConfig('auit_publicationbasic/product_pdf/template_mapping');
		foreach ( $map as $item )
		{
			$v = $product->getData($item['attribute']);
			if ( $this->_text_compare($item['value'], $v) )
			{
				return $item['template'];
				break;
			}
		}
		return $tid; 
		$tid = Mage::helper('auit_publicationbasic/pdf')->getBestTemplateId($sku);
	}
	protected function _text_compare($wild, $attValue) {
		$secs = explode('|',$wild);
		foreach ( $secs as $sec)
		{
			if ( !$sec ) continue;
	
			if ( function_exists('fnmatch') )
			{
				if ( fnmatch($sec, $attValue) )
					return true;
			} else {
				if ($this->_wild_compare($sec, $attValue))
					return true;
			}
		}
		return false;
	}
	protected function _wild_compare($wild, $string) {
		$wild_i = 0;
		$string_i = 0;
	
		$wild_len = strlen($wild);
		$string_len = strlen($string);
	
		while ($string_i < $string_len && $wild[$wild_i] != '*') {
			if (($wild[$wild_i] != $string[$string_i]) && ($wild[$wild_i] != '?')) {
				return 0;
			}
			$wild_i++;
			$string_i++;
		}
	
		$mp = 0;
		$cp = 0;
	
		while ($string_i < $string_len) {
			if ($wild[$wild_i] == '*') {
				if (++$wild_i == $wild_len) {
					return 1;
				}
				$mp = $wild_i;
				$cp = $string_i + 1;
			}
			else
				if (($wild[$wild_i] == $string[$string_i]) || ($wild[$wild_i] == '?')) {
				$wild_i++;
				$string_i++;
			}
			else {
				$wild_i = $mp;
				$string_i = $cp++;
			}
		}
	
		while ($wild[$wild_i] == '*') {
			$wild_i++;
		}
	
		return $wild_i == $wild_len ? 1 : 0;
	}	
	public function checkDirectorys()
	{
		static $_hasChecked=false;
		if ( !$_hasChecked )
		{
			$_hasChecked=true;
			$pdfPublication = Mage::getModel('auit_publicationbasic/renderer_pdf');
			$paths = array();
			if ( defined('K_PATH_CACHE') )
				$paths[]=K_PATH_CACHE;
			foreach ( $paths as $path )
			{
				$io = new Varien_Io_File();
				if ( !$io->isWriteable($path) || (!is_dir($path) && !$io->mkdir($path, 0777, true)) ) {
					$msg = Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path);
					Mage::log($msg );
					Mage::getSingleton('adminhtml/session')->addError($msg );
				}
			}
		}
	}
	public function getPreviewProductImageUrl($productId)
	{
		$update_time = time();
		return Mage::getModel('adminhtml/url')->getUrl('adminhtml/auitpublicationbasic_preview/productpreview',array('tid'=>$productId,'upd'=>$update_time));
	}
	public function getPreviewTemplateImageUrl($templateId,$update_time,$spread=1)
	{
		if ( $update_time != '[date]' )
			$update_time = strtotime ($update_time);
		return Mage::getModel('adminhtml/url')->getUrl('adminhtml/auitpublicationbasic_preview/templatepreview',array('tid'=>$templateId,'upd'=>$update_time,'spread'=>$spread));
	}
	public function getPreviewJobImageUrl($jobId)
	{
		$update_time = time();
		return Mage::getModel('adminhtml/url')->getUrl('adminhtml/auitpublicationbasic_preview/templatepreview',array('jid'=>$jobId,'upd'=>$update_time));
	}
	public function getPreviewImage($templateId,$update_time,$asThumb=true,$spread=0)
	{
		$model = null;
		if (  !is_object($templateId) )
		{
			$model = Mage::getModel('auit_publicationbasic/template')->load($templateId);
			$templateId = $model->getIdentifier();
			$update_time = $model->getUpdateTime();
			$update_time = strtotime ($update_time);
		}
		else if (  is_object($templateId) )
		{
			$model = $templateId;
			$templateId = $model->getIdentifier(); 
			$update_time = $model->getUpdateTime();
			$update_time = strtotime ($update_time);
		}
		$fileName = 'preview_template_'.$templateId.'_'.($asThumb?'t':'').'s'.$spread.'.jpg';
		//$fileName = 'preview_template_'.$templateId.'_'.($asThumb?'t':'').'.png';
		$directory = Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS.'cache'.DS.'_snm_publication_previews'.DS;
		$path = $directory.$fileName;
		if ( extension_loaded('imagick')) {
		
			
			if ( !file_exists($path) || filemtime($path) < $update_time)
			{
				$io = new Varien_Io_File();
				if ( !$io->isWriteable(dirname($path)) && !$io->mkdir(dirname($path), 0777, true)) {
					$msg = Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path);
					Mage::log($msg );
					Mage::getSingleton('adminhtml/session')->addError($msg );
				}else {
					if ( !$model )
						$model = Mage::getModel('auit_publicationbasic/template')->load($templateId);
					if ( $model->getId() ) {
						$this->createPreviewImage($model,$path,$asThumb,$spread);
					}
				}
			}
		}
		return $path;
	}
	public function createPreviewImage($template,$path,$asThumb,$spread=0)
	{
		$pdfPublication = Mage::getModel('auit_publicationbasic/renderer_pdf');
		$data = Mage::helper('core')->jsonDecode($template->getData('data'));
		try {
			$pdf = $pdfPublication->getPdfFromData('preview',$data,0,$spread);
			$im = new Imagick();
			$im->setBackgroundColor(new ImagickPixel('white'));
			$im->readimageblob($pdf->render());
			$im->setBackgroundColor(new ImagickPixel('white'));
			$im = $im->flattenImages();
			$im->setImageFormat('jpg');
			$im->setiteratorindex(0);
			
			$im->writeimage($path);
				
			$im->clear();
			$im->destroy();
		} catch (Exception $e) {
			Mage::logException($e);
			//Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		//$sender->revertDesign();
//		Mage::getDesign()->setArea($lastArea);
	}
}