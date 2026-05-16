@extends('layouts.app')

@section('title', 'Histori Produkti')

@section('content')
<div class="container py-4">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Search Results</h5>
        </div>

        <div class="card-body">

            @if($products->count())

                <div class="list-group">

                    @foreach($products as $product)

                        <a href="{{ route('product-history.show', $product->id) }}"
                           class="list-group-item list-group-item-action">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>
                                    <h6 class="mb-1">
                                        {{ $product->name }}
                                    </h6>

                                    <small class="text-muted">
                                        SKU: {{ $product->sku ?? '-' }}
                                        |
                                        Barcode: {{ $product->barcode ?? '-' }}
                                    </small>

                                    <br>

                                    <small class="text-muted">
                                        Category:
                                        {{ $product->category->name ?? '-' }}

                                        |

                                        Brand:
                                        {{ $product->brand->name ?? '-' }}
                                    </small>
                                </div>

                                <div>
                                    <i class="ri-arrow-right-line fs-5"></i>
                                </div>

                            </div>

                        </a>

                    @endforeach

                </div>

            @else

                <div class="text-center py-5">
                    <h6 class="text-muted mb-0">
                        No products found
                    </h6>
                </div>

            @endif

        </div>
    </div>

</div>
@endsection