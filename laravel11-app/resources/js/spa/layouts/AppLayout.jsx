import {useState} from 'react'
import {NavLink, Outlet, useNavigate} from 'react-router-dom'
import clsx from 'clsx'
import {
    HomeIcon,
    Squares2X2Icon,
    BanknotesIcon,
    UsersIcon,
    NewspaperIcon,
    FolderIcon,
    Cog6ToothIcon,
    UserCircleIcon,
    ArrowLeftOnRectangleIcon,
    Bars3Icon,
    XMarkIcon,
    BuildingLibraryIcon,
} from '@heroicons/react/24/outline'
import {useAuth} from '../context/AuthContext'

function NavItem({to, icon: Icon, label, onClick}) {
    return (
        <NavLink
            to={to}
            onClick={onClick}
            className={({isActive}) =>
                clsx(
                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                    isActive
                        ? 'bg-brand-blue text-white shadow-sm'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                )
            }
        >
            <Icon className="h-5 w-5" />
            {label}
        </NavLink>
    )
}

export default function AppLayout() {
    const {user, logout} = useAuth()
    const navigate = useNavigate()
    const [open, setOpen] = useState(false)
    const esTesoreria = user?.esAdministrador || user?.esTesorero

    const handleLogout = async () => {
        await logout()
        navigate('/login', {replace: true})
    }

    const nav = (
        <nav className="flex flex-1 flex-col gap-1 px-3 py-4">
            <NavItem to="/" icon={HomeIcon} label="Inicio" onClick={() => setOpen(false)} />
            <NavItem to="/panel" icon={Squares2X2Icon} label="Panel de control" onClick={() => setOpen(false)} />
            <NavItem to="/mis-cuotas" icon={BanknotesIcon} label="Mis cuotas" onClick={() => setOpen(false)} />
            {esTesoreria && (
                <NavItem to="/tesoreria" icon={BuildingLibraryIcon} label="Tesorería" onClick={() => setOpen(false)} />
            )}
            {esTesoreria && <NavItem to="/socios" icon={UsersIcon} label="Socios" onClick={() => setOpen(false)} />}
            <NavItem to="/documentos" icon={FolderIcon} label="Documentos" onClick={() => setOpen(false)} />
            {user?.permisos?.gestionarNoticias && (
                <NavItem to="/noticias" icon={NewspaperIcon} label="Noticias" onClick={() => setOpen(false)} />
            )}
            {user?.esAdministrador && (
                <NavItem to="/configuracion" icon={Cog6ToothIcon} label="Configuración" onClick={() => setOpen(false)} />
            )}
            <div className="my-2 border-t border-slate-100" />
            <NavItem to="/perfil" icon={UserCircleIcon} label="Mi perfil" onClick={() => setOpen(false)} />
        </nav>
    )

    return (
        <div className="min-h-screen bg-slate-50">
            <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
                <div className="flex items-center gap-3">
                    <button className="rounded-lg p-2 hover:bg-slate-100 lg:hidden" onClick={() => setOpen(true)}>
                        <Bars3Icon className="h-6 w-6 text-slate-600" />
                    </button>
                    <img src="/img/logo.png" alt="Logo" className="h-9 w-9 rounded-full object-cover" />
                    <span className="hidden text-base font-bold text-slate-900 sm:block">
                        {import.meta.env.VITE_APP_NAME || 'Club'}
                    </span>
                </div>
                <div className="flex items-center gap-3">
                    <div className="hidden text-right sm:block">
                        <p className="text-sm font-semibold text-slate-800">{user?.name}</p>
                        <p className="text-xs text-slate-500">{user?.cargo || user?.rol}</p>
                    </div>
                    <img
                        src={user?.avatar}
                        alt={user?.name}
                        className="h-9 w-9 rounded-full ring-2 ring-slate-100"
                    />
                    <button
                        onClick={handleLogout}
                        className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-rose-600"
                        title="Cerrar sesión"
                    >
                        <ArrowLeftOnRectangleIcon className="h-5 w-5" />
                    </button>
                </div>
            </header>

            <div className="flex">
                <aside className="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col">
                    {nav}
                </aside>

                {open && (
                    <div className="fixed inset-0 z-40 lg:hidden">
                        <div className="absolute inset-0 bg-slate-900/50" onClick={() => setOpen(false)} />
                        <div className="relative flex h-full w-64 flex-col bg-white shadow-xl">
                            <div className="flex items-center justify-between px-4 py-4">
                                <span className="text-sm font-bold text-slate-900">Menú</span>
                                <button onClick={() => setOpen(false)}>
                                    <XMarkIcon className="h-6 w-6 text-slate-500" />
                                </button>
                            </div>
                            {nav}
                        </div>
                    </div>
                )}

                <main className="min-h-[calc(100vh-4rem)] flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-6xl">
                        <Outlet />
                    </div>
                </main>
            </div>
        </div>
    )
}
