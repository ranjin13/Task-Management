<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import SubtaskForm from '@/components/Subtask/SubtaskForm.vue';
import SubtaskList from '@/components/Subtask/SubtaskList.vue';

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
    subtasks?: {
      id: number;
      title: string;
      description: string | null;
      completed: boolean;
      created_at: string;
      updated_at?: string;
    }[];
    subtasks_count?: number;
    completed_subtasks_count?: number;
    subtasks_progress?: number;
    image_url?: string;
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

function handleImageError(event: Event) {
  const imgElement = event.target as HTMLImageElement;
  
  // Remove the onerror handler to prevent infinite loops
  imgElement.onerror = null;
  
  // Log the error for debugging
  console.error('Image loading error:', imgElement.src);
  
  // Instead of trying to load another image (which may fail),
  // replace with a simple gray rectangle using a data URI
  imgElement.src = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22256%22%20height%3D%22256%22%20viewBox%3D%220%200%20256%20256%22%3E%3Crect%20width%3D%22256%22%20height%3D%22256%22%20fill%3D%22%23eee%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20style%3D%22dominant-baseline%3A%20middle%3B%20text-anchor%3A%20middle%3B%20font-size%3A%2016px%3B%20fill%3A%20%23999%3B%22%3EImage%20unavailable%3C%2Ftext%3E%3C%2Fsvg%3E';
  imgElement.classList.add('image-error');
}
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
          <h3 class="mb-3 text-lg font-semibold">Task Image</h3>
          <img
            :src="task.image_url || `/storage/${task.image_path}`"
            :alt="task.title"
            class="max-h-96 w-auto rounded-md"
            @error="handleImageError"
          />
        </div>
        <div v-else class="rounded-lg border bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="mb-4 text-lg font-semibold">Task Image</h2>
          <div class="flex h-40 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
            <p class="text-gray-500 dark:text-gray-400">No image attached</p>
          </div>
        </div>
      </div>

      <!-- Subtasks Section -->
      <div class="mt-4 grid grid-cols-1 gap-4">
        <!-- Subtask Form -->
        <div class="rounded-lg border bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h2 class="mb-4 text-lg font-semibold">Add Subtask</h2>
          <SubtaskForm :task-id="task.id" />
        </div>

        <!-- Subtask List -->
        <SubtaskList 
          :task-id="task.id"
          :subtasks="task.subtasks || null"
        />
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