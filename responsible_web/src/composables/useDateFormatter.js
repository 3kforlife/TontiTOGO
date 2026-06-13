export function useDateFormatter() {
  function formatDate(d, includeTime = false) {
    if (!d) return '—'

    let dateObj

    // Si la date est au format "d/m/Y" (ex: "25/06/2026")
    if (typeof d === 'string' && d.includes('/')) {
      const [day, month, year] = d.split('/').map(Number)
      dateObj = new Date(year, month - 1, day)
    } else {
      dateObj = new Date(d)
    }

    // Vérifier si la date est valide
    if (isNaN(dateObj.getTime())) return '—'

    if (includeTime) {
      return dateObj.toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })
    } else {
      return dateObj.toLocaleDateString('fr-FR')
    }
  }

  return { formatDate }
}
