<?php

declare(strict_types=1);

namespace App\UI\Controller\Api;

use App\Application\Port\Service\UserServiceInterface;
use App\UI\DTO\CreateUserInput;
use App\UI\DTO\GetListUsersInput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/users', name: 'api_users_')]
#[OA\Tag(name: 'Users')]
#[OA\Server(url: '/v1/api')]
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

    #[Route('/v1/api/ping', methods: ['GET'])]
    #[OA\Get(
        path: '/ping',
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function ping(): JsonResponse
    {
        return new JsonResponse(['ok' => true]);
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
    #[OA\Post(
        path: '/users',
        description: 'Queues a user creation request for async processing. IP geolocation is resolved in the background.',
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['firstName', 'lastName', 'phoneNumbers'],
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'Іван'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Шевченко'),
                    new OA\Property(
                        property: 'phoneNumbers',
                        type: 'array',
                        items: new OA\Items(type: 'string', example: '+380971234567'),
                        minItems: 1,
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_ACCEPTED,
                description: 'User creation queued successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'User creation queued'),
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid JSON body',
            ),
            new OA\Response(
                response: Response::HTTP_UNPROCESSABLE_ENTITY,
                description: 'Validation failed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: ['firstName' => 'First name is required'],
                        ),
                    ]
                )
            ),
        ]
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

        return new JsonResponse(['message' => 'User creation queued!'], Response::HTTP_CREATED);
    }

    /**
     * Отримуємо користувача по ID
     * @param GetListUsersInput $input
     * @return JsonResponse
     */
    #[Route('/list',
        name: 'list',
        methods: ['GET']
    )]
    public function getList(
        #[MapQueryString] GetListUsersInput $input
    ): JsonResponse
    {
        $list = $this->userService->getUsersListSorted($input->sort, $input->order);

        return new JsonResponse(['list' => $list], Response::HTTP_OK);
    }

    /**
     * Видалення користувача
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}',
        name: 'delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['DELETE']
    )]
    public function delete(
        int $id
    ): JsonResponse
    {
        $this->userService->deleteUser($id);

        return new JsonResponse(['message' => 'User deleted!'], Response::HTTP_NO_CONTENT);
    }
}
