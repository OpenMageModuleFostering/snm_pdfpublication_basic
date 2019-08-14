<?php
class My_Image_Adapter_Gd2 extends Varien_Image_Adapter_Abstract
{
    protected $_requiredExtensions = Array("gd");
    private static $_callbacks = array(
        IMAGETYPE_GIF  => array('output' => 'imagegif',  'create' => 'imagecreatefromgif'),
        IMAGETYPE_JPEG => array('output' => 'imagejpeg', 'create' => 'imagecreatefromjpeg'),
        IMAGETYPE_PNG  => array('output' => 'imagepng',  'create' => 'imagecreatefrompng'),
        IMAGETYPE_XBM  => array('output' => 'imagexbm',  'create' => 'imagecreatefromxbm'),
        IMAGETYPE_WBMP => array('output' => 'imagewbmp', 'create' => 'imagecreatefromxbm'),
    );

    public function open($filename)
    {
        $this->_fileName = $filename;
        $this->getMimeType();
        $this->_getFileAttributes();
        $this->_imageHandler = call_user_func($this->_getCallback('create'), $this->_fileName);

    }

    public function save($destination=null, $newName=null)
    {
        $fileName = ( !isset($destination) ) ? $this->_fileName : $destination;

        if( isset($destination) && isset($newName) ) {
            $fileName = $destination . "/" . $newName;
        } elseif( isset($destination) && !isset($newName) ) {
            $info = pathinfo($destination);
            $fileName = $destination;
            $destination = $info['dirname'];
        } elseif( !isset($destination) && isset($newName) ) {
            $fileName = $this->_fileSrcPath . "/" . $newName;
        } else {
            $fileName = $this->_fileSrcPath . $this->_fileSrcName;
        }

        $destinationDir = ( isset($destination) ) ? $destination : $this->_fileSrcPath;

        if( !is_writable($destinationDir) ) {
            try {
                $io = new Varien_Io_File();
                $io->mkdir($destination);
            } catch (Exception $e) {
                throw new Exception("Unable to write file into directory '{$destinationDir}'. Access forbidden.");
            }
        }

        // keep alpha transparency

        $isAlpha     = false;
        $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha);
        if ($isAlpha) {
	        if ( $this->_fileType == IMAGETYPE_PNG )
	        {
				//imagealphablending($this->_imageHandler, true);
				imagesavealpha($this->_imageHandler, true);
	        }else
	            $this->_fillBackgroundColor($this->_imageHandler);
        }



        $functionParameters = array();
        $functionParameters[] = $this->_imageHandler;
        $functionParameters[] = $fileName;

        // set quality param for JPG file type
        if (is_null($this->quality()) && $this->_fileType == IMAGETYPE_JPEG)
           	$this->quality(90);

        if (!is_null($this->quality()) && $this->_fileType == IMAGETYPE_JPEG)
        {
            $functionParameters[] = $this->quality();
        }

