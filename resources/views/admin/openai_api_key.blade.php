@extends('admin.layouts.master')
@section('content')

<div class="content-wrapper">

        <x-admin.page-header :title="$common['title'] ?? 'OpenAI API Key'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'OpenAI API Key'">
                    <form action="{{ route('store.openai.api.key') }}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="openai_api_key">OpenAI API Key</label>
                                    <input type="password"
                                           name="openai_api_key"
                                           id="openai_api_key"
                                           value=""
                                           class="form-control"
                                           placeholder="{{ !empty($hasKey) ? 'Key is saved — enter a new key to replace it' : 'sk-...' }}"
                                           autocomplete="off">
                                    <small class="form-text text-muted">
                                        @if(!empty($hasKey))
                                            A key is already stored. Leave blank to keep it, or enter a new key to replace it.
                                        @else
                                            Used for OpenAI-powered features.
                                        @endif
                                    </small>
                                </div>
                                @if(!empty($hasKey))
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="clear_openai_api_key" name="clear_openai_api_key" value="1">
                                        <label class="custom-control-label" for="clear_openai_api_key">Clear stored API key</label>
                                    </div>
                                </div>
                                @endif
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="show_openai_key">
                                        <label class="custom-control-label" for="show_openai_key">Show API key while typing</label>
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
        $('#show_openai_key').on('change', function () {
            $('#openai_api_key').attr('type', this.checked ? 'text' : 'password');
        });
    });
</script>
@endpush
