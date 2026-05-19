#!/usr/bin/env node

/**
 * Script para verificar que la API esté disponible
 * Uso: npm run test:api
 */

const API_URL = process.argv[2] || 'https://nightpos.ribersoft.com/api';

console.log(`\n🔍 Probando conexión a: ${API_URL}\n`);

async function testAPI() {
  try {
    const response = await fetch(`${API_URL}/health`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    console.log(`✅ Respuesta recibida: ${response.status} ${response.statusText}`);

    if (response.ok) {
      const data = await response.json();
      console.log(`📦 Datos: `, data);
      console.log(`\n✅ ¡API disponible y funcionando!\n`);
      process.exit(0);
    } else {
      console.log(`⚠️  API responde pero con error: ${response.status}`);
      const data = await response.json().catch(() => ({}));
      console.log(`📦 Respuesta:`, data);
      process.exit(1);
    }
  } catch (error) {
    console.log(`❌ Error de conexión: ${error.message}`);
    console.log(`\n⚠️  No se pudo conectar a la API en ${API_URL}`);
    console.log(`\n📋 Posibles causas:`);
    console.log(`   - El servidor no está disponible`);
    console.log(`   - La URL es incorrecta`);
    console.log(`   - Hay problemas de CORS`);
    console.log(`   - No hay conexión a internet\n`);
    process.exit(1);
  }
}

testAPI();
