<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Utils;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function proxy(Request $request, CookieJar $cookieJar, string $query): Response|Application|ResponseFactory
    {
        $user = $request->user();

        $authURL = config('services.external.url') . "/auth/" . $query;
        $apiURL = config('services.external.url') . "/api/" . $query;

        $options = [
            "cookies" => $cookieJar
        ];
        $attachment = [
            "file",
            $request->file("file")?->getContent(),
            $request->file("file")?->getClientOriginalName(),
            []
        ];
        $method = strtolower($request->method());

        try {
            $queryBody = Utils::jsonDecode(
                $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Exception) {
            $queryBody = [];
        }

        Http::asForm()
            ->withOptions($options)
            ->withoutVerifying()
            ->post($authURL, $user);

        $response = Http::withOptions($options)->withoutVerifying();

        !$request->hasFile("file")
            ?: $response->attach(...$attachment);

        $response = $response->$method($apiURL, $queryBody);

        return response($response)->header(
            "Content-Type",
            $response->header("Content-Type")
        );
    }
}
