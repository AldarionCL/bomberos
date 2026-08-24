import {useState} from 'react'
import {Navigate, useLocation, useNavigate} from 'react-router-dom'
import {useAuth} from '../context/AuthContext'
import {Button, Field, Input} from '../components/Ui'
import {apiErrorMessage} from '../context/ToastContext'
import {useSiteConfig} from '../context/SiteConfigContext'

export default function Login() {
    const {user, login} = useAuth()
    const navigate = useNavigate()
    const location = useLocation()
    const [email, setEmail] = useState('')
    const [password, setPassword] = useState('')
    const [error, setError] = useState('')
    const [loading, setLoading] = useState(false)
    const {nombreGrupo, logo} = useSiteConfig()

    if (user) {
        return <Navigate to={location.state?.from ?? '/'} replace />
    }

    const submit = async (e) => {
        e.preventDefault()
        setError('')
        setLoading(true)
        try {
            await login(email, password)
            navigate(location.state?.from ?? '/', {replace: true})
        } catch (err) {
            setError(apiErrorMessage(err, 'No pudimos iniciar sesión. Verifica tus credenciales.'))
        } finally {
            setLoading(false)
        }
    }

    return (
        <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-950 via-brand-blue to-brand-800 px-4">
            <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl dark:bg-slate-800">
                <div className="mb-6 flex flex-col items-center gap-3 text-center">
                    <img src={logo} alt="Logo" className="h-16 w-16 rounded-full object-cover shadow" />
                    <div>
                        <h1 className="text-xl font-bold text-slate-900 dark:text-slate-100">
                            {nombreGrupo}
                        </h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400">Ingresa con tu cuenta de socio</p>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-4">
                    <Field label="Correo electrónico">
                        <Input
                            type="email"
                            required
                            autoFocus
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="tucorreo@correo.com"
                        />
                    </Field>
                    <Field label="Contraseña">
                        <Input
                            type="password"
                            required
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="••••••••"
                        />
                    </Field>

                    {error && (
                        <p className="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">{error}</p>
                    )}

                    <Button type="submit" className="w-full justify-center" loading={loading}>
                        Ingresar
                    </Button>
                </form>
            </div>
        </div>
    )
}
