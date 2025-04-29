<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

// Props for task data
const props = defineProps<{
  task: {
    id: number;
    title: string;
    description: string | null;
    status: 'to-do' | 'in-progress' | 'done';
    image_path: string | null;
    is_published: boolean;
    created_at: string;
    updated_at: string;
  };
}>();

// Get status badge class
function getStatusClass(status: string): string {
  switch (status) {
    case 'to-do':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
    case 'in-progress':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
    case 'done':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Tasks',
    href: route('tasks.index'),
  },
  {
    title: props.task.title,
    href: route('tasks.show', props.task.id),
  },
];
</script>

<template>
  <Head :title="task.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold">{{ task.title }}</h1>
        <div class="flex gap-2">
          <Link
            :href="route('tasks.edit', task.id)"
            class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Edit
          </Link>
          <Link
            :href="route('tasks.destroy', task.id)"
            method="delete"
            as="button"
            type="button"
            class="inline-flex items-center rounded-md bg-red-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            onclick="return confirm('Are you sure you want to delete this task?')"
          >
            Delete
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <!-- Task details -->
        <div class="rounded-lg border bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <div class="space-y-6">
            <!-- Status -->
            <div>
              <h2 class="mb-2 text-lg font-semibold">Status</h2>
              <span
                :class="[
                  'rounded-full px-3 py-1 text-sm font-medium',
                  getStatusClass(task.status),
                ]"
              >
                {{ task.status.replace(/-/g, ' ') }}
              </span>
            </div>

            <!-- Description -->
            <div>
              <h2 class="mb-2 text-lg font-semibold">Description</h2>
              <p class="whitespace-pre-line text-gray-600 dark:text-gray-300">
                {{ task.description || 'No description provided.' }}
              </p>
            </div>

            <!-- Publication Status -->
            <div>
              <h2 class="mb-2 text-lg font-semibold">Publication Status</h2>
              <span
                :class="[
                  'rounded-full px-3 py-1 text-sm font-medium',
                  task.is_published
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                ]"
              >
                {{ task.is_published ? 'Published' : 'Draft' }}
              </span>
            </div>

            <!-- Dates -->
            <div>
              <h2 class="mb-2 text-lg font-semibold">Dates</h2>
              <div class="space-y-2">
                <p class="text-sm text-gray-600 dark:text-gray-300">
                  <span class="font-medium">Created:</span>
                  {{ new Date(task.created_at).toLocaleString() }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                  <span class="font-medium">Last Updated:</span>
                  {{ new Date(task.updated_at).toLocaleString() }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Task image -->
        <div v-if="task.image_path" class="rounded-lg border bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="mb-4 text-lg font-semibold">Task Image</h2>
          <img
            :src="`/storage/${task.image_path}`"
            :alt="task.title"
            class="h-auto max-h-96 w-full rounded-lg object-contain"
          />
        </div>
        <div v-else class="rounded-lg border bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="mb-4 text-lg font-semibold">Task Image</h2>
          <div class="flex h-40 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
            <p class="text-gray-500 dark:text-gray-400">No image attached</p>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <Link
          :href="route('tasks.index')"
          class="inline-flex items-center text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
        >
          &larr; Back to Tasks
        </Link>
      </div>
    </div>
  </AppLayout>
</template> 