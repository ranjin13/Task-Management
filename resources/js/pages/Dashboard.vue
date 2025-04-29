<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, CheckCircle, Clock, List } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Task Statistics -->
                <Link :href="route('tasks.index', { status: 'to-do' })" class="relative flex aspect-video flex-col items-center justify-center overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-sidebar-border dark:bg-gray-800">
                    <div class="absolute right-2 top-2 rounded-full bg-gray-100 p-2 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <List class="h-5 w-5" />
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white">To Do</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">View all pending tasks</p>
                </Link>
                
                <Link :href="route('tasks.index', { status: 'in-progress' })" class="relative flex aspect-video flex-col items-center justify-center overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-sidebar-border dark:bg-gray-800">
                    <div class="absolute right-2 top-2 rounded-full bg-blue-100 p-2 text-blue-600 dark:bg-blue-900 dark:text-blue-300">
                        <Clock class="h-5 w-5" />
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white">In Progress</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">View all active tasks</p>
                </Link>
                
                <Link :href="route('tasks.index', { status: 'done' })" class="relative flex aspect-video flex-col items-center justify-center overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-sidebar-border dark:bg-gray-800">
                    <div class="absolute right-2 top-2 rounded-full bg-green-100 p-2 text-green-600 dark:bg-green-900 dark:text-green-300">
                        <CheckCircle class="h-5 w-5" />
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Completed</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">View all completed tasks</p>
                </Link>
            </div>
            
            <div class="flex-1 rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Task Management</h2>
                    <Link :href="route('tasks.create')" class="rounded-md bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                        Add New Task
                    </Link>
                </div>
                
                <div class="mt-6 text-center">
                    <ClipboardList class="mx-auto h-16 w-16 text-gray-400" />
                    <h3 class="mt-2 text-lg font-medium text-gray-700 dark:text-gray-300">Organize Your Tasks</h3>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Create, manage, and track your tasks with our simple task management system.</p>
                    <Link :href="route('tasks.index')" class="mt-4 inline-block rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                        View All Tasks
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
