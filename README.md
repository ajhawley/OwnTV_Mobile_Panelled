# OwnTV Custom

A single-repository workspace for the customised Android IPTV player and its management panel.

## Repository layout

- `android/mobile/` — Android phone/tablet application (its own Gradle build).
- `android/core/` — shared Core and player engine (its own Gradle build).
- `server/` — Laravel management panel and API starter.
- `docs/` — project specification and Android API contract.
- `LICENSE` and `UPSTREAM_NOTICES.md` — GPLv3 licence and upstream notices.

## Android build requirements

The uploaded Core project specifies **JDK 25** and an Android SDK. Install JDK 25 before
attempting a build. This environment only had JDK 21, so no Android build is claimed here.

From the repository root:

```bash
cd android/mobile
./gradlew :app:assembleStandardDebug
```

The Mobile Gradle settings have been adjusted to include the sibling `../core` checkout as a
composite build automatically. You do not need to publish Core to Maven or set
`owntv.corePath` for this monorepo. An explicit `owntv.corePath` Gradle property still overrides
the bundled sibling path if you intentionally want to use a different Core checkout.

Core also contains its own Gradle wrapper and can be built independently:

```bash
cd android/core
./gradlew :core:testDebugUnitTest :player-core:testDebugUnitTest
```

## Server

The Laravel starter is in `server/`. Follow `server/README.md` and the deployment notes under
`server/deploy/`. Replace the placeholder host `paneldomain.com` with the real hostname only
when DNS and HTTPS are configured.

## Current status

This package is the source import / monorepo foundation. It preserves the uploaded Mobile and
Core projects and makes the local Core composite build resolve inside this repository. The
agreed app changes (panel-driven login, Xtream-only onboarding, launch-time synchronisation,
availability checks, maintenance notices, and centralised rebranding) are the next
implementation phase; they are **not claimed to be implemented by this import package**.

Do not overwrite a live Android project with selected files from this archive yet. Keep this
archive as the working source baseline and take a backup before applying subsequent patch
packages.
