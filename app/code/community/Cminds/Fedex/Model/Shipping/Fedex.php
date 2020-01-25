<?php

class Cminds_Fedex_Model_Shipping_Fedex extends Mage_Usa_Model_Shipping_Carrier_Fedex
{
    /**
     * @var array Cached rates fetched previously from fedex
     */
    private $cacheSuppliers = array();
    /**
     * @var array Cached object of the vendor
     */
    protected $vendor = array();

    /**
     * @var array day mapping
     */
    protected static $daysMapping = array(
        "ONE_DAY" => 1,
        "TWO_DAYS" => 2,
        "THREE_DAYS" => 3,
        "FOUR_DAYS" => 4,
        "FIVE_DAYS" => 5,
        "SIX_DAYS" => 6,
        "SEVEN_DAYS" => 7,
        "EIGHT_DAYS" => 8,
        "NINE_DAYS" => 9,
        "TEN_DAYS" => 10,
        "ELEVEN_DAYS" => 11,
        "TWELVE_DAYS" => 12,
        "THIRTEEN_DAYS" => 13,
        "FOURTEEN_DAYS" => 14,
        "FIFTEEN_DAYS" => 15,
        "SIXTEEN_DAYS" => 16,
        "SEVENTEEN_DAYS" => 17,
        "EIGHTEEN_DAYS" => 18,
        "NINETEEN_DAYS" => 19,
    );

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return false|Mage_Core_Model_Abstract
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');
        $this->_request = $request;

        $vendor_id = $request->getVendorId();
        $vendor = $this->getVendor($vendor_id);
        $quote = Mage::getModel("checkout/session")->getQuote();

        if ($vendor_id) {
            $rates = $this->getCachedVendors($vendor_id);

            if (!$rates) {
                $request->setPackageQty(count($request->getAllItems()));
                $totalWeight = 0;
                foreach ($request->getAllItems() AS $product) {
                    $totalWeight = $totalWeight + $product->getWeight();
                }
                $request->setPackageWeight($totalWeight);
                $request->setOrigPostcode(90210);
                $request->setDestPostcode($quote->getShippingAddress()->getPostcode());

                $this->setRequest($request);

                $rates = $this->_getQuotes();
                $this->cacheSuppliers[$vendor_id] = $rates;

                if (is_array($rates)) {
                    foreach ($rates AS $rate) {
                        if (!$rate->ServiceType) {
                            continue;
                        }
                        $allowedMethods = explode(',',
                            $this->getConfigData('allowed_methods'));
                        if (!in_array($rate->ServiceType,
                            $allowedMethods)
                        ) {
                            continue;
                        }

                        $r = $this->prepareResult($rate, $vendor);
                        $result->append($r);
                    }
                } else {
                    $r = $this->prepareResult($rates, $vendor);
                    $result->append($r);
                }
            }
        }

