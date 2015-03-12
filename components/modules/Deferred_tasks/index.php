<?php
/**
 * @package   Deferred tasks
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Deferred_tasks;
use
	cs\Config,
	cs\Route;
$Config      = Config::instance();
$module_data = $Config->module('Deferred_tasks');
$rc          = Route::instance()->route;
if ($module_data->security_key !== $rc[0]) {
	error_code(400);
	return;
}
Deferred_tasks::instance()->run(
	isset($rc[1]) ? $rc[1] : false
);
