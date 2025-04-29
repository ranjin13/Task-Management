<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';

const props = defineProps({
  trashedTasks: Object,
  filters: Object,
});

// Use props in a computed or method to avoid the unused warning
const hasTrashedTasks = () => props.trashedTasks?.data?.length > 0;

const confirmRestore = (taskId) => {
  if (confirm('Are you sure you want to restore this task?')) {
    router.post(route('tasks.restore', taskId));
  }
};

const confirmForceDelete = (taskId) => {
  if (confirm('Are you sure you want to permanently delete this task? This action cannot be undone.')) {
    router.delete(route('tasks.force-delete', taskId));
  }
};

const getDaysLeft = (deletedAt: string): number => {
  const deleteDate = new Date(deletedAt);
  const expiryDate = new Date(deleteDate);
  expiryDate.setDate(expiryDate.getDate() + 30);
  
  const today = new Date();
  const diffTime = expiryDate.getTime() - today.getTime();
  const daysLeft = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  return daysLeft > 0 ? daysLeft : 0;
};

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
    title: 'Trash',
    href: route('tasks.trashed'),
  },
];
</script>

<template>
  <Head title="Trashed Tasks" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Trashed Tasks</h1>
        <Link
          :href="route('tasks.index')"
          class="rounded-md bg-gray-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-600"
        >
          Back to Tasks
        </Link>
      </div>

      <div v-if="!hasTrashedTasks()" class="rounded-lg bg-white p-6 text-center shadow dark:bg-gray-800">
        <p class="text-gray-500 dark:text-gray-400">No trashed tasks found.</p>
      </div>

      <div v-else class="overflow-hidden rounded-lg border border-gray-200 shadow dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Title
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Deleted At
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Auto-Delete In
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
            <tr v-for="task in trashedTasks.data" :key="task.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ task.title }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ new Date(task.deleted_at).toLocaleString() }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ getDaysLeft(task.deleted_at) }} days
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex space-x-2">
                  <button
                    @click="confirmRestore(task.id)"
                    class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  >
                    Restore
                  </button>
                  <button
                    @click="confirmForceDelete(task.id)"
                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                  >
                    Delete Permanently
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination links -->
      <div v-if="trashedTasks.meta && trashedTasks.meta.links.length > 3" class="mt-4 flex justify-center">
        <div class="flex items-center space-x-1">
          <template v-for="(link, index) in trashedTasks.meta.links" :key="index">
            <Link
              v-if="link.url"
              :href="link.url"
              class="rounded-md px-2 py-1"
              :class="{
                'bg-primary-100 text-primary-700': link.active,
                'text-gray-500 hover:text-gray-700': !link.active
              }"
            >
              <span v-if="link.label === '&laquo; Previous'">&laquo; Previous</span>
              <span v-else-if="link.label === 'Next &raquo;'">Next &raquo;</span>
              <span v-else>{{ link.label }}</span>
            </Link>
            <span 
              v-else 
              class="rounded-md px-2 py-1 opacity-50 cursor-not-allowed"
            >
              <span v-if="link.label === '&laquo; Previous'">&laquo; Previous</span>
              <span v-else-if="link.label === 'Next &raquo;'">Next &raquo;</span>
              <span v-else>{{ link.label }}</span>
            </span>
          </template>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 