<?php
/**
 * The main InertiaJS API.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use Closure;
use TypeError;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class ResponseFactory {
	private static ResponseFactory $instance;

	private ?Closure $subscriber = null;
	private bool $hasSubscriber = false;

	/** @var mixed[] */
	protected array $shared    = array();
	protected string $template = Inertia::APP;
	protected string $version  = '1.0';

	/** @var array<string,mixed> */
	protected array $body;

	final public static function inertia(): static {
		return Adapter::app()?->has( id: Inertia::APP )
			? Adapter::app()->get( id: Inertia::APP )
			: ( static::$instance ??= new static() );
	}

	/**
	 * @throws LogicException When more than one argument passed as subscriber.
	 * @throws TypeError      When appropriate type not passed.
	 */
	public function subscribe( Closure|false|null $subscriber = null ): mixed {
		return $this->onSubscription( func_get_args() );
	}

	public function setVersion( string $version ): void {
		$this->version = $version;
	}

	public function setRoot( string $template ): void {
		$this->template = $template;
	}

	public function getVersion(): string {
		return $this->version;
	}

	/** @param string|mixed[] $data */
	public function share( string|array $data, mixed $value = null ): void {
		if ( is_array( $data ) ) {
			$this->shared = array( ...$this->shared, ...$data );
		} else {
			$this->set( $this->shared, $data, $value );
		}
	}

	/** @link https://inertiajs.com/partial-reloads */
	public function partial( Closure $prop ): Partial {
		return Adapter::app()?->has( id: Adapter::PARTIAL_ALIAS )
			? Adapter::app()->get( id: Adapter::PARTIAL_ALIAS )->set( data: $prop )
			: ( new Partial() )->set( data: $prop );
	}

	public function reloadServer( ServerRequestInterface $request ): ResponseInterface {
		$target   = $request->getRequestTarget();
		$response = Adapter::responseFrom( $request );

		return ! Inertia::active( $request )
			? $response->withStatus( code: 302 )->withHeader( 'Location', value: $target )
			: Header::Location->addTo(
				transport: $response->withStatus( code: 409, reasonPhrase: 'Conflict - Reload SPA' ),
				value: $target
			);
	}

	public function render(
		ServerRequestInterface $request,
		string $component,
		array $props = array()
	): ?ResponseInterface {
		$response   = Adapter::responseFrom( $request );
		$this->body = array(
			'component' => $component,
			'props'     => array( ...$this->shared, ...$this->resolved( $props, $request, $component ) ),
			'url'       => $request->getUri()->getPath(),
			'version'   => $this->version,
		);

		return Inertia::active( $request ) ? $this->json( $response ) : $this->html( $response );
	}

	/** Ensures Client-Side Rendering to create SPA using InertiaJS. */
	abstract protected function json( ResponseInterface $previous ): ?ResponseInterface;

	/** Ensures Server-Side Rendering for new request using the root template file. */
	abstract protected function html( ResponseInterface $previous ): ?ResponseInterface;

	/**
	 * @phpstan-param array<string,mixed> $props
	 * @phpstan-return array<string,mixed>
	 */
	private function resolved( array $props, ServerRequestInterface $request, string $component ): array {
		$isOnSamePage = Header::PartialComponent->of( $request ) === $component;

		/**
		 * Do not include partial props unless current page is reloaded, and
		 * the client sends partial prop key(s) in the request header.
		 *
		 * @link https://inertiajs.com/partial-reloads
		 */
		if ( ! $isOnSamePage ) {
			$props = array_filter(
				array: $props,
				callback: static fn ( mixed $prop ): bool => ! $prop instanceof Partial
			);
		}

		if ( $isOnSamePage && Header::PartialOnly->in( $request ) ) {
			$props = $this->resolvePartial( $props, $request );
		}

		return $this->resolveInvocable( $props );
	}

	/**
	 * @phpstan-param array<string,mixed> $props
	 * @phpstan-return array<string,mixed>
	 */
	private function resolvePartial( array $props, ServerRequestInterface $request ): array {
		return array_intersect_key(
			$props,
			array_flip( array: explode( separator: ',', string: Header::PartialOnly->of( $request ) ) )
		);
	}

	/**
	 * @phpstan-param array<string,mixed> $props
	 * @phpstan-return array<string,mixed>
	 */
	private function resolveInvocable( array $props ) {
		foreach ( $props as $key => $prop ) {
			if ( $prop instanceof Closure || $prop instanceof Partial ) {
				$props[ $key ] = $prop();
			}

			if ( is_array( $prop ) ) {
				$props[ $key ] = $this->resolveInvocable( $prop );
			}
		}

		return $props;
	}

	/**
	 * @param mixed[] $array
	 * @return mixed[]
	 */
	private function set( array &$array, string|int|null $key, mixed $value ): array {
		if ( is_null( $key ) ) {
			return $array = $value;
		}

		$keys = explode( '.', $key );

		foreach ( $keys as $i => $key ) {
			if ( 1 === count( $keys ) ) {
				break;
			}

			unset( $keys[ $i ] );

			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) ) {
				$array[ $key ] = array();
			}

			$array = &$array[ $key ];
		}

		$array[ array_shift( $keys ) ] = $value;

		return $array;
	}

	/** @param mixed[] $args */
	private function onSubscription( array $args ) {
		$method        = Inertia::class . '::subscribe()';
		$nullOrClosure = 'either "null" or a "Closure" instance';

		if ( 1 !== ( $count = count( $args ) ) ) {
			throw new LogicException(
				sprintf(
					'%1$s only accepts "1" argument: %2$s. Total "%3$s" argument passed.',
					$method,
					$nullOrClosure,
					$count
				)
			);
		}

		$arg      = reset( $args );
		$invoking = $this->hasSubscriber && false === $arg;

		if ( $subscribing = ! $this->hasSubscriber && ( null === $arg || $arg instanceof Closure ) ) {
			$this->hasSubscriber = true;
			$this->subscriber    = $arg;

			return null;
		}

		return match ( true ) {
			$subscribing, $invoking => null === $this->subscriber ? null : ( $this->subscriber )(),
			default                 => throw new TypeError( "$method only accepts $nullOrClosure." )
		};
	}
}
