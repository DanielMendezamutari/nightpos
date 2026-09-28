package com.ribersoft.riberresto.garzon.ui.screens.settings

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Error
import androidx.compose.material.icons.filled.Lan
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.ribersoft.riberresto.garzon.data.api.NetworkClient
import com.ribersoft.riberresto.garzon.data.local.PreferencesManager
import com.ribersoft.riberresto.garzon.ui.theme.*
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun SettingsScreen(
    preferencesManager: PreferencesManager,
    onBack: () -> Unit
) {
    val coroutineScope = rememberCoroutineScope()
    var serverInput by remember { mutableStateOf("") }
    var isTesting by remember { mutableStateOf(false) }
    var testResult by remember { mutableStateOf<Pair<Boolean, String>?>(null) }

    LaunchedEffect(Unit) {
        val currentUrl = preferencesManager.serverUrlFlow.first()
        serverInput = currentUrl
            .replace("http://", "")
            .replace("https://", "")
            .replace("/api/v1/", "")
            .replace("/", "")
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Configuración de Red Local", fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Volver", tint = TextPrimary)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = DarkSurface,
                    titleContentColor = TextPrimary
                )
            )
        },
        containerColor = DarkBackground
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .padding(paddingValues)
                .padding(24.dp)
                .fillMaxSize(),
            verticalArrangement = Arrangement.spacedBy(20.dp)
        ) {
            Card(
                shape = RoundedCornerShape(16.dp),
                colors = CardDefaults.cardColors(containerColor = DarkSurface)
            ) {
                Column(
                    modifier = Modifier.padding(20.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(10.dp)
                    ) {
                        Icon(Icons.Default.Lan, contentDescription = null, tint = Primary)
                        Text(
                            text = "Servidor POS Local (PC Caja)",
                            style = MaterialTheme.typography.titleMedium,
                            fontWeight = FontWeight.Bold,
                            color = TextPrimary
                        )
                    }

                    Text(
                        text = "Ingresa la dirección IP y puerto del servidor local en el restaurante (ej: 192.168.0.10:8000). Asegúrate de estar conectado a la misma red Wi-Fi.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = TextSecondary
                    )

                    OutlinedTextField(
                        value = serverInput,
                        onValueChange = {
                            serverInput = it
                            testResult = null
                        },
                        label = { Text("IP y Puerto del Servidor") },
                        placeholder = { Text("192.168.0.10:8000") },
                        modifier = Modifier.fillMaxWidth(),
                        singleLine = true,
                        shape = RoundedCornerShape(12.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = Primary,
                            unfocusedBorderColor = DarkBorder,
                            focusedTextColor = TextPrimary,
                            unfocusedTextColor = TextPrimary
                        )
                    )
                }
            }

            // Test Result indicator
            testResult?.let { (success, msg) ->
                Surface(
                    shape = RoundedCornerShape(12.dp),
                    color = if (success) StatusFree.copy(alpha = 0.15f) else StatusError.copy(alpha = 0.15f),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Row(
                        modifier = Modifier.padding(16.dp),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
                        Icon(
                            imageVector = if (success) Icons.Default.CheckCircle else Icons.Default.Error,
                            contentDescription = null,
                            tint = if (success) StatusFree else StatusError
                        )
                        Text(
                            text = msg,
                            color = if (success) StatusFree else StatusError,
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Medium
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.weight(1f))

            // Probar Conexión
            OutlinedButton(
                onClick = {
                    isTesting = true
                    coroutineScope.launch {
                        try {
                            val clean = "http://${serverInput.trim()}/api/v1/"
                            val service = NetworkClient.getService(clean)
                            val resp = service.getTables()
                            if (resp.isSuccessful) {
                                testResult = Pair(true, "¡Conexión exitosa con el servidor POS!")
                            } else {
                                testResult = Pair(false, "El servidor respondió con código: ${resp.code()}")
                            }
                        } catch (e: Exception) {
                            testResult = Pair(false, "No se pudo conectar: ${e.localizedMessage ?: "Tiempo de espera agotado"}")
                        } finally {
                            isTesting = false
                        }
                    }
                },
                modifier = Modifier
                    .fillMaxWidth()
                    .height(52.dp),
                shape = RoundedCornerShape(14.dp),
                enabled = !isTesting && serverInput.isNotBlank()
            ) {
                if (isTesting) {
                    CircularProgressIndicator(modifier = Modifier.size(24.dp), color = Primary)
                } else {
                    Text("Probar Conexión", color = Primary, fontWeight = FontWeight.Bold)
                }
            }

            // Guardar y Volver
            Button(
                onClick = {
                    coroutineScope.launch {
                        preferencesManager.saveServerUrl(serverInput)
                        onBack()
                    }
                },
                modifier = Modifier
                    .fillMaxWidth()
                    .height(52.dp),
                colors = ButtonDefaults.buttonColors(containerColor = Primary),
                shape = RoundedCornerShape(14.dp),
                enabled = serverInput.isNotBlank()
            ) {
                Text("Guardar y Continuar", fontWeight = FontWeight.Bold)
            }
        }
    }
}
