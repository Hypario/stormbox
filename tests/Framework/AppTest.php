<?php

namespace Tests\Framework;

use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class AppTest extends TestCase
{

    /**
     * @var App
     */
    private $app;

    protected function setUp(): void
    {
        $this->app = new App();
    }

    public function testApp()
    {
        // test that it pipe middlewares
        $this->app->pipe(get_class($this));
        $this->assertEquals([get_class($this)], $this->app->getMiddlewares());
    }

    public function testModuleContainer()
    {
        $app = (new App())->addModule(ModuleTest::class);

        $this->assertEquals(2, $app->getContainer()->get('a'));
    }

    public function testAppWithArrayDefinition()
    {
        $app = new App(['a' => 2]);
        $this->assertEquals(2, $app->getContainer()->get('a'));
    }

    public function testPipe()
    {
        // creates the false needed object
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $middleware2 = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();

        // what they want them to do
        $middleware->expects($this->once())->method('process')->willReturn($response);
        $middleware2->expects($this->never())->method('process')->willReturn($response);

        $this->assertEquals($response, $this->app->pipe($middleware)->handle($request));
    }

    public function testPipeWithClosure()
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $middleware->expects($this->once())->method('process')->willReturn($response);

        $this->app
            ->pipe(function ($request, $next) { return $next($request); })
            ->pipe($middleware);
        $this->assertEquals($response, $this->app->handle($request));
    }

    /**
     * The application should throw an exception whenever no
     * middleware has been found
     */
    public function testPipeWithoutMiddleware()
    {
        $this->expectException(\Exception::class);
        $this->app->handle($this->getMockBuilder(ServerRequestInterface::class)->getMock());
    }

    public function testPipeWithPrefix()
    {
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $middleware->expects($this->once())->method('process')->willReturn($response);

        $this->app->pipe($middleware, '/demo');
        $this->assertEquals($response, $this->app->handle(new ServerRequest('GET', '/demo')));
    }

    public function testAddModule()
    {
        // create an application with a module
        $app = (new App())->addModule(ModuleTest::class);

        // create the needed objects
        $middleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $middleware->method('process')->willReturn($response);

        // pipe a middleware to avoid an exception
        $app->pipe($middleware);

        // test that it add modules
        $this->assertEquals([ModuleTest::class], $app->getModules());
        // it tests that the module is instantiated and the app run smoothly
        $this->assertEquals($response, $app->handle($request));
    }

}
