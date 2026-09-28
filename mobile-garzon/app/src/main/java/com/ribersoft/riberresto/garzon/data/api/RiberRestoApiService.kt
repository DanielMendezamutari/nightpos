package com.ribersoft.riberresto.garzon.data.api

import com.ribersoft.riberresto.garzon.data.model.*
import retrofit2.Response
import retrofit2.http.*

interface RiberRestoApiService {

    @GET("users/login/{query}")
    suspend fun login(@Path("query") pin: String): Response<LoginResponse>

    @POST("table/allTable")
    suspend fun getTables(): Response<List<Table>>

    @GET("Assistants/All")
    suspend fun getAssistants(): Response<List<Assistant>>

    @POST("orders/SendOrder")
    suspend fun sendOrder(@Body request: OrderResquest): Response<SimpleResponse>

    @POST("orders/PrintOrder")
    suspend fun printOrder(@Body request: PrintRequest): Response<SimpleResponse>

    @POST("orders/OrderByTable")
    suspend fun getOrderByTable(@Body body: Map<String, Long>): Response<List<Order>>

    @GET("comandas/categorias")
    suspend fun getCategories(): Response<CategoryListResponse>

    @GET("comandas/productos")
    suspend fun getProducts(@Query("categoria_id") categoryId: Long? = null): Response<ProductListResponse>
}

@kotlinx.serialization.Serializable
data class CategoryListResponse(
    val success: Boolean = true,
    val data: List<CategoryItem> = emptyList()
)

@kotlinx.serialization.Serializable
data class CategoryItem(
    val id: Long,
    val nombre: String,
    val codigo: String? = null,
    val icono: String? = null
)

@kotlinx.serialization.Serializable
data class ProductListResponse(
    val success: Boolean = true,
    val data: List<ProductItem> = emptyList()
)

@kotlinx.serialization.Serializable
data class ProductItem(
    val id: Long,
    @kotlinx.serialization.SerialName("categoria_id") val categoriaId: Long,
    val nombre: String,
    val precio: Double,
    @kotlinx.serialization.SerialName("destino_impresion") val destinoImpresion: String? = null
)
