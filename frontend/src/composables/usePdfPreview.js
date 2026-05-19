import { ref } from 'vue'
import { apiDownloadFile, apiFetchBlob } from '../services/api'

/**
 * Vista previa de PDF en SPA (blob + iframe) y descarga con el mismo path API.
 * @param {() => string} getToken Bearer JWT
 */
export function usePdfPreview(getToken) {
  const pdfPreviewOpen = ref(false)
  const pdfPreviewLoading = ref(false)
  const pdfPreviewUrl = ref(null)
  const pdfPreviewTitle = ref('')
  /** Path relativo API usado para GET blob y para descarga (incl. query site_id). */
  const pdfDownloadPath = ref('')

  /**
   * @param {string} apiPath ej. /maintenance/purchases/1/pdf?site_id=2
   * @param {string} title Título del modal
   * @param {string} [downloadPath] Si se omite, usa apiPath
   */
  async function openPdfPreview(apiPath, title, downloadPath) {
    pdfPreviewTitle.value = title
    pdfDownloadPath.value = downloadPath ?? apiPath
    pdfPreviewOpen.value = true
    pdfPreviewLoading.value = true
    if (pdfPreviewUrl.value) {
      URL.revokeObjectURL(pdfPreviewUrl.value)
      pdfPreviewUrl.value = null
    }
    try {
      const blob = await apiFetchBlob(apiPath, getToken())
      pdfPreviewUrl.value = URL.createObjectURL(blob)
    } catch (e) {
      closePdfPreview()
      throw e
    } finally {
      pdfPreviewLoading.value = false
    }
  }

  function closePdfPreview() {
    pdfPreviewOpen.value = false
    pdfPreviewTitle.value = ''
    pdfDownloadPath.value = ''
    if (pdfPreviewUrl.value) {
      URL.revokeObjectURL(pdfPreviewUrl.value)
      pdfPreviewUrl.value = null
    }
  }

  async function downloadPdfPreview(fallbackName = 'documento.pdf') {
    if (!pdfDownloadPath.value) return
    await apiDownloadFile(pdfDownloadPath.value, getToken(), fallbackName)
  }

  return {
    pdfPreviewOpen,
    pdfPreviewLoading,
    pdfPreviewUrl,
    pdfPreviewTitle,
    pdfDownloadPath,
    openPdfPreview,
    closePdfPreview,
    downloadPdfPreview,
  }
}
