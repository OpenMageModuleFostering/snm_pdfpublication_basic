<?php
class AuIt_PublicationBasic_Block_Adminhtml_Import_Frame_Result extends Mage_Adminhtml_Block_Template
{
    /**
     * JavaScript actions for response.
     *
     * @var array
     */
    protected $_actions = array(
        'clear'           => array(), // remove element from DOM
        'innerHTML'       => array(), // set innerHTML property (use: elementID => new content)
        'value'           => array(), // set value for form element (use: elementID => new value)
        'show'            => array(), // show specified element
        'hide'            => array(), // hide specified element
        'removeClassName' => array(), // remove specified class name from element
        'addClassName'    => array()  // add specified class name to element
    );

    /**
     * Validation messages.
     *
     * @var array
     */
    protected $_messages = array(
        'error'   => array(),
        'success' => array(),
        'notice'  => array()
    );

    /**
     * Add action for response.
     *
     * @param string $actionName
     * @param string $elementId
     * @param mixed $value OPTIONAL
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addAction($actionName, $elementId, $value = null)
    {
        if (isset($this->_actions[$actionName])) {
            if (null === $value) {
                if (is_array($elementId)) {
                    foreach ($elementId as $oneId) {
                        $this->_actions[$actionName][] = $oneId;
                    }
                } else {
                    $this->_actions[$actionName][] = $elementId;
                }
            } else {
                $this->_actions[$actionName][$elementId] = $value;
            }
        }
        return $this;
    }

    /**
     * Add error message.
     *
     * @param string $message Error message
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addError($message)
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addError($row);
            }
        } else {
            $this->_messages['error'][] = $message;
        }
        return $this;
    }

    /**
     * Add notice message.
     *
     * @param mixed $message Message text
     * @param boolean $appendImportButton OPTIONAL Append import button to message?
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addNotice($message, $appendImportButton = false,$key='')
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addNotice($row);
            }
        } else {
            $this->_messages['notice'][] = $message . ($appendImportButton ? $this->getImportButtonHtml($key) : '');
        }
        return $this;
    }

    /**
     * Add success message.
     *
     * @param mixed $message Message text
     * @param boolean $appendImportButton OPTIONAL Append import button to message?
     * @return Mage_ImportExport_Block_Adminhtml_Import_Frame_Result
     */
    public function addSuccess($message, $appendImportButton = false,$key='')
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addSuccess($row);
            }
        } else {
            $this->_messages['success'][] = $message . ($appendImportButton ? $this->getImportButtonHtml($key) : '');
        }
        return $this;
    }

    /**
     * Import button HTML for append to message.
     *
     * @return string
     */
    public function getImportButtonHtml($key='')
    {
        return '&nbsp;&nbsp;<button onclick="editForm.startImport(\'' . $this->getImportStartUrl($key)
            . '\', \'' . 'publication_import_file'. '\');" class="scalable save"'
            . ' type="button"><span><span><span>' . $this->__('Import') . '</span></span></span></button>';
    }

    /**
     * Import start action URL.
     *
     * @return string
     */
    public function getImportStartUrl($key)
    {
        return $this->getUrl('*/*/start',array('lytarchiv'=>$key));
    }

    /**
     * Messages getter.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Messages rendered HTML getter.
     *
     * @return string
     */
    public function getMessagesHtml()
    {
        /** @var $messagesBlock Mage_Core_Block_Messages */
        $messagesBlock = $this->_layout->createBlock('core/messages');

        foreach ($this->_messages as $priority => $messages) {
            $method = "add{$priority}";

            foreach ($messages as $message) {
                $messagesBlock->$method($message);
            }
        }
        return $messagesBlock->toHtml();
    }

    /**
     * Return response as JSON.
     *
     * @return string
     */
    public function getResponseJson()
    {
        // add messages HTML if it is not already specified
        if (!isset($this->_actions['import_validation_messages'])) {
            $this->addAction('innerHTML', 'import_validation_messages', $this->getMessagesHtml());
        }
        return Mage::helper('core')->jsonEncode($this->_actions);
    }
}
