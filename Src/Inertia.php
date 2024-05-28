<?php
/**
 * The InertiaJS accessor.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use Closure;
use TypeError;
use LogicException;
use RuntimeException;
use BadMethodCallException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method static void                                         setVersion(string $version)
 * @method static void                                         setRoot(string $templateFilePath)
 * @method static string                                       getVersion()
 * @method static void                                         share(string|array $data, mixed $value = null)
 * @method static \TheWebSolver\Codegarage\Lib\Inertia\Partial partial(\Closure $callback)
 * @method static void                                         subscribe(?\Closure $subscriber)
 * @method static \Psr\Http\Message\ResponseInterface          reloadServer(\Psr\Http\Message\ServerRequestInterface $request)
 * @method static \Psr\Http\Message\ResponseInterface          render(\Psr\Http\Message\ServerRequestInterface $request, string $component, array $props = array())
 */
class Inertia {
	public const APP = 'inertia';

	/** @phpstan-param class-string<ResponseFactory> */
	private static ?string $factoryClassName = null;
	private static bool $hasSubscriber = false;

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

		if ( 'subscribe' === $method ) {
			return static::onSubscription( $args );
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

	/**
	 * @param mixed[] $args
	 * @throws LogicException When more than one argument passed as subscriber.
	 * @throws TypeError      When appropriate type not passed.
	 */
	private static function onSubscription( array $args ) {
		$method        = static::class . '::subscribe()';
		$nullOrClosure = 'either "null" or a "Closure" instance';

		if ( count( $args ) !== 1 ) {
			throw new LogicException( "$method only accepts 1 argument: $nullOrClosure." );
		}

		$arg      = reset( $args );
		$invoking = static::$hasSubscriber && false === $arg;

		if ( $subscribing = ! static::$hasSubscriber && ( null === $arg || $arg instanceof Closure ) ) {
			static::$hasSubscriber = true;
		}

		return match ( true ) {
			$subscribing, $invoking => static::$factoryClassName::inertia()->subscribe( $arg ),
			default                 => throw new TypeError( "$method only accepts $nullOrClosure." )
		};
	}
}
