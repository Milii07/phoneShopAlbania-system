<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="createModalLabel">
                    <i class="ri-add-line align-middle me-1"></i> Shto Produkt të Ri
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('products.store') }}" id="createProductForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Warehouses Multiselect -->
                        <div class="col-md-12 mb-3">
                            <label for="create_warehouse_ids" class="form-label">
                                Warehouses <span class="text-danger">*</span>
                                <small class="text-muted">(Mund të zgjedhësh shumë)</small>
                            </label>
                            <select class="form-select @error('warehouse_ids') is-invalid @enderror"
                                id="create_warehouse_ids"
                                name="warehouse_ids[]"
                                multiple="multiple"
                                required>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">
                                    {{ $warehouse->name }} - {{ $warehouse->location }}
                                </option>
                                @endforeach
                            </select>
                            @error('warehouse_ids')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Kategoria <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                id="category_id"
                                name="category_id"
                                required>
                                <option value="">Zgjidh Kategorinë</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                            <select class="form-select @error('brand_id') is-invalid @enderror"
                                id="brand_id"
                                name="brand_id"
                                required>
                                <option value="">Zgjidh Brand</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Emri <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Shkruani emrin e produktit"
                                required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Çmimi <span class="text-danger">*</span></label>
                            <input type="number"
                                class="form-control @error('price') is-invalid @enderror"
                                id="price"
                                name="price"
                                value="{{ old('price') }}"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                required>
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select @error('currency_id') is-invalid @enderror"
                                id="currency_id"
                                name="currency_id"
                                required>
                                <option value="">Zgjidh Currency</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }} ({{ $currency->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Phone Fields (conditional) -->
                    <div id="phone_fields" style="display: none;">
                        <hr>
                        <h6 class="mb-3"><i class="ri-smartphone-line me-1"></i> Detaje për Telefon</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="storage" class="form-label">Storage (GB) <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('storage') is-invalid @enderror"
                                    id="storage"
                                    name="storage"
                                    value="{{ old('storage') }}"
                                    placeholder="p.sh. 128GB">
                                @error('storage')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="ram" class="form-label">RAM <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('ram') is-invalid @enderror"
                                    id="ram"
                                    name="ram"
                                    value="{{ old('ram') }}"
                                    placeholder="p.sh. 8GB">
                                @error('ram')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="color" class="form-label">Ngjyra <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('color') is-invalid @enderror"
                                    id="color"
                                    name="color"
                                    value="{{ old('color') }}"
                                    placeholder="p.sh. Black">
                                @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line align-middle me-1"></i> Ruaj Produktin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>