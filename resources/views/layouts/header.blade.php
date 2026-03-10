<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('dashboard') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="17">
                        </span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                {{-- GLOBAL SEARCH - pranë hamburger --}}
                <div class="d-none d-md-flex align-items-center position-relative ms-2" id="global-search-wrapper">
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text bg-light border-end-0 border-light">
                            <i class="ri-search-line text-muted"></i>
                        </span>
                        <input
                            type="text"
                            id="globalSearchInput"
                            class="form-control bg-light border-start-0 border-light ps-0"
                            placeholder="Kërko klient, faturë, IMEI..."
                            autocomplete="off">
                        <span class="input-group-text bg-light border-start-0 border-light d-none" id="searchSpinner">
                            <div class="spinner-border spinner-border-sm text-primary" role="status" style="width:14px;height:14px;"></div>
                        </span>
                    </div>
                    <div id="globalSearchDropdown"
                        class="position-absolute bg-white rounded shadow-lg border"
                        style="top:100%; left:0; width:480px; max-height:480px; overflow-y:auto; z-index:9999; display:none; margin-top:4px;">
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">

                <!-- Search mobile (vetëm mobile) -->
                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                    <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">3<span class="visually-hidden">unread messages</span></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">
                        <!-- Notification content here -->
                    </div>
                </div>

                <!-- User Profile -->
                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/multi-user.jpg') }}" alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ Auth::user()->name ?? 'User' }}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text">{{ Auth::user()->role ?? 'Admin' }}</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Welcome {{ Auth::user()->name ?? 'User' }}!</h6>
                        <a class="dropdown-item" href="#"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
                        <a class="dropdown-item" href="#"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#"><i class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance : <b>$5971.67</b></span></a>
                        <a class="dropdown-item" href="#"><span class="badge bg-success-subtle text-success mt-1 float-end">New</span><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
                        <a class="dropdown-item" href="#"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>

