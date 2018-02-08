<?php

declare(strict_types = 1);

/**
 * File: ContentUploader.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Api\Data\File;

use \Magento\Downloadable\Api\Data\File\ContentInterface;

/**
 * Interface ContentUploaderInterface
 * @package LizardMedia\ProductAttachment\Api\Data\File
 */
interface ContentUploaderInterface
{
    /**
     * @param \Magento\Downloadable\Api\Data\File\ContentInterface $fileContent
     * @param string $contentType
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function upload(ContentInterface $fileContent, string $contentType) : array;
}
