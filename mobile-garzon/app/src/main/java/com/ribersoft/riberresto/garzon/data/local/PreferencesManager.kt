package com.ribersoft.riberresto.garzon.data.local

import android.content.Context
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

private val Context.dataStore by preferencesDataStore(name = "riberresto_garzon_prefs")

class PreferencesManager(private val context: Context) {

    companion object {
        val KEY_SERVER_URL = stringPreferencesKey("server_url")
        val KEY_WAITER_ID = stringPreferencesKey("waiter_id")
        val KEY_WAITER_NAME = stringPreferencesKey("waiter_name")
        val KEY_AUTH_TOKEN = stringPreferencesKey("auth_token")
        const val DEFAULT_SERVER_URL = "http://192.168.0.10:8000/api/v1/"
    }

    val serverUrlFlow: Flow<String> = context.dataStore.data.map { prefs ->
        prefs[KEY_SERVER_URL] ?: DEFAULT_SERVER_URL
    }

    val waiterNameFlow: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_WAITER_NAME]
    }

    val waiterIdFlow: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_WAITER_ID]
    }

    val authTokenFlow: Flow<String?> = context.dataStore.data.map { prefs ->
        prefs[KEY_AUTH_TOKEN]
    }

    suspend fun saveServerUrl(url: String) {
        var clean = url.trim()
        if (!clean.startsWith("http://") && !clean.startsWith("https://")) {
            clean = "http://$clean"
        }
        if (!clean.endsWith("/")) {
            clean = "$clean/"
        }
        if (!clean.contains("/api/v1/")) {
            clean = "${clean}api/v1/"
        }
        context.dataStore.edit { prefs ->
            prefs[KEY_SERVER_URL] = clean
        }
    }

    suspend fun saveSession(waiterId: String, waiterName: String, token: String?) {
        context.dataStore.edit { prefs ->
            prefs[KEY_WAITER_ID] = waiterId
            prefs[KEY_WAITER_NAME] = waiterName
            if (token != null) {
                prefs[KEY_AUTH_TOKEN] = token
            }
        }
    }

    suspend fun clearSession() {
        context.dataStore.edit { prefs ->
            prefs.remove(KEY_WAITER_ID)
            prefs.remove(KEY_WAITER_NAME)
            prefs.remove(KEY_AUTH_TOKEN)
        }
    }
}
