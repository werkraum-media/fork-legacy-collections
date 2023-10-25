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

namespace FriendsOfTYPO3\LegacyCollections\Tests\Functional\Collection;

use PHPUnit\Framework\MockObject\MockObject;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\SQLServerPlatform;
use FriendsOfTYPO3\LegacyCollections\Collection\RecordCollectionRepository;
use FriendsOfTYPO3\LegacyCollections\Collection\StaticRecordCollection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for \FriendsOfTYPO3\LegacyCollections\Collection\RecordCollectionRepository
 */
class RecordCollectionRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/legacy_collections',
    ];

    /**
     * @var RecordCollectionRepository|MockObject
     */
    protected $subject;

    /**
     * @var string
     */
    protected $testTableName;

    /**
     * Sets up this test case.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getMockBuilder(RecordCollectionRepository::class)
            ->onlyMethods(['getEnvironmentMode'])
            ->getMock();
        $this->testTableName = StringUtility::getUniqueId('tx_testtable');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_collection')
            ->truncate('sys_collection');
    }

    /**
     * @test
     */
    public function doesFindByTypeReturnNull(): void
    {
        $type = RecordCollectionRepository::TYPE_Static;
        $objects = $this->subject->findByType($type);
        self::assertNull($objects);
    }

    /**
     * @test
     */
    public function doesFindByTypeReturnObjects(): void
    {
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            ['uid' => 1, 'type' => $type, 'table_name' => $this->testTableName],
            ['uid' => 2, 'type' => $type, 'table_name' => $this->testTableName]
        ]);

        $objects = $this->subject->findByType($type);
        self::assertCount(2, $objects);
        self::assertInstanceOf(StaticRecordCollection::class, $objects[0]);
        self::assertInstanceOf(StaticRecordCollection::class, $objects[1]);
    }

    /**
     * @test
     */
    public function doesFindByTableNameReturnNull(): void
    {
        $objects = $this->subject->findByTableName($this->testTableName);
        self::assertNull($objects);
    }

    /**
     * @test
     */
    public function doesFindByTableNameReturnObjects(): void
    {
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            ['uid' => 1, 'type' => $type, 'table_name' => $this->testTableName],
            ['uid' => 2, 'type' => $type, 'table_name' => $this->testTableName]
        ]);
        $objects = $this->subject->findByTableName($this->testTableName);

        self::assertCount(2, $objects);
        self::assertInstanceOf(StaticRecordCollection::class, $objects[0]);
        self::assertInstanceOf(StaticRecordCollection::class, $objects[1]);
    }

    /**
     * @test
     */
    public function doesFindByTypeAndTableNameReturnNull(): void
    {
        $type = RecordCollectionRepository::TYPE_Static;
        $objects = $this->subject->findByTypeAndTableName($type, $this->testTableName);

        self::assertNull($objects);
    }

    /**
     * @test
     */
    public function doesFindByTypeAndTableNameReturnObjects(): void
    {
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            ['uid' => 1, 'type' => $type, 'table_name' => $this->testTableName],
            ['uid' => 2, 'type' => $type, 'table_name' => $this->testTableName]
        ]);
        $objects = $this->subject->findByTypeAndTableName($type, $this->testTableName);

        self::assertCount(2, $objects);
        self::assertInstanceOf(StaticRecordCollection::class, $objects[0]);
        self::assertInstanceOf(StaticRecordCollection::class, $objects[1]);
    }

    /**
     * @test
     */
    public function doesFindByUidReturnAnObjectInBackendMode(): void
    {
        $this->subject->method('getEnvironmentMode')->willReturn('BE');
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            [
                'uid' => 1,
                'type' => $type,
                'table_name' => $this->testTableName,
                'deleted' => 0,
                'hidden' => 0,
                'starttime' => 0,
                'endtime' => 0
            ]
        ]);
        $object = $this->subject->findByUid(1);

        self::assertInstanceOf(StaticRecordCollection::class, $object);
    }

    /**
     * @test
     */
    public function doesFindByUidRespectDeletedFieldInBackendMode(): void
    {
        $this->subject->method('getEnvironmentMode')->willReturn('BE');
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            [
                'uid' => 1,
                'type' => $type,
                'table_name' => $this->testTableName,
                'deleted' => 1,
                'hidden' => 0,
                'starttime' => 0,
                'endtime' => 0
            ]
        ]);
        $object = $this->subject->findByUid(1);

        self::assertNull($object);
    }

    /**
     * @test
     */
    public function doesFindByUidIgnoreOtherEnableFieldsInBackendMode(): void
    {
        $this->subject->method('getEnvironmentMode')->willReturn('BE');
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            [
                'uid' => 1,
                'type' => $type,
                'table_name' => $this->testTableName,
                'hidden' => 1,
            ],
            [
                'uid' => 2,
                'type' => $type,
                'table_name' => $this->testTableName,
                'starttime' => time() + 99999,
            ],
            [
                'uid' => 3,
                'type' => $type,
                'table_name' => $this->testTableName,
                'endtime' => time() - 99999
            ]
        ]);
        $hiddenObject  = $this->subject->findByUid(1);
        $futureObject  = $this->subject->findByUid(2);
        $expiredObject = $this->subject->findByUid(3);

        self::assertInstanceOf(StaticRecordCollection::class, $hiddenObject);
        self::assertInstanceOf(StaticRecordCollection::class, $futureObject);
        self::assertInstanceOf(StaticRecordCollection::class, $expiredObject);
    }

    /**
     * @test
     */
    public function doesFindByUidReturnAnObjectInFrontendMode(): void
    {
        $this->subject->method('getEnvironmentMode')->willReturn('FE');
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            [
                'uid' => 1,
                'type' => $type,
                'table_name' => $this->testTableName,
                'deleted' => 0,
                'hidden' => 0,
                'starttime' => 0,
                'endtime' => 0
            ]
        ]);
        $object = $this->subject->findByUid(1);

        self::assertInstanceOf(StaticRecordCollection::class, $object);
    }

    /**
     * @test
     */
    public function doesFindByUidRespectEnableFieldsInFrontendMode(): void
    {
        $this->subject->method('getEnvironmentMode')->willReturn('FE');
        $type = RecordCollectionRepository::TYPE_Static;
        $this->insertTestData([
            [
                'uid' => 1,
                'type' => $type,
                'table_name' => $this->testTableName,
                'deleted' => 1,
            ],
            [
                'uid' => 2,
                'type' => $type,
                'table_name' => $this->testTableName,
                'hidden' => 1,
            ],
            [
                'uid' => 3,
                'type' => $type,
                'table_name' => $this->testTableName,
                'starttime' => time() + 99999,
            ],
            [
                'uid' => 4,
                'type' => $type,
                'table_name' => $this->testTableName,
                'endtime' => time() - 99999
            ]
        ]);
        $deletedObject = $this->subject->findByUid(1);
        $hiddenObject  = $this->subject->findByUid(2);
        $futureObject  = $this->subject->findByUid(3);
        $expiredObject = $this->subject->findByUid(4);

        self::assertNull($deletedObject);
        self::assertNull($hiddenObject);
        self::assertNull($futureObject);
        self::assertNull($expiredObject);
    }

    /**
     * Insert test rows into the sys_collection table
     *
     * @param array $rows
     */
    protected function insertTestData(array $rows)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_collection');
        $platform = $connection->getDatabasePlatform();
        $sqlServerIdentityDisabled = false;
        if ($platform instanceof SQLServerPlatform) {
            try {
                $connection->exec('SET IDENTITY_INSERT sys_collection ON');
                $sqlServerIdentityDisabled = true;
            } catch (DBALException $e) {
                // Some tables like sys_refindex don't have an auto-increment uid field and thus no
                // IDENTITY column. Instead of testing existence, we just try to set IDENTITY ON
                // and catch the possible error that occurs.
            }
        }

        $types = [];
        $tableDetails = $connection->getSchemaManager()->listTableDetails('sys_collection');
        foreach ($rows as $row) {
            foreach ($row as $columnName => $columnValue) {
                $types[] = $tableDetails->getColumn($columnName)->getType()->getBindingType();
            }
            break;
        }

        foreach ($rows as $row) {
            $connection->insert('sys_collection', $row, $types);
        }

        if ($sqlServerIdentityDisabled) {
            // Reset identity if it has been changed
            $connection->exec('SET IDENTITY_INSERT sys_collection OFF');
        }
    }
}
