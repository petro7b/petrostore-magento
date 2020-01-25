<?php

class Cminds_Marketplace_Helper_Csv extends Mage_Core_Helper_Abstract
{

    /**
     * @return bool
     */
    public function canShowBillingAddressInOrderExport()
    {
        if (Mage::getStoreConfig('marketplace_configuration/csv_export/show_billing_address')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canShowGiftMessage()
    {
        if (Mage::getStoreConfig('marketplace_configuration/csv_export/show_gift_message')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canShowPaymentInformation()
    {
        if (Mage::getStoreConfig('marketplace_configuration/csv_export/show_payment_information')) {
            return true;
        }

        return false;
    }

    /**
     * @param $filename
     */
    public function prepareCsvHeaders($filename)
    {
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }

    /**
     * @param $array
     *
     * @return null|string
     */
    public function array2Csv($array)
    {
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }
}
