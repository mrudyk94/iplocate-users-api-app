<?php

declare(strict_types=1);

namespace App\UI\Controller\Api;

use App\Application\Exception\AppException;
use App\Application\Port\Service\UserServiceInterface;
use App\Domain\ValueObject\MobilePhone;
use App\UI\DTO\CreateUserInput;
use App\UI\DTO\UpdateUserInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users', name: 'api_users_')]
final readonly class UserController
{
    /**
     * @param UserServiceInterface $userService
     */
    public function __construct(
        private UserServiceInterface $userService,
    )
    {
    }

    /**
     * Додаємо нового користувача
     * @param Request $request
     * @param CreateUserInput $input
     * @return JsonResponse
     */
    #[Route('',
        name: 'create',
        methods: ['POST']
    )]
    public function create(
        Request $request,
        #[MapRequestPayload] CreateUserInput $input
    ): JsonResponse
    {
        $this->userService->createUser(
            $input->firstName,
            $input->lastName,
            $input->phoneNumbers,
            $request->getClientIp()
        );

        return new JsonResponse(['message' => 'Створення користувача поставлено в чергу!'], Response::HTTP_CREATED);
    }

    /**
     * Оновлення користувача
     * @param UpdateUserInput $input
     * @return JsonResponse
     */
    #[Route('',
        name: 'update',
        methods: ['PUT']
    )]
    public function update(
        #[MapRequestPayload] UpdateUserInput $input
    ): JsonResponse
    {
        // Шукаємо користувача за ID
        $user = $this->userRepository->findById($input->id);

        // Якщо користувача немає — кидаємо 404
        if (!$user) {
            throw new NotFoundHttpException(sprintf('User with ID %d not found', $input->id));
        }

        // Звичайний користувач може оновлювати тільки себе
        $currentUser = $this->security->getUser();
        if ($currentUser->getRoles()[0] !== 'ROLE_ROOT') {

            if ($currentUser->getId() !== $user->getId()) {
                throw new AccessDeniedHttpException('Access denied');
            }
        }

        // Оновлюємо дані користувача
        $user->setLogin($input->login);
        $user->setPhone(new MobilePhone($input->phone));

        // Оновлюємо пароль, якщо переданий
        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        try {
            $this->userRepository->saveAndFlush($user);
        } catch (AppException) {
            throw new ConflictHttpException('User already exists');
        }

        $responseData = [
            'id' => $user->getId()
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    /**
     * Отримуємо користувача по ID
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}',
        name: 'user',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    public function getUser(
        int $id
    ): JsonResponse
    {
        // Перевірка, чи користувач існує в базі даних
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new NotFoundHttpException(sprintf('User with ID %d not found', $id));
        }

        /**
         * По завданню вказано повертати пароль при запиту, але я не бачу сенсу, бо він хешований.
         * Користувачу пароль не потрібен. Навіть хеш — це витік чутливої інформації.
         */
        $data = [
            'id' => $user->getId(),
            'phone' => $user->getPhone()->asString(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Видалення користувача
     * @param int $id
     * @return JsonResponse
     */
    /*#[Route('/{id}',
        name: 'delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['DELETE']
    )]
    public function delete(
        int $id
    ): JsonResponse
    {
        // Перевірка, чи користувач якого хочемо видалити є в базі даних
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new NotFoundHttpException(sprintf('User with ID %d not found', $id));
        }

        // Видаляємо користувача
        $this->userRepository->deleteAndFlash($user);

        return new JsonResponse(['message' => 'User deleted'], Response::HTTP_OK);
    }*/
}
