<?php

namespace Sysrandom\Zego\Exceptions;

use Exception;
use Sysrandom\Zego\Support\ZegoErrorCodes;

class ZegoException extends Exception
{
    public function __construct($code)
    {
        parent::__construct(get_message($code), $code);
    }

    private function get_message($code): string
    {
        switch ($code) {
            case ZegoErrorCodes::appIDInvalid:
                return 'App ID is invalid';
            case ZegoErrorCodes::userIDInvalid:
                return 'User ID is invalid';
            case ZegoErrorCodes::secretInvalid:
                return 'App Secret must be a 16/24/32 bytes string';
            case ZegoErrorCodes::effectiveTimeInSecondsInvalid:
                return 'Token expiry is invalid';
        }
    }
}
