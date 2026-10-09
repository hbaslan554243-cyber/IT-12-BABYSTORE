@php $s = $supplier ?? null; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Supplier Name</label>
        <input type="text" name="name" value="{{ old('name', $s->name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Contact Person</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $s->contact_person ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $s->phone ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Facebook Handle</label>
        <input type="text" name="facebook_handle" value="{{ old('facebook_handle', $s->facebook_handle ?? '') }}" class="form-control" placeholder="e.g. fb.com/supplierpage">
    </div>
    <div class="col-md-4">
        <label class="form-label">Address</label>
        <input type="text" name="address" value="{{ old('address', $s->address ?? '') }}" class="form-control">
    </div>
</div>
