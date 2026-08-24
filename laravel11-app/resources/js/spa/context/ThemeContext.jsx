import {createContext, useContext, useEffect, useState} from 'react'

const ThemeContext = createContext(null)

function leerPreferenciaInicial() {
    try {
        const guardado = localStorage.getItem('theme')
        if (guardado === 'dark' || guardado === 'light') return guardado === 'dark'
    } catch {
        // localStorage no disponible: seguimos con la preferencia del sistema.
    }
    return window.matchMedia?.('(prefers-color-scheme: dark)').matches ?? false
}

export function ThemeProvider({children}) {
    const [oscuro, setOscuro] = useState(leerPreferenciaInicial)

    useEffect(() => {
        document.documentElement.classList.toggle('dark', oscuro)
        try {
            localStorage.setItem('theme', oscuro ? 'dark' : 'light')
        } catch {
            // Modo privado o storage lleno: la preferencia solo dura la sesión.
        }
    }, [oscuro])

    return (
        <ThemeContext.Provider value={{oscuro, alternar: () => setOscuro((o) => !o)}}>
            {children}
        </ThemeContext.Provider>
    )
}

export function useTheme() {
    const ctx = useContext(ThemeContext)
    if (!ctx) throw new Error('useTheme debe usarse dentro de <ThemeProvider>')
    return ctx
}
