<?php

if ( ! function_exists('get_trustup_io_authentification_redirection_url') )
{

    function get_trustup_io_authentification_redirection_url(string $path = null, array $params = []): string
    {
        $params = array_merge($params, [
            'callback' => request()->fullUrl(),
        ]);
        $url = rtrim(config('trustup-io-authentification.url').'/'.$path, '/');

        return $url . '?' . http_build_query($params);
    }

}

if ( ! function_exists('get_trustup_io_authentification_update_locale_url') )
{

    function get_trustup_io_authentification_update_locale_url(string $locale): string
    {
        return get_trustup_io_authentification_redirection_url('update-locale', ['locale' => $locale]);
    }

}

if ( ! function_exists('get_trustup_io_authentification_invalid_role_url') )
{

    function get_trustup_io_authentification_invalid_role_url(): string
    {
        return get_trustup_io_authentification_redirection_url('errors/invalid-role');
    }

}

if ( ! function_exists('get_trustup_io_authentification_base_url') )
{
    /**
     * Docker compatible url.
     * 
     * Docker is unable to make server to server calls using "https://xxxx".
     * We have to use service name if docker is activated in configuration.
     */
    function get_trustup_io_authentification_base_url(): string
    {
        $isUsingDocker = filter_var(
            config('trustup-io-authentification.docker.activated'),
            FILTER_VALIDATE_BOOLEAN
        );

        return $isUsingDocker
            ? config('trustup-io-authentification.docker.service')
            : config('trustup-io-authentification.url');
    }
}