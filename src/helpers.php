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