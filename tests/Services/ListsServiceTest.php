<?php

namespace Tests\Services;

use App\Services\ListsService;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;

class ListsServiceTest extends \PHPUnit_Framework_TestCase
{
    private $listService;

    public function setUp()
    {
        $app = new Application();
        $app->register(new DoctrineServiceProvider(), [
            'db.options' => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
        ]);
        $this->listService = new ListsService($app['db']);

        $stmt = $app['db']->prepare('CREATE TABLE lists (id INTEGER PRIMARY KEY AUTOINCREMENT, list VARCHAR NOT NULL)');
        $stmt->execute();
    }

    public function testGetAll()
    {
        $data = $this->listService->getAll();
        $this->assertNotNull($data);
    }

    public function testSave()
    {
        $list = ['list' => 'lorello'];
        $data = $this->listService->save($list);
        $data = $this->listService->getAll();
        $this->assertEquals(1, count($data));
    }

    public function testUpdate()
    {
        $list = ['list' => 'lorello1'];
        $this->listService->save($list);
        $list = ['list' => 'lorello2'];
        $this->listService->update(1, $list);
        $data = $this->listService->getAll();
        $this->assertEquals('lorello2', $data[0]['list']);
    }

    public function testDelete()
    {
        $list = ['list' => 'lorello1'];
        $this->listService->save($list);
        $this->listService->delete(1);
        $data = $this->listService->getAll();
        $this->assertEquals(0, count($data));
    }
}
