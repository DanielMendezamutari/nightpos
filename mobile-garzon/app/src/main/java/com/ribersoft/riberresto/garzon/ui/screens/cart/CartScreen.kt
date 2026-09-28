package com.ribersoft.riberresto.garzon.ui.screens.cart

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.ribersoft.riberresto.garzon.data.model.CartItem
import com.ribersoft.riberresto.garzon.data.model.Order
import com.ribersoft.riberresto.garzon.data.repository.OrderRepository
import com.ribersoft.riberresto.garzon.ui.theme.*
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CartScreen(
    tableId: Long,
    tableName: String,
    cartItems: MutableList<CartItem>,
    orderRepository: OrderRepository,
    onBack: () -> Unit,
    onOrderSentSuccess: () -> Unit
) {
    val coroutineScope = rememberCoroutineScope()
    var isSending by remember { mutableStateOf(false) }
    var isPrintingPrecuenta by remember { mutableStateOf(false) }
    var actionMessage by remember { mutableStateOf<Pair<Boolean, String>?>(null) }
    var showActiveOrdersDialog by remember { mutableStateOf(false) }
    var activeOrders by remember { mutableStateOf<List<Order>>(emptyList()) }
    var isLoadingActiveOrders by remember { mutableStateOf(false) }

    val totalPrice = cartItems.sumOf { it.subtotal }

    fun sendOrder() {
        if (cartItems.isEmpty() || isSending) return
        isSending = true
        actionMessage = null

        coroutineScope.launch {
            val result = orderRepository.sendOrder(tableId, cartItems)
            isSending = false
            if (result.isSuccess) {
                cartItems.clear()
                actionMessage = Pair(true, "¡Comanda enviada a Cocina y Barra!")
                kotlinx.coroutines.delay(1200)
                onOrderSentSuccess()
            } else {
                actionMessage = Pair(false, result.exceptionOrNull()?.localizedMessage ?: "Error al enviar comanda")
            }
        }
    }

    fun printPrecuenta() {
        if (isPrintingPrecuenta) return
        isPrintingPrecuenta = true
        actionMessage = null

        coroutineScope.launch {
            val result = orderRepository.printPrecuenta(tableId)
            isPrintingPrecuenta = false
            if (result.isSuccess) {
                actionMessage = Pair(true, "¡Precuenta enviada a impresora CAJA!")
            } else {
                actionMessage = Pair(false, result.exceptionOrNull()?.localizedMessage ?: "Error al solicitar precuenta")
            }
        }
    }

    fun loadActiveOrders() {
        showActiveOrdersDialog = true
        isLoadingActiveOrders = true
        coroutineScope.launch {
            val result = orderRepository.getOrderHistory(tableId)
            isLoadingActiveOrders = false
            if (result.isSuccess) {
                activeOrders = result.getOrDefault(emptyList())
            }
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Column {
                        Text(
                            text = "Comanda: $tableName",
                            style = MaterialTheme.typography.titleMedium,
                            fontWeight = FontWeight.Bold
                        )
                        Text(
                            text = "${cartItems.size} ítems en comanda",
                            fontSize = 12.sp,
                            color = Secondary
                        )
                    }
                },
                navigationIcon = {
                    IconButton(onClick = onBack) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Volver", tint = TextPrimary)
                    }
                },
                actions = {
                    // Botón Ver Comanda Activa (VerOrder de base.apk)
                    IconButton(onClick = { loadActiveOrders() }) {
                        Icon(Icons.Default.ReceiptLong, contentDescription = "Ver Comanda Activa", tint = TextPrimary)
                    }
                    // Botón Precuenta (PrintOrder de base.apk)
                    IconButton(onClick = { printPrecuenta() }, enabled = !isPrintingPrecuenta) {
                        Icon(Icons.Default.Print, contentDescription = "Imprimir Precuenta", tint = StatusPrecuenta)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = DarkSurface,
                    titleContentColor = TextPrimary
                )
            )
        },
        bottomBar = {
            Surface(
                color = DarkSurface,
                tonalElevation = 8.dp,
                modifier = Modifier.fillMaxWidth()
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(16.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    // Total summary
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = "Total a Enviar:",
                            style = MaterialTheme.typography.titleMedium,
                            color = TextSecondary
                        )
                        Text(
                            text = "Bs. ${"%.2f".format(totalPrice)}",
                            style = MaterialTheme.typography.headlineMedium,
                            fontWeight = FontWeight.ExtraBold,
                            color = AccentGold
                        )
                    }

                    // Enviar Comanda button (MakeOrderTask de base.apk)
                    Button(
                        onClick = { sendOrder() },
                        enabled = cartItems.isNotEmpty() && !isSending,
                        colors = ButtonDefaults.buttonColors(containerColor = Primary),
                        shape = RoundedCornerShape(16.dp),
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(54.dp)
                    ) {
                        if (isSending) {
                            CircularProgressIndicator(modifier = Modifier.size(24.dp), color = Color.White)
                        } else {
                            Icon(Icons.Default.Send, contentDescription = null)
                            Spacer(modifier = Modifier.width(8.dp))
                            Text(
                                text = "Enviar Pedido a Cocina / Barra",
                                fontWeight = FontWeight.Bold,
                                fontSize = 16.sp
                            )
                        }
                    }
                }
            }
        },
        containerColor = DarkBackground
    ) { paddingValues ->
        Column(
            modifier = Modifier
                .padding(paddingValues)
                .fillMaxSize()
        ) {
            // Status Alert Banner
            actionMessage?.let { (success, msg) ->
                Surface(
                    color = if (success) StatusFree.copy(alpha = 0.2f) else StatusError.copy(alpha = 0.2f),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Row(
                        modifier = Modifier.padding(12.dp),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        Icon(
                            imageVector = if (success) Icons.Default.CheckCircle else Icons.Default.Error,
                            contentDescription = null,
                            tint = if (success) StatusFree else StatusError
                        )
                        Text(
                            text = msg,
                            color = if (success) StatusFree else StatusError,
                            fontWeight = FontWeight.Bold,
                            fontSize = 13.sp
                        )
                    }
                }
            }

            // Cart Items List
            if (cartItems.isEmpty()) {
                Box(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(32.dp),
                    contentAlignment = Alignment.Center
                ) {
                    Column(
                        horizontalAlignment = Alignment.CenterHorizontally,
                        verticalArrangement = Arrangement.spacedBy(12.dp)
                    ) {
                        Icon(
                            Icons.Default.RemoveShoppingCart,
                            contentDescription = null,
                            tint = TextMuted,
                            modifier = Modifier.size(64.dp)
                        )
                        Text(
                            text = "El carrito de esta mesa está vacío",
                            style = MaterialTheme.typography.titleMedium,
                            color = TextSecondary
                        )
                        Text(
                            text = "Regresa al menú para agregar productos",
                            style = MaterialTheme.typography.bodyMedium,
                            color = TextMuted
                        )
                    }
                }
            } else {
                LazyColumn(
                    modifier = Modifier.fillMaxSize(),
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp)
                ) {
                    itemsIndexed(cartItems) { index, item ->
                        CartItemRow(
                            item = item,
                            onIncrease = { item.count++ },
                            onDecrease = {
                                if (item.count > 1) {
                                    item.count--
                                } else {
                                    cartItems.removeAt(index)
                                }
                            },
                            onDelete = {
                                cartItems.removeAt(index)
                            }
                        )
                    }
                }
            }
        }
    }

    // Modal: Ver comanda activa de la mesa (OrderByTable)
    if (showActiveOrdersDialog) {
        AlertDialog(
            onDismissRequest = { showActiveOrdersDialog = false },
            title = {
                Text(
                    text = "Comanda Activa: $tableName",
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary
                )
            },
            text = {
                if (isLoadingActiveOrders) {
                    Box(modifier = Modifier.fillMaxWidth().height(100.dp), contentAlignment = Alignment.Center) {
                        CircularProgressIndicator(color = Primary)
                    }
                } else if (activeOrders.isEmpty()) {
                    Text(
                        text = "La mesa no tiene consumos previos cargados.",
                        color = TextSecondary
                    )
                } else {
                    LazyColumn(
                        verticalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        items(activeOrders.size) { i ->
                            val ord = activeOrders[i]
                            Row(
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .clip(RoundedCornerShape(8.dp))
                                    .background(DarkSurfaceVariant)
                                    .padding(8.dp),
                                horizontalArrangement = Arrangement.SpaceBetween
                            ) {
                                Text(
                                    text = "${ord.count}x ${ord.productName}",
                                    color = TextPrimary,
                                    fontWeight = FontWeight.Medium
                                )
                            }
                        }
                    }
                }
            },
            confirmButton = {
                TextButton(onClick = { showActiveOrdersDialog = false }) {
                    Text("Cerrar", color = Primary)
                }
            },
            containerColor = DarkSurface
        )
    }
}

