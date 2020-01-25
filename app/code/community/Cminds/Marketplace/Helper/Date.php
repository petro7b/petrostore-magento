<?php

class Cminds_Marketplace_Helper_Date extends Mage_Core_Helper_Abstract
{
    /**
     * Current store locale.
     *
     * @var string
     */
    private $locale;

    /**
     * Return Zend Date object with date converted to the UTC time standard.
     *
     * @param string $date | String formatted like day/month/year, so for example: 02/25/2018
     *
     * @return Zend_Date
     */
    public function getUTCDate($date)
    {
        try {
            $localeCode = $this->getLocaleCode();

            $dateObj = $this
                ->getLocale()
                ->date(
                    null,
                    null,
                    $localeCode,
                    false
                )
                ->setTimezone(
                    Mage::app()
                        ->getStore()
                        ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)
                )
                ->set(
                    $date,
                    $this->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                    $localeCode
                )
                ->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

            return $dateObj;
        }
        catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get magento locale object.
     *
     * @return Mage_Core_Model_Locale|string
     */
    private function getLocale()
    {
        if (!$this->locale) {
            $this->locale = Mage::app()->getLocale();
        }

        return $this->locale;
    }

    /**
     * Get current store locale code.
     *
     * @return string
     */
    private function getLocaleCode()
    {
        return $this
            ->getLocale()
            ->getLocaleCode();
    }
}
