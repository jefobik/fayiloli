@extends('layouts.app')

@section('content')
<div class="dashboard-wrap" style="display:flex;align-items:center;justify-content:center;min-height:60vh">
    <div style="text-align:center;max-width:420px">
        <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem">
            <i class="fas fa-address-book" style="font-size:2.2rem;color:#059669"></i>
        </div>
        <h2 style="font-size:1.5rem;font-weight:800;color:#1e293b;margin:0 0 0.5rem">Contacts — Coming Soon</h2>
        <p style="color:#64748b;font-size:0.9rem;line-height:1.6;margin:0 0 2rem">
            The human resources module is currently under development.
            Organisation-wide human resources management and directory will be available here.
        </p>
        <a href="{{ route('home') }}" class="toolbar-btn toolbar-btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var content = document.querySelector('.page-content');
        if (content) content.style.display = 'block';
        var overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.style.display = 'none';
    });
</script>
@endsection
