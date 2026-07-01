@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-bold text-slate-900">申込一覧</h2>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 w-full sm:w-auto">
            <button
                type="button"
                id="customer-info-button"
                class="hidden inline-flex items-center justify-center rounded-md bg-[#5383c3] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90 transition-opacity shrink-0"
            >
                顧客情報入力
            </button>
            <x-admin-search-form :value="$search" />
        </div>
    </div>

    @if ($applications->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center text-slate-500">
            @if ($search !== '')
                「{{ $search }}」に一致する申込データがありません。
            @else
                表示する申込データがありません。
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="admin-table-scroll overflow-x-auto">
                <table class="admin-table-sticky admin-fixed-table text-sm text-left" data-sticky-cols="4">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="sticky-col w-[130px] px-3 py-3 font-medium whitespace-nowrap">作成日時</th>
                            <th class="sticky-col w-[90px] px-3 py-3 font-medium whitespace-nowrap">担当者</th>
                            <th class="sticky-col w-[140px] px-3 py-3 font-medium whitespace-nowrap">物件名</th>
                            <th class="sticky-col sticky-col-last w-[90px] px-3 py-3 font-medium whitespace-nowrap">部屋番号</th>
                            <th class="w-[110px] px-3 py-3 font-medium whitespace-nowrap">入居予定日</th>
                            <th class="w-[140px] px-3 py-3 font-medium whitespace-nowrap">管理会社名</th>
                            <th class="w-[180px] px-3 py-3 font-medium whitespace-nowrap">状況</th>
                            <th class="w-[180px] px-3 py-3 font-medium whitespace-nowrap">備考</th>
                            <th class="w-[90px] px-3 py-3 font-medium whitespace-nowrap text-center">営業要対応</th>
                            <th class="w-[80px] px-3 py-3 font-medium whitespace-nowrap text-center">審査ＯＫ</th>
                            <th class="w-[80px] px-3 py-3 font-medium whitespace-nowrap text-center">キャンセル</th>
                            <th class="w-[100px] px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['move_in_date'] }}</th>
                            <th class="w-[120px] px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['document_deadline'] }}</th>
                            <th class="w-[120px] px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['scheduled_visit_date'] }}</th>
                            <th class="w-[110px] px-3 py-3 font-medium whitespace-nowrap">{{ $columnLabels['key_handover_date'] }}</th>
                            @foreach ($booleanFields as $field)
                                @if (in_array($field, ['settlement_transition', 'has_broker_fee'], true))
                                    @continue
                                @endif
                                @if ($field === 'transfer_request_to_applicant')
                                    <th class="w-[110px] px-3 py-3 font-medium whitespace-nowrap">
                                        <div>{{ $columnLabels['ad_fee_invoice_creation'] }}</div>
                                        <div class="text-xs font-normal text-slate-500 mt-0.5">済か不要と入力</div>
                                    </th>
                                @endif
                                <th class="w-[100px] px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels[$field] }}</th>
                            @endforeach
                            <th class="w-[110px] px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['has_broker_fee'] }}</th>
                            <th class="w-[120px] px-3 py-3 font-medium whitespace-nowrap text-center">{{ $columnLabels['settlement_transition'] }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($applications as $application)
                            @php
                                $flowManagement = $application->flowManagement;
                                $flowEditable = $application->screening_ok;
                            @endphp
                            <tr
                                class="align-top bg-white hover:bg-slate-50 transition-colors {{ $application->screening_ok ? 'has-sticky-highlight-blue' : '' }}"
                                data-application-id="{{ $application->id }}"
                                data-screening-ok="{{ $application->screening_ok ? '1' : '0' }}"
                                data-customer-info-completed="{{ $application->customer?->customer_info_completed ? '1' : '0' }}"
                                data-flow-management-id="{{ $flowManagement?->id }}"
                                data-flow-editable="{{ $flowEditable ? '1' : '0' }}"
                            >
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">
                                    <div>{{ $application->created_at->format('Y/m/d H:i') }}</div>
                                    @if ($application->customer?->customer_info_completed)
                                        <div class="customer-info-star mt-1 text-amber-500 text-sm leading-none" title="顧客情報入力済み">★</div>
                                    @endif
                                </td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $application->staff_in_charge }}</td>
                                <td class="sticky-col px-3 py-3 whitespace-nowrap">{{ $application->property_name ?? '—' }}</td>
                                <td class="sticky-col sticky-col-last px-3 py-3 whitespace-nowrap">{{ $application->room_number ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->scheduled_move_in_date?->format('Y/m/d') ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $application->management_company_name }}</td>
                                <td class="px-3 py-3 max-w-[200px] whitespace-pre-line">{{ $application->status }}</td>
                                <td class="px-3 py-3 min-w-[180px]">
                                    <textarea
                                        class="application-memo-field w-full min-h-[2.5rem] rounded border border-slate-200 px-2 py-1 text-sm resize-y focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        rows="2"
                                        maxlength="2000"
                                        placeholder="備考を入力"
                                    >{{ $application->memo }}</textarea>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <input
                                        type="checkbox"
                                        class="flag-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="sales_action_required"
                                        @checked($application->sales_action_required)
                                    >
                                </td>
                                <td class="px-3 py-3 text-center exclusive-cell" data-exclusive-for="screening_ok">
                                    <input
                                        type="checkbox"
                                        class="flag-checkbox screening-ok-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="screening_ok"
                                        @checked($application->screening_ok)
                                    >
                                </td>
                                <td class="px-3 py-3 text-center exclusive-cell" data-exclusive-for="is_cancelled">
                                    <input
                                        type="checkbox"
                                        class="flag-checkbox cancel-checkbox h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                        data-field="is_cancelled"
                                        @checked($application->is_cancelled)
                                        @disabled($application->screening_ok)
                                    >
                                </td>
                                @include('admin.partials.flow-management-cells', [
                                    'flowManagement' => $flowManagement,
                                    'flowEditable' => $flowEditable,
                                    'booleanFields' => $booleanFields,
                                    'columnLabels' => $columnLabels,
                                ])
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($applications->hasPages())
            <div class="mt-6 pb-2">
                {{ $applications->links('vendor.pagination.admin') }}
            </div>
        @endif
    @endif

    @include('admin.partials.customer-form-modal')
