<?php

class Cminds_Marketplacepaypal_ManageController
    extends Cminds_Marketplace_Controller_Action
{

    public function settingsAction()
    {
        $this->_renderBlocks();
    }

    public function settingsSaveAction()
    {
        $helper = Mage::helper('marketplacepaypal');
        $postData = $this->getRequest()->getPost();
        $supplier = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getId());

        try {
            $supplier->setPaypalEmail($postData['paypal']);
            $supplier->save();
            Mage::getSingleton('core/session')->addSuccess($helper->__(
                'Paypal email was saved'
            ));
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('supplier/manage/settings'));
        } catch(Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getBaseUrl() . 'marketplace/settings/profile/');
        }
    }
}