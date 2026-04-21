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

#[OA\Tag(name: 'Users')]
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
    #[OA\Post(
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
                            property: 'error',
                            type: 'string',
                            example: "First name cannot be empty. First name must be at least 3 characters long",
                        ),
                        new OA\Property(
                            property: 'status',
                            type: 'integer',
                            example: 422,
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

        return new JsonResponse(['message' => 'User creation queued!'], Response::HTTP_ACCEPTED);
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
    #[OA\Get(
        description: 'Returns a sorted list of users. Supports sorting and ordering via query parameters.',
        summary: 'Get list of users',
        parameters: [
            new OA\Parameter(
                name: 'sort',
                description: 'Field by which to sort users',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'createdAt')
            ),
            new OA\Parameter(
                name: 'order',
                description: 'Sort direction',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'ASC', enum: ['ASC', 'DESC'])
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'List of users retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object'
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid query parameters'
            )
        ]
    )]
    public function getList(
        #[MapQueryString] GetListUsersInput $input
    ): JsonResponse
    {
        $data = $this->userService->getUsersListSorted($input->sort, $input->order);

        return new JsonResponse(['data' => $data], Response::HTTP_OK);
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
    #[OA\Delete(
        description: 'Deletes a user by their unique identifier.',
        summary: 'Delete user',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'User ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 5)
            )
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: 'User deleted successfully'
            ),
            new OA\Response(
                response: Response::HTTP_NOT_FOUND,
                description: 'User not found'
            ),
            new OA\Response(
                response: Response::HTTP_BAD_REQUEST,
                description: 'Invalid user ID'
            )
        ]
    )]
    public function delete(
        int $id
    ): JsonResponse
    {
        $this->userService->deleteUser($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
