<?php
class Cminds_Marketplacepaypal_Adminhtml_PaymentsController extends Mage_Adminhtml_Controller_Action {
    public function payAction() {
        $orderId = $this->getRequest()->getParam('order_id', null);
        $supplierId = $this->getRequest()->getParam('supplier_id', null);
        $model = Mage::getModel('marketplace/payments');
        if ($supplierId && $orderId) {

            $collection = $model->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('supplier_id', $supplierId);
                
            $model = $collection->getFirstItem();

            if (!$model->getId()) {
                $model->setOrderId($orderId);
                $model->setPaymentDate(date('Y-m-d H:is'));
                $model->setSupplierId($supplierId);
                $model->save();
            }

            $supplier = Mage::getModel('customer/customer')->load($supplierId);
            $order = Mage::getModel('sales/order')->load($orderId);

            Mage::register('payment_data', $model);
            Mage::register('supplier_data', $supplier);
            Mage::register('order_data', $order);

            $this->loadLayout();
            $this->_addContent($this->getLayout()->createBlock('marketplacepaypal/adminhtml_billing_pay'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('Payment does not exists'));
            $this->_redirect('*/*/');
        }
    }

    public function processPaymentAction()
    {
        $data = $this->getRequest()->getPost();

        try {
            if (isset($data['supplier_id'])) {
                $customer = Mage::getModel('customer/customer')->load($data['supplier_id']);

                if (!$customer->getId()) {
                    throw new Mage_Exception($this->__("Supplier does not exists"));
                }
            }

            $paypalMoneyTransfer = Mage::getModel('marketplacepaypal/transfer_paypal');

            $res = $paypalMoneyTransfer->forcePayment($data['supplier_id'], $data['amount']);

            if (!$res) {
                throw new Mage_Exception($this->__("Payment Failed"));
            }

            $model = Mage::getModel('marketplace/payments');
            $collection = $model->getCollection()
                ->addFieldToFilter('order_id', $data['order_id'])
                ->addFieldToFilter('supplier_id', $data['supplier_id']);
            $model = $collection->getFirstItem();

            if (!$model->getId()) {
                $model->setOrderId($data['order_id']);
                $model->setSupplierId($data['supplier_id']);
            }
            $model->setAmount($model->getAmount() + $data['amount']);
            $model->setPaymentDate(date('Y-m-d H:i:s'));
            $model->save();

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Payment Successful"));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/billing/');
    }
}