<?php
namespace Deegitalbe\LaravelTrustupIoAuthentification\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Deegitalbe\LaravelTrustupIoAuthentification\Contracts\Exceptions\SkipAuthContextContract;

class AuthServerError extends Exception implements SkipAuthContextContract
{
    protected $message = "Auth server could not authenticate user.";

    protected $code = 500;

    /** @var Response */
    protected $response;

    /** @var string|null */
    protected $identifier;

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

    /**
     * Setting related identifier.
     * 
     * @param string|null $identifier
     * @return static
     */
    public function setIdentifier($identifier): self
    {
        $this->identifier = $identifier;
        
        return $this;
    }

    public function context()
    {
        return [
            "identifier" => $this->identifier,
            "response" => [
                "status" => $this->response->status(),
                "body" => $this->response->json(),
                "message" => $this->response->reason() 
            ]
        ];
    }
}