## Introduction:

An **[InertiaJS][Inertia]** Adapter for using with PHP projects that uses implementation of **[PSR7 (HTTP Message Interface)][PSR7]**, **[PSR11 (Container Interface)][PSR11]** & **[PSR15 (Server Request Handlers)][PSR15]**.

## Documentation

### Installation (via Composer)

Install library using composer command:
```sh
$ composer require thewebsolver/inertia
```

### Usage
Use this InertiaJS library in your own PHP project and create a Single-Page Application hassle-free.

#### STEP:1 Create required concretes

To be able to use InertiaJS file, create Response Factory that returns appropriate response based on whether Inertia is being loaded or not.

```php
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TheWebSolver\Codegarage\Lib\Inertia\Adapter;
use TheWebSolver\Codegarage\Lib\Inertia\Partial;
use TheWebSolver\Codegarage\Lib\Inertia\Inertia;
use TheWebSolver\Codegarage\Lib\Inertia\Middleware;
use TheWebSolver\Codegarage\Lib\Inertia\ResponseFactory;

// This usage assumes your $appContainer supports aliasing, binding, and singleton features. If it does not, implement it in the way that works with your project.
$appContainer = new class implements ContainerInterface {}
$responseFactory = new class extends ResponseFactory {
	protected function html( ResponseInterface $MiddlewareResponse ): ResponseInterface {
		$response = new class( $MiddlewareResponse ) implements ResponseInterface {};

		/**
		 * Response should follow these implementations.
		 *
		 * - The header may have Content-Type as `text/html` or similar.
		 */
		return $response;
	}

	protected function json( ResponseInterface $previous ): ResponseInterface {
		$response = new class( $MiddlewareResponse ) implements ResponseInterface {};

		/**
		 * Response should follow these implementations.
		 *
		 * - The response must return a JSON encoded string when `$response->getBody()->getContents()` is called.
		 * - The header must have Content-Type as `application/json`.
		 */
		return $response;
	}
}
```

#### STEP:2 Auto-wiring/DI

In your project's main file, inject relevant data to the app container and InertiaJS app.

```php
// Inject container to Inertia app for auto-wiring.
Adapter::setApp( app: $appContainer );

// Start aliasing/binding inertia classes to your app container.
$appContainer->alias( abstract: $responseFactory::class, alias: Inertia::APP );
$appContainer->singleton( abstract: Inertia::APP, concrete: InertiaResponseFactory::class )
$appContainer->alias( abstract: Partial::class, alias: InertiaAdapter::PARTIAL_ALIAS )

// Inject factory to Inertia app.
Inertia::setFactory( classname: $responseFactory::class );

// If any external task needs to be handled when middleware is processed, you can pass a subscriber. Most use case would be to add bundled script so InertiaJS works as intended.
$subscriber = static fn() => '<script src="path/to/bundled/inertia.js">';

Inertia::subscribe( subscriber: $subscriber );
```

#### STEP:3 Project-specific Setup

Once everything is auto-wired, we'are ready to perform HTTP request/response. We'll assume your project has routes that takes middleware. Following codes are assumed and these implementations may/may not be applied to your project. Use Inertia app how it fits in your project.

```php
Route::get( '/posts/', function( ServerRequestInterface $request ) {
	$posts = array() // ...computed from API.
	$view  = 'path/to/first/loaded/by/server/templateFile.php';

	return Inertia::render( $request, component: 'posts', props: compact( 'posts' ) );
} )->middleware( new Middleware()->set( version: 'usuallyScriptVersion', rootView: $view ) );
```

<!-- MARKDOWN LINKS -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[Inertia]: https://inertiajs.com/
[PSR7]: https://www.php-fig.org/psr/psr-7/
[PSR11]: https://www.php-fig.org/psr/psr-11/
[PSR15]: https://www.php-fig.org/psr/psr-15/
