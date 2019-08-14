<?php

class AuIt_PublicationBasic_Model_Resource_Styles extends AuIt_PublicationBasic_Model_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('auit_publicationbasic/styles', 'style_id');
    }
}