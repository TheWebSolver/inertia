<?php
/**
 * Adapter for PSR11 Container and other libraries compatibility.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use ValueError;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Adapter {
	private static string $middlewareResponse = '';
	private static ?ContainerInterface $app = null;

	public static function app(): ?ContainerInterface {
		return static::$app;
	}

	public static function setApp( ContainerInterface $app ): void {
		static::$app = $app;
	}

	public static function setMiddlewareResponseKey( string $name ): void {
		static::$middlewareResponse = $name;
	}

	/**
	 * @throws LogicException When middleware Response key not set.
	 * @throws ValueError     When `$request->getAttribute(Adapter::$middlewareResponse)` does not return Response.
	 */
	public static function responseFrom( ServerRequestInterface $request ): ResponseInterface {
		if ( ! $key = static::$middlewareResponse ) {
			throw new LogicException(
				sprintf(
					'Name to get Response from %1$s::getAttribute() must be set using %2$s::setMiddlewareResponseKey.',
					$request::class,
					static::class
				)
			);
		}

		$response = $request->getAttribute( static::$middlewareResponse );

		if ( $response instanceof ResponseInterface ) {
			return $response;
		}

		throw new ValueError(
			sprintf(
				'Middleware must pass the generated response back to request with attribute key: "%s".',
				$key
			)
		);
	}
}
