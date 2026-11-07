<?php

namespace App\Http\Controllers;

use App\Enums\LegacyAction;
use App\Http\Requests\LegacyRequest;
use App\Models\Host;
use App\Models\Mapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LegacyController extends Controller
{
    public function submit(LegacyRequest $request) {
        $validator = Validator::make($request->all(), [
            'host' => ['string', 'required', 'exists:hosts,name'],
            'pass' => ['string', 'required'],
            'action' => [
                'string',
                'required',
                Rule::enum(LegacyAction::class),
            ],
            'ip' => ['sometimes', 'ip'],
            'ipv4' => ['sometimes', 'ipv4', 'prohibits:ip'],
            'ipv6' => ['sometimes', 'ipv6', 'prohibits:ip'],
        ]);
        if ($validator->fails()) {
            return response()->view('legacy', [
                'message' => 'Unprocessable Entity',
                'errors' => $validator->errors(),
            ], 422);
        }
        $hostname = $request->input('host');
        $action = LegacyAction::from($request->input('action'));

        if ($action == LegacyAction::Register) {
            if (!($ip = $request->input('ip'))) {
                $ip = $request->ip();
            }
            $family = AF_INET;
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $family = AF_INET6;
            }
            $host = Host::where([
                'name' => $hostname,
            ])->first();
            $host_id = $host->id;
            $result = Mapping::updateOrCreate(
                [
                    'host_id' => $host_id,
                    'family' => $family,
                ],
                [
                    'address' => $ip,
                    'family' => $family,
                    'host_id' => $host_id,
                ]
            );
            if ($result->wasRecentlyCreated) {
                return response()->view('legacy', [
                    'message' => "dyndns entry created for {$ip}.",
                ])->setStatusCode(201);
            }
            return response()->view('legacy', [
                'message' => "dyndns entry updated with {$ip}.",
            ])->setStatusCode(200);
        }
        return back();
    }
}
