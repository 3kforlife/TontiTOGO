export function useDateFormatter() {
  function formatDate(d, includeTime = false) {
    if (!d) return '—'

    let dateObj

    // Formats API : "d/m/Y" ou "d/m/Y H:i".
    if (typeof d === 'string') {
      const match = d.match(
        /^(\d{1,2})\/(\d{1,2})\/(\d{4})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/
      )

      if (match) {
        const [, day, month, year, hours = 0, minutes = 0, seconds = 0] = match
        dateObj = new Date(
          Number(year),
          Number(month) - 1,
          Number(day),
          Number(hours),
          Number(minutes),
          Number(seconds)
        )
      } else {
        dateObj = new Date(d)
      }
    } else {
      dateObj = new Date(d)
    }

    if (isNaN(dateObj.getTime())) return '—'

    if (includeTime) {
      return dateObj.toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })
    } else {
      return dateObj.toLocaleDateString('fr-FR')
    }
  }

  return { formatDate }
}
