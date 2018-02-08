<?php

declare(strict_types = 1);

/**
 * File: Index.php
 *
 * @author Bartosz Kubicki bartosz.kubicki@lizardmedia.pl>
 * @copyright Copyright (C) 2018 Lizard Media (http://lizardmedia.pl)
 */

namespace LizardMedia\ProductAttachment\Controller\Customer\Attachment;

use \Magento\Customer\Model\Session;
use \Magento\Customer\Model\Url;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Controller\ResultInterface;
use \Magento\Framework\Exception\NotFoundException;
use \Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package LizardMedia\ProductAttachment\Controller\Customer\Attachment
 */
class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;


    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;


    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
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
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     *
     * @return \Magento\Framework\App\ResponseInterface
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
     * @return \Magento\Framework\View\Result\Page $resultPage
     */
    public function execute() : ResultInterface
    {
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