        // set quality param for PNG file type
        if (!is_null($this->quality()) && $this->_fileType == IMAGETYPE_PNG)
        {
            $quality = round(($this->quality() / 100) * 10);
            if ($quality < 1) {
                $quality = 1;
            } elseif ($quality > 10) {
                $quality = 10;
            }
            $quality = 10 - $quality;
            $functionParameters[] = $quality;
        }
        call_user_func_array($this->_getCallback('output'), $functionParameters);
    }

    public function display()
    {
        header("Content-type: ".$this->getMimeType());
        call_user_func($this->_getCallback('output'), $this->_imageHandler);
    }

    /**
     * Obtain function name, basing on image type and callback type
     *
     * @param string $callbackType
     * @param int $fileType
     * @return string
     * @throws Exception
     */
    private function _getCallback($callbackType, $fileType = null, $unsupportedText = 'Unsupported image format.')
    {
        if (null === $fileType) {
            $fileType = $this->_fileType;
        }
        if (empty(self::$_callbacks[$fileType])) {
            throw new Exception($unsupportedText);
        }
        if (empty(self::$_callbacks[$fileType][$callbackType])) {
            throw new Exception('Callback not found.');
        }
        return self::$_callbacks[$fileType][$callbackType];
    }

    private function _fillBackgroundColor(&$imageResourceTo)
    {
        // try to keep transparency, if any
        if ( $this->_keepTransparency) {
            $isAlpha = false;
            $transparentIndex = $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha);
            try {
                // fill truecolor png with alpha transparency
                if ($isAlpha) {
                    if (!imagealphablending($imageResourceTo, false)) {
                        throw new Exception('Failed to set alpha blending for PNG image.');
                    }
                    $transparentAlphaColor = imagecolorallocatealpha($imageResourceTo, 0, 0, 0, 127);
                    if (false === $transparentAlphaColor) {
                        throw new Exception('Failed to allocate alpha transparency for PNG image.');
                    }
                    if (!imagefill($imageResourceTo, 0, 0, $transparentAlphaColor)) {
                        throw new Exception('Failed to fill PNG image with alpha transparency.');
                    }
                    if (!imagesavealpha($imageResourceTo, true)) {
                        throw new Exception('Failed to save alpha transparency into PNG image.');
                    }

                    return $transparentAlphaColor;
                }
                // fill image with indexed non-alpha transparency
                elseif (false !== $transparentIndex) {
                    list($r, $g, $b)  = array_values(imagecolorsforindex($this->_imageHandler, $transparentIndex));
                    $transparentColor = imagecolorallocate($imageResourceTo, $r, $g, $b);
                    if (false === $transparentColor) {
                        throw new Exception('Failed to allocate transparent color for image.');
                    }
                    if (!imagefill($imageResourceTo, 0, 0, $transparentColor)) {
                        throw new Exception('Failed to fill image with transparency.');
                    }
                    imagecolortransparent($imageResourceTo, $transparentColor);
                    return $transparentColor;
                }
            }
            catch (Exception $e) {
                // fallback to default background color
            }
        }
        list($r, $g, $b) = $this->_backgroundColor;
        $color = imagecolorallocate($imageResourceTo, $r, $g, $b);
        if (!imagefill($imageResourceTo, 0, 0, $color)) {
            throw new Exception("Failed to fill image background with color {$r} {$g} {$b}.");
        }

        return $color;
    }

    /**
     * Gives true for a PNG with alpha, false otherwise
     *
     * @param string $fileName
     * @return boolean
     */

    public function checkAlpha($fileName)
    {
        return ((ord(file_get_contents($fileName, false, null, 25, 1)) & 6) & 4) == 4;
    }

    private function _getTransparency($imageResource, $fileType, &$isAlpha = false, &$isTrueColor = false)
    {
        $isAlpha     = false;
        $isTrueColor = false;
        // assume that transparency is supported by gif/png only
        if ((IMAGETYPE_GIF === $fileType) || (IMAGETYPE_PNG === $fileType)) {
            // check for specific transparent color
            $transparentIndex = imagecolortransparent($imageResource);
            if ($transparentIndex >= 0) {
                return $transparentIndex;
            }
            // assume that truecolor PNG has transparency
            elseif (IMAGETYPE_PNG === $fileType) {
                $isAlpha     = $this->checkAlpha($this->_fileName);
                $isTrueColor = true;
                return $transparentIndex; // -1
            }
        }
        if (IMAGETYPE_JPEG === $fileType) {
            $isTrueColor = true;
        }
        return false;
    }

    /**
     * Change the image size
     *
     * @param int $frameWidth
     * @param int $frameHeight
     */
    public function resize($frameWidth = null, $frameHeight = null)
    {
        if (empty($frameWidth) && empty($frameHeight)) {
            throw new Exception('Invalid image dimensions.');
        }

        // calculate lacking dimension
        if (!$this->_keepFrame) {
            if (null === $frameWidth) {
                $frameWidth = round($frameHeight * ($this->_imageSrcWidth / $this->_imageSrcHeight));
            }
            elseif (null === $frameHeight) {
                $frameHeight = round($frameWidth * ($this->_imageSrcHeight / $this->_imageSrcWidth));
            }
        }
        else {
            if (null === $frameWidth) {
                $frameWidth = $frameHeight;
            }
            elseif (null === $frameHeight) {
                $frameHeight = $frameWidth;
            }
        }

        // define coordinates of image inside new frame
        $srcX = 0;
        $srcY = 0;
        $dstX = 0;
        $dstY = 0;
        $dstWidth  = $frameWidth;
        $dstHeight = $frameHeight;
        if ($this->_keepAspectRatio) {
            // do not make picture bigger, than it is, if required
            if ($this->_constrainOnly) {
                if (($frameWidth >= $this->_imageSrcWidth) && ($frameHeight >= $this->_imageSrcHeight)) {
                    $dstWidth  = $this->_imageSrcWidth;
                    $dstHeight = $this->_imageSrcHeight;
                }
            }
            // keep aspect ratio
            if ($this->_imageSrcWidth / $this->_imageSrcHeight >= $frameWidth / $frameHeight) {
                $dstHeight = round(($dstWidth / $this->_imageSrcWidth) * $this->_imageSrcHeight);
            } else {
                $dstWidth = round(($dstHeight / $this->_imageSrcHeight) * $this->_imageSrcWidth);
            }
        }
        // define position in center (TODO: add positions option)
        $dstY = round(($frameHeight - $dstHeight) / 2);
        $dstX = round(($frameWidth - $dstWidth) / 2);

        // get rid of frame (fallback to zero position coordinates)
        if (!$this->_keepFrame) {
            $frameWidth  = $dstWidth;
            $frameHeight = $dstHeight;
            $dstY = 0;
            $dstX = 0;
        }

        // create new image
        $isAlpha     = false;
        $isTrueColor = false;
        $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha, $isTrueColor);
        if ($isTrueColor) {
            $newImage = imagecreatetruecolor($frameWidth, $frameHeight);
        }
        else {
            $newImage = imagecreate($frameWidth, $frameHeight);
        }
		if ( $isAlpha && $this->_fileType == IMAGETYPE_PNG )
		{
        	imagealphablending($newImage, false);
			imagesavealpha($newImage, true);

		}else {
        // fill new image with required color
        	$this->_fillBackgroundColor($newImage);
		}

        // resample source image and copy it into new frame
        imagecopyresampled($newImage, $this->_imageHandler, $dstX, $dstY, $srcX, $srcY, $dstWidth, $dstHeight, $this->_imageSrcWidth, $this->_imageSrcHeight);
        $this->_imageHandler = $newImage;
        $this->refreshImageDimensions();
    }

    public function rotate($angle)
    {
/*
        $isAlpha = false;
        $backgroundColor = $this->_getTransparency($this->_imageHandler, $this->_fileType, $isAlpha);
        list($r, $g, $b) = $this->_backgroundColor;
        if ($isAlpha) {
            $backgroundColor = imagecolorallocatealpha($this->_imageHandler, 0, 0, 0, 127);
        }
        elseif (false === $backgroundColor) {
            $backgroundColor = imagecolorallocate($this->_imageHandler, $r, $g, $b);
        }
        $this->_imageHandler = imagerotate($this->_imageHandler, $angle, $backgroundColor);
//*/
        $this->_imageHandler = imagerotate($this->_imageHandler, $angle, $this->imageBackgroundColor);
        $this->refreshImageDimensions();
    }

    public function watermark($watermarkImage, $positionX=0, $positionY=0, $watermarkImageOpacity=30, $repeat=false)
    {
        list($watermarkSrcWidth, $watermarkSrcHeight, $watermarkFileType, ) = getimagesize($watermarkImage);
        $this->_getFileAttributes();
        $watermark = call_user_func($this->_getCallback('create', $watermarkFileType, 'Unsupported watermark image format.'), $watermarkImage);

        $merged = false;

        if( $this->getWatermarkWidth() && $this->getWatermarkHeigth() && ($this->getWatermarkPosition() != self::POSITION_STRETCH) ) {
            $newWatermark = imagecreatetruecolor($this->getWatermarkWidth(), $this->getWatermarkHeigth());
            imagealphablending($newWatermark, false);
            $col = imagecolorallocate($newWatermark, 255, 255, 255);
            imagecolortransparent($newWatermark, $col);
            imagefilledrectangle($newWatermark, 0, 0, $this->getWatermarkWidth(), $this->getWatermarkHeigth(), $col);
            imagealphablending($newWatermark, true);
            imageSaveAlpha($newWatermark, true);
            imagecopyresampled($newWatermark, $watermark, 0, 0, 0, 0, $this->getWatermarkWidth(), $this->getWatermarkHeigth(), imagesx($watermark), imagesy($watermark));
            $watermark = $newWatermark;
        }

        if( $this->getWatermarkPosition() == self::POSITION_TILE ) {
            $repeat = true;
        } elseif( $this->getWatermarkPosition() == self::POSITION_STRETCH ) {

            $newWatermark = imagecreatetruecolor($this->_imageSrcWidth, $this->_imageSrcHeight);
            imagealphablending($newWatermark, false);
            $col = imagecolorallocate($newWatermark, 255, 255, 255);
            imagecolortransparent($newWatermark, $col);
            imagefilledrectangle($newWatermark, 0, 0, $this->_imageSrcWidth, $this->_imageSrcHeight, $col);
            imagealphablending($newWatermark, true);
            imageSaveAlpha($newWatermark, true);
            imagecopyresampled($newWatermark, $watermark, 0, 0, 0, 0, $this->_imageSrcWidth, $this->_imageSrcHeight, imagesx($watermark), imagesy($watermark));
            $watermark = $newWatermark;

        } elseif( $this->getWatermarkPosition() == self::POSITION_CENTER ) {
            $positionX = ($this->_imageSrcWidth/2 - imagesx($watermark)/2);
            $positionY = ($this->_imageSrcHeight/2 - imagesy($watermark)/2);
            imagecopymerge($this->_imageHandler, $watermark, $positionX, $positionY, 0, 0, imagesx($watermark), imagesy($watermark), $this->getWatermarkImageOpacity());
        } elseif( $this->getWatermarkPosition() == self::POSITION_TOP_RIGHT ) {
            $positionX = ($this->_imageSrcWidth - imagesx($watermark));
            imagecopymerge($this->_imageHandler, $watermark, $positionX, $positionY, 0, 0, imagesx($watermark), imagesy($watermark), $this->getWatermarkImageOpacity());
        } elseif( $this->getWatermarkPosition() == self::POSITION_TOP_LEFT  ) {
            imagecopymerge($this->_imageHandler, $watermark, $positionX, $positionY, 0, 0, imagesx($watermark), imagesy($watermark), $this->getWatermarkImageOpacity());
        } elseif( $this->getWatermarkPosition() == self::POSITION_BOTTOM_RIGHT ) {
            $positionX = ($this->_imageSrcWidth - imagesx($watermark));
            $positionY = ($this->_imageSrcHeight - imagesy($watermark));
            imagecopymerge($this->_imageHandler, $watermark, $positionX, $positionY, 0, 0, imagesx($watermark), imagesy($watermark), $this->getWatermarkImageOpacity());
        } elseif( $this->getWatermarkPosition() == self::POSITION_BOTTOM_LEFT ) {
            $positionY = ($this->_imageSrcHeight - imagesy($watermark));
            imagecopymerge($this->_imageHandler, $watermark, $positionX, $positionY, 0, 0, imagesx($watermark), imagesy($watermark), $this->getWatermarkImageOpacity());
        }

        if( $repeat === false && $merged === false ) {
            imagecopymerge($this->_imageHandler, $watermark, $positionX, $positionY, 0, 0, imagesx($watermark), imagesy($watermark), $this->getWatermarkImageOpacity());
        } else {
            $offsetX = $positionX;
            $offsetY = $positionY;
            while( $offsetY <= ($this->_imageSrcHeight+imagesy($watermark)) ) {
                while( $offsetX <= ($this->_imageSrcWidth+imagesx($watermark)) ) {
                    imagecopymerge($this->_imageHandler, $watermark, $offsetX, $offsetY, 0, 0, imagesx($watermark), imagesy($watermark), $this->getWatermarkImageOpacity());
                    $offsetX += imagesx($watermark);
                }
                $offsetX = $positionX;
                $offsetY += imagesy($watermark);
            }
        }

        imagedestroy($watermark);
        $this->refreshImageDimensions();
    }

    public function crop($top=0, $bottom=0, $right=0, $left=0)
    {
        if( $left == 0 && $top == 0 && $right == 0 && $bottom == 0 ) {
            return;
        }

        $newWidth = $this->_imageSrcWidth - $left - $right;
        $newHeight = $this->_imageSrcHeight - $top - $bottom;

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        if ($this->_fileType == IMAGETYPE_PNG) {
            $this->_saveAlpha($canvas);
        }

        imagecopyresampled($canvas, $this->_imageHandler, $top, $bottom, $right, $left, $this->_imageSrcWidth, $this->_imageSrcHeight, $newWidth, $newHeight);

        $this->_imageHandler = $canvas;
        $this->refreshImageDimensions();
    }

    public function checkDependencies()
    {
        foreach( $this->_requiredExtensions as $value ) {
            if( !extension_loaded($value) ) {
                throw new Exception("Required PHP extension '{$value}' was not loaded.");
            }
        }
    }

    private function refreshImageDimensions()
    {
        $this->_imageSrcWidth = imagesx($this->_imageHandler);
        $this->_imageSrcHeight = imagesy($this->_imageHandler);
    }

    function __destruct()
    {
        @imagedestroy($this->_imageHandler);
    }

    /*
     * Fixes saving PNG alpha channel
     */
    private function _saveAlpha($imageHandler)
    {
        $background = imagecolorallocate($imageHandler, 0, 0, 0);
        ImageColorTransparent($imageHandler, $background);
        imagealphablending($imageHandler, false);
        imagesavealpha($imageHandler, true);
    }
}
class AuIt_PublicationBasic_Gd2 extends My_Image_Adapter_Gd2
{
    public function createDummy($filename,$w=1,$h=1)
    {
        $this->_fileName = $filename;
        $this->getMimeType();
        $this->_getFileAttributes();
        $this->_imageHandler = @ImageCreate ($w,$h);
    }
	public function crop2($x=0, $y=0, $width=0, $height=0)
    {
        if( $x == 0 && $y == 0 && $width == 0 && $height == 0 ) {
            return;
        }
        $canvas = imagecreatetruecolor($width, $height);
        if ($this->_fileType == IMAGETYPE_PNG) {
            $this->_saveAlpha($canvas);
        }
        if ( ImageCopy ($canvas, $this->_imageHandler, 0, 0, $x,$y, $width,$height)) //$newWidth, $newHeight, $this->_imageSrcWidth, $this->_imageSrcHeight) )
        {
        	@imagedestroy($this->_imageHandler);
        	$this->_imageHandler = $canvas;

        }
        $this->rotate(0);
        // Ist privet $this->refreshImageDimensions();
    }
	function flip ( $mode )
	{

	    $width                        =    $this->_imageSrcWidth;
	    $height                       =    $this->_imageSrcHeight;
	    $src_x                        =    0;
	    $src_y                        =    0;
	    $src_width                    =    $width;
	    $src_height                   =    $height;

	    switch ( $mode )
	    {

	        case '1': //vertical
	            $src_y                =    $height -1;
	            $src_height           =    -$height;
	        break;

	        case '2': //horizontal
	            $src_x                =    $width -1;
	            $src_width            =    -$width;
	        break;

	        case '3': //both
	            $src_x                =    $width -1;
	            $src_y                =    $height -1;
	            $src_width            =    -$width;
	            $src_height           =    -$height;
	        break;

	        default:
	            return $imgsrc;

	    }
	    $canvas  =    imagecreatetruecolor ( $width, $height );
	    imagecopyresampled ( $canvas, $this->_imageHandler, 0, 0, $src_x, $src_y , $width, $height, $src_width, $src_height );
        $this->_imageHandler = $canvas;
        $this->refreshImageDimensions2();

	}
    private function refreshImageDimensions2()
    {
        $this->_imageSrcWidth = imagesx($this->_imageHandler);
        $this->_imageSrcHeight = imagesy($this->_imageHandler);
    }

}
class AuIt_PublicationBasic_Helper_Dirdirective  extends Mage_Core_Helper_Abstract
{
	public function checkDefaults()
	{
		$previewFolder = Mage::helper('auit_publicationbasic/filemanager')->getStorageRoot().DS.'snm-portal'.DS.'preview';
		if ( !$this->getDirectiveFor($previewFolder,true) )
		{
			$fromFile = Mage::getConfig()->getModuleDir('etc', 'AuIt_PublicationBasic');
			@copy(	$fromFile.DS.'dir.directive.xml',$this->getDirectiveFilename($previewFolder));
		}
	}
	public function getDirectiveFilename($dir)
	{
		if ( !Mage::helper('auit_publicationbasic/filemanager')->isSubDir($dir) )
			return false;
	   	return $dir.DS.'.directive.xml';
	}
	public function getDirectiveFor($dir,$bexact=false)
	{
		do {
			if ( !Mage::helper('auit_publicationbasic/filemanager')->isSubDir($dir) )
				return false;
	    	$directive = $dir.DS.'.directive.xml';
    		if ( file_exists($directive) )
				return $directive;
			if ( $bexact )
				return false;
			$dir = dirname($dir);
		}while (true);
	}
    public function execute($filename,$mode='upload')
    {
    	$dir = dirname($filename);
    	$directive = $this->getDirectiveFor($dir);
    	if ( !$directive )
    		return;
    	/**
    	$directive = $dir.DS.'.directive.xml';
    	if ( !file_exists($directive) )
    		return;
    		*/
		$xml = new DOMDocument();
		$xml->preserveWhiteSpace =false;
		if ( !@$xml->load($directive) )
		{
			Mage::log("Can't load file $filename");
			return;
		}
		$params=array();
		$params['FULLNAME']=$filename;
		$params['DIR']=$dir;
		$params['FILENAME']=basename($filename);
		return $this->executeDirective($xml,$mode,$params);
    }
    public function executeDirectiveString($xmlString,$target,&$params)
    {
		$xml = new DOMDocument();
		$xml->preserveWhiteSpace =false;
		if ( !@$xml->load($xmlString) )
		{
			Mage::log("Can't load xmlstring $xmlString");
			return false;
		}
    	return $this->executeDirective($xml,$mode,$params);
    }
    public function executeDirective(DOMDocument $xml,$target,&$params)
    {
		$xpath = new DOMXPath($xml);
    	$target = $xpath->query('//target[@name="'.$target.'"]');
		try {
			foreach ($target as $entry)
			{
				foreach ( $entry->childNodes as $node )
				{
					$tageName = $node->nodeName;
					switch ($tageName)
					{
						case 'transform':
							$this->transformDirective($node,$params);
							break;
						case 'copy':
							$this->copyDirective($node,$params);
							break;
						case 'mkdir':
							$this->mkdirDirective($node,$params);
							break;
						case 'load':
							$this->loadDirective($node,$params);
							break;
						case 'replacefile':
							$this->replacefileDirective($node,$params);
							break;
					}
				}
				break;
			}
		}
		catch (Exception  $e )
		{
			Mage::log($e->getMessage());
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			return false;
		}
		return true;
    }
    protected function createDirectory($newdir)
    {
        $io = new Varien_Io_File();
        if (!$io->isWriteable($newdir) && !$io->mkdir($newdir))
        {
        	Mage::throwException(Mage::helper('cms')->__('Directory %s is not writable by server',$newdir));
		}
    }
    protected function getTempDir()
    {
    	return Mage::getConfig()->getOptions()->getMediaDir().DS.'tmp'.DS.'snm-portal';
    }
    protected function cleanDir($tmpDirectory,$prefix='')
    {
    	if ( !is_dir($tmpDirectory))
    		return;
    	$yesterday = time() - 24*3600;
    	$io = new Varien_Io_File();
    	try {
        	$io->checkAndCreateFolder($tmpDirectory);
            $io->open(array('path'=>$tmpDirectory));
            $del=array();
	        if ($dh = opendir($tmpDirectory)) {
	            while (($entry = readdir($dh)) !== false) {
	                $fullpath = $tmpDirectory . DIRECTORY_SEPARATOR . $entry;
	                if( !is_dir($fullpath) ) {
                    	continue;
	                }
	                if ( !$prefix || substr($entry,0,strlen($prefix))==$prefix)
	                {
                		if ( filectime($fullpath) < $yesterday )
                		{
            				$del[]=$fullpath;
	                	}
	            	}
	            }
	        }
	        foreach ( $del as $entry)
	        {
	        	$io->rmdir($entry, true);
	        }

        } catch (Exception $e) {
        	Mage::log($e->getMessage());
        }
    }
    static public function getNewFileName($destFile)
    {
        $fileInfo = pathinfo($destFile);
        if( file_exists($destFile) ) {
            $index = 1;
            $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            while( file_exists($fileInfo['dirname'] . DIRECTORY_SEPARATOR . $baseName) ) {
                $baseName = $fileInfo['filename']. '_' . $index . '.' . $fileInfo['extension'];
                $index ++;
            }
            $destFileName = $baseName;
        } else {
            return $fileInfo['basename'];
        }

        return $destFileName;
    }
    protected function loadDirective(DOMElement $node,array &$params)
    {
    	$from = $node->getAttribute('from');
    	if ( !$from )
    		return;
    	$to = $node->getAttribute('to');
    	if ( !$to )
    	{
    		$tmpDir = $this->getTempDir();
    		$this->cleanDir($tmpDir,'snm-portal-');
			$to = $tmpDir.DS.'snm-portal-'.session_id();
    	}
    	$this->createDirectory($to);
    	$toFile = $to.DS.basename($from);
    	$toFile = $to.DS.self::getNewFileName($toFile);
    	file_put_contents($toFile,file_get_contents($from));
    	$params['FULLNAME']=$toFile;
		$params['DIR']=$to;
		$params['FILENAME']=basename($toFile);
    }
    protected function replacefileDirective(DOMElement $node,array &$params)
    {
    	$helper= Mage::helper('auit_publicationbasic/filemanager');
    	$from = $node->getAttribute('from');
    	$from =  $helper->convertIdToPath($helper->convertUrlToPathArea($from),false);
    	if ( !$from || !file_exists($from))
    		return;
    	$to = $node->getAttribute('to');
    	$to =  $helper->convertIdToPath($helper->convertUrlToPathArea($to),false);
    	if ( !$to || !file_exists($to))
    	{
    		return;
    	}
    	$this->execute($to,'replace');
    	$bok = @copy( $from , $to );
        if (!$bok) {
            Mage::throwException(Mage::helper('cms')->__('replacefileDirective: can\'t copy from "%s" to "%s"', $from , $to));
        }
        $this->thumbnailDelete($to);
    	$params['FULLNAME']=$to;
		$params['DIR']=dirname($to);
		$params['FILENAME']=basename($to);
    }
    protected function mkdirDirective(DOMElement $node,array $params)
    {
    	$subdir = $node->getAttribute('dir');
    	if ( !$subdir )
    		return;
    	$newdir = $params['DIR'].DS.$subdir;
    	$this->createDirectory($newdir);
    }
    protected function copyDirective(DOMNode $node,array $params)
    {
    	$newFile = $params['DIR'];
    	$subdir = $node->getAttribute('todir');
    	if ( $subdir )
	    	$newFile .= DS.$subdir;
	    $newFile .= DS.$params['FILENAME'];
	    $newdir = dirname($newFile);
		$file = $params['FULLNAME'];
        $ioAdapter = new Varien_Io_File();
        if (!$ioAdapter->fileExists($file)) {
            Mage::throwException(Mage::helper('cms')->__('File "%s" don\'t exist', $file));
        }
        $ioAdapter->setAllowCreateFolders(true);
        $ioAdapter->createDestinationDir($newdir);
        return $ioAdapter->cp($file,$newFile);
    }
    protected function transformDirective(DOMNode $transNode,array $params)
    {
    	$filename = $params['FULLNAME'];
		$image = new AuIt_PublicationBasic_Gd2();

    	$image->open($filename);

    	if ( 1 )
    	foreach ( $transNode->childNodes as $node )
		{
			$tageName = $node->nodeName;
			switch ($tageName)
			{
				case 'resize':
					$this->transformResize($image,$node,$params);
					break;
				case 'rotate':
					$this->transformRotate($image,$node,$params);
					break;
				case 'crop':
					$this->transformCrop($image,$node,$params);
					break;
				case 'fliph':
					$this->transformFlipH($image,$node,$params);
					break;
				case 'flipv':
					$this->transformFlipV($image,$node,$params);
					break;
				case 'watermark':
					$this->transformWatermark($image,$node,$params);
					break;
			}
		}
		if ( $transNode->getAttribute('tofile') )
			$image->save($transNode->getAttribute('tofile'));
		else
			$image->save($filename);
    }
    protected function getBoolValue($value)
    {
    	$value = strtolower(trim($value));
    	if ( $value == 'true' or $value > 0 )	return true;
    	return false;
    }
    protected function getColorValue($value)
    {
    	$rgb=array();
    	$rgb[0] = hexdec(substr($value,1,2));
    	$rgb[1] = hexdec(substr($value,3,2));
    	$rgb[2] = hexdec(substr($value,5,2));
    	return $rgb;
    }
    protected function transformResize(Varien_Image_Adapter_Abstract $image,DOMNode $node,array $params)
    {
    	if ( $node->hasAttribute('keepAspectRatio') )
    		$image->keepAspectRatio($this->getBoolValue($node->getAttribute('keepAspectRatio')));
    	if ( $node->hasAttribute('keepFrame') )
    		$image->keepFrame($this->getBoolValue($node->getAttribute('keepFrame')));
    	if ( $node->hasAttribute('keepTransparency') )
    		$image->keepTransparency($this->getBoolValue($node->getAttribute('keepTransparency')));
    	if ( $node->hasAttribute('constrainOnly') )
    		$image->constrainOnly($this->getBoolValue($node->getAttribute('constrainOnly')));
    	if ( $node->hasAttribute('backgroundColor') )
    		$image->backgroundColor($this->getColorValue($node->getAttribute('backgroundColor')));

    	$frameWidth = null;
    	$frameHeight= null;

    	if ( $node->hasAttribute('width') )
    	{
    		$frameWidth = $node->getAttribute('width');
    	}
    	if ( $node->hasAttribute('height') )
    	{
    		$frameHeight = $node->getAttribute('height');
    	}
    	$image->resize($frameWidth, $frameHeight);
    }
    protected function transformRotate(Varien_Image_Adapter_Abstract $image,DOMNode $node,array $params)
    {
    	/*<rotate angle="45"/>*/
    	if ( $node->hasAttribute('angle') )
    	{
    		$angle = (int)$node->getAttribute('angle');
    		$image->rotate($angle);
    	}
    }
    protected function transformCrop(Varien_Image_Adapter_Abstract $image,DOMNode $node,array $params)
    {
    	/* <crop top="0" bottom="0" right="0" left="0"/>*/
    	$top="0"; $width="0"; $height="0"; $left="0";
    	if ( $node->hasAttribute('top') )
    		$top = $node->getAttribute('top');
    	if ( $node->hasAttribute('width') )
    		$width = $node->getAttribute('width');
    	if ( $node->hasAttribute('height') )
    		$height = $node->getAttribute('height');
    	if ( $node->hasAttribute('left') )
    		$left = $node->getAttribute('left');
		$image->crop2($left,$top,$width,$height);
    }
    protected function transformWatermark(Varien_Image_Adapter_Abstract $image,DOMNode $node,array $params)
    {
    	if ( $node->hasAttribute('image') )
    	{
    		$file =$node->getAttribute('image');
    		$watermarkImage = Mage::helper('auit_publicationbasic/filemanager')->getMediaRoot().DS.$file;
    		if ( file_exists($watermarkImage) )
    		{
	    		$positionX=0;$positionY=0; $watermarkImageOpacity=30; $repeat=false;
	    		if ( $node->hasAttribute('repeat') )
	    			$repeat = $this->getBoolValue($node->getAttribute('repeat'));
	    		if ( $node->hasAttribute('positionX') )
	    			$positionX = (int)$node->getAttribute('positionX');
	    		if ( $node->hasAttribute('positionY') )
	    			$positionY = (int)$node->getAttribute('positionY');
	    		if ( $node->hasAttribute('watermarkImageOpacity') )
	    			$watermarkImageOpacity = (int)$node->getAttribute('watermarkImageOpacity');

	    		$image->watermark($watermarkImage, $positionX, $positionY, $watermarkImageOpacity, $repeat);
    		}
    	}
    	/*	<watermark image="pfad" positionX="0" positionY="0" watermarkImageOpacity="30" repeat="false"/>*/

    }
    protected function transformFlipH(Varien_Image_Adapter_Abstract $image,DOMNode $node,array $params)
    {
   		$image->flip(2);
    }
	protected function transformFlipV(Varien_Image_Adapter_Abstract $image,DOMNode $node,array $params)
    {
   		$image->flip(1);
	}
	public function thumbnailDelete($filename)
	{

		$thum = $this->getThumbPath($filename);
		if ( file_exists($thum))
		{
			@unlink($thum);
		}
	}
    public function getThumbPath($filename)
    {
		$helper = Mage::helper('auit_publicationbasic/filemanager');
    	$path = $filename;
    	$rootPath = $helper->getRootArea($path);
    	$AreaName = $helper->getRootAreaName($path);
    	if ( $AreaName == AuIt_PublicationBasic_Helper_Filemanager::SKINROOT )
    		$rootPath = Mage::getBaseDir('skin');
    	$directory = Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS.'cache'.DS.'thumbs'.DS.$AreaName;
    	$thumbFile = str_replace($rootPath, $directory, $path);
    	return $thumbFile;
    }
    public function getDocumentThumbUrl($filename,$size='')
    {
        $allowed = Mage::getSingleton('auit_publicationbasic/filemanager_storage')->getAllowedExtensions('document');
        $thumbFile='';
        $finfo = pathinfo($filename);
        $fext = strtolower($finfo['extension']);
        if ( in_array($fext,$allowed))
        {
        	if ( file_exists(Mage::getBaseDir().'/js/auit/publicationbasic/images/thumbnail/'.$size.$fext.'.png') )
        	{
        		$thumbFile=  Mage::getBaseUrl('js').'auit/publicationbasic/images/thumbnail/'.$size.$fext.'.png';
        	}
        }
        return $thumbFile;
    }

