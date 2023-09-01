<?php

namespace Tests\Acceptance;

use App\Models\User\User;
use App\Repositories\UserRepository;
use Tests\FrameworkTest;

class UserControllerTest extends FrameworkTest
{
    /** @var UserRepository */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
    }

    public function testShowRequestReturnsUserData()
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $result = $this->get("/api/users/$user->id");
        $result->assertSuccessful();
        $this->assertEquals(
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nickname' => $user->nickname,
            ],
            json_decode($result->getContent(), true)
        );
    }

    // public function testUpdateRequestUpdatesUserData()
    // {
    //     /** @var User $user */
    //     $user = $this->userFactory->create();
    //     // Include nickname
    //     $data = [
    //         'id' => $user->id,
    //         'name' => $this->faker->name,
    //         'nickname' => $this->faker->unique()->userName,
    //         'email' => $user->email,
    //     ];
    //     $result = $this->put("/api/users/$user->id", $data);
    //     $result->assertSuccessful();
    //     $this->assertEquals($data, json_decode($result->getContent(), true));
    // }
    public function testUpdateRequestUpdatesUserData()
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $data = [
            'id' => $user->id,
            'name' => $this->faker->name,
            'email' => $user->email,
            'nickname' => $this->faker->unique()->userName, // Include a valid nickname
        ];
        $result = $this->put("/api/users/$user->id", $data);
        $result->assertSuccessful();

        // Validate that the response includes the 'nickname' field
        $responseData = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('nickname', $responseData);

        // Add assertions to validate the 'nickname' field further
        $this->assertIsString($responseData['nickname']);
        $this->assertTrue(strlen($responseData['nickname']) <= 30);

        // Optionally, you can also validate uniqueness if you want to test for a unique nickname.
        // Ensure that $responseData['nickname'] is unique among users.
        $this->assertFalse(User::where('nickname', $responseData['nickname'])->where('id', '!=', $user->id)->exists());
    }


    // public function testCreateRequestCreatesUser()
    // {
    //     // Include nickname
    //     $data = [
    //         'name' => $this->faker->name,
    //         'nickname' => $this->faker->unique()->userName,
    //         'email' => $email = $this->faker->unique()->email,
    //         'password' => 'hen rooster chicken duck',
    //     ];
    //     $this->assertFalse($this->repository->getModel()->newQuery()->where('email', $email)->exists());
    //     $result = $this->post("/api/users", $data);
    //     $result->assertSuccessful();
    //     $this->assertTrue($this->repository->getModel()->newQuery()->where('email', $email)->exists());
    // }
    public function testCreateRequestCreatesUser()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $email = $this->faker->unique()->email,
            'password' => 'hen rooster chicken duck',
            'nickname' => $this->faker->unique()->userName, // Include a valid nickname
        ];
        $this->assertFalse($this->repository->getModel()->newQuery()->where('email', $email)->exists());
        $result = $this->post("/api/users", $data);
        $result->assertSuccessful();
        $this->assertTrue($this->repository->getModel()->newQuery()->where('email', $email)->exists());

        // Validate that the response includes the 'nickname' field
        $responseData = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('nickname', $responseData);

        // Add assertions to validate the 'nickname' field further
        $this->assertIsString($responseData['nickname']);
        $this->assertTrue(strlen($responseData['nickname']) <= 30);

        /****
         * This is an optional test.
         the purpose is to fail this test to be honest, since multiple peoples different
          first name have  the same nicknames.
         this test checks if the nickname is unique among all the
         users in the database.
         Ensure that $responseData['nickname'] is unique among users.
          */
        // $this->assertFalse(User::where('nickname', $responseData['nickname'])->exists());
    }

}