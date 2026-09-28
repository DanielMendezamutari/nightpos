package com.ribersoft.riberresto.garzon.ui.screens.tables

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ExitToApp
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.ribersoft.riberresto.garzon.data.local.PreferencesManager
import com.ribersoft.riberresto.garzon.data.model.Table
import com.ribersoft.riberresto.garzon.data.repository.TableRepository
import com.ribersoft.riberresto.garzon.ui.components.TableCard
import com.ribersoft.riberresto.garzon.ui.theme.*
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun TableListScreen(
    tableRepository: TableRepository,
    preferencesManager: PreferencesManager,
    onSelectTable: (Long, String) -> Unit,
    onLogout: () -> Unit
) {
    val coroutineScope = rememberCoroutineScope()
    var tables by remember { mutableStateOf<List<Table>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    var selectedFilter by remember { mutableIntStateOf(0) } // 0: Todas, 1: Libres, 2: Ocupadas

    val waiterName by preferencesManager.waiterNameFlow.collectAsState(initial = "Garzón")

    fun loadTables() {
        isLoading = true
        coroutineScope.launch {
            val result = tableRepository.getTables()
            isLoading = false
            if (result.isSuccess) {
                tables = result.getOrDefault(emptyList())
            }
        }
    }

    LaunchedEffect(Unit) {
        loadTables()
    }

    val filteredTables = when (selectedFilter) {
        1 -> tables.filter { it.isFree }
        2 -> tables.filter { it.isBusy || it.isPrecuenta }
        else -> tables
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = {
                    Column {
                        Text(
                            text = "RiberResto POS",
                            style = MaterialTheme.typography.titleMedium,
                            fontWeight = FontWeight.Bold
                        )
                        Text(
                            text = "Mesero: ${waiterName ?: "Garzón"}",
                            fontSize = 12.sp,
                            color = Secondary
                        )
                    }
                },
                actions = {
                    IconButton(onClick = { loadTables() }) {
                        Icon(Icons.Default.Refresh, contentDescription = "Actualizar", tint = TextPrimary)
                    }
                    IconButton(onClick = {
                        coroutineScope.launch {
                            preferencesManager.clearSession()
                            onLogout()
                        }
                    }) {
                        Icon(Icons.AutoMirrored.Filled.ExitToApp, contentDescription = "Salir", tint = StatusError)
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
                .fillMaxSize()
        ) {
            // Filter Tabs (Todas, Libres, Ocupadas)
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 12.dp),
                horizontalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                val filters = listOf(
                    "Todas (${tables.size})",
                    "Libres (${tables.count { it.isFree }})",
                    "Ocupadas (${tables.count { it.isBusy || it.isPrecuenta }})"
                )

                filters.forEachIndexed { index, title ->
                    val isSelected = selectedFilter == index
                    FilterChip(
                        selected = isSelected,
                        onClick = { selectedFilter = index },
                        label = { Text(title, fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal) },
                        colors = FilterChipDefaults.filterChipColors(
                            selectedContainerColor = when (index) {
                                1 -> StatusFree
                                2 -> StatusBusy
                                else -> Primary
                            },
                            selectedLabelColor = Color.White,
                            containerColor = DarkSurface,
                            labelColor = TextSecondary
                        ),
                        shape = RoundedCornerShape(12.dp)
                    )
                }
            }

            // Content Grid
            if (isLoading) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = Primary)
                }
            } else if (filteredTables.isEmpty()) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Text(
                        text = "No hay mesas en esta categoría",
                        style = MaterialTheme.typography.bodyLarge,
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
                    items(filteredTables) { table ->
                        TableCard(
                            table = table,
                            onClick = { onSelectTable(table.id, table.name) }
                        )
                    }
                }
            }
        }
    }
}
