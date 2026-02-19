@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Notification</h5>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                        &larr; Back
                    </a>
                </div>
                <div class="card-body">
                    <p class="mb-2">{{ $notification->message }}</p>
                    <small class="text-muted">
                        {{ $notification->created_at?->format('d M Y, H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
