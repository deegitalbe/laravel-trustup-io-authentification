# Product Spec : Authentication package error handler works with Flare handler 4

Ticket : TDD-6485, branch `hotfix/tdd-6485-flare-4-register`

## Problem Statement

The shared TrustUp authentication package ships an error handler that applications can build their own error handler on. It adds one behavior: when the central authentication server fails to authenticate a user, the failure is sent to Flare and written to the application log with its context, once.

The package requires the Flare error handler package in its major version 4. Since that version, the error handler built into the authentication package can no longer be loaded at all: the application stops on a fatal error as soon as it reaches it, before answering any request. Version 4.0.1, the latest release, has this defect.

During the move to Laravel 12 on the night of 2026-10-21/22, three applications (billing, www.trustup.be and translations) had to stop building on it and build on the Flare handler directly, two of them copying its behavior by hand. The package's own test suite stayed green because no test ever loads that handler.

Leaving it alone keeps a broken building block in the package, so the next application that builds on it fails at boot.

## Actors

-   Application : any TrustUp application that uses the authentication package with the Flare handler 4 and builds its error handler on the one the package provides

## Acceptance Criteria

### AC-1 : An application boots with the package error handler

-   **Who** : Application
-   **Given** : the application uses the authentication package and the Flare handler 4, and its error handler is the one the package provides
-   **When** : the application starts and handles a request
-   **Then** : the system shall start without a fatal error and handle the request

### AC-2 : Authentication server failures are still reported once

-   **Who** : Application
-   **Given** : the application runs with the package error handler as in AC-1
-   **When** : the central authentication server fails to authenticate a user
-   **Then** : the system shall send the failure to Flare and write it to the application log with its context, and shall not report it a second time through the default reporting

### AC-3 : A fixed version is published

-   **Who** : Application
-   **Given** : AC-1 and AC-2 hold and the package's tests prove them with the Flare handler 4
-   **When** : the change is merged
-   **Then** : the system shall publish the package as version 4.0.2, visible on Packagist and as a GitHub release, with a changelog entry

## Edge Cases

-   An application that builds on the Flare handler directly (the workaround of that night) : it keeps working unchanged with 4.0.2.
-   An application on an older major version of the package (0.x, 1.x, 2.x) with an older Flare handler : not affected, nothing changes for it.

## Out of Scope

-   Upgrading any application to 4.0.2 or reverting its workaround: each application is listed in the ticket with a recommendation, and an upgrade is that application's own production change.
-   Replacing the deprecated Flare reporting helper the package handler still calls.
-   Any other change to the package's authentication behavior.
