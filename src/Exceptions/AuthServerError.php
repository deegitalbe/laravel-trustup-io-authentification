<?php
namespace Deegitalbe\LaravelTrustupIoAuthentification\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Deegitalbe\LaravelTrustupIoAuthentification\Contracts\Exceptions\SkipAuthContextContract;

class AuthServerError extends Exception implements SkipAuthContextContract
{
    protected $message = "Auth server could not handle request.";

    protected $code = 500;

    /** @var Response */
    protected $response;

    /**
     * Setting related response
     * 
     * @param Response $response
     * @return static
     */
    public function setResponse(Response $response): self
    {
        $this->response = $response;
        $this->code = $response->status();

        return $this;
    }

    public function context()
    {
        return [
            "response" => [
                "status" => $this->response->status(),
                "body" => $this->response->body(),
                "message" => $this->response->reason() 
            ]
        ];
    }
}