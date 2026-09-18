@extends('layouts.app')

@section('title', 'New Quality Inspection')

@section('content')
    <h2 class="fw-bold mb-4">New Quality Inspection</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('quality.inspections.store') }}" class="card p-4" style="max-width: 720px;" id="inspection-form">
        @csrf

        <div class="mb-3">
            <label for="production_record_id" class="form-label">Production Record</label>
            <select name="production_record_id" id="production_record_id" class="form-select" required>
                <option value="">Select a production record...</option>
                @foreach ($productionRecords as $record)
                    <option value="{{ $record->id }}" @selected(old('production_record_id', $preselectedRecordId ?? '') == $record->id)>
                        Order {{ $record->productionOrder->order_number }} &mdash; {{ $record->productionOrder->product->name }}
                        &mdash; {{ $record->produced_quantity }} units ({{ $record->recorded_at->format('Y-m-d') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="inspected_at" class="form-label">Inspected At</label>
            <input type="datetime-local" name="inspected_at" id="inspected_at" class="form-control"
                value="{{ old('inspected_at', now()->format('Y-m-d\TH:i')) }}">
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label mb-0">Inspection Items</label>
            <button type="button" class="btn btn-sm btn-outline-dark" id="add-item">+ Add Criterion</button>
        </div>

        <div id="items-container"></div>

        <template id="item-template">
            <div class="row g-2 mb-2 item-row">
                <div class="col-md-5">
                    <input type="text" name="items[__INDEX__][criterion_name]" class="form-control" placeholder="Criterion (e.g. Continuity Test)" required>
                </div>
                <div class="col-md-3">
                    <select name="items[__INDEX__][result]" class="form-select" required>
                        <option value="PASS">PASS</option>
                        <option value="FAIL">FAIL</option>
                        <option value="N_A">N/A</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="items[__INDEX__][remarks]" class="form-control" placeholder="Remarks (optional)">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger remove-item">&times;</button>
                </div>
            </div>
        </template>

        <button type="submit" class="btn btn-dark mt-3">Save Inspection</button>
    </form>

    <script>
        (function () {
            const container = document.getElementById('items-container');
            const template = document.getElementById('item-template');
            let index = 0;

            function addItem() {
                const html = template.innerHTML.replaceAll('__INDEX__', index);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                container.appendChild(wrapper.firstElementChild);
                index++;
            }

            container.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-item')) {
                    e.target.closest('.item-row').remove();
                }
            });

            document.getElementById('add-item').addEventListener('click', addItem);

            // Start with one row so the form isn't empty.
            addItem();
        })();
    </script>
@endsection