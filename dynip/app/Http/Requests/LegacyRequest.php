<?php

namespace App\Http\Requests;

use App\Models\Host;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class LegacyRequest extends FormRequest
{
    protected function failedValidation(Validator $validator) {
        dump($validator->errors());
        return response()->view('legacy', [
            'message' => 'Unprocessable Entity',
            'errors' => $validator->errors(),
        ])->setStatusCode(422);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $password = $this->input('pass');
        $hostname = $this->input('host');
        if ($password && $hostname) {
            $host = Host::where('name', $hostname)->first();
            /**
             * @var Host $host
             */
            if ($host) {
                if (!password_verify($password, $host->password)) {
                    Log::debug("password check failed with $password for $hostname / {$host->password}");
                    return false;
                }
            }
            return true;
        }
        return false;
    }
}
