@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('production.index') }}">{{ __('View Production') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 rounded-md shadow flex">
        <div class="flex-[2]">
            <div class="border rounded-md flex">
                <div class="flex-1 flex flex-col p-3">
                    <span class="text-md">{{ __('Created') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $production->formatted_created_at }}</span>
                </div>
                <div class="flex-1 flex flex-col p-3 border-x">
                    <span class="text-md">{{ __('Start Date') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $production->start_date }}</span>
                </div>
                <div class="flex-1 flex flex-col p-3">
                    <span class="text-md">{{ __('Due Date') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $production->due_date }}</span>
                </div>
            </div>
            <div class="flex gap-x-4">
                <div class="flex-1 flex flex-col pt-4">
                    <div class="bg-blue-300 rounded-lg p-1.5 flex flex-col">
                        <span class="flex-1 uppercase text-lg text-center font-semibold" id="status">{{ $production->status }}</span>
                        <span class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Status') }}</span>
                    </div>
                </div>
                <div class="flex-1 flex flex-col pt-4">
                    <div class="bg-slate-300 rounded-lg p-1.5 flex flex-col">
                        <span class="flex-1 uppercase text-lg text-center font-semibold" id="progress">{{ $production->progress }}%</span>
                        <span class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Progress') }}</span>
                    </div>
                </div>
                @if ($production->priority != null)
                    <div class="flex-1 flex flex-col pt-4">
                        <div class="bg-teal-300 rounded-lg p-1.5 flex flex-col">
                            <span class="flex-1 uppercase text-lg text-center font-semibold" id="progress">{{ $production->priority->name }}</span>
                            <span class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Priority') }}</span>
                        </div>
                    </div>
                @endif
            </div>
            <div class="border-t pt-4 mt-4">
                <div class="mb-4">
                    <h6 class="text-md font-semibold">{{ __('Production Name') }}</h6>
                    <span class="text-md text-slate-500">{{ $production->name }}</span>
                </div>
                <div class="mb-4">
                    <h6 class="text-md font-semibold">{{ __('Production Description') }}</h6>
                    <span class="text-md text-slate-500">{{ $production->desc }}</span>
                </div>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Milestone') }}</h6>
                <ul>
                    @foreach ($production->milestones as $ms)
                        <li class="flex items-center gap-x-2 py-1 ms-row transition duration-300 hover:bg-slate-50" data-id="{{ $ms->pivot->id }}" data-completed="{{ $ms->pivot->submitted_at != null }}">
                            <svg class="h-5 w-5 fill-blue-500 not-completed-icon {{ $ms->pivot->submitted_at != null ? 'hidden' : '' }}" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M2.42,6.49c-.45-.32-.56-.94-.24-1.39,.8-1.13,1.78-2.11,2.91-2.91,.45-.32,1.08-.21,1.39,.24,.32,.45,.21,1.08-.24,1.39-.94,.67-1.76,1.48-2.43,2.43-.19,.28-.5,.42-.82,.42-.2,0-.4-.06-.58-.18Zm1.4,11.26c-.32-.45-.94-.56-1.39-.24-.45,.32-.56,.94-.24,1.39,.8,1.13,1.78,2.11,2.91,2.91,.17,.12,.38,.18,.58,.18,.31,0,.62-.15,.82-.42,.32-.45,.21-1.08-.24-1.39-.95-.67-1.76-1.48-2.43-2.43Zm-1.82-5.75c0-.61,.05-1.22,.16-1.81,.1-.54-.26-1.06-.8-1.16-.54-.1-1.06,.26-1.16,.8-.13,.71-.2,1.44-.2,2.17s.07,1.46,.2,2.17c.09,.48,.51,.82,.98,.82,.06,0,.12,0,.18-.02,.54-.1,.9-.62,.8-1.16-.11-.59-.16-1.2-.16-1.81Zm18.18,5.75c-.67,.95-1.48,1.76-2.43,2.43-.45,.32-.56,.94-.24,1.39,.19,.28,.5,.42,.82,.42,.2,0,.4-.06,.58-.18,1.13-.8,2.11-1.78,2.91-2.91,.32-.45,.21-1.08-.24-1.39-.45-.32-1.08-.21-1.39,.24Zm-6.37,4.09c-.59,.11-1.2,.16-1.81,.16s-1.22-.05-1.81-.16c-.54-.1-1.06,.26-1.16,.8-.1,.54,.26,1.06,.8,1.16,.71,.13,1.44,.2,2.17,.2s1.46-.07,2.17-.2c.54-.1,.9-.62,.8-1.16-.1-.54-.62-.91-1.16-.8ZM14.17,.2c-.71-.13-1.44-.2-2.17-.2s-1.46,.07-2.17,.2c-.54,.1-.9,.62-.8,1.16,.09,.48,.51,.82,.98,.82,.06,0,.12,0,.18-.02,.59-.11,1.2-.16,1.81-.16s1.22,.05,1.81,.16c.06,.01,.12,.02,.18,.02,.47,0,.89-.34,.98-.82,.1-.54-.26-1.06-.8-1.16Zm6.01,6.05c.19,.28,.5,.42,.82,.42,.2,0,.4-.06,.58-.18,.45-.32,.56-.94,.24-1.39-.8-1.13-1.78-2.11-2.91-2.91-.45-.32-1.08-.21-1.39,.24-.32,.45-.21,1.08,.24,1.39,.95,.67,1.76,1.48,2.43,2.43Zm3.62,3.58c-.1-.54-.62-.91-1.16-.8-.54,.1-.9,.62-.8,1.16,.11,.59,.16,1.2,.16,1.81s-.05,1.22-.16,1.81c-.1,.54,.26,1.06,.8,1.16,.06,.01,.12,.02,.18,.02,.47,0,.89-.34,.98-.82,.13-.71,.2-1.44,.2-2.17s-.07-1.46-.2-2.17Z"/></svg>
                            <svg class="h-5 w-5 fill-green-500 completed-icon {{ $ms->pivot->submitted_at != null ? '' : 'hidden' }}" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m23.195,14.085l-2.159,2.157v1.722c0,1.654-1.346,3-3,3h-1.721l-2.158,2.157c-.565.566-1.319.879-2.121.879s-1.555-.312-2.121-.879l-2.157-2.157h-1.722c-1.654,0-3-1.346-3-3v-1.722l-2.157-2.157c-1.17-1.169-1.17-3.073,0-4.243l2.157-2.157v-1.721c0-1.654,1.346-3,3-3h1.722l2.157-2.158c1.134-1.135,3.111-1.132,4.243,0l2.157,2.157h1.721c.553,0,1,.448,1,1s-.447,1-1,1h-2.135c-.266,0-.52-.105-.707-.293l-2.451-2.451c-.377-.376-1.034-.378-1.414,0l-2.45,2.451c-.188.188-.441.293-.707.293h-2.136c-.552,0-1,.449-1,1v2.135c0,.265-.105.52-.293.707l-2.45,2.45c-.39.39-.39,1.024,0,1.415l2.45,2.45c.188.188.293.441.293.707v2.136c0,.552.448,1,1,1h2.136c.266,0,.52.105.707.293l2.45,2.45c.379.378,1.037.378,1.413,0l2.452-2.45c.188-.188.441-.293.707-.293h2.135c.552,0,1-.448,1-1v-2.136c0-.266.105-.52.293-.707l2.451-2.45c.39-.39.39-1.025,0-1.415-.39-.391-.39-1.024,0-1.414.391-.39,1.024-.391,1.415,0,1.168,1.169,1.168,3.072,0,4.242Zm-12.474-.423l-3.018-2.988c-.394-.39-1.025-.385-1.415.007-.389.392-.385,1.025.007,1.414l3.019,2.989c.614.608,1.422.913,2.229.913s1.617-.307,2.232-.918l8.93-8.871c.392-.389.394-1.022.004-1.414-.39-.392-1.022-.395-1.414-.005l-8.93,8.872c-.453.451-1.19.45-1.644,0Z"/>
                            </svg>
                            <span class="flex-1 text-md">{{ $ms->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="flex-1 pl-4 ml-4 border-l">
            <div class="bg-blue-900 rounded-lg p-2">
                <h1 class="font-black text-xl text-white">{{ __('Production ID') }}: {{ $production->sku }}</h1>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Assigned') }}</h6>
                <ul>
                    @foreach ($production->users as $u)
                        <li class="flex items-center gap-x-4 my-2">
                            <span>
                                <div class="h-8 w-8 rounded-full border overflow-hidden">
                                    @if (count($u->pictures) > 0)
                                        <img src="{{ $u->latest_picture->url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <img src="{{ asset('images/avatar.jpg') }}" alt="Avatar image" class="h-full w-full object-cover">
                                    @endif
                                </div>
                            </span>
                            <span class="flex-1 text-md">{{ $u->name }}</span>
                            <span class="text-md">{{ getUserRole($u) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Remark') }}</h6>
                <p class="text-md text-slate-500">{{ $production->remark ?? 'No Remark' }}</p>
            </div>
        </div>
    </div>

    <x-app.modal.production-milestone-modal/>
@endsection

@push('scripts')
<script>
    PRODUCTION = @json($production);
    PRODUCTION_MILESTONE_MATERIALS = @json($production_milestone_materials);

    $('.ms-row').on('click', function(e) {
        e.preventDefault()

        let id = $(this).data('id')
        let requiredSerialNo = false
        let completed = $(this).data('completed')
        let milestoneCount = PRODUCTION.milestones.length
        
        $('.ms-row').each(function() {
            if ($(this).data('completed') == true) {
                milestoneCount--
            }
        })

        // Prepare milestone modal material's content
        $('#serial-no-selection-container .selection').remove()

        for (let i = 0; i < PRODUCTION.milestones.length; i++) {
            if (PRODUCTION.milestones[i].pivot.id == id && PRODUCTION.milestones[i].pivot.material_use_product_id != null) {
                for (let j = 0; j < PRODUCTION.milestones[i].pivot.material_use_products.length; j++) {
                    if (PRODUCTION.milestones[i].pivot.material_use_products[j].material.is_sparepart == true) {
                        var productId = PRODUCTION.milestones[i].pivot.material_use_products[j].material.id

                        var clone = $('#serial-no-container #sp-template')[0].cloneNode(true);

                        $(clone).removeAttr('id')
                        $(clone).removeClass('hidden')
                        $(clone).addClass('selection')
                        $(clone).find('#product-name').text(PRODUCTION.milestones[i].pivot.material_use_products[j].material.model_name)
                        $(clone).find('#qty-needed').text(`Quantity needed: x${PRODUCTION.milestones[i].pivot.material_use_products[j].qty}`)
                        $(clone).attr('data-product-id', productId)
                        $(clone).find('#materials_err').attr('data-mu-id', PRODUCTION.milestones[i].pivot.material_use_products[j].material.id)
                        $('#serial-no-selection-container').append(clone)

                        // Prepare serial no selection
                        let children = PRODUCTION.milestones[i].pivot.material_use_products[j].material.children

                        let childIdsToHide = []
                        for (const key in PRODUCTION_MILESTONE_MATERIALS) { // Hidden other milestone selected child
                            if (key == id) {
                                continue   
                            }
                            childIdsToHide = childIdsToHide.concat(PRODUCTION_MILESTONE_MATERIALS[key])
                        }
                        for (let k = 0; k < children.length; k++) {
                            if (childIdsToHide.includes(children[k].id)) {
                                continue
                            }

                            let spClone = $(`#sp-template .sp-serial-no-container .sp-serial-no-template`)[0].cloneNode(true);
                            
                            $(spClone).data('id', children[k].id)
                            $(spClone).removeAttr('id')
                            $(spClone).removeClass('hidden')
                            $(spClone).find('input').attr('id', children[k].id)
                            $(spClone).find('input').attr('data-mu-id', PRODUCTION.milestones[i].pivot.material_use_products[j].material.id)
                            // $(spClone).find('input').prop('checked', false)
                            $(spClone).find('label').text(children[k].sku)
                            $(spClone).find('label').attr('for', children[k].id)
                            
                            $(`.selection[data-product-id="${productId}"] .sp-serial-no-container`).append(spClone)
                        }
                    } else {
                        var clone = $('#serial-no-container #rm-template')[0].cloneNode(true);
    
                        $(clone).removeAttr('id')
                        $(clone).removeClass('hidden')
                        $(clone).addClass('selection')
                        $(clone).find('#product-name').text(PRODUCTION.milestones[i].pivot.material_use_products[j].material.model_name)
                        $(clone).find('#qty-needed').text(`Quantity needed: x${PRODUCTION.milestones[i].pivot.material_use_products[j].qty}`)
                        $('#serial-no-selection-container').append(clone)
                    }

                    if ((j + 1) < PRODUCTION.milestones[i].pivot.material_use_products.length) {
                        $(clone).addClass('pb-4 border-b')
                    }
                    requiredSerialNo = true
                }
                break
            }
        }

        $('#production-milestone-modal #date').text(moment().format('D MMM YYYY HH:mm'))
        $('#production-milestone-modal #yes-btn').attr('data-id', id)
        if (requiredSerialNo) $('#production-milestone-modal #serial-no-container').removeClass('hidden')
        else $('#production-milestone-modal #serial-no-container').addClass('hidden')
        // Auto select materials needed
        if (PRODUCTION_MILESTONE_MATERIALS[id] !== undefined) {
            for (let i = 0; i < PRODUCTION_MILESTONE_MATERIALS[id].length; i++) {
                const element = PRODUCTION_MILESTONE_MATERIALS[id][i];
                
                $(`#production-milestone-modal #serial-no-container input[name="serial_no[]"][id="${element}"]`).attr('checked', true)
            }
        }
        // Completed
        if (completed) {
            if (requiredSerialNo && milestoneCount > 0) {
                $('#production-milestone-modal #yes-btn').text('Update')
                $('#production-milestone-modal #yes-btn').removeClass('hidden')
            } else {
                $('#production-milestone-modal #yes-btn').addClass('hidden')
            }
            $('#production-milestone-modal #no-btn').text('Close')
        } else {
            $('#production-milestone-modal #yes-btn').text('Confirm')
            $('#production-milestone-modal #yes-btn').removeClass('hidden')
            $('#production-milestone-modal #no-btn').text('No')
        }

        $('#production-milestone-modal').addClass('show-modal')
    })
</script>
@endpush