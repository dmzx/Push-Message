<?php
/**
 *
 * Push Message. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\pushmessage\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\language\language;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\user;
use phpbb\notification\manager;
use Symfony\Component\DependencyInjection\Container;

class main_listener implements EventSubscriberInterface
{
	/* @var language */
	protected $language;

	/* @var helper */
	protected $helper;

	/* @var template */
	protected $template;

	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var user */
	protected $user;

	/** @var manager */
	protected $notification_manager;

	/** @var Container */
	protected $phpbb_container;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param language		$language
	 * @param helper		$helper
	 * @param template		$template
	 * @param auth			$auth
	 * @param config		$config
	 * @param user 			$user
	 * @param manager 		$notification_manager
	 * @param Container 	$phpbb_container
	 * @param string		$php_ext
	 */
	public function __construct(
		language $language,
		helper $helper,
		template $template,
		auth $auth,
		config $config,
		user $user,
		manager $notification_manager,
		Container $phpbb_container,
		$php_ext
	)
	{
		$this->language 			= $language;
		$this->helper				= $helper;
		$this->template 			= $template;
		$this->auth 				= $auth;
		$this->config				= $config;
		$this->user 				= $user;
		$this->notification_manager = $notification_manager;
		$this->phpbb_container 		= $phpbb_container;
		$this->php_ext				= $php_ext;
	}

	public static function getSubscribedEvents(): array
	{
		return [
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header'							=> 'add_page_header_link',
			'core.viewonline_overwrite_location'		=> 'viewonline_page',
			'core.permissions'							=> 'add_permissions',
			'dmzx.mchat.action_add_after'				=> 'action_add_after',
		];
	}

	public function load_language_on_setup($event):void
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'dmzx/pushmessage',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function add_page_header_link():void
	{
		$this->template->assign_vars([
			'U_PUSHMESSAGE_PAGE'	=> $this->helper->route('dmzx_pushmessage_controller'),
			'U_PUSHMESSAGE_USER'	=> $this->helper->route('dmzx_pushmessage_user_controller'),
			'PUSHMESSAGE_ALLOW_USE'	=> $this->auth->acl_get('u_dmzx_pushmessage'),
			'S_PUSHMESSAGE_ENABLE'	=> $this->config['pushmessage_enable'],
			'S_PUSHMESSAGE_POPUP'	=> $this->config['pushmessage_popup'],
		]);
	}

	public function viewonline_page($event):void
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/pushmessage') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_DMZX_PUSHMESSAGE');
			$event['location_url'] = $this->helper->route('dmzx_pushmessage_controller');
		}
	}

	public function add_permissions($event):void
	{
		$permissions = $event['permissions'];
		$permissions['u_dmzx_pushmessage'] = ['lang' => 'ACL_U_DMZX_PUSHMESSAGE', 'cat' => 'misc'];
		$event['permissions'] = $permissions;
	}

	public function action_add_after($event):void
	{
		if ($this->phpbb_container->has('dmzx.mchat.settings') && $this->config['pushmessage_mention_mchat'])
		{
			$message = $event['message'];

			if (strpos($message, '@') !== false)
			{
				// Exclude quotes from the message
				$message_without_quotes = preg_replace('/\[quote.*?\[\/quote\]/s', '', $message);

				// Use regular expression to extract the value of 'u' from the message without quotes
				if (preg_match('/u=(\d+)/', $message_without_quotes, $matches))
				{
					$receiver_id = $matches[1];

					$message_data = $event['message_data'];

					// Increase our notification sent counter
					$this->config->increment('pushmessage_notification_id', 1);

					// Store the notification data we will use in an array
					$data = [
						'pushmessages_notify_id'	=> (int) $this->config['pushmessage_notification_id'],
						'pushmessages_notify_msg'	=> sprintf($this->user->lang['PUSHMESSAGE_TRANSFER_MCHAT'], $this->config['pushmessages_name']),
						'sender'					=> (int) $message_data['user_id'],
						'receiver'					=> (int) $receiver_id,
						'mode'						=> 'message',
					];

					 // Create the notification
					$this->createNotification($data);
				}
			}
		}
	}

	protected function createNotification(array $data)
	{
		$this->notification_manager->add_notifications('dmzx.pushmessage.notification.type.pushmessage', $data);
	}
}
