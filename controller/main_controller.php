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
					'receiver'				 => (int) $to_user_id,
					'mode'					 => 'message',
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

		// Assign template variables
		$this->template->assign_vars([
			'U_ACTION' => $this->u_action,
		]);

		$this->functions->assign_authors();

		$this->template->assign_vars([
			'PUSHMESSAGE_FOOTER_VIEW'	=> true,
			'PUSHMESSAGE_VERSION'		=> $this->config['pushmessage_version'],
		]);

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
