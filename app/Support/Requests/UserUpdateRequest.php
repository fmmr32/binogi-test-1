<?php

namespace App\Support\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *   schema="UserUpdateRequest",
 *   description="User Update Request Body",
 *   @OA\Property(
 *      property="name",
 *      type="string",
 *      example="Jane Doe",
 *      description="User name",
 *      minLength=1,
 *      maxLength=191,
 *   ),
 *   @OA\Property(
 *      property="email",
 *      type="string",
 *      minLength=1,
 *      maxLength=191,
 *      description="User email",
 *      example="JaneDoe@email.com",
 *   ),
 *   @OA\Property(
 *      property="password",
 *      type="string",
 *      minLength=1,
 *      maxLength=191,
 *      description="User Password",
 *      example="correct horse battery staple",
 *   ),
 * )
 *
 * Get the validation rules that apply to the request.
 *
 * @return array
 */
class UserUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // validation rules for nickname requirements: unique, shorter than 30 char
        return [
            'name' => 'string|max:191|min:1',
            'password' => 'string|min:8|max:191',
            'nickname' => 'required|string|max:30|unique:users,nickname',
            'email' => [
                'email',
                Rule::unique('users')->ignore(request()->route('user')->id),
            ],
        ];
    }
}