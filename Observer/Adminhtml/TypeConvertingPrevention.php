<?php

declare(strict_types=1);

/**
 * File: TypeConvertingPrevention.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2021 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Observer\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class TypeConvertingPrevention
 * @package LizardMedia\ProductAttachment\Observer\Adminhtml
 */
class TypeConvertingPrevention implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getProduct();
        $type = $this->request->getParam('type');

        if (!empty($type) && in_array($type, $this->getProductType(), true)) {
            $product->setTypeId($type);
        }
    }

    /**
     * @return string[]
     */
    private function getProductType(): array
    {
        return ['bundle', 'grouped'];
    }
}
