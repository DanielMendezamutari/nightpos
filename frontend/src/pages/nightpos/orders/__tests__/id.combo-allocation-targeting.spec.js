import { describe, expect, it } from 'vitest'
import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'

describe('orders/[id].vue combo allocation targeting guards', () => {
  const filePath = resolve(process.cwd(), 'src/pages/nightpos/orders/[id].vue')
  const content = readFileSync(filePath, 'utf8')

  it('tracks previous item ids before add and identifies the newly created item by id', () => {
    expect(content.includes('const previousItems = [...(order.value?.items ?? [])]')).toBe(true)
    expect(content.includes('const previousItemIds = new Set(')).toBe(true)
    expect(content.includes('const addedItemId = Number(order.value?.added_item_id)')).toBe(true)
    expect(content.includes('currentItems.find(item => Number(item?.id) === addedItemId)')).toBe(true)
  })

  it('uses the detected item id when syncing combo allocations', () => {
    expect(content.includes('syncOrderItemAllocations(orderId.value, addedAllocationTarget.id, addForm.allocations)')).toBe(true)
  })

  it('reopens allocation dialog using the exact created item id first', () => {
    expect(content.includes('find(i => Number(i?.id) === Number(addedAllocationTarget?.id) && i.requires_allocation && !i.allocation_complete)')).toBe(true)
  })

  it('does not fall back to product_id heuristics for combo sync target', () => {
    expect(content.includes('find(i => i.product_id === addForm.product_id && i.requires_allocation)')).toBe(false)
  })
})
