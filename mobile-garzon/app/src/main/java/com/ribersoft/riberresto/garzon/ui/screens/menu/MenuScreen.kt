package com.ribersoft.riberresto.garzon.ui.screens.menu

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.AddShoppingCart
import androidx.compose.material.icons.filled.ReceiptLong
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.ribersoft.riberresto.garzon.data.model.*
import com.ribersoft.riberresto.garzon.data.repository.OrderRepository
import com.ribersoft.riberresto.garzon.ui.components.ModifierDialog
import com.ribersoft.riberresto.garzon.ui.theme.*
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MenuScreen(
    tableId: Long,
    tableName: String,
    orderRepository: OrderRepository,
    cartItems: MutableList<CartItem>,
    onBack: () -> Unit,
    onNavigateToCart: () -> Unit,
    onViewActiveOrder: () -> Unit
) {
    val coroutineScope = rememberCoroutineScope()
    var categories by remember { mutableStateOf<List<Category>>(emptyList()) }
    var selectedCategoryId by remember { mutableStateOf<Long?>(null) }
    var products by remember { mutableStateOf<List<Product>>(emptyList()) }
    var assistants by remember { mutableStateOf<List<Assistant>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }

    var selectedProductForModifier by remember { mutableStateOf<Product?>(null) }

    LaunchedEffect(Unit) {
        isLoading = true
        val catResult = orderRepository.getCategories()
        val prodResult = orderRepository.getProducts()
        val astResult = orderRepository.getAssistants()

        if (catResult.isSuccess) {
            categories = catResult.getOrDefault(emptyList())
            if (categories.isNotEmpty()) {
                selectedCategoryId = categories.first().id
            }
        }
        if (prodResult.isSuccess) {
            products = prodResult.getOrDefault(emptyList())
        }
        if (astResult.isSuccess) {
            assistants = astResult.getOrDefault(emptyList())
        }
        isLoading = false
    }

    val filteredProducts = if (selectedCategoryId != null) {
        products.filter { it.categoryId == selectedCategoryId }
    } else {
        products
    }

    val totalItems = cartItems.sumOf { it.count }
    val totalPrice = cartItems.sumOf { it.subtotal }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Column {
                        Text(
                            text = tableName,
                            style = MaterialTheme.typography.titleMedium,
                            fontWeight = FontWeight.Bold
                        )
                        Text(
                            text = "Comandar Productos",
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
                    IconButton(onClick = onViewActiveOrder) {
                        Icon(Icons.Default.ReceiptLong, contentDescription = "Ver Cuenta", tint = Primary)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = DarkSurface,
                    titleContentColor = TextPrimary
                )
            )
        },
        bottomBar = {
            if (totalItems > 0) {
                Surface(
                    color = DarkSurface,
                    tonalElevation = 8.dp,
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Button(
                        onClick = onNavigateToCart,
                        colors = ButtonDefaults.buttonColors(containerColor = Primary),
                        shape = RoundedCornerShape(16.dp),
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(16.dp)
                            .height(54.dp)
                    ) {
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Row(
                                verticalAlignment = Alignment.CenterVertically,
                                horizontalArrangement = Arrangement.spacedBy(8.dp)
                            ) {
                                Icon(Icons.Default.ShoppingCart, contentDescription = null)
                                Text(
                                    text = "$totalItems ítem${if (totalItems > 1) "s" else ""}",
                                    fontWeight = FontWeight.Bold
                                )
                            }
                            Text(
                                text = "Bs. ${"%.2f".format(totalPrice)} • Ver Carrito",
                                fontWeight = FontWeight.Bold
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
            // Category Tabs Row
            if (categories.isNotEmpty()) {
                LazyRow(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 10.dp),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    items(categories) { cat ->
                        val isSelected = selectedCategoryId == cat.id
                        FilterChip(
                            selected = isSelected,
                            onClick = { selectedCategoryId = cat.id },
                            label = { Text(cat.name, fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal) },
                            colors = FilterChipDefaults.filterChipColors(
                                selectedContainerColor = Primary,
                                selectedLabelColor = Color.White,
                                containerColor = DarkSurface,
                                labelColor = TextSecondary
                            ),
                            shape = RoundedCornerShape(12.dp)
                        )
                    }
                }
            }

            // Products Grid
            if (isLoading) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = Primary)
                }
            } else if (filteredProducts.isEmpty()) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Text(
                        text = "No hay productos en esta categoría",
                        style = MaterialTheme.typography.bodyMedium,
                        color = TextSecondary
                    )
                }
            } else {
                LazyVerticalGrid(
                    columns = GridCells.Fixed(2),
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(horizontal = 16.dp),
                    horizontalArrangement = Arrangement.spacedBy(12.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp),
                    contentPadding = PaddingValues(bottom = 24.dp)
                ) {
                    items(filteredProducts) { product ->
                        ProductItemCard(
                            product = product,
                            onAddClick = {
                                selectedProductForModifier = product
                            }
                        )
                    }
                }
            }
        }
    }

    // Modifier and Notes Dialog
    selectedProductForModifier?.let { prod ->
        ModifierDialog(
            product = prod,
            availableAssistants = assistants,
            onDismiss = { selectedProductForModifier = null },
            onConfirm = { cartItem ->
                cartItems.add(cartItem)
                selectedProductForModifier = null
            }
        )
    }
}

@Composable
private fun ProductItemCard(
    product: Product,
    onAddClick: () -> Unit
) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(14.dp))
            .border(1.dp, DarkBorder, RoundedCornerShape(14.dp))
            .clickable(onClick = onAddClick),
        colors = CardDefaults.cardColors(containerColor = DarkSurface)
    ) {
        Column(
            modifier = Modifier.padding(14.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            Text(
                text = product.name,
                style = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Bold,
                color = TextPrimary,
                maxLines = 2
            )

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Bs. ${"%.2f".format(product.price)}",
                    fontSize = 15.sp,
                    fontWeight = FontWeight.Bold,
                    color = AccentGold
                )

                IconButton(
                    onClick = onAddClick,
                    modifier = Modifier
                        .size(32.dp)
                        .clip(RoundedCornerShape(8.dp))
                        .background(Primary)
                ) {
                    Icon(
                        imageVector = Icons.Default.AddShoppingCart,
                        contentDescription = "Agregar",
                        tint = Color.White,
                        modifier = Modifier.size(18.dp)
                    )
                }
            }
        }
    }
}
