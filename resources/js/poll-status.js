document.addEventListener('DOMContentLoaded', () => {
    const pendingRows = [...document.querySelectorAll('[data-status="pending"]')];

    pendingRows.forEach(row => {
        const interval = setInterval(async () => {
            try {
                const res = await fetch(row.dataset.pollUrl);
                if (!res.ok) {
                    clearInterval(interval);
                    return;
                }
                const data = await res.json();

                const badge = row.querySelector('.status-badge');
                if (badge) {
                    badge.textContent = data.statut_label;
                    badge.className = badge.className
                        .replace(/badge-(pending|processed|failed)/, '')
                        .trim() + ' ' + (data.statut === 'processed' ? 'badge-processed' : data.statut === 'failed' ? 'badge-failed' : 'badge-pending');
                }

                const count = row.querySelector('.depenses-count');
                if (count) {
                    count.textContent = data.nb_depenses;
                }

                if (data.statut !== 'pending') {
                    row.dataset.status = data.statut;
                    clearInterval(interval);
                }
            } catch {
                clearInterval(interval);
            }
        }, 3000);
    });
});
