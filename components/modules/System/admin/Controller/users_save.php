<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\User;

trait users_save {
	static function users_users_save () {
		if (!isset($_POST['mode'])) {
			return;
		}
		$Config = Config::instance();
		$Index  = Index::instance();
		$L      = Language::instance();
		$Page   = Page::instance();
		$User   = User::instance();
		switch ($_POST['mode']) {
			case 'edit_raw':
				$id = (int)$_POST['user']['id'];
				if (
					$id != User::GUEST_ID &&
					$id != User::ROOT_ID &&
					!in_array(User::BOT_GROUP_ID, (array)$User->get_groups($id))
				) {
					$User->set($_POST['user'], null, $id);
					$User->__finish();
					$Index->save(true);
				}
				break;
			case 'edit':
				if (isset($_POST['user'])) {
					$id = (int)$_POST['user']['id'];
					if ($id == User::GUEST_ID || $id == User::ROOT_ID) {
						break;
					}
					$user_data = &$_POST['user'];
					$columns   = [
						'id',
						'login',
						'username',
						'password',
						'email',
						'language',
						'timezone',
						'status',
						'block_until',
						'avatar'
					];
					foreach ($user_data as $item => &$value) {
						if ($item != 'data' && in_array($item, $columns)) {
							$value = xap($value, false);
						} elseif ($item != 'data') {
							unset($user_data[$item]);
						}
					}
					unset($item, $value, $columns);
					if ($_POST['user']['block_until'] > TIME) {
						$block_until              = $user_data['block_until'];
						$block_until              = explode('T', $block_until);
						$block_until[0]           = explode('-', $block_until[0]);
						$block_until[1]           = explode(':', $block_until[1]);
						$user_data['block_until'] = mktime(
							$block_until[1][0],
							$block_until[1][1],
							0,
							$block_until[0][1],
							$block_until[0][2],
							$block_until[0][0]
						);
						unset($block_until);
					} else {
						$user_data['block_until'] = 0;
					}
					if ($user_data['password']) {
						if (strlen($user_data['password']) < $Config->core['password_min_length']) {
							$Page->warning($L->password_too_short);
						} elseif (password_check($user_data['password'], $Config->core['password_min_length']) < $Config->core['password_min_strength']) {
							$Page->warning($L->password_too_easy);
						} else {
							$User->set_password($user_data['password'], $id);
						}
					}
					unset($user_data['password']);
					if (
						$user_data['login'] &&
						$user_data['login'] != $User->get('login', $id) &&
						(
							(
								!filter_var($user_data['login'], FILTER_VALIDATE_EMAIL) &&
								$User->get_id(hash('sha224', $user_data['login'])) === false
							) ||
							$user_data['login'] == $user_data['email']
						)
					) {
						$user_data['login_hash'] = hash('sha224', $user_data['login']);
					} else {
						if ($user_data['login'] != $User->get('login', $id)) {
							$Page->warning($L->login_occupied_or_is_not_valid);
						}
						unset($user_data['login']);
					}
					if ($user_data['email']) {
						$user_data['email_hash'] = hash('sha224', $user_data['email']);
					} else {
						unset($user_data['login']);
					}
					$result = $User->set($user_data, '', $id);
					$User->__finish();
					$Index->save($result);
				} elseif (isset($_POST['bot'])) {
					$result = $User->set_bot($_POST['bot']['id'], $_POST['bot']['name'], $_POST['bot']['user_agent'], $_POST['bot']['ip']);
					$User->__finish();
					$Index->save($result);
				}
				break;
			case 'deactivate':
				if (isset($_POST['id'])) {
					$id = (int)$_POST['id'];
					if ($id != User::GUEST_ID && $id != User::ROOT_ID) {
						$User->set('status', User::STATUS_INACTIVE, $id);
						$Index->save(true);
					}
				}
				break;
			case 'activate':
				if (isset($_POST['id'])) {
					$id = (int)$_POST['id'];
					if ($id != User::GUEST_ID && $id != User::ROOT_ID) {
						$User->set('status', User::STATUS_ACTIVE, $id);
						$Index->save(true);
					}
				}
				break;
			case 'groups':
				if (isset($_POST['user'], $_POST['user']['id'], $_POST['user']['groups']) && $_POST['user']['groups']) {
					$user_id = (int)$_POST['user']['id'];
					if ($_POST['user']['id'] == User::ROOT_ID || in_array(User::BOT_GROUP_ID, (array)$User->get_groups($user_id))) {
						break;
					}
					$groups = _json_decode($_POST['user']['groups']);
					$Index->save(
						$User->set_groups($groups, $user_id)
					);
				}
				break;
		}
	}
}
