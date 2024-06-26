<?php
/**
 * The InertiaJS middleware.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface {
	private readonly string $rootView;
	private readonly string $version;

	public function set( string $version = '1.0', string $rootView = '' ): static {
		$this->rootView ??= $rootView;
		$this->version  ??= $version;

		return $this;
	}

	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	): ResponseInterface {
		$response = Header::Vary->addTo( $handler->handle( $request ), value: Header::Inertia->value );

		// Run subscriber during middleware process.
		Inertia::subscribe( subscriber: false );

		if ( $this->version ?? false ) {
			Inertia::setVersion( $this->version );
		}

		Inertia::share(
			data: array( 'errors' => $request->getAttribute( 'validationErrors', default: array() ) )
		);

		if ( $this->rootView ?? false ) {
			Inertia::setRoot( $this->rootView );
		}

		if ( ! Inertia::active( $request ) ) {
			return $response;
		}

		return $this->needsCacheBusting( $request )
			? Inertia::reloadServer( $request, $response )
			: $response;
	}

	private function needsCacheBusting( ServerRequestInterface $request ): bool {
		return 'GET' === $request->getMethod() && ! Inertia::sameVersion( $request );
	}
}
