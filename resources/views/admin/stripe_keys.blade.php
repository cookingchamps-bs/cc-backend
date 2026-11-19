@extends('admin.layouts.master')
@section('content')

<div class="content-wrapper">

        <x-admin.page-header :title="$common['title'] ?? 'Stripe keys'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Stripe keys'">
                    <form action="{{ route('store.stripe.keys') }}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <p class="text-muted">
                                    Keys saved here are used for checkout and when creating subscription plans.
                                    Leave a field blank to keep the current value.
                                    @if(!empty($usingEnvFallback))
                                        Currently using keys from the server <code>.env</code> file until you save keys here.
                                    @endif
                                </p>

                                <div class="form-group">
                                    <label for="stripe_key">Publishable key</label>
                                    <input type="password"
                                           name="stripe_key"
                                           id="stripe_key"
                                           value="{{ old('stripe_key') }}"
                                           class="form-control @error('stripe_key') is-invalid @enderror"
                                           placeholder="{{ !empty($hasPublishableKey) ? 'Key is saved — enter a new key to replace it' : 'pk_test_... or pk_live_...' }}"
                                           autocomplete="off">
                                    @error('stripe_key')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Starts with <code>pk_test_</code> or <code>pk_live_</code>.
                                    </small>
                                </div>
                                @if(!empty($hasPublishableKey))
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="clear_stripe_key" name="clear_stripe_key" value="1">
                                        <label class="custom-control-label" for="clear_stripe_key">Clear stored publishable key</label>
                                    </div>
                                </div>
                                @endif

                                <div class="form-group">
                                    <label for="stripe_secret">Secret key</label>
                                    <input type="password"
                                           name="stripe_secret"
                                           id="stripe_secret"
                                           value="{{ old('stripe_secret') }}"
                                           class="form-control @error('stripe_secret') is-invalid @enderror"
                                           placeholder="{{ !empty($hasSecretKey) ? 'Key is saved — enter a new key to replace it' : 'sk_test_... or sk_live_...' }}"
                                           autocomplete="off">
                                    @error('stripe_secret')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Starts with <code>sk_test_</code> or <code>sk_live_</code>. Used to create Stripe checkout sessions.
                                    </small>
                                </div>
                                @if(!empty($hasSecretKey))
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="clear_stripe_secret" name="clear_stripe_secret" value="1">
                                        <label class="custom-control-label" for="clear_stripe_secret">Clear stored secret key</label>
                                    </div>
                                </div>
                                @endif

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="show_stripe_keys">
                                        <label class="custom-control-label" for="show_stripe_keys">Show keys while typing</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="url('admin/dashboard')" />
                    </form>
                    </x-admin.data-card>
        </div>
    </section>

</div>

@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('#show_stripe_keys').on('change', function () {
            var type = this.checked ? 'text' : 'password';
            $('#stripe_key, #stripe_secret').attr('type', type);
        });
    });
</script>
@endpush
