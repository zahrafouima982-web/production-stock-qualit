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
    <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $product->code ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1"
        {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Active</label>
</div>