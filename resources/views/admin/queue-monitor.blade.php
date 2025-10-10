@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Queue Monitor</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Monitor and manage background jobs and email sending</p>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Queue Worker Status -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Queue Worker Status</h2>
                <div class="flex items-center">
                    @if($queueWorkerRunning)
                        <span class="flex h-3 w-3 relative mr-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span class="text-green-600 font-medium">Running</span>
                    @else
                        <span class="flex h-3 w-3 relative mr-3">
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <span class="text-red-600 font-medium">Not Running</span>
                    @endif
                </div>
            </div>
            @if(!$queueWorkerRunning)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 font-semibold mb-2">⚠️ Critical: Queue worker is not running!</p>
                    <p class="text-red-700 text-sm">Jobs will not be processed until you start the queue worker.</p>
                    <code class="block mt-2 bg-red-100 p-2 rounded text-xs">php artisan queue:work --daemon</code>
                </div>
            @endif
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Pending Jobs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Pending Jobs</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $pendingJobs->count() }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Failed Jobs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Failed Jobs</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $failedJobs->count() }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Processing Emails -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Processing Emails</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $emailStats['processing'] ?? 0 }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Failed Emails -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Failed Emails</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $emailStats['failed'] ?? 0 }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Stuck Emails Alert -->
    @if($stuckEmails->count() > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-yellow-800 font-medium">{{ $stuckEmails->count() }} Email(s) Stuck in Processing</h3>
                    <p class="text-yellow-700 text-sm mt-1">These emails have been in "processing" status for more than 10 minutes.</p>
                    <form action="{{ route('admin.queue.reset-stuck-emails') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded text-sm font-medium">
                            Reset Stuck Emails
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Pending Jobs Section -->
    @if($pendingJobs->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Pending Jobs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Job Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Queue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waiting</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attempts</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($pendingJobs as $job)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $job['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $job['queue'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $job['created_at'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $job['waiting_minutes'] > 5 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $job['waiting_minutes'] }} min
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $job['attempts'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Failed Jobs Section -->
    @if($failedJobs->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Failed Jobs</h2>
                <div class="flex space-x-2">
                    <form action="{{ route('admin.queue.retry-all') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">
                            Retry All
                        </button>
                    </form>
                    <form action="{{ route('admin.queue.flush') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete all failed jobs?');">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium">
                            Flush All
                        </button>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Job Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Queue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Failed At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Exception</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($failedJobs as $job)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $job['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $job['queue'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $job['failed_at'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <details class="cursor-pointer">
                                        <summary class="text-blue-600 hover:text-blue-800">View Exception</summary>
                                        <pre class="mt-2 text-xs bg-gray-100 dark:bg-gray-900 p-2 rounded overflow-x-auto">{{ $job['exception'] }}</pre>
                                    </details>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex space-x-2">
                                        <form action="{{ route('admin.queue.retry', $job['uuid']) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800">Retry</button>
                                        </form>
                                        <form action="{{ route('admin.queue.delete', $job['uuid']) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Failed Emails Section -->
    @if($failedEmails->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Failed Emails</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">To</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prepared By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Error</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($failedEmails as $email)
                            <tr>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('tasks.show', $email->task_id) }}" class="text-blue-600 hover:text-blue-800">
                                        #{{ $email->task_id }} - {{ Str::limit($email->task->title ?? 'N/A', 30) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($email->to_emails, 30) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($email->subject, 40) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $email->preparer->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <details class="cursor-pointer">
                                        <summary class="text-red-600 hover:text-red-800">View Error</summary>
                                        <pre class="mt-2 text-xs bg-gray-100 dark:bg-gray-900 p-2 rounded overflow-x-auto">{{ $email->error_message }}</pre>
                                    </details>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="{{ route('admin.queue.retry-email', $email->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:text-blue-800">Reset to Draft</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Email Statistics -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Email Preparation Statistics</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($emailStats as $status => $count)
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $count }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ $status }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Auto-refresh notice -->
    <div class="text-center text-gray-500 dark:text-gray-400 text-sm mt-8">
        <p>This page auto-refreshes every 30 seconds</p>
        <p class="mt-1">Last refreshed: <span id="last-refresh">{{ now()->format('Y-m-d H:i:s') }}</span></p>
    </div>
</div>

<script>
    // Auto-refresh every 30 seconds
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection

