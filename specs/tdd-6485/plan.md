# Plan : Authentication package error handler works with Flare handler 4

## Summary

`TrustupIoAuthHandler::register()` declares no return type while `FlareExceptionHandler::register(): void` (henrotaym/laravel-flare-exception-handler v4), so PHP refuses to load the class. Declare `: void`, prove it with a boot test that installs the handler as the app's exception handler, and release 4.0.2 through the existing changeset workflow.

## Approach

A, minimalism, retained. B (switch the body to `FlareContext::report()`) touches the deprecated helper the spec puts out of scope. C (the provider registers the reporting on any handler) changes behavior for every app that runs `composer update`, including billing and www.trustup.be which already register the same rule; it is a minor release with per app checks, not a patch.

## Existing code reused

-   `src/Exceptions/TrustupIoAuthHandler.php` — the reporting rule (Flare context, `Log::error`, `->stop()`), unchanged apart from the return type.
-   `src/Exceptions/AuthServerError.php` — the `SkipAuthContextContract` exception the provider reports, used as the test subject.
-   `tests/TestCase.php` (Testbench) and `tests/Pest.php` — every test file runs on it.
-   `.github/workflows/github-release.yml` — on push to `main`, `bun changeset version` bumps `package.json` and `CHANGELOG.md`, commits `📝 Release vX`, tags `vX` and creates the GitHub release; Packagist follows the tag.

## File structure

-   `src/Exceptions/TrustupIoAuthHandler.php` (modified) — `register(): void`; the docblock `@return void` goes with it.
-   `tests/TrustupIoAuthHandlerTest.php` (new) — Pest feature test: boot with the handler bound as `Illuminate\Contracts\Debug\ExceptionHandler`, then report an `AuthServerError`.
-   `.changeset/flare-4-register-void.md` (new) — patch bump of `@deegitalbe/laravel-trustup-io-authentification` with the changelog line.

## Sketch

1. Test binds `ExceptionHandler` to `TrustupIoAuthHandler` and resolves it: the class loads with Flare 4 [AC-1]
2. A route throwing a plain exception answers 500 through that handler: the app handles a request [AC-1]
3. `report(AuthServerError)` → the `SkipAuthContextContract` callback sets the Flare context and writes one `Log::error` with the exception context, `->stop()` skips the default log line [AC-2]
4. Merge to `main` → changeset workflow → `v4.0.2` tag, GitHub release, Packagist 4.0.2 [AC-3]

```php
public function register(): void
{
    $this->reportable(function (SkipAuthContextContract $e) {
        $this->reportContextToFlare($e);
        Log::error($e->getMessage(), $this->exceptionContext($e));
    })->stop();

    parent::register();
}
```

## Slices overview

| Slice | Use case | ACs | Depends on |
| ----- | -------- | --- | ---------- |
| slice-01 | An app boots and reports with the package error handler | AC-1, AC-2, AC-3 | None |

## Coverage matrix

| AC  | Slice(s) |
| --- | -------- |
| AC-1 | slice-01 |
| AC-2 | slice-01 |
| AC-3 | slice-01 (changeset), release checked after merge |

## Architectural decisions

| Decision | Alternative rejected | Reason |
| -------- | -------------------- | ------ |
| Patch 4.0.2, signature only | Provider level registration (C) | C changes behavior on `composer update` for every ^4.0 app; 4.0.2 must be safe to pick up with no app change. |
| Bind the handler in the test itself | A second Testbench TestCase overriding `resolveApplicationExceptionHandler` | `tests/Pest.php` applies one TestCase to the whole directory; a container binding in the test proves the same load and dispatch. |
| No CI test job added | A tests workflow | The repo has only the release workflow; adding CI is outside the ticket. The suite runs locally in the repo's own `cli` image. |
