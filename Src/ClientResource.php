<?php
/**
 * The Asset Resource file DTO.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

readonly class ClientResource {
	/** @var string[] */
	public array $dependencies;

	public function __construct(
		public string $url,
		public string $version,
		string ...$dependencies
	) {
		$this->dependencies = $dependencies;
	}
}
