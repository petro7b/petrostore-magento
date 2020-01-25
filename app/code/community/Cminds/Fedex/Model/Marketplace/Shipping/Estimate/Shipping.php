<?php

class Cminds_Fedex_Model_Marketplace_Shipping_Estimate_Shipping
    extends Cminds_Marketplace_Model_Shipping_Estimate_Shipping
{
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $data = Mage::getModel('cminds_fedex/shipping_fedex')->collectRates($request);

        return $data;
    }
}