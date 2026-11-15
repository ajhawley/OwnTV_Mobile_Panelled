package tv.own.owntv.mobile.ui.setup

import android.content.Context
import android.security.keystore.KeyGenParameterSpec
import android.security.keystore.KeyProperties
import android.util.Base64
import org.json.JSONObject
import java.io.File
import java.io.FileOutputStream
import java.security.KeyStore
import java.security.SecureRandom
import java.util.UUID
import javax.crypto.Cipher
import javax.crypto.KeyGenerator
import javax.crypto.SecretKey
import javax.crypto.spec.GCMParameterSpec

/** Installation identity plus the bearer token. The token is always returned in memory only. */
internal data class PanelDeviceCredentials(
    val deviceId: String,
    val token: String,
)

/**
 * Stores a random per-installation identity and token without putting the token in plaintext storage.
 *
 * The token is encrypted with an AES-GCM key generated inside Android Keystore. The key is
 * non-exportable; the encrypted credential file lives in noBackupFilesDir, so Android backup/restore
 * cannot copy the ciphertext to an installation that does not have the original Keystore key.
 * Hardware identifiers are deliberately not collected.
 */
internal class PanelDeviceCredentialStore(context: Context) {
    private val appContext = context.applicationContext
    private val credentialFile = File(appContext.noBackupFilesDir, CREDENTIAL_FILE)

    @Synchronized
    fun getOrCreate(): PanelDeviceCredentials {
        if (credentialFile.exists()) return readExisting()

        val deviceId = UUID.randomUUID().toString()
        val tokenBytes = ByteArray(TOKEN_BYTES).also(SecureRandom()::nextBytes)
        val token = tokenBytes.joinToString(separator = "") { byte -> "%02x".format(byte.toInt() and 0xff) }
        tokenBytes.fill(0)

        val cipher = Cipher.getInstance(TRANSFORMATION)
        cipher.init(Cipher.ENCRYPT_MODE, getOrCreateKey())
        val tokenPlaintext = token.toByteArray(Charsets.UTF_8)
        val encrypted = try {
            cipher.doFinal(tokenPlaintext)
        } finally {
            tokenPlaintext.fill(0)
        }
        val record = JSONObject()
            .put("version", 1)
            .put("device_id", deviceId)
            .put("iv", Base64.encodeToString(cipher.iv, Base64.NO_WRAP))
            .put("token_ciphertext", Base64.encodeToString(encrypted, Base64.NO_WRAP))

        writeAtomically(record.toString())
        return PanelDeviceCredentials(deviceId, token)
    }

    private fun readExisting(): PanelDeviceCredentials {
        try {
            val record = JSONObject(credentialFile.readText(Charsets.UTF_8))
            val deviceId = record.getString("device_id")
            UUID.fromString(deviceId)

            val key = existingKey()
            val iv = Base64.decode(record.getString("iv"), Base64.NO_WRAP)
            val ciphertext = Base64.decode(record.getString("token_ciphertext"), Base64.NO_WRAP)
            val cipher = Cipher.getInstance(TRANSFORMATION)
            cipher.init(Cipher.DECRYPT_MODE, key, GCMParameterSpec(GCM_TAG_BITS, iv))
            val tokenBytes = cipher.doFinal(ciphertext)
            val token = tokenBytes.toString(Charsets.UTF_8)
            tokenBytes.fill(0)

            require(token.matches(Regex("[a-fA-F0-9]{64}"))) { "Stored device token is invalid." }
            return PanelDeviceCredentials(deviceId, token.lowercase())
        } catch (error: Exception) {
            throw IllegalStateException(
                "Saved OwnTV device credentials could not be unlocked. The app data or Android Keystore may have changed. Contact the administrator before resetting this installation.",
                error,
            )
        }
    }

    private fun existingKey(): SecretKey {
        val keyStore = KeyStore.getInstance(ANDROID_KEYSTORE).apply { load(null) }
        return keyStore.getKey(KEY_ALIAS, null) as? SecretKey
            ?: throw IllegalStateException("The saved Android Keystore key is missing.")
    }

    private fun getOrCreateKey(): SecretKey {
        existingKeyOrNull()?.let { return it }
        val generator = KeyGenerator.getInstance(KeyProperties.KEY_ALGORITHM_AES, ANDROID_KEYSTORE)
        generator.init(
            KeyGenParameterSpec.Builder(
                KEY_ALIAS,
                KeyProperties.PURPOSE_ENCRYPT or KeyProperties.PURPOSE_DECRYPT,
            )
                .setBlockModes(KeyProperties.BLOCK_MODE_GCM)
                .setEncryptionPaddings(KeyProperties.ENCRYPTION_PADDING_NONE)
                .setKeySize(256)
                .setRandomizedEncryptionRequired(true)
                .build(),
        )
        return generator.generateKey()
    }

    private fun existingKeyOrNull(): SecretKey? {
        val keyStore = KeyStore.getInstance(ANDROID_KEYSTORE).apply { load(null) }
        return keyStore.getKey(KEY_ALIAS, null) as? SecretKey
    }

    private fun writeAtomically(contents: String) {
        val parent = credentialFile.parentFile ?: error("No private app storage directory is available.")
        check(parent.exists() || parent.mkdirs()) { "Could not create private app storage." }
        val temporary = File(parent, "$CREDENTIAL_FILE.tmp")
        FileOutputStream(temporary).use { output ->
            output.write(contents.toByteArray(Charsets.UTF_8))
            output.fd.sync()
        }
        check(temporary.renameTo(credentialFile)) { "Could not save encrypted device credentials." }
    }

    private companion object {
        const val ANDROID_KEYSTORE = "AndroidKeyStore"
        const val KEY_ALIAS = "owntv.panel.device-token.v1"
        const val CREDENTIAL_FILE = "owntv-panel-device.json"
        const val TRANSFORMATION = "AES/GCM/NoPadding"
        const val TOKEN_BYTES = 32
        const val GCM_TAG_BITS = 128
    }
}