@Composable
private fun CartItemRow(
    item: CartItem,
    onIncrease: () -> Unit,
    onDecrease: () -> Unit,
    onDelete: () -> Unit
) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(14.dp))
            .border(1.dp, DarkBorder, RoundedCornerShape(14.dp)),
        colors = CardDefaults.cardColors(containerColor = DarkSurface)
    ) {
        Column(
            modifier = Modifier.padding(14.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = item.product.name,
                    style = MaterialTheme.typography.titleMedium,
                    fontWeight = FontWeight.Bold,
                    color = TextPrimary,
                    modifier = Modifier.weight(1f)
                )

                IconButton(
                    onClick = onDelete,
                    modifier = Modifier.size(28.dp)
                ) {
                    Icon(
                        Icons.Default.DeleteOutline,
                        contentDescription = "Eliminar",
                        tint = StatusError
                    )
                }
            }

            // Assistants / Notes
            if (item.assistants.isNotEmpty() || item.comment.isNotBlank()) {
                Surface(
                    color = DarkSurfaceVariant,
                    shape = RoundedCornerShape(8.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(modifier = Modifier.padding(8.dp)) {
                        if (item.assistants.isNotEmpty()) {
                            Text(
                                text = "Notas: ${item.assistants.joinToString(", ")}",
                                fontSize = 12.sp,
                                color = Secondary,
                                fontWeight = FontWeight.Medium
                            )
                        }
                        if (item.comment.isNotBlank()) {
                            Text(
                                text = "Comentario: ${item.comment}",
                                fontSize = 12.sp,
                                color = AccentGold
                            )
                        }
                    }
                }
            }

            // Quantity and Subtotal Row
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Row(
                    verticalAlignment = Alignment.CenterVertically,
                    horizontalArrangement = Arrangement.spacedBy(10.dp)
                ) {
                    IconButton(
                        onClick = onDecrease,
                        modifier = Modifier
                            .size(30.dp)
                            .clip(CircleShape)
                            .background(DarkBorder)
                    ) {
                        Icon(Icons.Default.Remove, contentDescription = "Menos", tint = TextPrimary, modifier = Modifier.size(16.dp))
                    }

                    Text(
                        text = item.count.toString(),
                        fontSize = 16.sp,
                        fontWeight = FontWeight.Bold,
                        color = TextPrimary
                    )

                    IconButton(
                        onClick = onIncrease,
                        modifier = Modifier
                            .size(30.dp)
                            .clip(CircleShape)
                            .background(Primary)
                    ) {
                        Icon(Icons.Default.Add, contentDescription = "Más", tint = Color.White, modifier = Modifier.size(16.dp))
                    }
                }

                Text(
                    text = "Bs. ${"%.2f".format(item.subtotal)}",
                    fontWeight = FontWeight.Bold,
                    fontSize = 16.sp,
                    color = AccentGold
                )
            }
        }
    }
}
