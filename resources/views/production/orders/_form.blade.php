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
    <label for="product_id" class="form-label">Product</label>
    <select name="product_id" id="product_id" class="form-select" required>
        <option value="">Select a product...</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}" @selected(old('product_id', $order->product_id ?? '') == $product->id)>
                {{ $product->name }} ({{ $product->code }})
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="production_line_id" class="form-label">Production Line</label>
    <select name="production_line_id" id="production_line_id" class="form-select" required>
        <option value="">Select a line...</option>
        @foreach ($productionLines as $line)
            <option value="{{ $line->id }}" @selected(old('production_line_id', $order->production_line_id ?? '') == $line->id)>
                {{ $line->name }} ({{ $line->code }})
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="planned_quantity" class="form-label">Planned Quantity</label>
    <input type="number" min="1" name="planned_quantity" id="planned_quantity" class="form-control"
        value="{{ old('planned_quantity', $order->planned_quantity ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="start_date" class="form-label">Start Date</label>
    <input type="date" name="start_date" id="start_date" class="form-control"
        value="{{ old('start_date', isset($order) ? $order->start_date?->format('Y-m-d') : '') }}">
</div>