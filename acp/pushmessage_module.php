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

class pushmessage_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $user, $request;

		// Add the ACP lang file
		$user->add_lang_ext('dmzx/pushmessage', 'acp_pushmessage');

		// Request
		$action = $request->variable('action', '');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('dmzx.pushmessage.admin.controller');

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		switch ($mode)
		{
			case 'settings':
				$this->tpl_name = 'acp_pushmessage';
				$this->page_title = $user->lang['ACP_PUSHMESSAGE_TITLE'];

				switch ($action)
				{
					case 'delete';
						$admin_controller->delete();
					break;
				}
				$admin_controller->display_options();
			break;
		}
	}
}
