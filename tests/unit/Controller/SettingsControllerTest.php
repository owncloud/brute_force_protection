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

namespace OCA\BruteForceProtection\Tests\Controller;

use OCA\BruteForceProtection\Controller\SettingsController;
use OCA\BruteForceProtection\BruteForceProtectionConfig;
use OCP\IRequest;
use Test\TestCase;

class SettingsControllerTest extends TestCase {
	/** @var IRequest|\PHPUnit\Framework\MockObject\MockObject */
	private $request;
	/** @var BruteForceProtectionConfig|\PHPUnit\Framework\MockObject\MockObject */
	private $config;
	/** @var SettingsController */
	private $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->request = $this->createMock(IRequest::class);
		$this->config = $this->createMock(BruteForceProtectionConfig::class);
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
