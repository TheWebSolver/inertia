<?php
/**
 * Adapter to interact with external libraries.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use ValueError;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use TheWebSolver\Codegarage\Helper\Event;
use TheWebSolver\Codegarage\Base\Commander;
use Psr\Http\Message\ServerRequestInterface;

class Adapter {
	public const MIDDLEWARE_RESPONSE = 'middlewareResponse';
	public const PARTIAL_ALIAS       = 'partialProperty';

	private static ?ContainerInterface $app = null;

	public static function app(): ?ContainerInterface {
		return static::$app;
	}

	public static function setApp( ContainerInterface $app ): void {
		static::$app = $app;
	}

	/** @throws ValueError When `$request->getAttribute(Adapter::MIDDLEWARE_RESPONSE)` does not return Response. */
	public static function responseFrom( ServerRequestInterface $request ): ResponseInterface {
		$response = $request->getAttribute( static::MIDDLEWARE_RESPONSE );

		if ( $response instanceof ResponseInterface ) {
			return $response;
		}

		throw new ValueError(
			sprintf(
				'Middleware must pass the generated response back to request with attribute key: %s.',
				static::MIDDLEWARE_RESPONSE
			)
		);
	}

	/**
	 * @phpstan-param mixed[]  $receiverArgs
	 * @phpstan-param string[] $ids
	 * @return ?Event
	 */
	public static function subscribeWith( ClientResource $script ) {
		$receiver = static function() use ( $script ) {
			\wp_enqueue_script(
				handle: Inertia::APP,
				src: $script->url,
				deps: $script->dependencies,
				ver: $script->version,
				args: array( 'in_footer' => true )
			);
		};

		if ( class_exists( '\\TheWebSolver\\Codegarage\\Helper\\Event' ) ) {
			return Event::subscribe( to: 'wp_enqueue_scripts' )
				->when( Inertia::APP )
				->needs( reason: 'registration of client file' )
				->do( command: Commander::Action->resolve( $receiver ) );
		}

		if ( function_exists( '\\add_action' ) ) {
			\add_action( hook_name: 'wp_enqueue_scripts', callback: $receiver );
		}

		return null;
	}
}
