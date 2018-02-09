<?php

declare(strict_types = 1);

/**
 * File: Settings.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Helper;

use \LizardMedia\ProductAttachment\Api\SettingsInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\ScopeInterface;

/**
 * Class Settings
 * @package LizardMedia\ProductAttachment\Helper
 */
class Settings extends AbstractHelper implements SettingsInterface
{
    /**
     * Settings codes
     *
     * @var string
     */
    const PRODUCT_ATTACHMENT_DEFAULT_TITLE_XML_PATH = 'catalog/product_attachments/default_title';
    const PRODUCT_ATTACHMENT_OPEN_IN_NEW_WINDOW_XML_PATH = 'catalog/product_attachments/links_target_new_window';
    const PRODUCT_ATTACHMENT_USE_CONTENT_DISPOSITION_XML_PATH = 'catalog/product_attachments/content_disposition';


    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;


    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }


    /**
     * @param string $scope
     *
     * @return string
     */
    public function getAttachmentDefaultTitle($scope = ScopeInterface::SCOPE_STORE) : string
    {
        return $this->scopeConfig->getValue(self::PRODUCT_ATTACHMENT_DEFAULT_TITLE_XML_PATH);
    }


    /**
     * @param string $scope
     *
     * @return bool
     */
    public function areLinksOpenedInNewWindow($scope = ScopeInterface::SCOPE_STORE) : bool
    {
        return (bool) $this->scopeConfig->getValue(self::PRODUCT_ATTACHMENT_OPEN_IN_NEW_WINDOW_XML_PATH);
    }


    /**
     * @param string $scope
     *
     * @return string
     */
    public function getContentDisposition($scope = ScopeInterface::SCOPE_STORE) : string
    {
        return $this->scopeConfig->getValue(self::PRODUCT_ATTACHMENT_USE_CONTENT_DISPOSITION_XML_PATH);
    }
}
