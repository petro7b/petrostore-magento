<?php

class Cminds_MarketplaceDeliveryDate_SettingsController extends Cminds_Supplierfrontendproductuploader_Controller_Action {


    /**
     * Check permission.
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $hasAccess = $this->_getHelper()->hasAccess();

        if (!$hasAccess) {
            $this->getResponse()
                ->setRedirect($this->_getHelper('supplierfrontendproductuploader')->getSupplierLoginPage());
        }
    }

    /**
     * Show form.
     */
    public function settingsAction()
    {
        if (!Mage::helper('marketplace_delivery_date')->getOrderLeadTimeConfig()) {
            $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
            $this->getResponse()->setHeader('Status', '404 File not found');
            $this->_forward('defaultNoRoute');
        }

        $this->_renderBlocks(true);
    }

    /**
     * Save customer data.
     */
    public function settingsSaveAction()
    {
        $postData = $this->getRequest()->getPost();

        if (!Mage::helper('marketplace_delivery_date')->getOrderLeadTimeConfig()) {
            $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
            $this->getResponse()->setHeader('Status', '404 File not found');
            $this->_forward('defaultNoRoute');
        }

        try {
            $customerData = false;
            
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerData = Mage::getModel('customer/customer')
                    ->load(Mage::getSingleton('customer/session')->getId());
            }

            if (!$customerData) {
                throw new ErrorException('Supplier does not exists');
            }

            if (isset($postData['submit'])) {
                if (!is_numeric($postData['order_lead_time'])) {
                    throw new Exception($this->_getHelper()->__('Wrong format for order lead time.'));
                }

                $customerData->setCutoffTimeFinish($postData['order_cutoff_time_finish']);
                $customerData->setOrderLeadTime($postData['order_lead_time']);
                $customerData->setAllowedWeekDays(json_encode($postData['weekdays']));
                $customerData->save();

                if (isset($postData['exclude_date']) && !empty($postData['exclude_date'])) {
                    $excludedDate = Mage::getModel('marketplace_delivery_date/excluded');
                    $excludedDate->setSupplierId($customerData->getId());
                    $excludedDate->setDate($postData['exclude_date']);
                    $excludedDate->save();
                }

                if ($postData['removed_excluded_days']) {
                    $ids = explode(',', $postData['removed_excluded_days']);
                    foreach ($ids as $id) {
                        if (!$id) {
                            continue;
                        };
                        $excludedDate = Mage::getModel('marketplace_delivery_date/excluded')->load($id);
                        if ($excludedDate->getId()) {
                            $excludedDate->delete();
                        }
                    }
                }
            }

            Mage::getSingleton('core/session')
                ->addSuccess($this->_getHelper()->__('Your delivery date settings was changed.'));
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getBaseUrl() . 'deliverydate/settings/settings/');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getBaseUrl() . 'deliverydate/settings/settings/');
            Mage::log($e->getMessage());
        }
    }
}
