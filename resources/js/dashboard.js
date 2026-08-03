import Chart from 'chart.js/auto';

const percentFormatter = new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
});
const integerFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function numberOrNull(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const number = Number(value);

    return Number.isFinite(number) ? number : null;
}

function numbers(values = []) {
    return values.map(numberOrNull);
}

function hasNumber(values = []) {
    return values.some((value) => numberOrNull(value) !== null);
}

function hasPositiveNumber(values = []) {
    return values.some((value) => (numberOrNull(value) ?? 0) > 0);
}

function markChartEmpty(canvas) {
    canvas.classList.add('hidden');
    const emptyState = canvas.parentElement?.querySelector('[data-chart-empty]');

    emptyState?.classList.remove('hidden');
    emptyState?.classList.add('flex');
}

function resizeHorizontalFrame(canvas, itemCount) {
    const frame = canvas.closest('[data-chart-frame]');

    if (frame instanceof HTMLElement) {
        frame.style.height = `${Math.min(1200, Math.max(320, itemCount * 34 + 72))}px`;
    }
}

function tooltipLabel(unit = 'percent') {
    return (context) => {
        const value = numberOrNull(context.raw);
        const prefix = context.dataset.label ? `${context.dataset.label}: ` : '';

        if (value === null) {
            return `${prefix}Data Tidak Tersedia`;
        }

        if (unit === 'integer') {
            return `${prefix}${integerFormatter.format(value)}`;
        }

        return `${prefix}${percentFormatter.format(value)}%`;
    };
}

function baseOptions(unit = 'percent') {
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: reducedMotion ? false : { duration: 450 },
        interaction: { mode: 'nearest', intersect: false },
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true, padding: 18 },
            },
            tooltip: {
                callbacks: { label: tooltipLabel(unit) },
            },
        },
    };
}

function percentScale() {
    return {
        beginAtZero: true,
        ticks: {
            callback: (value) => `${percentFormatter.format(value)}%`,
        },
        grid: { color: 'rgba(148, 163, 184, 0.18)' },
    };
}

function integerScale() {
    return {
        beginAtZero: true,
        ticks: {
            precision: 0,
            callback: (value) => integerFormatter.format(value),
        },
        grid: { color: 'rgba(148, 163, 184, 0.18)' },
    };
}

function renderChart(id, hasData, configuration, horizontalItemCount = null) {
    const canvas = document.getElementById(id);

    if (!(canvas instanceof HTMLCanvasElement)) {
        return;
    }

    if (!hasData) {
        markChartEmpty(canvas);
        return;
    }

    if (horizontalItemCount !== null) {
        resizeHorizontalFrame(canvas, horizontalItemCount);
    }

    new Chart(canvas, configuration);
}

function renderPriority(data) {
    const values = numbers(data?.values);

    renderChart('priorityChart', hasPositiveNumber(values), {
        type: 'doughnut',
        data: {
            labels: data?.labels ?? [],
            datasets: [{
                data: values,
                backgroundColor: data?.colors ?? ['#16a34a', '#d97706', '#dc2626'],
                borderColor: '#ffffff',
                borderWidth: 3,
            }],
        },
        options: {
            ...baseOptions('integer'),
            cutout: '64%',
        },
    });
}

function renderDominant(data) {
    const values = numbers(data?.values);
    const labels = data?.labels ?? [];
    const palette = ['#1f4b75', '#0f766e', '#7c3aed', '#d97706', '#dc2626', '#64748b'];

    renderChart('dominantChart', hasPositiveNumber(values), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Jumlah kecamatan',
                data: values,
                backgroundColor: labels.map((_, index) => palette[index % palette.length]),
                borderRadius: 7,
            }],
        },
        options: {
            ...baseOptions('integer'),
            indexAxis: 'y',
            scales: { x: integerScale(), y: { grid: { display: false } } },
        },
    }, labels.length);
}

