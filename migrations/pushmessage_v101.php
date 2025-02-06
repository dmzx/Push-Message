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

class pushmessage_v101 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\dmzx\pushmessage\migrations\install_pushmessage'
		];
	}

	public function update_data()
	{
		return [
			// Add config
			['config.update', ['pushmessage_version', '1.0.1']],
			// Remove config
			['config.remove', ['pushmessage_order_by', 'username_clean']],
			['config.remove', ['pushmessage_order_dir', 'ASC']],
		];
	}
}
