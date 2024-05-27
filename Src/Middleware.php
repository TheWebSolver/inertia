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
	private readonly ClientResource $script;
	private readonly string $rootView;

	public function set( ClientResource $script, string $rootView = '' ): void {
		$this->rootView = $rootView;
		$this->script   = $script;
	}

	public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ): ResponseInterface {
		$response = Header::Vary->addTo( Adapter::responseFrom( $request ), value: Header::Inertia->value );

		// This is just a fallback. The version must always be set using script version.
		Inertia::setVersion( $request->getProtocolVersion() );

		if ( isset( $this->script ) ) {
			Adapter::subscribeWith( $this->script );
			Inertia::setVersion( $this->script->version );
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

		return $this->needsCacheBusting( $request ) ? Inertia::reloadServer( $request ) : $response;
	}

	private function needsCacheBusting( ServerRequestInterface $request ): bool {
		return 'GET' === $request->getMethod() && ! Inertia::sameVersion( $request );
	}
}
