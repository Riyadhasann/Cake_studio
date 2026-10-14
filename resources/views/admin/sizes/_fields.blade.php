{{-- Shared by the add form and the edit page. $size is null when adding. --}}
<div>
    <label for="label" class="label">Label</label>
    <input id="label" type="text" name="label" value="{{ old('label', $size?->label) }}" required maxlength="50" placeholder="e.g. 1.5 kg" class="field">
</div>

<div>
    <label for="weight_kg" class="label">Weight (kg)</label>
    <input id="weight_kg" type="number" name="weight_kg" value="{{ old('weight_kg', $size?->weight_kg) }}" required min="0.1" max="100" step="0.01" class="field">
</div>

<div>
    <label for="price" class="label">Price (৳)</label>
    <input id="price" type="number" name="price" value="{{ old('price', $size?->price) }}" required min="0" max="999999.99" step="0.01" class="field">
</div>
