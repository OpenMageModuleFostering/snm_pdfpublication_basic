<?php

class AuIt_PublicationBasic_Model_Resource_Template extends AuIt_PublicationBasic_Model_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('auit_publicationbasic/templates', 'template_id');
    }

}