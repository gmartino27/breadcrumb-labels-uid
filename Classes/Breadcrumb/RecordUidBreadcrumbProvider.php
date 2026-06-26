<?php

declare(strict_types=1);

/*
 * This file is part of the "breadcrumb_labels_uid" extension for TYPO3 CMS.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Gmartino\BreadcrumbLabelsUid\Breadcrumb;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Breadcrumb\BreadcrumbContext;
use TYPO3\CMS\Backend\Breadcrumb\BreadcrumbProviderInterface;
use TYPO3\CMS\Backend\Breadcrumb\RecordBreadcrumbProvider;
use TYPO3\CMS\Backend\Dto\Breadcrumb\BreadcrumbNode;

/**
 * Appends the record uid to the current record breadcrumb label, restoring the
 * backend behaviour known from TYPO3 versions before v14.
 *
 * The core {@see RecordBreadcrumbProvider} is final and internal, so it cannot
 * be extended. Instead this provider is registered with a higher priority and
 * decorates the core provider: all breadcrumb generation is delegated, and only
 * the label of the resulting current-record node is post-processed. This keeps
 * the coupling to a single, stable interface method and avoids duplicating any
 * core breadcrumb logic.
 */
final readonly class RecordUidBreadcrumbProvider implements BreadcrumbProviderInterface
{
    /**
     * Priority of the decorated core provider. The decorator must rank above it
     * so that it is selected first for record contexts.
     */
    private const CORE_PROVIDER_PRIORITY = 10;

    public function __construct(
        private RecordBreadcrumbProvider $recordBreadcrumbProvider,
    ) {}

    public function supports(?BreadcrumbContext $context): bool
    {
        return $this->recordBreadcrumbProvider->supports($context);
    }

    public function generate(?BreadcrumbContext $context, ?ServerRequestInterface $request): array
    {
        $nodes = $this->recordBreadcrumbProvider->generate($context, $request);
        if ($nodes !== []) {
            $lastIndex = array_key_last($nodes);
            $nodes[$lastIndex] = $this->appendUid($nodes[$lastIndex]);
        }

        return $nodes;
    }

    public function getPriority(): int
    {
        return self::CORE_PROVIDER_PRIORITY + 10;
    }

    private function appendUid(BreadcrumbNode $node): BreadcrumbNode
    {
        $label = rtrim($node->label);
        $uidSuffix = ' [' . $node->identifier . ']';
        if (str_ends_with($label, $uidSuffix) === false) {
            $node = new BreadcrumbNode(
                identifier: $node->identifier,
                label: $label . $uidSuffix,
                icon: $node->icon,
                iconOverlay: $node->iconOverlay,
                url: $node->url,
                forceShowIcon: $node->forceShowIcon,
            );
        }

        return $node;
    }
}