{{-- PARTNER HISTORY MODAL --}}
<div class="modal fade" id="searchPartnerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-user-line me-2 text-primary"></i>
                    <span id="searchPartnerModalTitle">Historiku i Klientit</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="searchPartnerModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('globalSearchInput');
        const dropdown = document.getElementById('globalSearchDropdown');
        const spinner = document.getElementById('searchSpinner');

        if (!input || !dropdown || !spinner) return;

        let debounceTimer = null;

        // ── Input debounce ───────────────────────────────────────
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (q.length < 2) {
                hideDropdown();
                return;
            }
            debounceTimer = setTimeout(() => doSearch(q), 300);
        });

        // ── Mbyll kur klikohet jashtë ────────────────────────────
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('global-search-wrapper');
            if (wrapper && !wrapper.contains(e.target)) hideDropdown();
        });

        // ── Keyboard navigation ──────────────────────────────────
        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.gs-item');
            const active = dropdown.querySelector('.gs-item.active');
            let idx = Array.from(items).indexOf(active);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (idx < items.length - 1) {
                    active && active.classList.remove('active');
                    items[idx + 1].classList.add('active');
                    items[idx + 1].scrollIntoView({
                        block: 'nearest'
                    });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (idx > 0) {
                    active && active.classList.remove('active');
                    items[idx - 1].classList.add('active');
                    items[idx - 1].scrollIntoView({
                        block: 'nearest'
                    });
                }
            } else if (e.key === 'Enter' && active) {
                e.preventDefault();
                active.click();
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        function doSearch(q) {
            spinner.classList.remove('d-none');
            fetch('/global-search?q=' + encodeURIComponent(q), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    spinner.classList.add('d-none');
                    renderResults(data, q);
                })
                .catch(err => {
                    spinner.classList.add('d-none');
                    dropdown.innerHTML = `
                <div class="p-3 text-center text-danger">
                    <i class="ri-error-warning-line me-1"></i> Gabim gjatë kërkimit. (${err.message})
                </div>`;
                    showDropdown();
                });
        }

        // ── Render results ───────────────────────────────────────
        function renderResults(data, q) {
            if (!data || data.length === 0) {
                dropdown.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="ri-search-line fs-2 d-block mb-2 opacity-50"></i>
                    Nuk u gjet asgjë për "<strong>${escHtml(q)}</strong>"
                </div>`;
                showDropdown();
                return;
            }

            const groupLabels = {
                partner: {
                    label: 'Klientët',
                    icon: 'ri-user-line'
                },
                sale: {
                    label: 'Faturat',
                    icon: 'ri-file-list-3-line'
                },
                product: {
                    label: 'Produktet',
                    icon: 'ri-smartphone-line'
                },
                imei: {
                    label: 'IMEI',
                    icon: 'ri-barcode-line'
                },
                purchase: {
                    label: 'Hyrjet',
                    icon: 'ri-download-2-line'
                },
                online_order: {
                    label: 'Porositë Online',
                    icon: 'ri-shopping-cart-line'
                },
                bonus: {
                    label: 'Bonuset',
                    icon: 'ri-gift-line'
                },
            };

            const colorMap = {
                primary: '#0d6efd',
                success: '#198754',
                info: '#0dcaf0',
                warning: '#ffc107',
                danger: '#dc3545',
                secondary: '#6c757d'
            };

            const groups = {};
            data.forEach(item => {
                if (!groups[item.type]) groups[item.type] = [];
                groups[item.type].push(item);
            });

            let html = '';

            Object.entries(groups).forEach(([type, items]) => {
                const grp = groupLabels[type] || {
                    label: type,
                    icon: 'ri-search-line'
                };
                html += `
                <div class="gs-group-header px-3 py-2 bg-light border-bottom d-flex align-items-center gap-2">
                    <i class="${grp.icon} text-muted" style="font-size:13px;"></i>
                    <span class="fw-semibold text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;">${grp.label}</span>
                    <span class="badge bg-secondary ms-auto" style="font-size:10px;">${items.length}</span>
                </div>`;

                items.forEach(item => {
                    const iconColor = colorMap[item.color] || '#6c757d';
                    html += `
                    <div class="gs-item d-flex align-items-center gap-3 px-3 py-2"
                         data-type="${escAttr(item.type)}"
                         data-id="${escAttr(String(item.id))}"
                         data-url="${escAttr(item.url || '')}"
                         style="cursor:pointer;transition:background .15s;">
                        <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center"
                             style="width:34px;height:34px;background:${iconColor}20;">
                            <i class="${item.icon}" style="color:${iconColor};font-size:16px;"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-medium text-truncate" style="font-size:13px;">${highlightMatch(item.title, q)}</div>
                            <div class="text-muted text-truncate" style="font-size:11px;">${escHtml(item.subtitle || '')}</div>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted flex-shrink-0"></i>
                    </div>`;
                });
            });

            html += `
            <div class="border-top px-3 py-2 text-center">
                <small class="text-muted">${data.length} rezultat${data.length !== 1 ? 'e' : ''} • ESC për të mbyllur</small>
            </div>`;

            dropdown.innerHTML = html;

            dropdown.querySelectorAll('.gs-item').forEach(el => {
                el.addEventListener('mouseenter', () => {
                    dropdown.querySelectorAll('.gs-item').forEach(i => i.classList.remove('active'));
                    el.classList.add('active');
                });
                el.addEventListener('click', () => handleResultClick(el));
            });

            showDropdown();
        }

        // ── Click handler ────────────────────────────────────────
        function handleResultClick(el) {
            const type = el.dataset.type;
            const id = el.dataset.id;
            const url = el.dataset.url;
            hideDropdown();
            input.value = '';
            if (type === 'partner') {
                openPartnerModal(id);
            } else if (url) {
                window.location.href = url;
            }
        }

        // ── Partner modal ────────────────────────────────────────
        function openPartnerModal(id) {
            const modalEl = document.getElementById('searchPartnerModal');
            const modalBody = document.getElementById('searchPartnerModalBody');
            const modalTitle = document.getElementById('searchPartnerModalTitle');

            if (!modalEl) return;

            const modal = new bootstrap.Modal(modalEl);
            modalBody.innerHTML = `<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>`;
            modal.show();

            fetch('/partners/' + id, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    modalTitle.textContent = data.name;

                    const statusBadge = s => ({
                        'Confirmed': '<span class="badge bg-success">E Konfirmuar</span>',
                        'Draft': '<span class="badge bg-secondary">Draft</span>',
                        'PrePaid': '<span class="badge bg-info">Parapaguese</span>',
                        'Rejected': '<span class="badge bg-danger">Refuzuar</span>',
                    } [s] || `<span class="badge bg-secondary">${escHtml(s)}</span>`);

                    const payBadge = s => ({
                        'Paid': '<span class="badge bg-success">E Paguar</span>',
                        'Unpaid': '<span class="badge bg-danger">E Papaguar</span>',
                        'Partial': '<span class="badge bg-warning text-dark">Pjesërisht</span>',
                    } [s] || `<span class="badge bg-secondary">${escHtml(s)}</span>`);

                    const rows = data.sales && data.sales.length > 0 ?
                        data.sales.map(s => `
                    <tr>
                        <td><a href="${escAttr(s.show_url)}" class="text-primary fw-bold" target="_blank">${escHtml(s.invoice_number)}</a></td>
                        <td>${escHtml(s.invoice_date)}</td>
                        <td><strong>${escHtml(s.currency)} ${escHtml(s.total_amount)}</strong></td>
                        <td>${statusBadge(s.sale_status)}</td>
                        <td>${payBadge(s.payment_status)}</td>
                    </tr>`).join('') :
                        `<tr><td colspan="5" class="text-center text-muted py-3">
                       <i class="ri-file-list-line fs-4 d-block mb-1"></i>Nuk ka fatura.
                   </td></tr>`;

                    const debtAlert = (data.stats.unpaid > 0 || data.stats.partial > 0) ?
                        `<div class="alert alert-warning py-2 mb-3">
                       <i class="ri-error-warning-line me-1"></i>
                       <strong>Borxh aktiv:</strong> ${escHtml(data.stats.total_unpaid_amount)} ALL
                   </div>` : '';

                    modalBody.innerHTML = `
                <div class="row align-items-center mb-3">
                    <div class="col">
                        <p class="mb-1"><i class="ri-user-line me-1 text-muted"></i><strong>${escHtml(data.name)}</strong></p>
                        <p class="mb-0"><i class="ri-phone-line me-1 text-muted"></i>${escHtml(data.phone)}</p>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-primary fs-6">${data.stats.total_invoices} Fatura</span>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-bold text-success fs-5">${data.stats.paid}</div>
                            <small class="text-muted">Të Paguara</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-bold text-danger fs-5">${data.stats.unpaid}</div>
                            <small class="text-muted">Të Papaguara</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-bold text-warning fs-5">${data.stats.partial}</div>
                            <small class="text-muted">Pjesërisht</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-bold text-dark" style="font-size:.9rem;">${escHtml(data.stats.total_spent)}</div>
                            <small class="text-muted">Totali</small>
                        </div>
                    </div>
                </div>
                ${debtAlert}
                <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Fatura</th><th>Data</th><th>Totali</th><th>Statusi</th><th>Pagesa</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>`;
                })
                .catch(() => {
                    modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>Gabim gjatë ngarkimit të të dhënave.
                </div>`;
                });
        }

        // ── Helpers ──────────────────────────────────────────────
        function showDropdown() {
            dropdown.style.display = 'block';
        }

        function hideDropdown() {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
        }

        function escHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function escAttr(str) {
            return escHtml(str);
        }

        function highlightMatch(text, q) {
            const escaped = escHtml(text);
            const escapedQ = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return escaped.replace(new RegExp(`(${escHtml(escapedQ)})`, 'gi'),
                '<mark class="p-0 bg-warning bg-opacity-50">$1</mark>');
        }

        // ── Styles ───────────────────────────────────────────────
        const style = document.createElement('style');
        style.textContent = `
        .gs-item:hover, .gs-item.active { background: #f8f9fa !important; }
        .gs-group-header { position: sticky; top: 0; z-index: 1; }
        #globalSearchInput:focus { box-shadow: none; }
        #global-search-wrapper .input-group:focus-within {
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
            border-radius: 6px;
        }
    `;
        document.head.appendChild(style);
    });
</script>