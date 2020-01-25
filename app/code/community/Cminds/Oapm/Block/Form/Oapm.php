<?php

/**
 * Cminds OAPM method form block.
 *
 * @category    Cminds
 * @package     Cminds_Oapm
 * @author      Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Cminds_Oapm_Block_Form_Oapm extends Mage_Payment_Block_Form
{
    /**
     * Constructor method.
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('cminds/oapm/form/oapm.phtml');
    }

    public function canShowForm()
    {
        $configHelper = Mage::helper("cminds_oapm/config");

        if ($configHelper->getConfigData("approver") == Cminds_Oapm_Model_Adminhtml_System_Config_Source_Approver::APPROVER_CUSTOMER) {
            return true;
        }

        return false;
    }
}