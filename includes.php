<?php declare(strict_types=1);

include_once('vendor/psr/http-message/src/UriInterface.php');
include_once('vendor/psr/http-message/src/MessageInterface.php');
include_once('vendor/psr/http-message/src/RequestInterface.php');
include_once('vendor/psr/http-message/src/StreamInterface.php');
include_once('vendor/psr/http-message/src/ResponseInterface.php');

include_once('vendor/guzzlehttp/guzzle/src/Handler/StreamHandler.php');
include_once('vendor/guzzlehttp/guzzle/src/Handler/CurlHandler.php');
include_once('vendor/guzzlehttp/guzzle/src/Handler/CurlFactoryInterface.php');
include_once('vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php');
include_once('vendor/guzzlehttp/guzzle/src/Handler/CurlMultiHandler.php');
include_once('vendor/guzzlehttp/guzzle/src/Handler/Proxy.php');
include_once('vendor/guzzlehttp/guzzle/src/Handler/EasyHandle.php');

include_once('vendor/guzzlehttp/guzzle/src/functions.php');
include_once('vendor/guzzlehttp/guzzle/src/PrepareBodyMiddleware.php');
include_once('vendor/guzzlehttp/guzzle/src/RequestOptions.php');
include_once('vendor/guzzlehttp/guzzle/src/RedirectMiddleware.php');
include_once('vendor/guzzlehttp/guzzle/src/Middleware.php');
include_once('vendor/guzzlehttp/guzzle/src/ClientInterface.php');
include_once('vendor/guzzlehttp/guzzle/src/Client.php');
include_once('vendor/guzzlehttp/guzzle/src/HandlerStack.php');

include_once('vendor/guzzlehttp/psr7/src/functions.php');
include_once('vendor/guzzlehttp/psr7/src/Stream.php');
include_once('vendor/guzzlehttp/psr7/src/MessageTrait.php');
include_once('vendor/guzzlehttp/psr7/src/Request.php');
include_once('vendor/guzzlehttp/psr7/src/Uri.php');
include_once('vendor/guzzlehttp/psr7/src/UriResolver.php');
include_once('vendor/guzzlehttp/psr7/src/Response.php');

include_once('vendor/guzzlehttp/promises/src/functions.php');
include_once('vendor/guzzlehttp/promises/src/PromiseInterface.php');
include_once('vendor/guzzlehttp/promises/src/Promise.php');
include_once('vendor/guzzlehttp/promises/src/TaskQueueInterface.php');
include_once('vendor/guzzlehttp/promises/src/TaskQueue.php');
include_once('vendor/guzzlehttp/promises/src/FulfilledPromise.php');

include_once('vendor/google/auth/src/HttpHandler/Guzzle6HttpHandler.php');
include_once('vendor/google/auth/src/HttpHandler/HttpHandlerFactory.php');

include_once('vendor/google/auth/src/FetchAuthTokenInterface.php');
include_once('vendor/google/auth/src/OAuth2.php');

include_once('vendor/google/apiclient/src/Google/AccessToken/Revoke.php');

include_once('vendor/google/apiclient/src/Google/Client.php');
include_once('vendor/google/apiclient/src/Google/Service.php');

include_once('vendor/google/apiclient-services/src/Google/Service/Drive.php');
