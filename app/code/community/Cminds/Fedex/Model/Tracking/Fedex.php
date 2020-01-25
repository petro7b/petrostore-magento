<?php

class Cminds_Fedex_Model_Tracking_Fedex extends Cminds_Fedex_Model_Tracking_Abstract
{
    protected $_wsdl = "https://raw.githubusercontent.com/JeremyDunn/php-fedex-api-wrapper/master/src/FedEx/_wsdl/ShipService_v12.wsdl";
    const COUNTRY_CODE = "US";
    const LABEL_STOCK_TYPE = "PAPER_7X4.75";
    const WEIGHT_UNITS = "LB";
    const DEBUG_MODE = true;

    private $_logFile = "fedex_tracking.log";

    protected function doRequest($request)
    {
        return $this->_client->processShipment($request);
    }

    protected function _parseResponse($response)
    {
        if ($response->HighestSeverity
            != 'FAILURE' && $response->HighestSeverity != 'ERROR'
        ) {
            $this->setTrackingNumber($response->CompletedShipmentDetail
                ->CompletedPackageDetails
                ->TrackingIds
                ->TrackingNumber);
            $fp = fopen($this->_getLabelPath(), 'wb');
            fwrite($fp, ($response->CompletedShipmentDetail->CompletedPackageDetails
                ->Label->Parts->Image));
            fclose($fp);
        } else {
            throw new Exception($response->Notifications[0]->Message);
        }
    }

