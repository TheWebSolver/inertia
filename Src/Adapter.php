<?php
/**
 * Adapter for PSR11 Container and other libraries compatibility.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use ValueError;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
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
}
