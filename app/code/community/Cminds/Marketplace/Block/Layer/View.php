<?php

class Cminds_Marketplace_Block_Layer_View
    extends Mage_Catalog_Block_Layer_View
{
    public function getLayer()
    {
        return Mage::getSingleton('marketplace/layer');
    }
}