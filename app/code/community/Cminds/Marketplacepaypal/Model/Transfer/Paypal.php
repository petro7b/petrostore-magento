<?php
class Cminds_Marketplacepaypal_Model_Transfer_Paypal extends Cminds_Marketplacepaypal_Model_Transfer_Abstract {
    protected $_apiType = 'paypal/api_nvp';

    public function canTransfer() {
        if($this->getOrder()->getPayment()->getMethodInstance()->getTitle() == 'PayPal') {
            return true;
        }

        return ($this->getOrder()->getBaseTotalDue() == 0 || !$this->getOrder()->getBaseTotalDue());
    }

    public function transfer() {
        $this->_getApi();
        $vendorList = $this->_prepareVendorList();

        $request = array();
        $i = 0;
        $request['USER'] = $this->_api->getApiUsername();
        $request['PWD'] = $this->_api->getApiPassword();
        $request['SIGNATURE'] = $this->_api->getApiSignature();
        $request['VERSION'] = 2.3;
        $request['CURRENCYCODE'] = Mage::app()->getStore()->getCurrentCurrencyCode();
        $request['METHOD'] = 'MassPay';
        if(count($vendorList) == 0) return;

        $canPay = false;
        foreach($vendorList AS $vendor => $amount) {
            $vendorEmail = $this->_getVendorPaypalData($vendor);

            if(!$vendorEmail) continue;

            $request['L_EMAIL' . $i] = $vendorEmail;
            $request['L_AMT' . $i] = $amount;
            $request['RECEIVERTYPE' . $i] = 'EmailAddress';

            $canPay = true;
        }
        if($canPay) {
            $this->doPayment($request);
        }
    }

    public function forcePayment($vendor_id, $amount) {
        $this->_getApi();

        $request = array();
        $vendorEmail = $this->_getVendorPaypalData($vendor_id);

        if(!$vendorEmail) throw new Mage_Exception("Supplier does not have specified paypal login");

        $request['USER'] = $this->_api->getApiUsername();
        $request['PWD'] = $this->_api->getApiPassword();
        $request['SIGNATURE'] = $this->_api->getApiSignature();
        $request['VERSION'] = 2.3;
        $request['CURRENCYCODE'] = Mage::app()->getStore()->getCurrentCurrencyCode();
        $request['METHOD'] = 'MassPay';
        $request['L_EMAIL0'] = $vendorEmail;
        $request['L_AMT0'] = $amount;
        $request['RECEIVERTYPE'] = 'EmailAddress';

        return $this->doPayment($request, false);
    }

    private function doPayment($request, $useOrder = true) {
        try {
            $http = new Varien_Http_Adapter_Curl();
            $config = array(
                'timeout'    => 60,
                'verifypeer' => false,
                'verifyhost' => false,
            );

            if ($this->_api->getUseProxy()) {
                $config['proxy'] = $this->getProxyHost(). ':' . $this->getProxyPort();
            }
            if ($this->_api->getUseCertAuthentication()) {
                $config['ssl_cert'] = $this->getApiCertificate();
            }
            $http->setConfig($config);
            $http->write(
                Zend_Http_Client::POST,
                $this->_api->getApiEndpoint(),
                '1.1',
                $this->_headers,
                http_build_query($request)
            );
            $response = $http->read();
        } catch (Exception $e) {
            $debugData['http_error'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            throw $e;
        }
        $res = $this->parseResponse($response);

        if($useOrder) {
            $suppliers = $this->_prepareVendorList();

            foreach($suppliers AS $supplier => $amount) {
                $this->_savePayment($supplier, $amount);
            }
        } else {
            return strtolower($res['ACK']) == 'success';
        }

        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);

        $debugData['response'] = $response;

        if ($http->getErrno()) {
            Mage::logException(
                sprintf('PayPal NVP CURL connection error #%s: %s', $http->getErrno(), $http->getError())
            );
            $http->close();

        }

        $http->close();
    }

    private function _savePayment($vendorId, $amount) {
        $model = Mage::getModel('marketplace/payments');

        $collection = $model->getCollection()
            ->addFieldToFilter('order_id', $this->getOrder()->getId())
            ->addFieldToFilter('supplier_id', $vendorId);
        $model = $collection->getFirstItem();

        if (!$model->getId()) {
            $model->setOrderId($this->getOrder()->getId());
            $model->setSupplierId($vendorId);
        }

        $model->setPaymentDate(date('Y-m-d H:i:s'));
        $model->setAmount($amount);
        $model->save();
    }

    private function parseResponse($resp)
    {
        $a=explode("&", $resp);
        $out = array();
        foreach ($a as $v){
            $k = strpos($v, '=');
            if ($k) {
                $key = trim(substr($v,0,$k));
                $value = trim(substr($v,$k+1));
                if (!$key) continue;
                $out[$key] = urldecode($value);
            } else {
                $out[] = $v;
            }
        }
        return $out;
    }
}
