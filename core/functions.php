<?php
/**
 *
 * Push Message. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\pushmessage\core;

use phpbb\template\template;
use phpbb\extension\manager;
use phpbb\config\config;

class functions
{
	/** @var template */
	protected $template;

	/** @var manager */
	protected $extension_manager;

	/** @var config */
	protected $config;

	/**
		* Constructor
	*
	* @param template			$template
	* @param manager 			$extension_manager
	* @param config				$config
	*/
	function __construct(
		template $template,
		manager $extension_manager,
		config $config
	)
	{
		$this->template				= $template;
		$this->extension_manager	= $extension_manager;
		$this->config				= $config;
	}

	// Assign authors
	public function assign_authors()
	{
		$md_manager = $this->extension_manager->create_extension_metadata_manager('dmzx/pushmessage', $this->template);
		$meta = $md_manager->get_metadata();
		$author_names = [];
		$author_homepages = [];

		foreach ($meta['authors'] as $author)
		{
			$author_names[] = $author['name'];
			$author_homepages[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $author['homepage'], $author['name']);
		}

		$this->template->assign_vars([
			'PUSHMESSAGE_DISPLAY_NAME'		=> $meta['extra']['display-name'],
			'PUSHMESSAGE_AUTHOR_NAMES'		=> implode(' &amp; ', $author_names),
			'PUSHMESSAGE_AUTHOR_HOMEPAGES'	=> implode(' &amp; ', $author_homepages),
		]);
	}
}
