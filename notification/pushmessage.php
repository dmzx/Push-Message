<?php
/**
 *
 * Push Message. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\pushmessage\notification;

use phpbb\notification\type\base;
use phpbb\controller\helper;
use phpbb\user_loader;

class pushmessage extends base
{
	protected $user_loader;

	protected $helper;

	public function set_user_loader(user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function set_helper(helper $helper)
	{
		$this->helper = $helper;
	}

	public function get_type()
	{
		return 'dmzx.pushmessage.notification.type.pushmessage';
	}

	public static $notification_option = [
		'lang' 	=> 'NOTIFICATION_PUSHMESSAGE_UCP',
		'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
	];

	public function is_available()
	{
		return true;
	}

	public static function get_item_id($data)
	{
		return (int) $data['pushmessages_notify_id'];
	}

	public static function get_item_parent_id($data)
	{
		return 0;
	}

	public function find_users_for_notification($data, $options = [])
	{
		$users = [];
		$users[$data['receiver']] = [''];
		$this->user_loader->load_users(array_keys($users));

		return $this->check_user_notification_options(array_keys($users), $options);
	}

	public function users_to_query()
	{
		return array($this->get_data('sender'));
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('sender'));
	}

	public function get_title()
	{
		$users = [];
		$users = [$this->get_data('receiver')];
		$this->user_loader->load_users($users);
		$username = $this->user_loader->get_username($this->get_data('receiver'), 'no_profile');
		$username_sender = $this->user_loader->get_username($this->get_data('sender'), 'no_profile');

		return $username . '&nbsp;' . $this->get_data('pushmessages_notify_msg') . '&nbsp;' . $username_sender;
	}

	public function get_url()
	{
		return append_sid("{$this->phpbb_root_path}ucp.{$this->php_ext}", ['i' => 'ucp_notifications', 'mode' => 'notification_list']);
	}

	public function get_email_template()
	{
		return '@dmzx_pushmessage/pushmessage_notification';
	}

	public function get_email_template_variables()
	{
		return [
			'PUSHMESSAGES_NOTIFY_MSG'		=> $this->get_data('pushmessages_notify_msg'),
			'USERNAME_WHO'					=> $this->user_loader->get_username($this->get_data('sender'), 'username', false, false, false),
		];
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('pushmessages_notify_id', $data['pushmessages_notify_id']);
		$this->set_data('pushmessages_notify_msg', $data['pushmessages_notify_msg']);
		$this->set_data('sender', $data['sender']);
		$this->set_data('receiver', $data['receiver']);
		$this->set_data('mode', $data['mode']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
