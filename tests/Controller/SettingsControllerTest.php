<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\BruteForceProtection\Tests\Controller;

use OCA\BruteForceProtection\Controller\SettingsController;
use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCP\IRequest;
use Test\TestCase;

class SettingsControllerTest extends TestCase {
	/** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
	private $request;
	/** @var BruteForceProtectionConfig|\PHPUnit_Framework_MockObject_MockObject */
	private $config;
	/** @var SettingsController */
	private $controller;

	protected function setUp() {
		parent::setUp();
		$this->request = $this->getMockBuilder(IRequest::class)->getMock();
		$this->config = $this->getMockBuilder(BruteForceProtectionConfig::class)
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new SettingsController('brute_force_protection', $this->request, $this->config);
	}

	public function testState() {
		$expected = [
			'bruteForceProtectionState' => true,
		];
		$this->config->expects($this->exactly(1))
			->method('getBruteForceProtectionConfigs')
			->willReturn($expected);

		$this->assertEquals($expected, $this->controller->state());
	}
}
