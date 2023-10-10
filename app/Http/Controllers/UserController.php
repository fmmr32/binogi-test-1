<?php

namespace App\Http\Controllers;

use App\Mappers\UserMapper;
use App\Models\User\User;
use App\Repositories\UserRepository;
use App\Support\Requests\UserStoreRequest;
use App\Support\Requests\UserUpdateRequest;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct(private UserRepository $userRepository, private UserMapper $userMapper)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Show user",
     *     description="Show user",
     *     @OA\Parameter(
     *          name="user",
     *          in="path",
     *          description="ID of user",
     *          required=true,
     *          example=1,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="User Details",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     * )
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return \Response::json(
            $this->userMapper->single($user),
            200,
            []
        );
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create user",
     *     description="Create user",
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/UserStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     *     @OA\Response(response=400, description="User cannot be created"),
     *     @OA\Response(response=422, description="Failed validation of given params"),
     * )
     *
     * @param UserStoreRequest $request
     *
     * @return JsonResponse
     * @throws Exception
     */


    /***
     * 1. there is no RequestController file here, I am adding the store, update 
     * codes here.
     * 
     * 
     * 2. since there is no index method declared here, I am adding the codes
     * for rules and validation inside the store and update method
     * 
     */


    public function store(UserStoreRequest $request): JsonResponse
    {

        $user = $this->userRepository->create([
            'name' => $request->input('name'),
            'nickname' => $request->input('nickname'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        //using get
        //trim to reduce the risk of user type-o
        // Update: Add Nickname and trim all given data to reduce the risk of user type-o
        //  $user = $this->userRepository->create([
        //     'nickname' => trim($request->get('nickname')),
        //     'name'     => trim($request->get('name')),
        //     'email'    => trim($request->get('email')),
        //     'password' => Hash::make(trim($request->get('password'))),
        // ]);

        return \Response::json($this->userMapper->single($user));
    }


    /**
     * @OA\Put(
     *     path="/api/users/{user}",
     *     tags={"Users"},
     *     summary="Update user",
     *     description="Update user",
     *     @OA\Parameter(
     *          name="user",
     *          in="path",
     *          description="ID of user",
     *          required=true,
     *          example=1,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User after the update",
     *         @OA\JsonContent(ref="#/components/schemas/UserMapper"),
     *     ),
     *     @OA\Response(response=422, description="Failed validation of given params"),
     * )
     *
     * @param UserUpdateRequest $request
     * @param User              $user
     *
     * @return JsonResponse
     */

    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $data = [
            'name' => trim($request->input('name')),
            'nickname' => trim($request->input('nickname')),
            'email' => trim($request->input('email')),
            'password' => Hash::make(trim($request->input('password')) ?: null),
        ];

        //  // Fix: Avoid overwriting existing data if not providing all parameters.
        //  $data = [
        //     'nickname' => trim($request->input('nickname')) ?: $user->nickname,
        //     'name'     => trim($request->input('name')) ?: $user->name,
        //     'email'    => trim($request->input('email')) ?: $user->email,
        //     'password' => Hash::make(trim($request->input('password')) ?: $user->password),
        // ];

        $user->fill($data)->save();

        return \Response::json($this->userMapper->single($user));
    }
}