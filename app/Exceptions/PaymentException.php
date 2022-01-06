<?php

namespace App\Exceptions;

use Exception;

/**
 * PaymentException represents a runtime error during particular payment processing.
 *
 * This exception is meant to be shown to end user, passing error from payment gateway to user interface.
 */
class PaymentException extends Exception
{
}
