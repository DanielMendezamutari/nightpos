import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('waiter/orders/[id].vue combo allocation targeting guards', () => {
  const filePath = resolve(process.cwd(), 'src/pages/nightpos/waiter/orders/[id].vue')
  const content = readFileSync(filePath, 'utf8')

  it('tracks previous item ids and identifies the created item by id first', () => {
    expect(content.includes('const previousItems = [...(order.value?.items ?? [])]')).toBe(true)
    expect(content.includes('const previousItemIds = new Set(')).toBe(true)
    expect(content.includes('const addedItemId = Number(order.value?.added_item_id)')).toBe(true)
    expect(content.includes('currentItems.find(item => Number(item?.id) === addedItemId)')).toBe(true)
  })

  it('uses the detected item id when syncing combo allocations', () => {
    expect(content.includes('syncOrderItemAllocations(orderId.value, addedAllocationTarget.id, addForm.allocations)')).toBe(true)
  })

  it('checks pending allocation only for the exact created line id', () => {
    expect(content.includes('find(i => Number(i?.id) === Number(addedAllocationTarget?.id) && i.requires_allocation && !i.allocation_complete)')).toBe(true)
  })

  it('does not fall back to product_id heuristics for combo sync or pending checks', () => {
    expect(content.includes("find(i => i.product_id === addForm.product_id && i.requires_allocation)")).toBe(false)
    expect(content.includes('i.product_id === addForm.product_id && i.requires_allocation && !i.allocation_complete')).toBe(false)
  })
})
