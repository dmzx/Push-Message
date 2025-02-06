<?php
/**
 *
 * Push Message. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\pushmessage\acp;

class pushmessage_info
{
	function module()
	{
		return [
			'filename'	=> '\dmzx\pushmessage\acp\pushmessage_module',
			'title'		=> 'ACP_PUSHMESSAGE_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title' => 'ACP_PUSHMESSAGE_CONFIG',
					'auth' 	=> 'ext_dmzx/pushmessage && acl_a_board',
					'cat' 	=> ['ACP_PUSHMESSAGE_TITLE']
				],
			],
		];
	}
}
