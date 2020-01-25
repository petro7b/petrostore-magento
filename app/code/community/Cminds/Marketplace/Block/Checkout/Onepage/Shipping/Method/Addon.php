<?php
class Cminds_Marketplace_Block_Checkout_Onepage_Shipping_Method_Addon extends Mage_Core_Block_Template {

    /**
     * Render block HTML for all block childs.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $childs = $this->getChild();

        if (count($childs) > 0) {
            foreach ($childs as $child) {
                $data = $this->getData();
                $childData = $child->getData();
                $child->setData(array_merge($childData, $data));
                if (!$child->getTemplate()) {
                    $html .= '';
                }
                $html .= $child->renderView();
            }
        }

        return $html;
    }
}