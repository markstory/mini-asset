<?php
/**
 * MiniAsset
 * Copyright (c) Mark Story (http://mark-story.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Mark Story (http://mark-story.com)
 * @since         1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Test\TestCase;

use MiniAsset\AssetConfig;
use MiniAsset\Middleware\AssetMiddleware;
use PHPUnit_Framework_TestCase;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

class AssetMiddlewareTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $configFile = APP . 'config/integration.ini';
        $this->config = AssetConfig::buildFromIniFile($configFile);
        $this->middleware = new AssetMiddleware(
            $this->config,
            sys_get_temp_dir() . DIRECTORY_SEPARATOR,
            '/assets/'
        );
    }

    public function testInvokeIncorrectPrefix()
    {
        $request = ServerRequestFactory::fromGlobals([
            'REQUEST_URI' => '/wrong/assets/path'
        ]);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertSame($res, $response);
    }

    public function testInvokeMissingAssetFile()
    {
        $request = ServerRequestFactory::fromGlobals([
            'REQUEST_URI' => '/assets/nope.js'
        ]);
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
        $this->config->addTarget('invalid.css', [
            'files' => [APP . 'invalid.css']
        ]);

        $request = ServerRequestFactory::fromGlobals([
            'REQUEST_URI' => '/assets/invalid.css'
        ]);
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
        $request = ServerRequestFactory::fromGlobals([
            'REQUEST_URI' => '/assets/all.css'
        ]);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertNotSame($res, $response, 'Should be a new response');
        $this->assertSame(200, $res->getStatusCode(), 'Is 200 on success');
        $this->assertSame('application/css', $res->getHeaderLine('Content-Type'));
        $this->assertContains('cached data', '' . $res->getBody(), 'Is cached data.');

        unlink(sys_get_temp_dir() . '/all.css');
    }

    public function testInvokeSuccessfulBuild()
    {
        $request = ServerRequestFactory::fromGlobals([
            'REQUEST_URI' => '/assets/all.css'
        ]);
        $response = new Response();
        $next = function ($req, $res) {
            return $res;
        };
        $res = $this->middleware->__invoke($request, $response, $next);
        $this->assertNotSame($res, $response, 'Should be a new response');
        $this->assertSame(200, $res->getStatusCode(), 'Is 200 on success');
        $this->assertContains('#nav {', '' . $res->getBody(), 'Looks like CSS.');
    }
}
