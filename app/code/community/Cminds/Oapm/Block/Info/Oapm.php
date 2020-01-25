<?php

/**
 * Cminds OAPM method info block.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Block_Info_Oapm extends Mage_Payment_Block_Info
{
    /**
     * Prepare information specific to current payment method
     *
     * @param   Varien_Object|array $transport
     * @return  Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if ($this->_paymentSpecificInformation !== null) {
            return $this->_paymentSpecificInformation;
        }

        /** @var Mage_Payment_Helper_Data $paymentHelper */
        $paymentHelper = Mage::helper('payment');

        /** @var array $additionalData */
        $additionalData = unserialize($this->getInfo()->getAdditionalData());

        $data = array();

        if (!empty($additionalData['recipient_name'])) {
            $data[$paymentHelper->__('Recipient Name')] = $additionalData['recipient_name'];
        }

        if (!empty($additionalData['recipient_email'])) {
            $data[$paymentHelper->__('Recipient Email')] = $additionalData['recipient_email'];
        }

        if (!empty($additionalData['recipient_note'])) {
            $data[$paymentHelper->__('Recipient Note')] = $additionalData['recipient_note'];
        }

        $transport = parent::_prepareSpecificInformation($transport);

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}