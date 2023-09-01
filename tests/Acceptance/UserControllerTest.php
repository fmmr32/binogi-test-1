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


    }

    /*  optional tests  ( purpose is to fail)  -- TOTAL 5 tests that should fail. */

    /****
         the purpose is to fail this test to be honest, since multiple peoples different
          first name have  the same nicknames.
         this test checks if the nickname is unique among all the
         users in the database.
         Ensure that $responseData['nickname'] is unique among users.
          */



    public function testUpdateToExistingNickname()
    {
        // Arrange: Create two users with unique nicknames.
        $user1 = User::factory()->create(['nickname' => 'user1']);
        $user2 = User::factory()->create(['nickname' => 'user2']);

        // Act: Attempt to update the profile of user1 with the nickname of user2.
        $response = $this->actingAs($user1)->put("/profile/{$user1->id}", [
            'name' => 'Updated Name',
            'nickname' => 'user2', // Attempt to set the same nickname as user2
        ]);

        // Assert: Ensure that the profile update fails due to the attempt to use an existing nickname.
        $response->assertSessionHasErrors('nickname');
        $this->assertEquals('user1', $user1->fresh()->nickname); // User1's nickname should remain unchanged.
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
            'nickname' => 'john_doe', // Duplicate nickname
        ];
        $result = $this->post("/api/users", $data);

        // Assert: Expecting a validation error status code
        $result->assertStatus(422);

        // Optional: Validate that the response includes validation error messages for 'nickname'
        $responseData = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('nickname', $responseData['errors']);
    }



    public function testCaseInsensitiveUniqueNickname()
    {
        // Arrange: Create a user with a lowercase nickname.
        $user1 = User::factory()->create(['nickname' => 'john_doe']);

        // Act: Attempt to register a new user with the same nickname but in uppercase.
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'nickname' => 'JOHN_DOE',
            // Case-insensitive duplicate nickname
            'password' => 'password123',
        ]);

        // Assert: Ensure that the registration fails due to a case-insensitive duplicate nickname.
        $response->assertSessionHasErrors('nickname');
        $this->assertCount(1, User::all()); // Only user1 should exist in the database.
    }

    public function testUpdateRequestFailsWithLongNickname()
    {
        /** @var User $user */
        $user = $this->userFactory->create();
        $data = [
            'id' => $user->id,
            'name' => $this->faker->name,
            'email' => $user->email,
            'nickname' => str_repeat('a', 31), // An invalid long nickname
        ];
        $result = $this->put("/api/users/$user->id", $data);
        $result->assertStatus(422); // Expecting a validation error status code

        // Optional: Validate that the response includes validation error messages for 'nickname'
        $responseData = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('nickname', $responseData['errors']);
    }

    public function testCreateRequestFailsWithInvalidNicknameLength()
    {
        // Act: Attempt to create a new user with an invalid long nickname.
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => 'password123',
            'nickname' => str_repeat('a', 31), // An invalid long nickname
        ];
        $result = $this->post("/api/users", $data);

        // Assert: Expecting a validation error status code
        $result->assertStatus(422);

        // Optional: Validate that the response includes validation error messages for 'nickname'
        $responseData = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('nickname', $responseData['errors']);
    }


}