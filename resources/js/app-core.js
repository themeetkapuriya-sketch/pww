// Global Indian Currency Formatter (Lakhs & Crores format e.g. 10,00,00,000.00)
window.formatIndianCurrency = function(amount, decimals = 2) {
    if (amount === null || amount === undefined || amount === '') {
        return '';
    }
    const cleanStr = String(amount).replace(/,/g, '').trim();
    if (isNaN(cleanStr) || cleanStr === '') return amount;
    
    const numFloat = parseFloat(cleanStr);
    const isNegative = numFloat < 0;
    const absFloat = Math.abs(numFloat);

    const parts = absFloat.toFixed(decimals).split('.');
    let num = parts[0];
    const dec = parts[1] !== undefined && decimals > 0 ? '.' + parts[1] : '';

    if (num.length <= 3) {
        return (isNegative ? '-' : '') + num + dec;
    }

    const lastThree = num.substring(num.length - 3);
    let rest = num.substring(0, num.length - 3);

    let restFormatted = '';
    while (rest.length > 2) {
        restFormatted = ',' + rest.substring(rest.length - 2) + restFormatted;
        rest = rest.substring(0, rest.length - 2);
    }
    restFormatted = rest + restFormatted;

    return (isNegative ? '-' : '') + restFormatted + ',' + lastThree + dec;
};
window.formatINR = window.formatIndianCurrency;

// 1. Universal Button Loading Spinner State Manager (Prevents double submits & adds smooth spinner)
window.setButtonLoading = function(btn, isLoading, loadingText = 'Processing...') {
    if (!btn) return;
    const $btn = $(btn);
    if (!$btn.length) return;

    if (isLoading) {
        if ($btn.data('is-loading')) return;
        $btn.data('is-loading', true);
        $btn.data('original-html', $btn.html());
        $btn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed pointer-events-none');
        
        $btn.html(`
            <span class="inline-flex items-center justify-center gap-2">
                <svg class="animate-spin h-3.5 w-3.5 text-current shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>${loadingText}</span>
            </span>
        `);
    } else {
        $btn.data('is-loading', false);
        const originalHtml = $btn.data('original-html');
        if (originalHtml !== undefined && originalHtml !== null) {
            $btn.html(originalHtml);
        }
        $btn.prop('disabled', false).removeClass('opacity-75 opacity-50 cursor-not-allowed pointer-events-none');
    }
};

let toastTimer = null;

// 2. Universal Top-Right Corner Toast Notification Engine (Red for Delete/Error, Yellow for Update, Emerald/White for Success)
window.showToast = function(type, message) {
    const $toast = $('#globalToast');
    const $icon = $('#toastIcon');
    const $msgText = $('#toastMessage');
    if (!$toast.length || !$icon.length || !$msgText.length) return;
    
    if (toastTimer) {
        clearTimeout(toastTimer);
    }
    
    $msgText.text(message);

    const typeLower = (type || '').toLowerCase();
    const msgLower = (message || '').toLowerCase();
    
    const isDeleteOrError = (
        typeLower === 'error' || 
        typeLower === 'danger' || 
        typeLower === 'delete' ||
        msgLower.includes('delete') || 
        msgLower.includes('deleted') ||
        msgLower.includes('remove') ||
        msgLower.includes('removed') ||
        msgLower.includes('failed') ||
        msgLower.includes('error')
    );

    const isWarning = (
        typeLower === 'warning' ||
        typeLower === 'warn' ||
        msgLower.includes('warning') ||
        msgLower.includes('required') ||
        msgLower.includes('at least') ||
        msgLower.includes('please select') ||
        msgLower.includes('invalid')
    );

    const isInfo = (typeLower === 'info');

    const $innerCard = $toast.find('> div');

    if (isDeleteOrError) {
        $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-rose-100 text-rose-600 shrink-0')
             .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>');
        $innerCard.attr('class', 'bg-[#F43F5E] text-white shadow-2xl rounded-2xl p-4 flex items-center space-x-3 max-w-sm border border-rose-500 cursor-pointer transition-all duration-300');
        $msgText.attr('class', 'text-sm font-bold text-white');
    } else if (isWarning) {
        $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-amber-100 text-amber-700 shrink-0')
             .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>');
        $innerCard.attr('class', 'bg-amber-500 text-white shadow-2xl rounded-2xl p-4 flex items-center space-x-3 max-w-sm border border-amber-600 cursor-pointer transition-all duration-300');
        $msgText.attr('class', 'text-sm font-bold text-white');
    } else if (isInfo) {
        $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 shrink-0')
             .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>');
        $innerCard.attr('class', 'bg-blue-600 text-white shadow-2xl rounded-2xl p-4 flex items-center space-x-3 max-w-sm border border-blue-500 cursor-pointer transition-all duration-300');
        $msgText.attr('class', 'text-sm font-bold text-white');
    } else {
        $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600 shrink-0')
             .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>');
        $innerCard.attr('class', 'bg-white border border-slate-200 shadow-2xl rounded-2xl p-4 flex items-center space-x-3 max-w-sm cursor-pointer transition-all duration-300');
        $msgText.attr('class', 'text-sm font-semibold text-slate-800');
    }
    
    $toast.removeClass('translate-y-[-100px] opacity-0 pointer-events-none').addClass('translate-y-0 opacity-100 pointer-events-auto');
    
    toastTimer = setTimeout(() => {
        $toast.removeClass('translate-y-0 opacity-100 pointer-events-auto').addClass('translate-y-[-100px] opacity-0 pointer-events-none');
    }, 3000);
};

$(document).on('click', '#globalToast', function() {
    if (toastTimer) clearTimeout(toastTimer);
    $(this).removeClass('translate-y-0 opacity-100 pointer-events-auto').addClass('translate-y-[-100px] opacity-0 pointer-events-none');
});

// 3. DataTable-Aware In-Place Row & DOM Helper (Smooth animations + DataTables sync)
window.ERPTableHelper = {
    reindexTable: function($table) {
        if (!$table || !$table.length) return;
        const dt = (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($table[0]))
            ? $table.DataTable()
            : null;

        if (dt) {
            dt.draw(false);
        } else {
            $table.find('tbody tr:visible').each(function(index) {
                const $firstTd = $(this).find('td').first();
                if (!$firstTd.attr('colspan') && !$firstTd.closest('tr').hasClass('empty-row')) {
                    $firstTd.text(index + 1);
                }
            });
        }
    },

    removeRow: function(rowSelectorOrEl, onComplete) {
        const $row = $(rowSelectorOrEl);
        if (!$row.length) return;

        const $table = $row.closest('table');
        const dt = (typeof $.fn.DataTable !== 'undefined' && $table.length && $.fn.DataTable.isDataTable($table[0]))
            ? $table.DataTable()
            : null;

        $row.css({
            'transition': 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
            'opacity': '0',
            'transform': 'scale(0.97) translateY(-4px)'
        });

        setTimeout(function() {
            if (dt) {
                dt.row($row[0]).remove().draw(false);
            } else {
                $row.remove();
            }
            if ($table && $table.length) {
                window.ERPTableHelper.reindexTable($table);
            }
            if (typeof onComplete === 'function') onComplete();
        }, 300);
    },

    highlightRow: function(rowSelectorOrEl) {
        const $row = $(rowSelectorOrEl);
        if (!$row.length) return;
        $row.addClass('row-updated-glow');
        setTimeout(() => $row.removeClass('row-updated-glow'), 1200);
    },

    prependRow: function(tableSelectorOrEl, newRowHtml, onComplete) {
        const $table = $(tableSelectorOrEl);
        if (!$table.length) return;
        
        const $newRow = $(newRowHtml).addClass('row-fade-in');
        const dt = (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable($table[0]))
            ? $table.DataTable()
            : null;

        if (dt) {
            const rowNode = dt.row.add($newRow[0]).draw(false).node();
            $(rowNode).addClass('row-fade-in');
        } else {
            const $tbody = $table.find('tbody');
            if ($tbody.length) {
                $tbody.prepend($newRow);
            } else {
                $table.prepend($newRow);
            }
            window.ERPTableHelper.reindexTable($table);
        }
        if (typeof onComplete === 'function') onComplete($newRow);
    }
};

