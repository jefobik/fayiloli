import './bootstrap';
import { Chart, registerables } from 'chart.js';

// ─── CHART.JS ──────────────────────────────────────────────────────────────
// Registered globally so @script blocks in Livewire views can access `Chart`
// without an extra import. Chart.js is intentionally separate from Livewire's
// own Alpine bundle.
Chart.register(...registerables);
window.Chart = Chart;

// ─── ALPINE NOTE ───────────────────────────────────────────────────────────
// Livewire v4 bundles and boots Alpine automatically.
// DO NOT import Alpine or call Alpine.start() here — it will cause a
// "Alpine has already been initialised" error and break all reactive UI.
// Custom Alpine plugins / stores should be registered via:
//
//   document.addEventListener('alpine:init', () => {
//       Alpine.store('myStore', { ... });
//   });

// ─── TOAST NOTIFICATION SYSTEM ─────────────────────────────────────────────
// Alpine-independent vanilla JS helper. Accessible globally on all pages.
window.edmsToast = function (message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: 'fa-check-circle',
        error:   'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info:    'fa-info-circle',
    };

    const colorMap = {
        success: '#10b981',
        error:   '#ef4444',
        warning: '#f59e0b',
        info:    '#7c3aed',
    };

    const toast = document.createElement('div');
    toast.className = `toast-notif ${type}`;
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"
           style="font-size:1.1rem;flex-shrink:0;margin-top:1px;color:${colorMap[type] || colorMap.info}"></i>
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:0.82rem;margin-bottom:0.1rem;text-transform:capitalize">${type}</div>
            <div style="font-size:0.82rem;opacity:0.85;line-height:1.4">${message}</div>
        </div>
        <button onclick="this.closest('.toast-notif').remove()"
                style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:0;font-size:1rem;line-height:1;margin-top:1px">
            <i class="fas fa-times"></i>
        </button>`;

    container.appendChild(toast);
    if (duration > 0) setTimeout(() => toast.remove(), duration);
};
