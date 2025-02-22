<?php
/**
 *
 * Push Message. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\pushmessage\migrations;

class pushmessage_v102 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\dmzx\pushmessage\migrations\pushmessage_v101'
		];
	}

	public function update_data()
	{
		return [
			// Update config
			['config.update', ['pushmessage_version', '1.0.2']],
		];
	}
}
