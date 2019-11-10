<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 * @author Michael Usher <michael.usher@aarnet.edu.au>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH.
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

/** @var array $_ */
/** @var \OCP\IL10N $l */
script('brute_force_protection', 'settings-admin');
?>
<div id="brute-force-protection" class="section">
	<h2 class="inlineblock"><?php p($l->t('Brute Force Protection')); ?></h2>
	<span id="save-bfp-settings-message" class="msg"></span>
	<div>
		<label for="bfp-threshold-time"><?php p($l->t('Count failed attempts over how many seconds?')) ?></label><br>
		<input type="number" id="bfp-threshold-time"  value="<?php p($_['bruteForceProtectionTimeThreshold']) ?>"><br>
		<label for="bfp-fail-tolerance"><?php p($l->t('Ban after how many failed attempts?')) ?></label><br>
		<input type="number" id="bfp-fail-tolerance"  value="<?php p($_['bruteForceProtectionFailTolerance']) ?>"><br>
		<label for="bfp-ban-period"><?php p($l->t('Ban for how many seconds?')) ?></label><br>
		<input type="number" id="bfp-ban-period" value="<?php p($_['bruteForceProtectionBanPeriod']) ?>"><br>
		<button id="save-bfp-settings" class="save"><?php p($l->t('Save settings'));?></button>
	</div>
</div>
