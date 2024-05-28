## Introduction
An **[InertiaJS][Inertia]** Adapter for PHP projects that uses implementation of **[PSR7 (HTTP Message Interface)][PSR7]**, **[PSR15 (Server Request Handlers)][PSR15]**, and optionally **[PSR11 (Container Interface)][PSR11]**.

## Installation (via Composer)
Install library using composer command:
```sh
$ composer require thewebsolver/inertia
```

For seamless handling of Middlewares and storing each middleware response, require [Pipeline][pipeline] library and use it in your request handler. For more details see [pipeline docs][pipelineDocs].

```sh
$ composer require thewebsolver/pipeline
```

## Usage
Use this InertiaJS library in your own PHP project and create a Single-Page Application hassle-free.

To be able to use InertiaJS, create Response Factory that returns appropriate response based on whether Inertia is being loaded or not. Abstract protected methods accepts a response interface that is provided by the last middleware as part of request/response cycle.

> ❗IMPORTANT NOTE❗: Your Request Handler should always pass response returned by previous middleware to the request being passed to next middleware's `\Psr\Http\Server\MiddlewareInterface::process()` method.

### Create Factory
```php
use Psr\Http\Message\ResponseInterface;
use TheWebSolver\Codegarage\Lib\Inertia\ResponseFactory;

class InertiaResponseFactory extends ResponseFactory {
	/**
	 * Ensures that first request will paint the Browser DOM with full HTML page.
	 *
	 * Response should follow these implementations.
	 * - The header may have Content-Type as `text/html` or similar.
	 */
	protected function html( ResponseInterface $previous ): ResponseInterface {
		return new class( $previous ) implements ResponseInterface {
			// ...implement your logic to render full HTML page string as response body.
		};
	}

	/**
	 * Ensures subsequent request will only need to provide client-side props and no more server-side reloads.
	 *
	 * Response should follow these implementations.
	 * - The header must have Content-Type as `application/json`.
	 * - The response must return a JSON encoded string when
	 *   `$response->getBody()->getContents()` is called.
	 */
	protected function json( ResponseInterface $previous ): ResponseInterface {
		return new class( $previous ) implements ResponseInterface {
			// ...implement logic to pass JSON encoded data as response body.
		};
	}
}
```

### DI and Auto-wiring
#### Option 1: Using App Container
If your project has app Container that implements `Psr\Container\ContainerInterface`, set binding using it. We'll assume that app container supports singleton design pattern. If it does not, use [Option 2](#OPTION2).

```php
use TheWebSolver\Codegarage\Lib\Inertia\Adapter;
use TheWebSolver\Codegarage\Lib\Inertia\ResponseFactory;

// Inject container to Inertia app. Here "$appContainer" is your project container.
Adapter::setApp( app: $appContainer );

// Bind your custom InertiaResponseFactory to the abstract ResponseFactory as a singleton.
$appContainer->singleton(
	abstract: ResponseFactory::class,
	concrete: InertiaResponseFactory::class
);
```

#### <a href="OPTION2"></a>Option 2: Using Inertia API
Inject factory directly to the Inertia app.

```php
use TheWebSolver\Codegarage\Lib\Inertia\Inertia;

Inertia::setFactory( classname: InertiaResponseFactory::class );
```

### Inertia-Specific Setup
If any external task needs to be handled when middleware is processed, you can pass a subscriber. Most use case would be to add bundled script so InertiaJS works as intended.

```php
use TheWebSolver\Codegarage\Lib\Inertia\Inertia;

Inertia::subscribe(
	subscriber: static fn() => '<script src="path/to/bundled/inertia.js">'
);
```

### Your Project-Specific Setup
Once everything is setup, we'are ready to perform HTTP request/response. We'll assume your project has routes that takes middleware. Following codes are assumed and these implementations may/may not be applied to your project. The default root view name is set to "inertia". You can update it when passing the middleware.

```php
use Psr\Http\Message\ServerRequestInterface;
use TheWebSolver\Codegarage\Lib\Inertia\Middleware;

// Optional. Custom path to default root view. If not provided, it will search for php file named "inertia".
$view = 'path/to/first/loaded/by/server/templateFile.php';

Route::get( '/posts/', function( ServerRequestInterface $request ) {
	// Required. Props computed from API.
	$posts = array();

	return Inertia::render( $request, component: 'posts', props: compact( 'posts' ) );
} )->middleware( new Middleware()->set( version: 'usuallyScriptVersion', rootView: $view ) );
```

<!-- MARKDOWN LINKS -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[Inertia]: https://inertiajs.com/
[PSR7]: https://www.php-fig.org/psr/psr-7/
[PSR11]: https://www.php-fig.org/psr/psr-11/
[PSR15]: https://www.php-fig.org/psr/psr-15/
[pipeline]: https://github.com/TheWebSolver/pipeline
[pipelineDocs]: https://github.com/TheWebSolver/pipeline#psr-7--psr-15-bridge
