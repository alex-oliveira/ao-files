<?php

namespace AoFiles\Utils\Tools;

use AoScrud\Utils\Tools\RouterGerator;
use AoScrud\Utils\Traits\BuildTrait;

class Router extends RouterGerator
{

    use BuildTrait;

    protected $configs = [
        'prefix' => 'files',
        'as' => 'files.',
    ];

    protected $routes = [
        ['method' => 'get',    'url' => '/',              'configs' => ['as' => 'index',    'uses' => '@index']],
        ['method' => 'get',    'url' => '/{id}',          'configs' => ['as' => 'show',     'uses' => '@show']],
        ['method' => 'post',   'url' => '/',              'configs' => ['as' => 'store',    'uses' => '@store']],
        ['method' => 'put',    'url' => '/{id}',          'configs' => ['as' => 'update',   'uses' => '@update']],
        ['method' => 'put',    'url' => '/{id}/restore',  'configs' => ['as' => 'restore',  'uses' => '@restore']],
        ['method' => 'delete', 'url' => '/{id}',          'configs' => ['as' => 'destroy',  'uses' => '@destroy']],
        ['method' => 'get',    'url' => '/{id}/download', 'configs' => ['as' => 'download', 'uses' => '@download']],
    ];

    public function foreign($foreign)
    {
        $this->configs['prefix'] = '{' . $foreign . '}/files';
        return $this;
    }

}