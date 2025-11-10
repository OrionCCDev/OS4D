@if(auth()->user()->isSubAdmin())
    @php
        $data = app('App\Http\Controllers\DashboardController')->getSubAdminDashboardData();
    @endphp
    @include('dashboard.sub-admin', compact('data'))
@elseif(auth()->user()->isManager())
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
