@extends('layouts.app')

@section('title', 'Histori Produkti')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Histori Produkti</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Kërkimi i Produkteve</h4>
            </div>

            <div class="card-body">
                <form id="searchForm" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="searchInput" class="form-label">Kërkimi</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Kërko produktin..." required>
                    </div>

                    <div class="col-md-4">
                        <label for="searchType" class="form-label">Lloji i Kërkimit</label>
                        <select class="form-select" id="searchType">
                            <option value="name">Emri i Produktit</option>
                            <option value="sku">SKU</option>
                            <option value="barcode">Barcode</option>
                            <option value="imei">IMEI</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" id="searchBtn">
                            <i class="ri-search-line"></i> Kërko
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row" id="resultsContainer" style="display: none;">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Rezultatet e Kërkimit</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Emri i Produktit</th>
                                <th>Kategoria</th>
                                <th>Marca</th>
                                <th>Çmimi i Blerjës</th>
                                <th>Çmimi i Shitjës</th>
                                <th>Aksione</th>
                            </tr>
                        </thead>
                        <tbody id="searchResults">
                            <!-- Results loaded via JS -->
                        </tbody>
                    </table>
                </div>
                <div id="noResults" class="alert alert-info text-center" style="display: none;">
                    Nuk u gjetën produkte që përputhen me kërkimin tuaj.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Histori Produkti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="historyModalContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('searchBtn').addEventListener('click', function () {
    const searchTerm = document.getElementById('searchInput').value;
    const searchType = document.getElementById('searchType').value;

    if (!searchTerm) {
        Swal.fire('Gabim', 'Ju lutemi fusni një term kërkimi', 'error');
        return;
    }

    fetch(`{{ route('product-history.search') }}?q=${encodeURIComponent(searchTerm)}&type=${searchType}`, {
    headers: {
        'Accept': 'application/json'
    }
})
        .then(response => response.json())
        .then(products => {
            const resultsContainer = document.getElementById('resultsContainer');
            const searchResults = document.getElementById('searchResults');
            const noResults = document.getElementById('noResults');

            searchResults.innerHTML = '';

            if (products.length === 0) {
                noResults.style.display = 'block';
                resultsContainer.style.display = 'block';
                return;
            }

            noResults.style.display = 'none';

        products.forEach(product => {

            const showUrl = `{{ url('product-history') }}/product/${product.id}`;

            const row = document.createElement('tr');

            row.style.cursor = 'pointer';

            row.onclick = function () {
                window.location.href = showUrl;
            };

            row.innerHTML = `
                <td>${product.id}</td>

                <td>${product.name}</td>

                <td>${product.category?.name || '-'}</td>

                <td>${product.brand?.name || '-'}</td>

                <td>${parseFloat(product.price || 0).toFixed(2)} €</td>

                <td>${parseFloat(product.sale_price || 0).toFixed(2)} €</td>

                <td>
                    <a href="${showUrl}"
                    class="btn btn-sm btn-primary">

                        <i class="ri-eye-line"></i>
                        Shfaq
                    </a>
                </td>
            `;

            searchResults.appendChild(row);
        });

            resultsContainer.style.display = 'block';
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Gabim', 'Ndodhi një gabim gjatë kërkimit', 'error');
        });
});

function viewProductHistory(productId) {
    fetch(`{{ url('product-history/product') }}/${productId}/modal`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('historyModalContent').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('historyModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Gabim', 'Nuk u arrit të ngarko historia', 'error');
        });
}

// Allow search on Enter key
document.getElementById('searchInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('searchBtn').click();
    }
});
</script>
@endpush
