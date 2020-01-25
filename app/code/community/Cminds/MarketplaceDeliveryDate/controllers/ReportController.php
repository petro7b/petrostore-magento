<?php

class Cminds_MarketplaceDeliveryDate_ReportController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Order Instructions View.
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function indexAction()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            return $this->_redirect('customer/account/login');
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Delivery Info'));
        $this->renderLayout();
    }
}
