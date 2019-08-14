<?php
class AuIt_PublicationBasic_Model_Filemanager_Storage extends Varien_Object
{
    const DIRECTORY_NAME_REGEXP = '/^[a-z0-9\-\_]+$/si';
    public function getDirsCollection($path)
    {

        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setDirsFilter(self::DIRECTORY_NAME_REGEXP)
            ->setCollectFiles(false)
            ->setCollectRecursively(false);
        return $collection;
    }
    public function getFileDirsCollection($path, $type = null)
    {
        $collection = $this->getCollection($path)
            ->setCollectDirs(true)
            ->setDirsFilter(self::DIRECTORY_NAME_REGEXP)
            ->setCollectFiles(true)
            ->setCollectRecursively(false);
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed). ')$/i');
        }
		return $collection;
    }
    public function getFilesCollection($path, $type = null)
    {
        $collection = $this->getCollection($path)
            ->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(false)
            ->setOrder('mtime', Varien_Data_Collection::SORT_ORDER_ASC);

        // Add files extension filter
        if ($allowed = $this->getAllowedExtensions($type)) {
            $collection->setFilesFilter('/\.(' . implode('|', $allowed). ')$/i');
        }

        return $collection;
    }
    public function getCollection($path = null)
    {
        $collection = Mage::getModel('auit_publicationbasic/filemanager_storage_collection');
        if ($path !== null) {
            $collection->addTargetDir($path);
        }
        return $collection;
    }
    public function createDirectory($name, $path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $name)) {
            Mage::throwException(Mage::helper('auit_publicationbasic')->__('Invalid folder name. Please, use alphanumeric characters'));
        }
        if (!is_dir($path) || !is_writable($path)) {
            $path = Mage::helper('auit_publicationbasic/filemanager')->getStorageRoot();
        }

        $newPath = $path . DS . $name;

        if (file_exists($newPath)) {
            Mage::throwException(Mage::helper('auit_publicationbasic')->__('Such directory already exists. Try another folder name'));
        }

        $io = new Varien_Io_File();
        if ($io->mkdir($newPath)) {
            $result = array(
                'name'  => $name,
                'path'  => $newPath,
                'id'    => Mage::helper('auit_publicationbasic/filemanager')->convertPathToId($newPath)
            );
            return $result;
        }
        Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot create new directory'));
    }
    public function renameDirectory($newName,$path)
    {
        if (!preg_match(self::DIRECTORY_NAME_REGEXP, $newName)) {
            Mage::throwException(Mage::helper('auit_publicationbasic')->__('Invalid folder name. Please, use alphanumeric characters'));
        }
    	$newPath = dirname($path).DS.$newName;
        $io = new Varien_Io_File();
        if ( !@rename($path, $newPath) )
        {
            Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot rename directory %s', $path));
        }
        return $newPath;
    }
    public function deleteDirectory($path)
    {
        $io = new Varien_Io_File();
        if (!$io->rmdir($path, true)) {
            Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot delete directory %s', $path));
        }
    }
    public function renameFile($newName,$path)
    {
    	$newPath = dirname($path).DS.$newName;
    	$io = new Varien_Io_File();
    	$io->open(array('path'=>dirname($path)));
//    	$io->cd(dirname($path));
        if ( !$io->mv($path, $newPath) )
        {
            Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot rename file %s', basename($path)));
        }
        return $newPath;
    }
    public function deleteFile($filename)
    {
    	$io = new Varien_Io_File();
    	$io->cd(dirname($filename));
    	if ( !$io->rm($filename) )
        {
            Mage::throwException(Mage::helper('auit_publicationbasic')->__('Cannot delete file %s', basename($filename)));
        }
        return true;
    }
    public function uploadFile($targetPath, $type = null)
    {
        $uploader = new Varien_File_Uploader('aimage');
        if ($allowed = $this->getAllowedExtensions($type)) {
            $uploader->setAllowedExtensions($allowed);
        }
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        $result = $uploader->save($targetPath);
        if (!$result) {
            Mage::throwException( Mage::helper('auit_publicationbasic')->__('Cannot upload file') );
        }
        if ($filename = $uploader->getUploadedFileName()) {
        	// Mage::helper('auit_editor/dirdirective')->execute($targetPath.DS.$filename);
        }
        return $result;
    }
    public function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
            $value = Mage::getStoreConfig('auit_publicationbasic/wysiwyg/'.$key);
            if (is_null($value) || false===$value) {
                $value = $default;
            }
            $this->setData($key, $value);
        }
        return $this->_getData($key);
    }
    public function getAllowedExtensions($type = null)
    {
        $configKey = is_null($type) ? 'browser_allowed_extensions' : 'browser_'.$type.'_allowed_extensions';
        if (preg_match_all('/[a-z0-9]+/si', strtolower($this->getConfigData($configKey)), $matches)) {
            return $matches[0];
        }
        return array();
    }
}
