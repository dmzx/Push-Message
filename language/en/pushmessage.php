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
	'PUSHMESSAGE_VERSION'					=> 'Version',
	'PUSHMESSAGE_MESSAGE_SEND' 				=> 'Send Push Message',
	'PUSHMESSAGE_USER_SELECTION' 			=> 'Select User',
	'PUSHMESSAGE_USER_SELECTION_EXPLAIN' 	=> 'Select the user who receives the push message.',
	'PUSHMESSAGE_MESSAGE' 					=> 'Message',
	'PUSHMESSAGE_SUBMIT' 					=> 'Submit',
	'PUSHMESSAGE_SELECT_USER' 				=> 'Select User',
	'PUSHMESSAGE_CANNOT_SEND_TO_SELF' 		=> 'Cannot send message to yourself.',
	'PUSHMESSAGE_SENT_MESSAGES' 			=> 'sent',
	'PUSHMESSAGE_RECEIVED_MESSAGES' 		=> 'received',
	'PUSHMESSAGE_NO_RECEIVED_MESSAGES' 		=> 'No messages received yet.',
	'PUSHMESSAGE_NO_SENT_MESSAGES' 			=> 'No messages sent yet.',
	'PUSHMESSAGE_TO' 						=> 'To member',
	'PUSHMESSAGE_FROM' 						=> 'From member',
	'PUSHMESSAGE_DATE' 						=> 'Date and Time',
	'PUSHMESSAGE_COUNT_LAST_MESSAGE'		=>	[
		1 => 'Last message',
		2 => 'Last %s messages',
	],
]);
