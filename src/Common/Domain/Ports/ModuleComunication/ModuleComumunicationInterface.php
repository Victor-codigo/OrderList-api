<?php

declare(strict_types=1);

namespace Common\Domain\Ports\ModuleComunication;

use Common\Domain\ModuleComumication\ModuleComunicationConfigDto;
use Common\Domain\Response\ResponseDto;

interface ModuleComumunicationInterface
{
    public function __invoke(ModuleComunicationConfigDto $routeConfig): ResponseDto;
}
