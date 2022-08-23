<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Dao\UserCreateDao;
use App\User\Service\UserCreateService;
use Symfony\Component\HttpFoundation\Request;

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
