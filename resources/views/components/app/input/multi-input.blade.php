@props([
    'name' => '',
    'id' => '',
    'placeholder' => '',
    'disabled' => false,
    'hasError' => false,
    'value' => null,
    'uppercase' => false,
])

<div {{ $attributes->class(['border-red-500' => $hasError, '!bg-gray-100' => $disabled, 'uppercase-input' => $uppercase])->merge(['class' => 'bg-white rounded-md border border-gray-300 overflow-hidden p-2 py-1.5']) }}>
    <div class="flex flex-wrap gap-1.5 " id="{{ $id }}_value_group">
        <div class="rounded-full bg-slate-100 pl-2 items-center hidden {{ $id }}_values" data-id="0">
            <span class="text-xs leading-tight">some value</span>
            <button type="button" class="p-1 rounded-full bg-slate-300 ml-2 {{ $id }}_values_remove_btn" data-id="0">
                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
            </button>
        </div>
    </div>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" class="hidden" value="{{ $value }}">
    <input type="text" name="{{ $name }}_input" id="{{ $id }}" placeholder="{{ $placeholder }}" {{ $disabled ? 'disabled' : '' }} class="text-sm p-0 w-full border-none border-transparent focus:border-transparent focus:ring-0 {{ $disabled ? 'bg-gray-100' : '' }}">
</div>


@pushOnce('scripts')
    <script>
        function defaultValue(input_name, id) {
            let actualValues = []

            for (let i = 0; i < window[`${input_name}_multi_value`].length; i++) {
                const elem = window[`${ input_name }_multi_value`][i];
                
                let clone = $(`.${ id }_values`)[0].cloneNode(true);
                let totalCount = $(`.${ id }_values`).length - 1
                let lastValuesId = $($(`.${ id }_values`)[totalCount]).attr('data-id')
                lastValuesId++
    
                $(clone).attr('data-id', lastValuesId)
                $(clone).find('span').text(elem)
                $(clone).find('button').attr('data-id', lastValuesId)
                $(clone).removeClass('hidden')
                $(clone).addClass('flex')
    
                $(`#${ id }_value_group`).append(clone)
                
                actualValues.push(elem)
                $(`input[name="${ input_name }"]`).val(actualValues)
    
                $(this).val(null) // Reset 
            }

            if (actualValues.length > 0) {
                $(`input[name="${ input_name }_input"]`).addClass('mt-2')
            }
        }
    </script>
@endpushOnce

@push('scripts')
    <script>
        if ($('input[name="{{ $name }}"]').val() != null && $('input[name="{{ $name }}"]').val() != '') {
            window['{{ $name }}_multi_value'] = $('input[name="{{ $name }}"]').val().split(',')

            defaultValue('{{ $name }}', '{{ $id }}')
        }

        $('input[name="{{ $name }}_input"]').on('keypress', function(e) {
            if (e.key == ',') e.preventDefault()

            let val = $(this).val()

            if (val != '' && e.keyCode == 13) {
                let clone = $('.{{ $id }}_values')[0].cloneNode(true);
                let totalCount = $('.{{ $id }}_values').length - 1
                let lastValuesId = $($('.{{ $id }}_values')[totalCount]).attr('data-id')
                lastValuesId++

                $(clone).attr('data-id', lastValuesId)
                $(clone).find('span').text(val)
                $(clone).find('button').attr('data-id', lastValuesId)
                $(clone).removeClass('hidden')
                $(clone).addClass('flex')

                $('#{{ $id }}_value_group').append(clone)
                
                let actualValues = []
                
                let values = $('.{{ $id }}_values')
                for (let i = 0; i < values.length; i++) {
                    if ($(values[i]).attr('data-id') != 0) {
                        let value = $(values[i]).find('span').text()
                        actualValues.push(value)
                    }
                }
                $('input[name="{{ $name }}"]').val(actualValues)

                $(this).val(null) // Reset 

                return false
            }

            if ($('.{{ $id }}_values').length > 1) {
                $('input[name="{{ $name }}_input"]').addClass('mt-2')
            } else {
                $('input[name="{{ $name }}_input"]').removeClass('mt-2')
            }
        })

        $('#{{ $id }}_value_group').on('click', '.{{ $id }}_values_remove_btn', function() {
            let id = $(this).attr('data-id')

            $(`.{{ $id }}_values[data-id='${id}']`).remove()

            let actualValues = []
                
            let values = $('.{{ $id }}_values')
            for (let i = 0; i < values.length; i++) {
                if ($(values[i]).attr('data-id') != 0) {
                    let value = $(values[i]).find('span').text()
                    actualValues.push(value)
                }
            }
            $('input[name="{{ $name }}"]').val(actualValues)

            if ($('.{{ $id }}_values').length > 1) {
                $('input[name="{{ $name }}_input"]').addClass('mt-2')
            } else {
                $('input[name="{{ $name }}_input"]').removeClass('mt-2')
            }
        })
    </script>
@endpush