function renderComparison(data) {
    const current = numbers(data?.current);
    const previous = numbers(data?.previous);

    renderChart('comparisonChart', hasNumber([...current, ...previous]), {
        type: 'bar',
        data: {
            labels: data?.labels ?? [],
            datasets: [
                {
                    label: `Tahun ${data?.previous_year ?? '-'}`,
                    data: previous,
                    backgroundColor: '#94a3b8',
                    borderRadius: 7,
                    skipNull: true,
                },
                {
                    label: `Tahun ${data?.year ?? '-'}`,
                    data: current,
                    backgroundColor: '#1f4b75',
                    borderRadius: 7,
                    skipNull: true,
                },
            ],
        },
        options: {
            ...baseOptions(),
            scales: { y: percentScale(), x: { grid: { display: false } } },
        },
    });
}

function renderRanking(data) {
    const rows = (data?.labels ?? []).map((label, index) => ({
        label,
        value: numberOrNull(data?.values?.[index]),
        color: data?.colors?.[index] ?? '#94a3b8',
    })).sort((left, right) => {
        if (left.value === null) return 1;
        if (right.value === null) return -1;
        return right.value - left.value;
    });

    renderChart('rankingChart', hasNumber(rows.map((row) => row.value)), {
        type: 'bar',
        data: {
            labels: rows.map((row) => row.label),
            datasets: [{
                label: 'KPI-01',
                data: rows.map((row) => row.value),
                backgroundColor: rows.map((row) => row.color),
                borderRadius: 6,
                skipNull: true,
            }],
        },
        options: {
            ...baseOptions(),
            indexAxis: 'y',
            scales: { x: percentScale(), y: { grid: { display: false } } },
        },
    }, rows.length);
}

function renderEnvironment(data) {
    const labels = data?.labels ?? [];
    const airMinum = numbers(data?.air_minum);
    const jamban = numbers(data?.jamban);

    renderChart('environmentChart', hasNumber([...airMinum, ...jamban]), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Air minum tidak layak', data: airMinum, backgroundColor: '#0f766e', borderRadius: 6, skipNull: true },
                { label: 'Jamban tidak layak', data: jamban, backgroundColor: '#d97706', borderRadius: 6, skipNull: true },
            ],
        },
        options: {
            ...baseOptions(),
            indexAxis: 'y',
            scales: { x: percentScale(), y: { grid: { display: false } } },
        },
    }, labels.length);
}

function renderFourT(data) {
    const values = numbers(data?.values);

    renderChart('fourTChart', hasPositiveNumber(values), {
        type: 'bar',
        data: {
            labels: data?.labels ?? [],
            datasets: [{
                label: 'Jumlah PUS',
                data: values,
                backgroundColor: ['#1f4b75', '#0f766e', '#d97706', '#7c3aed'],
                borderRadius: 7,
            }],
        },
        options: {
            ...baseOptions('integer'),
            scales: { y: integerScale(), x: { grid: { display: false } } },
        },
    });
}

function renderWelfare(data) {
    const labels = data?.labels ?? [];
    const palette = ['#0f766e', '#0891b2', '#1f4b75', '#7c3aed', '#d97706'];
    const datasets = (data?.datasets ?? []).map((dataset, index) => ({
        label: dataset.label,
        data: numbers(dataset.values),
        backgroundColor: palette[index % palette.length],
        borderRadius: 4,
    }));
    const allValues = datasets.flatMap((dataset) => dataset.data);

    renderChart('welfareChart', hasPositiveNumber(allValues), {
        type: 'bar',
        data: { labels, datasets },
        options: {
            ...baseOptions('integer'),
            indexAxis: 'y',
            scales: {
                x: { ...integerScale(), stacked: true },
                y: { stacked: true, grid: { display: false } },
            },
        },
    }, labels.length);
}

export function initializeDashboardCharts() {
    const root = document.querySelector('[data-dashboard-charts]');
    const payload = window.__MONEV_DASHBOARD__;

    if (!(root instanceof HTMLElement) || !payload) {
        return;
    }

    Chart.defaults.font.family = window.getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#475569';
    Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.18)';

    renderPriority(payload.priority);
    renderDominant(payload.dominant);
    renderComparison(payload.comparison);
    renderRanking(payload.ranking);
    renderEnvironment(payload.environment);
    renderFourT(payload.four_t);
    renderWelfare(payload.welfare);

    root.setAttribute('aria-busy', 'false');
    root.querySelector('[data-dashboard-loading]')?.classList.add('hidden');
    delete window.__MONEV_DASHBOARD__;
}
