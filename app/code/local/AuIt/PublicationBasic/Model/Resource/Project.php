<?php

class AuIt_PublicationBasic_Model_Resource_Project extends AuIt_PublicationBasic_Model_Resource_Abstract
{
    public function _construct()
    {
        $this->_init('auit_publicationbasic/projects', 'project_id');
    }
}