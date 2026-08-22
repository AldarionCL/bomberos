import {useQuery} from '@tanstack/react-query'
import {NewspaperIcon, ArrowDownTrayIcon} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Card, EmptyState, PageLoader} from '../components/Ui'
import FileIcon from '../components/FileIcon'
import {formatDate} from '../utils/format'
import {useAuth} from '../context/AuthContext'

export default function Home() {
    const {user} = useAuth()
    const {data, isLoading} = useQuery({
        queryKey: ['noticias', 'home'],
        queryFn: async () => (await client.get('/noticias', {params: {porPagina: 12}})).data,
    })

    const noticias = data?.data ?? []

    return (
        <div className="space-y-6">
            <div className="rounded-2xl bg-gradient-to-r from-brand-blue to-blue-700 px-6 py-8 text-white shadow-lg">
                <p className="text-sm font-medium text-blue-100">Bienvenido/a</p>
                <h1 className="mt-1 text-2xl font-bold">{user?.name}</h1>
                <p className="mt-2 max-w-2xl text-sm text-blue-100">
                    Aquí encontrarás las últimas noticias y novedades del club.
                </p>
            </div>

            {isLoading && <PageLoader />}

            {!isLoading && noticias.length === 0 && (
                <Card>
                    <EmptyState
                        icon={NewspaperIcon}
                        title="Sin publicaciones por ahora"
                        description="Cuando se publique una noticia, aparecerá aquí."
                    />
                </Card>
            )}

            {!isLoading && noticias.length > 0 && (
                <div className="grid gap-5 sm:grid-cols-2">
                    {noticias.map((n, i) => (
                        <Card key={n.id} className={i === 0 ? 'overflow-hidden sm:col-span-2' : 'overflow-hidden'}>
                            {n.imagen && (
                                <img
                                    src={n.imagen}
                                    alt={n.titulo}
                                    className={i === 0 ? 'h-64 w-full object-cover' : 'h-40 w-full object-cover'}
                                />
                            )}
                            <div className="p-5">
                                <p className="text-xs font-medium uppercase tracking-wide text-brand-blue">
                                    {formatDate(n.fechaPublicacion || n.creadoEn)}
                                </p>
                                <h2 className="mt-1 text-lg font-bold text-slate-900">{n.titulo}</h2>
                                {n.subtitulo && <p className="mt-1 text-sm text-slate-500">{n.subtitulo}</p>}
                                {n.contenido && (
                                    <p className="mt-3 whitespace-pre-line text-sm text-slate-600 line-clamp-6">
                                        {n.contenido}
                                    </p>
                                )}
                                {n.documento && (
                                    <a
                                        href={n.documento.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="mt-4 flex items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-brand-blue hover:bg-blue-50"
                                    >
                                        <FileIcon extension={n.documento.extension} />
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-medium text-slate-700">{n.documento.nombre}</p>
                                            <p className="text-xs text-slate-400">{n.documento.tipo}</p>
                                        </div>
                                        <ArrowDownTrayIcon className="h-4 w-4 shrink-0 text-brand-blue" />
                                    </a>
                                )}
                            </div>
                        </Card>
                    ))}
                </div>
            )}
        </div>
    )
}
