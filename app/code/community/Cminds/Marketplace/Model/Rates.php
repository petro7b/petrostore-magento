<?php
class Cminds_Marketplace_Model_Rates extends Mage_Core_Model_Abstract
{
    const GLOBAL_MARKER = '*';
    protected function _construct()
    {
        $this->_init('marketplace/rates');
    }

    public function getRateByWeight($country, $region, $postcode, $total=0) {
        $unserializedData = $this->unserializeRate();
        if(!$unserializedData) return false;
        $total = $this->_validateTotal($total);

        $matched = $this->match((!$country ? '*' : $country), (!$region ? '*' : $region), $postcode, $total);

        foreach($matched AS $i => $data) {
            if(!isset($data[3])) continue;

            if($i == 1 && $total < $data[3]) {
                $shippingCost = $data;
                break;
            }
            if(isset($matched[$i+1][3]) && $data[3] <= $total && $total < $matched[$i+1][3]) {
                $shippingCost = $data;
                break;
            } elseif($data[3] <= $total && !isset($matched[$i+1][3])) {
                $shippingCost = $data;
            }
        }

        if(!isset($shippingCost) || $shippingCost == NULL && $total == 0 && count($matched) > 0) {
            $shippingCost = $matched;
        }

        if(isset($shippingCost[4]) && is_numeric($shippingCost[4])) {
            return $shippingCost[4];
        } else {
            return false;
        }
    }

    public function getRateByQty($country='*', $region='*', $postcode='*', $total=0) {

        $unserializedData = $this->unserializeRate();
        if(!$unserializedData) return false;
        $total = $this->_validateQtyTotal($total);

        $matched = $this->match((!$country ? '*' : $country), (!$region ? '*' : $region), $postcode, $total);

        foreach($matched AS $i => $data) {
            if(!isset($data[3])) continue;

            if($i == 1 && $total < $data[3]) {
                $shippingCost = $data;
                break;
            }
            if(isset($matched[$i+1][3]) && $data[3] <= $total && $total < $matched[$i+1][3]) {
                $shippingCost = $data;
                break;
            } elseif($data[3] <= $total && !isset($matched[$i+1][3])) {
                $shippingCost = $data;
            }
        }

        if(!isset($shippingCost) || $shippingCost == NULL && $total == 0 && count($matched) > 0) {
            $shippingCost = $matched;
        }

        if(isset($shippingCost[4]) && is_numeric($shippingCost[4])) {
            return $shippingCost[4];
        } else {
            return false;
        }
    }

    protected function _validateTotal($total) {
        if($total == 0) {
            if(!Mage::getSingleton("checkout/session")->getForceItem()) {
                foreach(Mage::getSingleton("checkout/session")->getQuote()->getAllItems() AS $item) {
                    $total += $item->getWeight() * $item->getQty();
                }
            } else {
                $forcedItem = Mage::getSingleton("checkout/session")->getForceItem();
                $item = Mage::getModel("sales/quote_item")->load($forcedItem);
                $total = $item->getWeight();
            }
        }

        return $total;
    }


    protected function _validateQtyTotal($total) {
        if($total == 0) {
            if(!Mage::getSingleton("checkout/session")->getForceItem()) {
                foreach(Mage::getSingleton("checkout/session")->getQuote()->getAllItems() AS $item) {
                    $total += $item->getQty();
                }
            } else {
                $total = 1;
            }
        }

        return $total;
    }

    protected function _validatePriceTotal($total) {
        if($total == 0) {
            if(!Mage::getSingleton("checkout/session")->getForceItem()) {
                foreach(Mage::getSingleton("checkout/session")->getQuote()->getAllItems() AS $item) {
                    $total += $item->getPrice() * $item->getQty();
                }
            } else {
                $forcedItem = Mage::getSingleton("checkout/session")->getForceItem();
                $item = Mage::getModel("sales/quote_item")->load($forcedItem);
                $total = $item->getPrice();
            }
        }

        return $total;
    }

