@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($ticket) ? 'Edit Ticket' : 'Create Ticket' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($ticket) ? route('ticket.update', ['ticket' => $ticket]) : route('ticket.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="customer" class="mb-1">Customer <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="customer" id="customer" :hasError="$errors->has('customer')" placeholder="Select a customer">
                        <option value="">Select a customer</option>
                        @foreach ($customers as $cu)
                            <option value="{{ $cu->id }}" @selected(old('customer', isset($ticket) ? $ticket->customer_id : null) == $cu->id)>{{ $cu->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('customer')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($ticket) ? $ticket->is_active : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($ticket) ? $ticket->is_active : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Attachment</x-app.input.label>
                    <x-app.input.file id="attachment[]" :hasError="$errors->has('attachment')" multiple="true"/>
                    <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    <div class="uploaded-file-preview-container" data-id="attachment">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($ticket))
                            @foreach ($ticket->attachments as $att)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                    <a href="{{ $att->url }}" target="_blank" class="text-blue-700 text-xs">{{ $att->src }}</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="subject" class="mb-1">Subject <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="subject" id="subject" :hasError="$errors->has('subject')" value="{{ old('subject', isset($ticket) ? $ticket->subject : null) }}" />
                    <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label id="body" class="mb-1">Body <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.textarea name="body" id="body" :hasError="$errors->has('body')" text="{{ old('body', isset($ticket) ? $ticket->body : null) }}" />
                    <x-input-error :messages="$errors->get('body')" class="mt-1" />
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <x-app.button.submit>{{ isset($ticket) ? 'Update Ticket' : 'Create New Ticket' }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('input[name="attachment[]"]').on('change', function() {
            let files = $(this).prop('files');

            $('.uploaded-file-preview-container[data-id="attachment"]').find('.old-preview').remove()
        
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                let clone = $('#uploaded-file-template')[0].cloneNode(true);
                $(clone).find('a').text(file.name)
                $(clone).find('a').attr('href', URL.createObjectURL(file))
                $(clone).addClass('old-preview')
                $(clone).removeClass('hidden')
                $(clone).removeAttr('id')

                $('.uploaded-file-preview-container[data-id="attachment"]').append(clone)
                $('.uploaded-file-preview-container[data-id="attachment"]').removeClass('hidden')
            }
        })
    </script>
@endpush