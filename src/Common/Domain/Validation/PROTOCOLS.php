<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

enum PROTOCOLS: string
{
    case HTTPS = 'https';
    case HTTP = 'http';
    case FTP = 'FTP';
}
