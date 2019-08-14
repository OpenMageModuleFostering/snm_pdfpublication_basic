<?php
class AuIt_PublicationBasic_Block_Generator extends Mage_Catalog_Block_Product_View
{
	protected $_generator = null;
	protected $_product = null;
	protected $_childBoxes = array();
	protected $_params = array();
	public function getGenerator()
	{
		if ( !$this->_generator)
		{
			$this->_generator = Mage::getModel('auit_publicationbasic/generator')
			->setStoreId(Mage::app()->getStore()->getId())
			->load($this->getGeneratorId());
		}
		return $this->_generator;
	}
	public function setParameter($params)
	{
		if ( !is_array($params) )
		{
			try {	
			$params = Mage::helper('core')->jsonDecode($params);
			$this->_params=$params;
			}catch (Exeption $e )
			{
			}
		} 
		return $this;
	}
	public function getGeneratorParameter($key,$default=null)
	{
		if ( isset($this->_params[$key]))
			return $this->_params[$key];
		$params = $this->getGenerator()->getParameter();
		foreach ( $params as $param )
		{
			if ( $param['code'] == $key )
			{
				if ( isset($param['default']) && $param['default'])
					return $param['default'];
			}
		}
		return $default;
	}
	public function addBox($params)
	{
		$this->_childBoxes[]=$params;
	}
	public function getBoxes()
	{
		return $this->_childBoxes;
	}
	
	protected function _prepareLayout()
	{
		return Mage_Catalog_Block_Product_Abstract::_prepareLayout();
		
	}
	public function getPriceMatrix($mode=0)
	{
		$matrix=array();
		$_product = $this->getProduct();
		
		if ( $_product->isConfigurable() )
		{
			
			$typeBlock = $this->getLayout()->createBlock('catalog/product_view_type_configurable');
			$data = Mage::helper('core')->jsonDecode($typeBlock->getJsonConfig());
			
			$attributes= array_reverse($data['attributes']);
			$pricevariants=array();
			$products=array();
			foreach ( $attributes as $attribute )
			{
				$aid=$attribute['label'];
				foreach ( $attribute['options'] as $option )
				{
					$price = $option['price'];
					$oid=$option['label'];
					foreach ( $option['products'] as $productId )
					{
						$pricevariants[$price]['products'][$productId]=1;
						$products[$productId]['attributes'][$aid]=$oid;
					}
				}
			}
			foreach ( $pricevariants as $p => $variant )
			{
				$price = sprintf('%02f',$p + $data['basePrice']);
				$pv = array(
						'price'=> $price,
						'price_formatedold'=> str_replace('#{price}',$price,$data['template']),
						'price_formated'=> Mage::helper('auit_publicationbasic')->formatAsSpanPrice($p + $data['basePrice']),
						//'old_price'=> $p + $data['oldPrice'],
						//'old_price_formated'=> str_replace('#{price}',$p + $data['oldPrice'],$data['template'])
				);
				
				
				foreach ( $variant['products'] as $pid => $tmp )
				{
					foreach ( $products[$pid]['attributes'] as $aid => $oid )
					{
						$pv['attributes'][$aid][$oid]=1;
					}
				}
				$matrix[]=$pv;
			}
			if ( $mode == 0 && !count($matrix))
			{
				$pv = array(
						'price'=> sprintf('%02f',$_product->getFinalPrice()),
						'price_formated'=> Mage::helper('auit_publicationbasic')->formatAsSpanPrice($_product->getFinalPrice()),
				);
				if ( $mode == 0)
					$pv['attributes'][$this->__('Sku')][$_product->getSku()]=1;
				else
					$pv['attributes']=array();
				$matrix[]=$pv;
			}
		}else {
			$pv = array(
				'price'=> sprintf('%02f',$_product->getFinalPrice()),
				'price_formated'=> Mage::helper('auit_publicationbasic')->formatAsSpanPrice($_product->getFinalPrice()),
			);
			if ( $mode == 0)
				$pv['attributes'][$this->__('Sku')][$_product->getSku()]=1;
			else
				$pv['attributes']=array();
			$matrix[]=$pv;
		}
		return $matrix; 
	}
	public function getAdditionalData(array $excludeAttr = array())
	{
		$data = array();
		$product = $this->getProduct();
		$attributes = $product->getAttributes();
		foreach ($attributes as $attribute) {
			//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
			if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
				$value = $attribute->getFrontend()->getValue($product);
	
				if (!$product->hasData($attribute->getAttributeCode())) {
					$value = Mage::helper('catalog')->__('N/A');
				} elseif ((string)$value == '') {
					$value = Mage::helper('catalog')->__('No');
				} elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
					$value = Mage::app()->getStore()->convertPrice($value, true);
				}
	
				if (is_string($value) && strlen($value)) {
					$data[$attribute->getAttributeCode()] = array(
							'label' => $attribute->getStoreLabel(),
							'value' => $value,
							'code'  => $attribute->getAttributeCode()
					);
				}
			}
		}
		return $data;
	}
	
	
	protected function _toHtml()
	{
		if ( $this->getGenerator()->getType() == AuIt_PublicationBasic_Model_Generator::TYPE_MARKUP )
		{
			/* @var $helper Mage_Cms_Helper_Data */
			$helper = Mage::helper('cms');
			$processor = $helper->getBlockTemplateProcessor();
			return $processor->filter($this->getGenerator()->getSource());
		}
		if ( $this->getGenerator()->getType() == AuIt_PublicationBasic_Model_Generator::TYPE_PHTML )
		{
			$html = $this->fetchView('dummy');
			return $html;
		}
		return '';
	}
	public function fetchView($fileName)
	{
		extract ($this->_viewVars, EXTR_SKIP);
		ob_start();
		$html ='';
		try {
			eval('?>'.$this->getGenerator()->getSource());
			$html = ''.ob_get_clean();
		} catch (Exception $e) {
			ob_get_clean();
			$html = 'Exception:'.$e->getMessage();
//			throw $e;
		}
		
		return $html;
	}
}
