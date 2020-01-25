<?php

class Cminds_Marketplaceproductquestions_Helper_Email extends Mage_Core_Helper_Abstract
{

    private function getConfig($slug) {
        return Mage::getStoreConfig('marketplace_productquestions/productquestions_notifications_email/' . $slug);
    }

    public function notifyCustomerOnQuestion($customer, $productId, $questionBody) {
        $productData = Mage::getModel('catalog/product')->load($productId);
        $isEnabled = $this->getConfig('notify_on_question_sent');

        if($isEnabled) {
            $topic  = $this->getConfig('email_title_on_question_sent');
            $message = $this->getConfig('email_text_on_question_sent');

            $replacements = array('{{customer_name}}', '{{productName}}', '{{productLink}}', '{{questionBody}}');

            $productName = $productData->getName();
            $productLink = $productData->getProductUrl();
            $customerFullName = $customer->getFirstname() .' '. $customer->getLastname();

            $replaces = array($customerFullName, $productName, $productLink, $questionBody);

            $rTopic = str_replace($replacements, $replaces, $topic);
            $rMessage = str_replace($replacements, $replaces, $message);

            $this->_sendEmail($customerFullName, $customer->getEmail(), $rTopic, $rMessage);
        }
    }


    public function notifyCustomerOnAnswer($customer, $productId, $questionBody, $answerBody) {
        $productData = Mage::getModel('catalog/product')->load($productId);

        $isEnabled = $this->getConfig('notify_on_answer_placed');

        if($isEnabled) {
            $topic  = $this->getConfig('email_title_on_answer_placed');
            $message = $this->getConfig('email_text_on_answer_placed');

            $replacements = array('{{customer_name}}', '{{productName}}', '{{productLink}}', '{{questionBody}}', '{{answerBody}}');

            $productName = $productData->getName();
            $productLink = $productData->getProductUrl();
            $customerFullName = $customer->getFirstname() .' '. $customer->getLastname();

            $replaces = array($customerFullName, $productName, $productLink, $questionBody, $answerBody);

            $rTopic = str_replace($replacements, $replaces, $topic);
            $rMessage = str_replace($replacements, $replaces, $message);

            $this->_sendEmail($customerFullName, $customer->getEmail(), $rTopic, $rMessage);
        }
    }

    public function notifyAdminOnQuestion($customer, $productId, $questionBody, $questionId) {
        $productData = Mage::getModel('catalog/product')->load($productId);

        $isEnabled = $this->getConfig('notify_admin_on_question_sent');

        if ($isEnabled) {
            $topic = $this->getConfig('email_title_to_admin_on_question_sent');
            $message = $this->getConfig('email_text_to_admin_on_question_sent');

            $replacements = array('{{customer_name}}', '{{productName}}', '{{productLink}}', '{{questionBody}}', '{{questionId}}');

            $productName = $productData->getName();
            $productLink = $productData->getProductUrl();

            if(is_string($customer)) {
                $customerFullName = $customer;
            }
            else {
                $customerFullName = $customer->getFirstname() . ' ' . $customer->getLastname();
            }

            $replaces = array($customerFullName, $productName, $productLink, $questionBody, $questionId);

            $rTopic = str_replace($replacements, $replaces, $topic);
            $rMessage = str_replace($replacements, $replaces, $message);

            $this->_sendEmail($customerFullName, Mage::getStoreConfig('trans_email/ident_general/email'), $rTopic, $rMessage);
        }
    }


    public function notifySupplierOnQuestion($customer, $productId, $questionBody, $questionId, $supplierId) {
        $productData = Mage::getModel('catalog/product')->load($productId);
        $supplier = Mage::getModel('customer/customer')->load($supplierId);
        $isEnabled = $this->getConfig('notify_supplier');

        if ($isEnabled) {
            $topic = $this->getConfig('email_title_to_notify_supplier');
            $message = $this->getConfig('email_text_to_notify_supplier');

            $replacements = array('{{customer_name}}', '{{productName}}', '{{productLink}}', '{{questionBody}}', '{{questionId}}');

            $productName = $productData->getName();
            $productLink = $productData->getProductUrl();

            $supplierFullName = $supplier->getFirstname() . ' ' . $supplier->getLastname();
            $supplierEmail = $supplier->getEmail();

            if(is_string($customer)) {
                $customerFullName = $customer;
            }
            else {
                $customerFullName = $customer->getFirstname() . ' ' . $customer->getLastname();
            }

            $replaces = array($customerFullName, $productName, $productLink, $questionBody, $questionId);

            $rTopic = str_replace($replacements, $replaces, $topic);
            $rMessage = str_replace($replacements, $replaces, $message);

            $this->_sendEmail($supplierFullName, $supplierEmail, $rTopic, $rMessage);
        }
    }
    public function _sendEmail($receiverName, $receiverEmail, $title, $content) {
        $email = Mage::getModel('core/email_template');
        $email->loadDefault('marketplaceproductquestions_email_question_sent');
        $email->setTemplateSubject($title);
        $emailVariables['content'] = $content;

        $email->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
        $email->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));

        try {
            $email->send($receiverEmail, $receiverName, $emailVariables);
        }
        catch(Exception $error) {
            Mage::log($error->getMessage());
        }
    }
}