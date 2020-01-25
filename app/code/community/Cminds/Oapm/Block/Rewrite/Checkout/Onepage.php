<?php

/**
 * Cminds OAPM rewrite checkout onepage block.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Block_Rewrite_Checkout_Onepage extends Mage_Checkout_Block_Onepage
{
    /**
     * Get 'one step checkout' step data.
     *
     * @return array
     */
    public function getSteps()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');

        $quoteId =  $this->getQuote()->getId();
        $oapmOrderQuoteId = $checkoutSession->getOapmOrderQuoteId();

        $steps = array();
        $stepCodes = $this->_getStepCodes();

        if ($this->isCustomerLoggedIn()) {
            $stepCodes = array_diff($stepCodes, array('login'));
        }

        foreach ($stepCodes as $step) {
            $steps[$step] = $this->getCheckout()->getStepData($step);
        }

        if ($oapmOrderQuoteId && $oapmOrderQuoteId === $quoteId) {
            foreach ($steps as $stepCode => &$stepData) {
                if (in_array($stepCode, array('billing', 'shipping', 'shipping_method', 'payment'))) {
                    $stepData['allow'] = true;
                }
            }
        }

        return $steps;
    }

    /**
     * Get active step.
     *
     * @return string
     */
    public function getActiveStep()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');

        if ($this->isCustomerLoggedIn()) {
            $quoteId =  $this->getQuote()->getId();
            $oapmOrderQuoteId = $checkoutSession->getOapmOrderQuoteId();

            if ($oapmOrderQuoteId && $oapmOrderQuoteId === $quoteId) {
                return 'payment';
            }

            return 'billing';
        }

        return 'login';
    }
}
