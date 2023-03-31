<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

class Measure
{
    /**
     * @return string
     */
    public static function centimeter(): string
    {
        if (class_exists('Magento\Framework\Measure\Length')) {
            return \Magento\Framework\Measure\Length::CENTIMETER;
        }
        return \Zend_Measure_Length::CENTIMETER;
    }

    /**
     * @return string
     */
    public static function inch(): string
    {
        if (class_exists('Magento\Framework\Measure\Length')) {
            return \Magento\Framework\Measure\Length::INCH;
        }
        return \Zend_Measure_Length::INCH;
    }

    /**
     * @return string
     */
    public static function pound(): string
    {
        if (class_exists('Magento\Framework\Measure\Weight')) {
            return \Magento\Framework\Measure\Weight::POUND;
        }
        return \Zend_Measure_Weight::POUND;
    }

    /**
     * @return string
     */
    public static function kilogram(): string
    {
        if (class_exists('Magento\Framework\Measure\Weight')) {
            return \Magento\Framework\Measure\Weight::KILOGRAM;
        }
        return \Zend_Measure_Weight::KILOGRAM;
    }

    /**
     * @return string
     */
    public static function gram(): string
    {
        if (class_exists('Magento\Framework\Measure\Weight')) {
            return \Magento\Framework\Measure\Weight::GRAM;
        }
        return \Zend_Measure_Weight::GRAM;
    }

    /**
     * @return string
     */
    public static function ounce(): string
    {
        if (class_exists('Magento\Framework\Measure\Weight')) {
            return \Magento\Framework\Measure\Weight::OUNCE;
        }
        return \Zend_Measure_Weight::OUNCE;
    }
}
