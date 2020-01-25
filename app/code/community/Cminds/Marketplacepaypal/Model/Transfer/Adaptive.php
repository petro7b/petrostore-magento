<?php

class Cminds_Marketplacepaypal_Model_Transfer_Adaptive extends Cminds_Marketplacepaypal_Model_Transfer_Abstract
{
    const ACTION = "CREATE";
    const TYPE = "PERSONAL";
    const FEE_PAYER = "SENDER";
    protected $_apiType = 'paypal/api_nvp';

    private static $_fundingTypes = array('ECHECK', 'BALANCE', 'CREDITCARD');

    private function _geAppID() {
        return Mage::getStoreConfig("marketplace_configuration/paypal_transfer/app_id");
    }

    public function transfer()
    {
        $vendorList = $this->_prepareVendorList();

        if (count($vendorList) == 0) return;

        $canPay = false;
        $receivers = array();

        foreach ($vendorList AS $vendor => $amount) {
            $vendorEmail = $this->_getVendorPaypalData($vendor);

//            if (!$vendorEmail) continue;
            $receivers[] = array(
                'Amount'           => $amount,
                'Email'            => 'wojtek+buyer@cminds.com',
                'AccountID'        => '',
                'InvoiceID'        => '',
                'PaymentType'      => self::TYPE,
                'PaymentSubType'   => '',
                'Phone'            => array('CountryCode' => '', 'PhoneNumber' => '', 'Extension' => ''),
                'Primary'          => ''
            );

            $canPay = true;
        }

        if ($canPay) {
            $this->doPayment($receivers);
        }


    }

    public function canTransfer()
    {
        if ($this->getOrder()->getPayment()->getMethodInstance()->getTitle() == 'PayPal') {
            return true;
        }

        return ($this->getOrder()->getBaseTotalDue() == 0 || !$this->getOrder()->getBaseTotalDue());
    }

    private function _getPrepareSdk()
    {
        $this->_getApi();

        $sdkConfig = array(
            "mode" => "sandbox",
            "acct1.UserName" => $this->_api->getApiUsername(),
            "acct1.Password" => $this->_api->getApiPassword(),
            "acct1.Signature" => $this->_api->getApiSignature(),
            "acct1.AppId" => $this->_geAppID()
        );

        return $sdkConfig;
    }

    function doPayment($receivers)
    {
        $PayPalConfig = array(
            'Sandbox' => true,
            'DeveloperAccountEmail' => '',
            'ApplicationID' => $this->_geAppID(),
            'DeviceID' => '',
            'IPAddress' => $_SERVER['REMOTE_ADDR'],
            'APISubject' => ''
        );

        $PayPalConfig = array_merge($PayPalConfig, $this->_getPrepareSdk());
        $PayPal = Mage::getModel('marketplacepaypal/lib_adaptive')->loadData($PayPalConfig);
        $PayRequestFields = array(

            'ActionType' => self::ACTION,
            'CancelURL' => '',
            'CurrencyCode' => Mage::app()->getStore()->getCurrentCurrencyCode(),
            'FeesPayer' => self::FEE_PAYER,
            'IPNNotificationURL' => '',
            'Memo' => '',
            'Pin' => '',
            'PreapprovalKey' => '',
            'ReturnURL' => '',
            'ReverseAllParallelPaymentsOnError' => '',
            'SenderEmail' => '',
            'TrackingID' => ''
        );

        $ClientDetailsFields = array(
            'CustomerID' => '',
            'CustomerType' => '',
            'GeoLocation' => '',
            'Model' => '',
            'PartnerName' => 'Always Give Back'
        );

        $AccountIdentifierFields = array(
            'Email' => '',
            'Phone' => array('CountryCode' => '', 'PhoneNumber' => '', 'Extension' => '')
        );

        $PayPalRequestData = array(
            'PayRequestFields' => $PayRequestFields,
            'ClientDetailsFields' => $ClientDetailsFields,
            'FundingTypes' => self::$_fundingTypes,
            'Receivers' => $receivers,
            'SenderIdentifierFields' => array("UseCredentials" => ""),
            'AccountIdentifierFields' => $AccountIdentifierFields
        );

        $PayPalResult = $PayPal->Pay($PayPalRequestData);

        return $PayPalResult;
    }
}
