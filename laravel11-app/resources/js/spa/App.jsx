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
import SocioDetalle from './pages/SocioDetalle'
import Noticias from './pages/Noticias'
import Documentos from './pages/Documentos'
import Configuracion from './pages/Configuracion'
import Perfil from './pages/Perfil'
import MiCarnet from './pages/MiCarnet'
import VerificarCarnet from './pages/VerificarCarnet'
import Licencias from './pages/Licencias'

const esTesoreria = (user) => user?.esAdministrador || user?.esTesorero
const esAdmin = (user) => user?.esAdministrador
const gestionaNoticias = (user) => !!user?.permisos?.gestionarNoticias
const vePersonas = (user) => !!user?.permisos?.verPersonas

export default function App() {
    return (
        <ToastProvider>
            <AuthProvider>
                <BrowserRouter basename="/v2">
                    <Routes>
                        <Route path="/login" element={<Login />} />
                        <Route path="/verificar/:token" element={<VerificarCarnet />} />
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
                            <Route path="/mi-carnet" element={<MiCarnet />} />
                            <Route path="/licencias" element={<Licencias />} />
                            <Route
                                path="/tesoreria"
                                element={<RequireRole allow={esTesoreria}><Tesoreria /></RequireRole>}
                            />
                            <Route
                                path="/socios"
                                element={<RequireRole allow={vePersonas}><Personas /></RequireRole>}
                            />
                            <Route
                                path="/socios/:id"
                                element={<RequireRole allow={vePersonas}><SocioDetalle /></RequireRole>}
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
