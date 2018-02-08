<?php

declare(strict_types = 1);

/**
 * File: SettingsInterface.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Api;

use \Magento\Store\Model\ScopeInterface;

/**
 * Interface SettingsInterface
 * @package LizardMedia\ProductAttachment\Api
 */
interface SettingsInterface
{
    /**
     * @param string $scope
     *
     * @return string
     */
    public function getAttachmentDefaultTitle($scope = ScopeInterface::SCOPE_STORE) : string;


    /**
     * @param string $scope
     *
     * @return bool
     */
    public function areLinksOpenedInNewWindow($scope = ScopeInterface::SCOPE_STORE) : bool;


    /**
     * @param string $scope
     *
     * @return string
     */
    public function getContentDisposition($scope = ScopeInterface::SCOPE_STORE) : string;
}
