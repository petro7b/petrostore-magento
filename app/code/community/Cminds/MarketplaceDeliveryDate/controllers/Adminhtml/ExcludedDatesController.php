<?php

class Cminds_MarketplaceDeliveryDate_Adminhtml_ExcludedDatesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/customer');
    }

    /**
     * @param $key
     * @return $this
     */
    protected function _initCustomer($key)
    {
        $customerId = (int)$this->getRequest()->getParam($key);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    public function gridAction()
    {
        $this->_initCustomer('id');

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'marketplace_delivery_date/adminhtml_customer_edit_tab_grid_excludedDates',
                    'customer_edit_tab_grid_excludedDates'
                )
                ->toHtml()
        );
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('marketplace_delivery_date/excluded')->load($this->getRequest()->getParam('id'));
                $supplierId = $model->getSupplierId();
                $model->delete();

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('adminhtml')
                    ->__('Date was successfully deleted'));
                $this->_redirect('*/customer/edit', array('id' => $supplierId));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/customer/edit', array('id' => $supplierId));
            }
        }
        $this->_redirect('*/customer/edit', array('id' => $supplierId));
    }
}
