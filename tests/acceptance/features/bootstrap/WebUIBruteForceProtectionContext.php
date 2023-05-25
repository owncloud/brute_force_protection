<?php declare(strict_types=1);
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
use Behat\MinkExtension\Context\RawMinkContext;
use Page\BlockedUserPage;
use Page\OwncloudPage;
use Page\BruteForceProtectionPage;
use Page\LoginPage;

require_once 'bootstrap.php';

/**
 * Context for brute force protection specific webUI steps
 */
class WebUIBruteForceProtectionContext extends RawMinkContext implements Context {
	/**
	 * @var FeatureContext
	 */
	private $featureContext;

	/**
	 *
	 * @var WebUIGeneralContext
	 */
	private $webUIGeneralContext;

	/**
	 *
	 * @var OwncloudPage
	 */
	private $owncloudPage;

	/**
	 *
	 * @var BruteForceProtectionPage
	 */
	private $bruteForceProtectionPage;

	/**
	 *
	 * @var LoginPage
	 */
	private $loginPage;
	/**
	 * WebUIBruteForceProtectionContext constructor.
	 *
	 * @param OwncloudPage $owncloudPage
	 * @param BruteForceProtectionPage $bruteForceProtectionPage
	 * @param LoginPage $loginPage
	 */
	public function __construct(
		OwncloudPage $owncloudPage,
		BruteForceProtectionPage $bruteForceProtectionPage,
		LoginPage $loginPage
	) {
		$this->owncloudPage = $owncloudPage;
		$this->bruteForceProtectionPage = $bruteForceProtectionPage;
		$this->loginPage = $loginPage;
	}

	/**
	 * @When the administrator sets the bruteforceprotection settings using the webUI to:
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
	public function setTheBruteforceprotectionSettings(TableNode $settings): void {
		$this->webUIGeneralContext->adminLogsInUsingTheWebUI();
		$this->bruteForceProtectionPage->open();
		$this->bruteForceProtectionPage->setSettings($this->getSession(), $settings->getRowsHash());
	}

	/**
	 * @When the blocked user :username tries to login using the password :password from the webUI
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theDisabledUserTriesToLogin(
		string $username,
		string $password
	): void {
		$password = $this->featureContext->getActualPassword($password);
		/**
		 *
		 * @var BlockedUserPage $blockedUserPage
		 */
		$blockedUserPage = $this->loginPage->loginAs($username, $password, 'BlockedUserPage');
		$blockedUserPage->waitTillPageIsLoaded($this->getSession());
	}

	/**
	 * @BeforeScenario
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function setUpScenario(BeforeScenarioScope $scope): void {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
		$this->webUIGeneralContext = $environment->getContext('WebUIGeneralContext');
	}
}
