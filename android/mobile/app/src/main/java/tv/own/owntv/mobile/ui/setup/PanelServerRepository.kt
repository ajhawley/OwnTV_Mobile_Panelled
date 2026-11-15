package tv.own.owntv.mobile.ui.setup

import android.content.Context
import android.os.Build
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import kotlinx.coroutines.withContext
import org.json.JSONArray
import org.json.JSONObject
import tv.own.owntv.mobile.BuildConfig
import java.net.HttpURLConnection
import java.net.URL

data class PanelServer(
    val id: String,
    val name: String,
    val baseUrl: String,
    val sortOrder: Int,
    val enabled: Boolean,
)

enum class PanelDeviceStatus {
    UNKNOWN,
    CHECKING,
    PENDING,
    ACTIVE,
    BLOCKED,
    REVOKED,
    CONFLICT,
}

data class PanelServerUiState(
    val servers: List<PanelServer> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null,
    val deviceStatus: PanelDeviceStatus = PanelDeviceStatus.UNKNOWN,
)

data class PanelDeviceRegistration(val deviceId: String, val status: PanelDeviceStatus)

internal class PanelApiException(
    val httpStatus: Int,
    val errorCode: String?,
    message: String,
) : IllegalStateException(message)

/** OwnTV panel API client. Its device token is stored only via [PanelDeviceCredentialStore]. */
class PanelServerRepository(context: Context) {
    private val credentials = PanelDeviceCredentialStore(context.applicationContext)
    private val registrationMutex = Mutex()

    suspend fun registerDevice(): PanelDeviceRegistration = withContext(Dispatchers.IO) {
        registrationMutex.withLock {
            val saved = credentials.getOrCreate()
            val payload = JSONObject()
                .put("device_id", saved.deviceId)
                .put("device_name", deviceName())
                .put("platform", "android")
                .put("app_version", BuildConfig.VERSION_NAME)

            try {
                val response = request(
                    method = "POST",
                    path = REGISTER_PATH,
                    token = saved.token,
                    body = payload,
                )
                PanelDeviceRegistration(
                    deviceId = response.optString("device_id", saved.deviceId),
                    status = response.optString("status").toDeviceStatus(),
                )
            } catch (error: PanelApiException) {
                val status = when (error.errorCode) {
                    "device_blocked" -> PanelDeviceStatus.BLOCKED
                    "device_revoked" -> PanelDeviceStatus.REVOKED
                    "device_registration_conflict" -> PanelDeviceStatus.CONFLICT
                    else -> throw error
                }
                PanelDeviceRegistration(saved.deviceId, status)
            }
        }
    }

    suspend fun loadServers(): List<PanelServer> = withContext(Dispatchers.IO) {
        val saved = credentials.getOrCreate()
        val root = request("GET", BOOTSTRAP_PATH, saved.token)
        val entries = root.optJSONArray("servers") ?: return@withContext emptyList()
        val parsed = mutableListOf<PanelServer>()

        for (index in 0 until entries.length()) {
            val item = entries.optJSONObject(index) ?: continue
            val id = item.optString("id").trim()
            val name = item.optString("name").trim()
            val baseUrl = item.optString("base_url").trim()
            if (id.isBlank() || name.isBlank() || baseUrl.isBlank()) continue

            val validUrl = try {
                val parsedUrl = URL(baseUrl)
                parsedUrl.host.isNotBlank() &&
                    parsedUrl.protocol.lowercase() in setOf("http", "https")
            } catch (_: Exception) {
                false
            }
            if (!validUrl) continue

            parsed += PanelServer(
                id = id,
                name = name,
                baseUrl = baseUrl.trimEnd('/'),
                sortOrder = item.optInt("sort_order", 0),
                enabled = item.optBoolean("enabled", false),
            )
        }
        parsed.sortedBy { it.sortOrder }
    }

    suspend fun updateProviderAssociations(serverIds: List<String>) = withContext(Dispatchers.IO) {
        val saved = credentials.getOrCreate()
        val ids = JSONArray().apply { serverIds.forEach(::put) }
        request(
            method = "PUT",
            path = PROVIDERS_PATH,
            token = saved.token,
            body = JSONObject().put("server_ids", ids),
        )
    }

    private fun deviceName(): String {
        val manufacturer = Build.MANUFACTURER.orEmpty().trim()
        val model = Build.MODEL.orEmpty().trim()
        return listOf(manufacturer, model)
            .filter { it.isNotBlank() }
            .distinct()
            .joinToString(" ")
            .ifBlank { "Android device" }
            .take(120)
    }

    private fun request(
        method: String,
        path: String,
        token: String,
        body: JSONObject? = null,
    ): JSONObject {
        val connection = URL("$API_BASE$path").openConnection() as HttpURLConnection
        try {
            connection.requestMethod = method
            connection.connectTimeout = TIMEOUT_MS
            connection.readTimeout = TIMEOUT_MS
            connection.setRequestProperty("Accept", "application/json")
            connection.setRequestProperty("Authorization", "Bearer $token")
            connection.setRequestProperty("Cache-Control", "no-store")

            if (body != null) {
                connection.doOutput = true
                connection.setRequestProperty("Content-Type", "application/json; charset=utf-8")
                connection.outputStream.use { output ->
                    output.write(body.toString().toByteArray(Charsets.UTF_8))
                }
            }

            val status = connection.responseCode
            val stream = if (status in 200..299) connection.inputStream else connection.errorStream
            val responseText = stream?.bufferedReader(Charsets.UTF_8)?.use { it.readText() }.orEmpty()
            val response = runCatching { JSONObject(responseText) }.getOrDefault(JSONObject())
            if (status !in 200..299) {
                throw PanelApiException(
                    httpStatus = status,
                    errorCode = response.optString("error").takeIf { it.isNotBlank() },
                    message = response.optString("message").ifBlank { "Panel returned HTTP $status" },
                )
            }
            return response
        } finally {
            connection.disconnect()
        }
    }

    private fun String.toDeviceStatus(): PanelDeviceStatus = when (lowercase()) {
        "pending" -> PanelDeviceStatus.PENDING
        "active" -> PanelDeviceStatus.ACTIVE
        "blocked" -> PanelDeviceStatus.BLOCKED
        "revoked" -> PanelDeviceStatus.REVOKED
        else -> PanelDeviceStatus.UNKNOWN
    }

    private companion object {
        const val API_BASE = "https://outbreak1.ddns.net/owntv/panel/api/v1"
        const val REGISTER_PATH = "/devices/register"
        const val BOOTSTRAP_PATH = "/bootstrap"
        const val PROVIDERS_PATH = "/devices/providers"
        const val TIMEOUT_MS = 12_000
    }
}