    protected function _prepareRequest()
    {
        $request = array();
        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' => $this->_getFedexConfig('key'),
                'Password' => $this->_getFedexConfig('password')
            )
        );

        $request['ClientDetail'] = array(
            'AccountNumber' => $this->_getFedexConfig('account'),
            'MeterNumber' => $this->_getFedexConfig('meter_number')
        );
        $request['TransactionDetail'] = array('CustomerTransactionId' => "");

        $request['Version'] = array(
            'ServiceId' => 'ship',
            'Major' => '12',
            'Intermediate' => '1',
            'Minor' => '0'
        );

        $request['RequestedShipment'] = array(
            'ShipTimestamp' => date('c'),
            'DropoffType' => 'REGULAR_PICKUP',
            'ServiceType' => $this->_getShippingMethod(),
            'PackagingType' => 'YOUR_PACKAGING',
            'Shipper' => $this->_getOriginAddress(),
            'Recipient' => $this->_getShippingAddress(),
            'ShippingChargesPayment' => $this->_getCharges(),
            'CustomsClearanceDetail' => $this->_getOrderData(),
            'LabelSpecification' => $this->_getLabelConf(),
            'CustomerSpecifiedDetail' => array(
                'MaskedData' => 'SHIPPER_ACCOUNT_NUMBER'
            ),
            'RateRequestTypes' => array('ACCOUNT'),
            'PackageCount' => 1,
            'RequestedPackageLineItems' => $this->_getItems(),
            'CustomerReferences' => array(
                '0' => array(
                    'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                    'Value' => 'TC007_07_PT1_ST01_PK01_SNDUS_RCPCA_POS'
                )
            )
        );

        return $request;
    }

    protected function _getFedexConfig($key)
    {
        return Mage::getStoreConfig('carriers/fedex/' . $key);
    }

    protected function _getShippingAddress()
    {
        $shippingAddress = $this->getOrder()->getShippingAddress();
        $region = Mage::getModel('directory/region')->loadByName($shippingAddress->getRegion(), self::COUNTRY_CODE);

        $recipient = array(
            'Contact' => array(
                'PersonName' => $shippingAddress->getName(),
                'CompanyName' => $shippingAddress->getCompany(),
                'PhoneNumber' => $shippingAddress->getTelephone()
            ),
            'Address' => array(
                'StreetLines' => $shippingAddress->getStreet(),
                'City' => $shippingAddress->getCity(),
                'StateOrProvinceCode' => $region->getCode(),
                'PostalCode' => $shippingAddress->getPostcode(),
                'CountryCode' => self::COUNTRY_CODE,
                'Residential' => false
            )
        );

        return $recipient;
    }

    protected function _getOriginAddress()
    {
        $shippingAddressId = $this->getVendor()->getDefaultShipping();
        $shippingAddress = Mage::getModel('customer/address')->load($shippingAddressId);
        $street = $shippingAddress->getStreet();
        $origin = array(
            'Contact' => array(
                'PersonName' => $this->getVendor()->getName(),
                'CompanyName' => $this->getVendor()->getCompany(),
                'PhoneNumber' => $this->getVendor()->getTelephone() ? $this->getVendor()->getTelephone() : "1234567890"
            ),
            'Address' => array(
                'StreetLines' => isset($street[0]) && $street[0] ? $street : array("Street"),
                'City' => $shippingAddress->getCity() ? $shippingAddress->getCity() : "Austin",
                'StateOrProvinceCode' => $shippingAddress->getRegion() ? $shippingAddress->getRegion() : "TX",
                'PostalCode' => $shippingAddress->getPostcode() ? $shippingAddress->getPostcode() : 73301,
                'CountryCode' => self::COUNTRY_CODE,
            )
        );

        return $origin;
    }

    protected function _getCharges()
    {
        $shippingChargesPayment = array(
            'PaymentType' => 'SENDER',
            'Payor' => array(
                'ResponsibleParty' => array(
                    'AccountNumber' => $this->_getFedexConfig('account'),
                    'Contact' => null,
                    'Address' => array('CountryCode' => self::COUNTRY_CODE)
                )
            )
        );

        return $shippingChargesPayment;
    }

    protected function _getLabelConf()
    {
        $labelSpecification = array(
            'LabelFormatType' => 'COMMON2D',
            'ImageType' => 'PDF',
            'LabelStockType' => self::LABEL_STOCK_TYPE);

        return $labelSpecification;
    }

    protected function addSpecialServices()
    {
        $specialServices = array(
            'SpecialServiceTypes' => array('COD'),
            'CodDetail' => array(
                'CodCollectionAmount' => array('Currency' => 'USD', 'Amount' => 150),
                'CollectionType' => 'ANY')
        );

        return $specialServices;
    }

    protected function _getOrderData()
    {
        $customerClearanceDetail = array(
            'DutiesPayment' => array(
                'PaymentType' => 'SENDER',
                'Payor' => array(
                    'ResponsibleParty' => array(
                        'AccountNumber' => $this->_getFedexConfig('account'),
                        'Contact' => null,
                        'Address' => array('CountryCode' => self::COUNTRY_CODE)
                    )
                )
            ),
            'DocumentContent' => 'NON_DOCUMENTS',
            'Commodities' => array(
                '0' => array(
                    'NumberOfPieces' => 1,
                    'CountryOfManufacture' => self::COUNTRY_CODE,
                    'Weight' => array(
                        'Units' => self::WEIGHT_UNITS,
                        'Value' => 1.0
                    )
                )
            ),
            'ExportDetail' => array(
                'B13AFilingOption' => 'NOT_REQUIRED'
            )
        );

        return $customerClearanceDetail;
    }

    protected function _getItems()
    {
        $items = array();
        $i = 1;

        foreach($this->getItems() AS $_item) {
            $items[] = array(
                'SequenceNumber' => $i,
                'GroupPackageCount' => 1,
                'Weight' => array(
                    'Value' => $_item->getWeight(),
                    'Units' => self::WEIGHT_UNITS
                )
            );
            $i++;
            return $items;
        }

        return $items;
    }

    protected function _getShippingMethod() {
        $method = Mage::helper("cminds_fedex")->getFedexType($this->getOrder());

        if($method) {
            return $method;
        }

        throw new Exception("This is not Fedex shipment");
    }

}