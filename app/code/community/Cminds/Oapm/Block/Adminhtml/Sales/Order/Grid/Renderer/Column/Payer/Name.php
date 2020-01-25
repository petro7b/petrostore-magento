<?php

/**
 * Cminds OAPM adminhtml sales order grid renderer column payer name.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Block_Adminhtml_Sales_Order_Grid_Renderer_Column_Payer_Name
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        /** @var array $additionalData */
        $additionalData = unserialize($row->getPayment()->getAdditionalData());

        return $additionalData['recipient_name'];
    }
}
