@extends('admin.layouts.master')
@section('content')

<div class="content-wrapper">
    <x-admin.page-header title="Manage Tax" :home-url="url('admin/dashboard')" />

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="Manage Tax">
                        <form action="{{route('update.tax')}}" method="Post">
                            @csrf

                            <div class="row">

                                {{-- Tax % --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tax Percentage (%)</label>
                                        <input type="text"
                                               name="tax_amount_per"
                                               id="tax_amount_per"
                                               value="{{ old('tax_amount_per', $tax->tax_amount_per ?? '') }}"
                                               class="form-control">

                                         @error('tax_amount_per')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror

                                    </div>
                                </div>

                                {{-- Tax Value --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tax Amount (Flat)</label>
                                        <input type="text"
                                               name="tax_amount"
                                               id="tax_amount"
                                               value="{{ old('tax_amount', $tax->tax_amount ?? '') }}"
                                               class="form-control">

                                         @error('tax_amount')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror


                                    </div>
                                </div>

                            </div>

                            <x-admin.form-actions submit-label="Save Tax" :cancel-url="url('admin/dashboard')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>

</div>

{{-- JS --}}
<script>
document.getElementById('tax_amount_per').addEventListener('input', function () {
    document.getElementById('tax_amount').value = '';
});

document.getElementById('tax_amount').addEventListener('input', function () {
    document.getElementById('tax_amount_per').value = '';
});
</script>

@endsection
