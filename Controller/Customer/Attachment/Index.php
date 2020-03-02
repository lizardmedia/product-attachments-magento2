<?php

declare(strict_types = 1);

/**
 * File: Index.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Customer\Attachment;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package LizardMedia\ProductAttachment\Controller\Customer\Attachment
 */
class Index extends Action
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Url
     */
    private $customerUrl;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Session $customerSession,
        Url $customerUrl,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Authenticate customer.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * @return Page
     */
    public function execute(): Page
    {
        return $this->resultPageFactory->create();
    }
}
