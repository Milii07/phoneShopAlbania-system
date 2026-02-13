<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logooo.png') }}" alt="" height="34" width="170">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logooo.png') }}" alt="" height="44" width="170">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logooo.png') }}" alt="" height="34" width="170">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logooo.png') }}" alt="" height="44" width="170">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                @if(function_exists('user_can_access_route') && user_can_access_route('dashboard'))
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboards</span>
                    </a>
                </li>
                @endif

                <li class="nav-item">
                    @if(function_exists('user_can_access_route') && user_can_access_route('warehouses.index'))
                    <a class="nav-link menu-link" href="#sidebarWarehouse" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarWarehouse">
                        <i class="ri-store-2-line"></i> <span data-key="t-apps">Dyqanet</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarWarehouse">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('warehouses.index') }}" class="nav-link" data-key="t-calendar"> Dyqanet </a>
                            </li>
                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('categories.index'))
                    <a class="nav-link menu-link" href="#sidebarCategories" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCategories">
                        <i class="ri-smartphone-fill"></i> <span data-key="t-apps">Kategorite</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCategories">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('categories.index') }}" class="nav-link" data-key="t-calendar"> Kategorite </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('brands.index'))
                    <a class="nav-link menu-link" href="#sidebarBrands" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBrands">
                        <i class="ri-apple-fill"></i> <span data-key="t-apps">Brendet</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarBrands">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('brands.index') }}" class="nav-link" data-key="t-calendar"> Brendet </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('currencies.index'))
                    <a class="nav-link menu-link" href="#sidebarCurrency" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCurrency">
                        <i class="ri-money-pound-box-fill"></i> <span data-key="t-apps">Monedhat</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCurrency">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('currencies.index') }}" class="nav-link" data-key="t-calendar"> Monedhat </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('products.index'))
                    <a class="nav-link menu-link" href="#sidebarProducts" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarProducts">
                        <i class="ri-barcode-line"></i> <span data-key="t-apps">Produktet</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarProducts">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('products.index') }}" class="nav-link" data-key="t-calendar"> Produktet </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('partners.index'))
                    <a class="nav-link menu-link" href="#sidebarPartners" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPartners">
                        <i class="ri-group-2-fill"></i> <span data-key="t-apps">Partnerët</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarPartners">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('partners.index') }}" class="nav-link" data-key="t-calendar"> Partnerët </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('sellers.index'))
                    <a class="nav-link menu-link" href="#sidebarSellers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSellers">
                        <i class="ri-user-add-fill"></i> <span data-key="t-apps">Shitesit</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarSellers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('sellers.index') }}" class="nav-link" data-key="t-calendar"> Shitesit </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('purchases.index'))
                    <a class="nav-link menu-link" href="#sidebarPurchases" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPurchases">
                        <i class="ri-file-list-3-fill"></i><span data-key="t-apps">Blerjet & Hyrjet</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarPurchases">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('purchases.index') }}" class="nav-link" data-key="t-calendar"> Blerjet </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('sales.index'))
                    <a class="nav-link menu-link" href="#sidebarSales" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarSales">
                        <i class="ri-price-tag-3-line"></i><span data-key="t-apps">Shitjet</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarSales">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('sales.index') }}" class="nav-link" data-key="t-calendar"> Shitjet </a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('stock-movements.index'))
                    <a class="nav-link menu-link" href="#sidebarStockMovements" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarStockMovements">
                        <i class=" ri-todo-line"></i> <span data-key="t-apps">Inventari</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarStockMovements">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('stock-movements.index') }}" class="nav-link" data-key="t-calendar">Inventari</a>
                            </li>

                        </ul>
                    </div>
                    @endif

                    @if(function_exists('user_can_access_route') && user_can_access_route('sales.daily-report'))
                    <a class="nav-link menu-link" href="#sidebarRaportet" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarRaportet">
                        <i class=" ri-bar-chart-fill"></i> <span data-key="t-apps">Raportet ditore</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarRaportet">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('sales.daily-report') }}" class="nav-link" data-key="t-calendar"> Raportet </a>
                            </li>

                        </ul>
                    </div>
                    @endif


                </li>



                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Pages</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAuth" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAuth">
                        <i class="ri-account-circle-line"></i> <span data-key="t-authentication">Authentication</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarAuth">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link" data-key="t-signin"> Sign In </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link" data-key="t-signup"> Sign Up </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>