{{-- Add this to the existing package card, NOT in a replacement layout. --}}
@php
    $lebdOwned = $activePackageAccess->get((int) $package->id);
@endphp
@if($lebdOwned)
    <div class="alert alert-success" role="status" style="margin:10px 0;padding:12px;border-radius:10px;">
        <strong>✓ আপনি এই প্যাকেজে যুক্ত আছেন।</strong>
        <div>মেয়াদ শেষ: {{ \Carbon\Carbon::parse($lebdOwned->ends_at)->timezone('Asia/Dhaka')->format('d M Y, h:i A') }}</div>
    </div>
    <span class="btn btn-success disabled" role="text" aria-disabled="true" style="pointer-events:none;opacity:.9">✓ আপনি প্যাকেজ কিনেছেন</span>
@else
    <a class="btn btn-primary" href="{{ route('lebd.packages.checkout', ['section'=>'mcq','package'=>$package->id]) }}">প্যাকেজ কিনুন</a>
@endif
