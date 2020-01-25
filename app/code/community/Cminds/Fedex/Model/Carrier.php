<?php

class Cminds_Fedex_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
        implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'cminds_vendor_fedex_shipping';

    public function getAllowedMethods()
    {
        return array(
            'standard' => 'Standard delivery',
        );
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier('cminds_vendor_fedex_shipping');
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod('cminds_vendor_fedex_shipping');
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice(6.00);
        $result->append($method);
        $this->_result = $result;

        return $this->getResult();
    }

    // protected function _getStandardRate()
    // {
    //     /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
    //     $rate = Mage::getModel('shipping/rate_result_method');

    //     $rate->setCarrier($this->_code);
    //     $rate->setCarrierTitle($this->getConfigData('title'));
    //     $rate->setMethod('large');
    //     $rate->setMethodTitle('Standard delivery');
    //     $rate->setPrice(1.23);
    //     $rate->setCost(0);

    //     return $rate;
    // }
    
    
    public function helper($type = 'aongkir')
		{
			return Mage::helper($type);	
		}
	
	public function getCityName()
		{
			$string_city = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCity();
			
			
			$sql = "select city_name_first from ".Mage::getConfig()->getTablePrefix()."daftar_alamat where concat(type,' ',city_name) = '$string_city' limit 0,1 ";
			
			$res =  $this->helper()->fetchSql($sql);
			
			return $res[0]['city_name_first'];
			
		}
		
	public function getBeratTotal()
		{
			$items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
			$totalWeight = 0;
			foreach($items as $item) {
				$totalWeight += ($item->getWeight() * $item->getQty()) ;
			}
			
			if($totalWeight < 1)
			$totalWeight = 1;
			
			
			return $totalWeight;
		}
    
    protected function _getStandardRate($dest,$dest1,$weight,$harga)
	{
		$key = $this->getConfigData('key'); // set a default 0
		$account = $this->getConfigData('account'); // set a default 0
		$dest   = $this->getCityName($res);
        $weight = $this->getBeratTotal($totalWeight);
        
		
		
		$cost='';
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_URL,'http://ws.firstlogistics.co.id/crate.php?ORG=Jakarta%2C%20Kota%20Jakarta&DEST='.urlencode($dest).'&WEIGHT='.$weight);
// 		curl_setopt($curl,CURLOPT_URL,'http://ws.firstlogistics.co.id/crate.php?');
		curl_setopt($curl,CURLOPT_POST, true);
// 		curl_setopt($curl,CURLOPT_POSTFIELDS, 'ORG=Jakarta%2C%20Kota%20Jakarta&DEST='.urlencode($dest).'&WEIGHT=2');
// 		curl_setopt($curl,CURLOPT_POSTFIELDS, 'APPID='.$key.'&ACCOUNT='.$account.'&FUNCTION=estdratemod&DEST='.urlencode($dest).'&WEIGHT='.$weight);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$respons=curl_exec($curl);
		$prc=json_decode($respons,true);
        // return $respons;
		$price=$prc['0']['RegPrice'];
		$name='Firstlogistic - Regular Service';
		
		$rate = Mage::getModel('shipping/rate_result_method');
		$rate->setCarrier($this->_code);
		$rate->setMethod($prc[0]['RegServ']);
		$rate->setCarrierTitle($this->getConfigData('title'));
		$rate->setMethodTitle($name);
		$rate->setPrice($price);
		$rate->setCost($price*$weight);
		
		
		$harga = $rate->setCost($price*$weight);

		return $rate;
	}
}