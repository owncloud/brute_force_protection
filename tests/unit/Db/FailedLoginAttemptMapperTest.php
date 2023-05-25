<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */

namespace OCA\BruteForceProtection\Tests\Db;

use OCA\BruteForceProtection\Db\FailedLoginAttempt;
use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IDBConnection;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

/**
 * @group DB
 */
class FailedLoginAttemptMapperTest extends TestCase {
	/** @var  FailedLoginAttemptMapper $mapper*/
	private $mapper;

	/** @var  IDBConnection $connection*/
	private $connection;

	/** @var ITimeFactory | MockObject $timeFactory */
	private $timeFactoryMock;

	/** @var int $baseTime */
	private $baseTime;

	/** @var string  */
	private $dbTable = 'bfp_failed_logins';

	public function setUp(): void {
		parent::setUp();
		$this->baseTime = \time();
		$this->timeFactoryMock = $this->createMock(ITimeFactory::class);
		$this->connection = \OC::$server->getDatabaseConnection();
		$this->mapper = new FailedLoginAttemptMapper($this->connection, $this->timeFactoryMock);

		$query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertEmpty($result, 'we need to start with a empty bfp_failed_logins table');

		$this->addInitialTestEntries();
	}

	public function addInitialTestEntries() {
		$failedLoginAttempt = new FailedLoginAttempt();
		$failedLoginAttempt->setUid('test1');
		$failedLoginAttempt->setIp("192.168.1.1");
		$failedLoginAttempt->setAttemptedAt($this->baseTime+20);
		$this->mapper->insert($failedLoginAttempt);

		$failedLoginAttempt = new FailedLoginAttempt();
		$failedLoginAttempt->setUid('test1');
		$failedLoginAttempt->setIp("192.168.1.1");
		$failedLoginAttempt->setAttemptedAt($this->baseTime+60);
		$this->mapper->insert($failedLoginAttempt);

		$failedLoginAttempt = new FailedLoginAttempt();
		$failedLoginAttempt->setUid('test1');
		$failedLoginAttempt->setIp("192.168.1.2");
		$failedLoginAttempt->setAttemptedAt($this->baseTime+60);
		$this->mapper->insert($failedLoginAttempt);

		$failedLoginAttempt = new FailedLoginAttempt();
		$failedLoginAttempt->setUid('test2');
		$failedLoginAttempt->setIp("192.168.1.1");
		$failedLoginAttempt->setAttemptedAt($this->baseTime+60);
		$this->mapper->insert($failedLoginAttempt);

		$failedLoginAttempt = new FailedLoginAttempt();
		$failedLoginAttempt->setUid('test1');
		$failedLoginAttempt->setIp("192.168.1.1");
		$failedLoginAttempt->setAttemptedAt($this->baseTime+100);
		$this->mapper->insert($failedLoginAttempt);
	}

	public function tearDown(): void {
		parent::tearDown();
		$query = $this->connection->getQueryBuilder()->delete($this->dbTable);
		$query->execute();
	}

	public function testGetSuspiciousActivityCountForUidIpCombination() {
		$this->assertEquals(3, $this->mapper->getFailedLoginCountForUidIpCombination('test1', '192.168.1.1', $this->baseTime));
		$this->assertEquals(1, $this->mapper->getFailedLoginCountForUidIpCombination('test1', '192.168.1.2', $this->baseTime));
		$this->assertEquals(1, $this->mapper->getFailedLoginCountForUidIpCombination('test2', '192.168.1.1', $this->baseTime));
	}

	public function testGetLastFailedLoginAttemptTimeForUidIpCombination() {
		$lastAttemptTime = $this->baseTime+100;
		$this->assertEquals($this->mapper->getLastFailedLoginAttemptTimeForUidIpCombination('test1', '192.168.1.1'), $lastAttemptTime);
	}

	public function testDeleteFailedLoginAttemptsForUidIpCombination() {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->select('*')->from($this->dbTable)
			->Where($builder->expr()->eq('ip', $builder->createNamedParameter("192.168.1.1")))
			->andWhere($builder->expr()->eq('uid', $builder->createNamedParameter("test1")));
		$result = $query->execute()->fetchAll();
		$this->assertCount(3, $result);

		$this->mapper->deleteFailedLoginAttemptsForUidIpCombination('test1', "192.168.1.1");

		$query = $builder->select('*')->from($this->dbTable)
			->Where($builder->expr()->eq('ip', $builder->createNamedParameter("192.168.1.1")))
			->andWhere($builder->expr()->eq('uid', $builder->createNamedParameter("test1")));
		$result = $query->execute()->fetchAll();
		$this->assertCount(0, $result);
	}

	public function testDeleteOldFailedLoginAttempts() {
		$builder = $this->connection->getQueryBuilder();
		$functionCallTime = $this->baseTime+130;
		$this->timeFactoryMock->method('getTime')
			->willReturn($functionCallTime);
		$query = $builder->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertCount(5, $result);
		$this->mapper->deleteOldFailedLoginAttempts(60);
		$query = $builder->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertCount(1, $result);
	}
}
