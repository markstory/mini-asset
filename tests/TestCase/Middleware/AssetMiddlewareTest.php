<?php
declare(strict_types=1);

/**
 * MiniAsset
 * Copyright (c) Mark Story (http://mark-story.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Mark Story (http://mark-story.com)
 * @since     1.1.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Test\TestCase;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use MiniAsset\AssetConfig;
use MiniAsset\Middleware\AssetMiddleware;
use PHPUnit\Framework\TestCase;

class AssetMiddlewareTest extends TestCase
{
    protected $config;
    protected $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $configFile = APP . 'config/integration.ini';
        $this->config = AssetConfig::buildFromIniFile($configFile);
        $this->middleware = new AssetMiddleware(
            $this->config,
            sys_get_temp_dir() . DIRECTORY_SEPARATOR,
            '/assets/',
        );
    }

    public function testInvokeIncorrectPrefix()
    {
        $request = ServerRequestFactory::fromGlobals(
            [
            'REQUEST_URI' => '/wrong/assets/path',
            ],
        );
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertSame($res, $response);
    }

    public function testInvokeMissingAssetFile()
    {
        $request = ServerRequestFactory::fromGlobals(
            [
            'REQUEST_URI' => '/assets/nope.js',
            ],
        );
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertSame($res, $response);
    }

    public function testInvokeFailedBuild()
    {
        // Add new invalid target.
        $this->config->addTarget(
            'invalid.css',
            [
            'files' => [APP . 'invalid.css'],
            ],
        );

        $request = ServerRequestFactory::fromGlobals(
            [
            'REQUEST_URI' => '/assets/invalid.css',
            ],
        );
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertNotSame($res, $response, 'Should be a new response');
        $this->assertSame(400, $res->getStatusCode(), 'Is 400 on failure');
        $this->assertSame('text/plain', $res->getHeaderLine('Content-Type'));
    }

    public function testInvokeCacheRead()
    {
        file_put_contents(sys_get_temp_dir() . '/all.css', 'cached data');
        $request = ServerRequestFactory::fromGlobals(
            [
            'REQUEST_URI' => '/assets/all.css',
            ],
        );
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertNotSame($res, $response, 'Should be a new response');
        $this->assertSame(200, $res->getStatusCode(), 'Is 200 on success');
        $this->assertSame('application/css', $res->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('cached data', '' . $res->getBody(), 'Is cached data.');

        unlink(sys_get_temp_dir() . '/all.css');
    }

    public function testInvokeSuccessfulBuild()
    {
        $request = ServerRequestFactory::fromGlobals(
            [
            'REQUEST_URI' => '/assets/all.css',
            ],
        );
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertNotSame($res, $response, 'Should be a new response');
        $this->assertSame(200, $res->getStatusCode(), 'Is 200 on success');
        $this->assertStringContainsString('#nav {', '' . $res->getBody(), 'Looks like CSS.');
    }

    public function contentTypesProvider()
    {
        return [
            ['/assets/libs.js', 'application/javascript'],
            ['/assets/all.css', 'application/css'],
            ['/assets/foo.bar.svg', 'image/svg+xml'],
        ];
    }

    /**
     * test returned content types
     *
     * @dataProvider contentTypesProvider
     * @return void
     */
    public function testBuildFileContentTypes($uri, $expected)
    {
        $request = ServerRequestFactory::fromGlobals(
            [
            'REQUEST_URI' => $uri,
            ],
        );

        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);

        $this->assertEquals($expected, $res->getHeaderLine('Content-Type'));
    }
}
