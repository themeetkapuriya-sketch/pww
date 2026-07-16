document.addEventListener('DOMContentLoaded', () => {
    // Cache selectors
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarPinToggle = document.getElementById('sidebarPinToggle');
    const sidebarPinDot = document.getElementById('sidebarPinDot');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    const getCsrfToken = () => csrfMeta ? csrfMeta.getAttribute('content') : '';

    const isDesktop = () => window.innerWidth >= 768;

    // Apply sidebar visual states
    function applySidebarState(pinned) {
        if (!sidebar || !mainContent) return;
        if (isDesktop()) {
            if (pinned) {
                sidebar.classList.remove('sidebar-collapsed', '-translate-x-full');
                sidebar.classList.add('translate-x-0');
                mainContent.classList.add('pl-64');
                mainContent.classList.remove('pl-[72px]', 'pl-0');
                if (sidebarPinDot) {
                    sidebarPinDot.classList.replace('bg-transparent', 'bg-blue-500');
                    sidebarPinDot.classList.replace('scale-0', 'scale-100');
                }
            } else {
                sidebar.classList.add('sidebar-collapsed', 'translate-x-0');
                sidebar.classList.remove('-translate-x-full');
                mainContent.classList.add('pl-[72px]');
                mainContent.classList.remove('pl-64', 'pl-0');
                if (sidebarPinDot) {
                    sidebarPinDot.classList.replace('bg-blue-500', 'bg-transparent');
                    sidebarPinDot.classList.replace('scale-100', 'scale-0');
                }
            }
            if (sidebarToggle) sidebarToggle.classList.add('hidden');
        } else {
            sidebar.classList.add('sidebar-collapsed', '-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            mainContent.classList.add('pl-0');
            mainContent.classList.remove('pl-64', 'pl-[72px]');
            if (sidebarToggle) sidebarToggle.classList.remove('hidden');
        }
    }

    // Toggle logic init
    if (sidebar) {
        const isPinned = localStorage.getItem('sidebar_pinned') !== 'false';
        applySidebarState(isPinned);

        if (sidebarPinToggle) {
            sidebarPinToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const currentPinned = localStorage.getItem('sidebar_pinned') !== 'false';
                localStorage.setItem('sidebar_pinned', !currentPinned ? 'true' : 'false');
                applySidebarState(!currentPinned);
            });
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                sidebarToggle.classList.add('hidden');
            });
        }

        // Close mobile sidebar on navigation/outside clicks
        const closeMobileSidebar = () => {
            if (!isDesktop()) {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                if (sidebarToggle) sidebarToggle.classList.remove('hidden');
            }
        };

        sidebar.querySelectorAll('.nav-link-item, .sidebar-logout-btn, .sidebar-footer a')
            .forEach(link => link.addEventListener('click', closeMobileSidebar));

        document.addEventListener('click', (e) => {
            if (!isDesktop() && !sidebar.contains(e.target) && sidebarToggle && !sidebarToggle.contains(e.target)) {
                closeMobileSidebar();
            }
        });

        window.addEventListener('resize', () => {
            applySidebarState(localStorage.getItem('sidebar_pinned') !== 'false');
        });
    }

    // SPA Page Loader
    async function loadPage(url) {
        if (!mainContent) {
            window.location.href = url;
            return;
        }
        mainContent.classList.add('opacity-50');
        
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                window.location.href = url;
                return;
            }
            
            const htmlText = await response.text();
            const doc = new DOMParser().parseFromString(htmlText, 'text/html');
            const newContent = doc.getElementById('page-content');
            
            if (newContent) {
                document.getElementById('page-content').innerHTML = newContent.innerHTML;
                document.title = doc.title;
                
                if (window.location.href !== url) {
                    history.pushState(null, '', url);
                }
                
                executeScripts(document.getElementById('page-content'));
                updateActiveSidebarLinks(url);
            } else {
                window.location.href = url;
            }
        } catch (err) {
            console.error('SPA load error:', err);
            window.location.href = url;
        } finally {
            mainContent.classList.remove('opacity-50');
        }
    }

    function executeScripts(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    function updateActiveSidebarLinks(urlStr) {
        if (!sidebar) return;
        const url = new URL(urlStr);
        const path = url.pathname;
        const tab = url.searchParams.get('tab');
        
        sidebar.querySelectorAll('a.nav-link-item').forEach(link => {
            const linkUrl = new URL(link.href);
            const linkPath = linkUrl.pathname;
            const linkTab = linkUrl.searchParams.get('tab');
            
            let isActive = false;
            if (linkPath === path) {
                isActive = linkTab ? (linkTab === tab) : (!tab || (path === '/inventory' && tab === 'materials') || (path === '/invoices' && tab === 'ledger'));
            }
            
            if (isActive) {
                link.classList.add('active-nav');
                link.classList.remove('text-slate-600', 'hover:bg-slate-50', 'hover:text-slate-900');
            } else {
                link.classList.remove('active-nav');
                link.classList.add('text-slate-600', 'hover:bg-slate-50', 'hover:text-slate-900');
            }
        });
    }

    // Intercept Link Clicks
    document.addEventListener('click', async (e) => {
        const link = e.target.closest('a');
        if (link && link.href && link.href.startsWith('http')) {
            const url = new URL(link.href);
            if (url.origin === window.location.origin && 
                !link.getAttribute('target') && 
                !link.classList.contains('no-ajax') &&
                !url.pathname.includes('/logout') &&
                !url.pathname.includes('/print') &&
                !url.pathname.includes('/export')) {
                
                e.preventDefault();
                await loadPage(link.href);
            }
        }
    });

    window.addEventListener('popstate', () => {
        loadPage(window.location.href);
    });

    // Forms submission interceptor
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form');
        if (!form) return;

        if (form.method.toLowerCase() === 'get') {
            const url = new URL(form.action || window.location.href);
            new FormData(form).forEach((value, key) => {
                if (value) url.searchParams.set(key, value);
                else url.searchParams.delete(key);
            });
            if (url.origin === window.location.origin) {
                e.preventDefault();
                await loadPage(url.href);
            }
            return;
        }

        if (!form.classList.contains('ajax-form')) return;
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const alertBox = form.querySelector('.form-alert') || createFormAlert(form);
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75');
            submitBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 mr-2 text-white inline" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Processing...</span>
            `;
        }
        
        alertBox.className = 'hidden';
        
        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((value, key) => {
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                if (!dataObj[cleanKey]) dataObj[cleanKey] = [];
                dataObj[cleanKey].push(value);
            } else if (key.startsWith('labor[')) {
                const staffId = key.match(/\[(.*?)\]/)[1];
                if (!dataObj['labor']) dataObj['labor'] = {};
                dataObj['labor'][staffId] = value;
            } else {
                dataObj[key] = value;
            }
        });
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(dataObj)
            });
            
            const responseData = await response.json();
            
            if (response.ok) {
                showToast('success', responseData.message || 'Operation completed successfully!');
                if (!form.classList.contains('no-reset')) form.reset();
                setTimeout(() => loadPage(window.location.href), 800);
            } else {
                const errors = responseData.errors 
                    ? (Array.isArray(responseData.errors) ? responseData.errors : Object.values(responseData.errors).flat())
                    : [responseData.message || 'Validation error. Please verify input.'];
                displayFormErrors(alertBox, errors);
                if (submitBtn) resetSubmitButton(submitBtn, originalBtnHtml);
            }
        } catch (err) {
            console.error(err);
            displayFormErrors(alertBox, ['A system network failure occurred. Please try again.']);
            if (submitBtn) resetSubmitButton(submitBtn, originalBtnHtml);
        }
    });

    function createFormAlert(form) {
        const div = document.createElement('div');
        div.className = 'form-alert hidden text-sm p-4 rounded-xl border mb-4';
        form.insertBefore(div, form.firstChild);
        return div;
    }
    
    function displayFormErrors(alertBox, errors) {
        alertBox.className = 'form-alert bg-rose-50 border-rose-200 text-rose-800 p-4 rounded-xl border text-xs mb-4';
        alertBox.innerHTML = `<strong>Submission Failed:</strong> <ul class="list-disc list-inside space-y-0.5">${errors.map(err => `<li>${err}</li>`).join('')}</ul>`;
    }
    
    function resetSubmitButton(btn, originalHtml) {
        btn.disabled = false;
        btn.classList.remove('opacity-75');
        btn.innerHTML = originalHtml;
    }

    // Expose showToast globally
    window.showToast = function(type, message) {
        const toast = document.getElementById('globalToast');
        const icon = document.getElementById('toastIcon');
        const msgText = document.getElementById('toastMessage');
        if (!toast || !icon || !msgText) return;
        
        msgText.innerText = message;
        
        if (type === 'success') {
            icon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600';
            icon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
        } else {
            icon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-rose-100 text-rose-600';
            icon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
        }
        
        toast.classList.remove('translate-y-[-100px]', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        
        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-[-100px]', 'opacity-0');
        }, 3000);
    };
});
