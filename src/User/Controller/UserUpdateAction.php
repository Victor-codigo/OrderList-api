<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Dao\UserUpdateDao;
use App\User\Service\UserUpdateService;
use Symfony\Component\HttpFoundation\Request;

class UserUpdateAction
{
    private UserUpdateService $userUpdateService;

    public function __construct(UserUpdateService $userUpdateService)
    {
        $this->userUpdateService = $userUpdateService;
    }

    public function __invoke(string $id, Request $request)
    {
        $data = \json_decode($request->getContent(), true, 512);

        $user = new UserUpdateDao();
        $user->setEmail($data['email'])
             ->setName($data['name'])
             ->setPassword($data['password']);

        $this->userUpdateService->__invoke($id, $user);
    }
}
