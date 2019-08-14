<?php

class AuIt_PublicationBasic_Block_Frame extends Mage_Core_Block_Template
{
	static function Inch2MM($val) {
		return $val*25.4;
	}
	static function MM2Inch($val)
	{
		return $val/25.4;
	}
	static function Px2Inch($val,$PixelsPerInch=96){
		return $val/$PixelsPerInch;
	}
	static function Inch2Px($val,$PixelsPerInch=96)
	{
		return $val*$PixelsPerInch;
	}
	static function Px2MM($val,$PixelsPerInch=96){
		return self::Inch2MM($val/$PixelsPerInch);
	}
	static function MM2Px($val,$PixelsPerInch=96)
	{
		return self::MM2Inch($val)*$PixelsPerInch;
	}
}
