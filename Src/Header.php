<?php
/**
 * InertiaJS supported headers.
 *
 * @package TheWebSolver\Codegarage\Library
 */

declare( strict_types = 1 );

namespace TheWebSolver\Codegarage\Lib\Inertia;

use Psr\Http\Message\MessageInterface;

enum Header: string {
	case Vary             = 'Vary';
	case Inertia          = 'X-Inertia';
	case Version          = 'X-Inertia-Version';
	case Location         = 'X-Inertia-Location';
	case PartialOnly      = 'X-Inertia-Partial-Data';
	case PartialComponent = 'X-Inertia-Partial-Component';

	public function addTo( MessageInterface $transport, string $value ): MessageInterface {
		return $transport->withHeader( $this->value, $value );
	}

	public function of( MessageInterface $transport ): string {
		return $transport->getHeader( $this->value )[0] ?? '';
	}

	public function in( MessageInterface $transport ): bool {
		return $transport->hasHeader( $this->value );
	}
}
