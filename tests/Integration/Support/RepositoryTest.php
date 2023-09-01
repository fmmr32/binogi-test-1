<?php

namespace Tests\Integration\Support;

use App\Models\User\User;
use App\Repositories\UserRepository;
use Tests\FrameworkTest;

class RepositoryTest extends FrameworkTest
{
    /** @var UserRepository */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
    }

    public function testAll()
    {
        $this->assertEquals(User::get(), $this->repository->all());
    }

    // public function testCreate()
    // {
    //     $user = $this->userFactory->create();
    //     $this->assertInstanceOf(User::class, $user);
    // }
    public function testCreate()
    {
        $userData = [
            'name' => 'James Joe',
            'email' => 'james@example.com',
            'password' => bcrypt('password'),
            'nickname' => 'james', // Include nickname
        ];

        $user = $this->repository->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertEquals($userData['nickname'], $user->nickname); // Ensure 'nickname' is correctly stored
    }
    // public function testUpdate()
    // {
    //     $user = $this->userFactory->create();
    //     $this->assertInstanceOf(User::class, $user);
    //     $this->repository->update(['name' => 'Luke Skywalker'], $user->id);
    //     // update nickname
    //     $this->repository->update(['nickname' => 'Lukey'], $user->id);
    //     $this->assertEquals('Luke Skywalker', $user->refresh()->name);
    //     $this->assertEquals('Lukey', $user->refresh()->nickname);
    // }

    public function testUpdate()
    {
        $user = $this->userFactory->create();
        $this->assertInstanceOf(User::class, $user);

        $updatedData = [
            'name' => 'Luke Skywalker',
            'nickname' => 'Lukey', // Include a valid updated nickname
        ];

        $this->repository->update($updatedData, $user->id);

        $user = $user->refresh();

        $this->assertEquals($updatedData['name'], $user->name);
        $this->assertEquals($updatedData['nickname'], $user->nickname); // Ensure 'nickname' is correctly updated
    }


    public function testDelete()
    {
        $user = $this->userFactory->create();
        $this->repository->delete($user->id);
        $this->assertFalse(User::where('id', $user->id)->exists());
    }

    // public function testFind()
    // {
    //     $user = $this->userFactory->create();
    //     $found = $this->repository->find($user->id);
    //     $this->assertInstanceOf(User::class, $found);
    //     $this->assertEquals($user->id, $found->id);
    // }
    public function testFind()
    {
        $user = $this->userFactory->create();

        $foundUser = $this->repository->find($user->id);

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals($user->nickname, $foundUser->nickname); // Ensure 'nickname' is retrieved correctly
    }

}