<?php
/**
 * The partial data that hydrates component props when same page is reloaded.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use Closure;

readonly class Partial {
	public function __construct( private Closure $data ) {}

	public function __invoke() {
		return ( $this->data )();
	}
}
