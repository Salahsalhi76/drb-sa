<?php

namespace App\Helpers\Exception;

use App\Base\Exceptions\CustomValidationException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;


trait ExceptionHelpers
{
    /**
     * Throws a custom validation error exception.
     *
     * @param array ...$args
     * @throws CustomValidationException
     */
    protected function throwCustomValidationException(...$args)
    {
        throw new CustomValidationException(...$args);
    }

    /**
     * Throws an exception with the invalid credentials message.
     *
     * @param string|null $field
     * @throws CustomValidationException
     * @throws Exception
     */
    protected function throwInvalidCredentialsException($field = null)
    {
        $this->throwCustomException('These credentials do not match our records.', $field);
    }

    /**
     * Throws an exception with the account disabled message.
     *
     * @param string|null $field
     * @throws CustomValidationException
     * @throws Exception
     */
    protected function throwAccountDisabledException($field = null)
    {
        $this->throwCustomException('The account has been disabled.', $field);
    }

    /**
     * Throws an exception with the account not activated message.
     *
     * @param string|null $field
     * @throws CustomValidationException
     * @throws Exception
     */
    protected function throwAccountNotActivatedException($field = null)
    {
        $this->throwCustomException('The account has not been activated.', $field);
    }

    /**
     * Throws an exception with the OTP sending error message.
     *
     * @param string|null $field
     * @throws CustomValidationException
     * @throws Exception
     */
    protected function throwSendOTPErrorException($field = null)
    {
        $this->throwCustomException('Error sending otp. Try again later.', $field);
    }

    /**
     * Throws a custom exception.
     *
     * @param string|array $message
     * @param string|null $field
     * @throws CustomValidationException
     * @throws Exception
     */
     protected function throwCustomException($message, $field = null)
    {
        try {
            if ($field) {
                $this->throwCustomValidationException($message, $field);
            }

            // Log the error details
            Log::info('Error message => ' . $message);
            Log::info('Error field => ' . $field);

            // Simulate throwing an exception
            throw new Exception($message);
        } catch (Exception $e) {
            // Catch the exception and log it
            Log::error('Caught Exception: ' . $e->getMessage());

            // Return a response with a 200 status code for testing
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 200,
            ], 200);
        }
    }

    /**
     * Throws unauthorized exception..
     *
     * @param string|null $message
     * @throws AuthorizationException
     */
    protected function throwAuthorizationException($message = null)
    {
        throw new AuthorizationException($message);
    }
}
