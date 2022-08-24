<?php

declare(strict_types=1);

namespace User\Controller;

use Symfony\Component\HttpFoundation\Request;
use User\Dao\UserCreateDao;
use User\Service\UserCreateService;

class UserCreateAction
{
    private UserCreateService $userCreateService;

    public function __construct(UserCreateService $userCreateService)
    {
        $this->userCreateService = $userCreateService;
    }

    public function __invoke(Request $request)
    {
        $data = \json_decode($request->getContent(), true, 512);

        $userNew = new UserCreateDao($data['email'], $data['password'], $data['name']);
        $this->userCreateService->__invoke($userNew);
    }
}
