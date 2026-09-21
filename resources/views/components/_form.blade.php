@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="code" class="form-label">Code</label>
    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $component->code ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $component->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="unit_of_measure" class="form-label">Unit of Measure</label>
    <input type="text" name="unit_of_measure" id="unit_of_measure" class="form-control" placeholder="e.g. pcs, meters, rolls"
        value="{{ old('unit_of_measure', $component->unit_of_measure ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="safety_stock_threshold" class="form-label">Safety Stock Threshold</label>
    <input type="number" step="0.01" min="0" name="safety_stock_threshold" id="safety_stock_threshold" class="form-control"
        value="{{ old('safety_stock_threshold', $component->safety_stock_threshold ?? 0) }}" required>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1"
        {{ old('is_active', $component->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Active</label>
</div>
