<?php
class AuIt_PublicationBasic_Helper_Export extends Mage_Core_Helper_Abstract
{
	const FIELD_NAME_SOURCE_FILE = 'publication_import_file';

	public function uploadSource($dir)
	{
		$uploader  = Mage::getModel('core/file_uploader', self::FIELD_NAME_SOURCE_FILE);
		$uploader->skipDbProcessing(true);
		$result    = $uploader->save($dir);
		$extension = pathinfo($result['file'], PATHINFO_EXTENSION);
		$uploadedFile = $result['path'] .DS. $result['file'];
		if ($extension != 'tgz') {
			unlink($uploadedFile);
			Mage::throwException(Mage::helper('auit_publicationbasic')->__('Uploaded file has no extension'));
		}
		$gzPacker = new Mage_Archive_Gz();
		$tmpName = '~tmp-'. microtime(true) . '.tar';
		$gzPacker->unpack($uploadedFile, $dir.DS.$tmpName);
		$tarPacker = new Mage_Backup_Archive_Tar();
		$tarPacker->unpack($dir.DS.$tmpName, $dir.DS.'import'.DS);
		return $dir.DS.'import';
	}
	public function getImportDir()
	{
		return Mage::getBaseDir("var").DS.'tmp'.DS.'import';
	}
	public function getTemplateDir($uniqId)
	{
		return Mage::getBaseDir("var").DS.'tmp'.DS.'templates'.DS.$uniqId;
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
	
	
	
	public function importArea($uniqId)
	{
		$importDir = $this->getImportDir().DS.$uniqId;
		return $this->importDir($uniqId,$importDir,false);
	}
	public function validImport($data)
	{
		$uniqId = uniqid();
		$importDir = $this->getImportDir().DS.$uniqId;
		$this->_checkDir($importDir);
		$this->uploadSource($importDir);
		return $this->importDir($uniqId,$importDir,true);
	}
	public function importDir($uniqId,$importDir,$bvalidate)
	{
		$templateDir = $importDir.DS.'import';
		$messages=array();
		$messages['import_dir']=$importDir;
		$messages['import_key']=$uniqId;
		
		$templates = unserialize(file_get_contents($templateDir.DS.'package.ser'));
		if ( is_array($templates))
		{
			if ( is_array($templates['fonts']) )
			foreach ( $templates['fonts'] as $font => $tmp)
			{
				$fileFrom = $templateDir.DS.'fonts'.DS.$font;
				if ( !is_file($fileFrom) )
				{
					$messages['error'][] = $this->__('Cannot find font in archiv : %s',$font);
				}else {
					$file = Mage::helper('auit_publicationbasic/font')->getFontDir($font);
					if ( !is_file($file) )
					{
						
						$messages['notice'][] = $this->__('Import font %s',$font);
						if (!$bvalidate  )
							$this->_checkDir(dirname($file));
						if (!$bvalidate && !copy($fileFrom,$file) )
							$messages['error'][] = $this->__('Cannot copy font :%s',$font);
					}else {
						$messages['notice'][] = $this->__('Font %s already imported -> ignore',$font);
					}
				}
			}
					
			if ( is_array($templates['images']) )
			foreach ( $templates['images'] as $image => $tmp )
			{
				if ( !$image) continue;
				$fileFrom = $templateDir.DS.'images'.DS.$image;
				if ( !is_file($fileFrom) )
				{
					$messages['error'][] = $this->__('Cannot find image in archiv : %s',$image);
				}else {
					$file = Mage::helper('auit_publicationbasic/filemanager')->convertIdToPath($image,false);
					if ( !is_file($file) )
					{
						$messages['notice'][] = $this->__('Import image %s',$image);
						if (!$bvalidate  )
							$this->_checkDir(dirname($file));
						if (!$bvalidate && !copy($fileFrom,$file) )
							$messages['error'][] = $this->__('Cannot copy image :%s',$image);
					}else {
						$messages['notice'][] = $this->__('Image %s already imported -> ignore',$image);
					}
				}				
			}
			if ( is_array($templates['styles']) )
			foreach ( $templates['styles'] as $styleId => $tmp )
			{
				$styleId=trim($styleId);
				$file = $templateDir.DS.'style_'.$styleId.'.ser';
				if ( !file_exists($file) )
					$messages['error'][]=$this->__('Cannot find style in archiv : %s',''.$file);
				else {
					$data = unserialize(file_get_contents($file));
					$model = Mage::getModel('auit_publicationbasic/styles');
					$model->load($styleId,'identifier');
					if (!$model->getId() || $model->getIdentifier() != $styleId || $model->getUpdateTime() <  $data['update_time'] )
					{
						$messages['notice'][] = $this->__('Import style %s ',$styleId);
						$model->importData($messages,$templateDir,$data,$bvalidate);
						$messages['transaction'][]=$model;
					}else
						$messages['notice'][] = $this->__('Style %s already imported -> ignore',$styleId);
				}
			}
			if ( is_array($templates['generators']) )
			foreach ( $templates['generators'] as $generatorId => $tmp )
			{
				$generatorId=trim($generatorId);
				$file = $templateDir.DS.'generator_'.$generatorId.'.ser';
				if ( !file_exists($file) )
					$messages['error'][]=$this->__('Cannot find generator in archiv : %s',''.$file);
				else {
					$data = unserialize(file_get_contents($file));
					$model = Mage::getModel('auit_publicationbasic/generator');
					$model->load($generatorId,'identifier');
					if (!$model->getId() || $model->getIdentifier() != $generatorId || $model->getUpdateTime() <  $data['update_time'] )
					{
						$messages['notice'][] = $this->__('Import generator %s ',$generatorId);
						$model->importData($messages,$templateDir,$data,$bvalidate);
						$messages['transaction'][]=$model;
					}else
						$messages['notice'][] = $this->__('Generator %s already imported -> ignore',$generatorId);
				}
			}
			
			if ( is_array($templates['templates']) )
				foreach ( $templates['templates'] as $templateId => $tmp )
				{
					$templateId=trim($templateId);
					$file = $templateDir.DS.'template_'.$templateId.'.ser';
					if ( !file_exists($file) )
						$messages['error'][]=$this->__('Cannot find template in archiv : %s',''.$file);
					else {
						$data = unserialize(file_get_contents($file));
						$model = Mage::getModel('auit_publicationbasic/template');
						$model->load($templateId,'identifier');
						if (!$model->getId() || $model->getIdentifier() != $templateId || $model->getUpdateTime() <  $data['update_time'] )
						{
							$messages['notice'][] = $this->__('Import template %s ',$templateId);
							$model->importData($messages,$templateDir,$data,$bvalidate);
							$messages['transaction'][]=$model;
						}else
							$messages['notice'][] = $this->__('Template %s already imported -> ignore',$templateId);
					}
				}
		}else {
			$messages['error'][] = $this->__('File does not contain data. Please upload another one');
		}
		return $messages;
	}
}