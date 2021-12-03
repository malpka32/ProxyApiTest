<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;

class ProxyControllerTest extends ProxyControllerTestAbstract
{

    use RefreshDatabase;

    /**
     * @dataProvider casesProvider
     */
    public function testProxyApi(bool $loginToApi, string $method, string $content, int $expectedResponseCode): void {
        Config::set('services.external.url', 'http://api.testowanie/');
        $this->setQuery('getUserInfo');

        $this->mockHttpFormWithOptionWithoutVerifyingAttache();
        $this->mockHttpPostAuthWithStatusCodeResponse();
        if($loginToApi) {
            $this->mockHttpWithMethodAndContentAndStatusCodeResponse($method, $content);
        } else {
            $this->mockHttpWithMethodAndContentAndStatusCodeResponse($method, $content, 401);
        }

        $contentToSend = [];
        if($method == 'post') {
            $contentToSend = [
                'file' => UploadedFile::fake()->create('document.doc', 2546)
            ];
        }

        $response = $this->actingAs($this->user)
            ->withHeader('content-type','application/json')
            ->$method(
                $this->getUrlToTest(),
                $contentToSend
            );

        $response->assertHeader('content-type','application/json');
        $this->assertEquals($expectedResponseCode, $response->getStatusCode());
        $this->assertJson($content);
    }

    public function casesProvider(): array {
        return [
            'Send post with success auth' => [true, 'post','{"message": "OK"}', 200],
            'Send post without success auth' => [false, 'post','{"message": "Unauthorized"}', 401],
            'Send get with success auth' => [true, 'get','{"message": "OK"}', 200],
            'Send get without success auth' => [false, 'get','{"message": "Unauthorized"}', 401],
            'Send put with success auth' => [true, 'put','{"message": "OK"}', 200],
            'Send put without success auth' => [false, 'put','{"message": "Unauthorized"}', 401],
        ];
    }
}
