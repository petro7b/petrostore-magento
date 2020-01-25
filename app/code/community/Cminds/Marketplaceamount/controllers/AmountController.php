<?php

class Cminds_Marketplaceamount_AmountController extends Cminds_Supplierfrontendproductuploader_Controller_Action
{
    public function viewAction()
    {
        $this->_renderBlocks(true);
    }

    public function saveAction()
    {
        $minOrderAmount = $this->getRequest()->getPost('supplier_min_order_amount');
        $minOrderQty = $this->getRequest()->getPost('supplier_min_order_qty');
        $minOrderAmountPer = $this->getRequest()->getPost('supplier_min_order_amount_per');
        $customerSession = Mage::getSingleton('customer/session');

        try {
            if (!$customerSession->isLoggedIn()) {
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }

            $customer = $customerSession->getCustomer();
            if ($minOrderAmount) {
                $customer->setData('supplier_min_order_amount', $minOrderAmount);
            }

            if ($minOrderQty) {
                $customer->setData('supplier_min_order_qty', $minOrderQty);
            }
            if ($minOrderAmountPer) {
                $customer->setData('supplier_min_order_amount_per', $minOrderAmountPer);
            }

            $customer->save();

            $customerSession->addSuccess('Success!');
            $this->_redirect('supplier/amount/view');
        } catch (Exception $e) {
            $customerSession->addError($e->getMessage());
            Mage::log($e->getMessage());
        }
    }
}
