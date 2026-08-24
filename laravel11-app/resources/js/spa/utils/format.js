const clp = new Intl.NumberFormat('es-CL', {style: 'currency', currency: 'CLP', maximumFractionDigits: 0})

export function formatMoney(value) {
    return clp.format(Number(value) || 0)
}

export function formatDate(value, options = {}) {
    if (!value) return '—'
    const date = new Date(value.length <= 10 ? `${value}T00:00:00` : value)
    if (Number.isNaN(date.getTime())) return '—'
    return date.toLocaleDateString('es-CL', {day: '2-digit', month: '2-digit', year: 'numeric', ...options})
}

export function formatMonthLabel(value) {
    if (!value) return '—'
    const [year, month] = value.split('-')
    const date = new Date(Number(year), Number(month) - 1, 1)
    return date.toLocaleDateString('es-CL', {month: 'long', year: 'numeric'})
}

export function formatRelativo(value) {
    if (!value) return '—'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return '—'
    const diffMs = Date.now() - date.getTime()
    const minutos = Math.round(diffMs / 60000)
    if (minutos < 1) return 'ahora'
    if (minutos < 60) return `hace ${minutos} min`
    const horas = Math.round(minutos / 60)
    if (horas < 24) return `hace ${horas} h`
    const dias = Math.round(horas / 24)
    if (dias < 7) return `hace ${dias} d`
    return formatDate(value)
}

export function relativeDays(value) {
    if (!value) return null
    const target = new Date(`${value}T00:00:00`)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    const diff = Math.round((target - today) / 86400000)
    return diff
}
