<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\CDNRepository;


use System\Engine\NCService;


/**
 * Class CDNRepository
 * @package Service\CDNRepository
 */
class CDNRepository extends NCService
{
    /**
     * Mark for latest version
     */
    const VERSION_LATEST = null;

    /**
     * @var string
     */
    static $local_repository = 'static';

    /**
     * @var string
     */
    private $repository = ['info'=>[], 'files'=>[]];

    /**
     * @var Repository
     */
    private $loader;

    /**
     * @param $name
     * @param null $version
     */
    public function __construct($name, $version)
    {
        $this->loader = new Repository();

        $this->info('version', $version);
        $this->info('name', $name);

        $this->load_version($version);
    }

    public function render(array $files = [])
    {
        if ( is_null($files) ) {
            $files = $this->getArrayCopy();
        }

        $html_file = '';
        foreach ( $this as $file => $path ) {
            if ( array_key_exists($file, $files) ) {
                $ext = strtolower(end(explode('.', $file)));
                switch ($ext) {
                    case 'js':
                        $html_file .= '<script type="text/javascript" charset="UTF-8" src="' . $path . '"></script>';
                        break;
                    case 'css':
                        $html_file .= '<link rel="stylesheet" type="text/css" href="' . $path . '" />';
                        break;
                    default;
                }
            }
        }

        return $html_file;
    }

    /**
     * @param $version
     * @return bool
     */
    public function load_version($version)
    {
        $repository_local = static::$local_repository . DIRECTORY_SEPARATOR . $this->info('name') . DIRECTORY_SEPARATOR . $version . '.json';
        if ( file_exists($repository_local) ) {
            $this->repository = json_decode(file_get_contents($repository_local), true);
            $this->exchangeArray($this->repository['files']);
            return true;
        }

        $this->repository = $this->loader->get_reposiroty($this->info('name'), $version);
        $this->info('version', $version);
        $this->exchangeArray($this->repository['files']);
        return $this->save();
    }

    /**
     * @param $key
     * @param null $value
     * @return null
     */
    public function info($key, $value = null)
    {
        if ( !is_null($value) ) {
            $this->repository['info'][$key] = $value;
        }

        if ( array_key_exists($key, $this->repository['info']) ) {
            return $this->repository['info'][$key];
        }

        return null;
    }

    /**
     * @param $file
     * @param null $path
     * @return null
     */
    public function file($file, $path = null)
    {
        if ( !is_null($path) ) {
            $this->repository['files'][$file] = $path;
        }

        if ( array_key_exists($file, $this->repository['files']) ) {
            return $this->repository['files'][$file];
        }

        return null;
    }

    /**
     * @return bool
     */
    private function save()
    {
        $dir_path = static::$local_repository . DIRECTORY_SEPARATOR . $this->info('name');
        # Create repo dir
        if ( !file_exists($dir_path) ) {
            @mkdir($dir_path, 0777, true);
        }

        $data = json_encode($this->repository, JSON_PRETTY_PRINT);
        return (bool)file_put_contents($dir_path . DIRECTORY_SEPARATOR . $this->info('version') . '.json', $data);
    }
}