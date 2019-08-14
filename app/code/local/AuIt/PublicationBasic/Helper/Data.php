<?php
class AuIt_PublicationBasic_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_templateProcessor = null;
	
	const PRINT_TYPE_PROMO=11;
	const TEMPLATE_UNKNOWN=0;
	const TEMPLATE_PRODUCT=1;
	const TEMPLATE_STATIC=2;
	const TEMPLATE_COUPON=3;
	
	static $_sortField;
	public function sortBy(&$items,$field)
	{
		self::$_sortField=$field;
		$oldValue = setlocale ( LC_COLLATE   , Mage::app()->getLocale()->getLocaleCode().'.UTF-8' );
		uasort($items, array($this,'mystrcoll')) ;
		return $this;
	}
	static function mystrcoll($item1, $item2)
	{
		return strcoll($item1[self::$_sortField], $item2[self::$_sortField]);
	}
    public static function log($message, $level = null) {
         Mage::log($message, null, 'snm-cronjob.log', true);
    }
    public function checkMediaFolder()
    {
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	foreach ( array(
    			Mage::getBaseDir('media').DS.'snm-portal'.DS.'fonts',
    			Mage::getBaseDir('media').DS.'snm-portal'.DS.'publication'.DS.'images',
       			Mage::getBaseDir('media').DS.'snm-portal'.DS.'publication'.DS.'jobs',
    	) as $newdir)
    	{
			if (!$helper->checkFolder ( $newdir )) {
				Mage::getSingleton('adminhtml/session')->addError('Directory %s is not writable by server',$newdir);
			}
    	}
    }
	public function cleanLayoutData($data)
	{
		$obj=array();
		try {
			$data = str_replace('$type','$type_',$data);
			$obj = Mage::helper('core')->jsonDecode(trim($data));
		}catch (Exception $e)
		{
			Mage::log("<br/>Decode: failed : ".$e->getMessage());
			Mage::log($data);
			echo "<br/>Decode: failed : ".$e->getMessage();
		}
		if ( !isset($obj['preview_sku']) )
			$obj['preview_sku']='';
		if ( !isset($obj['preview_store']) )
			$obj['preview_store']=0;
			
		if ( isset($obj['showCurrentSpread']) )
			unset($obj['showCurrentSpread']);
		return $obj;
	}
	public function getObjectData($sku,$type,$storeId=0,$bliveData=false,$ball=false,$printMode='x')
	{
		$data=array();
		
		switch ($type)
		{
			case AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT:
				
				if ( 0 && !$ball )
				{
					$product = Mage::getModel('catalog/product');
					$product->setStoreId($storeId)->load($product->getIdBySku($sku));
					$data[] = $this->getProductData($product,$bliveData);
				}else {
				
					$skuids=array();
					foreach ( explode(',',$sku) as $item )
					{
						if ( trim($item) )
						{
							$skuids[]=trim($item);
				//			if ( $printMode == 'preview' )
							//	break;
						}
					}
					if ( count($skuids) ){
						$collection = Mage::getResourceModel('catalog/product_collection')
						->setStoreId($storeId)
						->addAttributeToFilter('sku',array('in'=>$skuids))
						->addAttributeToSelect('*');
						$h=array();
						foreach ( $collection as $item )
							$h[trim($item->getSku())]=$this->getProductData($item,$bliveData);
						foreach ( explode(',',$sku) as $sid )
						{
							$sid=trim($sid);
							if ( isset($h[$sid]))
							$data[] = $h[$sid];
						}
					}
					if ( !count($data) )
					{	
						if ( $printMode == 'preview' )
						{
							
							$collection = Mage::getResourceModel('catalog/product_collection')
							->setStoreId($storeId)
							->addAttributeToSelect('*')
							//->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
							->addAttributeToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
							->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
							->setPageSize(1)
            				->setCurPage(1);
							
							foreach ( $collection as $item ){
								$data[] = $this->getProductData($item,$bliveData);
							}
						}
						if ( count($data) == 0 ) {
							$product = Mage::getModel('catalog/product');
							$data[] = $this->getProductData($product,$bliveData);
						}
					}
				}	
				break;
			case AuIt_PublicationBasic_Helper_Data::TEMPLATE_STATIC:
				$data[] = array('static'=>1);
				break;
			case AuIt_PublicationBasic_Helper_Data::TEMPLATE_COUPON:
				$rule = Mage::getModel('salesrule/rule');
				$rule->load($sku);
				$d = array();
				$rule->getData();
		
				$locale = Mage::app()->getLocale();
				$format = $locale->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
				$collection =null;
				foreach ( $this->_getCouponAttributes() as $code => $name )
				{
					$v = $rule->getData($code);
					switch ( $code )
					{
						case 'from_date':
						case 'to_date':
							$v = ''.Mage::helper('core')->formatDate($v, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
							break;
						case 'coupon_code':
							$v = trim($rule->getCouponCode());
							if ( $rule->getUseAutoGeneration() && !$ball )
							{
								$collection = Mage::getResourceModel('salesrule/coupon_collection');
								/** @var Mage_SalesRule_Model_Resource_Coupon_Collection */
								$collection->setPageSize(1)
								->addRuleToFilter($rule);
								foreach ( $collection as $child ) {
									$v = trim($child->getCode());
									break;
								}
							}
							break;
					}
					$d[$code]=$v;
				}
				$ruleData=$d;
				if ( $ball )
				{
					if ( $rule->getUseAutoGeneration())
					{
						$collection = Mage::getResourceModel('salesrule/coupon_collection');
						/** @var Mage_SalesRule_Model_Resource_Coupon_Collection */
						$collection->addRuleToFilter($rule);
						foreach ( $collection as $child ) {
							$ruleData['coupon_code']=trim($child->getCode());
							$data[]=$ruleData;
						}
					}else 
						$data[]=$ruleData;
				}else {
					$data[]=$ruleData;
				}
				break;
		}
		return $data;
	}
	protected function _getStoreId($storeId=0)
	{
		$sid=0;
		foreach ( Mage::app()->getStores(false,true) as $key => $store )
		{
			if ( $key == $storeId )
			{
				$sid = $store->getId();
				break;
			}
		}
		if ( !$sid )
		{
			foreach ( Mage::app()->getStores(false,true) as $key => $store )
			{
				$sid = $store->getId();
				break;
			}
		}
		return $sid;
	}
	public function getPreviewDataFromStore($sku,$type,$storeId=0)
	{
		$lsku = $sku;
		$lstoreId = $storeId;
		if ( !$sku )
			return array();
		$storeId = $this->_getStoreId($storeId);
		$appEmulation = Mage::getSingleton('core/app_emulation');
		$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		$data=$this->getObjectData($sku,$type,$storeId);
		$data = array_shift($data);
		// Stop store emulation process
		$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		// BUG translation from modulcodes
		$initialDesign = $initialEnvironmentInfo->getInitialDesign();
		Mage::getSingleton('core/translate')->init($initialDesign['area'], false);
		
		$data['preview_sku']=$lsku;
		$data['preview_store']=$lstoreId;
		
		return $data;
	}
	public function getPreviewData($sku,$type)
	{
		$lsku = $sku;
		$lstoreId = Mage::app()->getStore()->getName();
		if ( !$sku )
			return array();
//		$storeId = $this->_getStoreId($storeId);
	//	$appEmulation = Mage::getSingleton('core/app_emulation');
		//$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		$storeId=Mage::app()->getStore()->getId();
		$data=$this->getObjectData($sku,$type,$storeId);
		$data = array_shift($data);
		// Stop store emulation process
	//	$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		// BUG translation from modulcodes
		//$initialDesign = $initialEnvironmentInfo->getInitialDesign();
		//Mage::getSingleton('core/translate')->init($initialDesign['area'], false);
		
		$data['preview_sku']=$lsku;
		$data['preview_store']=$lstoreId;
		
		return $data;
	}
	public function getStaticBlockHTML($identifier,$storeId=0)
	{
		$html = '';
	//	$storeId = $this->_getStoreId($storeId);
		//$appEmulation = Mage::getSingleton('core/app_emulation');
		//$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		
		$html = Mage::getSingleton('core/layout')->createBlock('cms/block')
			->setBlockId($identifier)
			->toHtml();
		//$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		// BUG translation from modulcodes
		//$initialDesign = $initialEnvironmentInfo->getInitialDesign();
		//Mage::getSingleton('core/translate')->init($initialDesign['area'], false);
		return $html;		
	}
	public function getGeneratorHTML($param,$cls,$identifier,$pid,$storeId=0)
	{
		
		$html = '';
//		$storeId = $this->_getStoreId($storeId);
		//$appEmulation = Mage::getSingleton('core/app_emulation');
		//$initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
		
		if ( Mage::registry('product'))
			Mage::unregister('product');
		
		$_product = Mage::getModel('catalog/product');
		$_product->setStoreId(Mage::app()->getStore()->getId());
		$pid = $_product->getIdBySku($pid);
		if ( $pid )
			$_product->load($pid);
		Mage::register('product',$_product);
		
		$block = Mage::getSingleton('core/layout')->createBlock('auit_publicationbasic/generator')
		->setParameter($param)
		->setBoxClass($cls)
		->setGeneratorId($identifier);
		//->setProductSku($pid)
		
		//___store=german&___from_store=default
		//$appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
		// BUG translation from modulcodes
//		$initialDesign = $initialEnvironmentInfo->getInitialDesign();
	//	Mage::getSingleton('core/translate')->init($initialDesign['area'], false);
		
		return array('html'=>$block->toHtml(),'childs'=>$block->getBoxes());
	}	
	public function mbtrim( $string )
	{
		$nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
		$string = preg_replace( "/(^\s+)|(\s+$)/us", "", $string );
		return preg_replace( "/(^".$nonEscapableNbspChar."+)|(".$nonEscapableNbspChar."+$)/us", "", $string );
	}
	public function formatAsSpanPrice($price,$stripSpace=true,$withSypmbol=true)
	{
		
		$currency = Mage::app()->getStore()->getCurrentCurrency();
		if ( $currency )
		{
			$code = Mage::app()->getStore()->getCurrentCurrencyCode();
			$symbol ='';
			if ( Mage::app()->getLocale()->currency($code) )
				$symbol = Mage::app()->getLocale()->currency($code)->getSymbol();
			
			$pf=$currency->formatTxt($price);
			$pf2=$this->mbtrim(str_replace($symbol,'',$pf));
			/*
			 * $data['_price_formated3']=$pf2;
			 */
			$dp = substr($pf2, -3,1);
			if ( $dp == '.' || $dp == ',' )
			{
				$tmp = substr($pf2,0,-3);
				$tmp.= '<span¤class="bl-price-precision">'.substr($pf2,-3).'</span>';
				$pf = str_replace($pf2,$tmp,$pf);
				if ( $withSypmbol )
					$pf = str_replace($symbol,'<span¤class="bl-price-symbol">'.$symbol.'</span>',$pf);
				else 
					$pf = str_replace($symbol,'',$pf);
				if ( $stripSpace )
				{
					$nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
					$pf = preg_replace( "/(\s)/us", "", $pf );
					$pf = preg_replace( "/(".$nonEscapableNbspChar.")/us", "", $pf );
				}
				$pf = str_replace('¤',' ',$pf);
				return $pf;
			}
		}
		return $price;
	}
	protected function _getTemplateProcessor()
	{
		if (null === $this->_templateProcessor) {
			$this->_templateProcessor = Mage::helper('catalog')->getPageTemplateProcessor();
		}
		return $this->_templateProcessor;
	}
	
	public function getProductData($product,$uselocal=false)
	{
		$data = array();
		try {
			if ( !isset($data['media_gallery']) || !isset($data['media_gallery']['images']))
				$product->load('media_gallery');
			
			
			
			foreach ( $product->getAttributes() as $attribute ){
				if (in_array($attribute->getFrontendInput(), array('select','boolean','multiselect'))
				) {
					$value = $attribute->getFrontend()->getValue($product);
				} else {
					$value = $product->getData($attribute->getAttributeCode());
					if (  $attribute->getIsHtmlAllowedOnFront()) {
						$value = $this->_getTemplateProcessor()->filter($value);
					}
				}
				$data[$attribute->getAttributeCode()]=$value;
			}
			$attributes = $product->getMediaAttributes();
			$helper = Mage::helper('catalog/image');
			foreach ($attributes as $attribute) {
				/* @var $attribute Mage_Eav_Model_Entity_Attribute */
				$data[$attribute->getAttributeCode()]='';
				try {
					if ( $uselocal )
						$data[$attribute->getAttributeCode()]= $product->getData($attribute->getAttributeCode());
					else {
						$data[$attribute->getAttributeCode()]=''.$helper->init($product, $attribute->getAttributeCode());
					}
				}catch ( Exception $e )
				{
	
				}
			}
			if ( isset($data['media_gallery']) && isset($data['media_gallery']['images']))
			{
				$idx=1;
				foreach ($data['media_gallery']['images'] as  $image) {
					if ( !$image['disabled'] ) {
						if ( $uselocal )
							$data['image:'.$idx]= $image['file'];
						else {
							$data['image:'.$idx]=''.$helper->init($product, 'image',$image['file']);
						}
						$idx++;
					}
				}
				unset($data['media_gallery']);
			}		
			$data['_price']=$product->getFinalPrice();
			//$data['_price']=$product->getTierPrice($qty);
			$currency = Mage::app()->getStore()->getCurrentCurrency();
			$code = Mage::app()->getStore()->getCurrentCurrencyCode();
			$data['_price_formated']=$this->formatAsSpanPrice($data['_price'],false);
			$data['_price_formated2']=$this->formatAsSpanPrice($data['_price'],true,false);
			$data['_price_formated3']=$this->formatAsSpanPrice($data['_price'],true);
			$symbol ='';
			if ( Mage::app()->getLocale()->currency($code) )
				$symbol = Mage::app()->getLocale()->currency($code)->getSymbol();
			/*
			if (0 &&  $currency )
			{
				//$nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
				if ( Mage::app()->getLocale()->currency($code) )
					$symbol = Mage::app()->getLocale()->currency($code)->getSymbol();
				$data['_price_formated']=$currency->formatTxt($product->getFinalPrice());
				$data['_price_formated2']=$this->mbtrim(str_replace($symbol,'',$data['_price_formated']));
				$data['_price_formated3']=$data['_price_formated2'];
				
				$dp = substr($data['_price_formated2'], -3,1);
				
				if ( $dp == '.' || $dp == ',' )
				{
					$tmp = substr($data['_price_formated2'],0,-3);
					$tmp.= '<span¤class="precision">'.substr($data['_price_formated2'],-3).'</span>';
					$data['_price_formated'] = str_replace($data['_price_formated2'],$tmp,$data['_price_formated']);
					$data['_price_formated'] = str_replace($symbol,'<span¤class="symbol">'.$symbol.'</span>',$data['_price_formated']);
					$data['_price_formated2'] = $tmp;
					$nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
					$data['_price_formated3'] = preg_replace( "/(\s)/us", "", $data['_price_formated'] );
					$data['_price_formated3'] = preg_replace( "/(".$nonEscapableNbspChar.")/us", "", $data['_price_formated3'] );
					$data['_price_formated'] = str_replace('¤',' ',$data['_price_formated']);
					$data['_price_formated2'] = str_replace('¤',' ',$data['_price_formated2']);
					$data['_price_formated3'] = str_replace('¤',' ',$data['_price_formated3']);
				}
				
			}
			*/
//			$data['_price_formated']=Mage::app()->getStore()->formatPrice($product->getFinalPrice(),false);//$product->getFormatedPrice();
			$data['_price_final']=$product->getFinalPrice();
			$data['_price_calculated_final']=$product->getCalculatedFinalPrice();
			$data['_price_minimal_price']=$product->getMinimalPrice();
			$data['_price_special_price']=$product->getSpecialPrice();
			//$block = Mage::getModel('core/layout')->createBlock('catalog/product');
			//$data['_price_html_minimal']=$block->getPriceHtml($product, true);//$displayMinimalPrice = false, $idSuffix='')
			//$data['_price_html']=$block->getPriceHtml($product, false);//$displayMinimalPrice = false, $idSuffix='')
	
			$data['_price_currency_code']=$code;
			$data['_price_currency_symbol']=$symbol;
				
			$data['bc_url_website']=Mage::app()->getStore()->getUrl();
			$data['bc_url_product']=$product->getProductUrl();
		} catch (Exception $exception) {
			// Stop store emulation process
			Mage::logException($exception);
		}
	//	mage::log($data);
		// Stop store emulation process
		return $data;
	}
	public function getTemplateIdentifier($templateId)
	{
		$model = Mage::getModel('auit_publicationbasic/template')->load($templateId);
		if ( $model->getId() ) {
			return $model->getIdentifier();
		}
		return '';
	}
	public function getTemplatesOptions($basHash=false)
	{
		$devices = array(
			self::TEMPLATE_PRODUCT=> $this->__('Product'),
			//self::TEMPLATE_STATIC=> $this->__('Static'),
			self::TEMPLATE_COUPON=> $this->__('Coupon Code Card')
		);
		if ( $basHash )
			return $devices; 
		$options = array();
		$options[] = array('value' => '', 'label' => $this->__('Please Select Template Type'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
	}
	public function getJobTemplates($type=AuIt_PublicationBasic_Model_Jobqueue::TYPE_BROCHURE_LIST)
	{
		$collection = Mage::getResourceModel('auit_publicationbasic/jobqueue_collection')
		->addFieldToFilter('variante',$type)
		->addFieldToFilter('prio',array('gt'=>0));
		$options = array();
		foreach ($collection as $item )
		{
			$options[] = array('value' => 'J'.$item->getId(), 'label' => $item->getName());
		}
		return $options;
	}
	public function getTemplatesForType($type,$badd=false,$bhash=false)
	{
	//	as ass
	//	static $_options;
		$collection = Mage::getResourceModel('auit_publicationbasic/template_collection');
		$collection->addFieldToFilter('status', 1);
		if ( !is_array($type) )
			$type=array($type);
		$collection->addFieldToFilter('type',array('in'=>$type) );
		if ( !$bhash )
		{
			$options = $collection->toOptionArray();
			if ( $badd )
				$options[] = array('value' => '', 'label' => $this->__('No'));
		}else {
			foreach ( $collection as $opt )
			{
				$options[$opt->getId()] = $opt->getName();
			}
		}
		return $options;
	}
	public function getUsedSpread()
	{
		$_options=array();
		$_options[0]=$this->__('Use all spreads');
		for ( $i=1; $i <= 10; $i++ )
			$_options[$i]=$this->__('Spread').' '.$i;
		return $_options;
	}
	public function getTemplates($objectData,$type=null,$ball=true)
	{
		$_options=array();
//		if (!$_options) {
			$collection = Mage::getResourceModel('auit_publicationbasic/template_collection');
			$collection->addFieldToFilter('status', 1);
			if ( !$ball )
				$collection->addFieldToFilter('istoplevel', 1);
			if ( $type )
			{
				if ( !is_array($type) )
					$type=array($type);
				$collection->addFieldToFilter('type',array('in'=>$type) );
			}				
			foreach ( $collection as $opt )
			{
				if ( !$objectData || $objectData['model'] !='template' || $objectData['id']!= $opt->getId())
					$_options[$opt->getIdentifier()] = $opt->getName();
			}
	//	}
		return $_options;
	}
	
	public function getProjectsOptions()
	{
		$devices = array(
				'1'=> $this->__('Flyer'),
				'2'=> $this->__('Multipage Catalog')
		);
		$options = array();
		$options[] = array('value' => '', 'label' => $this->__('Please Select Template Type'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
	}
	public function getGeneratorOptions()
	{
		$devices = array(
				AuIt_PublicationBasic_Model_Generator::TYPE_PHTML=> $this->__('PHtml'),
				AuIt_PublicationBasic_Model_Generator::TYPE_MARKUP=> $this->__('Markup')
		);
		$options = array();
		$options[] = array('value' => '', 'label' => $this->__('Please Select Type'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
	}
	
	public function getPromoOptions()
	{
		$devices = array(
				AuIt_PublicationBasic_Model_Jobqueue::TYPE_COUPON_CARD=> $this->__('Coupon Card'),
				AuIt_PublicationBasic_Model_Jobqueue::TYPE_BROCHURE_LIST=> $this->__('Brochure').'/'.$this->__('Flyer')
				//,AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3=> $this->__('EBook (ePub3)')
		);
		//if (Mage::helper('core')->isModuleEnabled('AuIt_Ebook')) {
		if ( 0 )
			$devices[AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3] = $this->__('EBook (ePub3) (Beta)');
		//}
		$options = array();
		$options[] = array('value' => '', 'label' => $this->__('Please Select'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
	}
	
	public function getPromos($bHash=false)
	{
		$collection = Mage::getModel('salesrule/rule')->getResourceCollection();
		$collection->addWebsitesToResult();
		
		$devices = array();
		$devices['']=$this->__('Please Select');
		foreach ( $collection as $item )
		{
			$devices[$item->getId()] = $item->getName();
		}
		if ( $bHash )
			return $devices;
		$options = array();
//		$options[] = array('value' => '', 'label' => $this->__('Please Select'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
		
	}
	
	public function getJobQueueStatusOptions($bHash=false)
	{
		$devices = array(
        		AuIt_PublicationBasic_Model_Jobqueue::STATE_WAIT => Mage::helper('auit_publicationbasic')->__('Wait'),
				AuIt_PublicationBasic_Model_Jobqueue::STATE_START_NOW => Mage::helper('auit_publicationbasic')->__('Start Now'),
				AuIt_PublicationBasic_Model_Jobqueue::STATE_IN_PROGRESS => Mage::helper('auit_publicationbasic')->__('In Progress'),
				AuIt_PublicationBasic_Model_Jobqueue::STATE_HOLD => Mage::helper('auit_publicationbasic')->__('Hold'),
				AuIt_PublicationBasic_Model_Jobqueue::STATE_CANCELED => Mage::helper('auit_publicationbasic')->__('Canceled'),
				AuIt_PublicationBasic_Model_Jobqueue::STATE_COMPLETED => Mage::helper('auit_publicationbasic')->__('Completed'),
				AuIt_PublicationBasic_Model_Jobqueue::STATE_EXCEPTION => Mage::helper('auit_publicationbasic')->__('Exception'),
		);
		if ( $bHash )
			return $devices;
		$options = array();
		$options[] = array('value' => '', 'label' => $this->__('Please Select'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
	}
	public function getJobQueuePriorityOptions($bHash=false)
	{
		$devices = array(
				'0' => Mage::helper('auit_publicationbasic')->__('Disabled'),
				'10' => Mage::helper('auit_publicationbasic')->__('Low'),
				'20' => Mage::helper('auit_publicationbasic')->__('Medium'),
				'30' => Mage::helper('auit_publicationbasic')->__('High'),
				'40' => Mage::helper('auit_publicationbasic')->__('Very Hight')
		);
		if ( $bHash )
			return $devices;
		$options = array();
		$options[] = array('value' => '', 'label' => $this->__('Please Select'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
	}
	public function getJobQueueTypeOptions($bHash=false)
	{
		$devices = array(
			self::PRINT_TYPE_PROMO => $this->__('Promotions')
		);
		if ( $bHash )
			return $devices;
		$options = array();
		$options[] = array('value' => '', 'label' => $this->__('Please Select'));
		foreach ($devices as $type => $label) {
			$options[] = array('value' => $type, 'label' => $label);
		}
		return $options;
	}
	protected function _getCouponAttributes()
	{
		$hash['name']=$this->__('Rule Name');
		$hash['description']=$this->__('Description');
		//$hash['coupon_type']=$this->__('Rule Name');
		$hash['coupon_code']=$this->__('Coupon Code');

		$hash['from_date']=$this->__('From Date');
		$hash['to_date']=$this->__('To Date');
		return $hash;
	}
	protected function _getProductAttributes()
	{
		$hash=array();
		//$allowedAttributes=array('date','price','boolean','text','textarea','select','multiselect','media_image');
		$allowedAttributes=array('boolean','select','multiselect','text','textarea');
		//$allowedAttributes=array('text','textarea');
		$collection = Mage::getResourceModel('catalog/product_attribute_collection')
		->addVisibleFilter();
		$hashOpt=array();
		foreach ( $collection as $attr )
		{
			$code = $attr->getAttributeCode();
			$type = $attr->getFrontendInput();
			if (!in_array($type, $allowedAttributes) /*|| $attr->getFrontendInput() == 'hidden'*/) {
				continue;
			}
			$hash[$attr->getAttributeCode()]=$attr->getFrontendLabel();
		}
		asort($hash);
		return $hash;
	}
	public function getAttributes($isTemplate,$templateType)
	{
		static $_options;
		if (!$_options) {
			if ( $isTemplate )
			{
				switch ( $templateType )
				{
				case self::TEMPLATE_PRODUCT:
					$_options = $this->_getProductAttributes();
					break;
				case self::TEMPLATE_COUPON:
					$_options = $this->_getCouponAttributes();
					break;
				}
			}
	//		array_unshift($_options, $this->__('-- Please Select --'));//array('value'=> '', 'label'=> $this->__('-- Please Select --')));
		}
		return $_options;
	}
	public function getBarcodeAttributes($templ)
	{
		static $_options;
		if (!$_options) {
			$_options['bc_url_website']=$this->__('URL Shop Website');
			if ( $templ == self::TEMPLATE_PRODUCT)
				$_options['bc_url_product']=$this->__('URL Product Website');
			$_options['bc_free_text']=$this->__('Free Text');
			if ( $templ == self::TEMPLATE_PRODUCT)
			foreach ( $this->_getProductAttributes() as $k => $v )
				$_options[$k]=$v;
		}
		return $_options;
	}
	
	public function asOptions($options,$badd=false)
	{
		$html='';
		if ( $badd )
			$html .= '<option value="">'.$this->__('-- Please Select --').'</option>';
		if ( is_array($options) )
		foreach ( $options as $value => $label )
		{
			$html .= '<option value="'.$value.'">'.$label.'</option>';
		}
		return $html;
	}
	public function getImageAttributes($objectData)
	{
		static $_options;
		if (!$_options) {
			if ( $objectData['model']=='template' && $objectData['type']==1)
			{
				$attributes = Mage::getModel('catalog/product')->getMediaAttributes();
				if ( $attributes && is_array($attributes) )
				foreach ($attributes as $attribute) {
					/* @var $attribute Mage_Eav_Model_Entity_Attribute */
					$_options[$attribute->getAttributeCode()] = $attribute->getFrontend()->getLabel();
				}
			}

			$_options['media_static'] = $this->__('Media Static Image');
			
		}
		return $_options;
	}
	public function getPriceOptions()
	{
		static $_options;
		if (!$_options) {
			$_options = array();//$this->_getProductAttributes();
			$_options['_price'] = $this->__('Price');
			$_options['_price_formated'] = $this->__('Price (Formated with symbol)');
			$_options['_price_formated3'] = $this->__('Price (Formated without whitespace)');
			$_options['_price_formated2'] = $this->__('Price (Formated without symbol)');
			//$_options['_price_minimal_price'] = $this->__('Minimal Price');
			//$_options['_price_special_price'] = $this->__('Special Price');
			$_options['_price_currency_code'] = $this->__('Currency Code');
			$_options['_price_currency_symbol'] = $this->__('Currency Symbol');
			//$_options['special'] = $this->__('Special Price');
		}
		return $_options;
	}
	public function getPriceFromats()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['-'] = $this->__('Default');
			$_options['AL.digits(value)'] = $this->__('Digits');
			$_options['AL.decimals(value,2)'] = $this->__('Decimal Places');
		}
		return $_options;
	}
	
	public function getClippingMethodes()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['-'] = $this->__('No');
			$_options['c'] = $this->__('Circle');
			$_options['p'] = $this->__('Polygone');
			//$_options['x'] = '<img src="http://editor.method.ac/images/rotate.png"/>';
			
			//http://editor.method.ac/
//			https://code.google.com/p/svg-edit/source/browse/trunk/editor/extensions/?r=2694#extensions%2Fshapelib
		}
		return $_options;
	}
	
	public function getStaticBlocks()
	{
		static $_options;
		if (!$_options) {
			// ->toOptionArray()
			foreach ( Mage::getResourceModel('cms/block_collection')->load() as $opt )
			{
				$_options[$opt->getIdentifier()] = $opt->getTitle();
			}
		}
		return $_options;
	}
	public function getGeneratorBlocks()
	{
		static $_options;
		if (!$_options) {
			foreach ( Mage::getResourceModel('auit_publicationbasic/generator_collection')->load() as $opt )
			{
				$_options[$opt->getIdentifier()] = $opt->getName();
			}
		}
		return $_options;
	}
	public function getVAlign()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['T'] = $this->__('Top');
			$_options['M'] = $this->__('Middle');
			$_options['B'] = $this->__('Bottom');
		}
		return $_options;
	}
	public function getBoxTextOption()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['-'] = $this->__('None');
			$_options['fittextbox'] = $this->__('Fit Text to Box');
		}
		return $_options;
	}
	
	
	public function getPreviewStores($asPairs=false)
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			if ( $asPairs )
			{
				$storeModel = Mage::getSingleton('adminhtml/system_store');
				foreach ($storeModel->getWebsiteCollection() as $website) {
					$websiteShow = false;
					$options = array();
					
					foreach ($storeModel->getGroupCollection() as $group) {
						if ($group->getWebsiteId() != $website->getId()) {
							continue;
						}
						$groupShow = false;
						$optval=array();
						foreach ($storeModel->getStoreCollection() as $store) {
							if ($store->getGroupId() != $group->getId()) {
								continue;
							}
							$optval[]=array('value'=>$store->getCode(),'label'=>$store->getName());
						}
						if ( count($optval) )
						{
							$_options[] = array('value'=>$optval,'label'=>''.$website->getName().' / '.$group->getName());
						}
						$optval=array();
					}
				}
			}else {
				foreach ( Mage::app()->getStores(false,true) as $key => $store )
				{
					if ( $asPairs )
						$_options[] = array('value'=>array(array('value'=>$key,'label'=>$store->getName())),'label'=>'ggg'.$store->getName());
					else
						$_options[$key] = $store->getName();
				}
			}		
		}
		return $_options;
	}
	
		

	public function getPageFormate($useFree=true)
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			if ( $useFree)
				$_options[''] = $this->__('Free');
			$_options['DIN-A#297#420'] = $this->__('A3');
			$_options['DIN-A#210#297'] = $this->__('A4');
			$_options['DIN-A#148#210'] = $this->__('A5');
			$_options['DIN-B#176#250'] = $this->__('B5');
			$_options['FREE#119.944#120.65'] = $this->__('Compact Disc');
			/*
			$_options['DIN-A,841,1189'] = $this->__('DIN A0');
			$_options['DIN-A,594,841'] = $this->__('DIN A1');
			$_options['DIN-A,420,594'] = $this->__('DIN A2');
			$_options['DIN-A,105,148'] = $this->__('DIN A6');
			$_options['DIN-A,74,105'] = $this->__('DIN A7');
*/

			$_options['US#215.9#279.4'] = $this->__('Letter (8½ × 11)');
			$_options['US#215.9#355.6'] = $this->__('Legal (8½ × 14)');
			$_options['US#,279.4#431.8'] = $this->__('Tabloid (11 × 17)');
			/*
			$_options['US,105,241'] = $this->__('US #10');
			$_options['US,140,216'] = $this->__('Invoice (5½ × 8½)');
			$_options['US,184,267'] = $this->__('Executive (7¼ × 10½)');
			$_options['US,432,559'] = $this->__('Broadsheet (17 × 22)');
			*/
		}
		return $_options;
	}
	public function getOrientation()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['p'] = $this->__('Portrait');
			$_options['l'] = $this->__('Landscape');
		}
		return $_options;
	}
	public function getBoxTypes($templ)
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options[] = array('value'=>'p_attr','label'=>$this->__('Attribute'));
			$_options[] = array('value'=>'p_img','label'=>$this->__('Images'));
			$_options[] = array('value'=>'p_block','label'=>$this->__('Block'));
			if ( $templ == self::TEMPLATE_PRODUCT)
				$_options[] = array('value'=>'p_price','label'=>$this->__('Price'));
			$_options[] = array('value'=>'p_free','label'=>$this->__('Text'));
			$_options[] = array('value'=>'p_bc','label'=>$this->__('Barcode'));
			
			$_options[] = array('value'=>'p_templ','label'=>$this->__('Template'));
			$_options[] = array('value'=>'p_gen','label'=>$this->__('Generator'));
			//$_options[] = array('value'=>'p_group','label'=>$this->__('Group'));
		}
		return $_options;
	}
	public function getArrangeTypes()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options[] = array('value'=>'top','label'=>$this->__('Bring to Front'));
			$_options[] = array('value'=>'top1','label'=>$this->__('Bring Forward'));
			$_options[] = array('value'=>'bottom1','label'=>$this->__('Send Backward'));
			$_options[] = array('value'=>'bottom','label'=>$this->__('Send to Back'));
		}
		return $_options;
	}
	public function getAlignTypes()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options[] = array('value'=>'t','label'=>$this->__('Align top edges'));
			$_options[] = array('value'=>'v','label'=>$this->__('Align vertical centers'));
			$_options[] = array('value'=>'b','label'=>$this->__('Align bottom edges'));
			$_options[] = array('value'=>'l','label'=>$this->__('Align left edges'));
			$_options[] = array('value'=>'h','label'=>$this->__('Align horizontal centers'));
			$_options[] = array('value'=>'r','label'=>$this->__('Align right edges'));
		}
		return $_options;
	}
	public function getBoxCtxMenus()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options[] = array('value'=>'copyBox','label'=>$this->__('Copy Box'),'icon'=>'ui-icon-copy');
		//	$_options[] = array('value'=>'pasteBox','label'=>$this->__('Paste Box'),'icon'=>'ui-icon-mail-open');
			$_options[] = array('value'=>'removeBox','label'=>$this->__('Remove Box'),'icon'=>'ui-icon-circle-minus','cls'=>'seperator-bottom');
			$_options[] = array('value'=>'unlinkBox','label'=>$this->__('Unlink Template'),'icon'=>'ui-icon-link');
			$_options[] = array('value'=>'groupBox','label'=>$this->__('Group Box'),'icon'=>'ui-icon-copy');
			$_options[] = array('value'=>'ungroupBox','label'=>$this->__('Ungroup Box'),'icon'=>'ui-icon-circle-minus','cls'=>'seperator-bottom');
			$_options[] = array('value'=>'top','label'=>$this->__('Bring to Front'),'icon'=>'ui-icon-arrowstop-1-n');
			$_options[] = array('value'=>'top1','label'=>$this->__('Bring Forward'),'icon'=>'ui-icon-arrow-1-n');
			$_options[] = array('value'=>'bottom1','label'=>$this->__('Send Backward'),'icon'=>'ui-icon-arrow-1-s');
			$_options[] = array('value'=>'bottom','label'=>$this->__('Send to Back'),'icon'=>'ui-icon-arrowstop-1-s');
		}
		return $_options;
	}
	/*
	public function getFonts()
	{
		static $_options;
		if (!$_options) {
			$_options = array();
			foreach ( Mage::helper('auit_publicationbasic/font')->getFonts() as $font )
				$_options[$font['indentifier']] = $font['label'];
		}
		return $_options;
	}

	public function getFontStyles()
	{
		//regular, condens,...
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['T'] = $this->__('Top');
			$_options['M'] = $this->__('Middle');
			$_options['B'] = $this->__('Bottom');
		}
		return $_options;
	}
*/
	public function getFontSize()
	{
		//6pt ...72pt
		static $_options;
		if (!$_options) {
			$_options = array();
			foreach ( array('','6pt','8pt','9pt','10pt','11pt','12pt','14pt','18pt','24pt','30pt','36pt','48pt','60pt','72pt') as $v)
				$_options[$v] = $v?$v:$this->__('Default');
		}
		return $_options;
	}
	public function getLeading()
	{
		//(auto) 6pt 72pt
		static $_options;
		if (!$_options) {
			$_options = array();
			foreach ( array('','0.8','0.9','1.0','1.25','1.5','1.75','2.0') as $v)
//			foreach ( array('','6pt','8pt','9pt','10pt','11pt','12pt','14pt','18pt','24pt','30pt','36pt','48pt','60pt','72pt') as $v)
				$_options[$v] = $v?$v:$this->__('Default');
					}
		return $_options;
	}

	public function getAligment()
	{
		//link, rechts, zentriert, blocksatz
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options[''] = $this->__('');
			$_options['left'] = $this->__('Left');
			$_options['right'] = $this->__('Right');
			$_options['center'] = $this->__('Center');
			$_options['justify'] = $this->__('Justify');
		}
		return $_options;
	}
	public function getTextTransform()
	{
		
		//link, rechts, zentriert, blocksatz
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options[''] = $this->__('');
			$_options['uppercase'] = $this->__('Uppercase');
			$_options['lowercase'] = $this->__('Lowercase');
			$_options['capitalize'] = $this->__('Capitalize');
			
		}
		return $_options;
	}
	
	public function getColourMode()
	{
		//CMYK,RGB,PANTONE
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options[''] = $this->__('');
			$_options['rgb'] = $this->__('RGB');
			$_options['hsl'] = $this->__('HSL/CMYK');
		}
		return $_options;
	}
	public function getImageOption()
	{
		//CMYK,RGB,PANTONE
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['-'] = $this->__('Standard');
			$_options['fit2box'] = $this->__('Inhalt proportional anpassen');
			$_options['fill2box'] = $this->__('Rahmen proportional füllen');
		}
		return $_options;
	}
	public function getBarcodeOption()
	{
		//CMYK,RGB,PANTONE
		static $_options;
		if (!$_options) {
			$_options = array();
			$_options['-'] = $this->__(' ');
			$_options['DATAMATRIX'] = $this->__('Datamatrix (ISO/IEC 16022)');
			$_options['PDF417'] = $this->__('PDF417 (ISO/IEC 15438:2006)');
			$_options['QRCODE'] = $this->__('QR-CODE Low');
			$_options['QRCODE,M'] = $this->__('QR-CODE Medium');
			$_options['QRCODE,H'] = $this->__('QR-CODE Best');
			$_options['EAN13'] = $this->__('EAN13');
			$_options['C128'] = $this->__('C128');
			$_options['C128A'] = $this->__('C128A');
			$_options['C128B'] = $this->__('C128B');
			$_options['C128C'] = $this->__('C128C');
			$_options['C39'] = $this->__('C39');
		}
		return $_options;
	}
	
}
