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


    public function testUpdateRequestUpdatesUserData()
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $data = [
            'id' => $user->id,
            'name' => $this->faker->name,
            'email' => $user->email,
            'nickname' => $this->faker->unique()->userName,
            // Include a valid nickname
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

    public function testCreateRequestCreatesUser()
    {
        $data = [
            'name' => $this->faker->name,
            'email' => $email = $this->faker->unique()->email,
            'password' => 'hen rooster chicken duck',
            'nickname' => $this->faker->unique()->userName,
            // Include a valid nickname
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


    }

    /*  optional tests  ( purpose is to fail)  -- TOTAL 5 tests that should fail. */

    /****
         the purpose is to fail this test to be honest, since multiple peoples different
          first name have  the same nicknames.
         this test checks if the nickname is unique among all the
         users in the database.
         Ensure that $responseData['nickname'] is unique among users.
          */





    public function testUpdateRequestFailsWithExistingNickname()
    {
        $existingUser = User::factory()->create(['nickname' => 'existing_user']);
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => 'password123',
            'nickname' => $existingUser->nickname,
            // Attempt to use an existing nickname
        ];

        // Act: Attempt to create a new user with an existing nickname.
        $result = $this->put("/api/users/{$existingUser->id}", $data);
        // Assert: Expecting a validation error status code or a redirect
        $this->assertTrue($result->status() === 302 || $result->status() === 422);


        if ($result->status() === 422) {
            // Validate that the response includes validation error messages for 'nickname'
            $responseData = $result->json();
            $this->assertTrue(isset($responseData['errors']['nickname']));

            // Ensure that the original user's nickname remains unchanged
            $existingUser = $existingUser->fresh(); // Refresh the user instance to get the latest data from the database
            $this->assertEquals('existing_user', $existingUser->nickname);
        }
    }


    public function testCreateRequestFailsWithDuplicateNickname()
    {
        // Arrange: Create a user with a unique nickname.
        $existingUser = User::factory()->create(['nickname' => 'john_doe']);

        // Act: Attempt to create a new user with the same nickname.
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => 'password123',
            'nickname' => 'john_doe',
            // Duplicate nickname
        ];
        $result = $this->post("/api/users", $data);

        // Assert: Expecting a validation error status code or a redirect
        $this->assertTrue($result->status() === 302 || $result->status() === 422);

        if ($result->status() === 422) {
            // Try to parse the response as JSON and check for the error message
            $responseData = $result->json();
            $this->assertTrue(isset($responseData['message']));
            $this->assertEquals('The nickname has already been taken.', $responseData['message']);
        }
    }

    public function testCreateRequestFailsWithCaseInsensitiveExistingNickname()
    {
        // Arrange: Create an existing user with a lowercase nickname.
        $existingUser = User::factory()->create([
            'name' => 'New User 1',
            'email' => 'new1@example.com',
            'nickname' => 'john doe',
            // Attempt to use uppercase version of the existing nickname
            'password' => 'password123',
        ]);

        // Act: Attempt to register a new user with the same nickname but in uppercase.
        $response = $this->post('/api/users', [
            'name' => 'New User 2',
            'email' => 'new2@example.com',
            'nickname' => strtoupper($existingUser->nickname),
            // Attempt to use uppercase version of the existing nickname
            'password' => 'password1234',
        ]);

        // Assert: Expecting a validation error status code or a redirect
        $this->assertTrue($response->status() === 302 || $response->status() === 422);

        if ($response->status() === 422) {
            // Validate that the response includes validation error messages for the missing fields
            // Assert: Ensure that the registration fails due to a case-insensitive duplicate nickname.
            $responseData = $response->json();
            $this->assertTrue(isset($responseData['errors']['nickname']));
        }
    }





    public function testCreateRequestFailsWithInvalidNicknameLength()
    {
        // Act: Attempt to create a new user with an invalid long nickname.
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => 'password123',
            'nickname' => str_repeat('%=#aw', 31),
            // An invalid long nickname
        ];
        $result = $this->post("/api/users", $data);

        // Assert: Expecting a validation error status code or a redirect
        $this->assertTrue($result->status() === 302 || $result->status() === 422);

        if ($result->status() === 422) {
            // Validate that the response includes validation error messages for 'nickname'
            $responseData = json_decode($result->getContent(), true);
            $this->assertArrayHasKey('nickname', $responseData['errors']);
        }
    }


    public function testUpdateRequestFailsWithLongNickname()
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $data = [
            'id' => $user->id,
            'name' => $this->faker->name,
            'email' => $user->email,
            'nickname' => str_repeat('a', 31),
            // An invalid long nickname
        ];
        $result = $this->put("/api/users/$user->id", $data);

        // Assert: Expecting a validation error status code or a redirect
        $this->assertTrue($result->status() === 302 || $result->status() === 422);

        if ($result->status() === 422) {
            // Validate that the response includes validation error messages for 'nickname'
            $responseData = json_decode($result->getContent(), true);
            $this->assertArrayHasKey('nickname', $responseData['errors']);
        }
    }




}