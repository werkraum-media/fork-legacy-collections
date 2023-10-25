<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 Extension "legacy_collections".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace FriendsOfTYPO3\LegacyCollections\Tests\Functional\Tca;

use TYPO3\CMS\Backend\Tests\Functional\Form\FormTestService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class CollectionVisibleFieldsTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/legacy_collections',
    ];

    protected static $collectionFields = [
        'title',
        'sys_language_uid',
        'hidden',
        'type',
        'description',
        'table_name',
        'items',
        'starttime',
        'endtime',
    ];

    /**
     * @test
     */
    public function collectionFormContainsExpectedFields(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/BackendUser.csv');
        $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('en_EN');

        $formEngineTestService = new FormTestService();
        $formResult = $formEngineTestService->createNewRecordForm('sys_collection');

        foreach (static::$collectionFields as $expectedField) {
            self::assertNotFalse(
                $formEngineTestService->formHtmlContainsField($expectedField, $formResult['html']),
                'The field ' . $expectedField . ' is not in the form HTML'
            );
        }
    }
}