        return $result;
    }

    /**
     * @param $vendor_id
     *
     * @return mixed
     */
    protected function getVendor($vendor_id)
    {
        if (empty($this->vendor[$vendor_id])) {
            $this->vendor[$vendor_id] = Mage::getModel("customer/customer")
                ->load($vendor_id);
        }

        return $this->vendor[$vendor_id];
    }

    /**
     * @param $vendor_id
     *
     * @return bool
     */
    protected function getCachedVendors($vendor_id)
    {
        if (isset($this->cacheSuppliers[$vendor_id])) {
            return $this->cacheSuppliers[$vendor_id];
        }

        return false;
    }

    /**
     * @param $rate
     * @param $vendor
     *
     * @return false|Mage_Core_Model_Abstract
     */
    protected function prepareResult(
        $rate,
        $vendor
    ) {
        if (!is_object($vendor)) {
            $vendor = $this->getVendor($vendor);
        }

        $method = Mage::getModel('shipping/rate_result_method');
        $method->setCarrier('marketplace_estimated_time');
        $method->setMethod('marketplace_estimated_time' . $rate->ServiceType);
        $additionalData = array(
            'vendor_id' => $vendor->getId(),
            'is_ground' => $rate->ServiceType == 'FEDEX_GROUND' ? 1 : 0,
        );

        $method->setMethodTitle($this->getCode('method',
            $rate->ServiceType));
        $method->setMethodDescription(serialize($additionalData));

        $name = $this->getCode('method',
            $rate->ServiceType);

        if (
            isset($rate->RatedShipmentDetails[0]) &&
            isset($rate->RatedShipmentDetails[0]->ShipmentRateDetail) &&
            isset($rate->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetChargeWithDutiesAndTaxes) &&
            isset($rate->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetChargeWithDutiesAndTaxes->Amount)
        ) {
            $method->setPrice($rate
                ->RatedShipmentDetails[0]
                ->ShipmentRateDetail
                ->TotalNetChargeWithDutiesAndTaxes
                ->Amount);
        } else {
            $method->setPrice(10);
        }
        $method->setCarrierTitle($name);
        $method->setData('vendor_id', $vendor->getId());

        return $method;
    }

    /**
     * @param string $purpose
     *
     * @return mixed|null|string
     */
    protected function _doRatesRequest($purpose)
    {

        $ratesRequest = $this->_formRateRequest($purpose);

        $ratesRequest['ReturnTransitAndCommit'] = 1;
        $requestString = serialize($ratesRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = array('request' => $ratesRequest);
        if ($response === null) {
            try {
                $client = $this->_createRateSoapClient();
                $response = $client->getRates($ratesRequest);
                $this->_setCachedQuotes($requestString,
                    serialize($response));
                $debugData['result'] = $response;
            } catch (Exception $e) {
                $debugData['result'] = array(
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                );
                Mage::logException($e);
            }
        } else {
            $response = unserialize($response);
            $debugData['result'] = $response;
        }
        $this->_debug($debugData);

        return $response;
    }

    /**
     * @return array
     */
    protected function _getQuotes()
    {
        $response = $this->_doRatesRequest(self::RATE_REQUEST_GENERAL);
        return isset($response->RateReplyDetails) ? $response->RateReplyDetails : array();
    }

    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     *
     * @return array
     */


    protected function _formRateRequest($purpose)
    {
        $r = $this->_rawRequest;
        $vendor_id = $this->_request->getVendorId();
        $vendor = $this->getVendor($vendor_id);
        $ratesRequest = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key' => $vendor->getFedexKey(),
                    'Password' => $vendor->getFedexPassword()
                )
            ),
            'ClientDetail' => array(
                'AccountNumber' => $vendor->getAccountNumber(),
                'MeterNumber' => $vendor->getMeterNumber()
            ),
            'Version' => $this->getVersionInfo(),
            'RequestedShipment' => array(
                'DropoffType' => $r->getDropoffType(),
                'ShipTimestamp' => date('c'),
                'PackagingType' => $r->getPackaging(),
                'TotalInsuredValue' => array(
                    'Amount' => $r->getValue(),
                    'Currency' => $this->getCurrencyCode()
                ),
                'Shipper' => array(
                    'Address' => array(
                        'PostalCode' => $r->getOrigPostal(),
                        'CountryCode' => $r->getOrigCountry()
                    )
                ),
                'Recipient' => array(
                    'Address' => array(
                        'PostalCode' => $r->getDestPostal(),
                        'CountryCode' => $r->getDestCountry(),
                        'Residential' => false
                    )
                ),
                'ShippingChargesPayment' => array(
                    'PaymentType' => 'SENDER',
                    'Payor' => array(
                        'AccountNumber' => $vendor->getAccountNumber(),
                        'CountryCode' => $r->getOrigCountry()
                    )
                ),
                'CustomsClearanceDetail' => array(
                    'CustomsValue' => array(
                        'Amount' => $r->getValue(),
                        'Currency' => $this->getCurrencyCode()
                    )
                ),
                'RateRequestTypes' => 'LIST',
                'PackageCount' => '1',
                'PackageDetail' => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' => array(
                    '0' => array(
                        'Weight' => array(
                            'Value' => (float)$r->getWeight(),
                            'Units' => 'LB'
                        ),
                        'GroupPackageCount' => 1,
                    )
                )
            )
        );

        if ($purpose == self::RATE_REQUEST_GENERAL) {
            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][0]['InsuredValue'] = array(
                'Amount' => $r->getValue(),
                'Currency' => $this->getCurrencyCode()
            );
        } else {
            if ($purpose == self::RATE_REQUEST_SMARTPOST) {
                $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
                $ratesRequest['RequestedShipment']['SmartPostDetail'] = array(
                    'Indicia' => ((float)$r->getWeight() >= 1) ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                    'HubId' => $this->getConfigData('smartpost_hubid')
                );
            }
        }

        return $ratesRequest;
    }


    /**
     * Form array with appropriate structure for shipment request
     *
     * @param Varien_Object $request
     *
     * @return array
     */
    protected function _formShipmentRequest(Varien_Object $request)
    {
        $post = Mage::app()->getRequest()->getParams();


        $isResidental = false;

        if (isset($post['shipping']['location']) && $post['shipping']['location'] == '1') {
            $isResidental = true;
        } elseif (isset($post['form'])) {
            parse_str($post['form'], $params);

            $quoteItemId = $params['quote_item_id'];
            $formData = $params['shipping'][$quoteItemId];
            if (isset($formData['location']) && $formData['location'] == '1') {
                $isResidental = true;
            }
        } else {
            $isResidental = $this->getConfigData('residence_delivery');
        }


        if ($request->getReferenceData()) {
            $referenceData = $request->getReferenceData() . $request->getPackageId();
        } else {
            $referenceData = 'Order #'
                . $request->getOrderShipment()->getOrder()->getIncrementId()
                . ' P'
                . $request->getPackageId();
        }
        $packageParams = $request->getPackageParams();
        $customsValue = $packageParams->getCustomsValue();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == Zend_Measure_Weight::POUND ? 'LB' : 'KG';
        $dimensionsUnits = $packageParams->getDimensionUnits() == Zend_Measure_Length::INCH ? 'IN' : 'CM';
        $unitPrice = 0;
        $itemsQty = 0;
        $itemsDesc = array();
        $countriesOfManufacture = array();
        $productIds = array();
        $packageItems = $request->getPackageItems();
        foreach ($packageItems as $itemShipment) {
            $item = new Varien_Object();
            $item->setData($itemShipment);

            $unitPrice += $item->getPrice();
            $itemsQty += $item->getQty();

            $itemsDesc[] = $item->getName();
            $productIds[] = $item->getProductId();
        }

        // get countries of manufacture
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addStoreFilter($request->getStoreId())
            ->addFieldToFilter('entity_id',
                array('in' => $productIds))
            ->addAttributeToSelect('country_of_manufacture');
        foreach ($productCollection as $product) {
            $countriesOfManufacture[] = $product->getCountryOfManufacture();
        }

        $paymentType = $request->getIsReturn() ? 'RECIPIENT' : 'SENDER';
        $requestClient = array(
            'RequestedShipment' => array(
                'ShipTimestamp' => time(),
                'DropoffType' => $this->getConfigData('dropoff'),
                'PackagingType' => $request->getPackagingType(),
                'ServiceType' => $request->getShippingMethod(),
                'Shipper' => array(
                    'Contact' => array(
                        'PersonName' => $request->getShipperContactPersonName(),
                        'CompanyName' => $request->getShipperContactCompanyName(),
                        'PhoneNumber' => $request->getShipperContactPhoneNumber()
                    ),
                    'Address' => array(
                        'StreetLines' => array($request->getShipperAddressStreet()),
                        'City' => $request->getShipperAddressCity(),
                        'StateOrProvinceCode' => $request->getShipperAddressStateOrProvinceCode(),
                        'PostalCode' => $request->getShipperAddressPostalCode(),
                        'CountryCode' => $request->getShipperAddressCountryCode()
                    )
                ),
                'Recipient' => array(
                    'Contact' => array(
                        'PersonName' => $request->getRecipientContactPersonName(),
                        'CompanyName' => $request->getRecipientContactCompanyName(),
                        'PhoneNumber' => $request->getRecipientContactPhoneNumber()
                    ),
                    'Address' => array(
                        'StreetLines' => array($request->getRecipientAddressStreet()),
                        'City' => $request->getRecipientAddressCity(),
                        'StateOrProvinceCode' => $request->getRecipientAddressStateOrProvinceCode(),
                        'PostalCode' => $request->getRecipientAddressPostalCode(),
                        'CountryCode' => $request->getRecipientAddressCountryCode(),
                        'Residential' => (bool)$isResidental
                    ),
                ),
                'ShippingChargesPayment' => array(
                    'PaymentType' => $paymentType,
                    'Payor' => array(
                        'AccountNumber' => $this->getConfigData('account'),
                        'CountryCode' => Mage::getStoreConfig(
                            Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                            $request->getStoreId()
                        )
                    )
                ),
                'LabelSpecification' => array(
                    'LabelFormatType' => 'COMMON2D',
                    'ImageType' => 'PNG',
                    'LabelStockType' => 'PAPER_8.5X11_TOP_HALF_LABEL',
                ),
                'RateRequestTypes' => array('ACCOUNT'),
                'PackageCount' => 1,
                'RequestedPackageLineItems' => array(
                    'SequenceNumber' => '1',
                    'Weight' => array(
                        'Units' => $weightUnits,
                        'Value' => $request->getPackageWeight()
                    ),
                    'CustomerReferences' => array(
                        'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                        'Value' => $referenceData
                    ),
                    'SpecialServicesRequested' => array(
                        'SpecialServiceTypes' => 'SIGNATURE_OPTION',
                        'SignatureOptionDetail' => array('OptionType' => $packageParams->getDeliveryConfirmation())
                    ),
                )
            )
        );

        // for international shipping
        if ($request->getShipperAddressCountryCode() != $request->getRecipientAddressCountryCode()) {
            $requestClient['RequestedShipment']['CustomsClearanceDetail'] =
                array(
                    'CustomsValue' =>
                        array(
                            'Currency' => $request->getBaseCurrencyCode(),
                            'Amount' => $customsValue,
                        ),
                    'DutiesPayment' => array(
                        'PaymentType' => $paymentType,
                        'Payor' => array(
                            'AccountNumber' => $this->getConfigData('account'),
                            'CountryCode' => Mage::getStoreConfig(
                                Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID,
                                $request->getStoreId()
                            )
                        )
                    ),
                    'Commodities' => array(
                        'Weight' => array(
                            'Units' => $weightUnits,
                            'Value' => $request->getPackageWeight()
                        ),
                        'NumberOfPieces' => 1,
                        'CountryOfManufacture' => implode(',',
                            array_unique($countriesOfManufacture)),
                        'Description' => implode(', ', $itemsDesc),
                        'Quantity' => ceil($itemsQty),
                        'QuantityUnits' => 'pcs',
                        'UnitPrice' => array(
                            'Currency' => $request->getBaseCurrencyCode(),
                            'Amount' => $unitPrice
                        ),
                        'CustomsValue' => array(
                            'Currency' => $request->getBaseCurrencyCode(),
                            'Amount' => $customsValue
                        ),
                    )
                );
        }

        if ($request->getMasterTrackingId()) {
            $requestClient['RequestedShipment']['MasterTrackingId'] = $request->getMasterTrackingId();
        }

        // set dimensions
        if ($length || $width || $height) {
            $requestClient['RequestedShipment']['RequestedPackageLineItems']['Dimensions'] = array();
            $dimenssions = &$requestClient['RequestedShipment']['RequestedPackageLineItems']['Dimensions'];
            $dimenssions['Length'] = $length;
            $dimenssions['Width'] = $width;
            $dimenssions['Height'] = $height;
            $dimenssions['Units'] = $dimensionsUnits;
        }

        return $this->_getAuthDetails() + $requestClient;
    }
}