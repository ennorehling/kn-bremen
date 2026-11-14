<?php

namespace App\Http\Requests;

use App\Models\Host;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class LegacyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $password = $this->input('pass');
        $hostname = $this->input('host');
        if ($password && $hostname) {
            $host = Host::query()->where('name', $hostname)->first();
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
