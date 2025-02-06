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
	 * @param string		$php_ext
	 */
	public function __construct(
		language $language,
		helper $helper,
		template $template,
		auth $auth,
		config $config,
		$php_ext
	)
	{
		$this->language = $language;
		$this->helper	= $helper;
		$this->template = $template;
		$this->auth 	= $auth;
		$this->config	= $config;
		$this->php_ext	= $php_ext;
	}

	public static function getSubscribedEvents(): array
	{
		return [
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header'							=> 'add_page_header_link',
			'core.viewonline_overwrite_location'		=> 'viewonline_page',
			'core.permissions'							=> 'add_permissions',
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
}
