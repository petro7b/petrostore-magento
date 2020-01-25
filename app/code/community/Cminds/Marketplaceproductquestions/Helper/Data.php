<?php
class Cminds_Marketplaceproductquestions_Helper_Data extends Mage_Core_Helper_Abstract {

    public function isEnabled() {
        return Mage::getStoreConfig('marketplace_productquestions/general/module_enabled');
    }

    public function adminApprovalRequired() {
        return Mage::getStoreConfig('marketplace_productquestions/general/admin_approval_required');
    }

    public function isRecaptchaEnabled() {
        return Mage::getStoreConfig('marketplace_productquestions/recaptcha_settings/recaptcha_enabled');
    }

    public function getRecaptchaSecretKey() {
        return Mage::getStoreConfig('marketplace_productquestions/recaptcha_settings/recaptcha_secret_key');
    }

    public function getRecaptchaSiteKey() {
        return Mage::getStoreConfig('marketplace_productquestions/recaptcha_settings/recaptcha_site_key');
    }

    public function popupEnabled()
    {
        return Mage::getStoreConfig('marketplace_productquestions/general/show_popup_form');
    }

}