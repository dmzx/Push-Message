<?php
/**
 *
 * Push Message. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'PUSHMESSAGE_PAGE'						=> 'Push Message',
	'VIEWING_DMZX_PUSHMESSAGE'				=> 'Viewing Push Message page',
	'NOTIFICATION_PUSHMESSAGE_UCP' 			=> 'Receive Push Messages',
	'PUSHMESSAGE_TRANSFER_SUCCES' 			=> 'you received a message: %s from %s',
	'PUSHMESSAGE_MESSAGE_SENT_SUCCESSFULLY' => 'Push message sent successfully.',
	'PUSHMESSAGE_SELECT_USER_ERROR' 		=> 'Please select a user.',
	'PUSHMESSAGE_NOT_ENABELD' 				=> 'Push Message not enabled',
	'PUSHMESSAGE_VERSION' 					=> 'Version',
	'PUSHMESSAGE_TRANSFER_MCHAT' 			=> 'you are mentioned in mChat by %s',
]);
