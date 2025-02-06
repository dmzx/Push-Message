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
use dmzx\pushmessage\core\functions;
use phpbb\db\driver\driver_interface;
use phpbb\user;
use phpbb\log\log_interface;
use phpbb\pagination;
use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\Response;

class acp_controller
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

	/** @var functions */
	protected $functions;

	/** @var driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var log_interface */
	protected $log;

	/** @var pagination */
	protected $pagination;

	/** @var string */
	protected $pushmessage_log;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param helper				$helper
	 * @param template				$template
	 * @param language				$language
	 * @param auth					$auth
	 * @param request_interface		$request
	 * @param functions				$functions
	 * @param driver_interface 		$db
	 * @param user 					$user
	 * @param log_interface			$log
	 * @param string 				$pushmessage_log
	 */
	public function __construct(
		config $config,
		helper $helper,
		template $template,
		language $language,
		auth $auth,
		request_interface $request,
		functions $functions,
		driver_interface $db,
		user $user,
		log_interface $log,
		pagination $pagination,
		$pushmessage_log
	)
	{
		$this->config				= $config;
		$this->helper				= $helper;
		$this->template				= $template;
		$this->language				= $language;
		$this->auth 				= $auth;
		$this->request 				= $request;
		$this->functions			= $functions;
		$this->db 					= $db;
		$this->user 				= $user;
		$this->log 					= $log;
		$this->pagination 			= $pagination;
		$this->pushmessage_log 		= $pushmessage_log;
	}

	public function display_options()
	{
		add_form_key('acp_pushmessage_form_key');

		if (!$this->auth->acl_get('a_') || $this->user->data['user_type'] != USER_FOUNDER)
		{
			meta_refresh(2, append_sid($this->u_action));
			trigger_error($this->language->lang('ACP_PUSHMESSAGE_NOACCESS'), E_USER_WARNING);
		}

		$this->language->add_lang('pushmessage', 'dmzx/pushmessage');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('acp_pushmessage_form_key'))
			{
				meta_refresh(2, append_sid($this->u_action));
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->set_options();

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PUSHMESSAGE_SETTINGS');

			meta_refresh(2, append_sid($this->u_action));
			trigger_error($this->language->lang('ACP_PUSHMESSAGE_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		if ($this->request->is_set_post('delete_all'))
		{
			if (!check_form_key('acp_pushmessage_form_key'))
			{
				meta_refresh(2, append_sid($this->u_action));
				trigger_error('FORM_INVALID');
			}

			$sql = 'DELETE FROM ' . $this->pushmessage_log;
			$this->db->sql_query($sql);

			meta_refresh(2, append_sid($this->u_action));
			trigger_error($this->language->lang('ACP_PUSHMESSAGE_TABLE_DELETED'));
		}

		$this->functions->assign_authors();

		$this->template->assign_vars([
			'PUSHMESSAGE_VERSION'				=> $this->config['pushmessage_version'],
			'PUSHMESSAGE_ENABLE'				=> $this->config['pushmessage_enable'],
			'PUSHMESSAGE_POPUP'					=> $this->config['pushmessage_popup'],
			'PUSHMESSAGE_PAGINATION'			=> $this->config['pushmessage_pagination'],
			'U_ACTION'						 	=> $this->u_action,
		]);

		// Check if the table is empty
		$sql = 'SELECT COUNT(*) AS total_entries FROM ' . $this->pushmessage_log;
		$result = $this->db->sql_query($sql);
		$total_entries = $this->db->sql_fetchfield('total_entries');
		$this->db->sql_freeresult($result);

		// If the table is empty, skip the rest of the logic
		if ($total_entries == 0)
		{
			return;
		}

		$logs_per_page = $this->config['pushmessage_pagination'];
		$current_page = $this->request->variable('start', 0);
		$search_sender = $this->request->variable('search_sender', '', true);
		$search_receiver = $this->request->variable('search_receiver', '', true);
		$search_message = $this->request->variable('search_message', '', true);

		$conditions = [];
		if ($search_sender) {
			$conditions[] = "LOWER(u1.username_clean) LIKE '%" . $this->db->sql_escape(utf8_clean_string($search_sender)) . "%'";
		}
		if ($search_receiver) {
			$conditions[] = "LOWER(u2.username_clean) LIKE '%" . $this->db->sql_escape(utf8_clean_string($search_receiver)) . "%'";
		}
		if ($search_message) {
			$conditions[] = "LOWER(p.pushmessage_comment) LIKE '%" . $this->db->sql_escape(utf8_clean_string($search_message)) . "%'";
		}

		$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

		// Fetch the total number of log entries with search conditions
		$sql = 'SELECT COUNT(*) AS total_logs FROM ' . $this->pushmessage_log . ' p
				LEFT JOIN ' . USERS_TABLE . ' u1 ON p.pushmessage_send = u1.user_id
				LEFT JOIN ' . USERS_TABLE . ' u2 ON p.pushmessage_recv = u2.user_id ' . $where_clause;
		$result = $this->db->sql_query($sql);
		$total_logs = $this->db->sql_fetchfield('total_logs');
		$this->db->sql_freeresult($result);

		if (!empty($conditions)) {
			// If search is active, disable pagination
			$logs_per_page = $total_logs > 0 ? $total_logs : 1; // Ensure logs_per_page is not zero
		}

		// Trigger error if no results found
		if ($total_logs == 0)
		{
			meta_refresh(1, append_sid($this->u_action));
			trigger_error($this->language->lang('ACP_PUSHMESSAGE_NO_RESULTS'), E_USER_WARNING);
		}

		// Fetch the log entries with full user details for the current page
		$sql = 'SELECT p.*,
						u1.user_id AS sender_id, u1.username AS sender_username, u1.user_colour AS sender_colour,
						u2.user_id AS receiver_id, u2.username AS receiver_username, u2.user_colour AS receiver_colour
				FROM ' . $this->pushmessage_log . ' p
				LEFT JOIN ' . USERS_TABLE . ' u1 ON p.pushmessage_send = u1.user_id
				LEFT JOIN ' . USERS_TABLE . ' u2 ON p.pushmessage_recv = u2.user_id ' . $where_clause . '
				ORDER BY p.pushmessage_date DESC';
		$result = $this->db->sql_query_limit($sql, $logs_per_page, $current_page);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sender_username = get_username_string('full', $row['sender_id'], $row['sender_username'], $row['sender_colour']);
			$receiver_username = get_username_string('full', $row['receiver_id'], $row['receiver_username'], $row['receiver_colour']);

			$this->template->assign_block_vars('row', [
				'SENDER_USERNAME'	=> $sender_username,
				'RECEIVER_USERNAME'	=> $receiver_username,
				'MESSAGE'			=> $row['pushmessage_comment'],
				'DATE'				=> $this->user->format_date($row['pushmessage_date']),
				'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;pushmessage_id=' . $row['pushmessage_id'],
			]);
		}
		$this->db->sql_freeresult($result);

		$pagination_url = $this->u_action;
		$this->pagination->generate_template_pagination($pagination_url, 'pagination', 'start', $total_logs, $logs_per_page, $current_page);

		$this->template->assign_vars([
			'PUSHMESSAGE_TOTAL_MESSAGES'		=> $this->language->lang('ACP_PUSHMESSAGE_MESSAGES', (int) $total_logs),
			'SEARCH_SENDER'					=> $search_sender,
			'SEARCH_RECEIVER'					=> $search_receiver,
			'SEARCH_MESSAGE'					=> $search_message,
		]);
	}

	public function delete()
	{
		$pushmessage_id = $this->request->variable('pushmessage_id', '');

		if (confirm_box(true))
		{
			$sql = 'DELETE FROM ' . $this->pushmessage_log . ' WHERE pushmessage_id = ' . (int) $pushmessage_id;
			$this->db->sql_query($sql);

			meta_refresh(2, append_sid($this->u_action));
			trigger_error($this->language->lang('ACP_PUSHMESSAGE_ENTRY_DELETED'));
		}
		else
		{
			confirm_box(false, $this->user->lang['ACP_PUSHMESSAGE_REALLY_DELETE'], build_hidden_fields([
				'pushmessage_id'	=> $pushmessage_id,
				'action'			=> 'delete',
				])
			);
		}
		redirect($this->u_action);
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_options()
	{
		$this->config->set('pushmessage_enable', $this->request->variable('pushmessage_enable', 0));
		$this->config->set('pushmessage_popup', $this->request->variable('pushmessage_popup', 0));
		$this->config->set('pushmessage_pagination', $this->request->variable('pushmessage_pagination', 0));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
