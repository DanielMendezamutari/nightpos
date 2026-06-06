/**
 * Evita que la rueda del mouse cambie valores en inputs type="number" con foco.
 */
export function preventNumberWheelScroll(event) {
  const target = event.target

  if (
    target instanceof HTMLInputElement
    && target.type === 'number'
    && document.activeElement === target
  ) {
    event.preventDefault()
  }
}
