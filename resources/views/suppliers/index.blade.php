@extends('layouts.app')
@section('title', '')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Suppliers</h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#archivedSuppliersModal">
                Archived <span class="badge bg-secondary">{{ $archivedSuppliers->count() }}</span>
            </button>
            @if(auth()->user()->isOwner())
                <a href="{{ route('suppliers.create') }}" class="btn btn-danger">+ Add Supplier</a>
            @endif
        </div>
    </div>

    <table class="table table-bordered bg-white">
        <thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Facebook</th><th>Address</th>@if(auth()->user()->isOwner())<th></th>@endif</tr></thead>
        <tbody>
        @forelse($suppliers as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->contact_person }}</td>
                <td>{{ $s->phone }}</td>
                <td>{{ $s->facebook_handle }}</td>
                <td>{{ $s->address }}</td>
                @if(auth()->user()->isOwner())
                <td class="d-flex gap-2">
                    <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route('suppliers.archive', $s) }}" method="POST" onsubmit="return confirm('Archive this supplier? Existing purchase order history will be kept.')">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Archive</button>
                    </form>
                </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No suppliers added yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $suppliers->links() }}

    <div class="modal fade" id="archivedSuppliersModal" tabindex="-1" aria-labelledby="archivedSuppliersLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archivedSuppliersLabel">Archived Suppliers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Archived</th>@if(auth()->user()->isOwner())<th></th>@endif</tr></thead>
                            <tbody>
                                @forelse($archivedSuppliers as $archivedSupplier)
                                    <tr>
                                        <td>{{ $archivedSupplier->name }}</td>
                                        <td>{{ $archivedSupplier->contact_person }}</td>
                                        <td>{{ $archivedSupplier->phone }}</td>
                                        <td>{{ $archivedSupplier->archived_at->format('M d, Y') }}</td>
                                        @if(auth()->user()->isOwner())
                                            <td>
                                                <form action="{{ route('suppliers.restore', $archivedSupplier) }}" method="POST" onsubmit="return confirm('Restore this supplier?')">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary">Restore</button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No archived suppliers.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
