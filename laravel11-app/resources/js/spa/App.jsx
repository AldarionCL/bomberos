import {BrowserRouter, Routes, Route} from 'react-router-dom'
import {AuthProvider} from './context/AuthContext'
import {ToastProvider} from './context/ToastContext'
import {RequireAuth, RequireRole} from './RequireAuth'
import AppLayout from './layouts/AppLayout'
import Login from './pages/Login'
import Home from './pages/Home'
import Dashboard from './pages/Dashboard'
import MisCuotas from './pages/MisCuotas'
import Tesoreria from './pages/Tesoreria'
import Personas from './pages/Personas'
import Noticias from './pages/Noticias'
import Documentos from './pages/Documentos'
import Configuracion from './pages/Configuracion'
import Perfil from './pages/Perfil'

const esTesoreria = (user) => user?.esAdministrador || user?.esTesorero
const esAdmin = (user) => user?.esAdministrador
const gestionaNoticias = (user) => !!user?.permisos?.gestionarNoticias

export default function App() {
    return (
        <ToastProvider>
            <AuthProvider>
                <BrowserRouter>
                    <Routes>
                        <Route path="/login" element={<Login />} />
                        <Route
                            element={
                                <RequireAuth>
                                    <AppLayout />
                                </RequireAuth>
                            }
                        >
                            <Route path="/" element={<Home />} />
                            <Route path="/panel" element={<Dashboard />} />
                            <Route path="/mis-cuotas" element={<MisCuotas />} />
                            <Route path="/perfil" element={<Perfil />} />
                            <Route
                                path="/tesoreria"
                                element={<RequireRole allow={esTesoreria}><Tesoreria /></RequireRole>}
                            />
                            <Route
                                path="/socios"
                                element={<RequireRole allow={esTesoreria}><Personas /></RequireRole>}
                            />
                            <Route
                                path="/noticias"
                                element={<RequireRole allow={gestionaNoticias}><Noticias /></RequireRole>}
                            />
                            <Route path="/documentos" element={<Documentos />} />
                            <Route
                                path="/configuracion"
                                element={<RequireRole allow={esAdmin}><Configuracion /></RequireRole>}
                            />
                        </Route>
                    </Routes>
                </BrowserRouter>
            </AuthProvider>
        </ToastProvider>
    )
}
