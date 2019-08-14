<?php
class AuIt_PublicationBasic_Admin_ImportController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        // Define module dependent translate
       // $this->setUsedModuleName('Mage_ImportExport');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Import Template'))
            ->loadLayout()
            ->_setActiveMenu('auit_publicationbasic/import');

        return $this;
    }
    protected function _isAllowed()
    {
    	return Mage::getSingleton('admin/session')->isAllowed('snm/auit_publicationbasic/import');
    }
    public function indexAction()
    {
        $maxUploadSize = Mage::helper('importexport')->getMaxUploadSize();
        $this->_getSession()->addNotice(
            $this->__('Total size of uploadable files must not exceed %s', $maxUploadSize)
        );
        $this->_initAction()
            ->_title($this->__('Import'))
            ->_addBreadcrumb($this->__('Import'), $this->__('Import'));

        $this->renderLayout();
    }

    /**
     * Start import process action.
     *
     * @return void
     */
    public function startAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->loadLayout(false);
            /** @var $resultBlock Mage_ImportExport_Block_Adminhtml_Import_Frame_Result */
            $resultBlock = $this->getLayout()->getBlock('import.frame.result');
            $resultBlock->addAction('show', 'import_validation_container')
            ->addAction('innerHTML', 'import_validation_container_header', $this->__('Status'));
            
            try {
            $messages = Mage::helper('auit_publicationbasic/export')->importArea($this->getRequest()->getParam('lytarchiv'));
            }catch (Exception $e )
            {
            	$messages=array();
            	$messages['error'][]=$e->getMessage();
            }
            $bhasErrors=false;
            $bhasTransaction=false;
            foreach ( $messages as $type => $msgs )
            {
            	switch ( $type )
            	{
            		case 'notice':
            			foreach ( $msgs as $msg)
            				$resultBlock->addNotice($msg);
            			break;
            		case 'transaction':
            			$bhasTransaction=true;
            			break;
            		case 'import_dir':
            		case 'import_key':
            			break;
            		default:
            			$bhasErrors=true;
            			foreach ( $msgs as $msg)
            				$resultBlock->addError($msg);
            			break;
            	}
            }
            if ( !$bhasErrors ){
            	
           		if ( $bhasTransaction )
           		{
           			try{
           				foreach ( $messages['transaction'] as &$model)
           				{
           					$model->save();
           				}
           			}
           		    catch(Exception $e)
           			{
           				$bhasErrors=true;
           				$resultBlock->addError($e->getMessage());
           			}
           		}
           		if ( !$bhasErrors ){
	            	$resultBlock->addAction('hide', array('edit_form', 'upload_button', 'messages'))
	                	->addSuccess($this->__('Import successfully done.'));
           		}
           	}
           	try{
           		if ( is_dir_writeable($messages['import_dir']) )
           		{
           			$io = new Varien_Io_File();
           			$io->rmdir($messages['import_dir'], true);
           		}
           	}
           	catch(Exception $e)
           	{
           	}
           	
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Validate uploaded files action.
     *
     * @return void
     */
    public function validateAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $this->loadLayout(false);
            /** @var $resultBlock Mage_ImportExport_Block_Adminhtml_Import_Frame_Result */
            $resultBlock = $this->getLayout()->getBlock('import.frame.result');
            // common actions
            $resultBlock->addAction('show', 'import_validation_container')
                ->addAction('clear', array(
                    'publication_import_file',
               		'publication_import_file_archive')
                );

			try {
				$messages = Mage::helper('auit_publicationbasic/export')->validImport($data);
				$bhasErrors=false;
				$bhasTransaction=false;
				foreach ( $messages as $type => $msgs )
				{
					switch ( $type )
					{
						case 'notice':
							foreach ( $msgs as $msg)
							$resultBlock->addNotice($msg);
						break;
						case 'transaction':
							$bhasTransaction=true;
							break;
						case 'import_dir':
						case 'import_key':
							break;
						default:
							$bhasErrors=true;
							foreach ( $msgs as $msg)
								$resultBlock->addError($msg);
						break;
					}
				} 
				if ( !$bhasErrors && $bhasTransaction){
					$resultBlock->addSuccess(
						$this->__('File is valid! To start import process press "Import" button'), true,$messages['import_key']);
				}
				else {
					try{
						if ( is_dir_writeable($messages['import_dir']) )
						{
							$io = new Varien_Io_File();
							$io->rmdir($messages['import_dir'], true);
						}
					}
					catch(Exception $e)
					{
					}
				}
            } catch (Exception $e) {
                $resultBlock->addNotice($this->__('Please fix errors and re-upload file'))
                    ->addError($e->getMessage());
            }
            
            
            $this->renderLayout();
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $this->loadLayout(false);
            $resultBlock = $this->getLayout()->getBlock('import.frame.result');
            $resultBlock->addError($this->__('File was not uploaded'));
            $this->renderLayout();
        } else {
            $this->_getSession()->addError($this->__('Data is invalid or file is not uploaded'));
            $this->_redirect('*/*/index');
        }
    }
}
