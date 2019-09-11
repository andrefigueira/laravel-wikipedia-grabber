<?php

namespace Illuminated\Wikipedia\Tests;

use GuzzleHttp\Client;
use Illuminated\Wikipedia\Wikipedia;

class GrabberTest extends TestCase
{
    /** @test */
    public function it_has_get_client_method()
    {
        $client = (new Wikipedia)->getClient();

        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function client_has_default_user_agent_logic()
    {
        config([
            'wikipedia-grabber.user_agent' => null,
            'app.name' => 'Laravel Wikipedia Grabber',
            'app.url' => 'https://github.com/dmitry-ivanov/laravel-wikipedia-grabber',
        ]);

        $client = (new Wikipedia)->getClient();

        $this->assertEquals(
            'Laravel Wikipedia Grabber (https://github.com/dmitry-ivanov/laravel-wikipedia-grabber)',
            $client->getConfig('headers')['User-Agent']
        );
    }

    /** @test */
    public function and_it_takes_specified_user_agent_if_set()
    {
        $client = (new Wikipedia)->getClient();

        $this->assertEquals(
            'Laravel Wikipedia Grabber (https://github.com/dmitry-ivanov/laravel-wikipedia-grabber; dmitry.g.ivanov@gmail.com)',
            $client->getConfig('headers')['User-Agent']
        );
    }
}
