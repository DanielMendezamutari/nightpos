package com.ribersoft.riberresto.garzon.data.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class LoginResponse(
    @SerialName("loginSuccessful") val loginSuccessful: Boolean = false,
    @SerialName("existFreeTables") val existFreeTables: Boolean = true,
    @SerialName("waiterId") val waiterId: String? = null,
    @SerialName("waiterName") val waiterName: String? = null,
    @SerialName("token") val token: String? = null,
    @SerialName("message") val message: String? = null
)

@Serializable
data class Table(
    @SerialName("id") val id: Long,
    @SerialName("name") val name: String,
    @SerialName("state") val state: String = "TABLES_FREE", // TABLES_FREE, TABLES_BUSY, PRECUENTA
    @SerialName("codigo") val codigo: String? = null,
    @SerialName("salon_nombre") val salonNombre: String? = null
) {
    val isFree: Boolean get() = state == "TABLES_FREE" || state.equals("LIBRE", ignoreCase = true)
    val isBusy: Boolean get() = state == "TABLES_BUSY" || state.equals("OCUPADA", ignoreCase = true)
    val isPrecuenta: Boolean get() = state.equals("PRECUENTA", ignoreCase = true)
}

@Serializable
data class Category(
    @SerialName("id") val id: Long,
    @SerialName("name") val name: String,
    @SerialName("codigo") val codigo: String? = null,
    @SerialName("icono") val icono: String? = null
)

@Serializable
data class Product(
    @SerialName("id") val id: Long,
    @SerialName("categoryId") val categoryId: Long = 0,
    @SerialName("name") val name: String,
    @SerialName("price") val price: Double = 0.0,
    @SerialName("destinoImpresion") val destinoImpresion: String? = "COCINA",
    @SerialName("hasAssistants") val hasAssistants: Boolean = true
)

@Serializable
data class Assistant(
    @SerialName("mId") val id: Long,
    @SerialName("mName") val name: String,
    @SerialName("mFullName") val fullName: String
)

@Serializable
data class CartItem(
    val product: Product,
    var count: Int = 1,
    var comment: String = "",
    var assistants: List<String> = emptyList()
) {
    val subtotal: Double get() = product.price * count

    fun toLegacyPayload(): CartItemPayload {
        return CartItemPayload(
            productId = product.id,
            productName = product.name,
            count = count,
            comment = comment,
            assistants = assistants.joinToString(", ")
        )
    }
}

@Serializable
data class CartItemPayload(
    @SerialName("productId") val productId: Long,
    @SerialName("productName") val productName: String,
    @SerialName("count") val count: Int,
    @SerialName("comment") val comment: String,
    @SerialName("assistants") val assistants: String
)

@Serializable
data class OrderResquest(
    @SerialName("tableId") val tableId: Long,
    @SerialName("waiterId") val waiterId: String,
    @SerialName("cartItemList") val cartItemList: List<CartItemPayload>
)

@Serializable
data class PrintRequest(
    @SerialName("tableId") val tableId: Long,
    @SerialName("waiterId") val waiterId: String
)

@Serializable
data class Order(
    @SerialName("productId") val productId: Long,
    @SerialName("productName") val productName: String,
    @SerialName("count") val count: Int
)

@Serializable
data class SimpleResponse(
    @SerialName("success") val success: Boolean = true,
    @SerialName("message") val message: String = ""
)
