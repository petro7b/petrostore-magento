<?php

/**
 * Cminds OAPM rewrite checkout onepage billing block.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Block_Rewrite_Checkout_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');

        $oapmOrderQuoteId = $checkoutSession->getOapmOrderQuoteId();
        if ($oapmOrderQuoteId && $oapmOrderQuoteId === $this->getQuote()->getId()) {
            $this->_address = $this->getQuote()->getBillingAddress();
        } else {
            $this->_address = parent::getAddress();
        }

        return $this->_address;
    }
}