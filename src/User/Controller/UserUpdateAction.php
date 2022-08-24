<?php

declare(strict_types=1);

namespace User\Controller;

use Symfony\Component\HttpFoundation\Request;
use User\Dao\UserUpdateDao;
use User\Service\UserUpdateService;

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
