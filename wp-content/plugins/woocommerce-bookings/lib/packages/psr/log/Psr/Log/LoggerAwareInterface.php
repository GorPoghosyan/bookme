<?php
/**
 * @license MIT
 *
 * Modified by woocommerce on 07-August-2025 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace Automattic\WooCommerce\Bookings\Vendor\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger);
}
