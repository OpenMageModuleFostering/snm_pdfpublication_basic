<?php
class AuIt_PublicationBasic_Model_Font
{
    // --- ATTRIBUTES ---

    /**
     *  @access private
     *  @var string
     */
    private $_fileName          = NULL ;                    //  Name of the truetype font file


    /**
     *  @access private
     *  @var string
     */
    private $_copyright         = NULL ;                    //  Copyright


    /**
     *  @access private
     *  @var string
     */
    private $_fontFamily        = NULL ;                    //  Font Family


    /**
     *  @access private
     *  @var string
     */
    private $_fontSubFamily     = NULL ;                    //  Font SubFamily


    /**
     *  @access private
     *  @var string
     */
    private $_fontIdentifier    = NULL ;                    //  Font Unique Identifier


    /**
     *  @access private
     *  @var string
     */
    private $_fontName          = NULL ;                    //  Font Name


    /**
     *  @access private
     *  @var string
     */
    private $_fontVersion       = NULL ;                    //  Font Version


    /**
     *  @access private
     *  @var string
     */
    private $_postscriptName    = NULL ;                    //  Postscript Name


    /**
     *  @access private
     *  @var string
     */
    private $_trademark         = NULL ;                    //  Trademark



    // --- OPERATIONS ---

    private function _returnValue($inString)
    {
        if (ord($inString) == 0) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($inString,"UTF-8","UTF-16");
            } else {
                return str_replace(chr(00),'',$inString);
            }
        } else {
            return $inString;
        }
    }   //  function _returnValue()

    /**
     *  @access public
     *  @return integer
     */
    public function getCopyright()
    {
        return $this->_returnValue($this->_copyright);
    }   //  function getCopyright()


    /**
     *  @access public
     *  @return integer
     */
    public function getFontFamily()
    {
        return $this->_returnValue($this->_fontFamily);
    }   //  function getFontFamily()


    /**
     *  @access public
     *  @return integer
     */
    public function getFontSubFamily()
    {
        return $this->_returnValue($this->_fontSubFamily);
    }   //  function getFontSubFamily()


    /**
     *  @access public
     *  @return integer
     */
    public function getFontIdentifier()
    {
        return $this->_returnValue($this->_fontIdentifier);
    }   //  function getFontIdentifier()


    /**
     *  @access public
     *  @return integer
     */
    public function getFontName()
    {
        return $this->_returnValue($this->_fontName);
    }   //  function getFontName()


    /**
     *  @access public
     *  @return integer
     */
    public function getFontVersion()
    {
        return $this->_returnValue($this->_fontVersion);
    }   //  function getFontVersion()


    /**
     *  @access public
     *  @return integer
     */
    public function getPostscriptName()
    {
        return $this->_returnValue($this->_postscriptName);
    }   //  function getPostscriptName()


    /**
     *  @access public
     *  @return integer
     */
    public function getTrademark()
    {
        return $this->_returnValue($this->_trademark);
    }   //  function getTrademark()


    /**
     *  Convert a big-endian word or longword value to an integer
     *
     *  @access private
     *  @return integer
     */
    private function _UConvert($bytesValue,$byteCount)
    {
        $retVal = 0;
        $bytesLength = strlen($bytesValue);
        for ($i=0; $i < $bytesLength; $i++) {
            $tmpVal = ord($bytesValue{$i});
            $t = pow(256,($byteCount-$i-1));
            $retVal += $tmpVal*$t;
        }

        return $retVal;
    }   //  function UConvert()


    /**
     *  Convert a big-endian word value to an integer
     *
     *  @access private
     *  @return integer
     */
    private function _USHORT($stringValue) {
        return $this->_UConvert($stringValue,2);
    }


    /**
     *  Convert a big-endian word value to an integer
     *
     *  @access private
     *  @return integer
     */
    private function _ULONG($stringValue) {
        return $this->_UConvert($stringValue,4);
    }


    /**
     *  Read the Font Attributes
     *
     *  @access private
     *  @return integer
     */
    private function readFontAttributes() {
        $fontHandle = fopen($this->_fileName, "rb");

        //  Read the file header
        $TT_OFFSET_TABLE = fread($fontHandle, 12);

        $uMajorVersion  = $this->_USHORT(substr($TT_OFFSET_TABLE,0,2));
        $uMinorVersion  = $this->_USHORT(substr($TT_OFFSET_TABLE,2,2));
        $uNumOfTables   = $this->_USHORT(substr($TT_OFFSET_TABLE,4,2));
//      $uSearchRange   = $this->_USHORT(substr($TT_OFFSET_TABLE,6,2));
//      $uEntrySelector = $this->_USHORT(substr($TT_OFFSET_TABLE,8,2));
//      $uRangeShift    = $this->_USHORT(substr($TT_OFFSET_TABLE,10,2));

        //  Check is this is a true type font and the version is 1.0
        if ($uMajorVersion != 1 || $uMinorVersion != 0) {
            fclose($fontHandle);
            throw new Exception($this->_fileName.' is not a Truetype font file') ;
        }

        //  Look for details of the name table
        $nameTableFound = false;
        for ($t=0; $t < $uNumOfTables; $t++) {
            $TT_TABLE_DIRECTORY = fread($fontHandle, 16);
            $szTag = substr($TT_TABLE_DIRECTORY,0,4);
            if (strtolower($szTag) == 'name') {
//              $uCheckSum  = $this->_ULONG(substr($TT_TABLE_DIRECTORY,4,4));
                $uOffset    = $this->_ULONG(substr($TT_TABLE_DIRECTORY,8,4));
//              $uLength    = $this->_ULONG(substr($TT_TABLE_DIRECTORY,12,4));
                $nameTableFound = true;
                break;
            }
        }

        if (!$nameTableFound) {
            fclose($fontHandle);
            throw new Exception('Can\'t find name table in '.$this->_fileName) ;
        }

        //  Set offset to the start of the name table
        fseek($fontHandle,$uOffset,SEEK_SET);

        $TT_NAME_TABLE_HEADER = fread($fontHandle, 6);

//      $uFSelector     = $this->_USHORT(substr($TT_NAME_TABLE_HEADER,0,2));
        $uNRCount       = $this->_USHORT(substr($TT_NAME_TABLE_HEADER,2,2));
        $uStorageOffset = $this->_USHORT(substr($TT_NAME_TABLE_HEADER,4,2));

        $attributeCount = 0;
        for ($a=0; $a < $uNRCount; $a++) {
            $TT_NAME_RECORD = fread($fontHandle, 12);

            $uNameID = $this->_USHORT(substr($TT_NAME_RECORD,6,2));
            if ($uNameID <= 7) {
//              $uPlatformID    = $this->_USHORT(substr($TT_NAME_RECORD,0,2));
                $uEncodingID    = $this->_USHORT(substr($TT_NAME_RECORD,2,2));
//              $uLanguageID    = $this->_USHORT(substr($TT_NAME_RECORD,4,2));
                $uStringLength  = $this->_USHORT(substr($TT_NAME_RECORD,8,2));
                $uStringOffset  = $this->_USHORT(substr($TT_NAME_RECORD,10,2));

                if ($uStringLength > 0) {
                    $nPos = ftell($fontHandle);
                    fseek($fontHandle,$uOffset + $uStringOffset + $uStorageOffset,SEEK_SET);
                    $testValue = fread($fontHandle, $uStringLength);

                    if (trim($testValue) > '') {
                        switch ($uNameID) {
                            case 0  : if ($this->_copyright == NULL) {
                                        $this->_copyright = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                            case 1  : if ($this->_fontFamily == NULL) {
                                        $this->_fontFamily = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                            case 2  : if ($this->_fontSubFamily == NULL) {
                                        $this->_fontSubFamily = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                            case 3  : if ($this->_fontIdentifier == NULL) {
                                        $this->_fontIdentifier = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                            case 4  : if ($this->_fontName == NULL) {
                                        $this->_fontName = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                            case 5  : if ($this->_fontVersion == NULL) {
                                        $this->_fontVersion = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                            case 6  : if ($this->_postscriptName == NULL) {
                                        $this->_postscriptName = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                            case 7  : if ($this->_trademark == NULL) {
                                        $this->_trademark = $testValue;
                                        $attributeCount++;
                                      }
                                      break;
                        }
                    }
                    fseek($fontHandle,$nPos,SEEK_SET);
                }
            }
            if ($attributeCount > 7) {
                break;
            }
        }

        fclose($fontHandle);
        return true;
    }




    /**
     *  @access constructor
     *  @return void
     */
    function setFile($fileName='') {

        $this->_fileName = $fileName;
        if (!file_exists($this->_fileName)) {
            throw new Exception($this->_fileName.' does not exist') ;
        } elseif (!is_readable($this->_fileName)) {
            throw new Exception($this->_fileName.' is not a readable file') ;
        }
        return $this->readFontAttributes();
    }   //  function constructor()


}   /* end of class fontAttributes */
