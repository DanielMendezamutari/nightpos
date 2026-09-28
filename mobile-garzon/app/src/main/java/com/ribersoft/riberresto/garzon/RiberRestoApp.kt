package com.ribersoft.riberresto.garzon

import android.app.Application
import com.ribersoft.riberresto.garzon.data.local.PreferencesManager
import com.ribersoft.riberresto.garzon.data.repository.AuthRepository
import com.ribersoft.riberresto.garzon.data.repository.OrderRepository
import com.ribersoft.riberresto.garzon.data.repository.TableRepository

class RiberRestoApp : Application() {

    lateinit var preferencesManager: PreferencesManager
        private set

    lateinit var authRepository: AuthRepository
        private set

    lateinit var tableRepository: TableRepository
        private set

    lateinit var orderRepository: OrderRepository
        private set

    override fun onCreate() {
        super.onCreate()
        preferencesManager = PreferencesManager(this)
        authRepository = AuthRepository(preferencesManager)
        tableRepository = TableRepository(preferencesManager)
        orderRepository = OrderRepository(preferencesManager)
    }
}
