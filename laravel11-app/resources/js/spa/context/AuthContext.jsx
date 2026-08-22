import {createContext, useCallback, useContext, useEffect, useState} from 'react'
import client, {ensureCsrfCookie} from '../api/client'

const AuthContext = createContext(null)

export function AuthProvider({children}) {
    const [user, setUser] = useState(null)
    const [loading, setLoading] = useState(true)

    const refresh = useCallback(async () => {
        try {
            const {data} = await client.get('/me')
            setUser(data)
        } catch {
            setUser(null)
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => {
        refresh()
    }, [refresh])

    const login = useCallback(async (email, password) => {
        await ensureCsrfCookie()
        const {data} = await client.post('/login', {email, password})
        setUser(data)
        return data
    }, [])

    const logout = useCallback(async () => {
        await client.post('/logout')
        setUser(null)
    }, [])

    return (
        <AuthContext.Provider value={{user, loading, login, logout, refresh, setUser}}>
            {children}
        </AuthContext.Provider>
    )
}

export function useAuth() {
    const ctx = useContext(AuthContext)
    if (!ctx) throw new Error('useAuth debe usarse dentro de <AuthProvider>')
    return ctx
}
