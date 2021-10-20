<?php
/**
 * ownCloud
 *
 * @author Artur Neumann <info@jankaritech.com>
 * @copyright Copyright (c) 2018 Artur Neumann info@jankaritech.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use TestHelpers\SetupHelper;

require_once 'bootstrap.php';

/**
 * Context for brute force protection specific steps
 */
class BruteForceProtectionContext implements Context {

	/**
	 * @var FeatureContext
	 */
	private $featureContext;

	private $savedSettings = [];

	/**
	 * @Given the administrator has set the bruteforceprotection settings to:
	 *
	 * @param TableNode $settings settings to be set
	 *                            table without header and with two columns,
	 *                            the settings name and the setting value.
	 *                            possible settings:
	 *                            threshold-time, fail-tolerance, ban-period
	 *
	 * @return void
	 * @throws Exception
	 */
	public function setTheBruteforceprotectionSettings(
		TableNode $settings
	): void {
		foreach ($settings->getRowsHash() as $setting => $value) {
			$this->setBruteforceprotectionSetting($setting, $value);
		}
	}

	/**
	 *
	 * @param string $setting
	 * @param string $value
	 *
	 * @throws \Exception
	 * @return void
	 */
	public function setBruteforceprotectionSetting(
		string $setting,
		string $value
	): void {
		$settingName = $this->mapSettingName($setting);

		if ($value === '') {
			$value = "''";
		}

		$occResult = SetupHelper::runOcc(
			[
				'config:app:set',
				'brute_force_protection',
				$settingName,
				'--value',
				$value
			]
		);
		if ($occResult['code'] !== "0") {
			throw new \Exception(
				__METHOD__ .
				"\ncould not set '$settingName' for brute_force_protection app\n" .
				"error message: " . $occResult['code']
			);
		}
	}

	/**
	 *
	 * @param string $setting
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getBruteforceprotectionSetting(string $setting): string {
		$settingName = $this->mapSettingName($setting);
		return \trim(
			SetupHelper::runOcc(
				[
					'config:app:get',
					'brute_force_protection',
					$settingName,
				]
			)['stdOut']
		);
	}

	/**
	 * maps the settings threshold-time, fail-tolerance, ban-period to names needed for occ
	 *
	 * @param string $setting
	 *
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function mapSettingName(string $setting): string {
		switch ($setting) {
			case "threshold-time":
				$settingName = "brute_force_protection_time_threshold";
				break;
			case "fail-tolerance":
				$settingName = "brute_force_protection_fail_tolerance";
				break;
			case "ban-period":
				$settingName = "brute_force_protection_ban_period";
				break;
			default:
				throw new \InvalidArgumentException(
					"bruteforce setting can only be threshold-time || fail-tolerance || ban-period"
				);
		}
		return $settingName;
	}

	/**
	 * @BeforeScenario
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 * @throws Exception
	 */
	public function setUpScenario(BeforeScenarioScope $scope): void {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
		SetupHelper::init(
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getOcPath()
		);
		$this->savedSettings['threshold-time']
			= $this->getBruteforceprotectionSetting('threshold-time');
		$this->savedSettings['fail-tolerance']
			= $this->getBruteforceprotectionSetting('fail-tolerance');
		$this->savedSettings['ban-period']
			= $this->getBruteforceprotectionSetting('ban-period');
	}

	/**
	 * @AfterScenario
	 *
	 * make sure all created users get their ban-logs reset
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function cleanupBannedUsers(): void {
		$this->setBruteforceprotectionSetting('ban-period', '0');
		foreach ($this->featureContext->getCreatedUsers() as $user => $userData) {
			$this->featureContext->initializeUser(
				$user,
				$this->featureContext->getUserPassword($user)
			);
		}
	}

	/**
	 * @AfterScenario
	 *
	 * @return void
	 * @throws Exception
	 */
	public function setBackAppSettings(): void {
		foreach ($this->savedSettings as $setting => $value) {
			$this->setBruteforceprotectionSetting($setting, $value);
		}
	}
}
