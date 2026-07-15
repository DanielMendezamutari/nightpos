import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('settlements index autosync behavior', () => {
  const filePath = resolve(process.cwd(), 'src/pages/nightpos/settlements/index.vue')
  const content = readFileSync(filePath, 'utf8')

  it('oculta flujo manual de generar liquidaciones para operacion normal', () => {
    expect(content.includes('Generar liquidaciones del turno actual')).toBe(false)
    expect(content.includes('Pulse <strong>«Generar liquidaciones del turno actual»</strong>')).toBe(false)
  })

  it('muestra mensajes de autosincronizacion y ausencia de ventas cobradas pendientes', () => {
    expect(content.includes('Las liquidaciones se actualizan automáticamente con cada venta cobrada.')).toBe(true)
    expect(content.includes('Aún no existen ventas cobradas con pagos pendientes en esta caja.')).toBe(true)
  })

  it('deja reconciliacion manual solo para perfil admin', () => {
    expect(content.includes('Reconciliar liquidaciones')).toBe(true)
    expect(content.includes('canReconcileSettlements')).toBe(true)
  })

  it('el warning de garzon sin porcentaje pasa a compensacion manual pendiente', () => {
    expect(content.includes('Compensación manual pendiente.')).toBe(true)
    expect(content.includes('Contacte al administrador antes de generar liquidaciones.')).toBe(false)
  })

  it('consume campos explicitos de estado de autosync del payload', () => {
    expect(content.includes('autoSyncEnabled')).toBe(true)
    expect(content.includes('syncStatus')).toBe(true)
    expect(content.includes('pendingSourcesCount')).toBe(true)
  })
})
