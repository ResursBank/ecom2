<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Controller\Ecom;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Resursbank\Ecom\Module\Widget\Loader\Css as CssLoader;
use Magento\Framework\App\RequestInterface;

/**
 * Controller to read stylesheet from Ecom library and return CSS.
 *
 * This is to avoid using inline CSS which require sanitization even when the
 * CSS content is static.
 *
 * We cannot include stylesheets from the library directly because the vendor
 * directory is not publicly exposed, and we want to avoid copying files since
 * that may cause stale files.
 */
class Style implements HttpGetActionInterface
{
    /**
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly ResultFactory $resultFactory,
        private readonly RequestInterface  $request
    ) {
    }

    /**
     * @inheritDoc
     *
     * Render CSS.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->resultFactory->create(type: ResultFactory::TYPE_RAW);

        $css = match ($this->request->getParam(key: 'module')) {
            'loader' => (new CssLoader())->content,
            default => ''
        };

        $result->setHeader(name: 'Content-Type', value: 'text/css');

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $result->setContents(contents: $css);

        return $result;
    }
}
