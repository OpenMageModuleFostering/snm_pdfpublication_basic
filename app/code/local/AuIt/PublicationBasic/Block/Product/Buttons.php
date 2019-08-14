<?php

class AuIt_PublicationBasic_Block_Product_Buttons extends Mage_Core_Block_Template
{
	public function getProduct()
	{
		if (!$this->getData('product') instanceof Mage_Catalog_Model_Product) {
			$productId = $this->getProductId();
			if ($productId) {
				$product = Mage::getModel('catalog/product')->load($productId);
				if ($product) {
					$this->setProduct($product);
				}
			}
			if (!$this->getData('product') instanceof Mage_Catalog_Model_Product) {
				$this->setData('product', Mage::registry('product'));
			}
		}
		return $this->getData('product');
	}
	
}
