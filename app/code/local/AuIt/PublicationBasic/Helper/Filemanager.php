<?php
class AuIt_PublicationBasic_Helper_Filemanager extends Mage_Core_Helper_Abstract
{
   	const MEDIAROOT='MEDIAROOT';
   	const PROTECTEDROOT='PROTECTEDROOT';
   	const SKINROOT='SKINROOT';
   	const SKINTHEMEROOT='SKINTHEMEROOT';
   	const ROOT='ROOT';
   	const UNKNOWN='UNKNOWN';
    protected $_currentPath;
    protected $_currentUrl;
    protected $_rootPath;

    static public function return_bytes($val) {
    	$val = trim($val);
    	$last = strtolower($val[strlen($val)-1]);
    	switch($last) {
    		// The 'G' modifier is available since PHP 5.1.0
    		case 'g':
    			$val *= 1024;
    		case 'm':
    			$val *= 1024;
    		case 'k':
    			$val *= 1024;
    	}
    	return $val;
    }
    public function getMaxUploadSize($inMb=false)
    {
    	$val = min(self::return_bytes(ini_get('post_max_size')),self::return_bytes(ini_get('upload_max_filesize')));
    	if  ( $inMb )
    	{
    		$val /= 1024;
    		$val /= 1024;

    		$val = round($val,2).'MB';

    	}
    	return $val;
    }

    public function TreeRoots()
    {
    	$defDir = $this->correctPath( $this->getStorage()->getConfigData('upload_root') );
		$defDir = sprintf('{{media url="%s/{0}"}}', $defDir);
		return array(
					'root'=>'ROOT',
					'root_directive'=>'{{media url="{0}"}}',
//					'media'=>$this->convertPathToId($this->getMediaRoot()),
	//				'media_directive'=>$defDir,
					'preview'=>$this->convertPathToId($this->getStorageRoot().DS.'snm-portal'.DS.'preview'),
					'preview_directive'=>'{{media url="snm-portal/preview/{0}"}}',
					'preview_url'=>$this->getStorageRootUrl().'snm-portal/preview/',
					'categorie'=>$this->convertPathToId($this->getStorageRoot().DS.'catalog'.DS.'category'),
					'categorie_directive'=>'{{media url="catalog/category/{0}"}}'
				);
    }
    public function checkFolder($folder,$bhtAccess=false)
    {
        $io = new Varien_Io_File();
        if (!$io->isWriteable($folder) && !$io->mkdir($folder))
        {
			Mage::log($this->__('Directory %s is not writable by server',$folder));
            return false;
		}
    	if ( $bhtAccess && !file_exists($folder.DS.'.htaccess'))
    	{
    		file_put_contents($folder.DS.'.htaccess',
    		"<IfModule mod_rewrite.c>\n"
			."RewriteEngine on\n"
			."RewriteRule .* ../../../protected_area [L]\n"
			."</IfModule>\n"
			."<IfModule !mod_rewrite.c>\n"
			."Order deny,allow\n"
			."Deny from all\n"
			."</IfModule>\n");
    	}
    	return true;
    }
    public function getStorage()
    {
        return Mage::getSingleton('auit_publicationbasic/filemanager_storage');
    }

    public function isCurrentRootPath()
    {
     	return $this->getStorageRoot() == $this->getCurrentPath();
    }

    public function isSubDir($dir)
    {
    	return strpos($dir,$this->getStorageRoot()) === 0 || strpos($dir,$this->getSkinRoot()) === 0;
    }
    public function getProtectedRoot()
    {
        $root = $this->correctPath( $this->getStorage()->getConfigData('protected_root') );
        return Mage::getConfig()->getOptions()->getMediaDir() . DS . $root;
    }
    public function getProtectedBaseUrl()
    {
        $root = $this->correctPath( $this->getStorage()->getConfigData('protected_root') );
        return Mage::getBaseUrl('media'). $root. DS;
    }
    public function getPreviewRoot()
    {
        $root = $this->correctPath( $this->getStorage()->getConfigData('preview_root') );
        return Mage::getConfig()->getOptions()->getMediaDir() . DS . $root;
    }
    public function getCatalogRoot()
    {
    	return $this->getStorageRoot().DS.'catalog'.DS.'category';
    }
    public function getMediaRoot()
    {
      // $root = $this->correctPath( $this->getStorage()->getConfigData('upload_root') );
        return Mage::getConfig()->getOptions()->getMediaDir();// . DS . $root;
    }
    public function getMediaBaseUrl()
    {
     //  $root = $this->correctPath( $this->getStorage()->getConfigData('upload_root') );
        return Mage::getBaseUrl('media');//. $root. DS;
    }

    public function getStorageRootUrl()
    {
        return Mage::getBaseUrl('media');
    }

    public function getSkinBaseUrl()
    {
        return Mage::getDesign()->getSkinBaseUrl(array('_theme' => 'default','_package'=>'default'));
    }

    public function getSkinRoot()
    {
    	return Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default','_package'=>'default'));
    }
    public function getSkinThemeBaseUrl()
    {
        return Mage::getDesign()->getSkinBaseUrl();
    }

