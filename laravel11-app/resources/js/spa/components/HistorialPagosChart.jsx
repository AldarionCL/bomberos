import {Bar, BarChart, CartesianGrid, Cell, ResponsiveContainer, Tooltip, XAxis, YAxis} from 'recharts'
import {formatMoney, formatMonthLabel} from '../utils/format'

const INK_MUTED = '#898781'
const GRID = '#e1e0d9'

const COLOR = {
    Aprobado: '#0ca30c',
    'Pendiente Aprobacion': '#ec835a',
    Pendiente: '#fab219',
    Vencida: '#d03b3b',
    Rechazado: '#d03b3b',
    Cancelado: '#898781',
    'Sin registro': '#e1e0d9',
}

function estadoReal(cuota) {
    if (!cuota) return 'Sin registro'
    if (cuota.estadoLabel === 'Pendiente' && cuota.vencimiento && cuota.vencimiento < new Date().toISOString().slice(0, 10)) {
        return 'Vencida'
    }
    return cuota.estadoLabel
}

function TooltipHistorial({active, payload, label}) {
    if (!active || !payload?.length) return null
    const p = payload[0].payload
    return (
        <div className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-md">
            <p className="mb-1 font-semibold text-slate-700">{formatMonthLabel(label)}</p>
            <p style={{color: COLOR[p.estado]}} className="font-medium">{p.estado}</p>
            {p.monto > 0 && <p className="text-slate-500">{formatMoney(p.monto)}</p>}
        </div>
    )
}

export default function HistorialPagosChart({cuotas}) {
    const mensuales = cuotas.filter((c) => c.esMensual)
    const porMes = new Map(mensuales.map((c) => [c.periodo?.slice(0, 7), c]))

    const datos = []
    const cursor = new Date()
    cursor.setDate(1)
    for (let i = 11; i >= 0; i--) {
        const fecha = new Date(cursor.getFullYear(), cursor.getMonth() - i, 1)
        const key = `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}`
        const cuota = porMes.get(key)
        const estado = estadoReal(cuota)
        datos.push({mes: key, estado, monto: cuota?.monto ?? 0})
    }

    const maxMonto = Math.max(...datos.map((d) => d.monto), 1)

    return (
        <div className="h-56 px-2 py-4">
            <ResponsiveContainer width="100%" height="100%">
                <BarChart data={datos} barCategoryGap="20%">
                    <CartesianGrid vertical={false} stroke={GRID} />
                    <XAxis
                        dataKey="mes"
                        tickFormatter={(m) => formatMonthLabel(m).slice(0, 3)}
                        tick={{fill: INK_MUTED, fontSize: 11}}
                        axisLine={{stroke: GRID}}
                        tickLine={false}
                    />
                    <YAxis hide domain={[0, maxMonto * 1.1]} />
                    <Tooltip content={<TooltipHistorial />} cursor={{fill: '#f1f5f9'}} />
                    <Bar dataKey="monto" radius={[4, 4, 0, 0]} maxBarSize={28} isAnimationActive={false} minPointSize={3}>
                        {datos.map((d) => (
                            <Cell key={d.mes} fill={COLOR[d.estado] ?? '#898781'} />
                        ))}
                    </Bar>
                </BarChart>
            </ResponsiveContainer>
            <div className="mt-1 flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-slate-500">
                {Object.entries({Aprobado: 'Aprobado', Pendiente: 'Pendiente', Vencida: 'Vencida', 'Pendiente Aprobacion': 'En revisión'}).map(([k, label]) => (
                    <span key={k} className="flex items-center gap-1.5">
                        <span className="h-2.5 w-2.5 rounded-full" style={{backgroundColor: COLOR[k]}} />
                        {label}
                    </span>
                ))}
            </div>
        </div>
    )
}
