import {useEffect, useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import clsx from 'clsx'
import client from '../api/client'
import {Button, Card, CardHeader, Field, Input, PageLoader, Select} from '../components/Ui'
import {
    DatosGeneralesFields,
    DatosPersonalesFields,
    TallasFields,
    ObservacionesField,
    PERSONA_TABS_BASE,
    personaFormInicial,
} from '../components/PersonaCampos'
import {useAuth} from '../context/AuthContext'
import {useToast, apiErrorMessage} from '../context/ToastContext'

export default function Perfil() {
    const {user, setUser} = useAuth()
    const {notify} = useToast()
    const queryClient = useQueryClient()
    const [tab, setTab] = useState('general')
    const [form, setForm] = useState(null)
    const [foto, setFoto] = useState(null)

    const puedeGestionar = !!user?.permisos?.gestionarPersonas
    const personaId = user?.persona?.id

    const {data: catalogos} = useQuery({
        queryKey: ['catalogos'],
        queryFn: async () => (await client.get('/catalogos')).data,
    })

    useEffect(() => {
        if (user && !form) {
            setForm({
                name: user.name ?? '',
                email: user.email ?? '',
                password: '',
                idRole: user.idRole ?? '',
                ...personaFormInicial(user.persona),
            })
        }
    }, [user, form])

    const set = (key) => (e) => setForm((f) => ({...f, [key]: e.target.value}))

    const mutation = useMutation({
        mutationFn: () => {
            const data = new FormData()
            data.append('_method', 'PUT')
            Object.entries(form).forEach(([k, v]) => {
                if (k === 'password' && !v) return
                if (k === 'Activo') data.append(k, v ? '1' : '0')
                else data.append(k, v ?? '')
            })
            if (foto) data.append('foto', foto)
            return client.post(`/personas/${personaId}`, data)
        },
        onSuccess: ({data}) => {
            notify('Perfil actualizado correctamente.')
            setUser(data)
            queryClient.invalidateQueries({queryKey: ['personas']})
            setFoto(null)
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    if (!form) return <PageLoader />

    return (
        <div className="max-w-4xl space-y-6">
            <h1 className="text-xl font-bold text-slate-900 dark:text-slate-100">Mi perfil</h1>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-[220px_1fr]">
                <Card className="flex flex-col items-center gap-3 p-5 text-center">
                    <img
                        src={foto ? URL.createObjectURL(foto) : user?.avatar}
                        alt={user?.name}
                        className="h-28 w-28 rounded-full object-cover ring-4 ring-slate-100 dark:ring-slate-700"
                    />
                    <label className="cursor-pointer text-xs font-semibold text-brand-blue hover:underline">
                        Cambiar foto
                        <input type="file" accept="image/*" className="hidden" onChange={(e) => setFoto(e.target.files?.[0] ?? null)} />
                    </label>
                    <div>
                        <p className="font-semibold text-slate-800 dark:text-slate-200">{user?.name}</p>
                        <p className="text-xs text-slate-400 dark:text-slate-500">{user?.cargo}</p>
                    </div>
                </Card>

                <div className="space-y-6">
                    <Card>
                        <CardHeader title="Datos de usuario" />
                        <div className="grid gap-4 p-5 sm:grid-cols-2">
                            <Field label="Nombre completo"><Input value={form.name} onChange={set('name')} /></Field>
                            <Field label="Correo"><Input type="email" value={form.email} onChange={set('email')} /></Field>
                            <Field label="Nueva contraseña" hint="Deja en blanco para no cambiarla">
                                <Input type="password" value={form.password} onChange={set('password')} />
                            </Field>
                            {user?.esAdministrador && (
                                <Field label="Rol del sistema">
                                    <Select value={form.idRole ?? ''} onChange={set('idRole')}>
                                        {catalogos?.roles?.map((r) => <option key={r.id} value={r.id}>{r.Rol}</option>)}
                                    </Select>
                                </Field>
                            )}
                        </div>
                    </Card>

                    <Card>
                        <div className="flex gap-1 overflow-x-auto border-b border-slate-100 px-3 pt-3 dark:border-slate-700">
                            {PERSONA_TABS_BASE.map((t) => (
                                <button
                                    key={t.key}
                                    onClick={() => setTab(t.key)}
                                    className={clsx(
                                        'whitespace-nowrap rounded-t-lg px-3.5 py-2 text-sm font-medium transition',
                                        tab === t.key
                                            ? 'border-b-2 border-brand-blue text-brand-blue'
                                            : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'
                                    )}
                                >
                                    {t.label}
                                </button>
                            ))}
                        </div>

                        <div className="p-5">
                            {tab === 'general' && (
                                <DatosGeneralesFields form={form} set={set} catalogos={catalogos} puedeGestionar={puedeGestionar} esAdministrador={user?.esAdministrador} />
                            )}
                            {tab === 'personal' && <DatosPersonalesFields form={form} set={set} />}
                            {tab === 'tallas' && <TallasFields form={form} set={set} />}
                            {tab === 'observaciones' && <ObservacionesField form={form} set={set} />}
                        </div>

                        <div className="flex justify-end border-t border-slate-100 px-5 py-3 dark:border-slate-700">
                            <Button loading={mutation.isPending} onClick={() => mutation.mutate()}>
                                Guardar cambios
                            </Button>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    )
}
