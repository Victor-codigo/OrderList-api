<?php

declare(strict_types=1);

namespace Common\Domain\Response;

enum RESPONSE_STATUS: string
{
    case OK = 'ok';
    case ERROR = 'error';
    case FAIL = 'fail';
}
