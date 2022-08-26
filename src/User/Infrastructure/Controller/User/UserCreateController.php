<?php

declare(strict_types=1);

namespace User\Infrastructure\Controller\User;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Application\Create\UserCreate;

class UserCreateController
{
    private UserCreate $userCreate;

    public function __construct(UserCreate $userCreate)
    {
        $this->userCreate = $userCreate;
    }

    public function execute(): Response
    {
        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
