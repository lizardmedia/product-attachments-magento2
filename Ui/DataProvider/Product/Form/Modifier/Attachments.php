<?php

declare(strict_types = 1);

/**
 * File: Attachements.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier;

use LizardMedia\ProductAttachment\Model\Attachment;
use \LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier\Data\Attachments as AttachmentsData;
use \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use \Magento\Catalog\Model\Locator\LocatorInterface;
use \Magento\Downloadable\Model\Source\TypeUpload;
use \Magento\Framework\Stdlib\ArrayManager;
use \Magento\Framework\UrlInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Ui\Component\Container;
use \Magento\Ui\Component\DynamicRows;
use \Magento\Ui\Component\Form;

/**
 * Class Attachments
 * @package LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier
 */
class Attachments extends AbstractModifier
{
    /**
     * @var \LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier\Data\Attachments
     */
    private $attachmentsData;


    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    private $locator;


    /**
     * @var \Magento\Downloadable\Model\Source\TypeUpload
     */
    private $typeUpload;


    /**
     * @var \Magento\Framework\Stdlib\ArrayManager
     */
    private $arrayManager;


    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param \LizardMedia\ProductAttachment\Ui\DataProvider\Product\Form\Modifier\Data\Attachments $attachmentsData
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Magento\Downloadable\Model\Source\TypeUpload $typeUpload
     * @param \Magento\Framework\Stdlib\ArrayManager $arrayManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        AttachmentsData $attachmentsData,
        LocatorInterface $locator,
        TypeUpload $typeUpload,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->attachmentsData = $attachmentsData;
        $this->locator = $locator;
        $this->typeUpload = $typeUpload;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }


    /**
     * @param array $data
     *
     * @return array $data
     */
    public function modifyData(array $data) : array
    {
        $model = $this->locator->getProduct();

        $data[$model->getId()][self::DATA_SOURCE_DEFAULT]['attachments_title'] = $this->attachmentsData->getAttachmentsTitle();
        $data[$model->getId()]['downloadable']['attachment'] = $this->attachmentsData->getAttachmentsData();

        return $data;
    }


    /**
     * @param array $meta
     *
     * @return array $meta
     */
    public function modifyMeta(array $meta) : array
    {
        $attachmentsPath = Composite::CHILDREN_PATH . DIRECTORY_SEPARATOR . Composite::CONTAINER_ATTACHMENTS;
        $attachmentsContainer['arguments']['data']['config'] = [
            'additionalClasses' => 'admin__fieldset-section',
            'componentType' => Form\Fieldset::NAME,
            'label' => __('Attachments'),
            'dataScope' => '',
            'visible' => true,
            'sortOrder' => 10,
        ];

        $attachmentsTitle['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'label' => __('Title'),
            'dataScope' => 'product.attachments_title',
            'scopeLabel' => $this->storeManager->isSingleStoreMode() ? '' : '[STORE VIEW]',
        ];

        $informationAttachments['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => __('Alphanumeric, dash and underscore characters are recommended for filenames. Improper characters are replaced with \'_\'.'),
        ];

        $attachmentsContainer = $this->arrayManager->set(
            'children',
            $attachmentsContainer,
            [
                'attachments_title' => $attachmentsTitle,
                'attachment' => $this->getDynamicRows(),
                'information_attachments' => $informationAttachments,
            ]
        );

        return $this->arrayManager->set($attachmentsPath, $meta, $attachmentsContainer);
    }


    /**
     * @return array
     */
    private function getDynamicRows() : array
    {
        $dynamicRows['arguments']['data']['config'] = [
            'addButtonLabel' => __('Add attachment'),
            'componentType' => DynamicRows::NAME,
            'itemTemplate' => 'record',
            'renderDefaultRecord' => false,
            'columnsHeader' => true,
            'additionalClasses' => 'admin__field-wide',
            'dataScope' => 'downloadable',
            'deleteProperty'=> 'is_delete',
            'deleteValue' => '1',
        ];

        return $this->arrayManager->set('children/record', $dynamicRows, $this->getRecord());
    }


    /**
     * @return array
     */
    private function getRecord() : array
    {
        $record['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'isTemplate' => true,
            'is_collection' => true,
            'component' => 'Magento_Ui/js/dynamic-rows/record',
            'dataScope' => '',
        ];

        $recordPosition['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'sort_order',
            'visible' => false,
        ];

        $recordActionDelete['arguments']['data']['config'] = [
            'label' => null,
            'componentType' => 'actionDelete',
            'fit' => true,
        ];

        return $this->arrayManager->set(
            'children',
            $record,
            [
                'container_attachment_title' => $this->getTitleColumn(),
                'container_attachments' => $this->getAttachmentColumn(),
                'position' => $recordPosition,
                'action_delete' => $recordActionDelete,
            ]
        );
    }


    /**
     * @return array
     */
    private function getTitleColumn() : array
    {
        $titleContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Title'),
            'dataScope' => '',
        ];

        $titleField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'title',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/attachment_title', $titleContainer, $titleField);
    }


    /**
     * @return array
     */
    private function getAttachmentColumn() : array
    {
        $attachmentContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('File'),
            'dataScope' => '',
        ];

        $attachmentType['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magento_Downloadable/js/components/upload-type-handler',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => Attachment::ATTACHMENT_TYPE,
            'options' => $this->typeUpload->toOptionArray(),
            'typeFile' => 'attachment_file',
            'typeUrl' => 'attachment_url',
        ];

        $attachmentUrl['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'attachment_url',
            'placeholder' => 'URL',
            'validation' => [
                'required-entry' => true,
                'validate-url' => true,
            ],
        ];

        $attachmentUploader['arguments']['data']['config'] = [
            'formElement' => 'fileUploader',
            'componentType' => 'fileUploader',
            'component' => 'Magento_Downloadable/js/components/file-uploader',
            'elementTmpl' => 'Magento_Downloadable/components/file-uploader',
            'fileInputName' => 'attachments',
            'uploaderConfig' => [
                'url' => $this->urlBuilder->addSessionParam()->getUrl(
                    'adminhtml/attachment_file/upload',
                    [Attachment::ATTACHMENT_TYPE => 'attachments', '_secure' => true]
                ),
            ],
            'dataScope' => 'file',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set(
            'children',
            $attachmentContainer,
            [
                'attachment_type' => $attachmentType,
                'attachment_url' => $attachmentUrl,
                'attachment_file' => $attachmentUploader,
            ]
        );
    }
}
