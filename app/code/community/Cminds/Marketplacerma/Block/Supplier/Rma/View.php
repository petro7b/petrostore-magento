<?php
class Cminds_Marketplacerma_Block_Supplier_Rma_View extends Cminds_Marketplacerma_Block_Supplier_Rma_Abstract {
    public function _construct() {
        $this->setTemplate('marketplacerma/supplier/rma/view.phtml');
    }

    public function getRma() {
        return Mage::getModel('cminds_rma/rma')->load($this->getRmaId());
    }
}