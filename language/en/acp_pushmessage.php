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
	'ACP_PUSHMESSAGE_TABLE_DELETED'				=> 'Deleted all message in table',
	'ACP_PUSHMESSAGE_ENTRY_DELETED'				=> 'Deleted push message',
	'ACP_PUSHMESSAGE_REALLY_DELETE'				=> 'Really delete message?',
	'ACP_PUSHMESSAGE_ENABLE'					=> 'Enable Push Message',
	'ACP_PUSHMESSAGE_ENABLE_EXPLAIN'			=> 'Enable global on/off Push Message.',
	'ACP_PUSHMESSAGE_POPUP'						=> 'Pop up message',
	'ACP_PUSHMESSAGE_POPUP_EXPLAIN'				=> 'Pop up the message field.',
	'ACP_PUSHMESSAGE_TABLE_TITLE'				=> 'All messages',
	'ACP_PUSHMESSAGE_NO_TITLE'					=> 'No messages yet',
	'ACP_PUSHMESSAGE_NOACCESS'					=> 'No access',
	'ACP_PUSHMESSAGE_SENDER_USERNAME'			=> 'Sender',
	'ACP_PUSHMESSAGE_RECEIVER_USERNAME'			=> 'Receiver',
	'ACP_PUSHMESSAGE_MESSAGE'					=> 'Message',
	'ACP_PUSHMESSAGE_DATE'						=> 'Date',
	'ACP_PUSHMESSAGE_ACTION'					=> 'Action',
	'ACP_PUSHMESSAGE_DELETE_LABEL' 				=> 'I confirm that I want to delete all entries',
	'ACP_PUSHMESSAGE_CONFIRM_DELETE_PROMPT' 	=> 'Please confirm that you want to delete all entries by checking the box.',
	'ACP_PUSHMESSAGE_MESSAGE_UPC_STAY' 			=> 'This only empties the table, notification messages will stay in UPC',
	'ACP_PUSHMESSAGE_PAGINATION' 				=> 'Pagination',
	'ACP_PUSHMESSAGE_PAGINATION_EXPLAIN' 		=> 'Set pagination value.',
	'ACP_PUSHMESSAGE_SEARCH_SENDER' 			=> 'Search sender',
	'ACP_PUSHMESSAGE_SEARCH_RECEIVER' 			=> 'Search receiver',
	'ACP_PUSHMESSAGE_SEARCH_MESSAGE' 			=> 'Search message',
	'ACP_PUSHMESSAGE_SEARCH_DATE' 				=> 'Search date',
	'ACP_PUSHMESSAGE_NO_RESULTS' 				=> 'No search results',
	'ACP_PUSHMESSAGE_MESSAGES'		=>	[
		1 => '%s message',
		2 => '%s messages',
	],
]);
