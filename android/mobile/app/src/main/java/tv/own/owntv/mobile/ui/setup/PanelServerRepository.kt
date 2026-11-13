package tv.own.owntv.mobile.ui.setup

import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL

data class PanelServer(
    val id: String,
    val name: String,
    val baseUrl: String,
    val sortOrder: Int,
)

data class PanelServerUiState(
    val servers: List<PanelServer> = emptyList(),
    val isLoading: Boolean = false,
    val error: String? = null,
)

class PanelServerRepository {

    suspend fun loadServers(): List<PanelServer> = withContext(Dispatchers.IO) {
        val connection = URL(BOOTSTRAP_URL).openConnection() as HttpURLConnection

        try {
            connection.requestMethod = "GET"
            connection.connectTimeout = 12_000
            connection.readTimeout = 12_000
            connection.setRequestProperty("Accept", "application/json")

            val status = connection.responseCode
            if (status !in 200..299) {
                throw IllegalStateException("Panel returned HTTP $status")
            }

            val body = connection.inputStream
                .bufferedReader(Charsets.UTF_8)
                .use { it.readText() }

            val root = JSONObject(body)
            val entries = root.optJSONArray("servers") ?: return@withContext emptyList()

            val parsed = mutableListOf<PanelServer>()

            for (index in 0 until entries.length()) {
                val item = entries.optJSONObject(index) ?: continue
                if (!item.optBoolean("enabled", false)) continue

                val id = item.optString("id").trim()
                val name = item.optString("name").trim()
                val baseUrl = item.optString("base_url").trim()
                if (name.isBlank() || baseUrl.isBlank()) continue

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
                )
            }

            parsed.sortedBy { it.sortOrder }
        } finally {
            connection.disconnect()
        }
    }

    private companion object {
        const val BOOTSTRAP_URL =
            "https://outbreak1.ddns.net/owntv/panel/api/v1/bootstrap"
    }
}
