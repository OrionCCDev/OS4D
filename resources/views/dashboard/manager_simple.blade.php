<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manager Dashboard') }}
            </h2>
            <div class="text-sm text-gray-500">
                Last updated: {{ now()->format('M d, Y H:i') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <i class="bx bx-user text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $data['overview']['total_users'] }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400">{{ $data['overview']['active_users'] }} active</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Tasks -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <i class="bx bx-task text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tasks</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $data['overview']['total_tasks'] }}</p>
                                <p class="text-xs text-blue-600 dark:text-blue-400">{{ $data['overview']['completion_rate'] }}% completed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <i class="bx bx-folder text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Projects</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $data['overview']['total_projects'] }}</p>
                                <p class="text-xs text-purple-600 dark:text-purple-400">{{ $data['project_stats']['active'] }} active</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Completed -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                                    <i class="bx bx-check-circle text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Week</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $data['overview']['weekly_completed'] }}</p>
                                <p class="text-xs text-orange-600 dark:text-orange-400">tasks completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $data['task_stats']['completed'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Completed</p>
                        </div>
                        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $data['task_stats']['in_progress'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">In Progress</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $data['task_stats']['pending'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Pending</p>
                        </div>
                        <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $data['task_stats']['overdue'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Overdue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
