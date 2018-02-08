<?php

declare(strict_types = 1);

/**
 * File: AttachmentFactoryInterface.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Api\Data;

/**
 * Interface AttachmentFactoryInterface
 * @package LizardMedia\ProductAttachment\Api\Data
 */
interface AttachmentFactoryInterface
{
    /**
     * @param array $data
     *
     * @return \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface
     */
    public function create(array $data = []);
}
