import {useState} from 'react'
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query'
import {PlusIcon, PencilIcon, TrashIcon, NewspaperIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Button, Card, EmptyState, Field, Input, Modal, PageLoader, Select, Textarea} from '../components/Ui'
import Badge from '../components/Badge'
import FileIcon from '../components/FileIcon'
import {formatDate} from '../utils/format'
import {useToast, apiErrorMessage} from '../context/ToastContext'

const ESTADOS = {1: 'Publicado', 2: 'Agendado', 3: 'Expirado'}

function NoticiaModal({noticia, onClose, catalogos}) {
    const queryClient = useQueryClient()
    const {notify} = useToast()
    const [form, setForm] = useState({
        titulo: noticia?.titulo ?? '',
        subtitulo: noticia?.subtitulo ?? '',
        contenido: noticia?.contenido ?? '',
        estado: noticia?.estado ?? 1,
        fechaPublicacion: noticia?.fechaPublicacion ?? new Date().toISOString().slice(0, 10),
        documentoNombre: noticia?.documento?.nombre ?? '',
        documentoTipo: '',
    })
    const [imagen, setImagen] = useState(null)
    const [documentoArchivo, setDocumentoArchivo] = useState(null)

    const set = (key) => (e) => setForm((f) => ({...f, [key]: e.target.value}))

    const mutation = useMutation({
        mutationFn: () => {
            const data = new FormData()
            Object.entries(form).forEach(([k, v]) => data.append(k, v ?? ''))
            if (imagen) data.append('imagen', imagen)
            if (documentoArchivo) data.append('documentoArchivo', documentoArchivo)
            if (noticia) {
                data.append('_method', 'PUT')
                return client.post(`/noticias/${noticia.id}`, data)
            }
            return client.post('/noticias', data)
        },
        onSuccess: () => {
            notify(noticia ? 'Noticia actualizada.' : 'Noticia publicada.')
            queryClient.invalidateQueries({queryKey: ['noticias-admin']})
            queryClient.invalidateQueries({queryKey: ['noticias', 'home']})
            onClose()
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    return (
        <Modal open onClose={onClose} title={noticia ? 'Editar noticia' : 'Nueva noticia'} wide>
            <div className="space-y-4">
                <Field label="Título"><Input value={form.titulo} onChange={set('titulo')} /></Field>
                <Field label="Subtítulo"><Input value={form.subtitulo} onChange={set('subtitulo')} /></Field>
                <Field label="Contenido"><Textarea rows={5} value={form.contenido} onChange={set('contenido')} /></Field>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Estado">
                        <Select value={form.estado} onChange={set('estado')}>
                            {Object.entries(ESTADOS).map(([id, label]) => <option key={id} value={id}>{label}</option>)}
                        </Select>
                    </Field>
                    <Field label="Fecha de publicación">
                        <Input type="date" value={form.fechaPublicacion} onChange={set('fechaPublicacion')} />
                    </Field>
                </div>
                <Field label="Imagen de portada">
                    <input type="file" accept="image/*" onChange={(e) => setImagen(e.target.files?.[0] ?? null)} className="block w-full text-sm text-slate-600" />
                </Field>

                <div className="rounded-xl border border-slate-200 p-4">
                    <p className="mb-3 text-sm font-semibold text-slate-700">Documento adjunto (opcional)</p>

                    {noticia?.documento && (
                        <div className="mb-3 flex items-center gap-3 rounded-lg bg-slate-50 p-3">
                            <FileIcon extension={noticia.documento.extension} />
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-medium text-slate-700">{noticia.documento.nombre}</p>
                                <a href={noticia.documento.url} target="_blank" rel="noreferrer" className="text-xs text-brand-blue hover:underline">
                                    Ver archivo actual
                                </a>
                            </div>
                        </div>
                    )}

                    <div className="grid gap-3 sm:grid-cols-2">
                        <Field label="Nombre del documento">
                            <Input value={form.documentoNombre} onChange={set('documentoNombre')} placeholder="Ej: Circular informativa" />
                        </Field>
                        <Field label="Tipo de documento">
                            <Select value={form.documentoTipo} onChange={set('documentoTipo')}>
                                <option value="">Seleccione...</option>
                                {catalogos?.tiposDocumento?.map((t) => (
                                    <option key={t.id} value={t.id}>{t.Tipo}</option>
                                ))}
                            </Select>
                        </Field>
                    </div>
                    <Field label={noticia?.documento ? 'Reemplazar archivo (opcional)' : 'Archivo'} hint="PDF, Word, Excel o imagen">
                        <input
                            type="file"
                            onChange={(e) => setDocumentoArchivo(e.target.files?.[0] ?? null)}
                            className="mt-1 block w-full text-sm text-slate-600"
                        />
                    </Field>
                </div>
            </div>
            <div className="mt-5 flex justify-end gap-2">
                <Button variant="secondary" onClick={onClose}>Cancelar</Button>
                <Button loading={mutation.isPending} disabled={!form.titulo} onClick={() => mutation.mutate()}>
                    {noticia ? 'Guardar cambios' : 'Publicar'}
                </Button>
            </div>
        </Modal>
    )
}

export default function Noticias() {
    const [modalAbierto, setModalAbierto] = useState(false)
    const [editando, setEditando] = useState(null)
    const queryClient = useQueryClient()
    const {notify} = useToast()

    const {data, isLoading} = useQuery({
        queryKey: ['noticias-admin'],
        queryFn: async () => (await client.get('/noticias', {params: {todas: 1, porPagina: 50}})).data,
    })

    const {data: catalogos} = useQuery({
        queryKey: ['catalogos'],
        queryFn: async () => (await client.get('/catalogos')).data,
    })

    const eliminar = useMutation({
        mutationFn: (id) => client.delete(`/noticias/${id}`),
        onSuccess: () => {
            notify('Noticia eliminada.')
            queryClient.invalidateQueries({queryKey: ['noticias-admin']})
        },
        onError: (err) => notify(apiErrorMessage(err), 'error'),
    })

    const noticias = data?.data ?? []

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-xl font-bold text-slate-900">Noticias</h1>
                <Button onClick={() => setModalAbierto(true)}>
                    <PlusIcon className="h-4 w-4" /> Nueva noticia
                </Button>
            </div>

            <Card>
                {isLoading ? (
                    <PageLoader />
                ) : noticias.length === 0 ? (
                    <EmptyState icon={NewspaperIcon} title="No hay noticias publicadas" />
                ) : (
                    <div className="divide-y divide-slate-100">
                        {noticias.map((n) => (
                            <div key={n.id} className="flex items-center justify-between gap-4 px-5 py-4">
                                <div className="flex items-center gap-3">
                                    {n.imagen && <img src={n.imagen} alt="" className="h-12 w-12 rounded-lg object-cover" />}
                                    <div>
                                        <p className="font-medium text-slate-800">{n.titulo}</p>
                                        <p className="text-xs text-slate-400">
                                            {formatDate(n.fechaPublicacion)} · {n.autor}
                                            {n.documento && ' · con documento adjunto'}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Badge tone={n.estado === 1 ? 'Aprobado' : n.estado === 2 ? 'Pendiente' : 'Cancelado'}>
                                        {ESTADOS[n.estado]}
                                    </Badge>
                                    <button onClick={() => setEditando(n)} className="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                                        <PencilIcon className="h-4 w-4" />
                                    </button>
                                    <button
                                        onClick={() => confirm('¿Eliminar esta noticia?') && eliminar.mutate(n.id)}
                                        className="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-600"
                                    >
                                        <TrashIcon className="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </Card>

            {modalAbierto && <NoticiaModal onClose={() => setModalAbierto(false)} catalogos={catalogos} />}
            {editando && <NoticiaModal noticia={editando} onClose={() => setEditando(null)} catalogos={catalogos} />}
        </div>
    )
}
