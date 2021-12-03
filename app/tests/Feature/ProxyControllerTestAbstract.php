<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProxyControllerTestAbstract extends TestCase
{
    protected
        $user,
        $query;

    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function mockHttpFormWithOptionWithoutVerifying() {
        Http::shouldReceive('asForm')->andReturnSelf();
        Http::shouldReceive('withOptions')->withAnyArgs()->andReturnSelf();
        Http::shouldReceive('withoutVerifying')->andReturnSelf();
        Http::shouldReceive('attach')->andReturnSelf();
    }

    protected function mockHttpFormWithOptionWithoutVerifyingAttache() {
        $this->mockHttpFormWithOptionWithoutVerifying();
        Http::shouldReceive('attach')->andReturnSelf();
    }

    protected function mockHttpPostAuthWithStatusCodeResponse($statusCode = 200) {
        $authUrl = $this->getAuthUrlWithQuery();
        Http::shouldReceive('post')
            ->withSomeOfArgs($authUrl)
            ->andReturn(
                new Response('', $statusCode)
            );
    }

    protected function mockHttpWithMethodAndContentAndStatusCodeResponse($method, $content, $statusCode = 200) {
        Http::shouldReceive($method)
            ->andReturn(
                new Response(
                    $content,
                    $statusCode,
                    ['content-type' => 'application/json']
                )
            );
    }

    protected function getAuthUrlWithQuery() : string {
        return config('services.external.url') . "/auth/" . $this->query;
    }

    protected function getAuthUrlWithoutQuery() : string {
        return config('services.external.url') . "/auth/";
    }

    protected function setQuery(string $query) {
        $this->query = $query;
    }

    protected function getUrlToTest() {
        return 'api/proxy/'.$this->query;
    }
}
