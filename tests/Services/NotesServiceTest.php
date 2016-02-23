<?php

namespace Tests\Services;

use App\Services\NotesService;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;

class NotesServiceTest extends \PHPUnit_Framework_TestCase
{
    private $noteService;

    public function setUp()
    {
        $app = new Application();
        $app->register(new DoctrineServiceProvider(), [
            'db.options' => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
        ]);
        $this->noteService = new NotesService($app['db']);

        $stmt = $app['db']->prepare('CREATE TABLE notes (id INTEGER PRIMARY KEY AUTOINCREMENT,note VARCHAR NOT NULL)');
        $stmt->execute();
    }

    public function testGetAll()
    {
        $data = $this->noteService->getAll();
        $this->assertNotNull($data);
    }

    public function testSave()
    {
        $note = ['note' => 'arny'];
        $data = $this->noteService->save($note);
        $data = $this->noteService->getAll();
        $this->assertEquals(1, count($data));
    }

    public function testUpdate()
    {
        $note = ['note' => 'arny1'];
        $this->noteService->save($note);
        $note = ['note' => 'arny2'];
        $this->noteService->update(1, $note);
        $data = $this->noteService->getAll();
        $this->assertEquals('arny2', $data[0]['note']);
    }

    public function testDelete()
    {
        $note = ['note' => 'arny1'];
        $this->noteService->save($note);
        $this->noteService->delete(1);
        $data = $this->noteService->getAll();
        $this->assertEquals(0, count($data));
    }
}
