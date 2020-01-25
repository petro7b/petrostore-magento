<?php

abstract class Cminds_Fedex_Model_Tracking_Abstract extends Varien_Object
{
    const DEBUG_MODE = true;
    const LABEL_EXTENSION = ".pdf";
    private $_logFile;
    protected $_wsdl;
    private $_items;
    protected $_client;

    public function request()
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        try {
            $this->_client = new SoapClient($this->_wsdl, array("trace" => 1));
            $request = $this->_prepareRequest();

            if (self::DEBUG_MODE) {
                $this->_log($request);
            }

            $response = $this->doRequest($request);
            $this->_parseResponse($response);
        } catch (SoapFault $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    protected function _log($message)
    {
        Mage::log($message, null, $this->_logFile);
    }

    protected function applyFilters($items)
    {
        return $items;
    }

    public function getItems()
    {
        $items = $this->getOrder()->getAllItems();
        $this->applyFilters($items);
        $this->_items = $items;

        return $this->_items;
    }

    protected function doRequest($request)
    {
        return array();
    }

    protected function _getLabelPath()
    {
        $path = getcwd() . "/media/shipping_labels";

        if (!is_dir($path)) {
            mkdir($path, 0777);
        }

        $path .= "/" . $this->getTrackingNumber();
        $path .= self::LABEL_EXTENSION;

        return $path;
    }
}