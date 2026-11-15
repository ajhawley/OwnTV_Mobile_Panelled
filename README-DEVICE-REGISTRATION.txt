OwnTV Android device-registration integration patch

Apply this archive from the root of the existing repository (the directory containing
android/ and .github/). It contains only the Android source files changed for this feature.

What it adds:
- A random per-installation UUID and 32-byte bearer token.
- AES-256-GCM encryption of the token using a non-exportable Android Keystore key.
- Credential ciphertext stored in app-private noBackupFilesDir (the app already has allowBackup=false).
- Device registration at app launch and when opening the Xtream source form.
- Pending/active/blocked/revoked/conflict status in the Add Playlist form.
- Provider-association sync for the device's locally configured Xtream sources.

The app uses HTTPS for the OwnTV panel API. No IPTV username or password is sent to the
OwnTV admin panel. The API token is never written in plaintext to storage or logs.

IMPORTANT DEPLOYMENT ORDER:
1. Apply this patch to the current source tree.
2. Run the Android i18n check.
3. Commit and push; let GitHub Actions build the APK.
4. Install the updated APK and check the admin Devices page. A new installation should appear
   as Pending. Approve it in the panel.
5. In the app's Add Playlist > Type it in screen, tap Check approval. The app should report
   Active and populate enabled provider choices.
6. Only after this works should OWNTV_DEVICE_AUTH_REQUIRED be enabled on the panel.

Do NOT enable OWNTV_DEVICE_AUTH_REQUIRED before the updated APK is installed and tested.

Scope note: this integration registers the device, checks approval and protects panel API
requests when server-side enforcement is enabled. It does not yet create a universal local
playback lock for already-cached IPTV playlists. A device block can reject protected panel
requests, but direct connections to IPTV providers are not centrally proxied by OwnTV.
