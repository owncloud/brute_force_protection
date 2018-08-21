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

use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCA\BruteForceProtection\Db\FailedLoginAttempt;
use OCA\BruteForceProtection\Db\FailedLoginAttemptMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IDBConnection;
use Test\TestCase;

/**
 * @group DB
 */
class FailedLoginAttemptMapperTest extends TestCase {

	/** @var  FailedLoginAttemptMapper $mapper*/
	private $mapper;

	/** @var  IDBConnection $connection*/
	private $connection;

	/** @var BruteForceProtectionConfig | \PHPUnit_Framework_MockObject_MockObject $configMock */
	private $configMock;

	/** @var ITimeFactory | \PHPUnit_Framework_MockObject_MockObject $timeFactory */
	private $timeFactoryMock;

	/** @var int $baseTime */
	private $baseTime;

	/** @var string  */
	private $dbTable = 'bfp_failed_logins';

	/** @var string $thresholdConfigVal */
	private $thresholdConfigVal = '60';

	public function setUp() {
		parent::setUp();
		$this->baseTime = \time();
		$this->timeFactoryMock = $this->getMockBuilder(ITimeFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$this->connection = \OC::$server->getDatabaseConnection();
		$this->configMock = $this->getMockBuilder(BruteForceProtectionConfig::class)
			->disableOriginalConstructor()
			->getMock();
		$this->mapper = new FailedLoginAttemptMapper($this->connection, $this->configMock, $this->timeFactoryMock);

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

	public function tearDown() {
		parent::tearDown();
		$query = $this->connection->getQueryBuilder()->delete($this->dbTable);
		$query->execute();
	}

	public function testGetSuspiciousActivityCountForUidIpCombination() {
		$functionCallTime = $this->baseTime+110;
		$this->configMock->expects($this->exactly(3))
			->method('getBruteForceProtectionTimeThreshold')
			->willReturn($this->thresholdConfigVal);
		$this->timeFactoryMock->expects($this->exactly(3))
			->method('getTime')
			->willReturn($functionCallTime);

		$this->assertEquals(2, $this->mapper->getSuspiciousActivityCountForUidIpCombination('test1', '192.168.1.1'));
		$this->assertEquals(1, $this->mapper->getSuspiciousActivityCountForUidIpCombination('test1', '192.168.1.2'));
		$this->assertEquals(1, $this->mapper->getSuspiciousActivityCountForUidIpCombination('test2', '192.168.1.1'));
	}

	public function testGetLastFailedLoginAttemptTimeForIp() {
		$lastAttemptTime = $this->baseTime+100;
		$this->configMock->expects($this->once())
			->method('getBruteForceProtectionTimeThreshold')
			->willReturn('300');
		$this->timeFactoryMock->expects($this->once())
			->method('getTime')
			->willReturn($this->baseTime+300);

		$this->assertEquals($this->mapper->getLastFailedLoginAttemptTimeForIp('192.168.1.1'), $lastAttemptTime);
	}

	public function testDeleteSuspiciousAttemptsForUidIpCombination() {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->select('*')->from($this->dbTable)
			->Where($builder->expr()->eq('ip', $builder->createNamedParameter("192.168.1.1")))
			->andWhere($builder->expr()->eq('uid', $builder->createNamedParameter("test1")));
		$result = $query->execute()->fetchAll();
		$this->assertCount(3, $result);

		$this->mapper->deleteSuspiciousAttemptsForUidIpCombination('test1', "192.168.1.1");

		$query = $builder->select('*')->from($this->dbTable)
			->Where($builder->expr()->eq('ip', $builder->createNamedParameter("192.168.1.1")))
			->andWhere($builder->expr()->eq('uid', $builder->createNamedParameter("test1")));
		$result = $query->execute()->fetchAll();
		$this->assertCount(0, $result);
	}

	public function testDeleteOldFailedLoginAttempts() {
		$builder = $this->connection->getQueryBuilder();
		$functionCallTime = $this->baseTime+130;
		$this->timeFactoryMock->expects($this->exactly(1))
			->method('getTime')
			->willReturn($functionCallTime);
		$query = $builder->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertCount(5, $result);
		$this->mapper->deleteOldFailedLoginAttempts($this->thresholdConfigVal);
		$query = $builder->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertCount(1, $result);
	}
}