    public function getThumbUrl($filename)
    {
        if ( pathinfo($filename, PATHINFO_EXTENSION) == 'svg')
        {
			return str_replace(Mage::getBaseDir('media').DS,Mage::getBaseUrl('media'), $filename);
        }
    	$thumbFile = $this->getThumbPath($filename);
    	
    	if( !is_file($thumbFile)) {
    		$allowed = Mage::getSingleton('auit_publicationbasic/filemanager_storage')->getAllowedExtensions('image');
        	$finfo = pathinfo($filename);
        	$fext = strtolower($finfo['extension']);
        	if ( in_array($fext,$allowed))
        	{
        		$this->createThumbnail($filename,$thumbFile);
        	}
        	else {
        		$allowed = Mage::getSingleton('auit_publicationbasic/filemanager_storage')->getAllowedExtensions('document');
        		$thumbFile='';
        		if ( in_array($fext,$allowed))
        		{
        			if ( file_exists(Mage::getBaseDir().'/js/auit/publicationbasic/images/thumbnail/'.$fext.'.png') )
        			{
        				$thumbFile=  Mage::getBaseUrl('js').'auit/publicationbasic/images/thumbnail/'.$fext.'.png';
        			}
        		}
        		if ( !$thumbFile )
        			$thumbFile= Mage::getBaseUrl('js').'auit/publicationbasic/images/thumbnail/empty.png';
        		return $thumbFile;
        	}
    	}
    	if( is_file($thumbFile)) {
			return str_replace(Mage::getBaseDir('media').DS,Mage::getBaseUrl('media'), $thumbFile);
		}
    	return '';
    }
    public function createThumbnail($filename,$thumbFile)
    {
        $thumbsPath = dirname($thumbFile);
        $io = new Varien_Io_File();
        if ($io->isWriteable($thumbsPath)) {
            $io->mkdir($thumbsPath);
        }
        try {
	        $image = Varien_Image_Adapter::factory('GD2');
	        $image->keepAspectRatio(true);
	        $image->constrainOnly(true);
	        $image->keepTransparency(true);
	        $image->backgroundColor(array(255,255,255));
	        $image->open($filename);
	        //$image->keepFrame(true);
	        $width = Mage::getStoreConfig('auit_publicationbasic/wysiwyg/browser_resize_width');
	        $height = Mage::getStoreConfig('auit_publicationbasic/wysiwyg/browser_resize_height');
	        $image->resize($width, $height);
	        $image->save($thumbFile);
        }
        catch (Exception $e)
        {
        	Mage::log("createThumbnail($filename) failed (create dummy): ".$e->getMessage());
        	try {
        		$image = new AuIt_PublicationBasic_Gd2();

        		$image->createDummy($filename);
	        	$image->save($thumbFile);
        	}
	        catch (Exception $e)
	        {
	        }
        }
    }

}