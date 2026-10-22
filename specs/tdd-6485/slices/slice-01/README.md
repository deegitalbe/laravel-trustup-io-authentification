# Slice 1: An app boots and reports with the package error handler

## Covered ACs

-   **AC-1**: an app whose exception handler is `TrustupIoAuthHandler`, with henrotaym/laravel-flare-exception-handler v4 installed, loads it and handles a request.
-   **AC-2**: an `AuthServerError` (a `SkipAuthContextContract`) reported through that handler is sent to Flare and logged once with its context, and the default reporting does not log it again.
-   **AC-3**: a patch changeset makes the release workflow publish 4.0.2.

## Dependencies

None.

## Objective

`src/Exceptions/TrustupIoAuthHandler.php` declares `public function register()` with no return type, while its parent `Henrotaym\LaravelFlareExceptionHandler\FlareExceptionHandler::register(): void` (v4, installed in `vendor/`) has one. PHP then fails with "Declaration of ... must be compatible" the moment the class loads; `php -r` loading it today exits 255, and `phpstan analyse -l 5 src/Exceptions` reports it. Add `: void`, add the boot and reporting test (red first: it must fail on the current code), and add the changeset.

## Files

-   `src/Exceptions/TrustupIoAuthHandler.php` (modified) — `public function register(): void`; remove the docblock, it only restated the method. Nothing else changes in the body.
-   `tests/TrustupIoAuthHandlerTest.php` (new) — Pest test, same style as `tests/ImpersonateTest.php`; runs on `tests/TestCase.php` through `tests/Pest.php`. Bind `Illuminate\Contracts\Debug\ExceptionHandler` to `TrustupIoAuthHandler` as a singleton in the test.
-   `.changeset/flare-4-register-void.md` (new) — frontmatter `"@deegitalbe/laravel-trustup-io-authentification": patch`, body one line: `Declare register(): void in TrustupIoAuthHandler so apps extending it boot with henrotaym/laravel-flare-exception-handler 4`.

## Expected tests

-   Nominal boot: Given the handler bound as the app's exception handler, When it is resolved, Then it is a `TrustupIoAuthHandler` and a `FlareExceptionHandler`.
-   Nominal request: Given a test route that throws a `RuntimeException`, When the test calls it, Then the response is 500 (the handler rendered it).
-   Reporting rule: Given `Log` spied or mocked, When an `AuthServerError` built with a fake `Illuminate\Http\Client\Response` (e.g. `Http::fake` 401 then a real client call, or `new Response(new \GuzzleHttp\Psr7\Response(401, [], '{}'))`) and an identifier is reported via `report()`, Then `Log::error` is called exactly once with `'Auth server could not authenticate user.'` and a context containing the identifier and the response status.
-   Flare context: same Given, Then `Spatie\LaravelIgnition\Facades\Flare::context` receives the exception context keys (`identifier`, `response`); mock the facade.
-   Regression: Given a plain `RuntimeException` reported, Then the SkipAuth callback is not used (no `Log::error` with the auth message).
-   The test file must fail (fatal or red) on the unfixed `register()`; record that run.

## Verification

Run in the package image (already built as `laravel-trustup-io-authentification-tdd-6485-cli`, no network needed), from the repo root:

-   [ ] `docker run --rm --network none -v $PWD:/opt/apps/app laravel-trustup-io-authentification-tdd-6485-cli vendor/bin/pest` — all tests pass (8 existing + new)
-   [ ] same image, `vendor/bin/pint --test src/Exceptions tests .changeset` — PASS (pint fails on other untouched files already; do not reformat them)
-   [ ] same image, `vendor/bin/phpstan analyse --no-progress -l 5 src/Exceptions` — no error (today: 1 error, the covariance one)
-   [ ] `git status` — only the three files above changed or added; no `composer.lock`, `vendor`, or `.phpunit` files
