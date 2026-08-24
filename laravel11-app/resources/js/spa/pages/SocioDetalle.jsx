import {useEffect, useState} from 'react'
import {useNavigate, useParams} from 'react-router-dom'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import clsx from 'clsx'
import {ArrowLeftIcon, PlusIcon, TrashIcon, ArrowDownTrayIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, CardHeader, Field, Input, PageLoader, Select} from '../components/Ui'
import FileIcon from '../components/FileIcon'
import {
    DatosGeneralesFields,
    DatosPersonalesFields,
    TallasFields,
    ObservacionesField,
    PERSONA_TABS_BASE,
    personaFormInicial,
} from '../components/PersonaCampos'
import {useToast, apiErrorMessage} from '../context/ToastContext'
import {useAuth} from '../context/AuthContext'

const TABS = [...PERSONA_TABS_BASE, {key: 'documentos', label: 'Documentos'}]

function AgregarDocumentoPrivado({idUsuario, tipos}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [nombre, setNombre] = useState('')
    const [idTipo, setIdTipo] = useState('')
    const [archivo, setArchivo] = useState(null)

    const mutation = useMutation({
        mutationFn: () => {
            const data = new FormData()
            data.append('nombre', nombre)
            data.append('idTipo', idTipo)
            data.append('asociadoA', idUsuario)
            data.append('archivo', archivo)
            return client.post('/documentos', data)
        },
        onSuccess: () => {
            notify('Documento agregado.')
            queryClient.invalidateQueries({queryKey: ['persona-documentos', idUsuario]})
            setNombre('')
            setIdTipo('')
            setArchivo(null)
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <div className="grid gap-3 rounded-xl border border-dashed border-slate-300 p-4 dark:border-slate-600 sm:grid-cols-4">
            <Input placeholder="Nombre del documento" value={nombre} onChange={(e) => setNombre(e.target.value)} />
            <Select value={idTipo} onChange={(e) => setIdTipo(e.target.value)}>
                <option value="">Tipo de documento...</option>
                {tipos?.map((t) => <option key={t.id} value={t.id}>{t.Tipo}</option>)}
            </Select>
            <input type="file" onChange={(e) => setArchivo(e.target.files?.[0] ?? null)} className="text-sm text-slate-600 dark:text-slate-300 sm:col-span-1" />
            <Button
                variant="secondary"
                disabled={!nombre || !idTipo || !archivo}
                loading={mutation.isPending}
                onClick={() => mutation.mutate()}
            >
                <PlusIcon className="h-4 w-4" /> Agregar
            </Button>
        </div>
    )
}

function DocumentosTab({idUsuario}) {
    const {user} = useAuth()
    const queryClient = useQueryClient()
    const {notify} = useToast()

    const {data: catalogos} = useQuery({
        queryKey: ['catalogos'],
        queryFn: async () => (await client.get('/catalogos')).data,
    })

    const {data, isLoading} = useQuery({
        queryKey: ['persona-documentos', idUsuario],
        queryFn: async () => (await client.get('/documentos', {params: {asociadoA: idUsuario, porPagina: 100}})).data,
    })

    const eliminar = useMutation({
        mutationFn: (id) => client.delete(`/documentos/${id}`),
        onSuccess: () => {
            notify('Documento eliminado.')
            queryClient.invalidateQueries({queryKey: ['persona-documentos', idUsuario]})
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    const documentos = data?.data ?? []

    return (
        <div className="space-y-4">
            <AgregarDocumentoPrivado idUsuario={idUsuario} tipos={catalogos?.tiposDocumentoPrivado} />

            {isLoading ? (
                <PageLoader />
            ) : documentos.length === 0 ? (
                <p className="py-6 text-center text-sm text-slate-400 dark:text-slate-500">Sin documentos asociados.</p>
            ) : (
                <div className="divide-y divide-slate-100 dark:divide-slate-700">
                    {documentos.map((d) => (
                        <div key={d.id} className="flex items-center justify-between gap-3 py-3">
                            <div className="flex items-center gap-3">
                                <FileIcon extension={d.extension} />
                                <div>
                                    <p className="text-sm font-medium text-slate-700 dark:text-slate-200">{d.nombre}</p>
                                    <p className="text-xs text-slate-400 dark:text-slate-500">{d.tipo}</p>
                                </div>
                            </div>
                            <div className="flex items-center gap-2">
                                <a href={d.url} target="_blank" rel="noreferrer" className="rounded-lg p-2 text-slate-500 hover:bg-brand-50 hover:text-brand-blue dark:text-slate-400 dark:hover:bg-brand-900/20">
                                    <ArrowDownTrayIcon className="h-4 w-4" />
                                </a>
                                {user?.esAdministrador && (
                                    <button
                                        onClick={() => confirm('¿Eliminar este documento?') && eliminar.mutate(d.id)}
                                        className="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-400"
                                    >
                                        <TrashIcon className="h-4 w-4" />
                                    </button>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    )
}

export default function SocioDetalle() {
    const {id} = useParams()
    const {user} = useAuth()
    const navigate = useNavigate()
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [tab, setTab] = useState('general')
    const [form, setForm] = useState(null)
    const [foto, setFoto] = useState(null)

    const puedeGestionar = !!user?.permisos?.gestionarPersonas

    const {data: persona, isLoading} = useQuery({
        queryKey: ['persona', id],
        queryFn: async () => (await client.get(`/personas/${id}`)).data,
    })

    const {data: catalogos} = useQuery({
        queryKey: ['catalogos'],
        queryFn: async () => (await client.get('/catalogos')).data,
    })

    useEffect(() => {
        if (persona && !form) {
            setForm({
                name: persona.name ?? '',
                email: persona.email ?? '',
                password: '',
                idRole: persona.idRole ?? '',
                ...personaFormInicial(persona.persona),
            })
        }
    }, [persona, form])

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
            return client.post(`/personas/${id}`, data)
        },
        onSuccess: ({data}) => {
            notify('Ficha actualizada correctamente.')
            queryClient.setQueryData(['persona', id], data)
            queryClient.invalidateQueries({queryKey: ['personas']})
            setFoto(null)
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    if (isLoading || !form) return <PageLoader />

    return (
        <div className="space-y-6">
            <button onClick={() => navigate('/socios')} className="flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                <ArrowLeftIcon className="h-4 w-4" /> Volver a socios
            </button>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-[220px_1fr]">
                <Card className="flex flex-col items-center gap-3 p-5 text-center">
                    <img
                        src={foto ? URL.createObjectURL(foto) : (persona.avatar)}
                        alt={persona.name}
                        className="h-28 w-28 rounded-full object-cover ring-4 ring-slate-100 dark:ring-slate-700"
                    />
                    {puedeGestionar && (
                        <label className="cursor-pointer text-xs font-semibold text-brand-blue hover:underline">
                            Cambiar foto
                            <input type="file" accept="image/*" className="hidden" onChange={(e) => setFoto(e.target.files?.[0] ?? null)} />
                        </label>
                    )}
                    <div>
                        <p className="font-semibold text-slate-800 dark:text-slate-200">{persona.name}</p>
                        <p className="text-xs text-slate-400 dark:text-slate-500">{persona.persona?.cargo}</p>
                    </div>
                </Card>

                <div className="space-y-6">
                    <Card>
                        <CardHeader title="Datos de usuario" />
                        <div className="grid gap-4 p-5 sm:grid-cols-2">
                            <Field label="Nombre completo"><Input value={form.name} onChange={set('name')} disabled={!puedeGestionar && user?.id !== persona.idUsuario} /></Field>
                            <Field label="Correo"><Input type="email" value={form.email} onChange={set('email')} /></Field>
                            <Field label="Nueva contraseña" hint="Deja en blanco para no cambiarla">
                                <Input type="password" value={form.password} onChange={set('password')} />
                            </Field>
                            {user?.esAdministrador && (
                                <Field label="Rol del sistema">
                                    <Select value={form.idRole} onChange={set('idRole')}>
                                        {catalogos?.roles?.map((r) => <option key={r.id} value={r.id}>{r.Rol}</option>)}
                                    </Select>
                                </Field>
                            )}
                        </div>
                    </Card>

                    <Card>
                        <div className="flex gap-1 overflow-x-auto border-b border-slate-100 px-3 pt-3 dark:border-slate-700">
                            {TABS.map((t) => (
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
                            {tab === 'documentos' && <DocumentosTab idUsuario={persona.idUsuario} />}
                        </div>

                        {tab !== 'documentos' && (
                            <div className="flex justify-end border-t border-slate-100 px-5 py-3 dark:border-slate-700">
                                <Button loading={mutation.isPending} onClick={() => mutation.mutate()}>
                                    Guardar cambios
                                </Button>
                            </div>
                        )}
                    </Card>
                </div>
            </div>
        </div>
    )
}
