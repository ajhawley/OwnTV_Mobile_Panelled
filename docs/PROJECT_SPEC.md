# OwnTV Custom — initial project specification

## Product goal

Create a rebranded Android IPTV player for personal use and invited friends. Users select an IPTV server from a centrally managed list and enter their own Xtream Codes username and password.

## Android application

- Use the existing OwnTV Mobile app and its OwnTV Core library as the starting point.
- Combine Mobile, Core and the management panel in one monorepo when the source trees are imported, preserving upstream history where practical and retaining all GPLv3 licence and copyright notices.
- Keep the mobile and core projects as separate Gradle builds under `android/mobile` and `android/core`; point the mobile build's existing `owntv.corePath` composite-build property at the local Core directory.
- Replace the existing provider setup/onboarding with a panel-driven flow.
- Support Xtream Codes login only in the customised app. Remove M3U and Stalker paths from navigation and disable them in application logic, without deleting shared playback/database code still required by the app.
- Remove visible OwnTV branding from the customised product interface and centralise all visible brand text in Android resources (for example `branding.xml` and `strings.xml`). Retain required legal attribution and GPLv3 notices.
- Let the user save multiple provider accounts. Associate each credential pair with the stable server UUID, not the editable provider domain. Encrypt credentials and cached server configuration with Android Keystore-backed keys.
- On every cold app launch, fetch the current bootstrap configuration over HTTPS before initiating an IPTV login. Refresh after a prolonged background period as well.
- Reconcile the complete server list atomically. A changed domain updates the saved server record without deleting that server's saved username/password. A deleted server is removed; an unavailable/disabled server cannot be selected for a new connection.
- Check the Xtream API from the Android device using that user's credentials. Distinguish connection failure, invalid credentials and successful authentication. Never log the Xtream password or full credential-bearing URL.
- If the panel is unavailable, retain the last valid configuration, show that it may be stale, and offer retry. Do not erase saved credentials or cache because synchronisation failed.
- Present general and server-specific panel maintenance notices on the login screen.

## Management panel/API

- Laravel 13, PHP 8.3+, SQLite, Nginx and HTTPS on the Ubuntu 24.04 VPS.
- Placeholder panel URL during development: `https://paneldomain.com/panel/`.
- One administrator account; no friend/customer accounts and no storage of Xtream credentials.
- Server fields: stable UUID, display name, base URL (including protocol/port), enabled flag, sort order and timestamps.
- Encrypt provider base URLs at rest using Laravel's `APP_KEY`.
- Public, read-only Android bootstrap endpoint at `/panel/api/v1/bootstrap`.
- Every bootstrap response includes panel installation ID, configuration update timestamp, checksum, all server records (including enabled state), and currently active maintenance messages.
- Configuration timestamp is touched on every server/message create, edit, enable/disable or delete. Checksum is computed from the actual returned payload.
- A newly created metadata record gets a new random installation ID. A normal database restore restores the previous ID. If the panel database is lost and recreated, apps detect the new ID and perform a full sync.
- Maintenance messages support general or server-specific targeting, information/warning/urgent type, enabled state and optional start/end scheduling.
- No forced app-update or minimum-version system in the first release.

## Synchronisation semantics

1. App calls the bootstrap endpoint on each cold launch.
2. If installation ID differs, do a full replacement of cached configuration.
3. Otherwise compare checksum. If different, validate the full payload, then replace cache in one local transaction.
4. Timestamp is useful for diagnostics and change ordering; checksum is the final content comparison. Never rely on timestamp alone (a restored database can have an older timestamp).
5. If the response is malformed or validation fails, retain the previous valid cache.

## Security and operational boundaries

- The management API never receives or proxies provider credentials or IPTV traffic.
- HTTPS is mandatory for the panel.
- Encrypt server URLs in the panel database; use file permissions and encrypted/offsite backups.
- Back up SQLite and protect the `.env` encryption key separately. A database backup without the matching `APP_KEY` cannot decrypt the provider URLs.
- A provider URL returned to an app cannot be made secret from a determined user/device owner. The agreed protection is basic deterrence, TLS in transit and encryption at rest, not a reverse proxy or strong secrecy.
- Avoid exposing Laravel `.env`, database files, logs, backups or storage directories through Nginx. Serve only the Laravel `public/` directory.

## Out of scope for first release

- Forced app updates or minimum app version enforcement.
- Friend accounts or central storage of IPTV credentials.
- Reverse proxying IPTV streams through the VPS.
- Provider health-checking from the management VPS (the Android app checks from the user's device).