// 4. Dynamic Stat Counter Engine (Increments/Decrements/Formats counters in-place with micro-interaction)
window.updateStatCounter = function(selectorOrEl, deltaOrValue, isCurrency = false) {
    const $el = $(selectorOrEl);
    if (!$el.length) return;

    let newVal;
    if (typeof deltaOrValue === 'number' && (deltaOrValue === 1 || deltaOrValue === -1 || deltaOrValue > 0 || deltaOrValue < 0)) {
        let currentText = $el.text().replace(/[₹,\s]/g, '').trim();
        let currentNum = parseFloat(currentText) || 0;
        newVal = Math.max(0, currentNum + deltaOrValue);
    } else {
        newVal = deltaOrValue;
    }

    if (isCurrency && typeof window.formatIndianCurrency === 'function') {
        $el.text((isCurrency === true ? '₹' : '') + window.formatIndianCurrency(newVal));
    } else {
        $el.text(typeof newVal === 'number' ? Math.round(newVal) : newVal);
    }

    // Bounce pulse micro-interaction
    $el.addClass('scale-110 text-blue-600 transition-transform duration-200');
    setTimeout(() => {
        $el.removeClass('scale-110 text-blue-600');
    }, 250);
};

// 5. Smooth Tab Switching Engine with State Persistence
window.switchTabWithFade = function(tabName, tabBtnSelector, paneSelectorPrefix = 'empTab-') {
    $(`[id^="${paneSelectorPrefix}"]`).each(function() {
        if (!$(this).hasClass('hidden')) {
            $(this).css('opacity', '0');
            const $self = $(this);
            setTimeout(() => {
                $self.addClass('hidden');
            }, 120);
        }
    });

    setTimeout(() => {
        const $target = $(`#${paneSelectorPrefix}${tabName}`);
        if ($target.length) {
            $target.removeClass('hidden').css('opacity', '0');
            setTimeout(() => {
                $target.css({
                    'opacity': '1',
                    'transition': 'opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1)'
                });
            }, 20);
        }
    }, 130);

    if (tabBtnSelector) {
        $('.emp-tab-btn, .tab-btn').removeClass('active-emp-tab active-tab-btn bg-blue-50 text-blue-700');
        $(tabBtnSelector).addClass('active-emp-tab active-tab-btn bg-blue-50 text-blue-700 font-bold');
    }

    try {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    } catch(e) {}
};