    public function getSkinThemeRoot()
    {
    	return Mage::getDesign()->getSkinBaseDir();
    }

    public function getRootArea($path)
    {
    	if ( strpos($path,$this->getStorageRoot()) !== false )
    		return $this->getStorageRoot();
    	if ( strpos($path,$this->getMediaRoot()) !== false )
    		return $this->getMediaRoot();
    	if ( strpos($path,$this->getSkinThemeRoot()) !== false )
    		return $this->getSkinThemeRoot();
    	if ( strpos($path,$this->getSkinRoot()) !== false )
    		return $this->getSkinRoot();
    	if ( strpos($path,$this->getProtectedRoot()) !== false )
    		return $this->getProtectedRoot();
    	return $this->getStorageRoot();
    }
    public function getRootAreaName($path)
    {
    	if ( strpos($path,$this->getStorageRoot()) !== false )
    		return self::ROOT;
    	if ( strpos($path,$this->getMediaRoot()) !== false )
    		return self::MEDIAROOT;
    	if ( strpos($path,$this->getSkinThemeRoot()) !== false )
    		return self::SKINTHEMEROOT;
    	if ( strpos($path,$this->getSkinRoot()) !== false )
    		return self::SKINROOT;
    	if ( strpos($path,$this->getProtectedRoot()) !== false )
    		return self::PROTECTEDROOT;
    	return self::UNKNOWN;
    }
    public function getStorageRoot()
    {
    	return Mage::getBaseDir('media').DS.'snm-portal'.DS.'publication'.DS.'images';
//    	return $this->_rootPath;
  //  	return Mage::getConfig()->getOptions()->getMediaDir();
    }
    public function getTreeNodeName()
    {
        return 'id';
    }

    public function convertUrlToPathArea($url)
    {
    	$root = self::ROOT;
    	if ( strpos($url,$this->getMediaBaseUrl()) !== false )
    	{
    		return str_replace($this->getMediaBaseUrl(),self::MEDIAROOT.DS, $url);
    	}
        if ( strpos($url,$this->getStorageRootUrl()) !== false )
    	{
    		return str_replace($this->getStorageRootUrl(),self::ROOT.DS, $url);
    	}
    	if ( strpos($url,$this->getSkinThemeBaseUrl()) !== false )
    	{
    		return str_replace($this->getSkinThemeBaseUrl(),self::SKINTHEMEROOT.DS, $url);
    	}
    	if ( strpos($url,$this->getSkinBaseUrl()) !== false )
    	{
    		return str_replace($this->getSkinBaseUrl(),self::SKINROOT.DS, $url);
    	}
    	if ( strpos($url,$this->getProtectedBaseUrl()) !== false )
    	{
    		return str_replace($this->getProtectedBaseUrl(),self::PROTECTEDROOT.DS, $url);
    	}
    	return '';
    }
    public function convertPathToArea($path)
    {
    	$path = str_replace($this->getRootArea($path), $this->getRootAreaName($path).DS, $path);
        return str_replace(DS.DS, DS, $path);
    }
    public function urlEncode($url)
    {
    	return strtr(base64_encode($url), '=', '_');
    	return base64_encode($url).'_A';
    }
    public function urlDecode($url)
    {
    	$url = base64_decode(strtr($url, '_', '='));
    	return Mage::getSingleton('core/url')->sessionUrlVar($url);
    	return $url;
    	$url = substr($url,-2);

    	return base64_decode($url);
    }
    public function convertPathToId($path)
    {
    	return $this->urlEncode($this->convertPathToArea($path));
    }
    public function convertIdToPath($id,$encode=true)
    {
    	switch ( $id )
    	{
    		case self::ROOT:
//    		case 'AUITFBR':
    			return $this->getStorageRoot();
    			break;
    		case self::MEDIAROOT:
    			return $this->getMediaRoot();
    			break;
    		case self::SKINTHEMEROOT:
    			return $this->getSkinThemeRoot();
    			break;
    		case self::SKINROOT:
    			return $this->getSkinRoot();
    			break;
    		case self::PROTECTEDROOT:
    			return $this->getProtectedRoot();
    			break;
    	}
        $path = $encode?$this->urlDecode($id):$id;
        $fp = explode(DS,$path);
        switch (array_shift($fp))
        {
    		case self::MEDIAROOT:
    			$path = implode(DS,$fp);
	            $path = $this->getMediaRoot() .DS. $path;
    			break;
    		case self::SKINTHEMEROOT:
    			$path = implode(DS,$fp);
	            $path = $this->getSkinThemeRoot() .DS. $path;
    			break;
    		case self::SKINROOT:
    			$path = implode(DS,$fp);
	            $path = $this->getSkinRoot() .DS. $path;
    			break;
    		case self::PROTECTEDROOT:
    			$path = implode(DS,$fp);
	            $path = $this->getProtectedRoot() .DS. $path;
    			break;
    		case self::ROOT:
    			$path = implode(DS,$fp);
	            $path = $this->getStorageRoot() .DS. $path;
    			break;
    		default:
		        if (!strstr($path, $this->getStorageRoot())) {
		            $path = $this->getStorageRoot() . $path;
		        }
    			break;
        }
        return $path;
    }
    public function correctPath($path, $trim = true)
    {
        $path = strtr($path, "\\\/", DS . DS);
        if ($trim) {
            $path = trim($path, DS);
        }
        return $path;
    }
    public function convertPathToUrl($path)
    {
        return str_replace(DS, '/', $path);
    }
    public function getCurrentPath()
    {
        if (!$this->_currentPath) {
           	$currentPath = $this->getStorageRoot();
            $path = $this->_getRequest()->getParam($this->getTreeNodeName());
            if ($path) {
                $path = $this->convertIdToPath($path);
                if (is_dir($path)) {
                    $currentPath = $path;
                }
                else if (is_file($path) && is_dir(dirname($path))) {
                	$currentPath = dirname($path);
                }
            }
            /*
            $io = new Varien_Io_File();
            if (!$io->isWriteable($currentPath) && !$io->mkdir($currentPath)) {
            	Mage::throwException($this->__('Directory %s is not writable by server',$currentPath));
            }
            */
            $this->_currentPath = $currentPath;
        }
        return $this->_currentPath;
    }

