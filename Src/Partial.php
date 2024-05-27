<?php
/**
 * The partial properties to be hydrated when same page is reloaded.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use Closure;

class Partial {
	protected readonly Closure $data;

	public function set( Closure $data ): void {
		$this->data = $data;
	}

	public function __invoke() {
		return ( $this->data )();
	}
}
