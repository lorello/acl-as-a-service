<?php

namespace App;

use Silex\Application;

class ServicesLoader
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bindServicesIntoContainer()
    {
        $this->app['lists.service'] = $this->app->share(function () {
            return new Services\ListsService($this->app['db']);
        });
    }
}
