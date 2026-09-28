package com.ribersoft.riberresto.garzon.ui.navigation

import androidx.compose.runtime.*
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.navArgument
import com.ribersoft.riberresto.garzon.data.local.PreferencesManager
import com.ribersoft.riberresto.garzon.data.model.CartItem
import com.ribersoft.riberresto.garzon.data.repository.AuthRepository
import com.ribersoft.riberresto.garzon.data.repository.OrderRepository
import com.ribersoft.riberresto.garzon.data.repository.TableRepository
import com.ribersoft.riberresto.garzon.ui.screens.cart.CartScreen
import com.ribersoft.riberresto.garzon.ui.screens.login.LoginPinScreen
import com.ribersoft.riberresto.garzon.ui.screens.menu.MenuScreen
import com.ribersoft.riberresto.garzon.ui.screens.settings.SettingsScreen
import com.ribersoft.riberresto.garzon.ui.screens.splash.SplashScreen
import com.ribersoft.riberresto.garzon.ui.screens.tables.TableListScreen

sealed class Screen(val route: String) {
    object Splash : Screen("splash")
    object Settings : Screen("settings")
    object Login : Screen("login")
    object Tables : Screen("tables")
    object Menu : Screen("menu/{tableId}/{tableName}") {
        fun createRoute(tableId: Long, tableName: String) = "menu/$tableId/$tableName"
    }
    object Cart : Screen("cart/{tableId}/{tableName}") {
        fun createRoute(tableId: Long, tableName: String) = "cart/$tableId/$tableName"
    }
}

@Composable
fun AppNavigation(
    navController: NavHostController,
    preferencesManager: PreferencesManager,
    authRepository: AuthRepository,
    tableRepository: TableRepository,
    orderRepository: OrderRepository
) {
    // Shared Cart Map keyed by TableId
    val tableCarts = remember { mutableStateMapOf<Long, MutableList<CartItem>>() }

    NavHost(
        navController = navController,
        startDestination = Screen.Splash.route
    ) {
        composable(Screen.Splash.route) {
            SplashScreen(
                onSplashFinished = {
                    navController.navigate(Screen.Login.route) {
                        popUpTo(Screen.Splash.route) { inclusive = true }
                    }
                }
            )
        }

        composable(Screen.Settings.route) {
            SettingsScreen(
                preferencesManager = preferencesManager,
                onBack = { navController.popBackStack() }
            )
        }

        composable(Screen.Login.route) {
            LoginPinScreen(
                authRepository = authRepository,
                onLoginSuccess = {
                    navController.navigate(Screen.Tables.route) {
                        popUpTo(Screen.Login.route) { inclusive = true }
                    }
                },
                onNavigateToSettings = {
                    navController.navigate(Screen.Settings.route)
                }
            )
        }

        composable(Screen.Tables.route) {
            TableListScreen(
                tableRepository = tableRepository,
                preferencesManager = preferencesManager,
                onSelectTable = { tableId, tableName ->
                    navController.navigate(Screen.Menu.createRoute(tableId, tableName))
                },
                onLogout = {
                    navController.navigate(Screen.Login.route) {
                        popUpTo(Screen.Tables.route) { inclusive = true }
                    }
                }
            )
        }

        composable(
            route = Screen.Menu.route,
            arguments = listOf(
                navArgument("tableId") { type = NavType.LongType },
                navArgument("tableName") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val tableId = backStackEntry.arguments?.getLong("tableId") ?: 0L
            val tableName = backStackEntry.arguments?.getString("tableName") ?: "Mesa"

            val cart = tableCarts.getOrPut(tableId) { mutableStateListOf() }

            MenuScreen(
                tableId = tableId,
                tableName = tableName,
                orderRepository = orderRepository,
                cartItems = cart,
                onBack = { navController.popBackStack() },
                onNavigateToCart = {
                    navController.navigate(Screen.Cart.createRoute(tableId, tableName))
                },
                onViewActiveOrder = {
                    navController.navigate(Screen.Cart.createRoute(tableId, tableName))
                }
            )
        }

        composable(
            route = Screen.Cart.route,
            arguments = listOf(
                navArgument("tableId") { type = NavType.LongType },
                navArgument("tableName") { type = NavType.StringType }
            )
        ) { backStackEntry ->
            val tableId = backStackEntry.arguments?.getLong("tableId") ?: 0L
            val tableName = backStackEntry.arguments?.getString("tableName") ?: "Mesa"

            val cart = tableCarts.getOrPut(tableId) { mutableStateListOf() }

            CartScreen(
                tableId = tableId,
                tableName = tableName,
                cartItems = cart,
                orderRepository = orderRepository,
                onBack = { navController.popBackStack() },
                onOrderSentSuccess = {
                    // Volver a la lista de mesas tras enviar comanda
                    navController.navigate(Screen.Tables.route) {
                        popUpTo(Screen.Tables.route) { inclusive = false }
                    }
                }
            )
        }
    }
}
