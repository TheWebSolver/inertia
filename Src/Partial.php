<?php
/**
 * The partial data that hydrates component props when same page is reloaded.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use Closure;

class Partial {
	protected readonly Closure $data;

	public function set( Closure $data ): static {
		$this->data = $data;

		return $this;
	}

	public function __invoke() {
		return ( $this->data )();
	}
}
