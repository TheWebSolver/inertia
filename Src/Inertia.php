<?php
/**
 * The InertiaJS accessor.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use BadMethodCallException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method static void                                         setVersion(string $version)
 * @method static void                                         setRoot(string $templateFilePath)
 * @method static string                                       getVersion()
 * @method static void                                         share(string|array $data, mixed $value = null)
 * @method static \TheWebSolver\Codegarage\Lib\Inertia\Partial partial(Closure $callback)
 * @method static \Psr\Http\Message\ResponseInterface          reloadServer(\Psr\Http\Message\ServerRequestInterface $request)
 * @method static \Psr\Http\Message\ResponseInterface          render(\Psr\Http\Message\ServerRequestInterface $request, string $component, array $props = array())
 */
class Inertia {
	public const APP = 'inertia';

	/**
	 * Acts as facade accessor for Inertia's response factory.
	 *
	 * @throws BadMethodCallException When trying to invoke undefined method inside Response Factory.
	 * @uses ResponseFactory::inertia()
	 */
	public static function __callStatic( string $method, array $args ) {
		return ! method_exists( $factory = ResponseFactory::inertia(), $method )
			? throw new BadMethodCallException( message: "Inertia {$method} does not exist.", code: 404 )
			: $factory->$method( ...$args );
	}

	public static function active( MessageInterface $transport ): bool {
		return 'true' === Header::Inertia->of( $transport );
	}

	public static function sameVersion( ServerRequestInterface $request ): bool {
		return self::getVersion() === Header::Version->of( $request );
	}
}
