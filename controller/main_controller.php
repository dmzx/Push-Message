<?php
/**
 *
 * Push Message. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\pushmessage\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\auth\auth;
use phpbb\request\request_interface;
use phpbb\notification\manager;
use dmzx\pushmessage\core\functions;
use phpbb\db\driver\driver_interface;
use phpbb\user;
use phpbb\exception\http_exception;

class main_controller
{
	/** @var config */
	protected $config;

	/** @var helper */
	protected $helper;

	/** @var template */
	protected $template;

	/** @var language */
	protected $language;

	/** @var auth */
	protected $auth;

	/** @var request_interface */
	protected $request;

	/** @var manager */
	protected $notification_manager;

	/** @var functions */
	protected $functions;

	/** @var driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/**
	 * The database tables
	 *
	 * @var string
	 */
	protected $pushmessage_log;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param helper				$helper
	 * @param template				$template
	 * @param language				$language
	 * @param auth					$auth
	 * @param request_interface		$request
	 * @param manager 				$notification_manager
	 * @param functions				$functions
	 * @param driver_interface 		$db
	 * @param user 					$user
	 * @param string 				$pushmessage_log
	 */
	public function __construct(
		config $config,
		helper $helper,
		template $template,
		language $language,
		auth $auth,
		request_interface $request,
		manager $notification_manager,
		functions $functions,
		driver_interface $db,
		user $user,
		$pushmessage_log
	)
	{
		$this->config				= $config;
		$this->helper				= $helper;
		$this->template				= $template;
		$this->language				= $language;
		$this->auth 				= $auth;
		$this->request 				= $request;
		$this->notification_manager = $notification_manager;
		$this->functions			= $functions;
		$this->db 					= $db;
		$this->user 				= $user;
		$this->pushmessage_log 		= $pushmessage_log;
	}

	var $u_action;

	public function handle()
	{
		// Check auth
		if (!$this->auth->acl_get('u_dmzx_pushmessage'))
		{
			if (!$this->user->data['is_registered'])
			{
				login_box();
			}
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		// Check enable
		if (!$this->config['pushmessage_enable'])
		{
			if (!$this->user->data['is_registered'])
			{
				login_box();
			}
			throw new http_exception(403, 'PUSHMESSAGE_NOT_ENABLED');
		}

		// Add lang file
		$this->language->add_lang('pushmessage', 'dmzx/pushmessage');

		// Check if the form is submitted
		if ($this->request->is_set_post('submit'))
		{
			$username = $this->request->variable('username_id', '', true);
			$message = $this->request->variable('message', '', true);

			// Fetch the user ID based on the provided username
			$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE username = "' . $this->db->sql_escape($username) . '"';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$to_user_id = $row['user_id'];
			$this->db->sql_freeresult($result);

			if ($to_user_id)
			{

				// Check if the user is trying to send a message to themselves
				if ($this->user->data['user_id'] == $to_user_id)
				{
					// Redirect with an error message
					meta_refresh(2, append_sid($this->helper->route('dmzx_pushmessage_controller')));
					trigger_error($this->user->lang['PUSHMESSAGE_CANNOT_SEND_TO_SELF']);
				}

				$current_time = time();

				$uid = $bitfield = $options = '';
				$allow_bbcode = $allow_urls = $allow_smilies = true;

				generate_text_for_storage($message, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

				// Add to pushmessage_log
				$sql = 'INSERT INTO ' . $this->pushmessage_log . ' ' . $this->db->sql_build_array('INSERT', [
					'pushmessage_send'		=> (int) $this->user->data['user_id'],
					'pushmessage_recv'		=> (int) $to_user_id,
					'pushmessage_comment'	=> $message,
					'pushmessage_type'		=> '1',
					'pushmessage_date'		=> $current_time,
				]);
				$this->db->sql_query($sql);

				// Increase our notification sent counter
				$this->config->increment('pushmessage_notification_id', 1);

				// Store the notification data we will use in an array
				$data = [
					'pushmessages_notify_id'	=> (int) $this->config['pushmessage_notification_id'],
					'pushmessages_notify_msg'	=> sprintf($this->user->lang['PUSHMESSAGE_TRANSFER_SUCCES'], $message, $this->config['pushmessages_name']),
					'sender'					=> (int) $this->user->data['user_id'],
					'receiver'				 	=> (int) $to_user_id,
					'mode'					 	=> 'message',
				];

				// Create the notification
				$this->notification_manager->add_notifications('dmzx.pushmessage.notification.type.pushmessage', $data);

				// Redirect with a success message
				meta_refresh(2, append_sid($this->helper->route('dmzx_pushmessage_controller')));
				trigger_error($this->user->lang['PUSHMESSAGE_MESSAGE_SENT_SUCCESSFULLY']);
			}
			else
			{
				// Redirect with a fail message
				meta_refresh(2, append_sid($this->helper->route('dmzx_pushmessage_controller')));
				trigger_error($this->user->lang['PUSHMESSAGE_SELECT_USER_ERROR']);
			}
		}

		$user_id = (int) $this->user->data['user_id'];

		// Fetch recent sent messages
		$sql = 'SELECT * FROM ' . $this->pushmessage_log . ' WHERE pushmessage_send = ' . $user_id . ' ORDER BY pushmessage_date DESC';
		$result = $this->db->sql_query_limit($sql, $this->config['pushmessage_count_last_message']);
		$sent_messages = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sent_messages[] = $row;
		}
		$this->db->sql_freeresult($result);

		// Fetch recent received messages
		$sql = 'SELECT * FROM ' . $this->pushmessage_log . ' WHERE pushmessage_recv = ' . $user_id . ' ORDER BY pushmessage_date DESC';
		$result = $this->db->sql_query_limit($sql, $this->config['pushmessage_count_last_message']);
		$received_messages = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$received_messages[] = $row;
		}
		$this->db->sql_freeresult($result);

		// Get user IDs from messages
		$all_user_ids = [];
		foreach ($sent_messages as $msg)
		{
			$all_user_ids[] = $msg['pushmessage_recv'];
		}
		foreach ($received_messages as $msg)
		{
			$all_user_ids[] = $msg['pushmessage_send'];
		}
		$all_user_ids = array_unique($all_user_ids);

		// Fetch usernames and user data
		$user_datas = [];
		if (count($all_user_ids))
		{
			$sql = 'SELECT user_id, username, user_colour FROM ' . USERS_TABLE . ' WHERE ' . $this->db->sql_in_set('user_id', $all_user_ids);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_datas[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		// Attach username strings to the message arrays
		foreach ($sent_messages as &$msg)
		{
			$user_row = $user_datas[$msg['pushmessage_recv']] ?? null;
			$msg['receiver_username'] = $user_row['username'] ?? '(unknown)';
			$msg['receiver_username_string'] = ($user_row)	? get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']) : '(unknown)';
		}
		foreach ($received_messages as &$msg)
		{
			$user_row = $user_datas[$msg['pushmessage_send']] ?? null;
			$msg['sender_username'] = $user_row['username'] ?? '(unknown)';
			$msg['sender_username_string'] = ($user_row) ? get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']) : '(unknown)';
		}

		// Assign template variables
		$this->template->assign_vars([
			'SENT_MESSAGES' 					=> $sent_messages,
			'RECEIVED_MESSAGES' 				=> $received_messages,
			'PUSHMESSAGE_FOOTER_VIEW'			=> true,
			'PUSHMESSAGE_VERSION'				=> $this->config['pushmessage_version'],
			'PUSHMESSAGE_ENABLE_LAST_MESSAGE'	=> $this->config['pushmessage_enable_last_message'],
			'PUSHMESSAGE_COUNT_LAST_MESSAGE'	=> $this->language->lang('PUSHMESSAGE_COUNT_LAST_MESSAGE', (int) $this->config['pushmessage_count_last_message']),
			'U_ACTION' 							=> $this->u_action,
		]);

		$this->functions->assign_authors();

		// Send all data to the template file
		return $this->helper->render('pushmessage.html', $this->user->lang('PUSHMESSAGE_PAGE'));
	}

	public function page_user()
	{
		$username = $this->request->variable('username', '', true);

		if ($username)
		{
			$current_user_id = $this->user->data['user_id'];
			$sql = 'SELECT user_id, username FROM ' . USERS_TABLE . ' WHERE username_clean LIKE "' . $this->db->sql_escape(utf8_clean_string($username)) . '%" AND user_type <> ' . USER_IGNORE . ' AND user_id <> ' . (int) $current_user_id;
			$result = $this->db->sql_query($sql);

			$users = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$users[] = [
					'user_id' => $row['user_id'],
					'username' => $row['username'],
				];
			}
			$this->db->sql_freeresult($result);

			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send($users);
			}
			exit;
		}
	}
}
