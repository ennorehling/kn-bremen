<?php

namespace App\Http\Controllers;

use App\Enums\LegacyAction;
use App\Http\Requests\LegacyRequest;
use App\Models\Host;
use App\Models\Mapping;
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
            'ipv4' => ['sometimes', 'ipv4', 'prohibits:ip,ipv6'],
            'ipv6' => ['sometimes', 'ipv6', 'prohibits:ip,ipv4'],
            'norebuild' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->view('legacy', [
                'message' => 'Unprocessable Entity',
                'errors' => $validator->errors(),
            ], 422);
        }
        $hostname = $request->input('host');
        $action = LegacyAction::from($request->input('action'));
        $host = Host::query()->where([
            'name' => $hostname,
        ])->first();
        $host_id = $host->id;

        if ($action == LegacyAction::UnRegister) {
            Mapping::query()->where('host_id', '=', $host_id)->delete();
            return response()->noContent();
        }
        elseif ($action == LegacyAction::Register) {
            if ($ip = $request->input('ipv4')) {
                $family = AF_INET;
            }
            elseif ($ip = $request->input('ipv6')) {
                $family = AF_INET6;
            }
            else {
                $ip = $request->input('ip');
                if (! $ip) {
                    $ip = $request->ip();
                }
                $family = AF_INET;
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $family = AF_INET6;
                }
            }
            $result = Mapping::query()->updateOrCreate(
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
