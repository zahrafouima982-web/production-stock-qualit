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
    <label for="produced_quantity" class="form-label">Produced Quantity</label>
    <input type="number" min="1" name="produced_quantity" id="produced_quantity" class="form-control"
        value="{{ old('produced_quantity', $record->produced_quantity ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="shift" class="form-label">Shift</label>
    <input type="text" name="shift" id="shift" class="form-control" placeholder="e.g. Morning, Afternoon, Night"
        value="{{ old('shift', $record->shift ?? '') }}">
</div>

<div class="mb-3">
    <label for="recorded_at" class="form-label">Recorded At</label>
    <input type="datetime-local" name="recorded_at" id="recorded_at" class="form-control"
        value="{{ old('recorded_at', isset($record) ? $record->recorded_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
</div>