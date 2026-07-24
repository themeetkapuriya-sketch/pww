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
                
                if (window.location.href !== url) {
                    history.pushState(null, '', url);
                }
                
                initializeForms();
                updateActiveSidebarLinks(url);
                applySidebarState(localStorage.getItem('sidebar_pinned') !== 'false');
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
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || $(this).attr('target') || $(this).hasClass('no-ajax')) {
                return;
            }
            
            try {
                const url = new URL(href, window.location.href);
                if (url.origin === window.location.origin && 
                    !url.pathname.includes('/logout') && 
                    !url.pathname.includes('/print') && 
                    !url.pathname.includes('/download') && 
                    !url.pathname.includes('/export')) {
                    
                    e.preventDefault();
                    await window.loadPage(url.href);
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
                $submitBtn.prop('disabled', true);
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
                success: async function(response) {
                    if ($submitBtn.length) {
                        $submitBtn.prop('disabled', false);
                    }
                    window.showToast('success', response.message || 'Operation completed successfully!');
                    if (!$form.hasClass('no-reset')) {
                        $form[0].reset();
                    }
                    await window.loadPage(window.location.href);
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

        // Validate format only on blur (after user finishes typing)
        $(document).on('blur', 'input[name="vehicle_number"]', function() {
            let val = $(this).val().trim().toUpperCase();
            if (val.length > 0) {
                if (!VEHICLE_NUMBER_REGEX.test(val)) {
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

        // Expose deleteInvoiceRecord globally
        window.deleteInvoiceRecord = function(id, invoiceNumber) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Invoice?',
                    text: `Are you sure you want to permanently delete Invoice '${invoiceNumber}'? This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f43f5e',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Delete Invoice',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/invoices/${id}`,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            success: async function(response) {
                                window.showToast('success', response.message || 'Invoice deleted successfully!');
                                await window.loadPage(window.location.href);
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete invoice.';
                                window.showToast('error', msg);
                            }
                        });
                    }
                });
            } else if (confirm(`Are you sure you want to delete Invoice '${invoiceNumber}'?`)) {
                $.ajax({
                    url: `/invoices/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    success: async function(response) {
                        window.showToast('success', response.message || 'Invoice deleted successfully!');
                        await window.loadPage(window.location.href);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to delete invoice.';
                        alert(msg);
                    }
                });
            }
        };

        // Expose payInvoiceRecord globally
        window.payInvoiceRecord = function(id, invoiceNumber) {
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
                                if (window.showToast) {
                                    window.showToast('success', response.message || 'Invoice marked as paid!');
                                }
                                if (typeof window.loadPage === 'function') {
                                    await window.loadPage(window.location.href);
                                } else {
                                    window.location.reload();
                                }
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to update payment status.';
                                if (window.showToast) {
                                    window.showToast('error', msg);
                                } else {
                                    alert(msg);
                                }
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
                        if (window.showToast) {
                            window.showToast('success', response.message || 'Invoice marked as paid!');
                        }
                        if (typeof window.loadPage === 'function') {
                            await window.loadPage(window.location.href);
                        } else {
                            window.location.reload();
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

        let toastTimer = null;

        // Expose showToast globally
        window.showToast = function(type, message) {
            const $toast = $('#globalToast');
            const $icon = $('#toastIcon');
            const $msgText = $('#toastMessage');
            if (!$toast.length || !$icon.length || !$msgText.length) return;
            
            if (toastTimer) {
                clearTimeout(toastTimer);
            }
            
            $msgText.text(message);
            
            if (type === 'success') {
                $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600')
                     .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>');
            } else {
                $icon.attr('class', 'w-8 h-8 rounded-full flex items-center justify-center bg-rose-100 text-rose-600')
                     .html('<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>');
            }
            
            $toast.removeClass('translate-y-[-100px] opacity-0 pointer-events-none').addClass('translate-y-0 opacity-100 pointer-events-auto');
            
            toastTimer = setTimeout(() => {
                $toast.removeClass('translate-y-0 opacity-100 pointer-events-auto').addClass('translate-y-[-100px] opacity-0 pointer-events-none');
            }, 2500);
        };

        $(document).on('click', '#globalToast', function() {
            if (toastTimer) clearTimeout(toastTimer);
            $(this).removeClass('translate-y-0 opacity-100 pointer-events-auto').addClass('translate-y-[-100px] opacity-0 pointer-events-none');
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
                                if (meta.settings.fnRecordsTotal() === 0 || meta.settings.aiDisplay.length === 0) {
                                    return data;
                                }
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
                        infoEmpty: "Showing 0 to 0 of 0 entries",
                        zeroRecords: `<div class="py-8 text-center text-slate-500 font-medium">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-sm font-bold text-slate-600">No Records Available</p>
                            <p class="text-xs text-slate-400 mt-1">There are no records matching your request or search filter criteria.</p>
                        </div>`,
                        emptyTable: `<div class="py-8 text-center text-slate-500 font-medium">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-sm font-bold text-slate-600">No Records Available</p>
                            <p class="text-xs text-slate-400 mt-1">There are no entries recorded in this ledger yet.</p>
                        </div>`,
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
            if (window.initSearchableSelects) window.initSearchableSelects();
        }

        // Global Modal Teleport Engine: Moves top-level modal containers to document.body
        function initGlobalModalTeleport() {
            $('div.fixed[id*="Modal"], div.fixed[id*="modal"], div[id$="Modal"], div[id$="modal"]').each(function() {
                if (this.parentNode !== document.body) {
                    document.body.appendChild(this);
                }
            });
        }

        // Run initial forms setup, DataTables, and modal teleport on DOM ready
        initializeForms();
        window.initErpDataTables();
        initGlobalModalTeleport();
        $(document).ajaxComplete(initGlobalModalTeleport);
    });
});