document.addEventListener('DOMContentLoaded', () => {
    // jQuery Check
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded! Fallback logic would be needed.');
        return;
    }

    jQuery(document).ready(function($) {
        // Cache selectors
        const $sidebar = $('#sidebar');
        const $mainContent = $('#main-content');
        const $sidebarToggle = $('#sidebarToggle');
        const $sidebarPinToggle = $('#sidebarPinToggle');
        const $sidebarPinDot = $('#sidebarPinDot');
        const $csrfMeta = $('meta[name="csrf-token"]');

        const getCsrfToken = () => $csrfMeta.attr('content') || '';
        const isDesktop = () => window.innerWidth >= 768;

        // Apply sidebar visual states
        function applySidebarState(pinned) {
            const $sb = $('#sidebar');
            const $mc = $('#main-content');
            const $toggle = $('#sidebarToggle');
            const $dot = $('#sidebarPinDot');
            
            if (!$sb.length || !$mc.length) return;

            if (isDesktop()) {
                if ($toggle.length) $toggle.addClass('hidden');
                if (pinned) {
                    $sb.removeClass('sidebar-collapsed -translate-x-full md:-translate-x-full').addClass('translate-x-0 md:translate-x-0');
                    $mc.removeClass('pl-0 pl-[72px] md:pl-[72px]').addClass('pl-64 md:pl-64');
                    if ($dot.length) {
                        $dot.removeClass('bg-transparent scale-0').addClass('bg-blue-500 scale-100');
                    }
                } else {
                    $sb.removeClass('-translate-x-full md:-translate-x-full').addClass('sidebar-collapsed translate-x-0 md:translate-x-0');
                    $mc.removeClass('pl-0 pl-64 md:pl-64').addClass('pl-[72px] md:pl-[72px]');
                    if ($dot.length) {
                        $dot.removeClass('bg-blue-500 scale-100').addClass('bg-transparent scale-0');
                    }
                }
            } else {
                if ($toggle.length) $toggle.removeClass('hidden');
                $sb.removeClass('translate-x-0 md:translate-x-0 sidebar-collapsed').addClass('-translate-x-full');
                $mc.removeClass('pl-64 pl-[72px] md:pl-64 md:pl-[72px]').addClass('pl-0');
            }
        }

        // Toggle logic init
        const $sb = $('#sidebar');
        if ($sb.length) {
            const isPinned = localStorage.getItem('sidebar_pinned') !== 'false';
            applySidebarState(isPinned);

            $(document).on('click', '#sidebarPinToggle', function(e) {
                e.stopPropagation();
                const currentPinned = localStorage.getItem('sidebar_pinned') !== 'false';
                localStorage.setItem('sidebar_pinned', !currentPinned ? 'true' : 'false');
                applySidebarState(!currentPinned);
            });

            $(document).on('click', '#sidebarToggle', function(e) {
                e.stopPropagation();
                const $sbEl = $('#sidebar');
                $sbEl.removeClass('-translate-x-full').addClass('translate-x-0');
                $(this).addClass('hidden');
            });

            const closeMobileSidebar = () => {
                if (!isDesktop()) {
                    const $sbEl = $('#sidebar');
                    const $toggle = $('#sidebarToggle');
                    $sbEl.addClass('-translate-x-full').removeClass('translate-x-0');
                    if ($toggle.length) $toggle.removeClass('hidden');
                }
            };

            $(document).on('click', '#sidebar .nav-link-item, #sidebar .sidebar-logout-btn, #sidebar .sidebar-footer a', closeMobileSidebar);

            $(document).on('click', function(e) {
                const $sbEl = $('#sidebar');
                const $toggle = $('#sidebarToggle');
                if (!isDesktop() && $sbEl.length && !$sbEl.is(e.target) && $sbEl.has(e.target).length === 0 &&
                    !$toggle.is(e.target) && $toggle.has(e.target).length === 0) {
                    closeMobileSidebar();
                }
            });

            $(window).on('resize', () => {
                applySidebarState(localStorage.getItem('sidebar_pinned') !== 'false');
            });
        }

        // In-memory page response cache for instant 0ms page switching
        window.pageCache = window.pageCache || new Map();
        const pageCache = window.pageCache;

        // Render dynamic Low Stock Alert counter and dropdown in header
        window.renderStockAlerts = function(data) {
            const totalCount = data?.total_count || 0;
            const rawMaterials = data?.raw_materials || [];
            const products = data?.products || [];

            const wrapper = document.getElementById('lowStockDropdownWrapper');
            if (!wrapper) return;

            let btn = document.getElementById('lowStockAlertBtn');
            if (btn) {
                btn.title = totalCount > 0 ? `${totalCount} items are low on stock!` : 'Stock levels optimal';
                if (totalCount > 0) {
                    btn.className = 'relative p-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/80 text-amber-600 hover:text-amber-700 ring-2 ring-amber-300/60 transition cursor-pointer border border-slate-200/80';
                    let badge = btn.querySelector('span');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'absolute -top-1.5 -right-1.5 px-1.5 py-0.2 bg-rose-500 text-white font-black text-[10px] rounded-full shadow-xs border border-white animate-pulse';
                        btn.appendChild(badge);
                    }
                    badge.textContent = totalCount;
                    badge.classList.remove('hidden');
                } else {
                    btn.className = 'relative p-2 rounded-xl bg-slate-100/80 hover:bg-slate-200/80 text-slate-600 transition cursor-pointer border border-slate-200/80';
                    let badge = btn.querySelector('span');
                    if (badge) badge.classList.add('hidden');
                }
            }

            const card = document.getElementById('headerLowStockCard');
            if (!card) return;

            let html = `
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 rounded-lg ${totalCount > 0 ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'}">
                            ${totalCount > 0 ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>' : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'}
                        </span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Inventory Stock Alerts</h4>
                            <p class="text-[10px] text-slate-500 font-medium">
                                ${totalCount > 0 ? totalCount + ' item(s) below safe minimum' : 'All warehouse stock is healthy'}
                            </p>
                        </div>
                    </div>
                </div>
            `;

            if (totalCount > 0) {
                html += '<div class="max-h-64 overflow-y-auto space-y-2 pr-1 custom-scrollbar text-xs">';
                if (rawMaterials.length > 0) {
                    html += '<div class="text-[10px] font-black text-slate-400 uppercase tracking-wider px-1">Raw Materials</div>';
                    rawMaterials.forEach(rm => {
                        const stockNum = parseFloat(rm.current_stock || 0).toFixed(2);
                        const minNum = parseFloat(rm.safety_threshold > 0 ? rm.safety_threshold : 50).toFixed(0);
                        html += `
                            <div class="p-2.5 bg-amber-50/60 rounded-xl border border-amber-200/80 flex items-center justify-between">
                                <div class="min-w-0 pr-2">
                                    <p class="font-bold text-slate-800 truncate">${rm.material_name}</p>
                                    <p class="text-[10px] text-amber-700 font-semibold font-mono">
                                        Live: <span class="font-black text-rose-600">${stockNum} ${rm.unit || 'kg'}</span> (Min: ${minNum})
                                    </p>
                                </div>
                                <a href="/purchases?open=1&material_id=${rm.id}" class="shrink-0 px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-[10px] rounded-lg shadow-2xs transition">
                                    + Order
                                </a>
                            </div>
                        `;
                    });
                }
                if (products.length > 0) {
                    html += '<div class="text-[10px] font-black text-slate-400 uppercase tracking-wider px-1 pt-1">Finished Goods</div>';
                    products.forEach(pr => {
                        html += `
                            <div class="p-2.5 bg-blue-50/60 rounded-xl border border-blue-200/80 flex items-center justify-between">
                                <div class="min-w-0 pr-2">
                                    <p class="font-bold text-slate-800 truncate">${pr.product_name}</p>
                                    <p class="text-[10px] text-blue-700 font-semibold font-mono">
                                        Live: <span class="font-black text-rose-600">${pr.current_stock} ${pr.uom || 'pcs'}</span> (Min: 10)
                                    </p>
                                </div>
                                <a href="/production" class="shrink-0 px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] rounded-lg shadow-2xs transition">
                                    + Produce
                                </a>
                            </div>
                        `;
                    });
                }
                html += '</div>';
            } else {
                html += `
                    <div class="py-6 text-center text-xs text-slate-500 space-y-1">
                        <div class="text-2xl">🎉</div>
                        <p class="font-bold text-slate-700">No Low Stock Items!</p>
                        <p class="text-[11px] text-slate-400">All materials and finished products are well above minimum thresholds.</p>
                    </div>
                `;
            }

            html += `
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold">
                    <a href="/rawmaterial" class="text-blue-600 hover:underline">Raw Materials ➡️</a>
                    <a href="/product" class="text-blue-600 hover:underline">Products ➡️</a>
                </div>
            `;

            card.innerHTML = html;
        };

        // Instantly refresh both header widgets (Active Orders Pipeline & Low Stock Alerts)
        window.refreshHeaderWidgets = async function() {
            try {
                const res = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-PWW-SPA': '1' },
                    cache: 'no-store'
                });
                if (!res.ok) return;
                const htmlText = await res.text();
                const doc = new DOMParser().parseFromString(htmlText, 'text/html');
                const newSpaHeader = doc.getElementById('spa-header-content');
                const currentHeader = document.querySelector('header');
                if (newSpaHeader && currentHeader) {
                    const newHeaderEl = newSpaHeader.querySelector('header') || newSpaHeader;
                    currentHeader.innerHTML = newHeaderEl.tagName === 'HEADER' ? newHeaderEl.innerHTML : newSpaHeader.innerHTML;
                }
            } catch(e) {}
        };
        window.refreshStockAlerts = window.refreshHeaderWidgets;

        // Clear page cache when any data form is submitted or mutated and re-sync stock alerts
        window.clearPageCache = function() {
            pageCache.clear();
            if (typeof window.refreshHeaderWidgets === 'function') {
                window.refreshHeaderWidgets();
            }
        };

        // Remove any legacy progress bar if present
        if ($('#spaTopProgressBar').length) {
            $('#spaTopProgressBar').remove();
        }

        // Hover, mousedown, and touchstart prefetch for sidebar and internal navigation links
        $(document).on('mouseenter mousedown touchstart', 'a.nav-link-item, #sidebar a, .page-nav-link', function() {
            const href = $(this).attr('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.includes('/logout') || pageCache.has(href)) return;
            try {
                const url = new URL(href, window.location.href);
                if (url.origin === window.location.origin && !url.pathname.includes('/print') && !url.pathname.includes('/download') && !url.pathname.includes('/export')) {
                    fetch(url.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-PWW-SPA': '1' } })
                        .then(r => r.ok ? r.text() : null)
                        .then(html => { if (html) pageCache.set(url.href, html); })
                        .catch(() => {});
                }
            } catch (e) {}
        });

        // Background cache pre-warmer for instant 0ms sidebar switching
        function prewarmSidebarCache() {
            $('#sidebar a.nav-link-item').each(function() {
                const href = $(this).attr('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.includes('/logout') || pageCache.has(href)) return;
                try {
                    const url = new URL(href, window.location.href);
                    if (url.origin === window.location.origin && 
                        !url.pathname.includes('/print') && 
                        !url.pathname.includes('/download') && 
                        !url.pathname.includes('/export')) {
                        fetch(url.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-PWW-SPA': '1' } })
                            .then(r => r.ok ? r.text() : null)
                            .then(html => { if (html) pageCache.set(url.href, html); })
                            .catch(() => {});
                    }
                } catch (e) {}
            });
        }
        setTimeout(prewarmSidebarCache, 800);

        // Expose loadPage to window so it can be called elsewhere
        window.loadPage = async function(url, skipCache = false, preserveScroll = null) {
            if (skipCache) {
                pageCache.clear();
            }
            if (!$mainContent.length) {
                window.location.href = url;
                return;
            }

            const currentScrollY = window.scrollY;
            let shouldPreserveScroll = false;
            try {
                const targetUrlObj = new URL(url, window.location.origin);
                if (preserveScroll === true || targetUrlObj.pathname === window.location.pathname) {
                    shouldPreserveScroll = true;
                }
            } catch(e) {}
            
            try {
                let htmlText;
                if (!skipCache && pageCache.has(url)) {
                    htmlText = pageCache.get(url);
                } else {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-PWW-SPA': '1' } });
                    
                    // Handle session timeout / unauthorized / CSRF expiration
                    if (response.status === 401 || response.status === 419) {
                        window.location.href = '/login';
                        return;
                    }

                    if (response.redirected && response.url && response.url.includes('/login')) {
                        window.location.href = '/login';
                        return;
                    }

                    if (!response.ok) {
                        window.location.href = url;
                        return;
                    }

                    htmlText = await response.text();

                    // If response is the login page (unauthenticated redirect), perform clean full-page redirect to login
                    if (htmlText.includes('<title>PWW ERP - Secure Authentication</title>') || (htmlText.includes('name="email"') && htmlText.includes('name="password"') && !htmlText.includes('id="page-content"'))) {
                        window.location.href = '/login';
                        return;
                    }
                }

                const doc = new DOMParser().parseFromString(htmlText, 'text/html');
                const newContent = doc.getElementById('page-content');
                
                if (!newContent) {
                    window.location.replace('/login');
                    return;
                }
                
                if (typeof window.closeCategoryModal === 'function') {
                    window.closeCategoryModal();
                }
                if (typeof window.closeClearAuditLogsModal === 'function') {
                    window.closeClearAuditLogsModal();
                }
                if (typeof window.closeAdvanceModal === 'function') {
                    window.closeAdvanceModal();
                }
                if (typeof window.closeDisburseModal === 'function') {
                    window.closeDisburseModal();
                }
                if (typeof window.closePaymentModal === 'function') {
                    window.closePaymentModal();
                }
                document.querySelectorAll('#categoryModal, #clearAuditLogsModal, #giveAdvanceModal, #disburseSalaryModal, #paymentSalaryModal').forEach(m => m.classList.add('hidden'));

                if (newContent) {
                    $('#page-content').html(newContent.innerHTML);
                    if (doc.title) {
                        document.title = doc.title;
                        let pageTitleStr = doc.title.split('-')[0].trim();
                        if (pageTitleStr.startsWith('PWW ERP')) pageTitleStr = pageTitleStr.replace('PWW ERP', '').trim();
                        if (!pageTitleStr) pageTitleStr = 'Dashboard';
                        const txt = document.createElement('textarea');
                        txt.innerHTML = pageTitleStr;
                        pageTitleStr = txt.value;
                        $('#headerPageTitle').text(pageTitleStr);
                    }
                } else {
                    $('#page-content').html(htmlText);
                    const titleMatch = htmlText.match(/<title>(.*?)<\/title>/i);
                    if (titleMatch && titleMatch[1]) {
                        document.title = titleMatch[1];
                        let pageTitleStr = titleMatch[1].split('-')[0].trim();
                        if (pageTitleStr.startsWith('PWW ERP')) pageTitleStr = pageTitleStr.replace('PWW ERP', '').trim();
                        if (!pageTitleStr) pageTitleStr = 'Dashboard';
                        const txt = document.createElement('textarea');
                        txt.innerHTML = pageTitleStr;
                        pageTitleStr = txt.value;
                        $('#headerPageTitle').text(pageTitleStr);
                    }
                }

                // Dynamically sync Top Navigation Header (for instantaneous Active Orders & Stock Alert updates)
                const newSpaHeader = doc.getElementById('spa-header-content');
                const currentHeader = document.querySelector('header');
                if (newSpaHeader && currentHeader) {
                    const newHeaderEl = newSpaHeader.querySelector('header') || newSpaHeader;
                    currentHeader.innerHTML = newHeaderEl.tagName === 'HEADER' ? newHeaderEl.innerHTML : newSpaHeader.innerHTML;
                } else if (currentHeader) {
                    const newHeaderEl = doc.querySelector('header');
                    if (newHeaderEl) {
                        currentHeader.innerHTML = newHeaderEl.innerHTML;
                    }
                }
                
                if (window.location.href !== url) {
                    history.pushState(null, '', url);
                }

                if (shouldPreserveScroll && preserveScroll !== false) {
                    window.scrollTo({ top: currentScrollY, behavior: 'instant' });
                } else {
                    window.scrollTo({ top: 0, behavior: 'instant' });
                }
                initializeForms();
                updateActiveSidebarLinks(url);
                applySidebarState(localStorage.getItem('sidebar_pinned') !== 'false');
                executeScripts($('#page-content')[0]);
                window.initErpDataTables();
                if (window.ERPComboboxManager) {
                    document.querySelectorAll('.combobox-wrapper').forEach(w => window.ERPComboboxManager.syncDisplay(w));
                }
            } catch (err) {
                console.error('SPA load error:', err);
                window.location.href = url;
            }
        };

        function executeScripts(container) {
            $(container).find('script').each(function() {
                const newScript = document.createElement('script');
                Array.from(this.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(this.innerHTML));
                this.parentNode.replaceChild(newScript, this);
            });
        }

        function updateActiveSidebarLinks(urlStr) {
            const $sb = $('#sidebar');
            if (!$sb.length) return;
            try {
                const url = new URL(urlStr || window.location.href, window.location.origin);
                const path = url.pathname;
                const tab = url.searchParams.get('tab');
                
                $sb.find('a.nav-link-item').each(function() {
                    const $link = $(this);
                    const rawHref = $link.attr('href');
                    if (!rawHref) return;
                    
                    const linkUrl = new URL(rawHref, window.location.origin);
                    const linkPath = linkUrl.pathname;
                    const linkTab = linkUrl.searchParams.get('tab');
                    
                    let isActive = false;
                    if (linkPath === path) {
                        if (linkTab) {
                            isActive = (linkTab === tab) || (!tab && linkTab === 'materials' && path === '/inventory');
                        } else {
                            isActive = !tab;
                        }
                    }
                    
                    if (isActive) {
                        $link.addClass('active-nav').removeClass('text-slate-600 hover:bg-slate-50 hover:text-slate-900');
                    } else {
                        $link.removeClass('active-nav').addClass('text-slate-600 hover:bg-slate-50 hover:text-slate-900');
                    }
                });
            } catch (err) {
                console.error('Active sidebar link update error:', err);
            }
        }

        // Intercept Link Clicks for SPA Navigation
        $(document).on('click', 'a', async function(e) {
            const href = $(this).attr('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || $(this).attr('target') || $(this).hasClass('no-ajax') || $(this).attr('download') !== undefined) {
                return;
            }
            
            try {
                const url = new URL(href, window.location.href);
                const isDownloadUrl = url.pathname.includes('/logout') || 
                                     url.pathname.includes('/print') || 
                                     url.pathname.includes('/download') || 
                                     url.pathname.includes('/export') || 
                                     url.pathname.includes('/backup/full') || 
                                     url.pathname.includes('/backup/filtered');

                if (url.origin === window.location.origin && !isDownloadUrl) {
                    e.preventDefault();
                    const isPreserve = $(this).data('preserve-scroll') === true || $(this).hasClass('preserve-scroll') || url.pathname === window.location.pathname;
                    await window.loadPage(url.href, false, isPreserve);
                }
            } catch (err) {
                console.error('Link intercept error:', err);
            }
        });

        window.addEventListener('popstate', () => {
            window.loadPage(window.location.href);
        });

        // Forms submission interceptor
        $(document).on('submit', 'form', async function(e) {
            const $form = $(this);
            $form.attr('novalidate', true); // prevent default HTML5 validation tooltip

            if ($form.attr('method') && $form.attr('method').toLowerCase() === 'get') {
                const url = new URL($form.attr('action') || window.location.href);
                new FormData($form[0]).forEach((value, key) => {
                    if (value) url.searchParams.set(key, value);
                    else url.searchParams.delete(key);
                });
                if (url.origin === window.location.origin) {
                    e.preventDefault();
                    const isPreserve = $form.data('preserve-scroll') === true || url.pathname === window.location.pathname;
                    await window.loadPage(url.href, false, isPreserve);
                }
                return;
            }

            if (!$form.hasClass('ajax-form')) return;
            e.preventDefault();
            
            // Clean commas from amount input fields before validation & serialization
            $form.find('input.amount-input, input[name*="amount"], input[name*="price"], input[name*="salary"], input[name*="rate"]').each(function() {
                const name = ($(this).attr('name') || '').toLowerCase();
                if (name.includes('quantity') || name.includes('quantities') || name.includes('threshold')) return;
                const val = $(this).val();
                if (val) {
                    $(this).val(val.replace(/,/g, ''));
                }
            });

            // Disable browser default tooltip popups dynamically
            $form.attr('novalidate', 'novalidate');
            
            // Clear previous validation states
            $form.find('input, select, textarea').each(function() {
                clearInlineError($(this));
            });
            $form.find('.combobox-wrapper').each(function() {
                clearInlineError($(this).find('.combobox-search-input'));
            });
            $form.find('.form-alert').addClass('hidden').html('');

            // Client-side validation check
            let hasErrors = false;

            // 1. Validate standard inputs, selects, and textareas (skipping combobox search/hidden inputs)
            $form.find('input, select, textarea').each(function() {
                const $input = $(this);
                if ($input.is(':disabled') || $input.is(':hidden') || $input.closest('.hidden').length > 0 || $input.is(':submit') || $input.is(':button') || $input.attr('type') === 'hidden') return;
                if ($input.hasClass('combobox-search-input')) return; // Handled in combobox validation below

                const val = $input.val();
                let errorMsg = '';

                if ($input.prop('required') && (!val || val.toString().trim() === '')) {
                    let labelText = '';
                    const $label = $input.prev('label').length ? $input.prev('label') : $input.closest('div').find('label').first();
                    if ($label.length) {
                        labelText = $label.clone().children().remove().end().text().trim();
                    }
                    if (!labelText) {
                        labelText = $input.attr('placeholder') || 'this field';
                    }
                    labelText = labelText.replace(/[:*₹(]/g, '').trim().toLowerCase();
                    errorMsg = `Please enter the ${labelText || 'required information'}.`;
                    
                    if ($input.attr('name') === 'email') {
                        errorMsg = 'Please enter a valid email address.';
                    } else if ($input.attr('name') === 'password') {
                        errorMsg = 'Please enter your password.';
                    }
                }

                if (!errorMsg && $input.attr('type') === 'number' && val !== '' && val !== null && val !== undefined) {
                    const numVal = parseFloat(val);
                    const minVal = parseFloat($input.attr('min'));
                    if (!isNaN(minVal) && numVal < minVal) {
                        if ($input.attr('name') && $input.attr('name').includes('quantities')) {
                            errorMsg = 'Quantity must be greater than 0.';
                        } else {
                            errorMsg = `Value must be at least ${minVal}.`;
                        }
                    }
                }

                if (!errorMsg && $input.attr('type') === 'email' && val) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(val)) {
                        errorMsg = 'Please enter a valid email address.';
                    }
                }

                if (errorMsg) {
                    showInlineError($input, errorMsg);
                    hasErrors = true;
                }
            });

            // 2. Validate Comboboxes (Select Client & Plant, Products, Categories etc.)
            $form.find('.combobox-wrapper').each(function() {
                const $wrap = $(this);
                if ($wrap.is(':hidden') || $wrap.closest('.hidden').length > 0) return;

                const $hiddenInp = $wrap.find('.combobox-hidden-input');
                const $searchInp = $wrap.find('.combobox-search-input');
                
                const isRequired = $wrap.attr('data-required') === 'true' || 
                                   $wrap.data('required') === true || 
                                   $hiddenInp.prop('required') || 
                                   $hiddenInp.attr('required') !== undefined || 
                                   $searchInp.attr('data-required') === 'true' ||
                                   $wrap.find('label span.text-rose-500').length > 0 ||
                                   $wrap.closest('#directOrderClientContainer').length > 0;

                const hiddenVal = $hiddenInp.val();
                if (isRequired && (!hiddenVal || hiddenVal.toString().trim() === '')) {
                    let labelText = '';
                    const $label = $wrap.find('label').length ? $wrap.find('label').first() : $wrap.closest('div').find('label').first();
                    if ($label.length) {
                        labelText = $label.clone().children().remove().end().text().trim();
                    }
                    if (!labelText) {
                        labelText = $searchInp.attr('placeholder') || 'an option';
                    }
                    labelText = labelText.replace(/[:*₹(]/g, '').trim();
                    const errorMsg = `Please select ${labelText || 'an option'}.`;

                    showInlineError($searchInp, errorMsg);
                    hasErrors = true;
                }
            });

            if (hasErrors) {
                // Focus first errored field
                $form.find('.border-red-500').first().focus();
                return;
            }

            const $submitBtn = $form.find('button[type="submit"]');
            window.setButtonLoading($submitBtn, true, 'Saving...');
            
            // Abort previous pending request on same form to prevent double-submit race conditions
            if ($form.data('activeXhr')) {
                try { $form.data('activeXhr').abort(); } catch(e) {}
            }

            let ajaxData;
            let processData = true;
            let contentType = 'application/x-www-form-urlencoded; charset=UTF-8';

            if ($form.attr('enctype') === 'multipart/form-data') {
                ajaxData = new FormData($form[0]);
                processData = false;
                contentType = false;
            } else {
                ajaxData = $form.serialize();
            }

            const currentXhr = $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method') || 'POST',
                data: ajaxData,
                processData: processData,
                contentType: contentType,
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                success: async function(response) {
                    $form.removeData('activeXhr');
                    window.setButtonLoading($submitBtn, false);
                    window.showToast('success', response.message || 'Operation completed successfully!');

                    // Trigger custom DOM event for modular component handlers
                    $form.trigger('ajax:success', [response]);

                    // 1. Auto-close specified or parent modal immediately (<10ms)
                    const customModalTarget = $form.attr('data-close-modal');
                    if (customModalTarget) {
                        $(customModalTarget).addClass('hidden');
                    }
                    const $parentModal = $form.closest('.fixed.inset-0, [id$="Modal"]');
                    if ($parentModal.length) {
                        $parentModal.addClass('hidden');
                    }

                    // 2. Auto-close all inline form containers across all modules immediately
                    const $inlineFormCard = $form.closest('#salesOrderFormCard, #orderFormContainer, #productionFormCard, #purchaseFormContainer, #invoiceFormContainer, #invoiceFormCard, #section-manual-builder, #rawMaterialFormCard, #productFormCard, #employeeFormCard, #expenseFormCard, #clientFormCard, #plantFormCard, .inline-form-card, [id$="FormCard"], [id$="FormContainer"]');
                    if ($inlineFormCard.length) {
                        $inlineFormCard.addClass('hidden');
                    }
                    $('#section-manual-builder').addClass('hidden');

                    // 3. Reset toggle buttons to default closed label
                    $('button[onclick*="toggleInlineForm"], button[onclick*="orderFormContainer"], button[onclick*="salesOrderFormCard"], button[onclick*="productionFormCard"], button[onclick*="purchaseFormContainer"], button[onclick*="invoiceFormContainer"], #toggleInvoiceFormBtn').each(function() {
                        const origText = $(this).data('orig-text');
                        if (origText) $(this).html(origText);
                    });

                    if ($form.hasClass('no-reload') || $form.hasClass('no-refresh') || $form.hasClass('inplace-form') || $form.data('inplace') === true || ($form.attr('action') && $form.attr('action').includes('/settings/security'))) {
                        if (!$form.hasClass('no-reset')) {
                            $form[0].reset();
                        }
                        return;
                    }

                    if (!$form.hasClass('no-reset')) {
                        $form[0].reset();
                    }

                    // 4. Target Specific Location Background Data Table Refresh
                    let targetUrl = window.location.href;
                    if ($form.attr('id') === 'customInvoiceForm') {
                        const invMode = $form.find('#invoiceModeInput').val();
                        if (invMode === 'raw_material') {
                            targetUrl = '/invoices?mode=raw_material';
                        } else {
                            targetUrl = '/invoices';
                        }
                    } else if (response && response.redirect) {
                        targetUrl = response.redirect;
                    } else if ($form.attr('data-redirect')) {
                        targetUrl = $form.attr('data-redirect');
                    }
                    window.clearPageCache();
                    await window.loadPage(targetUrl, true);
                },
                error: function(xhr) {
                    $form.removeData('activeXhr');
                    window.setButtonLoading($submitBtn, false);

                    // Handle 419 CSRF Token Expiration
                    if (xhr.status === 419) {
                        window.showToast('warning', 'Session expired. Refreshing page...');
                        setTimeout(() => window.location.reload(), 1200);
                        return;
                    }

                    if (xhr.status === 422) {
                        const responseData = xhr.responseJSON || {};
                        const errors = responseData.errors || {};
                        const globalMessage = responseData.message || '';
                        
                        let hasShownError = false;
                        if (typeof errors === 'object' && errors !== null) {
                            Object.keys(errors).forEach(key => {
                                const val = errors[key];
                                const errorMsg = Array.isArray(val) ? val.join(', ') : val;
                                
                                let selector = `[name="${key}"]`;
                                
                                if (key.includes('.')) {
                                    const parts = key.split('.');
                                    const baseName = parts[0];
                                    const index = parseInt(parts[1]);
                                    const $inputs = $form.find(`[name="${baseName}[]"], [name^="${baseName}["]`);
                                    if ($inputs.length && $inputs.eq(index).length) {
                                        showInlineError($inputs.eq(index), errorMsg);
                                        hasShownError = true;
                                        return;
                                    }
                                }
                                
                                let $el = $form.find(selector);
                                if (!$el.length) {
                                    $el = $form.find(`[name="${key}[]"]`);
                                }
                                if (!$el.length) {
                                    const parsedKey = key.replace(/\.(\w+)/g, '[$1]');
                                    $el = $form.find(`[name="${parsedKey}"]`);
                                }
                                
                                const $comboWrap = $el.closest('.combobox-wrapper').length ? $el.closest('.combobox-wrapper') : $el.siblings('.combobox-wrapper');
                                if ($el.length && ($el.is(':visible') || $comboWrap.length > 0)) {
                                    const $targetEl = $comboWrap.length ? $comboWrap.find('.combobox-search-input').first() : $el.first();
                                    showInlineError($targetEl, errorMsg);
                                    hasShownError = true;
                                } else {
                                    showGlobalFormError($form, errorMsg);
                                    hasShownError = true;
                                }
                            });
                        }
                        if (!hasShownError && globalMessage) {
                            showGlobalFormError($form, globalMessage);
                        }
                    } else {
                        const message = xhr.responseJSON && xhr.responseJSON.message 
                            ? xhr.responseJSON.message 
                            : 'A system network failure occurred. Please try again.';
                        showGlobalFormError($form, message);
                    }
                }
            });
        });

        function showGlobalFormError($form, message) {
            let $alertContainer = $form.find('.form-alert');
            if (!$alertContainer.length) {
                $alertContainer = $('<div class="form-alert hidden mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold shadow-2xs"></div>');
                $form.prepend($alertContainer);
            }
            $alertContainer.removeClass('hidden').html(`
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>${message}</span>
                </div>
            `);
            if (window.showToast) window.showToast('error', message);
        }

        function showInlineError($element, message) {
            clearInlineError($element);

            // Add red border classes to the element
            $element.addClass('border-red-500 focus:border-red-500 focus:ring-red-500 focus:ring-opacity-50 text-red-900 bg-red-50/10');

            // Detect if element is part of an inline/flex table row (e.g. manual builder dynamic rows)
            const isInline = $element.closest('.flex-row, .flex, table, tr, td, .billing-row, .item-row').length > 0 && 
                             ($element.attr('name') && ($element.attr('name').includes('[]') || $element.attr('name').includes('[')));

            const isCombobox = $element.hasClass('combobox-search-input') || $element.closest('.combobox-wrapper').length > 0;

            if (isInline) {
                // For inline fields, keep the layout 100% untouched. Store and use native tooltip title.
                const originalTitle = $element.attr('title') || '';
                $element.data('original-title', originalTitle);
                $element.attr('title', message);
            } else if (isCombobox) {
                // For comboboxes, place the error label cleanly without breaking the relative container
                const $errorLabel = $('<span class="val-error text-red-600 text-xs font-bold mb-1 block"></span>').text(message);
                const $wrap = $element.closest('.combobox-wrapper');
                if ($wrap.length) {
                    $wrap.find('.val-error').remove();
                    const $label = $wrap.find('label');
                    if ($label.length) {
                        $label.after($errorLabel);
                    } else {
                        $wrap.prepend($errorLabel);
                    }
                } else {
                    $element.before($errorLabel);
                }
            } else {
                // Create error text label above input
                const $errorLabel = $('<span class="val-error text-red-600 text-xs font-bold mb-1 block"></span>').text(message);

                const isTextInput = $element.is('textarea') || 
                                    ($element.is('input') && ['text', 'number', 'email', 'password', 'date', 'tel', 'url'].includes($element.attr('type') || 'text'));

                if (isTextInput) {
                    // Wrap in a relative container to position the icon inside on the right
                    const $wrapper = $('<div class="val-error-wrapper relative w-full"></div>');
                    $element.wrap($wrapper);

                    const $icon = $(`
                        <div class="val-error-icon absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-red-500">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    `);
                    $element.after($icon);

                    // Label goes before the wrapper
                    $element.parent().before($errorLabel);
                } else {
                    // For select, checkboxes, file inputs etc.
                    $element.before($errorLabel);
                }
            }

            // Clear error on user interaction (input, change, or combobox selection)
            $element.one('input change', function() {
                clearInlineError($element);
            });
            const $wrap = $element.closest('.combobox-wrapper');
            if ($wrap.length) {
                $wrap.find('.combobox-hidden-input').one('change', function() {
                    clearInlineError($element);
                });
            }
        }

        function clearInlineError($element) {
            const originalTitle = $element.data('original-title');
            if (originalTitle !== undefined) {
                if (originalTitle) $element.attr('title', originalTitle);
                else $element.removeAttr('title');
                $element.removeData('original-title');
            }

            const $comboWrap = $element.closest('.combobox-wrapper');
            if ($comboWrap.length) {
                $comboWrap.find('.val-error').remove();
            }

            const $wrapper = $element.closest('.val-error-wrapper');
            if ($wrapper.length) {
                $wrapper.prev('.val-error').remove();
                $wrapper.find('.val-error-icon').remove();
                $element.unwrap();
            } else {
                $element.prev('.val-error').remove();
                $element.next('.val-error').remove();
            }
            $element.removeClass('border-red-500 focus:border-red-500 focus:ring-red-500 focus:ring-opacity-50 text-red-900 bg-red-50/10');
        }

        window.clearInlineError = clearInlineError;
        window.clearFormErrors = function($form) {
            const $f = $($form);
            if ($f.length) {
                $f.find('.form-alert').remove();
                $f.find('input, select, textarea').each(function() {
                    clearInlineError($(this));
                });
            }
        };

        // GST State Code Map for all 36 States & UTs
        const GST_STATE_CODES = {
            'Gujarat': '24', 'Maharashtra': '27', 'Madhya Pradesh': '23', 'Rajasthan': '08',
            'Delhi': '07', 'Haryana': '06', 'Punjab': '03', 'Uttar Pradesh': '09',
            'West Bengal': '19', 'Karnataka': '29', 'Telangana': '36', 'Tamil Nadu': '33',
            'Kerala': '32', 'Goa': '30', 'Andhra Pradesh': '37', 'Bihar': '10',
            'Odisha': '21', 'Himachal Pradesh': '02', 'Uttarakhand': '05', 'Jammu & Kashmir': '01',
            'Ladakh': '38', 'Chandigarh': '04', 'Jharkhand': '20', 'Chhattisgarh': '22',
            'Assam': '18', 'Sikkim': '11', 'Arunachal Pradesh': '12', 'Nagaland': '13',
            'Manipur': '14', 'Mizoram': '15', 'Tripura': '16', 'Meghalaya': '17',
            'Puducherry': '34', 'Daman & Diu': '25', 'Dadra & Nagar Haveli': '26',
            'Andaman & Nicobar Islands': '35'
        };

        window.initSearchableSelects = function() {
            if (typeof TomSelect === 'undefined') return;
            $('select.searchable-select').each(function() {
                if (this.tomselect) return;
                try {
                    new TomSelect(this, {
                        create: false,
                        sortField: { field: "text", direction: "asc" },
                        placeholder: "Type to search state or GST code...",
                        allowEmptyOption: true
                    });
                } catch(e) {
                    console.error('TomSelect init error:', e);
                }
            });
        };

        window.initAmountInputs = function() {
            $('input.amount-input, input[name*="amount"], input[name*="price"], input[name*="salary"], input[name*="rate"]').each(function() {
                const $input = $(this);
                const name = ($input.attr('name') || '').toLowerCase();
                
                if (name.includes('quantity') || name.includes('quantities') || name.includes('threshold') || name.includes('count') || name === 'remember') {
                    return;
                }

                if ($input.attr('type') === 'number') {
                    $input.attr('type', 'text').attr('inputmode', 'decimal').addClass('amount-input');
                }

                const rawVal = $input.val();
                if (rawVal && !rawVal.includes(',')) {
                    $input.val(window.formatIndianCurrency(rawVal));
                }
            });
        };

        function initializeForms() {
            window.initSearchableSelects();
            window.initAmountInputs();
        }
        window.initializeForms = initializeForms;

        $(document).on('focus', 'input.amount-input, input[name*="amount"], input[name*="price"], input[name*="salary"], input[name*="rate"]', function() {
            const name = ($(this).attr('name') || '').toLowerCase();
            if (name.includes('quantity') || name.includes('quantities') || name.includes('threshold')) return;
            const val = $(this).val();
            if (val) {
                $(this).val(val.replace(/,/g, ''));
            }
        });

        $(document).on('blur', 'input.amount-input, input[name*="amount"], input[name*="price"], input[name*="salary"], input[name*="rate"]', function() {
            const name = ($(this).attr('name') || '').toLowerCase();
            if (name.includes('quantity') || name.includes('quantities') || name.includes('threshold')) return;
            const val = $(this).val();
            if (val && !isNaN(parseFloat(val.replace(/,/g, '')))) {
                $(this).val(window.formatIndianCurrency(val));
            }
        });

        // Real-time 15-digit GSTIN UPPERCASE & State Code Validation
        $(document).on('input', 'input[name="gst_number"], input[name="plant_gst_number"]', function() {
            let val = $(this).val().toUpperCase();
            $(this).val(val);
            
            const $form = $(this).closest('form');
            const $stateSelect = $form.find('select[name="state"]');
            const stateVal = $stateSelect.val();
            const expectedCode = GST_STATE_CODES[stateVal];

            if (val.length > 0) {
                if (val.length !== 15) {
                    showInlineError($(this), `GSTIN must be EXACTLY 15 characters (currently ${val.length})`);
                } else if (expectedCode && !val.startsWith(expectedCode)) {
                    showInlineError($(this), `GSTIN for ${stateVal} must start with State Code ${expectedCode} (e.g. ${expectedCode}AAAAB1111A1Z5)`);
                } else {
                    clearInlineError($(this));
                }
            } else {
                clearInlineError($(this));
            }
        });

        // Helper to format Indian Vehicle Registration Numbers (e.g. GJO5MA4104 -> GJ-05-MA-4104)
        function formatVehicleNumber(val) {
            if (!val) return '';
            let clean = val.trim().toUpperCase().replace(/[\s-]/g, '');
            
            // Standard RTO Format: State(2) + District(1-2) + Series(1-3) + Number(1-4)
            const rtoMatch = clean.match(/^([A-Z]{2})([0-9O]{1,2})([A-Z]{1,3})([0-9O]{1,4})$/);
            if (rtoMatch) {
                let state = rtoMatch[1];
                let dist = rtoMatch[2].replace(/O/g, '0');
                if (dist.length === 1) dist = '0' + dist;
                let series = rtoMatch[3];
                let num = rtoMatch[4].replace(/O/g, '0');
                return `${state}-${dist}-${series}-${num}`;
            }
            
            // BH Series: Year(2) + BH + Number(1-4) + Series(1-2)
            const bhMatch = clean.match(/^([0-9O]{2})BH([0-9O]{1,4})([A-Z]{1,2})$/);
            if (bhMatch) {
                let yr = bhMatch[1].replace(/O/g, '0');
                let num = bhMatch[2].replace(/O/g, '0');
                let series = bhMatch[3];
                return `${yr}-BH-${num}-${series}`;
            }

            return val.toUpperCase();
        }

        // Vehicle Registration Number Format Validator (RTO & BH Series)
        const VEHICLE_NUMBER_REGEX = /^[A-Z]{2}[ -]?[0-9O]{1,2}[ -]?[A-Z]{0,3}[ -]?[0-9O]{1,4}$|^[0-9O]{2}[ -]?BH[ -]?[0-9O]{1,4}[ -]?[A-Z]{1,2}$/i;

        // Auto uppercase on input, clear error if valid or empty
        $(document).on('input', 'input[name="vehicle_number"]', function() {
            let val = $(this).val().toUpperCase();
            $(this).val(val);
            if (val.length === 0 || VEHICLE_NUMBER_REGEX.test(val)) {
                clearInlineError($(this));
            }
        });

        // Validate & Auto-format vehicle number on blur (after user finishes typing)
        $(document).on('blur', 'input[name="vehicle_number"]', function() {
            let val = $(this).val().trim();
            if (val.length > 0) {
                let formatted = formatVehicleNumber(val);
                $(this).val(formatted);
                if (!VEHICLE_NUMBER_REGEX.test(formatted)) {
                    showInlineError($(this), 'Enter valid vehicle number');
                } else {
                    clearInlineError($(this));
                }
            } else {
                clearInlineError($(this));
            }
        });

        $(document).on('change', 'select[name="state"]', function() {
            const stateVal = $(this).val();
            const expectedCode = GST_STATE_CODES[stateVal];
            const $form = $(this).closest('form');
            const $gstInput = $form.find('input[name="gst_number"], input[name="plant_gst_number"]').first();
            
            if ($gstInput.length && expectedCode) {
                $gstInput.attr('placeholder', `e.g. ${expectedCode}AAAAB1111A1Z5`);
                if ($gstInput.val()) {
                    $gstInput.trigger('input');
                }
            }
        });

        // Global Form Auto-Clear Listener: Clear field errors & hide alerts automatically on valid user input
        $(document).on('input change', 'form input, form select, form textarea', function() {
            const $input = $(this);
            const val = $input.val();
            const $form = $input.closest('form');

            // Clear inline error if field value is valid or non-empty
            if ($input.attr('type') === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!val || emailRegex.test(val)) {
                    clearInlineError($input);
                }
            } else if ($input.attr('name') === 'gst_number' || $input.attr('name') === 'plant_gst_number') {
                const stateVal = $form.find('select[name="state"]').val();
                const expectedCode = GST_STATE_CODES[stateVal];
                if (!val || (val.length === 15 && (!expectedCode || val.startsWith(expectedCode)))) {
                    clearInlineError($input);
                }
            } else if ($input.attr('name') === 'vehicle_number') {
                if (!val || VEHICLE_NUMBER_REGEX.test(val)) {
                    clearInlineError($input);
                }
            } else {
                if (val && val.toString().trim() !== '') {
                    clearInlineError($input);
                }
            }

            // Automatically hide form-level alert banners as user interacts with inputs
            if ($form.length) {
                $form.find('.form-alert, #alertContainer, #emailFormAlert').addClass('hidden').html('');
            }
        });

        // Expose resetFormAndErrors globally
        window.resetFormAndErrors = function(formSelector) {
            const $form = $(formSelector);
            if (!$form.length) return;
            
            if ($form[0] && typeof $form[0].reset === 'function') {
                $form[0].reset();
            }
            
            $form.find('input, select, textarea').each(function() {
                clearInlineError($(this));
            });
            $form.find('.form-alert').addClass('hidden').html('');
        };

        // Expose deleteInvoiceRecord globally with in-place DataTable removal
        window.deleteInvoiceRecord = function(id, invoiceNumber) {
            window.confirmDelete(
                'Delete Invoice?',
                `Are you sure you want to permanently delete Invoice '${invoiceNumber}'? This action cannot be undone!`,
                function() {
                    const token = getCsrfToken();
                    $.ajax({
                        url: `/invoices/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        success: async function(response) {
                            window.showToast('success', response.message || 'Invoice deleted successfully!');
                            const $row = $(`#row-inv-${id}, #invoice-row-${id}`);
                            if ($row.length && window.ERPTableHelper) {
                                window.ERPTableHelper.removeRow($row);
                                window.updateStatCounter('#statTotalInvoices, #totalInvoicesCounter', -1);
                            } else if (typeof window.loadPage === 'function') {
                                await window.loadPage(window.location.href);
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete invoice.';
                            window.showToast('error', msg);
                        }
                    });
                }
            );
        };

        // Expose payInvoiceRecord globally with in-place status update
        window.payInvoiceRecord = function(id, invoiceNumber) {
            if (typeof window.openInvoicePaymentModal === 'function') {
                window.openInvoicePaymentModal(id, invoiceNumber, 0);
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Mark Invoice as Paid?',
                    text: `Are you sure you want to mark Invoice '${invoiceNumber}' as fully paid?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Mark as Paid',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/invoices/${id}/pay`,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            success: async function(response) {
                                window.showToast('success', response.message || 'Invoice marked as paid!');
                                const $row = $(`#row-inv-${id}, #invoice-row-${id}`);
                                if ($row.length) {
                                    $row.find('.inv-status-badge, .status-badge').html(`
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Paid
                                        </span>
                                    `);
                                    $row.find('.inv-balance-cell').text('₹0.00');
                                    $row.find('.pay-btn, button:contains("Pay")').remove();
                                    if (window.ERPTableHelper) window.ERPTableHelper.highlightRow($row);
                                    window.updateStatCounter('#statUnpaidInvoices', -1);
                                    window.updateStatCounter('#statPaidInvoices', +1);
                                } else if (typeof window.loadPage === 'function') {
                                    await window.loadPage(window.location.href);
                                }
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update payment status.';
                                window.showToast('error', msg);
                            }
                        });
                    }
                });
            } else if (confirm(`Are you sure you want to mark Invoice '${invoiceNumber}' as fully paid?`)) {
                $.ajax({
                    url: `/invoices/${id}/pay`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    success: async function(response) {
                        window.showToast('success', response.message || 'Invoice marked as paid!');
                        const $row = $(`#row-inv-${id}, #invoice-row-${id}`);
                        if ($row.length) {
                            $row.find('.inv-status-badge, .status-badge').html(`
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Paid
                                </span>
                            `);
                            $row.find('.inv-balance-cell').text('₹0.00');
                            $row.find('.pay-btn, button:contains("Pay")').remove();
                            if (window.ERPTableHelper) window.ERPTableHelper.highlightRow($row);
                        } else if (typeof window.loadPage === 'function') {
                            await window.loadPage(window.location.href);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update payment status.';
                        alert(msg);
                    }
                });
            }
        };

        // Global Handler: Auto-reset forms & clear validation errors whenever "Cancel" or "Close" buttons are clicked
        $(document).on('click', 'button, a', function() {
            const txt = $(this).text().trim().toLowerCase();
            if (txt.includes('cancel') || txt.includes('close') || txt === '×') {
                const $container = $(this).closest('form, [id*="Card"], [id*="Modal"], [id*="form"], [id*="Form"]');
                if ($container.length) {
                    const $form = $container.is('form') ? $container : $container.find('form');
                    $form.each(function() {
                        window.resetFormAndErrors(this);
                    });
                }
            }
        });

        // Expose SweetAlert2 confirmDelete globally
        window.confirmDelete = function(title, text, confirmCallback) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title || 'Are you sure?',
                    text: text || "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-slate-200',
                        confirmButton: 'px-4 py-2 text-xs font-bold rounded-xl text-white bg-rose-500 hover:bg-rose-600 border-none shadow-xs mr-2',
                        cancelButton: 'px-4 py-2 text-xs font-bold rounded-xl text-white bg-slate-500 hover:bg-slate-600 border-none shadow-xs'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (typeof confirmCallback === 'function') {
                            confirmCallback();
                        }
                    }
                });
            } else {
                if (confirm((title ? title + "\n" : "") + (text || "Are you sure you want to delete this?"))) {
                    if (typeof confirmCallback === 'function') {
                        confirmCallback();
                    }
                }
            }
        };

        // DataTables Global Initializer
        window.initErpDataTables = function() {
            if (typeof $.fn.DataTable === 'undefined') return;

            // Silence popup warning alerts for custom colspan inline-edit rows
            $.fn.dataTable.ext.errMode = 'none';

            $('table.erp-datatable').each(function() {
                const $table = $(this);

                // Clean out server-side empty placeholder row if present before DataTables init
                $table.find('tbody tr.empty-row, tbody tr.empty-placeholder-row').remove();
                if ($table.find('tbody tr').length === 1 && $table.find('tbody tr td[colspan]').length > 0) {
                    $table.find('tbody tr').remove();
                }

                if ($.fn.DataTable.isDataTable(this)) {
                    $table.DataTable().destroy();
                }

                const isResponsive = !$table.hasClass('no-responsive') && $table.attr('data-responsive') !== 'false';

                $table.DataTable({
                    dom: '<"flex flex-wrap items-center justify-between gap-4 mb-4"lf><"erp-datatable-scroll-container overflow-x-auto w-full my-2"t><"flex flex-wrap items-center justify-between gap-4 mt-4"ip>',
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
                    paging: true,
                    pagingType: "simple_numbers",
                    searching: true,
                    ordering: true,
                    info: true,
                    responsive: isResponsive,
                    autoWidth: false,
                    order: [], // Preserve original server row order
                    columnDefs: [
                        {
                            targets: 0,
                            orderable: false
                        }
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search records...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        zeroRecords: `<div class="py-8 text-center text-slate-500 font-medium">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-sm font-bold text-slate-600">No Records Found</p>
                            <p class="text-xs text-slate-400 mt-1">There are no records matching your request or search filter criteria.</p>
                        </div>`,
                        emptyTable: `<div class="py-8 text-center text-slate-500 font-medium">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-sm font-bold text-slate-600">No Records Found</p>
                            <p class="text-xs text-slate-400 mt-1">There are no entries recorded in this ledger yet.</p>
                        </div>`,
                        infoFiltered: "(filtered from _MAX_ total records)",
                        paginate: {
                            previous: "‹",
                            next: "›"
                        }
                    },
                    drawCallback: function(settings) {
                        const api = this.api();
                        const $paginate = $(api.table().container()).find('.dataTables_paginate');
                        $paginate.addClass('inline-flex items-center gap-2');

                        // Automatically keep the # serial column sequentially numbered without gaps
                        const $firstTh = $(api.table().header()).find('th').first();
                        const headerText = $firstTh.text().trim();
                        if (headerText === '#' || headerText.toLowerCase() === 'sr.' || headerText.toLowerCase() === 'sr no' || headerText.toLowerCase() === 'no.') {
                            const pageInfo = api.page.info();
                            const start = pageInfo ? pageInfo.start : 0;
                            api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function(cell, i) {
                                $(cell).text(start + i + 1);
                            });
                        }
                    }
                });
            });
        };

        function initializeForms() {
            $('form').attr('novalidate', 'novalidate');
            if (window.initSearchableSelects) window.initSearchableSelects();
            if (window.initAmountInputs) window.initAmountInputs();
        }

        // Global Modal Teleport Engine: Moves top-level modal containers to document.body
        function initGlobalModalTeleport() {
            $('div.fixed[id*="Modal"], div.fixed[id*="modal"], div[id$="Modal"], div[id$="modal"]').each(function() {
                if (this.parentNode !== document.body) {
                    document.body.appendChild(this);
                }
            });
        }

        // Universal Modal Close Event Listeners (Backdrop click, Close buttons, ESC key)
        $(document).on('click', '[data-modal-close], .modal-close-btn', function(e) {
            e.preventDefault();
            const modalId = $(this).attr('data-modal-close');
            if (modalId) {
                $(`#${modalId}`).addClass('hidden');
            }
            $(this).closest('.fixed.inset-0, [id$="Modal"], [id$="modal"]').addClass('hidden');
        });

        $(document).on('click', '.fixed.inset-0', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden');
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                $('.fixed.inset-0:not(.hidden), [id$="Modal"]:not(.hidden), [id$="modal"]:not(.hidden)').addClass('hidden');
            }
        });

        // Global Keyboard Hotkeys Listener
        $(document).on('keydown', function(e) {
            // Ignore hotkeys when typing in form fields
            if ($(e.target).is('input, select, textarea, [contenteditable="true"]')) {
                return;
            }

            if (e.altKey) {
                const key = e.key.toLowerCase();

                // Route mapping: key -> path
                const routes = {
                    'o': '/overview',
                    'i': '/invoices',
                    'p': '/purchases',
                    'e': '/expenses',
                    'r': '/reports',
                    'b': '/backup',
                    'a': '/activity-logs'
                };

                // Handle navigation shortcuts
                if (routes[key]) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    if (window.loadPage) window.loadPage(routes[key]);
                    return false;
                }

                // Handle special action shortcuts (Help Toast)
                if (key === 'h') {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    if (window.showToast) {
                        window.showToast('info', '⌨️ Hotkeys: Alt+O (Overview), Alt+I (Invoices), Alt+P (Purchases), Alt+E (Expenses), Alt+R (Reports), Alt+B (Backup), Alt+A (Audits)');
                    }
                    return false;
                }
            }
        });

        // Theme Engine (Light / Dark Mode Toggle)
        window.initThemeEngine = function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        };

        window.toggleTheme = function() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            if (window.showToast) {
                window.showToast('info', isDark ? '🌙 Dark Mode Activated' : '☀️ Light Mode Activated');
            }
        };

        window.initThemeEngine();

        // Run initial forms setup, DataTables, and modal teleport on DOM ready
        initializeForms();
        window.initErpDataTables();
        initGlobalModalTeleport();
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (xhr && (xhr.status === 401 || xhr.status === 419)) {
                window.location.href = '/login';
                return;
            }
            initGlobalModalTeleport();
            if (settings && settings.type && settings.type.toUpperCase() !== 'GET') {
                if (typeof window.clearPageCache === 'function') {
                    window.clearPageCache();
                }
            }
        });

        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            if (xhr && (xhr.status === 401 || xhr.status === 419)) {
                window.location.href = '/login';
            }
        });
    });
});
