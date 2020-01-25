<?php
class Cminds_Marketplace_Adminhtml_BillingController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/suppliers/billing_grid');
    }
    public function indexAction() {
        $this->_title($this->__('Suppliers'));
        $this->loadLayout();
        $this->_setActiveMenu('suppliers');
        $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_billing_list'));
        $this->renderLayout();
    }

    public function editAction()
    {
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

            Mage::register('payment_data', $model);

            $this->loadLayout();
            $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_billing_edit'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('Payment does not exists'));
            $this->_redirect('*/*/');
        }

    }

    public function payAction()
    {
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

            Mage::register('payment_data', $model);

            $this->loadLayout();
            $this->_addContent($this->getLayout()->createBlock('marketplace/adminhtml_billing_pay'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('Payment does not exists'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost())
        {
            $model = Mage::getModel('marketplace/payments');
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }

            $filterInput = new Zend_Filter_LocalizedToNormalized(array(
                'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
            ));
            $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
                'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
            ));

            $paymentDate = $filterInput->filter($data['payment_date']);
            $paymentDate = $filterInternal->filter($paymentDate);
            $data['payment_date'] = $paymentDate;

            $model->setData($data);

            Mage::getSingleton('adminhtml/session')->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('marketplace')->__('No payment exists'));
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('marketplace')->__('Payment was successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketplace')->__('No data found to save'));
        $this->_redirect('*/*/');
    }

    public function exportCsvAction() {
        $fileName   = 'payments.csv';
        $grid       = $this->getLayout()->createBlock('marketplace/adminhtml_billing_list_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }


    public function exportExcelAction()
    {
        $fileName   = 'payments.xml';
        $grid       = $this->getLayout()->createBlock('marketplace/adminhtml_billing_list_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
}
