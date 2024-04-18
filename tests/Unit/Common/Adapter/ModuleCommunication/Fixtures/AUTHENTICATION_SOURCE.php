<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\ModuleCommunication\Fixtures;

enum AUTHENTICATION_SOURCE
{
    case REQUEST;
    case PASS_ON_HEADERS;
    case NOT_AUTHENTICATED;
}
