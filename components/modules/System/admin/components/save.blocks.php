<?php
global $Config, $Index;
$a = &$Index;
$rc = &$Config->routing['current'];
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'add':
		case 'edit':
			$block_new = &$_POST['block'];
			if ($_POST['mode'] == 'add') {
				$block	= [
					'position'	=> 'floating',
					'type'		=> xap($block_new['type'])
				];
			} else {
				$block	= &$Config->components['blocks'][$block_new['id']];
			}
			$block['title']		= $block_new['title'];
			$block['active']	= $block_new['active'];
			$block['template']	= $block_new['template'];
			$block['start']		= $block_new['start'];
			$start				= &$block_new['start'];
			$start				= explode('T', $start);
			$start[0]			= explode('-', $start[0]);
			$start[1]			= explode(':', $start[1]);
			$block['start']		= mktime($start[1][0], $start[1][1], 0, $start[0][1], $start[0][2], $start[0][0]);
			unset($start);
			if ($block_new['expire']['state']) {
				$expire				= &$block_new['expire']['date'];
				$expire				= explode('T', $expire);
				$expire[0]			= explode('-', $expire[0]);
				$expire[1]			= explode(':', $expire[1]);
				$block['expire']	= mktime($expire[1][0], $expire[1][1], 0, $expire[0][1], $expire[0][2], $expire[0][0]);
				unset($expire);
			} else {
				$block['expire']	= 0;
			}
			$block_new['update']	= explode(':', $block_new['update']);
			$block['update']		= $block_new['update'][0]*60+$block_new['update'][1];
			if ($block['type'] == 'html') {
				$block['data'] = xap($block_new['html'], true);
			} elseif ($block['type'] == 'php') {
				$block['data'] = $block_new['php'];
			} else {
				$block['data'] = '';
			}
			if ($_POST['mode'] == 'add') {
				$Config->components['blocks'][] = $block;
			}
			unset($block, $block_new);
			$a->save('components');
		break;
	}
} elseif (isset($_POST['edit_settings'])) {
	switch ($_POST['edit_settings']) {
		case 'apply':
		case 'save':
			$_POST['position'] = _json_decode($_POST['position']);
			if (is_array($_POST['position'])) {
				$blocks_array = [];
				foreach ($_POST['position'] as $position => $items) {
					foreach ($items as $item) {
						$item = (int)substr($item, 5);
						switch ($position) {
							default:
								$position = 'floating';
							break;
							case 'top':
							case 'left':
							case 'floating':
							case 'right':
							case 'bottom':
							break;
						}
						$Config->components['blocks'][$item]['position']	= $position;
						$blocks_array[]										= $Config->components['blocks'][$item];
					}
				}
				$Config->components['blocks']	= [];
				$Config->components['blocks']	= $blocks_array;
				unset($blocks_array, $position, $items, $item);
				if ($_POST['edit_settings'] == 'save') {
					$a->save('components');
				} else {
					$a->apply();
				}
			}
		break;
		case 'cancel':
			$a->cancel();
		break;
	}
}