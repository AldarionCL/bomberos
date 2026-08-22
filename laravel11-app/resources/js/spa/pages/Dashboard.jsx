import {useQuery} from '@tanstack/react-query'
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts'
import {
    BanknotesIcon,
    CalendarDaysIcon,
    UserGroupIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/react/24/outline'
import client from '../api/client'
import {Card, CardHeader, PageLoader} from '../components/Ui'
import StatCard from '../components/StatCard'
import Badge from '../components/Badge'
import {formatMoney, formatMonthLabel} from '../utils/format'
import {useAuth} from '../context/AuthContext'

const INK_SECONDARY = '#52514e'
const INK_MUTED = '#898781'
const GRID = '#e1e0d9'

const ESTADO_COLOR = {
    Pendiente: '#fab219',
    Aprobado: '#0ca30c',
    'Pendiente Aprobación': '#ec835a',
    Rechazado: '#d03b3b',
    Cancelado: '#898781',
}

function TooltipMoney({active, payload, label}) {
    if (!active || !payload?.length) return null
    return (
        <div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-md">
            <p className="mb-1 font-semibold text-slate-700">{formatMonthLabel(label)}</p>
            {payload.map((p) => (
                <p key={p.dataKey} style={{color: p.color}} className="font-medium">
                    {p.name}: {formatMoney(p.value)}
                </p>
            ))}
        </div>
    )
}

export default function Dashboard() {
    const {user} = useAuth()
    const esTesoreria = user?.esAdministrador || user?.esTesorero

    const {data, isLoading} = useQuery({
        queryKey: ['dashboard'],
        queryFn: async () => (await client.get('/dashboard')).data,
    })

    if (isLoading) return <PageLoader />

    const personal = data?.personal
    const org = data?.organizacion

    return (
        <div className="space-y-6">
            <h1 className="text-xl font-bold text-slate-900">Panel de control</h1>

            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    label="Estado de socio"
                    value={personal?.estadoSocio}
                    icon={UserGroupIcon}
                    tone={personal?.estadoSocio === 'Activo' ? 'green' : 'amber'}
                />
                <StatCard
                    label="Cuota vigente"
                    value={personal?.estadoCuotaVigente}
                    icon={BanknotesIcon}
                    tone="blue"
                    hint={personal?.montoCuotaMensual ? formatMoney(personal.montoCuotaMensual) + ' / mes' : null}
                />
                <StatCard
                    label="Cuotas pendientes"
                    value={personal?.cuotasPendientes ?? 0}
                    icon={ClockIcon}
                    tone={personal?.cuotasPendientes > 0 ? 'amber' : 'green'}
                />
                <StatCard
                    label="Días de inactividad"
                    value={personal?.diasInactividad ?? 0}
                    icon={CalendarDaysIcon}
                    tone={personal?.diasInactividad > 0 ? 'amber' : 'slate'}
                />
            </div>

            {esTesoreria && org && (
                <>
                    <h2 className="pt-2 text-base font-bold text-slate-900">Organización</h2>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <StatCard label="Total en caja" value={formatMoney(org.totalCaja)} icon={BanknotesIcon} tone="green" />
                        <StatCard label="Socios activos" value={org.sociosActivos} icon={UserGroupIcon} tone="blue" />
                        <StatCard
                            label="Pagos por aprobar"
                            value={org.cuotasPorAprobar}
                            icon={ExclamationTriangleIcon}
                            tone={org.cuotasPorAprobar > 0 ? 'amber' : 'slate'}
                            hint={org.montoPorAprobar ? formatMoney(org.montoPorAprobar) : null}
                        />
                        <StatCard label="Total egresos" value={formatMoney(org.totalGastos)} icon={ArrowTrendingDownIcon} tone="red" />
                    </div>

                    <div className="grid gap-5 lg:grid-cols-2">
                        <Card>
                            <CardHeader title="Ingresos vs. egresos" subtitle="Últimos 12 meses" />
                            <div className="h-72 px-2 py-4">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={org.serieMensual} barCategoryGap="25%" barGap={2}>
                                        <CartesianGrid vertical={false} stroke={GRID} />
                                        <XAxis
                                            dataKey="mes"
                                            tickFormatter={(m) => formatMonthLabel(m).slice(0, 3)}
                                            tick={{fill: INK_MUTED, fontSize: 11}}
                                            axisLine={{stroke: GRID}}
                                            tickLine={false}
                                        />
                                        <YAxis
                                            tick={{fill: INK_MUTED, fontSize: 11}}
                                            axisLine={false}
                                            tickLine={false}
                                            width={40}
                                            tickFormatter={(v) => `${Math.round(v / 1000)}k`}
                                        />
                                        <Tooltip content={<TooltipMoney />} cursor={{fill: '#f1f5f9'}} />
                                        <Legend
                                            wrapperStyle={{fontSize: 12, color: INK_SECONDARY}}
                                            iconType="circle"
                                        />
                                        <Bar dataKey="ingresos" name="Ingresos" fill="#2a78d6" radius={[4, 4, 0, 0]} maxBarSize={22} isAnimationActive={false} />
                                        <Bar dataKey="egresos" name="Egresos" fill="#eb6834" radius={[4, 4, 0, 0]} maxBarSize={22} isAnimationActive={false} />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </Card>

                        <Card>
                            <CardHeader title="Cuotas por estado" subtitle="Histórico completo" />
                            <div className="h-72 px-2 py-4">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={org.cuotasPorEstado}
                                        layout="vertical"
                                        margin={{left: 8, right: 24}}
                                    >
                                        <CartesianGrid horizontal={false} stroke={GRID} />
                                        <XAxis type="number" hide />
                                        <YAxis
                                            type="category"
                                            dataKey="estado"
                                            width={130}
                                            tick={{fill: INK_SECONDARY, fontSize: 12}}
                                            axisLine={false}
                                            tickLine={false}
                                        />
                                        <Tooltip
                                            cursor={{fill: '#f1f5f9'}}
                                            formatter={(value) => [value, 'Cuotas']}
                                            labelFormatter={() => ''}
                                        />
                                        <Bar dataKey="total" radius={[0, 4, 4, 0]} maxBarSize={22} isAnimationActive={false} label={{position: 'right', fill: INK_SECONDARY, fontSize: 12}}>
                                            {org.cuotasPorEstado.map((entry) => (
                                                <Cell key={entry.estado} fill={ESTADO_COLOR[entry.estado] ?? '#898781'} />
                                            ))}
                                        </Bar>
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </Card>
                    </div>
                </>
            )}

            {!esTesoreria && (
                <Card>
                    <CardHeader title="Tu situación con el club" />
                    <div className="flex flex-wrap items-center gap-3 p-5">
                        <Badge tone={personal?.estadoCuotaVigente}>{personal?.estadoCuotaVigente}</Badge>
                        <span className="text-sm text-slate-500">
                            Periodo vigente: {personal?.periodoCuotaVigente ? formatMonthLabel(personal.periodoCuotaVigente.slice(0, 7)) : 'sin registro'}
                        </span>
                    </div>
                </Card>
            )}
        </div>
    )
}
