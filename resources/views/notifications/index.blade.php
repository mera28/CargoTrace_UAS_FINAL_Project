@extends(auth()->user()->isCustomer() ? 'layouts.customer' : 'layouts.app')

@section('title', 'In-App Notifications')
@section('header-title', 'Operational Notifications')
@section('header-subtitle', 'Real-time alerts, customs exceptions, and corridor milestones')

@section('content')
<div class="space-y-6 max-w-5xl {{ auth()->user()->isCustomer() ? 'mx-auto px-4 sm:px-6 lg:px-8 mt-6' : '' }}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Notification Center</h2>
            @if($unreadCount > 0)
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                    {{ $unreadCount }} Unread
                </span>
            @else
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    All Caught Up
                </span>
            @endif
        </div>

        @if($unreadCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-xl text-xs font-semibold shadow-sm transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Mark All as Read</span>
                </button>
            </form>
        @endif
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($notifications as $notif)
            @php
                $isUnread = !$notif->is_read;
                $isDanger = str_contains(strtolower($notif->title), 'hold') || str_contains(strtolower($notif->title), 'delay') || str_contains(strtolower($notif->title), 'alert');
            @endphp
            <div class="p-5 {{ $isUnread ? ($isDanger ? 'bg-rose-50/40 border-l-4 border-rose-500' : 'bg-ocean-50/30 border-l-4 border-ocean-600') : 'hover:bg-slate-50/60' }} transition-colors flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="flex items-start space-x-3.5">
                    <!-- Icon -->
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isDanger ? 'bg-rose-100 text-rose-600' : ($isUnread ? 'bg-ocean-100 text-ocean-600' : 'bg-slate-100 text-slate-400') }}">
                        @if($isDanger)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-bold {{ $isUnread ? 'text-slate-900' : 'text-slate-700' }}">{{ $notif->title }}</h3>
                            @if($isUnread)
                                <span class="w-2 h-2 rounded-full bg-ocean-500"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">{{ $notif->message }}</p>

                        <div class="flex flex-wrap items-center gap-3 pt-1 text-[11px] text-slate-400 font-mono">
                            <span>{{ $notif->created_at->format('d M Y - H:i') }} UTC ({{ $notif->created_at->diffForHumans() }})</span>

                            @if($notif->shipment)
                                <span class="text-slate-300">&bull;</span>
                                <a href="{{ route('shipments.show', $notif->shipment) }}" class="font-bold text-ocean-600 hover:text-ocean-700 underline flex items-center space-x-1">
                                    <span>Consignment {{ $notif->shipment->shipment_number }}</span>
                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if($isUnread)
                    <form action="{{ route('notifications.read', $notif) }}" method="POST" class="self-end sm:self-auto flex-shrink-0">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-ocean-700 hover:text-ocean-800 hover:bg-ocean-100 rounded-lg border border-ocean-200 transition-colors">
                            Mark as read
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="p-12 text-center text-slate-400 space-y-2">
                <svg class="w-12 h-12 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <div class="text-sm font-semibold text-slate-600">No Notifications Yet</div>
                <p class="text-xs text-slate-400">All alerts and ETA tracking updates will appear here automatically.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="pt-2">
            {{ $notifications->links() }}
        </div>
    @endif

</div>
@endsection
