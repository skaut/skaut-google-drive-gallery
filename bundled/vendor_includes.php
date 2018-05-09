<?php declare(strict_types=1);

include_once('vendor/psr/http-message/src/UriInterface.php');
include_once('vendor/psr/http-message/src/MessageInterface.php');
include_once('vendor/psr/http-message/src/RequestInterface.php');
include_once('vendor/psr/http-message/src/StreamInterface.php');
include_once('vendor/psr/http-message/src/ResponseInterface.php');

include_once('vendor/psr/log/Psr/Log/LoggerInterface.php');

include_once('vendor/psr/cache/src/CacheItemPoolInterface.php');
include_once('vendor/psr/cache/src/CacheItemInterface.php');

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

include_once('vendor/monolog/monolog/src/Monolog/Handler/HandlerInterface.php');
include_once('vendor/monolog/monolog/src/Monolog/Handler/AbstractHandler.php');
include_once('vendor/monolog/monolog/src/Monolog/Handler/AbstractProcessingHandler.php');
include_once('vendor/monolog/monolog/src/Monolog/Handler/StreamHandler.php');

include_once('vendor/monolog/monolog/src/Monolog/Logger.php');

include_once('vendor/google/auth/src/Cache/MemoryCacheItemPool.php');
include_once('vendor/google/auth/src/Cache/Item.php');

include_once('vendor/google/auth/src/HttpHandler/Guzzle6HttpHandler.php');
include_once('vendor/google/auth/src/HttpHandler/HttpHandlerFactory.php');

include_once('vendor/google/auth/src/CacheTrait.php');
include_once('vendor/google/auth/src/FetchAuthTokenInterface.php');
include_once('vendor/google/auth/src/OAuth2.php');

include_once('vendor/google/auth/src/Middleware/ScopedAccessTokenMiddleware.php');

include_once('vendor/google/apiclient/src/Google/Http/REST.php');

include_once('vendor/google/apiclient/src/Google/Task/Runner.php');

include_once('vendor/google/apiclient/src/Google/AccessToken/Revoke.php');

include_once('vendor/google/apiclient/src/Google/AuthHandler/AuthHandlerFactory.php');
include_once('vendor/google/apiclient/src/Google/AuthHandler/Guzzle6AuthHandler.php');

include_once('vendor/google/apiclient/src/Google/Service/Resource.php');

include_once('vendor/google/apiclient/src/Google/Utils/UriTemplate.php');

include_once('vendor/google/apiclient/src/Google/Client.php');
include_once('vendor/google/apiclient/src/Google/Model.php');
include_once('vendor/google/apiclient/src/Google/Collection.php');
include_once('vendor/google/apiclient/src/Google/Service.php');

include_once('vendor/google/apiclient-services/src/Google/Service/Drive/DriveFile.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/FileList.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/TeamDrive.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/TeamDriveList.php');

include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/About.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Changes.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Channels.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Comments.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Files.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Permissions.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Replies.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Revisions.php');
include_once('vendor/google/apiclient-services/src/Google/Service/Drive/Resource/Teamdrives.php');

include_once('vendor/google/apiclient-services/src/Google/Service/Drive.php');
