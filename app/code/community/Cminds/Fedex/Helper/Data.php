<?php

class Cminds_Fedex_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $rates;
    protected $address;
    protected $quote;
    protected $checkout;
    protected $current_lowest;
    protected $vendor_codes;
    protected $lowest_price;
    protected $nearest_arrival_date;

    public function getSupplierShippingMethods()
    {
        $rates = $this->getEstimaterates();

        $shippingMethod = array();

        foreach ($rates AS $rates) {
            foreach ($rates AS $rate) {
                if (!$rate->getMethodDescription()) {
                    continue;
                }
                $method = unserialize($rate->getMethodDescription());
                if (!isset($method['vendor_id'])) {
                    continue;
                }
                if (!isset($shippingMethod[$method['vendor_id']])) {
                    $shippingMethod[$method['vendor_id']] = array();
                }
                if (!isset($shippingMethod[$method['vendor_id']][$method['item_id']])) {
                    $shippingMethod[$method['vendor_id']][$method['item_id']] = array();
                }
                if (
                    !isset($this->vendor_codes[$method['vendor_id']]) ||
                    !in_array($rate->getCode(), $this->vendor_codes[$method['vendor_id']])
                ) {
                    $shippingMethod[$method['vendor_id']][$method['item_id']][] = $rate;
                    $this->rates[$method['vendor_id']][] = $rate;
                    $this->vendor_codes[$method['vendor_id']] = array();
                    $this->vendor_codes[$method['vendor_id']][] = $rate->getCode();
                }
            }
        }

        return $shippingMethod;
    }

    public function getLowestEstimateCost()
    {
        if (!$this->current_lowest) {
            $this->_prepare();
        }

        if (!$this->current_lowest) {
            return false;
        }

        if (count($this->getquote()->getAllVisibleItems()) === 1) {
            $this->lowest_price['price'] = $this->current_lowest->getPrice();
            $this->lowest_price['code'] = $this->current_lowest->getCode();

            if ($this->current_lowest->getMethodDescription()) {
                $method = unserialize($this->current_lowest->getMethodDescription());
                $this->lowest_price['arrival'] = $method['delivery'];
                $this->lowest_price['name'] = $method['name'];

                $this->lowest_price[$method['vendor_id']]['code'] = $this->current_lowest->getCode();
                $this->lowest_price[$method['vendor_id']]['name'] = $method['name'];
                $this->lowest_price[$method['vendor_id']]['arrival'] = $method['delivery'];
            } else {
                $this->lowest_price[]['name'] = !$this->current_lowest->getMethodTitle() ? $this->current_lowest->getCarrierTitle() : $this->current_lowest->getMethodTitle();
            }
            $estimatedPrices = $this->lowest_price;
            $cheapest = $this->lowest_price;
        } else {
            $this->lowest_price['price'] = $this->current_lowest->getPrice();
            if ($this->current_lowest->getMethodDescription()) {
                $method = unserialize($this->current_lowest->getMethodDescription());
                $this->lowest_price['name'] = $method['name'];
            } else {
                $this->lowest_price['name'] = $this->current_lowest->getMethodTitle();
            }

            $estimated = $this->getSupplierShippingMethods();
            $estimatedPrices = array();
            foreach ($estimated AS $rates) {
                foreach ($rates AS $rate) {
                    foreach ($rate AS $r) {
                        $method = unserialize($r->getMethodDescription());
                        if (!isset($estimatedPrices[$method['vendor_id']])) {
                            $estimatedPrices[$method['vendor_id']] = array(
                                "price" => 999999999999999,
                                "name" => "",
                                "arrival" => ""
                            );
                        }
                        if ($estimatedPrices[$method['vendor_id']]['price'] > $r->getPrice()) {
                            $estimatedPrices[$method['vendor_id']]['name'] = isset($method['name']) && $method['name'] ? $method['name'] : $r->getMethodTitle();
                            $estimatedPrices[$method['vendor_id']]['price'] = $r->getPrice();
                            $estimatedPrices[$method['vendor_id']]['arrival'] = isset($method['delivery']) ? $method['delivery'] : "";
                            $estimatedPrices[$method['vendor_id']]['code'] = $r->getCode();
                        }
                    }
                }
            }

            $cheapest = array('price' => 0, 'name' => '', 'arrival' => '');
            if (count($estimatedPrices) > 1) {
                foreach ($estimatedPrices AS $rate) {
                    $cheapest['name'] = $this->__('Shipping');
                    $cheapest['price'] += $rate['price'];

                    if ($rate['arrival']) {
                        $cheapest['arrival'] = $rate['arrival'];
                    }
                }
            } else {
                $rate = array_values($estimatedPrices);
                $cheapest = $rate[0];
            }
        }
        $Session = Mage::getSingleton('checkout/session');
        if ($estimatedPrices) {
            $Session->setData('selected_prices', array($estimatedPrices));
        }

        if ($cheapest) {
            $Session->setData('cheapest_shipping', $cheapest);
        } else {
            if ($Session->getData('cheapest_shipping')) {
                return $Session->getData('cheapest_shipping');
            }
        }

        return $cheapest;
    }

    public function getCalculatedPrices()
    {
        $Session = Mage::getSingleton('checkout/session');
        if ($Session->getData('cheapest_shipping')) {
            return $Session->getData('cheapest_shipping');
        }

        return $this->getLowestEstimateCost();
    }

    public function getSelectedPrices()
    {
        return Mage::getSingleton('checkout/session')->getData('selected_prices');
    }

    public function _prepare()
    {
        $estimatedrates = $this->getEstimaterates();

        foreach ($estimatedrates as $rates) {
            foreach ($rates as $rate) {
                if ($rate->getCode() == 'marketplace_shipping_marketplace_shipping') {
                    continue;
                }
                if (!$this->current_lowest && $rate->hasData('price')) {
                    $this->current_lowest = $rate;
                    continue;
                }
                if ((is_object($rate) && $rate->hasData('price')) &&
                    ($rate->getPrice() && $rate->getPrice() <= $this->current_lowest->getPrice() ||
                        $rate->getPrice() == $this->current_lowest->getPrice() && !$this->current_lowest->getMethodTitle()
                    )
                ) {
                    $this->current_lowest = $rate;
                }
            }
        }

        return $this->current_lowest;
    }

    public function getArrivalDate()
    {
        if (!$this->current_lowest) {
            $this->_prepare();
        }

        if (count($this->getquote()->getAllVisibleItems()) === 1) {
            if (!$this->current_lowest->getMethodDescription()) {
                return '';
            }

            $method = unserialize($this->current_lowest->getMethodDescription());
            $this->nearest_arrival_date = $method['delivery'];
        } else {
            $estimated = $this->getSupplierShippingMethods();

            $method = unserialize($this->current_lowest->getMethodDescription());
            $this->nearest_arrival_date = $method['delivery'];

            foreach ($estimated AS $rates) {
                foreach ($rates AS $r) {
                    foreach ($r AS $rate) {
                        $method = unserialize($rate->getMethodDescription());
                        $saved = strtotime($this->nearest_arrival_date);
                        $new = strtotime($method['delivery']);

                        if ($saved > $new) {

                            $this->nearest_arrival_date = $method['delivery'];
                        }
                    }
                }
            }
        }

        return $this->nearest_arrival_date;
    }

    public function getEstimaterates()
    {
        if (empty($this->rates)) {
            $groups = $this->getaddress()->getGroupedAllShippingrates();
            $this->rates = $groups;
        }

        return $this->rates;
    }

    public function getaddress()
    {
        if (empty($this->address)) {
            $this->address = $this->getquote()->getShippingaddress();
        }

        return $this->address;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/' . $carrierCode . '/title')) {
            return $name;
        }

        return $carrierCode;
    }

    public function getaddressShippingMethod()
    {
        return $this->getaddress()->getShippingMethod();
    }

    public function getquote()
    {
        if (null === $this->quote) {
            $this->quote = $this->getcheckout()->getquote();
        }

        return $this->quote;
    }

    public function getcheckout()
    {
        if (null === $this->checkout) {
            $this->checkout = Mage::getSingleton('checkout/session');
        }

        return $this->checkout;
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getHolidaysForCurrentYear()
    {
        return $this->getHolidays(date("Y"));
    }

    public function getHolidays($year)
    {
        $fixed_holidays = array(
            $year . '-01-01' => 'New Year\'s Day',
            $year . '-07-04' => 'Independence Day',
            $year . '-11-11' => 'Veterans Day',
            $year . '-12-25' => 'Christmas Day',
        );

        $holidays = $fixed_holidays;
        // Martin Luther King Jr. Day (Third Monday in January)
        $holidayDay = date('Y-m-d', strtotime($year . '-01 third monday'));
        $holidays[$holidayDay] = 'Martin Luther King Jr. Day';
        // Presidents' Day (Third Monday in February)
        $holidayDay = date('Y-m-d', strtotime($year . '-02 third monday'));
        $holidays[$holidayDay] = 'Presidents\' Day';
        // Memorial Day (Last Monday in May)
        $holidayDay = date('Y-m-d', strtotime('last monday of May $year'));
        $holidays[$holidayDay] = 'Memorial Day';
        // Father's Day (Third Sunday in June)
        // Labor Day (First Monday in September)
        $holidayDay = date('Y-m-d', strtotime($year . '-09 first monday'));
        $holidays[$holidayDay] = 'Labor Day';
        // Columbus Day (Second Monday in October)
        $holidayDay = date('Y-m-d', strtotime($year . '-10 second monday'));
        $holidays[$holidayDay] = 'Columbus Day';
        // Thanksgiving Day (Fourth Thursday in November)
        $holidayDay = date('Y-m-d', strtotime($year . '-11 fourth thursday'));
        $holidays[$holidayDay] = 'Thanksgiving Day';

        return $holidays;
    }

    public function getFedexType($order)
    {
        $shippingMethod = $order->getShippingMethod();
        $shippingDescription = $order->getShippingDescription();
        $shippingDescription = str_replace(' ', '_', $shippingDescription);
        $shippingDescription = substr($shippingDescription, 11);

        $availablePackages = Mage::getModel('usa/shipping_carrier_fedex')->getCode('method');

        foreach (array_keys($availablePackages) AS $type) {
            if (strpos(strtolower($shippingMethod), strtolower($type))) {
                return $type;
            }
        }
        foreach (array_keys($availablePackages) AS $type) {
            if (strtolower($type) == 'fedex_' . strtolower($shippingDescription)) {
                return $type;
            }
        }

        $fedexDescription = explode('_-_', $shippingDescription);
        $type = $fedexDescription[1];
        $type = str_replace('_', ' ', $type);
        foreach (array_values($availablePackages) AS $package) {
            if ($package == $type) {
                return true;
            }
        }

        return false;
    }
}