@endsection

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        #customer-info-modal .flatpickr-calendar {
            border-radius: 0.75rem;
            border-color: rgb(226 232 240);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        }
        #customer-info-modal .flatpickr-day.selected,
        #customer-info-modal .flatpickr-day.selected:hover {
            background: #5383c3;
            border-color: #5383c3;
        }

        .application-row-cancel-fade {
            pointer-events: none;
            animation: application-row-cancel-fade 0.5s ease-out forwards;
        }

        .application-row-cancel-fade td {
            background-color: #e2e8f0 !important;
        }

        @keyframes application-row-cancel-fade {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .application-row-cancel-fade {
                animation: none;
                opacity: 0;
            }
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
    const exclusiveMessages = {
        screening_ok: 'キャンセルが選択されているため、審査ＯＫは設定できません。先にキャンセルのチェックを外してください。',
        is_cancelled: '審査ＯＫが選択されているため、キャンセルは設定できません。先に審査ＯＫのチェックを外してください。',
    };

    function isFlowEditable(row) {
        return row.dataset.flowEditable === '1';
    }

    function updateFlowCellState(checkbox) {
        const cell = checkbox.closest('.flow-check-cell');
        if (!cell) {
            return;
        }

        cell.classList.toggle('admin-highlight-bg', checkbox.checked && isFlowEditable(checkbox.closest('tr')));
    }

    function updateFlowSectionState(row) {
        const screeningOk = row.querySelector('.screening-ok-checkbox').checked;
        row.dataset.flowEditable = screeningOk ? '1' : '0';

        row.querySelectorAll('.flow-section-cell').forEach((cell) => {
            cell.classList.toggle('flow-section-disabled', !screeningOk);
            cell.querySelectorAll('input, textarea').forEach((field) => {
                field.disabled = !screeningOk;
            });
        });

        row.querySelectorAll('.flow-field-checkbox').forEach((checkbox) => {
            updateFlowCellState(checkbox);
        });
    }

    function updateRowState(row) {
        const screeningOk = row.querySelector('.screening-ok-checkbox');
        const cancel = row.querySelector('.cancel-checkbox');

        row.classList.remove('has-sticky-highlight-blue');
        row.dataset.screeningOk = screeningOk.checked ? '1' : '0';

        if (screeningOk.checked) {
            row.classList.add('has-sticky-highlight-blue');
        }

        cancel.disabled = screeningOk.checked;

        updateFlowSectionState(row);
        updateCustomerInfoButton();

        if (typeof window.refreshAdminStickyColumns === 'function') {
            window.refreshAdminStickyColumns();
        }
    }

    function getSelectedApplicationRow() {
        return document.querySelector('tbody tr.is-row-selected');
    }

    function updateCustomerInfoButton() {
        const button = document.getElementById('customer-info-button');
        if (!button) {
            return;
        }

        const selectedRow = getSelectedApplicationRow();
        const show = selectedRow && selectedRow.dataset.screeningOk === '1';

        button.classList.toggle('hidden', !show);

        if (show) {
            button.dataset.applicationId = selectedRow.dataset.applicationId;
        } else {
            delete button.dataset.applicationId;
        }
    }

    const fadeOutCancelledRow = (row) => new Promise((resolve) => {
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reducedMotion) {
            row.remove();
            resolve();
            return;
        }

        row.classList.add('application-row-cancel-fade');

        const finish = () => {
            if (row.isConnected) {
                row.remove();
            }
            resolve();
        };

        row.addEventListener('animationend', finish, { once: true });
        setTimeout(finish, 550);
    });

    function markCustomerInfoCompleted(row) {
        if (!row) {
            return;
        }

        row.dataset.customerInfoCompleted = '1';

        const createdAtCell = row.querySelector('.sticky-col');
        if (!createdAtCell || createdAtCell.querySelector('.customer-info-star')) {
            return;
        }

        const star = document.createElement('div');
        star.className = 'customer-info-star mt-1 text-amber-500 text-sm leading-none';
        star.title = '顧客情報入力済み';
        star.textContent = '★';
        createdAtCell.appendChild(star);
    }

    document.addEventListener('admin-row-selection-changed', updateCustomerInfoButton);

    document.querySelectorAll('tbody tr').forEach((row) => {
        updateRowState(row);

        row.querySelectorAll('.exclusive-cell').forEach((cell) => {
            cell.addEventListener('click', (event) => {
                const checkbox = cell.querySelector('.flag-checkbox');
                if (checkbox.disabled) {
                    event.preventDefault();
                    alert(exclusiveMessages[checkbox.dataset.field]);
                }
            });
        });
    });

    updateCustomerInfoButton();

    document.querySelectorAll('.flag-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', async (event) => {
            const target = event.target;
            const row = target.closest('tr');
            const applicationId = row.dataset.applicationId;
            const field = target.dataset.field;
            const previousChecked = !target.checked;

            const screeningOk = row.querySelector('.screening-ok-checkbox');
            const cancel = row.querySelector('.cancel-checkbox');

            if (target.checked) {
                if (field === 'screening_ok' && cancel.checked) {
                    target.checked = false;
                    alert(exclusiveMessages.screening_ok);
                    return;
                }
                if (field === 'is_cancelled' && screeningOk.checked) {
                    target.checked = false;
                    alert(exclusiveMessages.is_cancelled);
                    return;
                }
            }

            if (field === 'screening_ok' && previousChecked) {
                target.checked = true;
                updateRowState(row);

                const confirmed = await window.confirmUncheckTransition();
                if (!confirmed) {
                    return;
                }

                target.checked = false;
                updateRowState(row);
            }

            if (field === 'is_cancelled' && target.checked) {
                target.checked = false;

                const confirmed = await window.confirmUncheckTransition('本当にキャンセルでよろしいですか？');
                if (!confirmed) {
                    return;
                }

                const saveCancelled = fetch(adminApiUrl(`/admin/applications/${applicationId}/flags`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: 'is_cancelled', value: 1 }),
                }).then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'キャンセルの保存に失敗しました。');
                    }
                    return data;
                });

                await fadeOutCancelledRow(row);
                updateCustomerInfoButton();

                try {
                    await saveCancelled;
                } catch (error) {
                    alert(error.message || 'キャンセルの保存に失敗しました。ページを再読み込みしてください。');
                    window.location.reload();
                }

                return;
            }

            const value = target.checked ? 1 : 0;

            try {
                const response = await fetch(adminApiUrl(`/admin/applications/${applicationId}/flags`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field, value }),
                });

                const data = await response.json();

                if (!response.ok) {
                    target.checked = previousChecked;
                    alert(data.message || '更新に失敗しました。もう一度お試しください。');
                    updateRowState(row);
                    return;
                }

                if (typeof data.screening_ok === 'boolean') {
                    screeningOk.checked = data.screening_ok;
                }
                if (typeof data.is_cancelled === 'boolean') {
                    cancel.checked = data.is_cancelled;
                }

                if (field === 'is_cancelled' && data.is_cancelled) {
                    row.remove();
                    updateCustomerInfoButton();
                    return;
                }

                if (field === 'screening_ok' && data.flow_management_id) {
                    row.dataset.flowManagementId = data.flow_management_id;
                }

                updateRowState(row);
            } catch (error) {
                target.checked = previousChecked;
                alert('更新に失敗しました。もう一度お試しください。');
                updateRowState(row);
            }
        });
    });

    function getFlowManagementId(row) {
        const id = row.dataset.flowManagementId;
        return id ? id : null;
    }

    document.querySelectorAll('.flow-field-checkbox').forEach((checkbox) => {
        updateFlowCellState(checkbox);

        checkbox.addEventListener('change', async (event) => {
            const target = event.target;
            const row = target.closest('tr');

            if (!isFlowEditable(row)) {
                target.checked = !target.checked;
                return;
            }

            const flowManagementId = getFlowManagementId(row);
            if (!flowManagementId) {
                target.checked = !target.checked;
                alert('フロー管理データの準備中です。ページを再読み込みしてください。');
                return;
            }

            const field = target.dataset.field;
            const previousChecked = !target.checked;
            const value = target.checked ? 1 : 0;

            if (field === 'settlement_transition' && previousChecked) {
                target.checked = true;
                updateFlowCellState(target);

                const confirmed = await window.confirmUncheckTransition();
                if (!confirmed) {
                    return;
                }

                target.checked = false;
            }

            updateFlowCellState(target);

            try {
                const response = await fetch(adminApiUrl(`/admin/flow-managements/${flowManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field, value }),
                });

                const data = await response.json();

                if (!response.ok) {
                    target.checked = previousChecked;
                    updateFlowCellState(target);
                    alert(data.message || '更新に失敗しました。もう一度お試しください。');
                }
            } catch (error) {
                target.checked = previousChecked;
                updateFlowCellState(target);
                alert('更新に失敗しました。もう一度お試しください。');
            }
        });
    });

    document.querySelectorAll('.flow-inline-text-field').forEach((field) => {
        let previousValue = field.value;
        let saveTimer = null;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        const saveField = async () => {
            const row = field.closest('tr');
            if (!isFlowEditable(row)) {
                return;
            }

            const flowManagementId = getFlowManagementId(row);
            if (!flowManagementId) {
                return;
            }

            const value = field.value;
            if (value === previousValue) {
                return;
            }

            try {
                const response = await fetch(adminApiUrl(`/admin/flow-managements/${flowManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: fieldName, value }),
                });

                if (!response.ok) {
                    field.value = previousValue;
                    const data = await response.json();
                    alert(data.message || `${fieldLabel}の保存に失敗しました。`);
                    return;
                }

                previousValue = value;
            } catch (error) {
                field.value = previousValue;
                alert(`${fieldLabel}の保存に失敗しました。`);
            }
        };

        field.addEventListener('blur', saveField);
        field.addEventListener('input', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveField, 800);
        });
    });

    document.querySelectorAll('.flow-date-field').forEach((field) => {
        let previousValue = field.value;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        const saveField = async () => {
            const row = field.closest('tr');
            if (!isFlowEditable(row)) {
                return;
            }

            const flowManagementId = getFlowManagementId(row);
            if (!flowManagementId) {
                return;
            }

            const value = field.value;
            if (value === previousValue) {
                return;
            }

            try {
                const response = await fetch(adminApiUrl(`/admin/flow-managements/${flowManagementId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: fieldName, value: value || null }),
                });

                if (!response.ok) {
                    field.value = previousValue;
                    const data = await response.json();
                    alert(data.message || `${fieldLabel}の保存に失敗しました。`);
                    return;
                }

                previousValue = value;
            } catch (error) {
                field.value = previousValue;
                alert(`${fieldLabel}の保存に失敗しました。`);
            }
        };

        field.addEventListener('change', saveField);
    });

    document.querySelectorAll('.application-memo-field').forEach((textarea) => {
        let previousValue = textarea.value;
        let saveTimer = null;

        const saveMemo = async () => {
            const row = textarea.closest('tr');
            const applicationId = row.dataset.applicationId;
            const value = textarea.value;

            if (value === previousValue) {
                return;
            }

            try {
                const response = await fetch(adminApiUrl(`/admin/applications/${applicationId}/fields`), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ field: 'memo', value: value || null }),
                });

                if (!response.ok) {
                    textarea.value = previousValue;
                    const data = await response.json();
                    alert(data.message || '備考の保存に失敗しました。');
                    return;
                }

                previousValue = value;
            } catch (error) {
                textarea.value = previousValue;
                alert('備考の保存に失敗しました。');
            }
        };

        textarea.addEventListener('blur', saveMemo);
        textarea.addEventListener('input', () => {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveMemo, 800);
        });
    });

    const customerInfoModal = document.getElementById('customer-info-modal');
    const customerInfoForm = document.getElementById('customer-info-form');
    const customerInfoButton = document.getElementById('customer-info-button');
    const customerInfoErrors = document.getElementById('customer-info-errors');
    const customerInfoCaseNumber = document.getElementById('customer-info-case-number');
    const customerInfoBooleanFields = ['contract_period_type', 'is_married'];
    const customerInfoCancelButton = document.getElementById('customer-info-modal-cancel');
    const customerInfoSaveButton = document.getElementById('customer-info-modal-save');
    let activeCustomerApplicationId = null;
    let customerInfoDatePickers = [];

    const invertCustomerInfoFlatpickrSpinnerArrows = (instance) => {
        const calendar = instance.calendarContainer;
        if (!calendar) {
            return;
        }

        calendar.querySelectorAll('.numInputWrapper').forEach((wrapper) => {
            if (wrapper.dataset.arrowsInverted) {
                return;
            }

            const up = wrapper.querySelector('.arrowUp');
            const down = wrapper.querySelector('.arrowDown');
            if (!up || !down) {
                return;
            }

            up.classList.remove('arrowUp');
            up.classList.add('arrowDown');
            down.classList.remove('arrowDown');
            down.classList.add('arrowUp');
            wrapper.dataset.arrowsInverted = '1';
        });
    };

    const destroyCustomerInfoDatePickers = () => {
        customerInfoDatePickers.forEach((instance) => instance.destroy());
        customerInfoDatePickers = [];
    };

    const initCustomerInfoDatePickers = () => {
        destroyCustomerInfoDatePickers();

        if (!customerInfoForm || typeof flatpickr === 'undefined') {
            return;
        }

        flatpickr.localize(flatpickr.l10ns.ja);

        customerInfoForm.querySelectorAll('[data-date-picker]').forEach((element) => {
            const instance = flatpickr(element, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'Y/m/d',
                allowInput: true,
                monthSelectorType: 'dropdown',
                disableMobile: true,
                maxDate: element.dataset.dateMax === 'today' ? 'today' : undefined,
                onReady(_selectedDates, _dateStr, fp) {
                    if (fp.altInput) {
                        fp.altInput.id = fp.input.id;
                        fp.input.removeAttribute('id');
                    }

                    invertCustomerInfoFlatpickrSpinnerArrows(fp);
                },
                onOpen(_selectedDates, _dateStr, fp) {
                    invertCustomerInfoFlatpickrSpinnerArrows(fp);
                },
            });

            customerInfoDatePickers.push(instance);
        });
    };

    const closeCustomerInfoModal = () => {
        if (!customerInfoModal) {
            return;
        }

        destroyCustomerInfoDatePickers();
        customerInfoModal.classList.add('hidden');
        customerInfoModal.classList.remove('flex');
        activeCustomerApplicationId = null;
        customerInfoForm?.reset();
        customerInfoErrors?.classList.add('hidden');
        customerInfoErrors.textContent = '';
    };

    const populateCustomerInfoForm = (formValues) => {
        if (!customerInfoForm || !formValues) {
            return;
        }

        Object.entries(formValues).forEach(([field, value]) => {
            const input = customerInfoForm.elements.namedItem(field);
            if (!input) {
                return;
            }

            if (customerInfoBooleanFields.includes(field)) {
                input.value = value === true || value === 1 || value === '1'
                    ? '1'
                    : (value === false || value === 0 || value === '0' ? '0' : '');
                return;
            }

            input.value = value ?? '';
        });
    };

    const openCustomerInfoModal = async () => {
        const applicationId = customerInfoButton?.dataset.applicationId;
        if (!applicationId || !customerInfoModal || !customerInfoForm) {
            return;
        }

        customerInfoErrors.classList.add('hidden');
        customerInfoErrors.textContent = '';
        customerInfoSaveButton.disabled = true;

        try {
            const response = await fetch(adminApiUrl(`/admin/applications/${applicationId}/customer`), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            if (!response.ok) {
                alert(data.message || '顧客情報の取得に失敗しました。');
                return;
            }

            activeCustomerApplicationId = applicationId;
            customerInfoCaseNumber.textContent = data.customer.case_number ?? '未採番';
            customerInfoForm.reset();
            populateCustomerInfoForm(data.form);

            customerInfoModal.classList.remove('hidden');
            customerInfoModal.classList.add('flex');
            initCustomerInfoDatePickers();
        } catch (error) {
            alert('顧客情報の取得に失敗しました。');
        } finally {
            customerInfoSaveButton.disabled = false;
        }
    };

    customerInfoButton?.addEventListener('click', openCustomerInfoModal);
    customerInfoCancelButton?.addEventListener('click', closeCustomerInfoModal);

    customerInfoForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!activeCustomerApplicationId) {
            return;
        }

        const formData = new FormData(customerInfoForm);
        const payload = Object.fromEntries(formData.entries());

        customerInfoErrors.classList.add('hidden');
        customerInfoErrors.textContent = '';
        customerInfoSaveButton.disabled = true;

        try {
            const response = await fetch(adminApiUrl(`/admin/applications/${activeCustomerApplicationId}/customer`), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    const messages = Object.values(data.errors).flat();
                    customerInfoErrors.innerHTML = messages.map((message) => `<p>${message}</p>`).join('');
                    customerInfoErrors.classList.remove('hidden');
                    return;
                }

                alert(data.message || '顧客情報の保存に失敗しました。');
                return;
            }

            closeCustomerInfoModal();
            markCustomerInfoCompleted(getSelectedApplicationRow());
            alert(data.message || '顧客情報を保存しました。');
        } catch (error) {
            alert('顧客情報の保存に失敗しました。');
        } finally {
            customerInfoSaveButton.disabled = false;
        }
    });
</script>
@endpush
