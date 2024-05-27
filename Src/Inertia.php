<?php
/**
 * The InertiaJS accessor.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use RuntimeException;
use BadMethodCallException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method static void                                         setVersion(string $version)
 * @method static void                                         setRoot(string $templateFilePath)
 * @method static string                                       getVersion()
 * @method static void                                         share(string|array $data, mixed $value = null)
 * @method static \TheWebSolver\Codegarage\Lib\Inertia\Partial partial(Closure $callback)
 * @method static void                                         subscribe(?Closure $subscriber)
 * @method static \Psr\Http\Message\ResponseInterface          reloadServer(\Psr\Http\Message\ServerRequestInterface $request)
 * @method static \Psr\Http\Message\ResponseInterface          render(\Psr\Http\Message\ServerRequestInterface $request, string $component, array $props = array())
 */
class Inertia {
	public const APP = 'inertia';

	/** @phpstan-param class-string<ResponseFactory> */
	private static ?string $factoryClassName = null;

	/** @phpstan-param class-string<ResponseFactory> */
	public static function setFactory( string $classname ): void {
		static::$factoryClassName = $classname;
	}

	/**
	 * Acts as facade accessor for Inertia's response factory.
	 *
	 * @throws RuntimeException When a child-class of ResponseFactory not set.
	 * @throws BadMethodCallException When trying to invoke undefined method inside Response Factory.
	 * @uses ResponseFactory::inertia()
	 */
	public static function __callStatic( string $method, array $args ) {
		if ( ! static::$factoryClassName ) {
			throw new RuntimeException(
				sprintf(
					'A factory instance that extends "%s" must be provided for inertia to return the response.',
					ResponseFactory::class
				)
			);
		}

		return ! method_exists( $factory = static::$factoryClassName::inertia(), $method )
			? throw new BadMethodCallException( message: "Inertia {$method} does not exist.", code: 404 )
			: $factory->$method( ...$args );
	}

	public static function active( MessageInterface $transport ): bool {
		return 'true' === Header::Inertia->of( $transport );
	}

	public static function sameVersion( ServerRequestInterface $request ): bool {
		return static::getVersion() === Header::Version->of( $request );
	}
}
