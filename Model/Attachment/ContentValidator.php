<?php

declare(strict_types = 1);

/**
 * File: ContentValidator.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Model\Attachment;

use \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface;
use \Magento\Downloadable\Helper\Download;
use \Magento\Downloadable\Model\File\ContentValidator as FileContentValidator;
use \Magento\Framework\Exception\InputException;
use \Magento\Framework\Url\Validator as UrlValidator;

/**
 * Class ContentValidator
 * @package LizardMedia\ProductAttachment\Model\Attachment
 */
class ContentValidator
{
    /**
     * @var \Magento\Downloadable\Model\File\ContentValidator
     */
    private $fileContentValidator;


    /**
     * @var UrlValidator
     */
    private $urlValidator;


    /**
     * @param \Magento\Downloadable\Model\File\ContentValidator $fileContentValidator
     * @param \Magento\Framework\Url\Validator $urlValidator
     */
    public function __construct(
        FileContentValidator $fileContentValidator,
        UrlValidator $urlValidator
    ) {
        $this->fileContentValidator = $fileContentValidator;
        $this->urlValidator = $urlValidator;
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     * @param bool $validateAttachmentContent
     *
     * @throws \Magento\Framework\Exception\InputException
     *
     * @return bool
     */
    public function isValid(AttachmentInterface $attachment, bool $validateAttachmentContent = true) : bool
    {
        if (filter_var($attachment->getSortOrder(), FILTER_VALIDATE_INT) === false
            || $attachment->getSortOrder() < 0) {
            throw new InputException(__('Sort order must be a positive integer.'));
        }

        if ($validateAttachmentContent) {
            $this->validateAttachmentResource($attachment);
        }

        return true;
    }


    /**
     * @param \LizardMedia\ProductAttachment\Api\Data\AttachmentInterface $attachment
     *
     * @throws \Magento\Framework\Exception\InputException
     *
     * @return void
     */
    protected function validateAttachmentResource(AttachmentInterface $attachment) : void
    {
        $attachmentFile = $attachment->getAttachmentFileContent();
        if ($attachment->getAttachmentType() == Download::LINK_TYPE_FILE
            && (!$attachmentFile || !$this->fileContentValidator->isValid($attachmentFile))
        ) {
            throw new InputException(__('Provided file content must be valid base64 encoded data.'));
        }

        if ($attachment->getAttachmentType() == Download::LINK_TYPE_URL
            && !$this->urlValidator->isValid($attachment->getAttachmentUrl())
        ) {
            throw new InputException(__('Attachment URL must have valid format.'));
        }
    }
}
