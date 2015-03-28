<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	h,
	Phar;

class Builder {
	/**
	 * @var string
	 */
	protected $target;
	/**
	 * @param string $target
	 */
	function __construct ($target) {
		$this->target = $target;
	}
	/**
	 * @return string
	 */
	function form () {
		return h::{'form[method=post]'}(
			h::nav(
				'Build: '.
				h::{'radio.build-mode[name=mode]'}(
					[
						'value'   => ['core', 'module', 'plugin', 'theme'],
						'in'      => ['Core', 'Module', 'Plugin', 'Theme'],
						'onclick' => 'change_mode(this.value, this);'
					]
				)
			).
			h::{'table tr| td'}(
				[
					'Modules',
					'Plugins',
					'Themes'
				],
				[
					h::{'select#modules[name=modules[]][size=20][multiple] option'}(
						array_map(
							function ($module) {
								return [
									$module,
									file_exists(DIR."/components/modules/$module/meta.json") ? [
										'title' => 'Version: '.file_get_json(DIR."/components/modules/$module/meta.json")['version']
									] : [
										'title' => 'No meta.json file found',
										'disabled'
									]
								];
							},
							get_files_list(DIR.'/components/modules', '/[^System)]/', 'd')
						)
					),
					h::{'select#plugins[name=plugins[]][size=20][multiple] option'}(
						array_map(
							function ($plugin) {
								return [
									$plugin,
									file_exists(DIR."/components/plugins/$plugin/meta.json") ? [
										'title' => 'Version: '.file_get_json(DIR."/components/plugins/$plugin/meta.json")['version']
									] : [
										'title' => 'No meta.json file found',
										'disabled'
									]
								];
							},
							get_files_list(DIR.'/components/plugins', false, 'd')
						)
					),
					h::{'select#themes[name=themes[]][size=20][multiple] option'}(
						array_map(
							function ($theme) {
								return [
									$theme,
									file_exists(DIR."/themes/$theme/meta.json") ? [
										'title' => 'Version: '.file_get_json(DIR."/themes/$theme/meta.json")['version']
									] : [
										'title' => 'No meta.json file found',
										'disabled'
									]
								];
							},
							get_files_list(DIR.'/themes', '/[^CleverStyle)]/', 'd')
						)
					)
				]
			).
			h::{'input[name=suffix]'}(
				[
					'placeholder' => 'Package file suffix'
				]
			).
			h::{'button.uk-button.license'}(
				'License',
				[
					'onclick' => "window.open('license.txt', 'license', 'location=no')"
				]
			).
			h::{'button.uk-button[type=submit]'}(
				'Build'
			)
		);
	}
	/**
	 * @return string
	 */
	function core () {
		$version = file_get_json(DIR.'/components/modules/System/meta.json')['version'];
		if (file_exists(DIR.'/build.phar')) {
			unlink(DIR.'/build.phar');
		}
		$phar   = new Phar(DIR.'/build.phar');
		$length = mb_strlen(DIR.'/');
		foreach (get_files_list(DIR.'/install', false, 'f', true, true) as $file) {
			$phar->addFile($file, mb_substr($file, $length));
		}
		unset($file);
		/**
		 * Files to be included into installation package
		 */
		$files = $this->get_files(
			[
				DIR.'/components/modules/System',
				DIR.'/core',
				DIR.'/custom',
				DIR.'/includes',
				DIR.'/templates',
				DIR.'/themes/CleverStyle',
				DIR.'/components/blocks/.gitkept',
				DIR.'/components/plugins/.gitkept',
				DIR.'/index.php',
				DIR.'/license.txt',
				DIR.'/Storage.php',
				DIR.'/composer.json',
				DIR.'/composer.lock'
			]
		);
		/**
		 * Add selected modules that should be built-in into package
		 */
		$components_files = [];
		if (@$_POST['modules']) {
			$modules = [];
			foreach ($_POST['modules'] as $module) {
				if ($this->get_component_files(DIR."/components/modules/$module", $components_files)) {
					$modules[] = $module;
				}
			}
			unset($module);
			$phar->addFromString('modules.json', _json_encode($modules));
		}
		/**
		 * Add selected plugins that should be built-in into package
		 */
		if (@$_POST['plugins']) {
			$plugins = [];
			foreach ($_POST['plugins'] as $plugin) {
				if ($this->get_component_files(DIR."/components/plugins/$plugin", $components_files)) {
					$plugins[] = $plugin;
				}
			}
			unset($plugin);
			$phar->addFromString('plugins.json', _json_encode($plugins));
		}
		/**
		 * Add selected themes that should be built-in into package
		 */
		if (@$_POST['themes']) {
			$themes = [];
			foreach ($_POST['themes'] as $theme) {
				if ($this->get_component_files(DIR."/components/themes/$theme", $components_files)) {
					$themes[] = $theme;
				}
			}
			unset($theme);
			$phar->addFromString('themes.json', _json_encode($themes));
		}
		/**
		 * Joining system and components files
		 */
		$files = array_merge(
			$files,
			$components_files
		);
		/**
		 * Addition files content into package
		 */
		$files = array_map(
			function ($index, $file) use ($phar, $length) {
				$phar->addFromString("fs/$index", file_get_contents($file));
				return substr($file, $length);
			},
			array_keys($files),
			$files
		);
		/**
		 * Addition of separate files into package
		 */
		$files[] = 'readme.html';
		$phar->addFromString(
			'fs/'.(count($files) - 1),
			str_replace(
				[
					'$version$',
					'$image$'
				],
				[
					$version,
					h::img(
						[
							'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
						]
					)
				],
				file_get_contents(DIR.'/readme.html')
			)
		);
		$phar->addFromString(
			'languages.json',
			_json_encode(
				array_merge(
					_mb_substr(get_files_list(DIR.'/core/languages', '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
					_mb_substr(get_files_list(DIR.'/core/languages', '/^.*?\.json$/i', 'f'), 0, -5) ?: []
				)
			)
		);
		$phar->addFromString(
			'db_engines.json',
			_json_encode(
				_mb_substr(get_files_list(DIR.'/core/engines/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4)
			)
		);
		/**
		 * Fixing of system files list (without components files and core/fs.json file itself), it is needed for future system updating
		 */
		$files[] = 'core/fs.json';
		$phar->addFromString(
			'fs/'.(count($files) - 1),
			_json_encode(
				array_flip(array_diff(array_slice($files, 0, -1), _substr($components_files, $length)))
			)
		);
		unset($components_files, $length);
		/**
		 * Addition of files, that are needed only for installation
		 */
		$files[] = '.htaccess';
		$phar->addFromString(
			'fs/'.(count($files) - 1),
			'AddDefaultCharset utf-8
Options -Indexes -Multiviews +FollowSymLinks
IndexIgnore *.php *.pl *.cgi *.htaccess *.htpasswd

RewriteEngine On
RewriteBase /

<FilesMatch ".*/.*">
	Options -FollowSymLinks
</FilesMatch>
<FilesMatch "\.(css|js|gif|jpg|jpeg|png|ico|eot|ttc|ttf|svg|svgz|woff)$">
	RewriteEngine Off
</FilesMatch>
<Files license.txt>
	RewriteEngine Off
</Files>
#<Files Storage.php>
#	RewriteEngine Off
#</Files>

RewriteRule .* index.php
'
		);
		$files[] = 'config/main.php';
		$phar->addFromString(
			'fs/'.(count($files) - 1),
			file_get_contents(DIR.'/config/main.php')
		);
		$files[] = 'favicon.ico';
		$phar->addFromString(
			'fs/'.(count($files) - 1),
			file_get_contents(DIR.'/favicon.ico')
		);
		$files[] = '.gitignore';
		$phar->addFromString(
			'fs/'.(count($files) - 1),
			file_get_contents(DIR.'/.gitignore')
		);
		/**
		 * Flip array to have direct access to files by name during extracting and installation, and fixing of files list for installation
		 */
		$phar->addFromString(
			'fs.json',
			_json_encode(
				array_flip($files)
			)
		);
		unset($files);
		/**
		 * Addition of supplementary files, that are needed directly for installation process: installer with GUI interface, readme, license, some additional
		 * information about available languages, themes, current version of system
		 */
		$phar->addFromString(
			'install.php',
			str_replace('$version$', $version, file_get_contents(DIR.'/install.php'))
		);
		$phar->addFromString(
			'readme.html',
			str_replace(
				[
					'$version$',
					'$image$'
				],
				[
					$version,
					h::img(
						[
							'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(DIR.'/install/logo.png'))
						]
					)
				],
				file_get_contents(DIR.'/readme.html')
			)
		);
		$phar->addFromString(
			'license.txt',
			file_get_contents(DIR.'/license.txt')
		);
		$themes = get_files_list(DIR.'/themes', false, 'd');
		asort($themes);
		$phar->addFromString(
			'themes.json',
			_json_encode($themes)
		);
		$phar->addFromString(
			'version',
			"\"$version\""
		);
		unset($themes, $theme);
		$phar->setStub(
			"<?php
		if (PHP_SAPI == 'cli') {
			Phar::mapPhar('cleverstyle_cms.phar');
			include 'phar://cleverstyle_cms.phar/install.php';
		} else {
			Phar::webPhar(null, 'install.php');
		}
		__HALT_COMPILER();"
		);
		unset($phar);
		$suffix = @$_POST['suffix'] ? "_$_POST[suffix]" : '';
		rename(DIR.'/build.phar', DIR."/CleverStyle_CMS_$version$suffix.phar.php");
		return "Done! CleverStyle CMS $version";
	}
	/**
	 * Get array of files
	 *
	 * @param string[] $source Files and directories (absolute paths); If file does non exists - it will be skipped, if directory - all files will be returned
	 *                         instead
	 *
	 * @return array
	 */
	protected function get_files ($source) {
		$files = [];
		foreach ($source as $s) {
			if (is_file($s)) {
				$files[] = $s;
			} elseif (is_dir($s)) {
				/** @noinspection SlowArrayOperationsInLoopInspection */
				$files = array_merge(
					$files,
					get_files_list($s, false, 'f', true, true, false, false, true)
				);
			}
		}
		return $files;
	}
	/**
	 * @param string   $component_root
	 * @param string[] $files Array, where new files will be appended
	 *
	 * @return bool
	 */
	protected function get_component_files ($component_root, &$files) {
		/**
		 * Do not allow building System module and CleverStyle theme
		 */
		if (in_array(basename($component_root), ['System', 'CleverStyle'])) {
			return false;
		}
		/**
		 * Components without meta.json also not allowed
		 */
		if (!file_exists("$component_root/fs.json")) {
			return false;
		}
		@unlink("$component_root/fs.json");
		$files = array_merge(
			$files,
			get_files_list($component_root, false, 'f', true, true, false, false, true)
		);
		file_put_json(
			"$component_root/fs.json",
			array_values(
				_mb_substr(
					$files,
					mb_strlen("$component_root/")
				)
			)
		);
		$files[] = "$component_root/fs.json";
		return true;
	}
	/**
	 * @param string $module
	 *
	 * @return string
	 */
	function module ($module) {
		$module = $module ?: $_POST['modules'][0];
		if ($module == 'System') {
			return "Can't build module, System module is a part of core, it is not necessary to build it as separate module";
		}
		return $this->generic_package_creation(DIR."/components/modules/$module", @$_POST['suffix']);
	}
	/**
	 * @param string $plugin
	 *
	 * @return string
	 */
	function plugin ($plugin) {
		$plugin = $plugin ?: $_POST['plugins'][0];
		return $this->generic_package_creation(DIR."/components/plugins/$plugin", @$_POST['suffix']);
	}
	/**
	 * @param string $theme
	 *
	 * @return string
	 */
	function theme ($theme) {
		$theme = $theme ?: $_POST['themes'][0];
		if ($theme == 'CleverStyle') {
			return "Can't build theme, CleverStyle theme is a part of core, it is not necessary to build it as separate theme";
		}
		return $this->generic_package_creation(DIR."/themes/$theme", @$_POST['suffix']);
	}
	protected function generic_package_creation ($source_dir, $suffix = null) {
		if (file_exists("$this->target/build.phar")) {
			unlink("$this->target/build.phar");
		}
		if (!file_exists("$source_dir/meta.json")) {
			$component = basename($source_dir);
			return "Can't build $component, meta information (meta.json) not found";
		}
		$meta   = file_get_json("$source_dir/meta.json");
		$phar   = new Phar("$this->target/build.phar");
		$files  = get_files_list($source_dir, false, 'f', true, true, false, false, true);
		$length = mb_strlen("$source_dir/");
		$files  = array_map(
			function ($index, $file) use ($phar, $length) {
				$phar->addFromString("fs/$index", file_get_contents($file));
				return mb_substr($file, $length);
			},
			array_keys($files),
			$files
		);
		unset($length);
		/**
		 * Flip array to have direct access to files by name during extraction
		 */
		$phar->addFromString(
			'fs.json',
			_json_encode(
				array_flip($files)
			)
		);
		unset($files);
		$phar->addFromString('meta.json', _json_encode($meta));
		//TODO remove in future versions
		$phar->addFromString('dir', $meta['package']);
		$readme = false;
		if (file_exists("$source_dir/readme.html")) {
			$phar->addFromString('readme.html', file_get_contents("$source_dir/readme.html"));
			$readme = 'readme.html';
		} elseif (file_exists("$source_dir/readme.txt")) {
			$phar->addFromString('readme.txt', file_get_contents("$source_dir/readme.txt"));
			$readme = 'readme.txt';
		}
		if ($readme) {
			$phar->setStub("<?php Phar::webPhar(null, '$readme'); __HALT_COMPILER();");
		} else {
			$phar->addFromString('index.html', isset($meta['description']) ? $meta['description'] : $meta['package']);
			$phar->setStub("<?php Phar::webPhar(null, 'index.html'); __HALT_COMPILER();");
		}
		unset($readme, $phar);
		$suffix = $suffix ? "_$suffix" : '';
		$type   = 'CleverStyle_CMS';
		$Type   = 'CleverStyle CMS';
		switch ($meta['category']) {
			case 'modules':
				$type = 'module';
				$Type = 'Module';
				break;
			case 'plugins':
				$type = 'plugins';
				$Type = 'Plugin';
				break;
			case 'themes':
				$type = 'theme';
				$Type = 'Theme';
				break;
		}
		rename("$this->target/build.phar", "$this->target/{$type}_$meta[package]_$meta[version]$suffix.phar.php");
		return "Done! $Type $meta[package] $meta[version]";
	}
}
