<?php

declare(strict_types=1);

namespace Common\Adapter\Command\HexFilesStructure;

enum PLACEHOLDER: string
{
    case NAMESPACE = '[NAMESPACE]';
    case ENDPOINT = '[ENDPOINT]';
    case NAMESPACE_INNER_LAYER = '[NAMESPACE_INNER_LAYER]';
}
