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

class install_pushmessage extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330'
		];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'pushmessage_log' => [
					'COLUMNS' => [
						'pushmessage_id' 		=> ['UINT', null, 'auto_increment'],
						'pushmessage_send' 		=> ['UINT', 0],
						'pushmessage_recv' 		=> ['UINT', 0],
						'pushmessage_comment' 	=> ['TEXT', ''],
						'pushmessage_type' 		=> ['TINT:1', 0],
						'pushmessage_date' 		=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'pushmessage_id',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			// Add config
			['config.add', ['pushmessage_version', '1.0.0']],
			['config.add', ['pushmessage_enable', 0]],
			['config.add', ['pushmessage_popup', 0]],
			['config.add', ['pushmessage_notification_id', 0]],
			['config.add', ['pushmessage_pagination', 10]],
			['config.add', ['pushmessage_order_by', 'username_clean']],
			['config.add', ['pushmessage_order_dir', 'ASC']],
			// Add user permission
			['permission.add', ['u_dmzx_pushmessage', true]],
			// Set permission
			['permission.permission_set', ['ADMINISTRATORS', 'u_dmzx_pushmessage', 'group']],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PUSHMESSAGE_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_PUSHMESSAGE_TITLE',
				[
					'module_basename'	=> '\dmzx\pushmessage\acp\pushmessage_module',
					'modes'				=> ['settings'],
				],
			]],

		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'pushmessage_log',
			],
		];
	}
}
