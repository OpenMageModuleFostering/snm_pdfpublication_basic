<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct($arguments=array())
    {
        parent::__construct($arguments);

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            //$this->setId('skuChooserGrid_'.$this->getId());
            $this->setId('auit_publicationbasic_catalog_skus2_content');
            
        }
        $form = 'auit_l_product_fields';
        $this->setRowClickCallback("$form.gridRowClick");
        $this->setCheckboxCheckCallback("$form.gridCheckboxCheck");
        $this->setRowInitCallback("$form.gridRowInit");
        

        $this->setDefaultSort('sku');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Retrieve quote store object
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore();
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $selected = $this->_getSelectedProducts();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('sku', array('in'=>$selected));
            } else {
                $this->getCollection()->addFieldToFilter('sku', array('nin'=>$selected));
            }
        } else {
        	if ($this->getCollection()) {
        		if ($column->getId() == 'websites') {
        			$this->getCollection()->joinField('websites',
        					'catalog/product_website',
        					'website_id',
        					'product_id=entity_id',
        					null,
        					'left');
        		}
        	}
        	 
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare Catalog Product Collection for attribute SKU in Promo Conditions SKU chooser
     *
     * @return Mage_Adminhtml_Block_Promo_Widget_Chooser_Sku
     */
    protected function _prepareCollectionXX()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId(0)
            ->addAttributeToSelect('name', 'type_id', 'attribute_set_id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    protected function _getStore()
    {
    	$storeId = (int) $this->getRequest()->getParam('store', 0);
    	return Mage::app()->getStore($storeId);
    }
    
    protected function _prepareCollection()
    {
    	$store = $this->_getStore();
    	$collection = Mage::getModel('catalog/product')->getCollection()
    	->addAttributeToSelect('sku')
    	->addAttributeToSelect('name')
    	->addAttributeToSelect('attribute_set_id')
    	->addAttributeToSelect('type_id');
    
    	if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
    		$collection->joinField('qty',
    				'cataloginventory/stock_item',
    				'qty',
    				'product_id=entity_id',
    				'{{table}}.stock_id=1',
    				'left');
    	}
    	if ($store->getId()) {
    		//$collection->setStoreId($store->getId());
    		$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
    		$collection->addStoreFilter($store);
    		$collection->joinAttribute(
    				'name',
    				'catalog_product/name',
    				'entity_id',
    				null,
    				'inner',
    				$adminStore
    		);
    		$collection->joinAttribute(
    				'custom_name',
    				'catalog_product/name',
    				'entity_id',
    				null,
    				'inner',
    				$store->getId()
    		);
    		$collection->joinAttribute(
    				'status',
    				'catalog_product/status',
    				'entity_id',
    				null,
    				'inner',
    				$store->getId()
    		);
    		$collection->joinAttribute(
    				'visibility',
    				'catalog_product/visibility',
    				'entity_id',
    				null,
    				'inner',
    				$store->getId()
    		);
    		$collection->joinAttribute(
    				'price',
    				'catalog_product/price',
    				'entity_id',
    				null,
    				'left',
    				$store->getId()
    		);
    		$collection->joinAttribute(
    				'small_image',
    				'catalog_product/small_image',
    				'entity_id',
    				null,
    				'left',
    				$store->getId()
    		);
    	}
    	else {
    		$collection->addAttributeToSelect('price');
    		$collection->addAttributeToSelect('small_image');
    		$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
    		$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
    	}
    
    	$this->setCollection($collection);
    
    	parent::_prepareCollection();
    	$this->getCollection()->addWebsiteNamesToResult();
    	return $this;
    }
    
    /**
     * Define Cooser Grid Columns and filters
     *
     * @return Mage_Adminhtml_Block_Promo_Widget_Chooser_Sku
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'in_products',
            'values'    => $this->_getSelectedProducts(),
            'align'     => 'center',
            'index'     => 'sku',
            'use_index' => true,
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('sales')->__('ID'),
            'sortable'  => true,
       		'width' => '30',
            'index'     => 'entity_id'
        ));

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '100px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '10px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));

        $this->addColumn('chooser_sku', array(
            'header'    => Mage::helper('sales')->__('SKU'),
            'name'      => 'chooser_sku',
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('chooser_name', array(
            'header'    => Mage::helper('sales')->__('Product Name'),
            'name'      => 'chooser_name',
            'index'     => 'name'
        ));
        
        $this->addColumn('small_image', array(
        		'type'  => 'image',
        		'width' => '80px',
        		'header'    => Mage::helper('sales')->__('Picture'),
        		'index'     => 'small_image',
        		'renderer'  => 'auit_publicationbasic/widget_grid_column_renderer_image'
        ));
        
        $this->addColumn('visibility',
        		array(
        				'header'=> Mage::helper('catalog')->__('Visibility'),
        				'width' => '7px',
        				'index' => 'visibility',
        				'type'  => 'options',
        				'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        		));
        
        $this->addColumn('status',
        		array(
        				'header'=> Mage::helper('catalog')->__('Status'),
        				'width' => '7px',
        				'index' => 'status',
        				'type'  => 'options',
        				'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        		));
        
        if (!Mage::app()->isSingleStoreMode()) {
        	$this->addColumn('websites',
        			array(
        					'header'=> Mage::helper('catalog')->__('Websites'),
        					'width' => '10px',
        					'sortable'  => false,
        					'index'     => 'websites',
        					'type'      => 'options',
        					'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
        			));
        }
        

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/chooser', array(
            '_current'          => true,
            'current_grid_id'   => $this->getId(),
            'collapse'          => null
        ));
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected', array());

        return $products;
    }

}

    