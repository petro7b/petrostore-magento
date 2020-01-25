<?php

class Cminds_Marketplaceamount_Block_Supplier_Amount_View extends Mage_Core_Block_Template
{
    protected $currentCustomer;

    public function __construct()
    {
        parent::_construct();

        $this->setTemplate('cminds_marketplaceamount/supplier/amount/view.phtml');
    }

    public function isSelected($value)
    {
        $minOrderType = $this->currentCustomer->getData('supplier_min_order_amount_per');
        if ((int) $minOrderType !== $value) {
            return false;
        }
        return true;
    }

    public function setCustomer($customer)
    {
        $this->currentCustomer = $customer;

        return $this;
    }
}