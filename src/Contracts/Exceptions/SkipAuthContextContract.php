<?php
namespace Deegitalbe\LaravelTrustupIoAuthentification\Contracts\Exceptions;

use Throwable;

/**
 * Interface making sure handler is skipping auth related context.
 */
interface SkipAuthContextContract extends Throwable {}