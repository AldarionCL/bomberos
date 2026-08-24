// Genera, a partir de un único color elegido en Configuración, la escala de
// tonos que usa todo el sitio (botones, enlaces, degradados). Se conserva el
// tono/saturación del color elegido y se recalcula la luminosidad según una
// escalera fija, para que el contraste sea consistente sin importar qué tan
// claro u oscuro sea el color que elija el administrador.
const LIGHTNESS_LADDER = {
    50: 0.97, 100: 0.93, 200: 0.86, 300: 0.76, 400: 0.65,
    500: 0.56, 600: 0.47, 700: 0.39, 800: 0.32, 900: 0.26, 950: 0.17,
}

function hexToRgb(hex) {
    const clean = hex.replace('#', '')
    return [0, 2, 4].map((i) => parseInt(clean.slice(i, i + 2), 16))
}

function rgbToHsl(r, g, b) {
    r /= 255; g /= 255; b /= 255
    const max = Math.max(r, g, b)
    const min = Math.min(r, g, b)
    let h = 0
    let s = 0
    const l = (max + min) / 2
    const d = max - min
    if (d !== 0) {
        s = d / (1 - Math.abs(2 * l - 1))
        switch (max) {
            case r: h = ((g - b) / d) % 6; break
            case g: h = (b - r) / d + 2; break
            default: h = (r - g) / d + 4
        }
        h *= 60
        if (h < 0) h += 360
    }
    return [h, s, l]
}

function hslToRgb(h, s, l) {
    const c = (1 - Math.abs(2 * l - 1)) * s
    const x = c * (1 - Math.abs(((h / 60) % 2) - 1))
    const m = l - c / 2
    let [r, g, b] = [0, 0, 0]
    if (h < 60) [r, g, b] = [c, x, 0]
    else if (h < 120) [r, g, b] = [x, c, 0]
    else if (h < 180) [r, g, b] = [0, c, x]
    else if (h < 240) [r, g, b] = [0, x, c]
    else if (h < 300) [r, g, b] = [x, 0, c]
    else [r, g, b] = [c, 0, x]
    return [r, g, b].map((v) => Math.round((v + m) * 255))
}

/** Aplica el color de marca al documento actual escribiendo las variables CSS --brand-*. */
export function aplicarColorMarca(hex) {
    if (!hex || !/^#[0-9a-fA-F]{6}$/.test(hex)) return
    const [r, g, b] = hexToRgb(hex)
    const [h, s] = rgbToHsl(r, g, b)
    const root = document.documentElement
    Object.entries(LIGHTNESS_LADDER).forEach(([stop, l]) => {
        const [rr, gg, bb] = hslToRgb(h, s, l)
        root.style.setProperty(`--brand-${stop}`, `${rr} ${gg} ${bb}`)
    })
}
