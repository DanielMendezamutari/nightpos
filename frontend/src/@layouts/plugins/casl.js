import { useAbility } from '@casl/vue'
import { ability as globalAbility } from '@/plugins/casl/ability'

/**
 * Comprueba permiso de menú / UI usando la instancia global de CASL
 * (evita depender de getCurrentInstance, que falla al renderizar el nav).
 */
export const can = (action, subject) => {
  if (!action && !subject)
    return true

  return globalAbility.can(action, subject)
}

/**
 * Check if user can view item based on it's ability
 * Based on item's action and subject & Hide group if all of it's children are hidden
 * @param {object} item navigation object item
 */
export const canViewNavMenuGroup = item => {
  const hasAnyVisibleChild = item.children.some(i => can(i.action, i.subject))

  // If subject and action is defined in item => Return based on children visibility (Hide group if no child is visible)
  // Else check for ability using provided subject and action along with checking if has any visible child
  if (!(item.action && item.subject))
    return hasAnyVisibleChild
  
  return can(item.action, item.subject) && hasAnyVisibleChild
}
export const canNavigate = to => {
  const ability = useAbility()
    
  return to.matched.some(route => ability.can(route.meta.action, route.meta.subject))
}
