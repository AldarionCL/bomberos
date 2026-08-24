import {createContext, useCallback, useContext, useEffect, useState} from 'react'
import client from '../api/client'
import {aplicarColorMarca} from '../utils/theme'

const SiteConfigContext = createContext(null)

const VALOR_INICIAL = {
    nombreGrupo: import.meta.env.VITE_APP_NAME || 'Club',
    logo: '/img/logo.png',
    color: '#023aab',
}

export function SiteConfigProvider({children}) {
    const [config, setConfig] = useState(VALOR_INICIAL)

    const recargar = useCallback(async () => {
        try {
            const {data} = await client.get('/configuracion/sitio')
            const siguiente = {
                nombreGrupo: data.nombreGrupo || VALOR_INICIAL.nombreGrupo,
                logo: data.logo || VALOR_INICIAL.logo,
                color: data.color || VALOR_INICIAL.color,
            }
            setConfig(siguiente)
            aplicarColorMarca(siguiente.color)
        } catch {
            // Sin conexión o backend caído: seguimos con la marca por defecto.
        }
    }, [])

    useEffect(() => {
        recargar()
    }, [recargar])

    return (
        <SiteConfigContext.Provider value={{...config, recargar}}>
            {children}
        </SiteConfigContext.Provider>
    )
}

export function useSiteConfig() {
    const ctx = useContext(SiteConfigContext)
    if (!ctx) throw new Error('useSiteConfig debe usarse dentro de <SiteConfigProvider>')
    return ctx
}
