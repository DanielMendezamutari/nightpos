import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('cash index Sprint 4A dashboard behavior guards', () => {
  const filePath = resolve(process.cwd(), 'src/pages/nightpos/cash/index.vue')
  const content = readFileSync(filePath, 'utf8')

  it('renders physical cash block', () => {
    expect(content.includes('<CashPhysicalSummaryCard')).toBe(true)
    expect(content.includes(':summary="cashSummaryData"')).toBe(true)
  })

  it('keeps expected cash centered in cash summary path (QR/card not merged into cash expected)', () => {
    expect(content.includes('return toNumber(cashSummaryData.value.expected_cash)')).toBe(true)
    expect(content.includes('cash: toNumber(cashSummaryData.value.expected_cash')).toBe(true)
  })

  it('maps pending settlements by role for waiters, girls, and cleaning', () => {
    expect(content.includes('waiters: toNumber(settlement.waiters?.pending_net_amount)')).toBe(true)
    expect(content.includes('girls: toNumber(settlement.girls?.pending_net_amount)')).toBe(true)
    expect(content.includes('cleaning: toNumber(settlement.cleaning?.pending_net_amount)')).toBe(true)
  })

  it('maps sales summary by payment method', () => {
    expect(content.includes('sales_cash: byMethod.cash ?? legacy.total_cash ?? 0')).toBe(true)
    expect(content.includes('sales_qr: byMethod.qr ?? legacy.total_qr ?? 0')).toBe(true)
    expect(content.includes('sales_card: byMethod.card ?? legacy.total_card ?? 0')).toBe(true)
  })

  it('maps movement summary by category buckets', () => {
    expect(content.includes('operating_expenses: toNumber(expenseByCategory.OPERATING_EXPENSE)')).toBe(true)
    expect(content.includes('purchases: toNumber(expenseByCategory.PURCHASE)')).toBe(true)
    expect(content.includes('other_expenses: toNumber(expenseByCategory.OTHER_EXPENSE)')).toBe(true)
  })

  it('keeps scope context visible and leaves pending alerts limited to operational blockers', () => {
    expect(content.includes('<CashScopeContextAlert :scope="scopeSummaryData" />')).toBe(true)
    expect(content.includes('critical_alerts: blockers.map(item => item.message)')).toBe(true)
  })

  it('shows explicit legacy fallback warning when financial_dashboard is missing', () => {
    expect(content.includes('const usingLegacyFallback = computed(() => Boolean(session.value) && !financialDashboard.value)')).toBe(true)
    expect(content.includes('financial_dashboard ausente. Se activo fallback legacy temporal (financial_summary).')).toBe(true)
  })

  it('shows api error state with retry action', () => {
    expect(content.includes('const apiError = ref(null)')).toBe(true)
    expect(content.includes('v-if="apiError"')).toBe(true)
    expect(content.includes('@click="loadSession"')).toBe(true)
  })

  it('shows no-open-cash state', () => {
    expect(content.includes('No hay caja abierta. Indique el fondo inicial para comenzar a cobrar.')).toBe(true)
    expect(content.includes('Abrir caja')).toBe(true)
  })

  it('keeps existing cashier actions available', () => {
    expect(content.includes('Venta directa')).toBe(true)
    expect(content.includes('Ingreso / egreso manual')).toBe(true)
    expect(content.includes('Cerrar caja')).toBe(true)
  })
})
