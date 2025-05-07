<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Mate;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $user =  User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        function getProtectedValue($obj, $name) {
            $array = (array)$obj;
            $prefix = chr(0).'*'.chr(0);
            return $array[$prefix.$name];
        }

        $attributes = getProtectedValue($user, 'attributes');
        $user_id = $attributes["id"];

        if (isset($input['newsletter'])) {
            User::where('id', $user_id)->update(['newsletter' => 1]);
        }

        function generateRandomId($length) {
            // with removed i, and l
            $characters = '1234567890abcdefghjkmnpqrstouvwxyz';
            $id = '';
            
            for ($i = 0; $i < $length; $i++) {
                do {
                    $randomIndex = mt_rand(0, strlen($characters) - 1);
                    $nextChar = $characters[$randomIndex];
        
                    // Check if the previous three characters are of the same type (either all letters or all numbers)
                    $sameTypeSequence = $i > 2 && 
                        ctype_alpha($nextChar) == ctype_alpha($id[$i - 1]) &&
                        ctype_alpha($nextChar) == ctype_alpha($id[$i - 2]) &&
                        ctype_alpha($nextChar) == ctype_alpha($id[$i - 3]);
        
                } while ($sameTypeSequence);
        
                $id .= $nextChar;
            }
            
            return $id;
        }
        
        $user_uid = generateRandomId(12);
        
        Mate::create([
            'uid' => $user_uid,
            'user_id' => $user_id,
            'name' => $input['name'],
            'username' => $user_uid,
            'avatar' => $user_uid,
            'hobbies_interests' => '',
            'visited_countries' => '[]'
        ]);

        // Join the Example Coliving Space: app/Listeners/AfterEmailVerified.php

        // Automatically verify the first user (ID = 1)
        if ($user->id === 1) {
            $user->email_verified_at = now();
            $user->save();
        }

        return $user;
    }
}
