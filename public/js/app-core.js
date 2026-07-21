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
            if (!$sidebar.length || !$mainContent.length) return;
            if (isDesktop()) {
                if (pinned) {
                    $sidebar.removeClass('sidebar-collapsed -translate-x-full').addClass('translate-x-0');
                    $mainContent.addClass('pl-64').removeClass('pl-[72px] pl-0');
                    if ($sidebarPinDot.length) {
                        $sidebarPinDot.removeClass('bg-transparent scale-0').addClass('bg-blue-500 scale-100');
                    }
                } else {
                    $sidebar.addClass('sidebar-collapsed translate-x-0').removeClass('-translate-x-full');
                    $mainContent.addClass('pl-[72px]').removeClass('pl-64 pl-0');
                    if ($sidebarPinDot.length) {
                        $sidebarPinDot.removeClass('bg-blue-500 scale-100').addClass('bg-transparent scale-0');
                    }
                }
                $sidebarToggle.addClass('hidden');
            } else {
                $sidebar.addClass('sidebar-collapsed -translate-x-full').removeClass('translate-x-0');
                $mainContent.addClass('pl-0').removeClass('pl-64 pl-[72px]');
                $sidebarToggle.removeClass('hidden');
            }
        }

        // Toggle logic init
        if ($sidebar.length) {
            const isPinned = localStorage.getItem('sidebar_pinned') !== 'false';
            applySidebarState(isPinned);

            $sidebarPinToggle.on('click', function(e) {
                e.stopPropagation();
                const currentPinned = localStorage.getItem('sidebar_pinned') !== 'false';
                localStorage.setItem('sidebar_pinned', !currentPinned ? 'true' : 'false');
                applySidebarState(!currentPinned);
            });

            $sidebarToggle.on('click', function(e) {
                e.stopPropagation();
                $sidebar.removeClass('-translate-x-full').addClass('translate-x-0');
                $sidebarToggle.addClass('hidden');
            });

            const closeMobileSidebar = () => {
                if (!isDesktop()) {
                    $sidebar.addClass('-translate-x-full').removeClass('translate-x-0');
                    $sidebarToggle.removeClass('hidden');
                }
            };

            $sidebar.on('click', '.nav-link-item, .sidebar-logout-btn, .sidebar-footer a', closeMobileSidebar);

            $(document).on('click', function(e) {
                if (!isDesktop() && !$sidebar.is(e.target) && $sidebar.has(e.target).length === 0 &&
                    !$sidebarToggle.is(e.target) && $sidebarToggle.has(e.target).length === 0) {
                    closeMobileSidebar();
                }
            });

            $(window).on('resize', () => {
                applySidebarState(localStorage.getItem('sidebar_pinned') !== 'false');
            });
        }

        // Expose loadPage to window so it can be called elsewhere
        window.loadPage = async function(url) {
            if (!$mainContent.length) {
                window.location.href = url;
                return;
            }
            
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
                    $('#page-content').html(newContent.innerHTML);
                    if (doc.title) document.title = doc.title;
                } else {
                    $('#page-content').html(htmlText);
                    const titleMatch = htmlText.match(/<title>(.*?)<\/title>/i);
                    if (titleMatch && titleMatch[1]) {
                        document.title = titleMatch[1];
                    }
                }
                
                if (window.location.href !== url) {
                    history.pushState(null, '', url);
                }
                
                initializeForms();
                updateActiveSidebarLinks(url);
                executeScripts($('#page-content')[0]);
                window.initErpDataTables();
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
            if (!$sidebar.length) return;
            const url = new URL(urlStr);
            const path = url.pathname;
            const tab = url.searchParams.get('tab');
            
            $sidebar.find('a.nav-link-item').each(function() {
                const $link = $(this);
                const linkUrl = new URL(this.href);
                const linkPath = linkUrl.pathname;
                const linkTab = linkUrl.searchParams.get('tab');
                
                let isActive = false;
                if (linkPath === path) {
                    isActive = linkTab ? (linkTab === tab) : (!tab || (path === '/inventory' && tab === 'materials') || (path === '/invoices' && tab === 'ledger'));
                }
                
                if (isActive) {
                    $link.addClass('active-nav').removeClass('text-slate-600 hover:bg-slate-50 hover:text-slate-900');
                } else {
                    $link.removeClass('active-nav').addClass('text-slate-600 hover:bg-slate-50 hover:text-slate-900');
                }
            });
        }

        // Intercept Link Clicks
        $(document).on('click', 'a', async function(e) {
            const href = $(this).attr('href');
            if (href && href.startsWith('http') && !$(this).attr('target') && !$(this).hasClass('no-ajax')) {
                const url = new URL(href);
                if (url.origin === window.location.origin && 
                    !url.pathname.includes('/logout') && 
                    !url.pathname.includes('/print') && 
                    !url.pathname.includes('/export')) {
                    
                    e.preventDefault();
                    await window.loadPage(href);
                }
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
                    await window.loadPage(url.href);
                }
                return;
            }

            if (!$form.hasClass('ajax-form')) return;
            e.preventDefault();
            
            // Disable browser default tooltip popups dynamically
            $form.attr('novalidate', 'novalidate');
            
            // Clear previous validation states
            $form.find('input, select, textarea').each(function() {
                clearInlineError($(this));
            });
            $form.find('.form-alert').addClass('hidden').html('');

            // Client-side validation check
            let hasErrors = false;
            $form.find('input, select, textarea').each(function() {
                const $input = $(this);
                if ($input.is(':disabled') || $input.is(':submit') || $input.is(':button') || $input.attr('type') === 'hidden') return;

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

            if (hasErrors) {
                // Focus first errored field
                $form.find('.border-red-500').first().focus();
                return;
            }

            const $submitBtn = $form.find('button[type="submit"]');
            const originalBtnHtml = $submitBtn.length ? $submitBtn.html() : '';
            
            if ($submitBtn.length) {
                $submitBtn.prop('disabled', true).addClass('opacity-75');
                $submitBtn.html(`
                    <svg class="animate-spin h-5 w-5 mr-2 text-white inline-block" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Processing...</span>
                `);
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

            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method') || 'POST',
                data: ajaxData,
                processData: processData,
                contentType: contentType,
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    window.showToast('success', response.message || 'Operation completed successfully!');
                    if (!$form.hasClass('no-reset')) {
                        $form[0].reset();
                    }
                    setTimeout(async () => {
                        await window.loadPage(window.location.href);
                    }, 800);
                },
                error: function(xhr) {
                    if ($submitBtn.length) {
                        $submitBtn.prop('disabled', false).removeClass('opacity-75').html(originalBtnHtml);
                    }
                    
                    if (xhr.status === 422) {
                        const responseData = xhr.responseJSON || {};
                        const errors = responseData.errors || {};
                        
                        Object.keys(errors).forEach(key => {
                            const errorMsg = errors[key].join(', ');
                            
                            let selector = `[name="${key}"]`;
                            
                            if (key.includes('.')) {
                                const parts = key.split('.');
                                const baseName = parts[0];
                                const index = parseInt(parts[1]);
                                const $inputs = $form.find(`[name="${baseName}[]"], [name^="${baseName}["]`);
                                if ($inputs.length && $inputs.eq(index).length) {
                                    showInlineError($inputs.eq(index), errorMsg);
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
                            
                            if ($el.length) {
                                showInlineError($el.first(), errorMsg);
                            } else {
                                showGlobalFormError($form, errorMsg);
                            }
                        });
                    } else {
                        const message = xhr.responseJSON && xhr.responseJSON.message 
                            ? xhr.responseJSON.message 
                            : 'A system network failure occurred. Please try again.';
                        showGlobalFormError($form, message);
                    }
                }
            });
        });

        function showInlineError($element, message) {
            clearInlineError($element);

            // Add red border classes to the element
            $element.addClass('border-red-500 focus:border-red-500 focus:ring-red-500 focus:ring-opacity-50 text-red-900 bg-red-50/10');

            // Detect if element is part of an inline/flex table row (e.g. manual builder dynamic rows)
            const isInline = $element.closest('.flex-row, .flex, table, tr, td, .billing-row, .item-row').length > 0 && 
                             ($element.attr('name') && ($element.attr('name').includes('[]') || $element.attr('name').includes('[')));

            if (isInline) {
                // For inline fields, keep the layout 100% untouched. Store and use native tooltip title.
                const originalTitle = $element.attr('title') || '';
                $element.data('original-title', originalTitle);
                $element.attr('title', message);
            } else {
                // Create error text label above input (Image 2 style)
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

            // Clear error on user interaction (excluding focus to avoid clearing on initial focus focus)
            $element.one('input change', function() {
                clearInlineError($element);
            });
        }

        function clearInlineError($element) {
            const originalTitle = $element.data('original-title');
            if (originalTitle !== undefined) {
                if (originalTitle) $element.attr('title', originalTitle);
                else $element.removeAttr('title');
                $element.removeData('original-title');
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

        function showGlobalFormError($form, message) {
            let $alert = $form.find('.form-alert');
            if (!$alert.length) {
                $alert = $('<div class="form-alert bg-rose-50 border-rose-200 text-rose-800 p-4 rounded-xl border text-xs mb-4"></div>');
                $form.prepend($alert);
            }
            $alert.removeClass('hidden').html(`<strong>Submission Failed:</strong> ${message}`);
        }

        // Expose showToast globally
        window.showToast = function(type, message) {
            const $toast = $('#globalToast');
            const $icon = $('#toastIcon');
            const $msgText = $('#toastMessage');
            if (!$toast.length || !$icon.length || !$msgText.length) return;
            
            $msgText.text(message);
            
            if (type === 'success') {
                $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600')
                     .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>');
            } else {
                $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-rose-100 text-rose-600')
                     .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>');
            }
            
            $toast.removeClass('translate-y-[-100px] opacity-0').addClass('translate-y-0 opacity-100');
            
            setTimeout(() => {
                $toast.removeClass('translate-y-0 opacity-100').addClass('translate-y-[-100px] opacity-0');
            }, 3000);
        };

        // DataTables Global Initializer
        window.initErpDataTables = function() {
            if (typeof $.fn.DataTable === 'undefined') return;

            $('table.erp-datatable').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }

                $(this).DataTable({
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
                    columnDefs: [
                        {
                            searchable: true,
                            orderable: true,
                            targets: 0,
                            render: function (data, type, row, meta) {
                                if (type === 'display') {
                                    return meta.row + meta.settings._iDisplayStart + 1;
                                }
                                return data || (meta.row + 1);
                            }
                        }
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search records...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "No records available",
                        infoFiltered: "(filtered from _MAX_ total records)",
                        paginate: {
                            first: "«",
                            previous: "‹",
                            next: "›",
                            last: "»"
                        }
                    },
                    responsive: true,
                    order: [], // Preserve original server row order
                    autoWidth: false
                });
            });
        };

        function initializeForms() {
            $('form').attr('novalidate', 'novalidate');
        }

        // Run initial forms setup and DataTables on DOM ready
        initializeForms();
        window.initErpDataTables();
    });
});