    public function getRateByPrice($country, $region, $postcode, $total) {
        $unserializedData = $this->unserializeRate();

        if(!$unserializedData) return false;
        $matched = $this->match((!$country ? '*' : $country), (!$region ? '*' : $region), $postcode, $total);

        foreach($matched AS $i => $data) {
            if(!isset($data[3])) continue;

            if($i == 0 && $total < $data[3]) {
                $shippingCost = $data;
                break;
            }
            if(isset($matched[$i+1][3]) && $data[3] <= $total && $total < $matched[$i+1][3]) {
                $shippingCost = $data;
                break;
            } elseif($data[3] <= $total && !isset($matched[$i+1][3])) {
                $shippingCost = $data;
            }
        }

        if(!isset($shippingCost) || $shippingCost == NULL && $total == 0 && count($matched) > 0) {
            $shippingCost = $matched;
        }

        if(isset($shippingCost[4]) && is_numeric($shippingCost[4])) {
            return $shippingCost[4];
        } else {
            return false;
        }
    }

    public function getRate($country, $region, $postcode, $total=0) {
        $unserializedData = $this->unserializeRate();

        if(!$unserializedData) return false;

        $matched = $this->match($country, $region, $postcode, $total);

        if(count($matched) > 1 || count($matched) == 0) {
            return false;
        }

        $shippingCost = $matched[0];

        if(isset($shippingCost[4]) && is_numeric($shippingCost[4])) {
            return $shippingCost[4];
        } else {
            return false;
        }

    }

    public function unserializeRate($getEmpty = false) {
        if($this->getRateData() != NULL) {
            return unserialize($this->getRateData());
        }
        return ($getEmpty) ? array() : false;
    }

    private function match($country, $region, $postcode, $total) {
        $rates = $this->unserializeRate(true);
        $validCountries = array();

        $countryModel = Mage::getModel('directory/country')->loadByCode($country);
        $threeCharFormat = $countryModel->getData('iso3_code');

        foreach($rates AS $rate) {
            if(strtoupper($rate[0]) == strtoupper($country)
                || strtoupper($rate[0]) == strtoupper($threeCharFormat)
                || $rate[0] == self::GLOBAL_MARKER) {
                $validCountries[] = $rate;
            }
        }

        $validRegions = array();
        $regionDb = Mage::getModel('directory/region')->loadByName($region, $rate[0]);

        $isRegionExists = false;
        foreach($validCountries AS $validCountry) {
            $validCountyCheck = strpos($validCountry[1], ",");
            if($validCountyCheck === false) {
                if($validCountry[1] === self::GLOBAL_MARKER) {
                    $validRegions[] = $validCountry;
                }
                if($validCountry[1] === $regionDb->getName()) {
                    $isRegionExists = true;
                    $validRegions[] = $validCountry;
                }
            } else {
                $states = explode(',', $validCountry[1]);
                if(in_array($regionDb->getCode(), $states)) {
                    $isRegionExists = true;
                    $validRegions[] = $validCountry;
                }
            }
        }
        if($isRegionExists) {
            foreach($validRegions AS $key => $validRegion) {
                if($validRegion[1] === self::GLOBAL_MARKER) {
                    unset($validRegions[$key]);
                }
            }
        }
        $validZipCodes = array();
        foreach($validRegions AS $validRegion) {
            if($validRegion[2] === $postcode || $validRegion[2] = self::GLOBAL_MARKER) {
                $validZipCodes[] = $validRegion;
            } else {
                $pc = explode('-', $postcode);
                $vR = explode('-', $validRegion[2]);

                if($vR[0] == self::GLOBAL_MARKER && $pc[1] = $vR[1]) {
                    $validZipCodes[] = $validRegion;
                }elseif($vR[1] == self::GLOBAL_MARKER && $pc[0] = $vR[0]) {
                    $validZipCodes[] = $validRegion;
                }

            }
        }
        return $validZipCodes;
    }
}
