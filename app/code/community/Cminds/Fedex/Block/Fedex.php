<?php

class Cminds_Fedex_Block_Fedex extends Mage_Core_Block_Template
{
    public function getFormAction()
    {
        return $this->getUrl('marketplace/fedex/post');
    }
}
