package com.ribersoft.riberresto.garzon.data.repository

import com.ribersoft.riberresto.garzon.data.api.NetworkClient
import com.ribersoft.riberresto.garzon.data.api.RiberRestoApiService
import com.ribersoft.riberresto.garzon.data.local.PreferencesManager
import com.ribersoft.riberresto.garzon.data.model.*
import kotlinx.coroutines.flow.first

class AuthRepository(private val preferencesManager: PreferencesManager) {

    private suspend fun getApi(): RiberRestoApiService {
        val url = preferencesManager.serverUrlFlow.first()
        return NetworkClient.getService(url)
    }

    suspend fun login(pin: String): Result<LoginResponse> {
        return try {
            val response = getApi().login(pin)
            if (response.isSuccessful && response.body() != null) {
                val data = response.body()!!
                if (data.loginSuccessful) {
                    preferencesManager.saveSession(
                        waiterId = data.waiterId ?: "1",
                        waiterName = data.waiterName ?: "Garzón",
                        token = data.token
                    )
                    Result.success(data)
                } else {
                    Result.failure(Exception(data.message ?: "PIN incorrecto"))
                }
            } else {
                Result.failure(Exception("Error al conectar con el servidor (${response.code()})"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}

class TableRepository(private val preferencesManager: PreferencesManager) {

    private suspend fun getApi(): RiberRestoApiService {
        val url = preferencesManager.serverUrlFlow.first()
        return NetworkClient.getService(url)
    }

    suspend fun getTables(): Result<List<Table>> {
        return try {
            val response = getApi().getTables()
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("No se pudieron cargar las mesas"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}

class OrderRepository(private val preferencesManager: PreferencesManager) {

    private suspend fun getApi(): RiberRestoApiService {
        val url = preferencesManager.serverUrlFlow.first()
        return NetworkClient.getService(url)
    }

    suspend fun getCategories(): Result<List<Category>> {
        return try {
            val response = getApi().getCategories()
            if (response.isSuccessful && response.body() != null) {
                val list = response.body()!!.data.map {
                    Category(id = it.id, name = it.nombre, codigo = it.codigo, icono = it.icono)
                }
                Result.success(list)
            } else {
                Result.failure(Exception("Error al obtener categorías"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getProducts(categoryId: Long? = null): Result<List<Product>> {
        return try {
            val response = getApi().getProducts(categoryId)
            if (response.isSuccessful && response.body() != null) {
                val list = response.body()!!.data.map {
                    Product(
                        id = it.id,
                        categoryId = it.categoriaId,
                        name = it.nombre,
                        price = it.precio,
                        destinoImpresion = it.destinoImpresion,
                        hasAssistants = true
                    )
                }
                Result.success(list)
            } else {
                Result.failure(Exception("Error al obtener productos"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getAssistants(): Result<List<Assistant>> {
        return try {
            val response = getApi().getAssistants()
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                // Fallback por defecto si no hay conexión
                Result.success(
                    listOf(
                        Assistant(1, "Sin Sal", "Sin Sal"),
                        Assistant(2, "Sin Cebolla", "Sin Cebolla"),
                        Assistant(3, "Picante Extra", "Picante Extra"),
                        Assistant(4, "Término Medio", "Carne Término Medio"),
                        Assistant(5, "Bien Cocido", "Carne Bien Cocida"),
                        Assistant(6, "Con Hielo", "Con Hielo"),
                        Assistant(7, "Sin Hielo", "Sin Hielo")
                    )
                )
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun sendOrder(tableId: Long, items: List<CartItem>): Result<SimpleResponse> {
        return try {
            val waiterId = preferencesManager.waiterIdFlow.first() ?: "1"
            val payload = OrderResquest(
                tableId = tableId,
                waiterId = waiterId,
                cartItemList = items.map { it.toLegacyPayload() }
            )
            val response = getApi().sendOrder(payload)
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("Error al enviar la comanda"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun printPrecuenta(tableId: Long): Result<SimpleResponse> {
        return try {
            val waiterId = preferencesManager.waiterIdFlow.first() ?: "1"
            val payload = PrintRequest(tableId = tableId, waiterId = waiterId)
            val response = getApi().printOrder(payload)
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("Error al solicitar la precuenta"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getOrderHistory(tableId: Long): Result<List<Order>> {
        return try {
            val response = getApi().getOrderByTable(mapOf("tableId" to tableId))
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("Error al obtener comanda de la mesa"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
