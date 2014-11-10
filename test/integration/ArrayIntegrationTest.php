<?php
namespace Graze\Queue;

use Graze\Queue\Adapter\ArrayAdapter;
use Graze\Queue\Message\MessageFactory;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class ArrayIntegrationTest extends TestCase
{
    public function setUp()
    {
        $factory = new MessageFactory();

        $this->messages = [
            $factory->createMessage('foo'),
            $factory->createMessage('bar'),
            $factory->createMessage('baz')
        ];

        $this->client = new Client(new ArrayAdapter($this->messages));
    }

    public function testReceive()
    {
        $msgs = [];
        $this->client->receive(function ($msg) use (&$msgs) {
            $msgs[] = $msg;
        }, 100);

        $this->assertEquals($this->messages, $msgs);
    }

    public function testSend()
    {
        $this->client->send([$this->client->create('foo')]);
    }
}
