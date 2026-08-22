import {Navigate, useLocation} from 'react-router-dom'
import {useAuth} from './context/AuthContext'
import {PageLoader} from './components/Ui'

export function RequireAuth({children}) {
    const {user, loading} = useAuth()
    const location = useLocation()

    if (loading) return <PageLoader />
    if (!user) return <Navigate to="/login" state={{from: location.pathname}} replace />

    return children
}

export function RequireRole({children, allow}) {
    const {user} = useAuth()
    const ok = allow(user)
    if (!ok) return <Navigate to="/" replace />
    return children
}
