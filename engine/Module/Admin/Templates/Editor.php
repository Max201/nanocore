<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin\Templates;


use Symfony\Component\HttpFoundation\Request;


/**
 * Class Editor
 * @package Module\Admin\Templates
 */
trait Editor
{
    public function editor_files(Request $request, $matches)
    {
        return $this->view->render('com/editor/list.twig', [
            'title'     => $this->lang->translate('admin.templates'),
            'templates' => $this->editor_get_templates_list()
        ]);
    }

    public function editor_file(Request $request, $matches)
    {
        $filename = $matches->get('tpl');
        $filepath = $this->editor_get_templates_dir($filename);
        $metafile = $this->editor_get_templates_dir('.' . $filename);

        if ( $request->isMethod('post') ) {
            $content = $request->get('content');
            if ( file_put_contents($filepath, $content) ) {
                return static::json_response([
                    'message'  => $this->lang->translate('form.saved')
                ]);
            } else {
                return static::json_response([
                    'message'  => $this->lang->translate('form.failed')
                ]);
            }
        }

        $context = [
            'title'     => $this->lang->translate('admin.templates'),
            'filename'  => $filename,
            'content'   => file_get_contents($filepath),
            'files'     => $this->editor_get_templates_list()
        ];


        if ( file_exists($metafile) ) {
            $context['meta'] = '@assets/templates/.' . $filename;
        }

        return $this->view->render('com/editor/edit.twig', $context);
    }

    /**
     * @return array
     */
    private function editor_get_templates_list()
    {
        $dir_handler = opendir($this->editor_get_templates_dir());
        $items = [];
        while ( $item = readdir($dir_handler) ) {
            if ( $item[0] == '.' || strtolower(end(explode('.', $item))) != 'twig' ) continue;
            $items[] = [
                'path'  => $this->editor_get_templates_dir($item),
                'name'  => $item
            ];
        }

        return $items;
    }

    /**
     * @return string
     */
    private function editor_get_templates_dir()
    {
        return ROOT . S . 'theme' . S . 'assets' . S . 'templates' . (
            count(func_get_args()) ? S . implode(S, func_get_args()) : ''
        );
    }
} 