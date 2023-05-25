<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
 * @copyright Copyright (c) 2020, ownCloud GmbH
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

use OCA\BruteForceProtection\Db\FailedLinkAccess;
use OCA\BruteForceProtection\Db\FailedLinkAccessMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IDBConnection;
use Test\TestCase;

/**
 * @group DB
 */
class FailedLinkAccessMapperTest extends TestCase {
	/** @var  FailedLinkAccess $mapper*/
	private $mapper;

	/** @var  IDBConnection $connection*/
	private $connection;

	/** @var ITimeFactory | \PHPUnit\Framework\MockObject\MockObject $timeFactory */
	private $timeFactoryMock;

	/** @var int $baseTime */
	private $baseTime;

	/** @var string  */
	private $dbTable = 'bfp_link_accesses';

	/** @var int $thresholdConfigVal */
	private $thresholdConfigVal = 60;

	public function setUp(): void {
		parent::setUp();
		$this->baseTime = \time();
		$this->timeFactoryMock = $this->createMock(ITimeFactory::class);
		$this->connection = \OC::$server->getDatabaseConnection();
		$this->mapper = new FailedLinkAccessMapper($this->connection, $this->timeFactoryMock);

		$query = $this->connection->getQueryBuilder()->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertEmpty($result, 'we need to start with a empty bfp_link_accesses table');

		$this->addInitialTestEntries();
	}

	public function addInitialTestEntries() {
		$linkAccess = new FailedLinkAccess();
		$linkAccess->setLinkToken('token1');
		$linkAccess->setIp("192.168.1.1");
		$linkAccess->setAttemptedAt($this->baseTime+20);
		$this->mapper->insert($linkAccess);

		$linkAccess = new FailedLinkAccess();
		$linkAccess->setLinkToken('token1');
		$linkAccess->setIp("192.168.1.1");
		$linkAccess->setAttemptedAt($this->baseTime+60);
		$this->mapper->insert($linkAccess);

		$linkAccess = new FailedLinkAccess();
		$linkAccess->setLinkToken('token1');
		$linkAccess->setIp("192.168.1.2");
		$linkAccess->setAttemptedAt($this->baseTime+60);
		$this->mapper->insert($linkAccess);

		$linkAccess = new FailedLinkAccess();
		$linkAccess->setLinkToken('token2');
		$linkAccess->setIp("192.168.1.1");
		$linkAccess->setAttemptedAt($this->baseTime+60);
		$this->mapper->insert($linkAccess);

		$linkAccess = new FailedLinkAccess();
		$linkAccess->setLinkToken('token1');
		$linkAccess->setIp("192.168.1.1");
		$linkAccess->setAttemptedAt($this->baseTime+100);
		$this->mapper->insert($linkAccess);
	}

	public function tearDown(): void {
		parent::tearDown();
		$query = $this->connection->getQueryBuilder()->delete($this->dbTable);
		$query->execute();
	}

	public function testGetFailedAccessCountForTokenIpCombination() {
		$this->assertEquals(3, $this->mapper->getFailedAccessCountForTokenIpCombination('token1', '192.168.1.1', $this->baseTime));
		$this->assertEquals(1, $this->mapper->getFailedAccessCountForTokenIpCombination('token1', '192.168.1.2', $this->baseTime));
		$this->assertEquals(1, $this->mapper->getFailedAccessCountForTokenIpCombination('token2', '192.168.1.1', $this->baseTime));
	}

	public function testGetLastFailedAccessTimeForTokenIpCombination() {
		$lastAttemptTime = $this->baseTime+100;
		$this->assertEquals($this->mapper->getLastFailedAccessTimeForTokenIpCombination('token1', '192.168.1.1'), $lastAttemptTime);
	}

	public function testDeleteFailedAccessForTokenIpCombination() {
		$builder = $this->connection->getQueryBuilder();

		$query = $builder->select('*')->from($this->dbTable)
			->Where($builder->expr()->eq('ip', $builder->createNamedParameter("192.168.1.1")))
			->andWhere($builder->expr()->eq('link_token', $builder->createNamedParameter("token1")));
		$result = $query->execute()->fetchAll();
		$this->assertCount(3, $result);

		$this->mapper->deleteFailedAccessForTokenIpCombination('token1', "192.168.1.1");

		$query = $builder->select('*')->from($this->dbTable)
			->Where($builder->expr()->eq('ip', $builder->createNamedParameter("192.168.1.1")))
			->andWhere($builder->expr()->eq('link_token', $builder->createNamedParameter("token1")));
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
		$this->mapper->deleteOldFailedAccesses(60);
		$query = $builder->select('*')->from($this->dbTable);
		$result = $query->execute()->fetchAll();
		$this->assertCount(1, $result);
	}
}
