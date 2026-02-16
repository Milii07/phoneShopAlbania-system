@extends('layouts.app')

@section('title', 'Llogarit Bonus')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px 10px 0 0;
        margin-bottom: 20px;
    }

    .result-box {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .result-box.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .breakdown-item {
        background: white;
        border-left: 4px solid #667eea;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .breakdown-item.phone {
        border-left-color: #11998e;
    }

    .breakdown-item.accessory {
        border-left-color: #4facfe;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">💰 Llogarit Bonus të Ri</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('seller-bonuses.index') }}">Bonuset</a></li>
                    <li class="breadcrumb-item active">Llogarit Bonus</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card form-card">
            <div class="section-header">
                <h5 class="mb-0">
                    <i class="ri-calculator-line me-2"></i>
                    Llogaritja e Bonusit
                </h5>
            </div>
            <div class="card-body">
                <form id="bonusCalculatorForm">
                    @csrf
                    <div class="row">
                        <!-- Seller Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Punëtori <span class="text-danger">*</span></label>
                            <select class="form-select select2-seller" name="seller_id" id="seller_id" required>
                                <option value="">Zgjidh Punëtorin...</option>
                                @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}"
                                    data-phone-percentage="{{ $seller->phone_bonus_percentage }}"
                                    data-accessory-percentage="{{ $seller->accessory_bonus_percentage }}">
                                    {{ $seller->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Period Start -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nga Data <span class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control"
                                name="period_start"
                                id="period_start"
                                required>
                        </div>

                        <!-- Period End -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deri në Datë <span class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control"
                                name="period_end"
                                id="period_end"
                                required>
                        </div>

                        <!-- Phone Bonus Percentage -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">% Bonusi Telefonat <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number"
                                    class="form-control"
                                    name="phone_bonus_percentage"
                                    id="phone_bonus_percentage"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    placeholder="0.00"
                                    required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- Accessory Bonus Percentage -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">% Bonusi Aksesorë <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number"
                                    class="form-control"
                                    name="accessory_bonus_percentage"
                                    id="accessory_bonus_percentage"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    placeholder="0.00"
                                    required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ri-calculator-line me-1"></i> Llogarit Bonusin
                        </button>
                        <a href="{{ route('seller-bonuses.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Anulo
                        </a>
                    </div>
                </form>

                <!-- Results Section -->
                <div id="resultsSection" class="result-box" style="display: none;">
                    <h5 class="mb-4">
                        <i class="ri-check-line me-2 text-success"></i>
                        Rezultati i Llogaritjes
                    </h5>

                    <div class="breakdown-item phone">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">📱 Xhiro nga Telefonat</small>
                                <h5 class="mb-0" id="result_phone_sales">0.00 L</h5>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Bonus (<span id="result_phone_percentage">0</span>%)</small>
                                <h5 class="mb-0 text-success" id="result_phone_bonus">0.00 L</h5>
                            </div>
                        </div>
                    </div>

                    <div class="breakdown-item accessory">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">🔌 Xhiro nga Aksesorët</small>
                                <h5 class="mb-0" id="result_accessory_sales">0.00 L</h5>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Bonus (<span id="result_accessory_percentage">0</span>%)</small>
                                <h5 class="mb-0 text-info" id="result_accessory_bonus">0.00 L</h5>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Numri i Shitjeve</small>
                                <h6 class="mb-0" id="result_sales_count">0</h6>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">BONUS TOTAL</small>
                                <h3 class="mb-0 text-primary" id="result_total_bonus">0.00 L</h3>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('seller-bonuses.store') }}" method="POST" id="saveBonusForm" class="mt-4">
                        @csrf
                        <input type="hidden" name="seller_id" id="save_seller_id">
                        <input type="hidden" name="period_start" id="save_period_start">
                        <input type="hidden" name="period_end" id="save_period_end">
                        <input type="hidden" name="phone_sales_total" id="save_phone_sales_total">
                        <input type="hidden" name="accessory_sales_total" id="save_accessory_sales_total">
                        <input type="hidden" name="phone_bonus_percentage" id="save_phone_bonus_percentage">
                        <input type="hidden" name="accessory_bonus_percentage" id="save_accessory_bonus_percentage">
                        <input type="hidden" name="phone_bonus_amount" id="save_phone_bonus_amount">
                        <input type="hidden" name="accessory_bonus_amount" id="save_accessory_bonus_amount">
                        <input type="hidden" name="total_bonus" id="save_total_bonus">
                        <input type="hidden" name="total_sales_count" id="save_total_sales_count">

                        <div class="mb-3">
                            <label class="form-label">Shënime (Opsionale)</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Shënime shtesë..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="ri-save-line me-1"></i> Ruaj Bonusin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Guide -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="ri-lightbulb-line me-2 text-warning"></i>
                    Si Funksionon
                </h6>
                <ul class="small mb-0 ps-3">
                    <li class="mb-2">Zgjidhni punëtorin dhe periudhën e llogaritjes</li>
                    <li class="mb-2">Vendosni perqindjen e bonusit për telefona dhe aksesorë</li>
                    <li class="mb-2">Sistemi llogarit automatikisht xhiron dhe bonusin</li>
                    <li class="mb-2">Kontrolloni rezultatin dhe ruajeni</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="ri-information-line me-2 text-info"></i>
                    Informacion
                </h6>
                <p class="small mb-0">
                    Bonusi llogaritet vetëm për shitjet e konfirmuara (Confirmed) në periudhën e zgjedhur.
                    Produktet kategorizohen automatikisht në telefona dhe aksesorë.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2-seller').select2({
            placeholder: 'Zgjidh Punëtorin...',
            allowClear: true
        });

        // Auto-fill percentages when seller is selected
        $('#seller_id').on('change', function() {
            const selected = $(this).find(':selected');
            const phonePercentage = selected.data('phone-percentage') || 0;
            const accessoryPercentage = selected.data('accessory-percentage') || 0;

            $('#phone_bonus_percentage').val(phonePercentage);
            $('#accessory_bonus_percentage').val(accessoryPercentage);
        });

        // Calculate Bonus
        $('#bonusCalculatorForm').on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Duke Llogaritur...',
                text: 'Ju lutem prisni',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("seller-bonuses.calculate") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        const data = response.data;

                        // Display results
                        $('#result_phone_sales').text(data.phone_sales_total + ' L');
                        $('#result_phone_percentage').text(data.phone_bonus_percentage);
                        $('#result_phone_bonus').text(data.phone_bonus_amount + ' L');

                        $('#result_accessory_sales').text(data.accessory_sales_total + ' L');
                        $('#result_accessory_percentage').text(data.accessory_bonus_percentage);
                        $('#result_accessory_bonus').text(data.accessory_bonus_amount + ' L');

                        $('#result_sales_count').text(data.total_sales_count);
                        $('#result_total_bonus').text(data.total_bonus + ' L');

                        // Populate hidden form fields
                        $('#save_seller_id').val($('#seller_id').val());
                        $('#save_period_start').val($('#period_start').val());
                        $('#save_period_end').val($('#period_end').val());
                        $('#save_phone_sales_total').val(data.phone_sales_total.replace(/,/g, ''));
                        $('#save_accessory_sales_total').val(data.accessory_sales_total.replace(/,/g, ''));
                        $('#save_phone_bonus_percentage').val(data.phone_bonus_percentage);
                        $('#save_accessory_bonus_percentage').val(data.accessory_bonus_percentage);
                        $('#save_phone_bonus_amount').val(data.phone_bonus_amount.replace(/,/g, ''));
                        $('#save_accessory_bonus_amount').val(data.accessory_bonus_amount.replace(/,/g, ''));
                        $('#save_total_bonus').val(data.total_bonus.replace(/,/g, ''));
                        $('#save_total_sales_count').val(data.total_sales_count);

                        // Show results section
                        $('#resultsSection').slideDown();

                        // Scroll to results
                        $('html, body').animate({
                            scrollTop: $('#resultsSection').offset().top - 100
                        }, 500);
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gabim!',
                        text: xhr.responseJSON?.message || 'Ka ndodhur një gabim!'
                    });
                }
            });
        });

        // Validate dates
        $('#period_end').on('change', function() {
            const startDate = new Date($('#period_start').val());
            const endDate = new Date($(this).val());

            if (endDate < startDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Vëmendje!',
                    text: 'Data e mbarimit nuk mund të jetë para datës së fillimit!'
                });
                $(this).val('');
            }
        });
    });
</script>
@endpush