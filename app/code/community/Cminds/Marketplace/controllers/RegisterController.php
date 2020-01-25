<?php

require_once Mage::getModuleDir('controllers', 'Cminds_Supplierfrontendproductuploader') . DS . 'RegisterController.php';

class Cminds_Marketplace_RegisterController extends Cminds_Supplierfrontendproductuploader_RegisterController {

    public function createPostAction()
    {
        if(!$this->_getHelper()->canRegister()) {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
            return;
        }

        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if (!$this->getRequest()->isPost()) {
            $canRegister = $this
                ->_getHelper()
                ->canRegister();
            
            if (!$canRegister) {
                $errUrl = Mage::getUrl('*/*/index', array('_secure' => true));
            } else {
                $errUrl = Mage::getUrl('supplier/register', array('_secure' => true));
            }

            $this->_redirectError($errUrl);
            
            return;
        }

        $customer = $this->_getCustomer();

        try {
            $errors = $this->_getCustomerErrors($customer);

            if (empty($errors)) {
                if(method_exists($customer, 'cleanPasswordsValidationData')) {
                    $customer->cleanPasswordsValidationData();
                }

                $customFieldsValues = $this->_setCustomFields($customer, $this->getRequest()->getPost());
                $waitingForApproval = null;

                if($customer->getData('rejected_notfication_seen')) {
                    $customer->setNewCustomFieldsValues(serialize($customFieldsValues));
                } else {
                    $customer->setCustomFieldsValues(serialize($customFieldsValues));
                }
                $customer->setTermsConditionsAgreed(1);
                $customer->save();
                Mage::helper('supplierfrontendproductuploader/email')->notifyAdminOnSupplierRegister($customer);
                Mage::dispatchEvent('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );
                $this->_successProcessRegistration($customer);
                return;
            } else {
                $this->_addSessionError($errors);
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = Mage::getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $session->addError($message);
        } catch (Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost())
                ->addException($e, $this->__('Cannot save the supplier.'));
        }
        
        $canRegister = $this
            ->_getHelper()
            ->canRegister();

        if (!$canRegister) {
            $errUrl = Mage::getUrl('*/*/index', array('_secure' => true));
        } else {
            $errUrl = Mage::getUrl('supplier/register', array('_secure' => true));
        }
        
        $this->_redirectError($errUrl);
    }

    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess(
            $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );

        Mage::helper('supplierfrontendproductuploader/email')
            ->welcomeNewSupplier($customer);

        $successUrl = Mage::getUrl('supplier/index/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }

        return $successUrl;
    }

    protected function _setCustomFields($customer, $postData) {

        $customFieldsCollection = Mage::getModel('marketplace/fields')->getCollection();
        $customFieldsValues = array();

        foreach ($customFieldsCollection as $field) {
            if (!$field->getShowInRegistration()) {
                continue;
            }

            if (isset($postData[$field->getName()])) {
                if ($field->getIsRequired() && $postData[$field->getName()] == '') {
                    throw new Exception("Field ".$field->getName()." is required");
                }

                if ($field->getType() == 'date' && !strtotime($postData[$field->getName()])) {
                    throw new Exception("Field ".$field->getName()." is not valid date");
                }

                if ($field->getMustBeApproved()) {
                    $customer->setData('rejected_notfication_seen', 2);
                }
                $customFieldsValues[] = array('name' => $field->getName(), 'value' => $postData[$field->getName()]);
            }
        }
        return $customFieldsValues;
    }

    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();
        if ($customer->isConfirmationRequired()) {
            Mage::helper('supplierfrontendproductuploader/email')
                ->welcomeNewSupplier($customer);
            if($this->_getHelper()->isSupplierNeedsToBeApproved()) {
                Mage::helper('supplierfrontendproductuploader/email')->notifySupplierNeedApprove($customer);
            }
            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = Mage::getUrl('supplier/index/index', array('_secure' => true));
        } else {
            if($this->_getHelper()->isSupplierNeedsToBeApproved()) {
                Mage::helper('supplierfrontendproductuploader/email')->notifySupplierNeedApprove($customer);
                Mage::getSingleton('customer/session')->addError(
                    Mage::helper('supplierfrontendproductuploader')->__('Thank you for creating account on our store. Your account must be approved by store admin. When it will be done we will send you an email.')
                );
                $url = Mage::getBaseUrl();
            } else {
                $session->setCustomerAsLoggedIn($customer);
                $url = $this->_welcomeCustomer($customer);
            }
        }
        $this->_redirectSuccess($url);
        return $this;
    }
}
