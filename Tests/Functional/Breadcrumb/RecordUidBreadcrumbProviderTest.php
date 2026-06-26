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

namespace Gmartino\BreadcrumbLabelsUid\Tests\Functional\Breadcrumb;

use Gmartino\BreadcrumbLabelsUid\Breadcrumb\RecordUidBreadcrumbProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Breadcrumb\BreadcrumbContext;
use TYPO3\CMS\Backend\Template\Components\Breadcrumb;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class RecordUidBreadcrumbProviderTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['gmartino/breadcrumb-labels-uid'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
    }

    #[Test]
    public function decoratorRanksAboveCoreRecordProvider(): void
    {
        self::assertGreaterThan(10, $this->get(RecordUidBreadcrumbProvider::class)->getPriority());
    }

    #[Test]
    public function uidIsAppendedToCurrentRecordLabel(): void
    {
        $context = $this->createPageContext(2);

        $nodes = $this->get(RecordUidBreadcrumbProvider::class)->generate($context, null);

        $lastNode = $nodes[array_key_last($nodes)];
        self::assertSame('2', $lastNode->identifier);
        self::assertStringEndsWith(' [2]', $lastNode->label);
        self::assertStringStartsWith('Test Page', $lastNode->label);
    }

    #[Test]
    public function breadcrumbComponentSelectsDecoratorForRecordContext(): void
    {
        $context = $this->createPageContext(2);

        $nodes = $this->get(Breadcrumb::class)->getBreadcrumb(null, $context);

        $lastNode = $nodes[array_key_last($nodes)];
        self::assertStringEndsWith(' [2]', $lastNode->label);
    }

    #[Test]
    public function existingUidSuffixIsNotDuplicated(): void
    {
        $context = $this->createPageContext(2);
        $provider = $this->get(RecordUidBreadcrumbProvider::class);

        $nodes = $provider->generate($context, null);
        $label = $nodes[array_key_last($nodes)]->label;

        self::assertSame(1, substr_count($label, ' [2]'));
    }

    private function createPageContext(int $uid): BreadcrumbContext
    {
        $record = $this->get(RecordFactory::class)
            ->createResolvedRecordFromDatabaseRow('pages', BackendUtility::getRecord('pages', $uid));

        return new BreadcrumbContext($record);
    }
}
