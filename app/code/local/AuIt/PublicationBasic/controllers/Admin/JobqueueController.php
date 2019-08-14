<?php
// https://github.com/kschroeder/Magento-ZendServer-JobQueue
class AuIt_PublicationBasic_Admin_JobqueueController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_title($this->__('SNM Publication'))->_title($this->__('Job Queue'));
    	$this->loadLayout()->_setActiveMenu('auit_publicationbasic/jobqueue');
    	Mage::helper('auit_publicationbasic')->checkMediaFolder();
        return $this;
    }
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }
    public function newAction()
    {
    	/*
    	$this->_initAction();
    	$this->renderLayout();
    	*/
    }
    
    public function chooserAction()
    {
		$type = 'auit_publicationbasic/adminhtml_jobqueue_edit_tab_products_grid';
    	if (!empty($type)) {
    		$block = $this->getLayout()->createBlock($type);
    		if ($block) {
    			$this->getResponse()->setBody($block->toHtml());
    		}
    	}
    	 
    }
    public function newpromoAction()
    {
    	Mage::getSingleton('adminhtml/session')->setData('new_auit_publication', 11);
    	$this->_initAction();
    	$this->renderLayout();
    }
    public function downloadAction()
    {
    	/* @var $model AuIt_PublicationBasic_Model_Jobqueue */
    	$model = Mage::getModel('auit_publicationbasic/jobqueue');
    	$model->load($this->getRequest()->getParam('id'));
    	if (!$model->getId() || !$model->downloadExists()) {
    		return $this->_redirect('*/*');
    	}
    	
    	$fileName = $model->getFilename();
    	
    	$this->_prepareDownloadResponse($fileName, null, 'application/pdf',$model->getFileSize());
    	$this->getResponse()->sendHeaders();
    	$model->outputFile();
    	exit();
    }
    
    public function editAction()
    {
        $id = $this->getRequest()->getParam('jobqueue_id');
        $type = $this->getRequest()->getParam('type');
        $model = Mage::getModel('auit_publicationbasic/jobqueue');
        $model->load($id);
        if (!$model->getId() && $type ) {
        	$model->setType($type);
        	$model->setVariante($this->getRequest()->getParam('variante'));
        }
        if ($model->getId() || $model->getType()) {
        	Mage::getSingleton('adminhtml/session')->setData('new_auit_publication', false);
        } else {
            $this->_redirect('*/*/');
        	return;
        }
        $model->initialBasisData();
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        Mage::register('auit_publicationbasic_jobqueue', $model);
        $this->_initAction()->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
        	$id = $this->getRequest()->getParam('jobqueue_id');
        	if ( !$id && $this->getRequest()->getParam('type') ) {
        		if ( Mage::getSingleton('adminhtml/session')->getData('new_auit_publication') )
        		{
        			$this->_redirect('*/*/edit', array('type' => $this->getRequest()->getParam('type'),'variante' => $this->getRequest()->getParam('variante')));
        			return;
        			 
        		}
        	}
            $model = Mage::getModel('auit_publicationbasic/jobqueue')->load($id);
            $model->setQueueStartAtByString($this->getRequest()->getParam('start_at'));
            if (!$model->getId() && $id) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('auit_publicationbasic')->__('This jobqueue no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            // try to save it
            try {
	            // init model and set data
	            $model->addData($data);

                // save the data
                $model->save();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('auit_publicationbasic')->__('The jobqueue has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('jobqueue_id' => $model->getId(), '_current'=>true));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('jobqueue_id' => $this->getRequest()->getParam('jobqueue_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('jobqueue_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('auit_publicationbasic/jobqueue');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('auit_publicationbasic')->__('The jobqueue has been deleted.'));
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('jobqueue_id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('auit_publicationbasic')->__('Unable to find a jobqueue to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }
    public function massDeleteAction()
    {
    	$Ids = $this->getRequest()->getParam('jobqueue_ids');
    	$session    = Mage::getSingleton('adminhtml/session');

    	if(!is_array($Ids)) {
    		$session->addError(Mage::helper('adminhtml')->__('Please select.'));
    	} else {
    		try {
    			foreach ($Ids as $itemId) {
    				$model = Mage::getModel('auit_publicationbasic/jobqueue')->load($itemId);
    				$model->delete();
    			}
    			Mage::getSingleton('adminhtml/session')->addSuccess(
    					Mage::helper('adminhtml')->__('Total of %d record(s) have been deleted.', count($Ids))
    			);
    		} catch (Mage_Core_Exception $e) {
    			$session->addError($e->getMessage());
    		} catch (Exception $e){
    			$session->addException($e, Mage::helper('adminhtml')->__('An error occurred while deleting record(s).'));
    		}
    	}
    	$this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
    public function massCancelAction()
    {
    	$Ids = $this->getRequest()->getParam('jobqueue_ids');
    	$session    = Mage::getSingleton('adminhtml/session');
    
    	if(!is_array($Ids)) {
    		$session->addError(Mage::helper('adminhtml')->__('Please select.'));
    	} else {
    		try {
    			foreach ($Ids as $itemId) {
    				$model = Mage::getModel('auit_publicationbasic/jobqueue')->load($itemId);
    				$model->setQueueState(AuIt_PublicationBasic_Model_Jobqueue::STATE_CANCELED);
    				$model->save();
    			}
    		} catch (Mage_Core_Exception $e) {
    			$session->addError($e->getMessage());
    		} catch (Exception $e){
    			$session->addException($e, Mage::helper('adminhtml')->__('An error occurred while change record(s).'));
    		}
    	}
    	$this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
    public function massResetAction()
    {
    	$Ids = $this->getRequest()->getParam('jobqueue_ids');
    	$session    = Mage::getSingleton('adminhtml/session');
    
    	if(!is_array($Ids)) {
    		$session->addError(Mage::helper('adminhtml')->__('Please select.'));
    	} else {
    		try {
    			foreach ($Ids as $itemId) {
    				$model = Mage::getModel('auit_publicationbasic/jobqueue')->load($itemId);
    				$model->setQueueState(AuIt_PublicationBasic_Model_Jobqueue::STATE_WAIT);
    				$model->save();
    			}
    		} catch (Mage_Core_Exception $e) {
    			$session->addError($e->getMessage());
    		} catch (Exception $e){
    			$session->addException($e, Mage::helper('adminhtml')->__('An error occurred while change record(s).'));
    		}
    	}
    	$this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
    public function massStartAction()
    {
    	$Ids = $this->getRequest()->getParam('jobqueue_ids');
    	$session    = Mage::getSingleton('adminhtml/session');
    
    	if(!is_array($Ids)) {
    		$session->addError(Mage::helper('adminhtml')->__('Please select.'));
    	} else {
    		
    		try {
    			foreach ($Ids as $itemId) {
    				$model = Mage::getModel('auit_publicationbasic/jobqueue')->load($itemId);
    				$model->setQueueState(AuIt_PublicationBasic_Model_Jobqueue::STATE_START_NOW);
    				$model->save();
    			}
    		} catch (Mage_Core_Exception $e) {
    			$session->addError($e->getMessage());
    		} catch (Exception $e){
    			Mage::log($e->getMessage());
    			$session->addException($e, Mage::helper('adminhtml')->__('An error occurred while change record(s).'));
    		}
    		
    		try {
	    		// NUR PING!!
	    		$uri = Zend_Uri::factory(str_replace('/media/','/cron.php',Mage::getBaseUrl('media')).'?aqc='.implode(',', $Ids));
	    		@file_get_contents(''.$uri);
	    		/*
	    		$socket = new Zend_Http_Client_Adapter_Socket();
	    		$socket->setConfig(array('timeout'=>30));
	    		if ( !$uri->getPort() ) 
	    			$uri->setPort(80);
	    		$socket->connect($uri->getHost(), $uri->getPort(), ($uri->getScheme() == 'https' ? true : false));
	    		$socket->write('GET', $uri,'1.1',array('Host: '.$uri->getHost()));
				*/
    		} catch (Exception $e){
    			Mage::log('Zend_Http_Client_Adapter_Socket:'.$e->getMessage());
    		}
    	}
    	$this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
    public function testAdd()
    {
    	$product_id = 168;

    	$product = Mage::getModel('catalog/product')->load($product_id);

    	$param = array( 'product' => $product->getId(), 
    					'qty' => 1,
		    			'options' => array(
		    					$option_id => $option_value,
		    					$option_id2 => $option_value2,
		    			),
    	);
    	
    	$cart = Mage::getModel('checkout/cart')->init();
    	$cart->addProduct($product, new Varien_Object($param));
    	$cart->save();
    	$product_id = 1234;
    	$option_id = "ID deines Options Feldes";
    	
    	$product = Mage::getModel('catalog/product')->load($product_id);
    	
    	$param = array( 'product' => $product->getId(),
    			'qty' => 1,
    			'options' => array(
    					$option_id => $JSON_STRING
    			),
    	);
    	 
    	$cart = Mage::getModel('checkout/cart')->init();
    	$cart->addProduct($product, new Varien_Object($param));
    	
    	
    	$cart->save();
    	
    	$service = Mage::getModel('sales/service_quote', $cart->getQuote());
    	$service->submitAll();
    	$order = $service->getOrder();
    	 
    }
    public function massHoldAction()
    {
    	//$this->testAdd();
    	
    	$Ids = $this->getRequest()->getParam('jobqueue_ids');
    	$session    = Mage::getSingleton('adminhtml/session');
    
    	if(!is_array($Ids)) {
    		$session->addError(Mage::helper('adminhtml')->__('Please select.'));
    	} else {
    		try {
    			foreach ($Ids as $itemId) {
    				$model = Mage::getModel('auit_publicationbasic/jobqueue')->load($itemId);
    				$model->setQueueState(AuIt_PublicationBasic_Model_Jobqueue::STATE_HOLD);
    				$model->save();
    			}
    		} catch (Mage_Core_Exception $e) {
    			$session->addError($e->getMessage());
    		} catch (Exception $e){
    			$session->addException($e, Mage::helper('adminhtml')->__('An error occurred while change record(s).'));
    		}
    	}
    	$this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
    
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('snm/auit_publicationbasic/jobqueue');
    }


}