    public function getUrl($path)
    {
    	$RelPath = str_replace($this->getRootArea($path).DS, '', $path);
    	switch ( $this->getRootAreaName($path) )
    	{
    		case self::SKINTHEMEROOT:
    			$path =  $this->getSkinThemeBaseUrl().$RelPath;
    			break;
    		case self::SKINROOT:
    			$path =  $this->getSkinBaseUrl().$RelPath;
    			break;
    		case self::MEDIAROOT:
    			$path = $this->getMediaBaseUrl().$RelPath;
    			break;
    		case self::PROTECTEDROOT:
    			$path = $this->getProtectedBaseUrl().$RelPath;
    			break;
    		case self::ROOT:
    			$path = Mage::getBaseUrl('media').'snm-portal/publication/images/'.$RelPath;
    			break;
    		default:
    			$path = '';
    			break;
    	}
    	return $path;
    }
    public function getDonwloadUrl($path)
    {
    	return $this->getUrl($path);
    }

    public function getRelative($path,$root='')
    {
    	if ( !$root )
    		$root= $this->getRootArea($path);
    	return str_replace($root.DS, '', $path);
    }
    public function getDirective($path,$quote='"')
    {
    	$root = $this->getRootAreaName($path);
    	$path = str_replace($this->getRootArea($path).DS, '', $path);
    	switch ( $root )
    	{
    		case self::SKINTHEMEROOT:
    			return  sprintf('{{skin url='.$quote.'%s'.$quote.'}}', $path);
    			break;
    		case self::SKINROOT:
    			return  sprintf('{{skin url='.$quote.'%s'.$quote.'}}', $path);
    			break;
    		case self::MEDIAROOT:
    			$root = $this->correctPath( $this->getStorage()->getConfigData('upload_root') );
    			return  sprintf('{{media url='.$quote.'%s/%s'.$quote.'}}', $root,$path);
    			break;
    		case self::PROTECTEDROOT:
    			$root = $this->correctPath( $this->getStorage()->getConfigData('protected_root') );
    			return  sprintf('{{media url="%s/%s"}}', $root,$path);
    			break;
    		case self::ROOT:
    			return  sprintf('{{media url='.$quote.'%s'.$quote.'}}', $path);
    			break;
    	}
    	return $path;
    }
    public function getMIMEType($fileName)
    {
    	$extension = pathinfo($fileName, PATHINFO_EXTENSION);
    	switch (strtolower($extension)) {
    		case 'gif':
    			$contentType = 'image/gif';
    			break;
    		case 'jpg':
    			$contentType = 'image/jpeg';
    			break;
    		case 'png':
    			$contentType = 'image/png';
    			break;
    		case 'svg':
    			$contentType = 'image/svg+xml';
    			break;
    		default:
    			$contentType = 'application/octet-stream';
    			break;
    	}
    	return $contentType;
    }
    public function getResolution($path,$default=72)
    {
    	$dpi=$default;
    	if ( is_file($path) && pathinfo($path, PATHINFO_EXTENSION) != 'svg')
    	{
    		$cacheKey = 'AUIT_PUBLICATION_FILE_' .md5 ($path);
    		$cache = Mage::app()->loadCache($cacheKey);
    		if ( Mage::app()->useCache('config') && $cache) {
    			$dpi=$cache;
    		} else {
    			if (extension_loaded('imagick')) {
    				try {
    					$image=new Imagick($path);
    					$res=$image->getImageResolution();
    					$dpi=array_shift($res);
    			
    				}catch (Exception $e )
    				{
    					Mage::log("getResolution not found : $path : ".$e->getMessage());
    				}
    			}
    			if (Mage::app()->useCache('config')) {
    				Mage::app()->saveCache($dpi, $cacheKey, array('config'));
    			}
    		}
    	}
    	return $dpi;
    }
}
