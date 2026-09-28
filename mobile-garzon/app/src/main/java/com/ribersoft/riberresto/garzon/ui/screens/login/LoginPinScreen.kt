package com.ribersoft.riberresto.garzon.ui.screens.login

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Badge
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.hapticfeedback.HapticFeedbackType
import androidx.compose.ui.platform.LocalHapticFeedback
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.ribersoft.riberresto.garzon.data.repository.AuthRepository
import com.ribersoft.riberresto.garzon.ui.components.PinDisplay
import com.ribersoft.riberresto.garzon.ui.components.PinKeypad
import com.ribersoft.riberresto.garzon.ui.theme.*
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

@Composable
fun LoginPinScreen(
    authRepository: AuthRepository,
    onLoginSuccess: () -> Unit,
    onNavigateToSettings: () -> Unit
) {
    val coroutineScope = rememberCoroutineScope()
    val haptic = LocalHapticFeedback.current

    var pin by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    var isError by remember { mutableStateOf(false) }

    fun submitPin(currentPin: String) {
        if (isLoading) return
        isLoading = true
        errorMessage = null
        isError = false

        coroutineScope.launch {
            val result = authRepository.login(currentPin)
            isLoading = false
            if (result.isSuccess) {
                haptic.performHapticFeedback(HapticFeedbackType.TextHandleMove)
                onLoginSuccess()
            } else {
                haptic.performHapticFeedback(HapticFeedbackType.LongPress)
                isError = true
                errorMessage = result.exceptionOrNull()?.localizedMessage ?: "PIN Incorrecto"
                delay(1000)
                pin = ""
                isError = false
            }
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(DarkBackground)
    ) {
        // Settings Button
        IconButton(
            onClick = onNavigateToSettings,
            modifier = Modifier
                .align(Alignment.TopEnd)
                .padding(16.dp)
        ) {
            Icon(Icons.Default.Settings, contentDescription = "Configuración IP", tint = TextSecondary)
        }

        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(horizontal = 24.dp, vertical = 32.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.SpaceBetween
        ) {
            // Header
            Column(
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.spacedBy(8.dp),
                modifier = Modifier.padding(top = 16.dp)
            ) {
                Box(
                    modifier = Modifier
                        .size(72.dp)
                        .clip(CircleShape)
                        .background(Primary.copy(alpha = 0.15f)),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Default.Badge,
                        contentDescription = null,
                        tint = Primary,
                        modifier = Modifier.size(38.dp)
                    )
                }

                Text(
                    text = "Acceso Garzón",
                    style = MaterialTheme.typography.headlineMedium,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary
                )

                Text(
                    text = "Ingresa tu PIN de 4 dígitos",
                    style = MaterialTheme.typography.bodyMedium,
                    color = TextSecondary
                )

                // Error message
                if (errorMessage != null) {
                    Text(
                        text = errorMessage!!,
                        color = StatusError,
                        fontWeight = FontWeight.SemiBold,
                        fontSize = 14.sp,
                        modifier = Modifier.padding(top = 4.dp)
                    )
                }
            }

            // PIN Display
            if (isLoading) {
                CircularProgressIndicator(
                    modifier = Modifier.size(36.dp),
                    color = Primary
                )
            } else {
                PinDisplay(
                    pinLength = pin.length,
                    maxDigits = 4,
                    isError = isError
                )
            }

            // Tactile Keypad
            PinKeypad(
                onDigitClick = { digit ->
                    if (pin.length < 4 && !isLoading) {
                        val newPin = pin + digit
                        pin = newPin
                        if (newPin.length == 4) {
                            submitPin(newPin)
                        }
                    }
                },
                onDeleteClick = {
                    if (pin.isNotEmpty() && !isLoading) {
                        pin = pin.dropLast(1)
                        errorMessage = null
                    }
                },
                onClearClick = {
                    pin = ""
                    errorMessage = null
                    isError = false
                },
                modifier = Modifier.padding(bottom = 16.dp)
            )
        }
    }
}
