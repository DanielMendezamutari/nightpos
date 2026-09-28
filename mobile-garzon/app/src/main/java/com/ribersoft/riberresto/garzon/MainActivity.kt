package com.ribersoft.riberresto.garzon

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.Surface
import androidx.compose.ui.Modifier
import androidx.navigation.compose.rememberNavController
import com.ribersoft.riberresto.garzon.ui.navigation.AppNavigation
import com.ribersoft.riberresto.garzon.ui.theme.DarkBackground
import com.ribersoft.riberresto.garzon.ui.theme.RiberRestoTheme

class MainActivity : ComponentActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val app = application as RiberRestoApp

        setContent {
            RiberRestoTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = DarkBackground
                ) {
                    val navController = rememberNavController()
                    AppNavigation(
                        navController = navController,
                        preferencesManager = app.preferencesManager,
                        authRepository = app.authRepository,
                        tableRepository = app.tableRepository,
                        orderRepository = app.orderRepository
                    )
                }
            }
        }
    }
}
