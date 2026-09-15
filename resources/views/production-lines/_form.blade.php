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
    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $productionLine->code ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $productionLine->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="location" class="form-label">Location</label>
    <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $productionLine->location ?? '') }}">
</div>

<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1"
        {{ old('is_active', $productionLine->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Active</label>
</div>