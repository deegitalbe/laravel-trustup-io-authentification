# TDD implementation slice slice-01 : Boot and report with the package error handler

## Behavior List

1. Handler bound as exception handler resolves as TrustupIoAuthHandler and FlareExceptionHandler, request handled with 500 — AC-1
2. AuthServerError reported: Log::error once with message and context, Flare context receives identifier and response — AC-2
3. Plain RuntimeException reported: no auth server log — AC-2 (regression)
4. Patch changeset — AC-3

## Executed Cycles

### Cycle 1 — boot and reporting
- **RED**: `tests/TrustupIoAuthHandlerTest.php` (5 tests). Run exits 255: `Fatal error: Declaration of ...TrustupIoAuthHandler::register() must be compatible with ...FlareExceptionHandler::register(): void in src/Exceptions/TrustupIoAuthHandler.php on line 16`
- Red commit: not committed (instructed)
- **GREEN**: `src/Exceptions/TrustupIoAuthHandler.php` `register(): void`, docblock removed (comment review); `.changeset/flare-4-register-void.md`
- Green commit: not committed (instructed)
- **REFACTOR**: None

## Affected Files
- src/Exceptions/TrustupIoAuthHandler.php (modified)
- tests/TrustupIoAuthHandlerTest.php (new)
- .changeset/flare-4-register-void.md (new)

## Internal Checks
- vendor/bin/pest : 13 passed (8 existing + 5 new)
- vendor/bin/pint --test src/Exceptions tests .changeset : PASS
- vendor/bin/phpstan analyse --no-progress -l 5 src/Exceptions : no errors
- ./cli lint/analyse/test --strict-coverage/rector : not run: ./cli forbidden, no network

## OpenAPI
No API endpoints affected.

## Unaddressed Findings
None.
