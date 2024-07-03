<?php

declare(strict_types=1);

namespace Common\Domain\Ports\ModuleCommunication;

use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDtoPaginatorInterface;
use Common\Domain\Response\ResponseDto;

interface ModuleCommunicationInterface
{
    /**
     * @throws Error400Exception
     * @throws ModuleCommunicationException
     * @throws ValueError
     */
    public function __invoke(ModuleCommunicationConfigDto $routeConfig): ResponseDto;

    /**
     * @throws ModuleCommunicationException
     * @throws ValueError
     * @throws ModuleCommunicationTokenNotFoundInRequestException
     * @throws ModuleCommunicationErrorResponseException
     * @throws InvalidArgumentException
     */
    public function getPagesRangeEndpoint(ModuleCommunicationConfigDtoPaginatorInterface $routeConfig, int $pageIni, ?int $pageEnd): \Generator;

    /**
     * @throws ModuleCommunicationException
     * @throws ValueError
     * @throws ModuleCommunicationTokenNotFoundInRequestException
     * @throws ModuleCommunicationErrorResponseException
     * @throws InvalidArgumentException
     */
    public function getAllPagesOfEndpoint(ModuleCommunicationConfigDtoPaginatorInterface $routeConfig): \Generator;
}
