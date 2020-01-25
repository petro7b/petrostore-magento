<?php
class Cminds_Marketplaceproductquestions_Block_Adminhtml_Product_Questions_Renderer_Approve extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    protected function _getValue(Varien_Object $row)
    {
        $p = Mage::getModel('marketplaceproductquestions/answers')->load($row->getId(), 'question_id');
        if(!$p->getAnswerBody()) return '';
        if($p->getData('admin_approval') == 1) {
            $label = $this->__('Disapprove');
            $action = "disapprove";
        }
        else {
            $label= $this->__('Approve');
            $action = "approve";
        }
        $str = '<a href="' .  $this->getUrl("*/*/$action" , array('id' => $row->getId())) . '">'.$label.'</a>';
        return $str;
    }
}
