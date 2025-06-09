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
                    <span class="text-md">{{ __('Created At') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $production->formatted_created_at }}</span>
                </div>
                <div class="flex-1 flex flex-col p-3 border-x">
                    <span class="text-md">{{ __('Start Date') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $production->start_date }}</span>
                </div>
                <div class="flex-1 flex flex-col p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-md">{{ __('Due Date') }}</span>
                        <div class="flex gap-x-3">
                            @if ($can_extend_due_date)
                                <x-app.button.button class="bg-transparent !p-0" title="{{ __('Extend Due Date') }}"
                                    id="extend-due-date-btn">
                                    <svg class="h-4 w-4" id="Layer_1" height="512" viewBox="0 0 24 24" width="512"
                                        xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                                        <path
                                            d="m23 18h-3v-3a1 1 0 0 0 -2 0v3h-3a1 1 0 0 0 0 2h3v3a1 1 0 0 0 2 0v-3h3a1 1 0 0 0 0-2z" />
                                        <path
                                            d="m11 7v4.586l-2.707 2.707a1 1 0 1 0 1.414 1.414l3-3a1 1 0 0 0 .293-.707v-5a1 1 0 0 0 -2 0z" />
                                        <path
                                            d="m14.728 21.624a9.985 9.985 0 1 1 6.9-6.895 1 1 0 1 0 1.924.542 11.989 11.989 0 1 0 -8.276 8.277 1 1 0 1 0 -.544-1.924z" />
                                    </svg>
                                </x-app.button.button>
                            @endif
                            @if (count($production->dueDates) > 0)
                                <x-app.button.button class="bg-transparent !p-0" title="{{ __('Extend History') }}"
                                    id="extend-due-date-history-btn">
                                    <svg class="h-4 w-4" id="Layer_1" height="512" viewBox="0 0 24 24" width="512"
                                        xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                                        <path d="m9 24h-8a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2z" />
                                        <path d="m7 20h-6a1 1 0 0 1 0-2h6a1 1 0 0 1 0 2z" />
                                        <path d="m5 16h-4a1 1 0 0 1 0-2h4a1 1 0 0 1 0 2z" />
                                        <path
                                            d="m13 23.955a1 1 0 0 1 -.089-2 10 10 0 1 0 -10.87-10.865 1 1 0 0 1 -1.992-.18 12 12 0 0 1 23.951 1.09 11.934 11.934 0 0 1 -10.91 11.951c-.03.003-.061.004-.09.004z" />
                                        <path
                                            d="m12 6a1 1 0 0 0 -1 1v5a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414l-2.707-2.707v-4.586a1 1 0 0 0 -1-1z" />
                                    </svg>
                                </x-app.button.button>
                            @endif
                        </div>
                    </div>
                    <span class="text-lg mt-2 font-semibold">{{ $production->due_date }}</span>
                </div>
            </div>
            <div class="flex gap-x-4">
                <div class="flex-1 flex flex-col pt-4">
                    <div class="bg-blue-300 rounded-lg p-1.5 flex flex-col">
                        <span class="flex-1 uppercase text-lg text-center font-semibold"
                            id="status">{{ $production->status }}</span>
                        <span
                            class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Status') }}</span>
                    </div>
                </div>
                <div class="flex-1 flex flex-col pt-4">
                    <div class="bg-slate-300 rounded-lg p-1.5 flex flex-col">
                        <span class="flex-1 uppercase text-lg text-center font-semibold"
                            id="progress">{{ $production->progress }}%</span>
                        <span
                            class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Progress') }}</span>
                    </div>
                </div>
                @if ($production->priority != null)
                    <div class="flex-1 flex flex-col pt-4">
                        <div class="bg-teal-300 rounded-lg p-1.5 flex flex-col">
                            <span class="flex-1 uppercase text-lg text-center font-semibold"
                                id="progress">{{ $production->priority->name }}</span>
                            <span
                                class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Priority') }}</span>
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
                    <span class="text-md text-slate-500">{{ $production->desc ?? '-' }}</span>
                </div>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Milestone') }}</h6>
                <ul>
                    @foreach ($production->milestones as $ms)
                        <div class="flex items-center justify-between gap-x-2">
                            <li class="w-full flex items-center gap-x-2 py-1 ms-row transition duration-300 hover:bg-slate-50"
                                data-id="{{ $ms->pivot->id }}" data-completed="{{ $ms->pivot->submitted_at }}"
                                data-completed-by="{{ $ms->pivot->submittedBy }}">
                                <svg class="h-5 w-5 fill-blue-500 not-completed-icon {{ $ms->pivot->submitted_at != null ? 'hidden' : '' }}"
                                    xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                    viewBox="0 0 24 24" width="512" height="512">
                                    <path
                                        d="M2.42,6.49c-.45-.32-.56-.94-.24-1.39,.8-1.13,1.78-2.11,2.91-2.91,.45-.32,1.08-.21,1.39,.24,.32,.45,.21,1.08-.24,1.39-.94,.67-1.76,1.48-2.43,2.43-.19,.28-.5,.42-.82,.42-.2,0-.4-.06-.58-.18Zm1.4,11.26c-.32-.45-.94-.56-1.39-.24-.45,.32-.56,.94-.24,1.39,.8,1.13,1.78,2.11,2.91,2.91,.17,.12,.38,.18,.58,.18,.31,0,.62-.15,.82-.42,.32-.45,.21-1.08-.24-1.39-.95-.67-1.76-1.48-2.43-2.43Zm-1.82-5.75c0-.61,.05-1.22,.16-1.81,.1-.54-.26-1.06-.8-1.16-.54-.1-1.06,.26-1.16,.8-.13,.71-.2,1.44-.2,2.17s.07,1.46,.2,2.17c.09,.48,.51,.82,.98,.82,.06,0,.12,0,.18-.02,.54-.1,.9-.62,.8-1.16-.11-.59-.16-1.2-.16-1.81Zm18.18,5.75c-.67,.95-1.48,1.76-2.43,2.43-.45,.32-.56,.94-.24,1.39,.19,.28,.5,.42,.82,.42,.2,0,.4-.06,.58-.18,1.13-.8,2.11-1.78,2.91-2.91,.32-.45,.21-1.08-.24-1.39-.45-.32-1.08-.21-1.39,.24Zm-6.37,4.09c-.59,.11-1.2,.16-1.81,.16s-1.22-.05-1.81-.16c-.54-.1-1.06,.26-1.16,.8-.1,.54,.26,1.06,.8,1.16,.71,.13,1.44,.2,2.17,.2s1.46-.07,2.17-.2c.54-.1,.9-.62,.8-1.16-.1-.54-.62-.91-1.16-.8ZM14.17,.2c-.71-.13-1.44-.2-2.17-.2s-1.46,.07-2.17,.2c-.54,.1-.9,.62-.8,1.16,.09,.48,.51,.82,.98,.82,.06,0,.12,0,.18-.02,.59-.11,1.2-.16,1.81-.16s1.22,.05,1.81,.16c.06,.01,.12,.02,.18,.02,.47,0,.89-.34,.98-.82,.1-.54-.26-1.06-.8-1.16Zm6.01,6.05c.19,.28,.5,.42,.82,.42,.2,0,.4-.06,.58-.18,.45-.32,.56-.94,.24-1.39-.8-1.13-1.78-2.11-2.91-2.91-.45-.32-1.08-.21-1.39,.24-.32,.45-.21,1.08,.24,1.39,.95,.67,1.76,1.48,2.43,2.43Zm3.62,3.58c-.1-.54-.62-.91-1.16-.8-.54,.1-.9,.62-.8,1.16,.11,.59,.16,1.2,.16,1.81s-.05,1.22-.16,1.81c-.1,.54,.26,1.06,.8,1.16,.06,.01,.12,.02,.18,.02,.47,0,.89-.34,.98-.82,.13-.71,.2-1.44,.2-2.17s-.07-1.46-.2-2.17Z" />
                                </svg>
                                <svg class="h-5 w-5 fill-green-500 completed-icon {{ $ms->pivot->submitted_at != null ? '' : 'hidden' }}"
                                    xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="m23.195,14.085l-2.159,2.157v1.722c0,1.654-1.346,3-3,3h-1.721l-2.158,2.157c-.565.566-1.319.879-2.121.879s-1.555-.312-2.121-.879l-2.157-2.157h-1.722c-1.654,0-3-1.346-3-3v-1.722l-2.157-2.157c-1.17-1.169-1.17-3.073,0-4.243l2.157-2.157v-1.721c0-1.654,1.346-3,3-3h1.722l2.157-2.158c1.134-1.135,3.111-1.132,4.243,0l2.157,2.157h1.721c.553,0,1,.448,1,1s-.447,1-1,1h-2.135c-.266,0-.52-.105-.707-.293l-2.451-2.451c-.377-.376-1.034-.378-1.414,0l-2.45,2.451c-.188.188-.441.293-.707.293h-2.136c-.552,0-1,.449-1,1v2.135c0,.265-.105.52-.293.707l-2.45,2.45c-.39.39-.39,1.024,0,1.415l2.45,2.45c.188.188.293.441.293.707v2.136c0,.552.448,1,1,1h2.136c.266,0,.52.105.707.293l2.45,2.45c.379.378,1.037.378,1.413,0l2.452-2.45c.188-.188.441-.293.707-.293h2.135c.552,0,1-.448,1-1v-2.136c0-.266.105-.52.293-.707l2.451-2.45c.39-.39.39-1.025,0-1.415-.39-.391-.39-1.024,0-1.414.391-.39,1.024-.391,1.415,0,1.168,1.169,1.168,3.072,0,4.242Zm-12.474-.423l-3.018-2.988c-.394-.39-1.025-.385-1.415.007-.389.392-.385,1.025.007,1.414l3.019,2.989c.614.608,1.422.913,2.229.913s1.617-.307,2.232-.918l8.93-8.871c.392-.389.394-1.022.004-1.414-.39-.392-1.022-.395-1.414-.005l-8.93,8.872c-.453.451-1.19.45-1.644,0Z" />
                                </svg>
                                <div class="flex items-center justify-between w-full">
                                    <span class="flex-1 text-md">{{ $ms->name }}</span>
                                    @if ($ms->pivot->submitted_at != null)
                                        <div class="flex flex-col items-end">
                                            <span class="text-xs text-slate-600">By {{ $ms->pivot->submittedBy }}</span>
                                            <span class="text-xs text-slate-600">{{ $ms->pivot->submitted_at }}</span>
                                        </div>
                                    @endif
                                </div>
                            </li>
                            @if (count($ms->pivot->rejects) > 0)
                                <x-app.button.button class="view-rejections-btns bg-transparent !p-0"
                                    title="{{ __('View Rejections') }}"
                                    data-production-milestone-id="{{ $ms->pivot->id }}"
                                    data-milestone-title="{{ $ms->name }}">
                                    <svg class="h-4 w-4" id="Layer_1" height="512" viewBox="0 0 24 24" width="512"
                                        xmlns="http://www.w3.org/2000/svg" data-name="Layer 1">
                                        <path d="m9 24h-8a1 1 0 0 1 0-2h8a1 1 0 0 1 0 2z" />
                                        <path d="m7 20h-6a1 1 0 0 1 0-2h6a1 1 0 0 1 0 2z" />
                                        <path d="m5 16h-4a1 1 0 0 1 0-2h4a1 1 0 0 1 0 2z" />
                                        <path
                                            d="m13 23.955a1 1 0 0 1 -.089-2 10 10 0 1 0 -10.87-10.865 1 1 0 0 1 -1.992-.18 12 12 0 0 1 23.951 1.09 11.934 11.934 0 0 1 -10.91 11.951c-.03.003-.061.004-.09.004z" />
                                        <path
                                            d="m12 6a1 1 0 0 0 -1 1v5a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414l-2.707-2.707v-4.586a1 1 0 0 0 -1-1z" />
                                    </svg>
                                </x-app.button.button>
                            @endif
                        </div>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="flex-1 pl-4 ml-4 border-l">
            <div class="rounded-lg">
                <h1 class="font-black text-xl text-blue-900">{{ __('Production ID') }}: {{ $production->sku }}</h1>
                @if (in_array(strtolower($production->status), ['doing']))
                    <x-app.button.button
                        class="font-semibold w-full justify-center mt-2 bg-transparent text-emerald-500 border border-emerald-500 transition duration-250 hover:bg-emerald-500 hover:text-white"
                        id="complete-task-btn">
                        {{ __('Complete Task') }}
                    </x-app.button.button>
                @endif
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Assigned') }}</h6>
                <ul>
                    @foreach ($production->users as $u)
                        <li class="flex items-center gap-x-4 my-2">
                            <div class="h-8 w-8 rounded-full border overflow-hidden">
                                @if (count($u->pictures) > 0)
                                    <img src="{{ $u->latest_picture->url }}" alt=""
                                        class="h-full w-full object-cover">
                                @else
                                    <img src="{{ asset('images/avatar.jpg') }}" alt="Avatar image"
                                        class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div>
                                <p class="flex-1 text-md leading-none font-semibold">{{ $u->name }}</p>
                                <span class="text-sm text-slate-500 leading-none">{{ join(', ', getUserRole($u)) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Remark') }}</h6>
                <p class="text-sm text-slate-500">{{ $production->remark ?? 'No Remark' }}</p>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Product') }}</h6>
                <p class="text-sm text-slate-500">{{ $production->product->model_name }}</p>
                @if ($production->productChild != null)
                    <div class="flex items-center mt-2">
                        <span class="text-sm font-semibold mr-1">Serial No: </span>
                        <span class="text-sm text-slate-500">{{ $production->productChild->sku ?? null }}</span>
                    </div>
                @endif
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('B.O.M Material Use') }}</h6>
                <ul>
                    @foreach ($material_use->materials as $material)
                        <li class="flex justify-between mt-0.5 hover:bg-slate-50">
                            <p class="text-sm text-slate-500">{{ $material->material->model_name }}</p>
                            <p class="text-sm text-slate-500 ml-2">x{{ $material->qty }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <x-app.modal.extend-due-date-modal :production="$production" />
    <x-app.modal.extend-due-date-history-modal :histories="$production->dueDates" />
    <x-app.modal.production-milestone-modal />
    <x-app.modal.qr-scanner-modal />
    <x-app.modal.confirmation-modal />
    <x-app.modal.milestone-rejections-modal />
@endsection

@push('scripts')
    <script>
        PRODUCTION = @json($production);
        PRODUCTION_MILESTONE_MATERIALS = @json($production_milestone_materials);
        SELECTED_PRODUCTION_MILESTONE_ID = null
        SPAREPART_KEYWORD = {} // productId: keyword

        $(document).ready(function() {
            if (PRODUCTION.status.toLowerCase() != 'doing') {
                $('#production-milestone-modal #yes-btn').attr('disabled', true)
                $('#production-milestone-modal #yes-btn').addClass(
                    'bg-slate-200 text-black hover:bg-slate-200 hover:text-black')
            }
        })

        $('.ms-row').on('click', function(e) {
            e.preventDefault()

            let id = $(this).data('id')
            SELECTED_PRODUCTION_MILESTONE_ID = id
            let completed = $(this).data('completed')
            let completedBy = $(this).data('completed-by')
            let milestoneCount = PRODUCTION.milestones.length

            $('.ms-row').each(function() {
                if ($(this).data('completed') != null && $(this).data['completed'] != '') {
                    milestoneCount--
                }
            })

            // Prepare milestone modal material's content
            let requiredSerialNo = rebuildMaterialTemplate(null, completed != null && completed != '')

            $('#production-milestone-modal #checked-in-by-container').addClass('hidden')
            if (completed != null && completed != '') {
                $('#production-milestone-modal #date').text(moment(completed).format('D MMM YYYY HH:mm'))
                $('#production-milestone-modal #checked-in-by-container').removeClass('hidden')
                $('#production-milestone-modal #checked-in-by').text(completedBy)
            } else {
                $('#production-milestone-modal #date').text(moment().format('D MMM YYYY HH:mm'))
            }
            $('#production-milestone-modal #yes-btn').attr('data-id', id)
            $('#production-milestone-modal #reject-btn').attr('data-id', id)
            if (requiredSerialNo) $('#production-milestone-modal #serial-no-container').removeClass('hidden')
            else $('#production-milestone-modal #serial-no-container').addClass('hidden')
            // Auto select materials needed
            if (PRODUCTION_MILESTONE_MATERIALS[id] !== undefined) {
                for (let i = 0; i < PRODUCTION_MILESTONE_MATERIALS[id].length; i++) {
                    const element = PRODUCTION_MILESTONE_MATERIALS[id][i];

                    $(`#production-milestone-modal #serial-no-container input[name="serial_no[]"][id="${element}"]`)
                        .attr('checked', true)
                }
            }
            // Completed
            $('#production-milestone-modal #reject-btn').addClass('hidden')
            if (completed) {
                if (requiredSerialNo && milestoneCount > 0) {
                    $('#production-milestone-modal #yes-btn').text('Update')
                    $('#production-milestone-modal #yes-btn').removeClass('hidden')
                } else {
                    $('#production-milestone-modal #reject-btn').removeClass('hidden')
                    $('#production-milestone-modal #yes-btn').addClass('hidden')
                }
                $('#production-milestone-modal #no-btn').text('Close')
            } else {
                $('#production-milestone-modal #yes-btn').text('Confirm')
                $('#production-milestone-modal #yes-btn').removeClass('hidden')
                $('#production-milestone-modal #no-btn').text('No')
            }

            $('#production-milestone-modal #general_err').addClass('hidden')
            $('#production-milestone-modal').addClass('show-modal')
        })

        $('body').on('keyup', '.filter-search', function() {
            let productId = $(this).attr('id')
            let keyword = $(this).val()
            if (keyword == '') keyword = null
            SPAREPART_KEYWORD[productId] = keyword

            rebuildMaterialTemplate(productId)
        })

        function rebuildMaterialTemplate(search_product_id = null, completed = false) {
            $('#serial-no-selection-container .selection').remove()

            let requiredSerialNo = false

            for (let i = 0; i < PRODUCTION.milestones.length; i++) {
                if (PRODUCTION.milestones[i].pivot.id == SELECTED_PRODUCTION_MILESTONE_ID && PRODUCTION.milestones[i]
                    .preview.length > 0) {
                    for (let j = 0; j < PRODUCTION.milestones[i].preview.length; j++) {
                        if (PRODUCTION.milestones[i].preview[j].product.is_sparepart == true) {
                            var productId = PRODUCTION.milestones[i].preview[j].product.id

                            var clone = $('#serial-no-container #sp-template')[0].cloneNode(true);

                            $(clone).removeAttr('id')
                            $(clone).removeClass('hidden')
                            $(clone).addClass('selection')
                            $(clone).find('#product-name').text(PRODUCTION.milestones[i].preview[j].product.model_name)
                            $(clone).find('.filter-search').attr('name', productId)
                            $(clone).find('.filter-search').attr('id', productId)
                            $(clone).find('.filter-search').val(SPAREPART_KEYWORD[productId])
                            $(clone).find('.scanner-btn').attr('data-product-id', productId)
                            $(clone).find('#qty-needed').text(
                                `Quantity needed: x${PRODUCTION.milestones[i].preview[j].qty}`)
                            $(clone).attr('data-product-id', productId)
                            $(clone).find('#materials_err').attr('data-product-id', productId)
                            $('#serial-no-selection-container').append(clone)

                            // Prepare serial no selection
                            let children = PRODUCTION.milestones[i].preview[j].children

                            let childIdsToHide = []
                            if (completed == false) {
                                for (const key in
                                        PRODUCTION_MILESTONE_MATERIALS) { // Hidden other milestone selected child
                                    childIdsToHide = childIdsToHide.concat(PRODUCTION_MILESTONE_MATERIALS[key])
                                }
                            }
                            for (let k = 0; k < children.length; k++) {
                                if (completed == true && !PRODUCTION_MILESTONE_MATERIALS[SELECTED_PRODUCTION_MILESTONE_ID]
                                    .includes(children[k].id)) {
                                    continue;
                                }
                                if (childIdsToHide.includes(children[k].id)) {
                                    continue
                                }
                                if (SPAREPART_KEYWORD[productId] != undefined && !children[k].sku.includes(
                                        SPAREPART_KEYWORD[productId])) {
                                    continue
                                }

                                let spClone = $(`#sp-template .sp-serial-no-container .sp-serial-no-template`)[0]
                                    .cloneNode(true);

                                $(spClone).data('id', children[k].id)
                                $(spClone).removeAttr('id')
                                $(spClone).removeClass('hidden')
                                $(spClone).find('.first-half input').attr('id', children[k].id)
                                $(spClone).find('.first-half input').attr('data-product-id', productId)
                                $(spClone).find('.first-half label').text(children[k].sku)
                                $(spClone).find('.first-half label').attr('for', children[k].id)

                                if (completed) {
                                    $(spClone).find('.first-half input').addClass('hidden')
                                    $(spClone).find('.second-half').attr('data-product-child-id', children[k].id)

                                    $(spClone).find('.second-half input').eq(0).attr('id', `reason1-${children[k].id}`)
                                    $(spClone).find('.second-half input').eq(0).attr('name', `reason-${children[k].id}`)
                                    $(spClone).find('.second-half label').eq(0).attr('for',
                                        `reason1-${children[k].id}`)
                                    $(spClone).find('.second-half input').eq(1).attr('id', `reason2-${children[k].id}`)
                                    $(spClone).find('.second-half input').eq(1).attr('name', `reason-${children[k].id}`)
                                    $(spClone).find('.second-half label').eq(1).attr('for',
                                        `reason2-${children[k].id}`)

                                    $(spClone).find('.second-half').removeClass('hidden')
                                } else {
                                    $(spClone).find('.second-half').addClass('hidden')
                                }

                                $(`.selection[data-product-id="${productId}"] .sp-serial-no-container`).append(
                                    spClone)
                            }
                            $(`.filter-search[id="${search_product_id}"]`).focus()
                        } else {
                            var clone = $('#serial-no-container #rm-template')[0].cloneNode(true);

                            $(clone).removeAttr('id')
                            $(clone).removeClass('hidden')
                            $(clone).addClass('selection')
                            $(clone).find('#product-name').text(PRODUCTION.milestones[i].preview[j].product.model_name)
                            $(clone).find('#qty-needed').text(
                                `Quantity needed: x${PRODUCTION.milestones[i].preview[j].qty}`)
                            $('#serial-no-selection-container').append(clone)
                        }

                        if ((j + 1) < PRODUCTION.milestones[i].preview.length) {
                            $(clone).addClass('pb-4 border-b')
                        }
                        requiredSerialNo = true
                    }
                    break
                }
            }
            return requiredSerialNo
        }
        // Extend Due Date
        $('#extend-due-date-btn').on('click', function() {
            $('#extend-due-date-modal').addClass('show-modal')
        })
        $('#extend-due-date-history-btn').on('click', function() {
            $('#extend-due-date-history-modal').addClass('show-modal')
        })
        // Confirm Task
        $('#complete-task-btn').on('click', function() {
            $('#confirmation-modal #msg').text(
                '{{ __('Are you sure to complete the production without completing all the milestones?') }}')
            $('#confirmation-modal').addClass('show-modal')
        })
        $('#confirmation-modal #yes-btn').on('click', function() {
            let url = '{{ config('app.url') }}'
            url = `${url}/production/force-complete-task/${PRODUCTION.id}`

            window.location.href = url
        })
        // View rejections
        $('.view-rejections-btns').on('click', function() {
            let productionMilestoneId = $(this).data('production-milestone-id')
            let ms = $(this).data('milestone-title')

            $('#milestone-rejections-modal #record-container .records').remove()

            for (let i = 0; i < PRODUCTION.milestones.length; i++) {
                if (PRODUCTION.milestones[i].pivot.id == productionMilestoneId) {
                    for (let j = 0; j < PRODUCTION.milestones[i].pivot.rejects.length; j++) {
                        var clone = $('#milestone-rejections-modal #record-template')[0].cloneNode(true);

                        // Record
                        $(clone).find('#rejected-by').text(PRODUCTION.milestones[i].pivot.rejects[j].rejected_by
                            .name)
                        $(clone).find('#submitted-by').text(PRODUCTION.milestones[i].pivot.rejects[j].submitted_by
                            .name)
                        $(clone).find('#submitted-at').text(PRODUCTION.milestones[i].pivot.rejects[j].submitted_at)
                        $(clone).addClass('records')
                        $(clone).removeClass('hidden')
                        // Materials
                        for (let k = 0; k < PRODUCTION.milestones[i].pivot.rejects[j].milestone_materials
                            .length; k++) {
                            if (PRODUCTION.milestones[i].pivot.rejects[j].milestone_materials[k].product ==
                                null) { // Spare part
                                var spChildTemplate = $(clone).find('#sp-child-template')[0].cloneNode(true)

                                $(spChildTemplate).find('#sku').text(PRODUCTION.milestones[i].pivot.rejects[j]
                                    .milestone_materials[k].product_child.sku)
                                $(spChildTemplate).find('#reason').text(PRODUCTION.milestones[i].pivot.rejects[j]
                                    .milestone_materials[k].reject_reason.replace('-', ' '))

                                $(clone).find('#sp-child-container').append(spChildTemplate)

                                if (k == 0) {
                                    var spTemplate = $(clone).find('#sp-template')[0].cloneNode(true)
                                    $(spTemplate).find('#product').text(PRODUCTION.milestones[i].pivot.rejects[j]
                                        .milestone_materials[k].product_child.parent.model_name)
                                    $(spTemplate).removeClass('hidden')
                                    $(clone).find('#material-container').append(spTemplate)
                                }
                            } else { // Raw material
                                var rmTemplate = $(clone).find('#rm-template')[0].cloneNode(true)

                                $(rmTemplate).find('#product').text(PRODUCTION.milestones[i].pivot.rejects[j]
                                    .milestone_materials[k].product.model_name)
                                $(rmTemplate).find('#qty').text(
                                    `x${PRODUCTION.milestones[i].pivot.rejects[j].milestone_materials[k].qty}`)

                                $(rmTemplate).removeClass('hidden')
                                $(clone).find('#material-container').append(rmTemplate)
                            }
                        }

                        $('#milestone-rejections-modal #record-container').append(clone)
                    }
                    break
                }
            }

            $('#milestone-rejections-modal #milestone').text(ms)
            $('#milestone-rejections-modal').addClass('show-modal')
        })
    </script>
@endpush
