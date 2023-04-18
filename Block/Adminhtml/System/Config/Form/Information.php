<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Block\Adminhtml\System\Config\Form;

use Calcurates\ModuleMagento\Model\Update\VersionManager;
use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Helper\Js;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    public const CALCURATES_URL = 'https://calcurates.com/configuration-service?utm_source=admin&utm_medium=magento';

    public const GITHUB_URL = 'https://github.com/calcurates/module-magento/releases';

    /**
     * @var VersionManager
     */
    private $versionManager;

    /**
     * Information constructor.
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param VersionManager $versionManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        VersionManager $versionManager,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->versionManager = $versionManager;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $moduleVersion = $this->versionManager->getCurrentVersion();

        $isVersionLast = $this->versionManager->isVersionLatest();
        $className = $isVersionLast ? 'last-version' : '';

        $html = '<a id="' .
            $element->getHtmlId() .
            '-head" href="#' .
            $element->getHtmlId() .
            '-link" onclick="Fieldset.toggleCollapse(\'' .
            $element->getHtmlId() .
            '\', \'' .
            $this->getUrl(
                '*/*/state'
            ) . '\'); return false;">' . __('Calcurates') . ' ';

        $html .= '<span class="calcurates-info-block">';
        $html .= '<span class="module-version ' . $className . '" >' . $moduleVersion . '</span>';
        $html .= '</span>';
        $html .= ' ' . $this->getLogoHtml();
        $html .= '</a>';

        if (!$isVersionLast) {
            $html .= '<div class="calcurates-info-block"><span class="message message-warning">'
                . __(
                    'Extension requires update. Newer version is available'
                    . ' <a target="_blank" href="%1">here</a>.',
                    self::GITHUB_URL
                )
                . '</span></div><br/>';
        }

        $html .= '<div class="calcurates-info-block"><span class="message success">'
            . __(
                'Confused with configuration? Feel free to request'
                . ' <a target="_blank" href="%1">configuration service</a>'
                . ' and save your time.',
                self::CALCURATES_URL
            )
            . '</span></div><br/>';

        return $html;
    }

    /**
     * @return string
     */
    private function getLogoHtml()
    {
        $src = $this->_assetRepo->getUrl("Calcurates_ModuleMagento::images/calcurates-logo-black.svg");

        return '<object><img class="calcurates-logo" src="' . $src . '"/></object>';
    }
}
