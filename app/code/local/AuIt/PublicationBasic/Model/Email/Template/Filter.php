<?php
class AuIt_PublicationBasic_Model_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{

    public function auitVariable($name,$default='')
    {
		return $this->_getVariable($name, $default);
    }
    public function getVariables()
    {
		return $this->_templateVars;
    }

}
