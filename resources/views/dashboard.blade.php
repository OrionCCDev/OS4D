@if(auth()->user()->isManager())
    @php
        $data = app('App\Http\Controllers\DashboardController')->getDashboardData();
    @endphp
    @include('dashboard.manager', compact('data'))
@else
    @php
        $userData = app('App\Http\Controllers\DashboardController')->getUserDashboardData();
    @endphp
    @include('dashboard.user', compact('userData'))
@endif
