<?php

namespace App;

use Silex\Application;

class RoutesLoader
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->instantiateControllers();
    }

    private function instantiateControllers()
    {
        $this->app['lists.controller'] = $this->app->share(function () {
            return new Controllers\ListsController($this->app['lists.service']);
        });
    }

    public function bindRoutesToControllers()
    {
        $api = $this->app['controllers_factory'];

        $api->get('/lists', 'lists.controller:getAll');
        $api->post('/lists/{id}', 'lists.controller:save');
        $api->put('/lists/{id}', 'lists.controller:update');
        $api->delete('/lists/{id}', 'lists.controller:delete');

        $this->app->mount($this->app['api.endpoint'].'/'.$this->app['api.version'], $api);
    }
}
